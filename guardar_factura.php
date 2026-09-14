<?php

require_once 'config.php';


/* ==========================================
   SOLO POST
========================================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: facturas.php');
    exit;

}


/* ==========================================
   FUNCIONES
========================================== */

function volverConError($mensaje)
{
    http_response_code(400);

    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error | ViziuneAI</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #07111f;
            color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
        }

        .error {
            width: 100%;
            max-width: 600px;
            padding: 30px;
            background: #0d1a29;
            border: 1px solid #5b2930;
            border-radius: 12px;
            text-align: center;
        }

        h1 {
            margin-top: 0;
            color: #ff7c89;
        }

        p {
            color: #b7c5d3;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 20px;
            border-radius: 8px;
            background: #00cfe0;
            color: #031017;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="error">

        <h1>❌ No se pudo guardar la factura</h1>

        <p>' .
        htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')
        . '</p>

        <a href="crear_factura.php">
            ← Volver a crear factura
        </a>

    </div>

</body>
</html>';

    exit;
}


/* ==========================================
   RECIBIR DATOS
========================================== */

$serie = trim($_POST['serie'] ?? '');

$fechaEmision =
    trim($_POST['fecha_emision'] ?? '');

$fechaVencimiento =
    trim($_POST['fecha_vencimiento'] ?? '');

$clienteId =
    (int) ($_POST['cliente_id'] ?? 0);

$metodoPago =
    trim($_POST['metodo_pago'] ?? '');

$observaciones =
    trim($_POST['observaciones'] ?? '');

$tipoIrpf =
    (float) ($_POST['tipo_irpf'] ?? 0);


/* ==========================================
   ARRAYS DE LINEAS
========================================== */

$descripciones =
    $_POST['descripcion'] ?? [];

$cantidades =
    $_POST['cantidad'] ?? [];

$precios =
    $_POST['precio_unitario'] ?? [];

$descuentos =
    $_POST['descuento'] ?? [];

$tiposIva =
    $_POST['tipo_iva'] ?? [];


/* ==========================================
   VALIDACIONES BÁSICAS
========================================== */

if ($serie === '') {

    volverConError(
        'La serie de la factura es obligatoria.'
    );

}


if (mb_strlen($serie) > 20) {

    volverConError(
        'La serie de la factura no puede superar los 20 caracteres.'
    );

}


if ($fechaEmision === '') {

    volverConError(
        'La fecha de emisión es obligatoria.'
    );

}


/* ==========================================
   VALIDAR FECHA DE EMISIÓN
========================================== */

$fechaEmisionObj =
    DateTime::createFromFormat(
        'Y-m-d',
        $fechaEmision
    );


if (
    !$fechaEmisionObj ||
    $fechaEmisionObj->format('Y-m-d') !== $fechaEmision
) {

    volverConError(
        'La fecha de emisión no es válida.'
    );

}


/* ==========================================
   VALIDAR FECHA DE VENCIMIENTO
========================================== */

if ($fechaVencimiento !== '') {

    $fechaVencimientoObj =
        DateTime::createFromFormat(
            'Y-m-d',
            $fechaVencimiento
        );


    if (
        !$fechaVencimientoObj ||
        $fechaVencimientoObj->format('Y-m-d') !== $fechaVencimiento
    ) {

        volverConError(
            'La fecha de vencimiento no es válida.'
        );

    }


    if (
        $fechaVencimiento <
        $fechaEmision
    ) {

        volverConError(
            'La fecha de vencimiento no puede ser anterior a la fecha de emisión.'
        );

    }

} else {

    $fechaVencimiento = null;

}


/* ==========================================
   VALIDAR CLIENTE
========================================== */

if ($clienteId <= 0) {

    volverConError(
        'Debes seleccionar un cliente.'
    );

}


$stmtCliente =
    $pdo->prepare("
        SELECT
            id,
            nombre_razon_social,
            nif
        FROM clientes
        WHERE id = ?
          AND activo = 1
        LIMIT 1
    ");

$stmtCliente->execute([
    $clienteId
]);

$cliente =
    $stmtCliente->fetch();


if (!$cliente) {

    volverConError(
        'El cliente seleccionado no existe o está inactivo.'
    );

}


/* ==========================================
   VALIDAR IRPF
========================================== */

$porcentajesIrpfPermitidos = [
    0,
    7,
    15
];


if (
    !in_array(
        $tipoIrpf,
        $porcentajesIrpfPermitidos,
        true
    )
) {

    volverConError(
        'El porcentaje de IRPF seleccionado no es válido.'
    );

}


/* ==========================================
   VALIDAR LINEAS
========================================== */

if (
    !is_array($descripciones) ||
    count($descripciones) === 0
) {

    volverConError(
        'La factura debe contener al menos una línea.'
    );

}


/* ==========================================
   COMPROBAR MISMA CANTIDAD DE ARRAYS
========================================== */

$totalDescripciones =
    count($descripciones);

$totalCantidades =
    count($cantidades);

$totalPrecios =
    count($precios);

$totalDescuentos =
    count($descuentos);

$totalTiposIva =
    count($tiposIva);


if (
    $totalDescripciones !== $totalCantidades ||
    $totalDescripciones !== $totalPrecios ||
    $totalDescripciones !== $totalDescuentos ||
    $totalDescripciones !== $totalTiposIva
) {

    volverConError(
        'Los datos de las líneas de la factura no son coherentes.'
    );

}


/* ==========================================
   TIPOS DE IVA PERMITIDOS
========================================== */

$tiposIvaPermitidos = [
    0,
    4,
    10,
    21
];


/* ==========================================
   PROCESAR LINEAS
========================================== */

$lineasProcesadas = [];

$baseImponible = 0.00;

$totalIva = 0.00;


foreach (
    $descripciones as $indice => $descripcion
) {

    $descripcion =
        trim((string) $descripcion);


    if ($descripcion === '') {

        volverConError(
            'Todas las líneas deben tener una descripción.'
        );

    }


    if (
        mb_strlen($descripcion) > 500
    ) {

        volverConError(
            'Una descripción supera el máximo permitido de 500 caracteres.'
        );

    }


    $cantidad =
        (float) (
            $cantidades[$indice] ?? 0
        );


    $precioUnitario =
        (float) (
            $precios[$indice] ?? 0
        );


    $descuento =
        (float) (
            $descuentos[$indice] ?? 0
        );


    $tipoIva =
        (float) (
            $tiposIva[$indice] ?? 0
        );


    /* ------------------------------------------
       VALIDAR CANTIDAD
    ------------------------------------------ */

    if ($cantidad <= 0) {

        volverConError(
            'La cantidad de una línea debe ser mayor que cero.'
        );

    }


    /* ------------------------------------------
       VALIDAR PRECIO
    ------------------------------------------ */

    if ($precioUnitario < 0) {

        volverConError(
            'El precio unitario no puede ser negativo.'
        );

    }


    /* ------------------------------------------
       VALIDAR DESCUENTO
    ------------------------------------------ */

    if (
        $descuento < 0 ||
        $descuento > 100
    ) {

        volverConError(
            'El descuento debe estar entre 0% y 100%.'
        );

    }


    /* ------------------------------------------
       VALIDAR IVA
    ------------------------------------------ */

    if (
        !in_array(
            $tipoIva,
            $tiposIvaPermitidos,
            true
        )
    ) {

        volverConError(
            'Uno de los tipos de IVA seleccionados no es válido.'
        );

    }


    /* ------------------------------------------
       CALCULAR BRUTO
    ------------------------------------------ */

    $bruto =
        $cantidad * $precioUnitario;


    /* ------------------------------------------
       CALCULAR DESCUENTO
    ------------------------------------------ */

    $importeDescuento =
        $bruto * $descuento / 100;


    /* ------------------------------------------
       BASE DE LA LINEA
    ------------------------------------------ */

    $baseLinea =
        $bruto - $importeDescuento;


    /* ------------------------------------------
       IVA DE LA LINEA
    ------------------------------------------ */

    $cuotaIva =
        $baseLinea * $tipoIva / 100;


    /* ------------------------------------------
       TOTAL DE LA LINEA
    ------------------------------------------ */

    $totalLinea =
        $baseLinea + $cuotaIva;


    /* ------------------------------------------
       REDONDEO MONETARIO
    ------------------------------------------ */

    $baseLinea =
        round(
            $baseLinea,
            2
        );

    $cuotaIva =
        round(
            $cuotaIva,
            2
        );

    $totalLinea =
        round(
            $totalLinea,
            2
        );


    /* ------------------------------------------
       ACUMULAR TOTALES
    ------------------------------------------ */

    $baseImponible +=
        $baseLinea;

    $totalIva +=
        $cuotaIva;


    /* ------------------------------------------
       GUARDAR LINEA PROCESADA
    ------------------------------------------ */

    $lineasProcesadas[] = [

        'descripcion' =>
            $descripcion,

        'cantidad' =>
            round($cantidad, 3),

        'precio_unitario' =>
            round($precioUnitario, 4),

        'descuento' =>
            round($descuento, 2),

        'tipo_iva' =>
            round($tipoIva, 2),

        'base_linea' =>
            $baseLinea,

        'cuota_iva' =>
            $cuotaIva,

        'total_linea' =>
            $totalLinea,

        'orden' =>
            $indice + 1

    ];

}


/* ==========================================
   REDONDEAR TOTALES
========================================== */

$baseImponible =
    round(
        $baseImponible,
        2
    );

$totalIva =
    round(
        $totalIva,
        2
    );


/* ==========================================
   CALCULAR IRPF
========================================== */

$totalIrpf =
    round(
        $baseImponible *
        $tipoIrpf /
        100,
        2
    );


/* ==========================================
   TOTAL FINAL
========================================== */

$totalFactura =
    round(
        $baseImponible +
        $totalIva -
        $totalIrpf,
        2
    );


/* ==========================================
   COMPROBAR TOTAL
========================================== */

if ($totalFactura < 0) {

    volverConError(
        'El total de la factura no puede ser negativo.'
    );

}


/* ==========================================
   TRANSACCIÓN
========================================== */

try {

    $pdo->beginTransaction();


    /* ==========================================
       CREAR FACTURA COMO BORRADOR
    ========================================== */

    /*
       IMPORTANTE:

       numero = NULL porque todavía no
       se está emitiendo la factura.

       El número definitivo se asignará
       posteriormente en el proceso de emisión.
    */

    $stmtFactura =
        $pdo->prepare("
            INSERT INTO facturas (
                serie,
                numero,
                fecha_emision,
                cliente_id,
                moneda,
                base_imponible,
                total_iva,
                total_irpf,
                total,
                metodo_pago,
                fecha_vencimiento,
                observaciones,
                estado
            )
            VALUES (
                ?,
                NULL,
                ?,
                ?,
                'EUR',
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                'borrador'
            )
        ");


    $stmtFactura->execute([

        $serie,

        $fechaEmision,

        $clienteId,

        number_format(
            $baseImponible,
            2,
            '.',
            ''
        ),

        number_format(
            $totalIva,
            2,
            '.',
            ''
        ),

        number_format(
            $totalIrpf,
            2,
            '.',
            ''
        ),

        number_format(
            $totalFactura,
            2,
            '.',
            ''
        ),

        $metodoPago !== ''
            ? $metodoPago
            : null,

        $fechaVencimiento,

        $observaciones !== ''
            ? $observaciones
            : null

    ]);


    /* ==========================================
       ID DE LA FACTURA
    ========================================== */

    $facturaId =
        (int) $pdo->lastInsertId();


    if ($facturaId <= 0) {

        throw new Exception(
            'No se pudo obtener el ID de la factura.'
        );

    }


    /* ==========================================
       INSERTAR LINEAS
    ========================================== */

    $stmtLinea =
        $pdo->prepare("
            INSERT INTO factura_lineas (
                factura_id,
                descripcion,
                cantidad,
                precio_unitario,
                descuento,
                tipo_iva,
                base_linea,
                cuota_iva,
                total_linea,
                orden
            )
            VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");


    foreach (
        $lineasProcesadas as $linea
    ) {

        $stmtLinea->execute([

            $facturaId,

            $linea['descripcion'],

            number_format(
                $linea['cantidad'],
                3,
                '.',
                ''
            ),

            number_format(
                $linea['precio_unitario'],
                4,
                '.',
                ''
            ),

            number_format(
                $linea['descuento'],
                2,
                '.',
                ''
            ),

            number_format(
                $linea['tipo_iva'],
                2,
                '.',
                ''
            ),

            number_format(
                $linea['base_linea'],
                2,
                '.',
                ''
            ),

            number_format(
                $linea['cuota_iva'],
                2,
                '.',
                ''
            ),

            number_format(
                $linea['total_linea'],
                2,
                '.',
                ''
            ),

            $linea['orden']

        ]);

    }


    /* ==========================================
       CONFIRMAR TRANSACCIÓN
    ========================================== */

    $pdo->commit();


    /* ==========================================
       REDIRECCIÓN
    ========================================== */

    header(
        'Location: ver_factura.php?id=' .
        $facturaId .
        '&guardada=1'
    );

    exit;


} catch (Throwable $e) {

    /* ==========================================
       DESHACER SI HAY ERROR
    ========================================== */

    if (
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();

    }


    error_log(
        'Error guardando factura: ' .
        $e->getMessage()
    );


    volverConError(
        'Se produjo un error al guardar la factura. Revisa la configuración de la base de datos.'
    );

}