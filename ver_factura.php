<?php

require_once 'config.php';


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

$stmtFactura = $pdo->prepare("
    SELECT
        f.id,
        f.serie,
        f.numero,
        f.fecha_emision,
        f.cliente_id,
        f.moneda,
        f.base_imponible,
        f.total_iva,
        f.total_irpf,
        f.total,
        f.metodo_pago,
        f.fecha_vencimiento,
        f.observaciones,
        f.estado,
        f.created_at,
        f.updated_at,

        c.nombre_razon_social,
        c.nif,
        c.direccion,
        c.codigo_postal,
        c.ciudad,
        c.provincia,
        c.pais,
        c.email,
        c.telefono

    FROM facturas f

    INNER JOIN clientes c
        ON c.id = f.cliente_id

    WHERE f.id = ?

    LIMIT 1
");

$stmtFactura->execute([
    $facturaId
]);

$factura = $stmtFactura->fetch();


if (!$factura) {

    http_response_code(404);

    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Factura no encontrada | ViziuneAI</title>
        <link rel="stylesheet" href="css/ver-factura.css">
    </head>

    <body>

        <div class="error-factura">

            <h1>Factura no encontrada</h1>

            <p>
                La factura solicitada no existe o ya no está disponible.
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
   OBTENER LINEAS
========================================== */

$stmtLineas = $pdo->prepare("
    SELECT
        id,
        descripcion,
        cantidad,
        precio_unitario,
        descuento,
        tipo_iva,
        base_linea,
        cuota_iva,
        total_linea,
        orden

    FROM factura_lineas

    WHERE factura_id = ?

    ORDER BY orden ASC, id ASC
");

$stmtLineas->execute([
    $facturaId
]);

$lineas = $stmtLineas->fetchAll();


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
                href="generar_pdf.php?id=<?php echo $facturaId; ?>"
                class="boton boton-principal"
            >
                Generar PDF
            </a>

        <?php endif; ?>


    </div>


</div>

</body>

</html>