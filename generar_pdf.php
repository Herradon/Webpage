
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

    exit(
        'ID de factura no válido.'
    );
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

    exit(
        'Debes iniciar sesión para generar el PDF.'
    );
}

$usuarioId =
    (int) $_SESSION['usuario_id'];


/*
|--------------------------------------------------------------------------
| CLAVE DE CIFRADO
|--------------------------------------------------------------------------
*/

$claveCifrado =
    $VIZIUNEAI_FACTURAS_KEY ?? '';


if (
    $claveCifrado === '' ||
    strlen($claveCifrado) < 32
) {

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

    $timestamp =
        strtotime($fecha);

    if (!$timestamp) {

        return '';
    }

    return date(
        'd/m/Y',
        $timestamp
    );
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

    $datosBinarios =
        base64_decode(
            $datosCifrados,
            true
        );


    if ($datosBinarios === false) {

        throw new Exception(
            'Los datos privados de la factura no son válidos.'
        );
    }


    if (
        strlen($datosBinarios) <= 16
    ) {

        throw new Exception(
            'Los datos privados de la factura están incompletos.'
        );
    }


    $iv =
        substr(
            $datosBinarios,
            0,
            16
        );


    $contenidoCifrado =
        substr(
            $datosBinarios,
            16
        );


    $clave =
        hash(
            'sha256',
            $claveCifrado,
            true
        );


    $jsonFactura =
        openssl_decrypt(
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

        $datosFactura =
            json_decode(
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


    if (
        !is_array($datosFactura)
    ) {

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


    $factura =
        $stmt->fetch();


    if (!$factura) {

        http_response_code(403);

        exit(
            'No tienes permiso para acceder a esta factura.'
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


    $privado =
        $stmt->fetch();


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

    $datosFactura =
        descifrarFacturaPrivada(
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


    $datosEmisor =
        $datosFactura['emisor'] ?? [];


    $lineas =
        $datosFactura['lineas'] ?? [];


    if (!is_array($datosInternosFactura)) {

        $datosInternosFactura = [];

    }


    if (!is_array($datosCliente)) {

        $datosCliente = [];

    }


    if (!is_array($datosEmisor)) {

        $datosEmisor = [];

    }


    if (!is_array($lineas)) {

        $lineas = [];

    }


    /*
    |--------------------------------------------------------------------------
    | EMISOR
    |--------------------------------------------------------------------------
    */

    if (empty($datosEmisor)) {

        $stmtEmisor = $pdo->prepare("
            SELECT
                tipo_persona,
                nombre,
                apellidos,
                nombre_razon_social,
                nombre_comercial,
                nif,
                direccion,
                codigo_postal,
                ciudad,
                provincia,
                pais,
                email,
                telefono,
                web

            FROM clientes

            WHERE usuario_id = ?
              AND activo = 1

            LIMIT 1
        ");


        $stmtEmisor->execute([
            $usuarioId
        ]);


        $datosEmisor =
            $stmtEmisor->fetch();


        if (!$datosEmisor) {

            $datosEmisor = [];

        }

    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR EMISOR
    |--------------------------------------------------------------------------
    */

    $emisorNombreRazonSocial =
        trim(
            (string) (
                $datosEmisor['nombre_razon_social']
                ?? ''
            )
        );


    $emisorNombreComercial =
        trim(
            (string) (
                $datosEmisor['nombre_comercial']
                ?? ''
            )
        );


    $emisorNombre =
        trim(
            (string) (
                $datosEmisor['nombre']
                ?? ''
            )
        );


    $emisorApellidos =
        trim(
            (string) (
                $datosEmisor['apellidos']
                ?? ''
            )
        );


    $emisorNif =
        trim(
            (string) (
                $datosEmisor['nif']
                ?? ''
            )
        );


    $emisorDireccion =
        trim(
            (string) (
                $datosEmisor['direccion']
                ?? ''
            )
        );


    $emisorCodigoPostal =
        trim(
            (string) (
                $datosEmisor['codigo_postal']
                ?? ''
            )
        );


    $emisorCiudad =
        trim(
            (string) (
                $datosEmisor['ciudad']
                ?? ''
            )
        );


    $emisorProvincia =
        trim(
            (string) (
                $datosEmisor['provincia']
                ?? ''
            )
        );


    $emisorPais =
        trim(
            (string) (
                $datosEmisor['pais']
                ?? ''
            )
        );


    $emisorEmail =
        trim(
            (string) (
                $datosEmisor['email']
                ?? ''
            )
        );


    $emisorTelefono =
        trim(
            (string) (
                $datosEmisor['telefono']
                ?? ''
            )
        );


    $emisorWeb =
        trim(
            (string) (
                $datosEmisor['web']
                ?? ''
            )
        );


    if (
        $emisorNombreRazonSocial !== ''
    ) {

        $emisorNombreMostrar =
            $emisorNombreRazonSocial;

    } elseif (
        $emisorNombreComercial !== ''
    ) {

        $emisorNombreMostrar =
            $emisorNombreComercial;

    } else {

        $emisorNombreMostrar =
            trim(
                $emisorNombre .
                ' ' .
                $emisorApellidos
            );

    }


    if (
        $emisorNombreMostrar === ''
    ) {

        $emisorNombreMostrar =
            'Emisor';

    }


    $emisorLocalidad =
        trim(
            implode(
                ' · ',
                array_filter([
                    $emisorCodigoPostal,
                    $emisorCiudad,
                    $emisorProvincia
                ])
            )
        );


    /*
    |--------------------------------------------------------------------------
    | NÚMERO DE FACTURA
    |--------------------------------------------------------------------------
    */

    $serie =
        trim(
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

        $numeroFactura =
            $serie .
            '-BORRADOR';

    }


    /*
    |--------------------------------------------------------------------------
    | FECHAS
    |--------------------------------------------------------------------------
    */

    $fechaEmision =
        $factura['fecha_emision']
        ?? $datosInternosFactura['fecha_emision']
        ?? '';


    $fechaVencimiento =
        $datosInternosFactura[
            'fecha_vencimiento'
        ]
        ?? '';


    /*
    |--------------------------------------------------------------------------
    | OTROS DATOS
    |--------------------------------------------------------------------------
    */

    $metodoPago =
        $datosInternosFactura[
            'metodo_pago'
        ]
        ?? '';


    $observaciones =
        $datosInternosFactura[
            'observaciones'
        ]
        ?? '';


    $baseImponible =
        $datosInternosFactura[
            'base_imponible'
        ]
        ?? 0;


    $totalIva =
        $datosInternosFactura[
            'total_iva'
        ]
        ?? 0;


    $totalIrpf =
        $datosInternosFactura[
            'total_irpf'
        ]
        ?? 0;


    $total =
        $datosInternosFactura[
            'total'
        ]
        ?? 0;


    /*
    |--------------------------------------------------------------------------
    | CLIENTE
    |--------------------------------------------------------------------------
    */

    $clienteNombre =
        $datosCliente[
            'nombre_razon_social'
        ]
        ?? '';


    $clienteNif =
        $datosCliente[
            'nif'
        ]
        ?? '';


    $clienteDireccion =
        trim(
            (string) (
                $datosCliente[
                    'direccion'
                ]
                ?? ''
            )
        );


    $clienteLocalidad =
        trim(
            implode(
                ' · ',
                array_filter([
                    $datosCliente[
                        'codigo_postal'
                    ] ?? '',

                    $datosCliente[
                        'ciudad'
                    ] ?? '',

                    $datosCliente[
                        'provincia'
                    ] ?? ''
                ])
            )
        );


    $clientePais =
        $datosCliente[
            'pais'
        ]
        ?? '';


    $clienteEmail =
        $datosCliente[
            'email'
        ]
        ?? '';


    $clienteTelefono =
        $datosCliente[
            'telefono'
        ]
        ?? '';


    /*
    |--------------------------------------------------------------------------
    | LÍNEAS
    |--------------------------------------------------------------------------
    */

    $lineasHtml = '';


    foreach ($lineas as $linea) {

        $cantidad =
            (float) (
                $linea['cantidad']
                ?? 0
            );


        $precio =
            (float) (
                $linea['precio_unitario']
                ?? 0
            );


        $descuento =
            (float) (
                $linea['descuento']
                ?? 0
            );


        $iva =
            (float) (
                $linea['tipo_iva']
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | TOTAL DE LA LÍNEA
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $linea['total_linea']
            )
        ) {

            $totalLinea =
                (float) $linea[
                    'total_linea'
                ];

        } elseif (
            isset(
                $linea['base_linea']
            )
        ) {

            $totalLinea =
                (float) $linea[
                    'base_linea'
                ];

        } else {

            $baseLinea =
                $cantidad *
                $precio;


            if ($descuento > 0) {

                $baseLinea -=
                    $baseLinea *
                    (
                        $descuento /
                        100
                    );

            }


            $totalLinea =
                $baseLinea +
                (
                    $baseLinea *
                    (
                        $iva /
                        100
                    )
                );

        }


        $lineasHtml .= '

        <tr>

            <td>
                ' .
                h(
                    $linea[
                        'descripcion'
                    ] ?? ''
                ) .
                '
            </td>

            <td class="centro">
                ' .
                number_format(
                    $cantidad,
                    3,
                    ',',
                    '.'
                ) .
                '
            </td>

            <td class="derecha">
                ' .
                dinero(
                    $precio
                ) .
                '
            </td>

            <td class="derecha">
                ' .
                number_format(
                    $descuento,
                    2,
                    ',',
                    '.'
                ) .
                ' %
            </td>

            <td class="derecha">
                ' .
                number_format(
                    $iva,
                    2,
                    ',',
                    '.'
                ) .
                ' %
            </td>

            <td class="derecha">
                <strong>
                    ' .
                    dinero(
                        $totalLinea
                    ) .
                    '
                </strong>
            </td>

        </tr>

        ';

    }


    /*
    |--------------------------------------------------------------------------
    | HTML
    |--------------------------------------------------------------------------
    */

    $html = '

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<style>

@page {
    margin: 35px 40px;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #222;
}

h1 {
    font-size: 22px;
    margin: 0 0 10px;
}

h2 {
    font-size: 16px;
    margin: 0 0 8px;
}

h3 {
    font-size: 11px;
    margin: 0 0 8px;
}

p {
    margin: 3px 0;
}

.cabecera {
    width: 100%;
    margin-bottom: 25px;
}

.emisor {
    width: 55%;
    float: left;
}

.datos {
    width: 40%;
    float: right;
    text-align: right;
}

.clear {
    clear: both;
}

.numero {
    font-size: 12px;
    font-weight: bold;
}

.subtexto {
    color: #777;
    font-size: 9px;
}

.bloque {
    border: 1px solid #ddd;
    padding: 12px;
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
}

th {
    background: #eeeeee;
    border: 1px solid #ccc;
    padding: 7px;
    font-size: 9px;
}

td {
    border: 1px solid #ddd;
    padding: 7px;
    font-size: 9px;
}

.centro {
    text-align: center;
}

.derecha {
    text-align: right;
}

.totales {
    width: 45%;
    margin-left: auto;
    margin-top: 20px;
}

.fila {
    padding: 6px 0;
    border-bottom: 1px solid #ddd;
}

.fila span {
    display: inline-block;
    width: 55%;
}

.fila strong {
    display: inline-block;
    width: 40%;
    text-align: right;
}

.total {
    border-top: 2px solid #222;
    border-bottom: 2px solid #222;
    font-size: 13px;
    padding: 9px 0;
}

.final {
    margin-top: 30px;
    text-align: center;
    color: #777;
    font-size: 9px;
}

</style>

</head>

<body>


<div class="cabecera">

    <div class="emisor">

        <div class="subtexto">
            EMISOR
        </div>

        <h2>
            ' .
            h($emisorNombreMostrar) .
            '
        </h2>
';


if ($emisorNif !== '') {

    $html .= '
        <p>
            <strong>NIF:</strong>
            ' .
            h($emisorNif) .
            '
        </p>';

}


if ($emisorDireccion !== '') {

    $html .= '
        <p>
            ' .
            h($emisorDireccion) .
            '
        </p>';

}


if ($emisorLocalidad !== '') {

    $html .= '
        <p>
            ' .
            h($emisorLocalidad) .
            '
        </p>';

}


if ($emisorPais !== '') {

    $html .= '
        <p>
            ' .
            h($emisorPais) .
            '
        </p>';

}


if ($emisorEmail !== '') {

    $html .= '
        <p>
            <strong>Email:</strong>
            ' .
            h($emisorEmail) .
            '
        </p>';

}


if ($emisorTelefono !== '') {

    $html .= '
        <p>
            <strong>Teléfono:</strong>
            ' .
            h($emisorTelefono) .
            '
        </p>';

}


$html .= '

    </div>


    <div class="datos">

        <h1>
            FACTURA
        </h1>

        <p class="numero">
            Nº ' .
            h($numeroFactura) .
            '
        </p>

        <p>
            Fecha emisión:
            ' .
            h(
                fecha_es(
                    $fechaEmision
                )
            ) .
            '
        </p>
';


if (!empty($fechaVencimiento)) {

    $html .= '
        <p>
            Vencimiento:
            ' .
            h(
                fecha_es(
                    $fechaVencimiento
                )
            ) .
            '
        </p>';

}


$html .= '

        <p>
            ' .
            h(
                ucfirst(
                    $factura['estado']
                )
            ) .
            '
        </p>

    </div>


    <div class="clear"></div>

</div>


<div class="bloque">

    <h3>
        CLIENTE
    </h3>

    <p>
        <strong>
            ' .
            h($clienteNombre) .
            '
        </strong>
    </p>

    <p>
        <strong>NIF:</strong>
        ' .
        h($clienteNif) .
        '
    </p>
';


if ($clienteDireccion !== '') {

    $html .= '
    <p>
        ' .
        h($clienteDireccion) .
        '
    </p>';

}


if ($clienteLocalidad !== '') {

    $html .= '
    <p>
        ' .
        h($clienteLocalidad) .
        '
    </p>';

}


if (!empty($clientePais)) {

    $html .= '
    <p>
        ' .
        h($clientePais) .
        '
    </p>';

}


if (!empty($clienteEmail)) {

    $html .= '
    <p>
        <strong>Email:</strong>
        ' .
        h($clienteEmail) .
        '
    </p>';

}


if (!empty($clienteTelefono)) {

    $html .= '
    <p>
        <strong>Teléfono:</strong>
        ' .
        h($clienteTelefono) .
        '
    </p>';

}


$html .= '

</div>


<table>

<thead>

<tr>

<th>Descripción</th>

<th>Cant.</th>

<th>Precio</th>

<th>Desc.</th>

<th>IVA</th>

<th>Total</th>

</tr>

</thead>

<tbody>

' .
$lineasHtml .
'

</tbody>

</table>


<div class="totales">


    <div class="fila">

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


    <div class="fila">

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
';


if (
    (float) $totalIrpf > 0
) {

    $html .= '

    <div class="fila">

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

    </div>';

}


$html .= '

    <div class="fila total">

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


<div class="bloque">

    <h3>
        Forma de pago
    </h3>

    <p>
        ' .
        h(
            $metodoPago !== ''
            ? $metodoPago
            : 'No especificada'
        ) .
        '
    </p>


    <h3 style="margin-top:15px;">
        Observaciones
    </h3>

    <p>
';


if (
    trim(
        (string) $observaciones
    ) !== ''
) {

    $html .= nl2br(
        h(
            $observaciones
        )
    );

} else {

    $html .=
        'Sin observaciones.';

}


$html .= '

    </p>

</div>


<div class="final">

    Generado mediante ViziuneAI

</div>


</body>

</html>
';


    /*
    |--------------------------------------------------------------------------
    | CONFIGURAR DOMPDF
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    | NO creamos ninguna carpeta dentro del proyecto.
    |
    | Utilizamos una carpeta temporal que PHP ya tenga disponible.
    |
    */

    $directoriosTemporales = [];


    /*
    |--------------------------------------------------------------------------
    | upload_tmp_dir
    |--------------------------------------------------------------------------
    */

    $uploadTmpDir =
        ini_get(
            'upload_tmp_dir'
        );


    if (
        is_string(
            $uploadTmpDir
        ) &&
        trim(
            $uploadTmpDir
        ) !== ''
    ) {

        $directoriosTemporales[] =
            rtrim(
                $uploadTmpDir,
                DIRECTORY_SEPARATOR
            );

    }


    /*
    |--------------------------------------------------------------------------
    | sys_get_temp_dir
    |--------------------------------------------------------------------------
    */

    $tempSistema =
        sys_get_temp_dir();


    if (
        is_string(
            $tempSistema
        ) &&
        trim(
            $tempSistema
        ) !== ''
    ) {

        $directoriosTemporales[] =
            rtrim(
                $tempSistema,
                DIRECTORY_SEPARATOR
            );

    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR DIRECTORIO VÁLIDO
    |--------------------------------------------------------------------------
    */

    $rutaTempDompdf = '';


    foreach (
        array_unique(
            $directoriosTemporales
        ) as $ruta
    ) {

        if (
            is_dir($ruta) &&
            is_writable($ruta)
        ) {

            $rutaTempDompdf =
                $ruta;

            break;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SI NO HAY DIRECTORIO TEMPORAL
    |--------------------------------------------------------------------------
    */

    if (
        $rutaTempDompdf === ''
    ) {

        throw new Exception(
            'PHP no dispone de una carpeta temporal válida y escribible para Dompdf.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | OPCIONES DOMPDF
    |--------------------------------------------------------------------------
    */

    $options =
        new Options();


    $options->set(
        'defaultFont',
        'DejaVu Sans'
    );


    $options->set(
        'isRemoteEnabled',
        false
    );


    $options->set(
        'isHtml5ParserEnabled',
        true
    );


    $options->set(
        'tempDir',
        $rutaTempDompdf
    );


    $options->set(
        'fontCache',
        $rutaTempDompdf
    );


    $options->set(
        'chroot',
        __DIR__
    );


    /*
    |--------------------------------------------------------------------------
    | CREAR DOMPDF
    |--------------------------------------------------------------------------
    */

    $dompdf =
        new Dompdf(
            $options
        );


    /*
    |--------------------------------------------------------------------------
    | CARGAR HTML
    |--------------------------------------------------------------------------
    */

    $dompdf->loadHtml(
        $html,
        'UTF-8'
    );


    /*
    |--------------------------------------------------------------------------
    | PAPEL A4
    |--------------------------------------------------------------------------
    */

    $dompdf->setPaper(
        'A4',
        'portrait'
    );


    /*
    |--------------------------------------------------------------------------
    | GENERAR
    |--------------------------------------------------------------------------
    */

    $dompdf->render();


    /*
    |--------------------------------------------------------------------------
    | LIMPIAR BUFFER
    |--------------------------------------------------------------------------
    */

    while (
        ob_get_level() > 0
    ) {

        ob_end_clean();

    }


    /*
    |--------------------------------------------------------------------------
    | NOMBRE DEL PDF
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


    /*
    |--------------------------------------------------------------------------
    | MOSTRAR EN EL NAVEGADOR
    |--------------------------------------------------------------------------
    */

    $dompdf->stream(
        $nombreArchivo,
        [
            'Attachment' => false
        ]
    );


    exit;


} catch (Throwable $e) {

    error_log(
        'ERROR generar_pdf.php: ' .
        $e->getMessage() .
        ' | FILE: ' .
        $e->getFile() .
        ' | LINE: ' .
        $e->getLine()
    );


    http_response_code(500);

    echo '<!DOCTYPE html>';

    echo '<html lang="es">';

    echo '<head>';

    echo '<meta charset="UTF-8">';

    echo '<title>Error generando PDF</title>';

    echo '</head>';

    echo '<body style="
        font-family:Arial,sans-serif;
        padding:40px;
        background:#f7f7f7;
        color:#222;
    ">';

    echo '<h1>
        Error al generar el PDF
    </h1>';

    echo '<p>';

    echo h(
        $e->getMessage()
    );

    echo '</p>';

    echo '</body>';

    echo '</html>';

    exit;
}
