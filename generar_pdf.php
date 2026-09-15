<?php

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/*
|--------------------------------------------------------------------------
| ID DE LA FACTURA
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    http_response_code(400);
    exit('ID de factura no válido.');
}

/*
|--------------------------------------------------------------------------
| USUARIO CONECTADO
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['usuario_id']) ||
    (int) $_SESSION['usuario_id'] <= 0
) {
    http_response_code(403);
    exit('Debes iniciar sesión para generar el PDF.');
}

$usuarioId = (int) $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| CLAVE DE CIFRADO
|--------------------------------------------------------------------------
*/

$claveCifrado = $VIZIUNEAI_FACTURAS_KEY ?? '';

if ($claveCifrado === '' || strlen($claveCifrado) < 32) {
    http_response_code(500);

    exit(
        'La clave de cifrado de facturas no está configurada correctamente.'
    );
}

/*
|--------------------------------------------------------------------------
| FUNCIONES
|--------------------------------------------------------------------------
*/

function h($valor)
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

function dinero($valor)
{
    return number_format(
        (float) $valor,
        2,
        ',',
        '.'
    ) . ' €';
}

function fecha_es($fecha)
{
    if (empty($fecha)) {
        return '';
    }

    $timestamp = strtotime($fecha);

    if (!$timestamp) {
        return '';
    }

    return date('d/m/Y', $timestamp);
}

/*
|--------------------------------------------------------------------------
| DESCIFRAR FACTURA PRIVADA
|--------------------------------------------------------------------------
*/

function descifrarFacturaPrivada(
    string $datosCifrados,
    string $claveCifrado
): array {

    $datosBinarios = base64_decode(
        $datosCifrados,
        true
    );

    if ($datosBinarios === false) {
        throw new Exception(
            'Los datos privados de la factura no son válidos.'
        );
    }

    if (strlen($datosBinarios) <= 16) {
        throw new Exception(
            'Los datos privados de la factura están incompletos.'
        );
    }

    $iv = substr(
        $datosBinarios,
        0,
        16
    );

    $contenidoCifrado = substr(
        $datosBinarios,
        16
    );

    $clave = hash(
        'sha256',
        $claveCifrado,
        true
    );

    $jsonFactura = openssl_decrypt(
        $contenidoCifrado,
        'AES-256-CBC',
        $clave,
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($jsonFactura === false) {
        throw new Exception(
            'No se ha podido descifrar la información privada de la factura.'
        );
    }

    try {

        $datosFactura = json_decode(
            $jsonFactura,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

    } catch (Throwable $e) {

        throw new Exception(
            'Los datos privados de la factura no tienen un formato válido.'
        );
    }

    if (!is_array($datosFactura)) {
        throw new Exception(
            'Los datos privados de la factura no son válidos.'
        );
    }

    return $datosFactura;
}

/* ==========================================================================
   CONSULTAR FACTURA
========================================================================== */

try {

    /*
    |--------------------------------------------------------------------------
    | FACTURA DEL USUARIO CONECTADO
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            f.id,
            f.serie,
            f.numero,
            f.fecha_emision,
            f.estado,
            f.cliente_id,
            f.created_at,
            f.updated_at
        FROM facturas f
        INNER JOIN clientes c
            ON c.id = f.cliente_id
        WHERE f.id = ?
          AND c.usuario_id = ?
          AND c.activo = 1
        LIMIT 1
    ");

    $stmt->execute([
        $id,
        $usuarioId
    ]);

    $factura = $stmt->fetch();

    if (!$factura) {
        http_response_code(403);

        exit(
            'No tienes permiso para acceder a esta factura.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SOLO FACTURAS EMITIDAS
    |--------------------------------------------------------------------------
    */

    if ($factura['estado'] !== 'emitida') {
        http_response_code(400);

        exit(
            'El PDF solamente puede generarse para una factura emitida.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS PRIVADOS CIFRADOS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            datos_cifrados
        FROM facturas_privadas
        WHERE factura_id = ?
          AND usuario_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $id,
        $usuarioId
    ]);

    $privado = $stmt->fetch();

    if (!$privado) {
        throw new Exception(
            'No se han encontrado los datos privados de esta factura.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DESCIFRAR
    |--------------------------------------------------------------------------
    */

    $datosFactura = descifrarFacturaPrivada(
        $privado['datos_cifrados'],
        $claveCifrado
    );

    /*
    |--------------------------------------------------------------------------
    | DATOS DE FACTURA
    |--------------------------------------------------------------------------
    */

    $datosInternosFactura =
        $datosFactura['factura'] ?? [];

    $datosCliente =
        $datosFactura['cliente'] ?? [];

    $lineas =
        $datosFactura['lineas'] ?? [];

    if (!is_array($datosInternosFactura)) {
        $datosInternosFactura = [];
    }

    if (!is_array($datosCliente)) {
        $datosCliente = [];
    }

    if (!is_array($lineas)) {
        $lineas = [];
    }

    if (empty($lineas)) {
        throw new Exception(
            'La factura no contiene líneas.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EMPRESA EMISORA
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT *
        FROM empresa_facturacion
        ORDER BY id ASC
        LIMIT 1
    ");

    $empresa = $stmt->fetch();

    if (!$empresa) {
        throw new Exception(
            'No existe la configuración de la empresa emisora.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NÚMERO DE FACTURA
    |--------------------------------------------------------------------------
    */

    $serie = trim(
        (string) (
            $factura['serie']
            ?? $datosInternosFactura['serie']
            ?? ''
        )
    );

    $numero =
        $factura['numero']
        ?? $datosInternosFactura['numero']
        ?? null;

    if (
        $numero !== null &&
        $numero !== ''
    ) {

        $numeroFactura =
            $serie .
            '-' .
            str_pad(
                (string) $numero,
                4,
                '0',
                STR_PAD_LEFT
            );

    } else {

        $numeroFactura = 'BORRADOR';
    }

    /*
    |--------------------------------------------------------------------------
    | FECHA DE EMISIÓN
    |--------------------------------------------------------------------------
    */

    $fechaEmision =
        $factura['fecha_emision']
        ?? $datosInternosFactura['fecha_emision']
        ?? '';

    /*
    |--------------------------------------------------------------------------
    | FECHA DE VENCIMIENTO
    |--------------------------------------------------------------------------
    */

    $fechaVencimiento =
        $datosInternosFactura['fecha_vencimiento']
        ?? '';

    /*
    |--------------------------------------------------------------------------
    | MÉTODO DE PAGO
    |--------------------------------------------------------------------------
    */

    $metodoPago =
        $datosInternosFactura['metodo_pago']
        ?? '';

    /*
    |--------------------------------------------------------------------------
    | OBSERVACIONES
    |--------------------------------------------------------------------------
    */

    $observaciones =
        $datosInternosFactura['observaciones']
        ?? '';

    /*
    |--------------------------------------------------------------------------
    | TOTALES
    |--------------------------------------------------------------------------
    */

    $baseImponible =
        $datosInternosFactura['base_imponible']
        ?? 0;

    $totalIva =
        $datosInternosFactura['total_iva']
        ?? 0;

    $totalIrpf =
        $datosInternosFactura['total_irpf']
        ?? 0;

    $total =
        $datosInternosFactura['total']
        ?? 0;

    /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    */

    $logoHtml = '';

    if (!empty($empresa['logo'])) {

        $rutaLogo = trim(
            (string) $empresa['logo']
        );

        if (
            preg_match(
                '/^https?:\/\//i',
                $rutaLogo
            )
        ) {

            $logoHtml =
                '<img src="' .
                h($rutaLogo) .
                '" class="logo">';

        } else {

            $rutaLogo =
                __DIR__ .
                '/' .
                ltrim(
                    $rutaLogo,
                    '/\\'
                );

            if (file_exists($rutaLogo)) {

                $extension =
                    strtolower(
                        pathinfo(
                            $rutaLogo,
                            PATHINFO_EXTENSION
                        )
                    );

                $permitidas = [
                    'jpg',
                    'jpeg',
                    'png',
                    'gif'
                ];

                if (
                    in_array(
                        $extension,
                        $permitidas,
                        true
                    )
                ) {

                    $contenidoLogo =
                        file_get_contents(
                            $rutaLogo
                        );

                    if ($contenidoLogo !== false) {

                        $mime = match ($extension) {

                            'jpg',
                            'jpeg' =>
                                'image/jpeg',

                            'png' =>
                                'image/png',

                            'gif' =>
                                'image/gif',

                            default =>
                                null
                        };

                        if ($mime) {

                            $logoBase64 =
                                base64_encode(
                                    $contenidoLogo
                                );

                            $logoHtml =
                                '<img src="data:' .
                                $mime .
                                ';base64,' .
                                $logoBase64 .
                                '" class="logo">';
                        }
                    }
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HTML DE LAS LÍNEAS
    |--------------------------------------------------------------------------
    */

    $lineasHtml = '';

    foreach ($lineas as $linea) {

        $lineasHtml .= '

            <tr>

                <td class="descripcion">
                    ' .
                    h(
                        $linea['descripcion'] ?? ''
                    ) .
                    '
                </td>

                <td class="cantidad">
                    ' .
                    number_format(
                        (float) (
                            $linea['cantidad'] ?? 0
                        ),
                        3,
                        ',',
                        '.'
                    ) .
                    '
                </td>

                <td class="precio">
                    ' .
                    dinero(
                        $linea['precio_unitario'] ?? 0
                    ) .
                    '
                </td>

                <td class="descuento">
                    ' .
                    number_format(
                        (float) (
                            $linea['descuento'] ?? 0
                        ),
                        2,
                        ',',
                        '.'
                    ) .
                    ' %
                </td>

                <td class="iva">
                    ' .
                    number_format(
                        (float) (
                            $linea['tipo_iva'] ?? 0
                        ),
                        2,
                        ',',
                        '.'
                    ) .
                    ' %
                </td>

                <td class="importe">
                    ' .
                    dinero(
                        $linea['base_linea'] ?? 0
                    ) .
                    '
                </td>

            </tr>

        ';
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS EMPRESA
    |--------------------------------------------------------------------------
    */

    $empresaDireccion = trim(
        (string) (
            $empresa['direccion'] ?? ''
        )
    );

    $empresaLocalidad = trim(
        implode(
            ' · ',
            array_filter([
                $empresa['codigo_postal'] ?? '',
                $empresa['ciudad'] ?? '',
                $empresa['provincia'] ?? ''
            ])
        )
    );

    /*
    |--------------------------------------------------------------------------
    | DATOS CLIENTE
    |--------------------------------------------------------------------------
    */

    $clienteNombre =
        $datosCliente['nombre_razon_social']
        ?? '';

    $clienteNif =
        $datosCliente['nif']
        ?? '';

    $clienteDireccion =
        trim(
            (string) (
                $datosCliente['direccion']
                ?? ''
            )
        );

    $clienteLocalidad = trim(
        implode(
            ' · ',
            array_filter([
                $datosCliente['codigo_postal'] ?? '',
                $datosCliente['ciudad'] ?? '',
                $datosCliente['provincia'] ?? ''
            ])
        )
    );

    $clientePais =
        $datosCliente['pais']
        ?? '';

    $clienteEmail =
        $datosCliente['email']
        ?? '';

    $clienteTelefono =
        $datosCliente['telefono']
        ?? '';

    /*
    |--------------------------------------------------------------------------
    | CARGAR CSS DEL PDF
    |--------------------------------------------------------------------------
    |
    | El archivo es:
    |
    | /css/generar-pdf.css
    |
    */

    $rutaCssPdf =
        __DIR__ . '/css/generar-pdf.css';

    if (!file_exists($rutaCssPdf)) {
        throw new Exception(
            'No existe el archivo CSS del PDF: ' .
            $rutaCssPdf
        );
    }

    if (!is_readable($rutaCssPdf)) {
        throw new Exception(
            'El archivo CSS del PDF no se puede leer: ' .
            $rutaCssPdf
        );
    }

    $cssPdf = file_get_contents(
        $rutaCssPdf
    );

    if (
        $cssPdf === false ||
        trim($cssPdf) === ''
    ) {
        throw new Exception(
            'No se ha podido cargar el estilo del PDF.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HTML COMPLETO
    |--------------------------------------------------------------------------
    |
    | El contenido del CSS se introduce directamente dentro de <style>.
    | De esta forma Dompdf utiliza los estilos del archivo generar-pdf.css.
    |
    */

    $html = '

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <style>
        ' . $cssPdf . '
    </style>

</head>

<body>

<div class="pagina">

    <header class="cabecera">

        <div class="empresa">

            ' . $logoHtml . '

            <div class="empresa-datos">

                <h1>
                    ' .
                    h(
                        $empresa['razon_social'] ?? ''
                    ) .
                    '
                </h1>

                <p>

                    <strong>NIF:</strong>

                    ' .
                    h(
                        $empresa['nif'] ?? ''
                    ) .
                    '

                </p>

                ' . (
                    $empresaDireccion !== ''
                    ? '<p>' .
                        h($empresaDireccion) .
                      '</p>'
                    : ''
                ) . '

                ' . (
                    $empresaLocalidad !== ''
                    ? '<p>' .
                        h($empresaLocalidad) .
                      '</p>'
                    : ''
                ) . '

                ' . (
                    !empty($empresa['pais'])
                    ? '<p>' .
                        h($empresa['pais']) .
                      '</p>'
                    : ''
                ) . '

                ' . (
                    !empty($empresa['email'])
                    ? '<p>' .
                        h($empresa['email']) .
                      '</p>'
                    : ''
                ) . '

                ' . (
                    !empty($empresa['telefono'])
                    ? '<p>' .
                        h($empresa['telefono']) .
                      '</p>'
                    : ''
                ) . '

                ' . (
                    !empty($empresa['web'])
                    ? '<p>' .
                        h($empresa['web']) .
                      '</p>'
                    : ''
                ) . '

            </div>

        </div>


        <div class="factura-titulo">

            <h2>FACTURA</h2>

            <div class="numero-factura">

                ' .
                h($numeroFactura) .
                '

            </div>

            <div class="datos-factura">

                <p>

                    <strong>
                        Fecha de emisión:
                    </strong>

                    <br>

                    ' .
                    fecha_es(
                        $fechaEmision
                    ) .
                    '

                </p>

                ' . (
                    !empty($fechaVencimiento)
                    ? '

                    <p>

                        <strong>
                            Fecha de vencimiento:
                        </strong>

                        <br>

                        ' .
                        fecha_es(
                            $fechaVencimiento
                        ) .
                        '

                    </p>

                    '
                    : ''
                ) . '

            </div>

        </div>

    </header>


    <section class="bloque-clientes">

        <div class="cliente">

            <div class="titulo-bloque">
                CLIENTE
            </div>

            <h3>
                ' .
                h($clienteNombre) .
                '
            </h3>

            <p>

                <strong>NIF:</strong>

                ' .
                h($clienteNif) .
                '

            </p>

            ' . (
                $clienteDireccion !== ''
                ? '<p>' .
                    h($clienteDireccion) .
                  '</p>'
                : ''
            ) . '

            ' . (
                $clienteLocalidad !== ''
                ? '<p>' .
                    h($clienteLocalidad) .
                  '</p>'
                : ''
            ) . '

            ' . (
                !empty($clientePais)
                ? '<p>' .
                    h($clientePais) .
                  '</p>'
                : ''
            ) . '

            ' . (
                !empty($clienteEmail)
                ? '<p>' .
                    h($clienteEmail) .
                  '</p>'
                : ''
            ) . '

            ' . (
                !empty($clienteTelefono)
                ? '<p>' .
                    h($clienteTelefono) .
                  '</p>'
                : ''
            ) . '

        </div>

    </section>


    <section class="tabla-lineas">

        <table>

            <thead>

                <tr>

                    <th class="descripcion">
                        DESCRIPCIÓN
                    </th>

                    <th>
                        CANT.
                    </th>

                    <th>
                        PRECIO
                    </th>

                    <th>
                        DTO.
                    </th>

                    <th>
                        IVA
                    </th>

                    <th>
                        BASE
                    </th>

                </tr>

            </thead>

            <tbody>

                ' .
                $lineasHtml .
                '

            </tbody>

        </table>

    </section>


    <section class="zona-final">

        <div class="pago">

            <div class="titulo-bloque">
                FORMA DE PAGO
            </div>

            <p>
                ' .
                h($metodoPago) .
                '
            </p>

            ' . (
                !empty($empresa['iban'])
                ? '

                <p>

                    <strong>
                        IBAN:
                    </strong>

                    <br>

                    ' .
                    h(
                        $empresa['iban']
                    ) .
                    '

                </p>

                '
                : ''
            ) . '

        </div>


        <div class="totales">

            <div class="fila-total">

                <span>
                    Base imponible
                </span>

                <strong>

                    ' .
                    dinero(
                        $baseImponible
                    ) .
                    '

                </strong>

            </div>


            <div class="fila-total">

                <span>
                    IVA
                </span>

                <strong>

                    ' .
                    dinero(
                        $totalIva
                    ) .
                    '

                </strong>

            </div>


            ' . (
                (float) $totalIrpf != 0
                ? '

                <div class="fila-total">

                    <span>
                        IRPF
                    </span>

                    <strong>

                        -' .
                        dinero(
                            $totalIrpf
                        ) .
                        '

                    </strong>

                </div>

                '
                : ''
            ) . '


            <div class="fila-total total-final">

                <span>
                    TOTAL
                </span>

                <strong>

                    ' .
                    dinero(
                        $total
                    ) .
                    '

                </strong>

            </div>

        </div>

    </section>


    ' . (
        !empty(
            trim(
                (string) $observaciones
            )
        )
        ? '

        <section class="observaciones">

            <div class="titulo-bloque">
                OBSERVACIONES
            </div>

            <p>

                ' .
                nl2br(
                    h($observaciones)
                ) .
                '

            </p>

        </section>

        '
        : ''
    ) . '


    <footer class="pie">

        <p>

            ' .
            h(
                $empresa['razon_social'] ?? ''
            ) .
            '

            · NIF:

            ' .
            h(
                $empresa['nif'] ?? ''
            ) .
            '

        </p>

        <p>
            Documento generado electrónicamente.
        </p>

    </footer>

</div>

</body>

</html>

';

    /*
    |--------------------------------------------------------------------------
    | CONFIGURAR DOMPDF
    |--------------------------------------------------------------------------
    */

    $rutaTempDompdf =
        __DIR__ . '/tmp/dompdf';

    if (!is_dir($rutaTempDompdf)) {

        if (
            !mkdir(
                $rutaTempDompdf,
                0775,
                true
            ) &&
            !is_dir($rutaTempDompdf)
        ) {

            throw new Exception(
                'No se ha podido crear la carpeta temporal de Dompdf: ' .
                $rutaTempDompdf
            );
        }
    }

    if (!is_writable($rutaTempDompdf)) {

        throw new Exception(
            'La carpeta temporal de Dompdf no tiene permisos de escritura: ' .
            $rutaTempDompdf
        );
    }

    $options = new Options();

    $options->set(
        'tempDir',
        $rutaTempDompdf
    );

    $options->set(
        'isRemoteEnabled',
        true
    );

    $options->set(
        'isHtml5ParserEnabled',
        true
    );

    /*
    |--------------------------------------------------------------------------
    | GENERAR PDF
    |--------------------------------------------------------------------------
    */

    $dompdf = new Dompdf(
        $options
    );

    $dompdf->loadHtml(
        $html,
        'UTF-8'
    );

    $dompdf->setPaper(
        'A4',
        'portrait'
    );

    $dompdf->render();

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR PDF
    |--------------------------------------------------------------------------
    */

    $nombreArchivo =
        'Factura-' .
        preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            $numeroFactura
        ) .
        '.pdf';

    $dompdf->stream(
        $nombreArchivo,
        [
            'Attachment' => false
        ]
    );

    exit;

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | DIAGNÓSTICO TEMPORAL
    |--------------------------------------------------------------------------
    */

    error_log(
        'ERROR REAL generar_pdf.php: ' .
        $e->getMessage() .
        ' | FILE: ' .
        $e->getFile() .
        ' | LINE: ' .
        $e->getLine() .
        ' | TRACE: ' .
        $e->getTraceAsString()
    );

    http_response_code(500);

    ?>

    <!DOCTYPE html>

    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            Error generando PDF
        </title>

        <link
            rel="stylesheet"
            href="css/generar-pdf.css"
        >

    </head>

    <body>

        <main class="error-pdf">

            <div class="error-icono">
                !
            </div>

            <h1>
                No se ha podido generar el PDF
            </h1>

            <p>

                ERROR REAL:

                <?php echo h($e->getMessage()); ?>

            </p>

            <div class="error-acciones">

                <a
                    href="javascript:history.back()"
                    class="boton"
                >
                    ← Volver
                </a>

            </div>

        </main>

    </body>

    </html>

    <?php

    exit;
}