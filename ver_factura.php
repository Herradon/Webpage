
<?php

session_start();

require_once 'config.php';


/* ==========================================
   COMPROBAR SESIÓN
========================================== */

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');
    exit;

}


$usuarioId =
    (int) $_SESSION['usuario_id'];


if ($usuarioId <= 0) {

    header('Location: login.php');
    exit;

}


/* ==========================================
   OBTENER ID
========================================== */

$facturaId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$facturaId || $facturaId <= 0) {

    header('Location: facturas.php');
    exit;

}


/* ==========================================
   OBTENER FACTURA
========================================== */

/*
|--------------------------------------------------------------------------
| SEGURIDAD DE ACCESO
|--------------------------------------------------------------------------
|
| usuarios.id
|      ↓
| clientes.usuario_id
|      ↓
| facturas.cliente_id
|
| El usuario solo puede consultar sus propias
| facturas.
|
| IMPORTANTE:
|
| Aquí solamente obtenemos información técnica
| de la factura.
|
| Los datos privados están en facturas_privadas
| y se descifran después.
|
*/

$stmtFactura = $pdo->prepare("
    SELECT
        f.id,
        f.serie,
        f.numero,
        f.fecha_emision,
        f.cliente_id,
        f.estado,
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

$stmtFactura->execute([
    $facturaId,
    $usuarioId
]);


$facturaTecnica =
    $stmtFactura->fetch();


/* ==========================================
   FACTURA NO ENCONTRADA / SIN PERMISO
========================================== */

if (!$facturaTecnica) {

    http_response_code(403);

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso no permitido | ViziuneAI</title>
        <link rel="stylesheet" href="css/ver-factura.css">
    </head>

    <body>

        <div class="error-factura">

            <h1>Acceso no permitido</h1>

            <p>
                No tienes permiso para consultar esta factura.
            </p>

            <a href="mi_cuenta.php" class="boton boton-principal">
                ← Volver a mi cuenta
            </a>

        </div>

    </body>
    </html>
    ';

    exit;
}


/* ==========================================
   COMPROBAR CLAVE DE CIFRADO
========================================== */

$claveCifrado =
    $VIZIUNEAI_FACTURAS_KEY ?? '';


if (!$claveCifrado) {

    http_response_code(500);

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error | ViziuneAI</title>
        <link rel="stylesheet" href="css/ver-factura.css">
    </head>

    <body>

        <div class="error-factura">

            <h1>Error de configuración</h1>

            <p>
                No está configurada la clave de cifrado de las facturas.
            </p>

            <a href="facturas.php" class="boton boton-principal">
                ← Volver a facturas
            </a>

        </div>

    </body>
    </html>
    ';

    exit;
}


if (strlen($claveCifrado) < 32) {

    http_response_code(500);

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error | ViziuneAI</title>
        <link rel="stylesheet" href="css/ver-factura.css">
    </head>

    <body>

        <div class="error-factura">

            <h1>Error de configuración</h1>

            <p>
                La clave de cifrado configurada no es válida.
            </p>

            <a href="facturas.php" class="boton boton-principal">
                ← Volver a facturas
            </a>

        </div>

    </body>
    </html>
    ';

    exit;
}


/* ==========================================
   GENERAR CLAVE BINARIA
========================================== */

$clave =
    hash(
        'sha256',
        $claveCifrado,
        true
    );


/* ==========================================
   OBTENER FACTURA PRIVADA CIFRADA
========================================== */

$stmtPrivada = $pdo->prepare("
    SELECT
        datos_cifrados

    FROM facturas_privadas

    WHERE factura_id = ?
      AND usuario_id = ?

    LIMIT 1
");

$stmtPrivada->execute([
    $facturaId,
    $usuarioId
]);


$facturaPrivada =
    $stmtPrivada->fetch();


/* ==========================================
   COMPROBAR DATOS PRIVADOS
========================================== */

if (!$facturaPrivada) {

    http_response_code(404);

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Factura no disponible | ViziuneAI</title>
        <link rel="stylesheet" href="css/ver-factura.css">
    </head>

    <body>

        <div class="error-factura">

            <h1>Factura no disponible</h1>

            <p>
                No se han encontrado los datos privados de esta factura.
            </p>

            <a href="facturas.php" class="boton boton-principal">
                ← Volver a facturas
            </a>

        </div>

    </body>
    </html>
    ';

    exit;
}


/* ==========================================
   DESCIFRAR FACTURA CON OPENSSL
========================================== */

try {

    /* ------------------------------------------
       COMPROBAR OPENSSL
    ------------------------------------------ */

    if (
        !function_exists('openssl_decrypt')
    ) {

        throw new Exception(
            'La extensión OpenSSL de PHP no está disponible.'
        );

    }


    /* ------------------------------------------
       DECODIFICAR CONTENIDO
    ------------------------------------------ */

    $contenidoPrivado =
        base64_decode(
            $facturaPrivada['datos_cifrados'],
            true
        );


    if (
        $contenidoPrivado === false ||
        strlen($contenidoPrivado) <= 16
    ) {

        throw new Exception(
            'Contenido cifrado no válido.'
        );

    }


    /* ------------------------------------------
       EXTRAER IV
    ------------------------------------------

       guardar_factura.php guarda:

       IV de 16 bytes
       +
       datos cifrados

    ------------------------------------------ */

    $iv =
        substr(
            $contenidoPrivado,
            0,
            16
        );


    /* ------------------------------------------
       EXTRAER DATOS CIFRADOS
    ------------------------------------------ */

    $datosCifrados =
        substr(
            $contenidoPrivado,
            16
        );


    if (
        $datosCifrados === false ||
        $datosCifrados === ''
    ) {

        throw new Exception(
            'No se encontraron datos cifrados.'
        );

    }


    /* ------------------------------------------
       GENERAR CLAVE AES-256
    ------------------------------------------ */

    $clave =
        hash(
            'sha256',
            $claveCifrado,
            true
        );


    if (
        strlen($clave) !== 32
    ) {

        throw new Exception(
            'La clave de cifrado no tiene una longitud válida.'
        );

    }


    /* ------------------------------------------
       DESCIFRAR AES-256-CBC
    ------------------------------------------ */

    $jsonFactura =
        openssl_decrypt(
            $datosCifrados,
            'AES-256-CBC',
            $clave,
            OPENSSL_RAW_DATA,
            $iv
        );


    if ($jsonFactura === false) {

        throw new Exception(
            'No se pudieron descifrar los datos de la factura.'
        );

    }


    /* ------------------------------------------
       CONVERTIR JSON
    ------------------------------------------ */

    $datosFactura =
        json_decode(
            $jsonFactura,
            true,
            512,
            JSON_THROW_ON_ERROR
        );


} catch (Throwable $e) {

    error_log(
        'Error descifrando factura ' .
        $facturaId .
        ': ' .
        $e->getMessage()
    );


    http_response_code(500);

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error | ViziuneAI</title>
        <link rel="stylesheet" href="css/ver-factura.css">
    </head>

    <body>

        <div class="error-factura">

            <h1>Error al abrir la factura</h1>

            <p>
                No se ha podido descifrar la información privada de esta factura.
            </p>

            <a href="facturas.php" class="boton boton-principal">
                ← Volver a facturas
            </a>

        </div>

    </body>
    </html>
    ';

    exit;
}


/* ==========================================
   COMPROBAR ESTRUCTURA DESCIFRADA
========================================== */

if (
    !isset($datosFactura['factura']) ||
    !isset($datosFactura['cliente']) ||
    !isset($datosFactura['lineas'])
) {

    http_response_code(500);

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error | ViziuneAI</title>
        <link rel="stylesheet" href="css/ver-factura.css">
    </head>

    <body>

        <div class="error-factura">

            <h1>Datos de factura incompletos</h1>

            <p>
                La información privada de esta factura no tiene un formato válido.
            </p>

            <a href="facturas.php" class="boton boton-principal">
                ← Volver a facturas
            </a>

        </div>

    </body>
    </html>
    ';

    exit;
}


/* ==========================================
   RECONSTRUIR FACTURA
========================================== */

/*
|--------------------------------------------------------------------------
| Información técnica procedente de facturas
|--------------------------------------------------------------------------
*/

$factura = [

    'id' =>
        $facturaTecnica['id'],

    'serie' =>
        $facturaTecnica['serie'],

    'numero' =>
        $facturaTecnica['numero'],

    'fecha_emision' =>
        $facturaTecnica['fecha_emision'],

    'cliente_id' =>
        $facturaTecnica['cliente_id'],

    'estado' =>
        $facturaTecnica['estado'],

    'created_at' =>
        $facturaTecnica['created_at'],

    'updated_at' =>
        $facturaTecnica['updated_at']

];


/*
|--------------------------------------------------------------------------
| Datos privados de la factura
|--------------------------------------------------------------------------
*/

$datosFacturaReal =
    $datosFactura['factura'];


/*
|--------------------------------------------------------------------------
| Si el dato privado contiene número, fecha,
| etc., usamos ese contenido.
|--------------------------------------------------------------------------
*/

if (
    array_key_exists(
        'numero',
        $datosFacturaReal
    ) &&
    $datosFacturaReal['numero'] !== null
) {

    $factura['numero'] =
        $datosFacturaReal['numero'];

}


if (
    !empty(
        $datosFacturaReal['fecha_emision']
    )
) {

    $factura['fecha_emision'] =
        $datosFacturaReal['fecha_emision'];

}


/*
|--------------------------------------------------------------------------
| Estado
|--------------------------------------------------------------------------
*/

if (
    !empty(
        $datosFacturaReal['estado']
    )
) {

    $factura['estado'] =
        $datosFacturaReal['estado'];

}


/*
|--------------------------------------------------------------------------
| DATOS PRIVADOS QUE UTILIZA LA VISTA
|--------------------------------------------------------------------------
*/

$factura['moneda'] =
    $datosFacturaReal['moneda'] ?? 'EUR';


$factura['base_imponible'] =
    $datosFacturaReal['base_imponible'] ?? 0;


$factura['total_iva'] =
    $datosFacturaReal['total_iva'] ?? 0;


$factura['total_irpf'] =
    $datosFacturaReal['total_irpf'] ?? 0;


$factura['total'] =
    $datosFacturaReal['total'] ?? 0;


$factura['metodo_pago'] =
    $datosFacturaReal['metodo_pago'] ?? null;


$factura['fecha_vencimiento'] =
    $datosFacturaReal['fecha_vencimiento'] ?? null;


$factura['observaciones'] =
    $datosFacturaReal['observaciones'] ?? null;


$factura['tipo_irpf'] =
    $datosFacturaReal['tipo_irpf'] ?? 0;


/* ==========================================
   DATOS DEL CLIENTE PRIVADOS
========================================== */

$clientePrivado =
    $datosFactura['cliente'];


$factura['nombre_razon_social'] =
    $clientePrivado['nombre_razon_social'] ?? '';


$factura['nif'] =
    $clientePrivado['nif'] ?? '';


$factura['direccion'] =
    $clientePrivado['direccion'] ?? '';


$factura['codigo_postal'] =
    $clientePrivado['codigo_postal'] ?? '';


$factura['ciudad'] =
    $clientePrivado['ciudad'] ?? '';


$factura['provincia'] =
    $clientePrivado['provincia'] ?? '';


$factura['pais'] =
    $clientePrivado['pais'] ?? '';


$factura['email'] =
    $clientePrivado['email'] ?? '';


$factura['telefono'] =
    $clientePrivado['telefono'] ?? '';


/* ==========================================
   LINEAS PRIVADAS
========================================== */

$lineas =
    is_array(
        $datosFactura['lineas']
    )
    ? $datosFactura['lineas']
    : [];


/* ==========================================
   DATOS DE LA EMPRESA
========================================== */

$stmtEmpresa = $pdo->query("
    SELECT
        razon_social,
        nif,
        direccion,
        codigo_postal,
        ciudad,
        provincia,
        pais,
        email,
        telefono,
        web,
        iban,
        logo

    FROM empresa_facturacion

    ORDER BY id ASC

    LIMIT 1
");

$empresa = $stmtEmpresa->fetch();


/* ==========================================
   NUMERO DE FACTURA
========================================== */

if (
    $factura['numero'] !== null &&
    $factura['numero'] !== ''
) {

    $numeroFactura =
        $factura['serie'] .
        '-' .
        $factura['numero'];

} else {

    $numeroFactura = 'BORRADOR';

}


/* ==========================================
   FECHAS
========================================== */

$fechaEmision = date(
    'd/m/Y',
    strtotime(
        $factura['fecha_emision']
    )
);


$fechaVencimiento = '';

if (
    !empty(
        $factura['fecha_vencimiento']
    )
) {

    $fechaVencimiento = date(
        'd/m/Y',
        strtotime(
            $factura['fecha_vencimiento']
        )
    );

}


/* ==========================================
   ESTADO
========================================== */

$estado = $factura['estado'];

$estadoTexto = 'Borrador';

if ($estado === 'emitida') {

    $estadoTexto = 'Emitida';

} elseif ($estado === 'anulada') {

    $estadoTexto = 'Anulada';

}


/* ==========================================
   MENSAJE GUARDADA
========================================== */

$guardada =
    isset($_GET['guardada']) &&
    $_GET['guardada'] === '1';


/* ==========================================
   FUNCIONES
========================================== */

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
        <?php echo h($numeroFactura); ?> |
        ViziuneAI
    </title>

    <link
        rel="stylesheet"
        href="css/ver-factura.css"
    >

</head>


<body>

<div class="contenedor-factura">


    <!-- ======================================
         BARRA SUPERIOR
    ====================================== -->

    <div class="barra-superior">

        <h1>
            Factura <?php echo h($numeroFactura); ?>
        </h1>


        <div class="navegacion">

            <a
                href="facturas.php"
                class="boton"
            >
                ← Facturas
            </a>


            <?php if ($estado === 'borrador'): ?>

                <a
                    href="crear_factura.php?id=<?php echo $facturaId; ?>"
                    class="boton"
                >
                    ✏️ Editar
                </a>

            <?php endif; ?>


            <?php if ($estado === 'emitida'): ?>

                <a
                    href="generar_pdf.php?id=<?php echo $facturaId; ?>"
                    class="boton boton-principal"
                >
                    📄 Generar PDF
                </a>

            <?php endif; ?>


            <button
                type="button"
                class="boton"
                onclick="window.print();"
            >
                🖨️ Imprimir
            </button>

        </div>

    </div>


    <!-- ======================================
         MENSAJE
    ====================================== -->

    <?php if ($guardada): ?>

        <div class="mensaje-exito">

            ✅ La factura se ha guardado correctamente
            como borrador.

        </div>

    <?php endif; ?>


    <!-- ======================================
         DOCUMENTO
    ====================================== -->

    <div class="documento">


        <!-- ==================================
             CABECERA
        ================================== -->

        <div class="documento-cabecera">


            <!-- EMPRESA -->

            <div class="empresa">

                <?php if (
                    !empty($empresa['logo'])
                ): ?>

                    <img
                        src="<?php echo h($empresa['logo']); ?>"
                        alt="Logo"
                        class="empresa-logo"
                    >

                <?php endif; ?>


                <?php if ($empresa): ?>

                    <h2>
                        <?php
                        echo h(
                            $empresa['razon_social']
                        );
                        ?>
                    </h2>


                    <?php if (
                        !empty($empresa['nif'])
                    ): ?>

                        <p>
                            <strong>NIF:</strong>

                            <?php
                            echo h(
                                $empresa['nif']
                            );
                            ?>
                        </p>

                    <?php endif; ?>


                    <?php if (
                        !empty($empresa['direccion'])
                    ): ?>

                        <p>
                            <?php
                            echo h(
                                $empresa['direccion']
                            );
                            ?>
                        </p>

                    <?php endif; ?>


                    <?php

                    $ubicacionEmpresa = [];

                    if (
                        !empty(
                            $empresa['codigo_postal']
                        )
                    ) {
                        $ubicacionEmpresa[] =
                            $empresa['codigo_postal'];
                    }

                    if (
                        !empty(
                            $empresa['ciudad']
                        )
                    ) {
                        $ubicacionEmpresa[] =
                            $empresa['ciudad'];
                    }

                    if (
                        !empty(
                            $empresa['provincia']
                        )
                    ) {
                        $ubicacionEmpresa[] =
                            $empresa['provincia'];
                    }

                    ?>


                    <?php if (
                        !empty($ubicacionEmpresa)
                    ): ?>

                        <p>
                            <?php
                            echo h(
                                implode(
                                    ', ',
                                    $ubicacionEmpresa
                                )
                            );
                            ?>
                        </p>

                    <?php endif; ?>


                    <?php if (
                        !empty($empresa['email'])
                    ): ?>

                        <p>
                            <?php
                            echo h(
                                $empresa['email']
                            );
                            ?>
                        </p>

                    <?php endif; ?>


                    <?php if (
                        !empty($empresa['telefono'])
                    ): ?>

                        <p>
                            <?php
                            echo h(
                                $empresa['telefono']
                            );
                            ?>
                        </p>

                    <?php endif; ?>


                <?php else: ?>

                    <h2>
                        Empresa de facturación
                    </h2>

                    <p>
                        Configura los datos de la empresa
                        en empresa_facturacion.
                    </p>

                <?php endif; ?>

            </div>


            <!-- DATOS FACTURA -->

            <div class="datos-factura">

                <h2>
                    FACTURA
                </h2>


                <p class="numero">

                    Nº:
                    <?php
                    echo h(
                        $numeroFactura
                    );
                    ?>

                </p>


                <p>

                    Fecha emisión:
                    <?php
                    echo h(
                        $fechaEmision
                    );
                    ?>

                </p>


                <?php if (
                    $fechaVencimiento !== ''
                ): ?>

                    <p>

                        Vencimiento:
                        <?php
                        echo h(
                            $fechaVencimiento
                        );
                        ?>

                    </p>

                <?php endif; ?>


                <p>

                    <span
                        class="estado estado-<?php echo h($estado); ?>"
                    >
                        <?php
                        echo h(
                            $estadoTexto
                        );
                        ?>
                    </span>

                </p>

            </div>

        </div>


        <!-- ==================================
             CLIENTE
        ================================== -->

        <div class="bloque-cliente">

            <h3>
                Cliente
            </h3>


            <div class="cliente">

                <p>

                    <strong>

                        <?php
                        echo h(
                            $factura[
                                'nombre_razon_social'
                            ]
                        );
                        ?>

                    </strong>

                </p>


                <p>

                    <strong>NIF:</strong>

                    <?php
                    echo h(
                        $factura['nif']
                    );
                    ?>

                </p>


                <?php if (
                    !empty(
                        $factura['direccion']
                    )
                ): ?>

                    <p>

                        <?php
                        echo h(
                            $factura['direccion']
                        );
                        ?>

                    </p>

                <?php endif; ?>


                <?php

                $ubicacionCliente = [];

                if (
                    !empty(
                        $factura[
                            'codigo_postal'
                        ]
                    )
                ) {
                    $ubicacionCliente[] =
                        $factura[
                            'codigo_postal'
                        ];
                }

                if (
                    !empty(
                        $factura['ciudad']
                    )
                ) {
                    $ubicacionCliente[] =
                        $factura['ciudad'];
                }

                if (
                    !empty(
                        $factura['provincia']
                    )
                ) {
                    $ubicacionCliente[] =
                        $factura['provincia'];
                }

                ?>


                <?php if (
                    !empty($ubicacionCliente)
                ): ?>

                    <p>

                        <?php
                        echo h(
                            implode(
                                ', ',
                                $ubicacionCliente
                            )
                        );
                        ?>

                    </p>

                <?php endif; ?>


                <?php if (
                    !empty(
                        $factura['email']
                    )
                ): ?>

                    <p>

                        <strong>Email:</strong>

                        <?php
                        echo h(
                            $factura['email']
                        );
                        ?>

                    </p>

                <?php endif; ?>


                <?php if (
                    !empty(
                        $factura['telefono']
                    )
                ): ?>

                    <p>

                        <strong>Teléfono:</strong>

                        <?php
                        echo h(
                            $factura['telefono']
                        );
                        ?>

                    </p>

                <?php endif; ?>

            </div>

        </div>


        <!-- ==================================
             LINEAS
        ================================== -->

        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>

                        <th>
                            Descripción
                        </th>

                        <th class="texto-centro">
                            Cantidad
                        </th>

                        <th class="texto-derecha">
                            Precio
                        </th>

                        <th class="texto-derecha">
                            Descuento
                        </th>

                        <th class="texto-derecha">
                            IVA
                        </th>

                        <th class="texto-derecha">
                            Total
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach (
                    $lineas as $linea
                ): ?>

                    <tr>

                        <td>

                            <div class="descripcion">

                                <?php
                                echo h(
                                    $linea[
                                        'descripcion'
                                    ]
                                );
                                ?>

                            </div>

                        </td>


                        <td class="texto-centro">

                            <?php
                            echo number_format(
                                (float)
                                $linea['cantidad'],
                                3,
                                ',',
                                '.'
                            );
                            ?>

                        </td>


                        <td class="texto-derecha">

                            <?php
                            echo dinero(
                                $linea[
                                    'precio_unitario'
                                ]
                            );
                            ?>

                        </td>


                        <td class="texto-derecha">

                            <?php
                            echo number_format(
                                (float)
                                $linea['descuento'],
                                2,
                                ',',
                                '.'
                            );
                            ?>

                            %

                        </td>


                        <td class="texto-derecha">

                            <?php
                            echo number_format(
                                (float)
                                $linea['tipo_iva'],
                                2,
                                ',',
                                '.'
                            );
                            ?>

                            %

                            <div class="subtexto">

                                <?php
                                echo dinero(
                                    $linea[
                                        'cuota_iva'
                                    ]
                                );
                                ?>

                            </div>

                        </td>


                        <td class="texto-derecha">

                            <strong>

                                <?php
                                echo dinero(
                                    $linea[
                                        'total_linea'
                                    ]
                                );
                                ?>

                            </strong>

                        </td>

                    </tr>

                <?php endforeach; ?>


                <?php if (
                    count($lineas) === 0
                ): ?>

                    <tr>

                        <td
                            colspan="6"
                            class="sin-lineas"
                        >

                            No hay líneas en esta factura.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- ==================================
             TOTALES
        ================================== -->

        <div class="zona-totales">

            <div class="totales">


                <div class="fila-total">

                    <span>
                        Base imponible
                    </span>

                    <strong>

                        <?php
                        echo dinero(
                            $factura[
                                'base_imponible'
                            ]
                        );
                        ?>

                    </strong>

                </div>


                <div class="fila-total">

                    <span>
                        IVA
                    </span>

                    <strong>

                        <?php
                        echo dinero(
                            $factura[
                                'total_iva'
                            ]
                        );
                        ?>

                    </strong>

                </div>


                <?php if (
                    (float)
                    $factura['total_irpf'] > 0
                ): ?>

                    <div class="fila-total irpf">

                        <span>
                            IRPF
                        </span>

                        <strong>

                            −
                            <?php
                            echo dinero(
                                $factura[
                                    'total_irpf'
                                ]
                            );
                            ?>

                        </strong>

                    </div>

                <?php endif; ?>


                <div class="fila-total total-final">

                    <span>
                        TOTAL
                    </span>

                    <strong>

                        <?php
                        echo dinero(
                            $factura['total']
                        );
                        ?>

                    </strong>

                </div>

            </div>

        </div>


        <!-- ==================================
             INFORMACIÓN FINAL
        ================================== -->

        <div class="informacion-final">


            <div class="bloque-info">

                <h3>
                    Forma de pago
                </h3>


                <p>

                    <?php

                    if (
                        !empty(
                            $factura['metodo_pago']
                        )
                    ) {

                        echo h(
                            $factura[
                                'metodo_pago'
                            ]
                        );

                    } else {

                        echo 'No especificada';

                    }

                    ?>

                </p>


                <?php if (
                    !empty(
                        $empresa['iban']
                    )
                ): ?>

                    <p>

                        <strong>IBAN:</strong>

                        <?php
                        echo h(
                            $empresa['iban']
                        );
                        ?>

                    </p>

                <?php endif; ?>

            </div>


            <div class="bloque-info">

                <h3>
                    Observaciones
                </h3>


                <p>

                    <?php

                    if (
                        !empty(
                            $factura[
                                'observaciones'
                            ]
                        )
                    ) {

                        echo nl2br(
                            h(
                                $factura[
                                    'observaciones'
                                ]
                            )
                        );

                    } else {

                        echo 'Sin observaciones.';

                    }

                    ?>

                </p>

            </div>


        </div>


    </div>


    <!-- ======================================
         ACCIONES
    ====================================== -->

    <div class="acciones-factura">


        <a
            href="facturas.php"
            class="boton"
        >
            ← Volver
        </a>


        <?php if (
            $estado === 'borrador'
        ): ?>

            <a
                href="crear_factura.php?id=<?php echo $facturaId; ?>"
                class="boton"
            >
                Editar borrador
            </a>


            <a
                href="emitir_factura.php?id=<?php echo $facturaId; ?>"
                class="boton boton-principal"
            >
                Emitir factura
            </a>

        <?php endif; ?>


        <?php if (
            $estado === 'emitida'
        ): ?>

            <a
                href="generar_pdf.php?id=<?php echo (int) $factura['id']; ?>"
                class="boton boton-pdf"
                target="_blank"
            >
                Generar PDF
            </a>

        <?php endif; ?>


    </div>


</div>

</body>

</html>
