<?php

session_start();

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Error | Viziune</title>

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

        <h1>
            ❌ No se pudo guardar la factura
        </h1>

        <p>'
        . htmlspecialchars(
            $mensaje,
            ENT_QUOTES,
            'UTF-8'
        )
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
   COMPROBAR SESIÓN
========================================== */

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    volverConError(
        'Debes iniciar sesión para crear una factura.'
    );

}


$usuarioId =
    (int) $_SESSION['usuario_id'];


if ($usuarioId <= 0) {

    volverConError(
        'La sesión de usuario no es válida.'
    );

}


/* ==========================================
   COMPROBAR CLAVE DE CIFRADO
========================================== */

$claveCifrado =
    $VIZIUNEAI_FACTURAS_KEY ?? '';


if (!$claveCifrado) {

    volverConError(
        'No está configurada la clave de cifrado de las facturas.'
    );

}


if (strlen($claveCifrado) < 32) {

    volverConError(
        'La clave de cifrado configurada no es suficientemente segura.'
    );

}


/* ==========================================
   RECIBIR DATOS
========================================== */

$facturaId =
    (int) ($_POST['factura_id'] ?? 0);


$serie =
    trim($_POST['serie'] ?? '');


$fechaEmision =
    trim($_POST['fecha_emision'] ?? '');


$fechaVencimiento =
    trim($_POST['fecha_vencimiento'] ?? '');


$metodoPago =
    trim($_POST['metodo_pago'] ?? '');


$observaciones =
    trim($_POST['observaciones'] ?? '');


/*
|--------------------------------------------------------------------------
| IRPF ELIMINADO
|--------------------------------------------------------------------------
*/

$totalIrpf = 0.00;


/* ==========================================
   DATOS DEL CLIENTE REAL
========================================== */

$clienteNombre =
    trim(
        $_POST['cliente_nombre_razon_social'] ?? ''
    );


$clienteNif =
    trim(
        $_POST['cliente_nif'] ?? ''
    );


$clienteDireccion =
    trim(
        $_POST['cliente_direccion'] ?? ''
    );


$clienteCodigoPostal =
    trim(
        $_POST['cliente_codigo_postal'] ?? ''
    );


$clienteCiudad =
    trim(
        $_POST['cliente_ciudad'] ?? ''
    );


$clienteProvincia =
    trim(
        $_POST['cliente_provincia'] ?? ''
    );


$clientePais =
    trim(
        $_POST['cliente_pais'] ?? ''
    );


$clienteEmail =
    trim(
        $_POST['cliente_email'] ?? ''
    );


$clienteTelefono =
    trim(
        $_POST['cliente_telefono'] ?? ''
    );


/* ==========================================
   ARRAYS DE LÍNEAS
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

if ($clienteNombre === '') {

    volverConError(
        'El nombre o razón social del cliente es obligatorio.'
    );

}


if (mb_strlen($clienteNombre) > 255) {

    volverConError(
        'El nombre o razón social del cliente es demasiado largo.'
    );

}


if (mb_strlen($clienteNif) > 50) {

    volverConError(
        'El NIF/CIF del cliente es demasiado largo.'
    );

}


if (mb_strlen($clienteDireccion) > 255) {

    volverConError(
        'La dirección del cliente es demasiado larga.'
    );

}


if (mb_strlen($clienteCodigoPostal) > 20) {

    volverConError(
        'El código postal del cliente no es válido.'
    );

}


if (mb_strlen($clienteCiudad) > 100) {

    volverConError(
        'La ciudad del cliente es demasiado larga.'
    );

}


if (mb_strlen($clienteProvincia) > 100) {

    volverConError(
        'La provincia del cliente es demasiado larga.'
    );

}


if (mb_strlen($clientePais) > 100) {

    volverConError(
        'El país del cliente es demasiado largo.'
    );

}


if (mb_strlen($clienteEmail) > 255) {

    volverConError(
        'El email del cliente es demasiado largo.'
    );

}


if ($clienteEmail !== '') {

    if (
        !filter_var(
            $clienteEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        volverConError(
            'El email del cliente no es válido.'
        );

    }

}


if (mb_strlen($clienteTelefono) > 50) {

    volverConError(
        'El teléfono del cliente es demasiado largo.'
    );

}


/* ==========================================
   OBTENER PERFIL DEL USUARIO
========================================== */

$stmtEmisor =
    $pdo->prepare("
        SELECT
            id,
            usuario_id,
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
            web,
            sector_actividad

        FROM clientes

        WHERE usuario_id = ?
          AND activo = 1

        LIMIT 1
    ");


$stmtEmisor->execute([
    $usuarioId
]);


$emisor =
    $stmtEmisor->fetch(
        PDO::FETCH_ASSOC
    );


if (!$emisor) {

    volverConError(
        'No se ha encontrado tu perfil de datos. Completa primero tus datos en "Mi cuenta".'
    );

}


/* ==========================================
   DATOS MÍNIMOS DEL EMISOR
========================================== */

$emisorNombreRazonSocial =
    trim(
        (string) (
            $emisor['nombre_razon_social'] ?? ''
        )
    );


$emisorNif =
    trim(
        (string) (
            $emisor['nif'] ?? ''
        )
    );


$emisorPais =
    trim(
        (string) (
            $emisor['pais'] ?? ''
        )
    );


if ($emisorNombreRazonSocial === '') {

    volverConError(
        'Completa tu nombre o razón social en "Mi cuenta" antes de crear una factura.'
    );

}


if ($emisorNif === '') {

    volverConError(
        'Completa tu NIF/CIF en "Mi cuenta" antes de crear una factura.'
    );

}


if ($emisorPais === '') {

    volverConError(
        'Completa tu país en "Mi cuenta" antes de crear una factura.'
    );

}


/* ==========================================
   CLIENTE TÉCNICO
========================================== */

$clienteTecnicoId =
    (int) ($emisor['id'] ?? 0);


if ($clienteTecnicoId <= 0) {

    volverConError(
        'No se pudo establecer la relación técnica con tu cuenta.'
    );

}


/* ==========================================
   VALIDAR EDICIÓN
========================================== */

if ($facturaId > 0) {

    $stmtFacturaEditar =
        $pdo->prepare("
            SELECT
                f.id,
                f.cliente_id,
                f.estado

            FROM facturas f

            INNER JOIN clientes c
                ON c.id = f.cliente_id

            WHERE f.id = ?
              AND c.usuario_id = ?
              AND c.activo = 1

            LIMIT 1
        ");


    $stmtFacturaEditar->execute([
        $facturaId,
        $usuarioId
    ]);


    $facturaEditar =
        $stmtFacturaEditar->fetch();


    if (!$facturaEditar) {

        volverConError(
            'No tienes permiso para modificar esta factura.'
        );

    }


    if (
        $facturaEditar['estado'] !== 'borrador'
    ) {

        volverConError(
            'Esta factura ya ha sido emitida y no puede modificarse.'
        );

    }


    if (
        (int) $facturaEditar['cliente_id']
        !==
        $clienteTecnicoId
    ) {

        volverConError(
            'No tienes permiso para modificar esta factura.'
        );

    }

}


/* ==========================================
   VALIDAR LÍNEAS
========================================== */

if (
    !is_array($descripciones) ||
    count($descripciones) === 0
) {

    volverConError(
        'La factura debe contener al menos una línea.'
    );

}


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
   TIPOS IVA
========================================== */

$tiposIvaPermitidos = [
    0,
    4,
    10,
    21
];


/* ==========================================
   PROCESAR LÍNEAS
========================================== */

$lineasProcesadas = [];

$baseImponible = 0.00;

$totalIva = 0.00;


foreach (
    $descripciones as $indice => $descripcion
) {

    $descripcion =
        trim(
            (string) $descripcion
        );


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


    if ($cantidad <= 0) {

        volverConError(
            'La cantidad de una línea debe ser mayor que cero.'
        );

    }


    if ($precioUnitario < 0) {

        volverConError(
            'El precio unitario no puede ser negativo.'
        );

    }


    if (
        $descuento < 0 ||
        $descuento > 100
    ) {

        volverConError(
            'El descuento debe estar entre 0% y 100%.'
        );

    }


    if (
        !in_array(
            (int) $tipoIva,
            $tiposIvaPermitidos,
            true
        )
    ) {

        volverConError(
            'Uno de los tipos de IVA seleccionados no es válido.'
        );

    }


    $bruto =
        $cantidad *
        $precioUnitario;


    $importeDescuento =
        $bruto *
        $descuento /
        100;


    $baseLinea =
        $bruto -
        $importeDescuento;


    $cuotaIva =
        $baseLinea *
        $tipoIva /
        100;


    $totalLinea =
        $baseLinea +
        $cuotaIva;


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


    $baseImponible +=
        $baseLinea;


    $totalIva +=
        $cuotaIva;


    $lineasProcesadas[] = [

        'descripcion' =>
            $descripcion,

        'cantidad' =>
            round(
                $cantidad,
                3
            ),

        'precio_unitario' =>
            round(
                $precioUnitario,
                4
            ),

        'descuento' =>
            round(
                $descuento,
                2
            ),

        'tipo_iva' =>
            round(
                $tipoIva,
                2
            ),

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
   TOTALES
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


$totalIrpf = 0.00;


$totalFactura =
    round(
        $baseImponible +
        $totalIva -
        $totalIrpf,
        2
    );


if ($totalFactura < 0) {

    volverConError(
        'El total de la factura no puede ser negativo.'
    );

}


/* ==========================================
   DATOS HISTÓRICOS DEL EMISOR
========================================== */

$datosEmisor = [

    'tipo_persona' =>
        $emisor['tipo_persona'] ?? null,

    'nombre' =>
        $emisor['nombre'] ?? null,

    'apellidos' =>
        $emisor['apellidos'] ?? null,

    'nombre_razon_social' =>
        $emisorNombreRazonSocial,

    'nombre_comercial' =>
        $emisor['nombre_comercial'] ?? null,

    'nif' =>
        $emisorNif,

    'direccion' =>
        $emisor['direccion'] ?? null,

    'codigo_postal' =>
        $emisor['codigo_postal'] ?? null,

    'ciudad' =>
        $emisor['ciudad'] ?? null,

    'provincia' =>
        $emisor['provincia'] ?? null,

    'pais' =>
        $emisorPais,

    'email' =>
        $emisor['email'] ?? null,

    'telefono' =>
        $emisor['telefono'] ?? null,

    'web' =>
        $emisor['web'] ?? null,

    'sector_actividad' =>
        $emisor['sector_actividad'] ?? null

];


/* ==========================================
   ESTRUCTURA PRIVADA
========================================== */

$datosFactura = [

    'factura' => [

        'serie' =>
            $serie,

        'numero' =>
            null,

        'fecha_emision' =>
            $fechaEmision,

        'fecha_vencimiento' =>
            $fechaVencimiento,

        'moneda' =>
            'EUR',

        'base_imponible' =>
            $baseImponible,

        'total_iva' =>
            $totalIva,

        'tipo_irpf' =>
            0,

        'total_irpf' =>
            0.00,

        'total' =>
            $totalFactura,

        'metodo_pago' =>
            $metodoPago !== ''
                ? $metodoPago
                : null,

        'observaciones' =>
            $observaciones !== ''
                ? $observaciones
                : null,

        'estado' =>
            'borrador'

    ],

    'emisor' =>
        $datosEmisor,

    'cliente' => [

        'nombre_razon_social' =>
            $clienteNombre,

        'nif' =>
            $clienteNif,

        'direccion' =>
            $clienteDireccion,

        'codigo_postal' =>
            $clienteCodigoPostal,

        'ciudad' =>
            $clienteCiudad,

        'provincia' =>
            $clienteProvincia,

        'pais' =>
            $clientePais,

        'email' =>
            $clienteEmail,

        'telefono' =>
            $clienteTelefono

    ],

    'lineas' =>
        $lineasProcesadas

];


/* ==========================================
   JSON
========================================== */

try {

    $jsonFactura =
        json_encode(
            $datosFactura,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_THROW_ON_ERROR
        );

} catch (Throwable $e) {

    error_log(
        'Error convirtiendo factura a JSON: ' .
        $e->getMessage()
    );

    volverConError(
        'No se pudieron preparar los datos de la factura.'
    );

}


/* ==========================================
   CIFRADO AES-256-CBC
========================================== */

try {

    $clave =
        hash(
            'sha256',
            $claveCifrado,
            true
        );


    $iv =
        random_bytes(16);


    $datosCifrados =
        openssl_encrypt(
            $jsonFactura,
            'AES-256-CBC',
            $clave,
            OPENSSL_RAW_DATA,
            $iv
        );


    if ($datosCifrados === false) {

        throw new Exception(
            'No se pudieron cifrar los datos.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | IMPORTANTE
    |--------------------------------------------------------------------------
    |
    | ver_factura.php espera:
    |
    | primeros 16 bytes = IV
    | resto = datos cifrados
    |
    */

    $contenidoPrivado =
        base64_encode(
            $iv .
            $datosCifrados
        );


} catch (Throwable $e) {

    error_log(
        'Error cifrando factura: ' .
        $e->getMessage()
    );

    volverConError(
        'No se pudieron cifrar los datos privados de la factura.'
    );

}


/* ==========================================
   TRANSACCIÓN
========================================== */

try {

    $pdo->beginTransaction();


    /* ==========================================
       CREAR FACTURA
    ========================================== */

    if ($facturaId <= 0) {

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
                    estado,
                    created_at,
                    updated_at
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
                    'borrador',
                    NOW(),
                    NOW()
                )
            ");


        $stmtFactura->execute([

            $serie,

            $fechaEmision,

            $clienteTecnicoId,

            $baseImponible,

            $totalIva,

            $totalIrpf,

            $totalFactura,

            $metodoPago !== ''
                ? $metodoPago
                : null,

            $fechaVencimiento,

            $observaciones !== ''
                ? $observaciones
                : null

        ]);


        $facturaId =
            (int) $pdo->lastInsertId();


        if ($facturaId <= 0) {

            throw new Exception(
                'No se pudo obtener el ID de la factura.'
            );

        }


    } else {

        /* ==========================================
           ACTUALIZAR BORRADOR
        ========================================== */

        $stmtFactura =
            $pdo->prepare("
                UPDATE facturas

                SET
                    serie = ?,
                    numero = NULL,
                    fecha_emision = ?,
                    cliente_id = ?,
                    moneda = 'EUR',
                    base_imponible = ?,
                    total_iva = ?,
                    total_irpf = ?,
                    total = ?,
                    metodo_pago = ?,
                    fecha_vencimiento = ?,
                    observaciones = ?,
                    updated_at = NOW()

                WHERE id = ?
                  AND estado = 'borrador'
            ");


        $stmtFactura->execute([

            $serie,

            $fechaEmision,

            $clienteTecnicoId,

            $baseImponible,

            $totalIva,

            $totalIrpf,

            $totalFactura,

            $metodoPago !== ''
                ? $metodoPago
                : null,

            $fechaVencimiento,

            $observaciones !== ''
                ? $observaciones
                : null,

            $facturaId

        ]);


        if (
            $stmtFactura->rowCount() === 0
        ) {

            throw new Exception(
                'No se pudo actualizar la factura.'
            );

        }

    }


    /* ==========================================
       GUARDAR DATOS PRIVADOS
    ========================================== */

    /*
    |--------------------------------------------------------------------------
    | Para evitar duplicados al editar una factura,
    | primero eliminamos su registro privado anterior.
    |--------------------------------------------------------------------------
    */

    $stmtEliminarPrivada =
        $pdo->prepare("
            DELETE FROM facturas_privadas

            WHERE factura_id = ?
              AND usuario_id = ?
        ");


    $stmtEliminarPrivada->execute([

        $facturaId,

        $usuarioId

    ]);


    /*
    |--------------------------------------------------------------------------
    | Insertar nueva versión cifrada
    |--------------------------------------------------------------------------
    */

    $stmtPrivada =
        $pdo->prepare("
            INSERT INTO facturas_privadas (
                factura_id,
                usuario_id,
                datos_cifrados
            )

            VALUES (
                ?,
                ?,
                ?
            )
        ");


    $stmtPrivada->execute([

        $facturaId,

        $usuarioId,

        $contenidoPrivado

    ]);


    /* ==========================================
       CONFIRMAR
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

    if (
        $pdo->inTransaction()
    ) {

        $pdo->rollBack();

    }


    error_log(
        'ERROR GUARDANDO FACTURA: ' .
        $e->getMessage() .
        ' | FILE: ' .
        $e->getFile() .
        ' | LINE: ' .
        $e->getLine()
    );


    volverConError(
        'No se pudo guardar la factura. ' .
        $e->getMessage()
    );

}