<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


/*
|--------------------------------------------------------------------------
| ID DE LA FACTURA
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    exit('ID de factura no válido.');
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



/* ==========================================================================
   CONSULTAR FACTURA
========================================================================== */

try {

    /*
    |--------------------------------------------------------------------------
    | USUARIO CONECTADO
    |--------------------------------------------------------------------------
    |
    | Si existe una sesión de usuario, comprobamos que la factura
    | pertenezca al cliente asociado a ese usuario.
    |
    */

    if (isset($_SESSION['usuario_id'])) {

        $usuarioId =
            (int) $_SESSION['usuario_id'];


        if ($usuarioId <= 0) {

            http_response_code(403);

            exit(
                'No tienes permiso para generar este PDF.'
            );

        }


        $stmt = $pdo->prepare("

            SELECT

                f.*,

                c.nombre_razon_social AS cliente_nombre,

                c.tipo_persona AS cliente_tipo_persona,

                c.nif AS cliente_nif,

                c.direccion AS cliente_direccion,

                c.codigo_postal AS cliente_codigo_postal,

                c.ciudad AS cliente_ciudad,

                c.provincia AS cliente_provincia,

                c.pais AS cliente_pais,

                c.email AS cliente_email,

                c.telefono AS cliente_telefono

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

    } else {

        /*
        |--------------------------------------------------------------------------
        | ADMINISTRACIÓN
        |--------------------------------------------------------------------------
        |
        | Si no hay usuario conectado, mantenemos exactamente
        | el comportamiento anterior.
        |
        */

        $stmt = $pdo->prepare("

            SELECT

                f.*,

                c.nombre_razon_social AS cliente_nombre,

                c.tipo_persona AS cliente_tipo_persona,

                c.nif AS cliente_nif,

                c.direccion AS cliente_direccion,

                c.codigo_postal AS cliente_codigo_postal,

                c.ciudad AS cliente_ciudad,

                c.provincia AS cliente_provincia,

                c.pais AS cliente_pais,

                c.email AS cliente_email,

                c.telefono AS cliente_telefono

            FROM facturas f

            INNER JOIN clientes c

                ON c.id = f.cliente_id

            WHERE f.id = ?

            LIMIT 1

        ");


        $stmt->execute([

            $id

        ]);

    }


    $factura = $stmt->fetch();


    if (!$factura) {

        /*
        |--------------------------------------------------------------------------
        | NO REVELAR INFORMACIÓN DE FACTURAS AJENAS
        |--------------------------------------------------------------------------
        */

        if (isset($_SESSION['usuario_id'])) {

            http_response_code(403);

            exit(
                'No tienes permiso para acceder a esta factura.'
            );

        }


        http_response_code(404);

        exit(
            'La factura no existe.'
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
    | LÍNEAS DE FACTURA
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            *
        FROM factura_lineas
        WHERE factura_id = ?
        ORDER BY orden ASC, id ASC
    ");

    $stmt->execute([$id]);

    $lineas = $stmt->fetchAll();


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
        (string) ($factura['serie'] ?? '')
    );

    $numero = $factura['numero'];

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
    | LOGO
    |--------------------------------------------------------------------------
    */

    $logoHtml = '';

    if (
        !empty($empresa['logo'])
    ) {

        $rutaLogo = $empresa['logo'];

        if (
            !preg_match(
                '/^https?:\/\//i',
                $rutaLogo
            )
        ) {

            $rutaLogo = __DIR__ . '/' .
                ltrim(
                    $rutaLogo,
                    '/\\'
                );

            if (file_exists($rutaLogo)) {

                $extension = strtolower(
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
                        file_get_contents($rutaLogo);

                    if ($contenidoLogo !== false) {

                        $mime = match ($extension) {
                            'jpg',
                            'jpeg' => 'image/jpeg',

                            'png' => 'image/png',

                            'gif' => 'image/gif',

                            default => null
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

        } else {

            $logoHtml =
                '<img src="' .
                h($rutaLogo) .
                '" class="logo">';
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
                    ' . h(
                        $linea['descripcion']
                    ) . '
                </td>

                <td class="cantidad">
                    ' . number_format(
                        (float) $linea['cantidad'],
                        3,
                        ',',
                        '.'
                    ) . '
                </td>

                <td class="precio">
                    ' . dinero(
                        $linea['precio_unitario']
                    ) . '
                </td>

                <td class="descuento">
                    ' . number_format(
                        (float) $linea['descuento'],
                        2,
                        ',',
                        '.'
                    ) . ' %
                </td>

                <td class="iva">
                    ' . number_format(
                        (float) $linea['tipo_iva'],
                        2,
                        ',',
                        '.'
                    ) . ' %
                </td>

                <td class="importe">
                    ' . dinero(
                        $linea['base_linea']
                    ) . '
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
        (string) ($empresa['direccion'] ?? '')
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

    $clienteDireccion = trim(
        (string) (
            $factura['cliente_direccion'] ?? ''
        )
    );

    $clienteLocalidad = trim(
        implode(
            ' · ',
            array_filter([
                $factura['cliente_codigo_postal'] ?? '',
                $factura['cliente_ciudad'] ?? '',
                $factura['cliente_provincia'] ?? ''
            ])
        )
    );


    /*
    |--------------------------------------------------------------------------
    | HTML COMPLETO
    |--------------------------------------------------------------------------
    */

    $html = '

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<style>

' . file_get_contents(
        __DIR__ . '/css/generar-pdf.css'
    ) . '

</style>

</head>

<body>

<div class="pagina">

    <header class="cabecera">

        <div class="empresa">

            ' . $logoHtml . '

            <div class="empresa-datos">

                <h1>
                    ' . h(
                        $empresa['razon_social']
                    ) . '
                </h1>

                <p>
                    <strong>NIF:</strong>
                    ' . h(
                        $empresa['nif']
                    ) . '
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
                ' . h($numeroFactura) . '
            </div>

            <div class="datos-factura">

                <p>
                    <strong>Fecha de emisión:</strong><br>
                    ' . fecha_es(
                        $factura['fecha_emision']
                    ) . '
                </p>

                ' . (
                    !empty(
                        $factura['fecha_vencimiento']
                    )
                    ? '
                    <p>
                        <strong>Fecha de vencimiento:</strong><br>
                        ' . fecha_es(
                            $factura['fecha_vencimiento']
                        ) . '
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
                ' . h(
                    $factura['cliente_nombre']
                ) . '
            </h3>

            <p>
                <strong>NIF:</strong>
                ' . h(
                    $factura['cliente_nif']
                ) . '
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
                !empty(
                    $factura['cliente_pais']
                )
                ? '<p>' .
                    h($factura['cliente_pais']) .
                  '</p>'
                : ''
            ) . '

            ' . (
                !empty(
                    $factura['cliente_email']
                )
                ? '<p>' .
                    h($factura['cliente_email']) .
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

                ' . $lineasHtml . '

            </tbody>

        </table>

    </section>


    <section class="zona-final">

        <div class="pago">

            <div class="titulo-bloque">
                FORMA DE PAGO
            </div>

            <p>
                ' . h(
                    $factura['metodo_pago'] ?? ''
                ) . '
            </p>

            ' . (
                !empty($empresa['iban'])
                ? '
                <p>
                    <strong>IBAN:</strong><br>
                    ' . h(
                        $empresa['iban']
                    ) . '
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
                    ' . dinero(
                        $factura['base_imponible']
                    ) . '
                </strong>

            </div>

            <div class="fila-total">

                <span>
                    IVA
                </span>

                <strong>
                    ' . dinero(
                        $factura['total_iva']
                    ) . '
                </strong>

            </div>

            ' . (
                (float) $factura['total_irpf'] != 0
                ? '
                <div class="fila-total">

                    <span>
                        IRPF
                    </span>

                    <strong>
                        -' . dinero(
                            $factura['total_irpf']
                        ) . '
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
                    ' . dinero(
                        $factura['total']
                    ) . '
                </strong>

            </div>

        </div>

    </section>


    ' . (
        !empty(
            trim(
                (string) (
                    $factura['observaciones'] ?? ''
                )
            )
        )
        ? '

        <section class="observaciones">

            <div class="titulo-bloque">
                OBSERVACIONES
            </div>

            <p>
                ' . nl2br(
                    h(
                        $factura['observaciones']
                    )
                ) . '
            </p>

        </section>

        '
        : ''
    ) . '


    <footer class="pie">

        <p>
            ' . h(
                $empresa['razon_social']
            ) . '
            · NIF:
            ' . h(
                $empresa['nif']
            ) . '
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

    $options = new Options();

    $options->set(
        'isRemoteEnabled',
        true
    );

    $options->set(
        'isHtml5ParserEnabled',
        true
    );

    $options->set(
        'defaultFont',
        'DejaVu Sans'
    );


    /*
    |--------------------------------------------------------------------------
    | GENERAR PDF
    |--------------------------------------------------------------------------
    */

    $dompdf = new Dompdf($options);

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

    error_log(
        'Error generar_pdf.php: ' .
        $e->getMessage()
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

        <title>Error generando PDF</title>

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