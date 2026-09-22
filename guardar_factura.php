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

        <p>'
        . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')
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
|
| Ya no se recibe ni se calcula ningún porcentaje de IRPF.
|
| La columna total_irpf de la tabla facturas se mantiene por compatibilidad
| con la estructura actual de la base de datos, pero siempre se guarda 0.
|
*/

$totalIrpf = 0.00;


/* ==========================================
   DATOS DEL CLIENTE REAL DE LA FACTURA
========================================== */

$clienteNombre =
    trim($_POST['cliente_nombre_razon_social'] ?? '');

$clienteNif =
    trim($_POST['cliente_nif'] ?? '');

$clienteDireccion =
    trim($_POST['cliente_direccion'] ?? '');

$clienteCodigoPostal =
    trim($_POST['cliente_codigo_postal'] ?? '');

$clienteCiudad =
    trim($_POST['cliente_ciudad'] ?? '');

$clienteProvincia =
    trim($_POST['cliente_provincia'] ?? '');

$clientePais =
    trim($_POST['cliente_pais'] ?? '');

$clienteEmail =
    trim($_POST['cliente_email'] ?? '');

$clienteTelefono =
    trim($_POST['cliente_telefono'] ?? '');


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
   VALIDAR CLIENTE REAL DE LA FACTURA
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

/*
|--------------------------------------------------------------------------
| IMPORTANTE
|--------------------------------------------------------------------------
|
| El registro de clientes asociado al usuario contiene los datos
| fiscales que el usuario ha rellenado desde "Mi cuenta".
|
| Esos datos serán el EMISOR de la factura.
|
*/

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
    $stmtEmisor->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Si no existe perfil fiscal
|--------------------------------------------------------------------------
*/

if (!$emisor) {

    volverConError(
        'No se ha encontrado tu perfil de datos. Completa primero tus datos en "Mi cuenta".'
    );

}


/*
|--------------------------------------------------------------------------
| Validar datos mínimos del emisor
|--------------------------------------------------------------------------
*/

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
   PERFIL TÉCNICO
========================================== */

/*
|--------------------------------------------------------------------------
| Este cliente sigue siendo el registro técnico que relaciona:
|
| usuario → facturas
|
| NO es el cliente receptor de la factura.
|--------------------------------------------------------------------------
*/

$clienteTecnicoId =
    (int) ($emisor['id'] ?? 0);


if ($clienteTecnicoId <= 0) {

    volverConError(
        'No se pudo establecer la relación técnica con tu cuenta.'
    );

}


/* ==========================================
   VALIDAR FACTURA EN MODO EDICIÓN
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


    if ($facturaEditar['estado'] !== 'borrador') {

        volverConError(
            'Esta factura ya ha sido emitida y no puede modificarse.'
        );

    }


    if (
        (int) $facturaEditar['cliente_id'] !== $clienteTecnicoId
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
   TIPOS DE IVA PERMITIDOS
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
        $cantidad * $precioUnitario;


    $importeDescuento =
        $bruto * $descuento / 100;


    $baseLinea =
        $bruto - $importeDescuento;


    $cuotaIva =
        $baseLinea * $tipoIva / 100;


    $totalLinea =
        $baseLinea + $cuotaIva;


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
   IRPF
========================================== */

/*
|--------------------------------------------------------------------------
| IRPF ELIMINADO
|--------------------------------------------------------------------------
|
| Ya no se aplica ninguna retención.
|
*/

$totalIrpf = 0.00;


/* ==========================================
   TOTAL FINAL
========================================== */

$totalFactura =
    round(
        $baseImponible +
        $totalIva,
        2
    );


if ($totalFactura < 0) {

    volverConError(
        'El total de la factura no puede ser negativo.'
    );

}


/* ==========================================
   DATOS PRIVADOS DE LA FACTURA
========================================== */

/*
|--------------------------------------------------------------------------
| EMISOR
|--------------------------------------------------------------------------
|
| Se guarda una copia de los datos actuales del perfil.
|
| Esto permite que una factura antigua conserve sus datos originales
| aunque el usuario modifique posteriormente su perfil.
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| FACTURA PRIVADA
|--------------------------------------------------------------------------
*/

$datosFactura = [

    'factura' => [

        'serie' =>
            $serie,

        'numero' =>
            null,

        'fecha_emision' =>
            $fechaEmision,

        'moneda' =>
            'EUR',

        'fecha_vencimiento' =>
            $fechaVencimiento,

        'metodo_pago' =>
            $metodoPago !== ''
                ? $metodoPago
                : null,

        'observaciones' =>
            $observaciones !== ''
                ? $observaciones
                : null,

        /*
        |--------------------------------------------------------------------------
        | IRPF eliminado.
        |
        | Se mantiene a 0 únicamente por compatibilidad con datos antiguos
        | que pudieran esperar esta propiedad.
        |--------------------------------------------------------------------------
        */

        'tipo_irpf' =>
            0,

        'base_imponible' =>
            $baseImponible,

        'total_iva' =>
            $totalIva,

        'total_irpf' =>
            0.00,

        'total' =>
            $totalFactura

    ],

    /*
    |--------------------------------------------------------------------------
    | DATOS HISTÓRICOS DEL EMISOR
    |--------------------------------------------------------------------------
    */

    'emisor' =>
        $datosEmisor,

    /*
    |--------------------------------------------------------------------------
    | CLIENTE RECEPTOR
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | LÍNEAS
    |--------------------------------------------------------------------------
    */

    'lineas' =>
        $lineasProcesadas,

    /*
    |--------------------------------------------------------------------------
    | FECHA DE GUARDADO
    |--------------------------------------------------------------------------
    */

    'fecha_guardado' =>
        date('Y-m-d H:i:s')

];


/* ==========================================
   CONVERTIR A JSON
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
        'Error convirtiendo factura a JSON: '
        . $e->getMessage()
    );

    volverConError(
        'No se pudieron preparar los datos de la factura.'
    );

}


/* ==========================================
   CIFRAR DATOS PRIVADOS CON OPENSSL
========================================== */

try {

    if (
        !function_exists('openssl_encrypt')
    ) {

        throw new Exception(
            'La extensión OpenSSL de PHP no está disponible.'
        );

    }


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


    if (
        $datosCifrados === false
    ) {

        throw new Exception(
            'OpenSSL no pudo cifrar los datos.'
        );

    }


    $contenidoPrivado =
        base64_encode(
            $iv .
            $datosCifrados
        );


    if (
        $contenidoPrivado === ''
    ) {

        throw new Exception(
            'El resultado del cifrado está vacío.'
        );

    }


} catch (Throwable $e) {

    error_log(
        'Error cifrando factura: '
        . $e->getMessage()
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
       MODO EDICIÓN
    ========================================== */

    if ($facturaId > 0) {

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
                    total_irpf = 0,
                    total = ?,
                    metodo_pago = ?,
                    fecha_vencimiento = ?,
                    observaciones = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
                  AND estado = 'borrador'
            ");


        $actualizado =
            $stmtFactura->execute([

                $serie,

                $fechaEmision,

                $clienteTecnicoId,

                $baseImponible,

                $totalIva,

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


        if (!$actualizado) {

            throw new Exception(
                'No se pudo actualizar la factura.'
            );

        }


    } else {

        /* ==========================================
           CREAR FACTURA COMO BORRADOR
        ========================================== */

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
                    0,
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

            $clienteTecnicoId,

            $baseImponible,

            $totalIva,

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

    }


    /* ==========================================
       GUARDAR DATOS PRIVADOS CIFRADOS
========================================== */

    $stmtPrivada =
        $pdo->prepare("
            INSERT INTO facturas_privadas (
                usuario_id,
                factura_id,
                datos_cifrados
            )
            VALUES (
                ?,
                ?,
                ?
            )
            ON DUPLICATE KEY UPDATE
                usuario_id = VALUES(usuario_id),
                datos_cifrados = VALUES(datos_cifrados)
        ");


    $guardadaPrivada =
        $stmtPrivada->execute([

            $usuarioId,

            $facturaId,

            $contenidoPrivado

        ]);


    if (!$guardadaPrivada) {

        throw new Exception(
            'No se pudieron guardar los datos privados de la factura.'
        );

    }


    /* ==========================================
       NO USAR factura_lineas
========================================== */

    /*
    |--------------------------------------------------------------------------
    | Las líneas permanecen dentro de datos_cifrados.
    |--------------------------------------------------------------------------
    */


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
        'ERROR REAL: ' .
        $e->getMessage()
    );

}