
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

$usuarioId = (int) $_SESSION['usuario_id'];


/* ==========================================
   COMPROBAR SI ESTAMOS EDITANDO
========================================== */

$facturaId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$modoEdicion = false;

$facturaEditar = null;

$lineasEditar = [];


/* ==========================================
   DATOS PRIVADOS POR DEFECTO
========================================== */

$clienteFactura = [
    'nombre_razon_social' => '',
    'nif' => '',
    'direccion' => '',
    'codigo_postal' => '',
    'ciudad' => '',
    'provincia' => '',
    'pais' => 'España',
    'email' => '',
    'telefono' => ''
];

$datosPrivadosEditar = null;


/* ==========================================
   EDITAR FACTURA
========================================== */

if ($facturaId) {

    /*
    |--------------------------------------------------------------------------
    | Buscar factura perteneciente al usuario
    |--------------------------------------------------------------------------
    |
    | El cliente técnico asociado a la factura se utiliza únicamente
    | para comprobar que la factura pertenece al usuario conectado.
    |
    */

    $stmtFactura = $pdo->prepare("
        SELECT f.*
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

    $facturaEditar = $stmtFactura->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | La factura no pertenece al usuario
    |--------------------------------------------------------------------------
    */

    if (!$facturaEditar) {

        http_response_code(403);

        die('
            <div style="
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 80px auto;
                padding: 30px;
                text-align: center;
                border: 1px solid #ddd;
                border-radius: 12px;
            ">
                <h2>Acceso no permitido</h2>

                <p>
                    No tienes permiso para acceder a esta factura.
                </p>

                <a href="facturas.php">
                    Volver a facturación
                </a>
            </div>
        ');
    }


    /*
    |--------------------------------------------------------------------------
    | Solo se pueden editar borradores
    |--------------------------------------------------------------------------
    */

    if ($facturaEditar['estado'] !== 'borrador') {

        die('
            <div style="
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 80px auto;
                padding: 30px;
                text-align: center;
                border: 1px solid #ddd;
                border-radius: 12px;
            ">
                <h2>Factura no editable</h2>

                <p>
                    Esta factura ya ha sido emitida y no puede modificarse.
                </p>

                <a href="ver_factura.php?id=' . (int) $facturaId . '">
                    Ver factura
                </a>
            </div>
        ');
    }


    /* ==========================================
       OBTENER DATOS PRIVADOS CIFRADOS
    ========================================== */

    try {

        $stmtPrivada = $pdo->prepare("
            SELECT datos_cifrados
            FROM facturas_privadas
            WHERE factura_id = ?
              AND usuario_id = ?
            LIMIT 1
        ");

        $stmtPrivada->execute([
            $facturaId,
            $usuarioId
        ]);

        $privada = $stmtPrivada->fetch(PDO::FETCH_ASSOC);


        if ($privada && !empty($privada['datos_cifrados'])) {

            $claveCifrado = $VIZIUNEAI_FACTURAS_KEY ?? '';

            if (empty($claveCifrado)) {
                throw new Exception(
                    'No está configurada la clave de cifrado.'
                );
            }

            if (strlen($claveCifrado) < 32) {
                throw new Exception(
                    'La clave de cifrado no es suficientemente segura.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | DERIVAR CLAVE AES-256
            |--------------------------------------------------------------------------
            */

            $clave = hash(
                'sha256',
                $claveCifrado,
                true
            );


            /*
            |--------------------------------------------------------------------------
            | DECODIFICAR DATOS
            |--------------------------------------------------------------------------
            */

            $contenido = base64_decode(
                $privada['datos_cifrados'],
                true
            );


            if ($contenido === false) {
                throw new Exception(
                    'No se pudieron leer los datos cifrados.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | OPENSSL AES-256-CBC
            |--------------------------------------------------------------------------
            |
            | Los primeros 16 bytes son el IV.
            | El resto contiene los datos cifrados.
            |
            */

            $ivLength = 16;


            if (strlen($contenido) <= $ivLength) {
                throw new Exception(
                    'Los datos cifrados de la factura no son válidos.'
                );
            }


            $iv = substr(
                $contenido,
                0,
                $ivLength
            );


            $datosCifrados = substr(
                $contenido,
                $ivLength
            );


            /*
            |--------------------------------------------------------------------------
            | DESCIFRAR
            |--------------------------------------------------------------------------
            */

            $jsonFactura = openssl_decrypt(
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


            /*
            |--------------------------------------------------------------------------
            | CONVERTIR JSON
            |--------------------------------------------------------------------------
            */

            $datosPrivadosEditar = json_decode(
                $jsonFactura,
                true
            );


            if (!is_array($datosPrivadosEditar)) {
                throw new Exception(
                    'Los datos privados de la factura no son válidos.'
                );
            }


            /* ==========================================
               CLIENTE DE LA FACTURA
            ========================================== */

            if (
                isset($datosPrivadosEditar['cliente']) &&
                is_array($datosPrivadosEditar['cliente'])
            ) {

                foreach (
                    $clienteFactura as $campo => $valor
                ) {

                    if (
                        array_key_exists(
                            $campo,
                            $datosPrivadosEditar['cliente']
                        )
                    ) {

                        $clienteFactura[$campo] =
                            $datosPrivadosEditar['cliente'][$campo] ?? '';
                    }
                }
            }


            /* ==========================================
               LÍNEAS DE LA FACTURA
            ========================================== */

            if (
                isset($datosPrivadosEditar['lineas']) &&
                is_array($datosPrivadosEditar['lineas'])
            ) {

                $lineasEditar =
                    $datosPrivadosEditar['lineas'];
            }
        }


    } catch (Throwable $e) {

        error_log(
            "Error descifrando factura para edición: "
            . $e->getMessage()
        );

        die('
            <div style="
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 80px auto;
                padding: 30px;
                text-align: center;
                border: 1px solid #ddd;
                border-radius: 12px;
            ">
                <h2>No se pudo cargar la factura</h2>

                <p>
                    Los datos privados de esta factura no se pudieron recuperar.
                </p>

                <a href="facturas.php">
                    Volver a facturación
                </a>
            </div>
        ');
    }


    $modoEdicion = true;
}


/* ==========================================
   EMPRESA
========================================== */

$stmtEmpresa = $pdo->query("
    SELECT *
    FROM empresa_facturacion
    ORDER BY id ASC
    LIMIT 1
");

$empresa = $stmtEmpresa->fetch();


/* ==========================================
   DATOS POR DEFECTO
========================================== */

$serieFactura = $facturaEditar['serie']
    ?? ($empresa['serie_factura'] ?? 'A');

$fechaHoy = $facturaEditar['fecha_emision']
    ?? date('Y-m-d');

$fechaVencimiento = '';

$tipoIrpf = 0;

$metodoPago = '';

$observaciones = '';


/* ==========================================
   RECUPERAR DATOS DE FACTURA AL EDITAR
========================================== */

if ($modoEdicion && $datosPrivadosEditar) {

    $fechaVencimiento =
        $datosPrivadosEditar['factura']['fecha_vencimiento']
        ?? '';

    $tipoIrpf =
        $datosPrivadosEditar['factura']['tipo_irpf']
        ?? 0;

    $metodoPago =
        $datosPrivadosEditar['factura']['metodo_pago']
        ?? '';

    $observaciones =
        $datosPrivadosEditar['factura']['observaciones']
        ?? '';
}


/* ==========================================
   DESTINO AL VOLVER
========================================== */

$urlVolver = 'facturas.php';

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
        <?= $modoEdicion ? 'Editar factura' : 'Crear factura' ?> | ViziuneAI
    </title>

    <link
        rel="stylesheet"
        href="css/crear-factura.css"
    >

</head>

<body>

    <main class="crear-factura-contenedor">

        <!-- ==========================================
             CABECERA
        ========================================== -->

        <header class="crear-factura-header">

            <div>

                <h1>
                    <?= $modoEdicion ? 'Editar factura' : 'Crear factura' ?>
                </h1>

                <p>

                    <?php if ($modoEdicion): ?>

                        Modifica la factura y guarda los cambios.

                    <?php else: ?>

                        Crea una nueva factura y guárdala como borrador.

                    <?php endif; ?>

                </p>

            </div>

            <a
                href="<?= htmlspecialchars($urlVolver) ?>"
                class="boton-volver"
            >
                ← Volver
            </a>

        </header>


        <!-- ==========================================
             FORMULARIO
        ========================================== -->

        <form
            id="formularioFactura"
            class="formulario-factura"
            action="guardar_factura.php"
            method="POST"
        >

            <?php if ($modoEdicion): ?>

                <input
                    type="hidden"
                    name="factura_id"
                    value="<?= (int) $facturaId ?>"
                >

            <?php endif; ?>


            <!-- ==========================================
                 DATOS DE LA FACTURA
            ========================================== -->

            <section class="bloque-factura">

                <h2>Datos de la factura</h2>

                <div class="grid-factura">

                    <div class="campo-factura">

                        <label for="serie">
                            Serie
                        </label>

                        <input
                            type="text"
                            id="serie"
                            name="serie"
                            value="<?= htmlspecialchars(
                                $serieFactura,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            maxlength="20"
                            required
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="numero">
                            Número
                        </label>

                        <input
                            type="text"
                            id="numero"
                            value="<?= $modoEdicion
                                ? 'Se asignará al emitir'
                                : 'Automático al emitir' ?>"
                            readonly
                        >

                        <small>
                            El número definitivo se asignará al emitir la factura.
                        </small>

                    </div>


                    <div class="campo-factura">

                        <label for="fecha_emision">
                            Fecha de emisión
                        </label>

                        <input
                            type="date"
                            id="fecha_emision"
                            name="fecha_emision"
                            value="<?= htmlspecialchars(
                                $fechaHoy,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="fecha_vencimiento">
                            Fecha de vencimiento
                        </label>

                        <input
                            type="date"
                            id="fecha_vencimiento"
                            name="fecha_vencimiento"
                            value="<?= htmlspecialchars(
                                $fechaVencimiento,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                </div>

            </section>


            <!-- ==========================================
                 CLIENTE
            ========================================== -->

            <section class="bloque-factura">

                <h2>Cliente</h2>

                <p style="
                    margin-top: -5px;
                    margin-bottom: 20px;
                    opacity: 0.8;
                ">
                    Introduce los datos del cliente al que vas a emitir la factura.
                </p>


                <div class="grid-factura">

                    <div class="campo-factura completo">

                        <label for="cliente_nombre_razon_social">
                            Nombre / Razón social
                        </label>

                        <input
                            type="text"
                            id="cliente_nombre_razon_social"
                            name="cliente_nombre_razon_social"
                            value="<?= htmlspecialchars(
                                $clienteFactura['nombre_razon_social'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Nombre de la persona o empresa"
                            maxlength="255"
                            required
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="cliente_nif">
                            NIF / CIF
                        </label>

                        <input
                            type="text"
                            id="cliente_nif"
                            name="cliente_nif"
                            value="<?= htmlspecialchars(
                                $clienteFactura['nif'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="NIF o CIF"
                            maxlength="50"
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="cliente_email">
                            Email
                        </label>

                        <input
                            type="email"
                            id="cliente_email"
                            name="cliente_email"
                            value="<?= htmlspecialchars(
                                $clienteFactura['email'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="cliente@ejemplo.com"
                            maxlength="255"
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="cliente_telefono">
                            Teléfono
                        </label>

                        <input
                            type="text"
                            id="cliente_telefono"
                            name="cliente_telefono"
                            value="<?= htmlspecialchars(
                                $clienteFactura['telefono'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Teléfono"
                            maxlength="50"
                        >

                    </div>


                    <div class="campo-factura completo">

                        <label for="cliente_direccion">
                            Dirección
                        </label>

                        <input
                            type="text"
                            id="cliente_direccion"
                            name="cliente_direccion"
                            value="<?= htmlspecialchars(
                                $clienteFactura['direccion'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Dirección completa"
                            maxlength="255"
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="cliente_codigo_postal">
                            Código postal
                        </label>

                        <input
                            type="text"
                            id="cliente_codigo_postal"
                            name="cliente_codigo_postal"
                            value="<?= htmlspecialchars(
                                $clienteFactura['codigo_postal'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Código postal"
                            maxlength="20"
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="cliente_ciudad">
                            Ciudad
                        </label>

                        <input
                            type="text"
                            id="cliente_ciudad"
                            name="cliente_ciudad"
                            value="<?= htmlspecialchars(
                                $clienteFactura['ciudad'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Ciudad"
                            maxlength="100"
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="cliente_provincia">
                            Provincia
                        </label>

                        <input
                            type="text"
                            id="cliente_provincia"
                            name="cliente_provincia"
                            value="<?= htmlspecialchars(
                                $clienteFactura['provincia'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="Provincia"
                            maxlength="100"
                        >

                    </div>


                    <div class="campo-factura">

                        <label for="cliente_pais">
                            País
                        </label>

                        <input
                            type="text"
                            id="cliente_pais"
                            name="cliente_pais"
                            value="<?= htmlspecialchars(
                                $clienteFactura['pais'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="País"
                            maxlength="100"
                        >

                    </div>

                </div>

            </section>


            <!-- ==========================================
                 LINEAS DE FACTURA
            ========================================== -->

            <section class="bloque-factura">

                <h2>Conceptos</h2>

                <div class="lineas-cabecera">

                    <div>Descripción</div>
                    <div>Cantidad</div>
                    <div>Precio</div>
                    <div>Descuento %</div>
                    <div>IVA</div>
                    <div></div>

                </div>


                <div id="lineasFactura">

                    <?php if (
                        $modoEdicion &&
                        !empty($lineasEditar)
                    ): ?>

                        <?php foreach (
                            $lineasEditar as $linea
                        ): ?>

                            <div class="linea-factura">

                                <input
                                    type="text"
                                    name="descripcion[]"
                                    placeholder="Descripción del producto o servicio"
                                    value="<?= htmlspecialchars(
                                        $linea['descripcion'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    required
                                >

                                <input
                                    type="number"
                                    name="cantidad[]"
                                    class="cantidad"
                                    value="<?= htmlspecialchars(
                                        $linea['cantidad'] ?? 1,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    min="0.001"
                                    step="0.001"
                                    required
                                >

                                <input
                                    type="number"
                                    name="precio_unitario[]"
                                    class="precio-unitario"
                                    value="<?= htmlspecialchars(
                                        $linea['precio_unitario'] ?? 0,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    min="0"
                                    step="0.01"
                                    required
                                >

                                <input
                                    type="number"
                                    name="descuento[]"
                                    class="descuento"
                                    value="<?= htmlspecialchars(
                                        $linea['descuento'] ?? 0,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                >

                                <select
                                    name="tipo_iva[]"
                                    class="tipo-iva"
                                >

                                    <option
                                        value="21"
                                        <?= (float) (
                                            $linea['tipo_iva'] ?? 21
                                        ) === 21.0
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        21%
                                    </option>

                                    <option
                                        value="10"
                                        <?= (float) (
                                            $linea['tipo_iva'] ?? 21
                                        ) === 10.0
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        10%
                                    </option>

                                    <option
                                        value="4"
                                        <?= (float) (
                                            $linea['tipo_iva'] ?? 21
                                        ) === 4.0
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        4%
                                    </option>

                                    <option
                                        value="0"
                                        <?= (float) (
                                            $linea['tipo_iva'] ?? 21
                                        ) === 0.0
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        0%
                                    </option>

                                </select>

                                <div class="linea-total">

                                    <?= number_format(
                                        (float) (
                                            $linea['total_linea'] ?? 0
                                        ),
                                        2,
                                        ',',
                                        '.'
                                    ) ?> €

                                </div>

                                <button
                                    type="button"
                                    class="boton-eliminar-linea"
                                    title="Eliminar línea"
                                >
                                    ×
                                </button>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="linea-factura">

                            <input
                                type="text"
                                name="descripcion[]"
                                placeholder="Descripción del producto o servicio"
                                required
                            >

                            <input
                                type="number"
                                name="cantidad[]"
                                class="cantidad"
                                value="1"
                                min="0.001"
                                step="0.001"
                                required
                            >

                            <input
                                type="number"
                                name="precio_unitario[]"
                                class="precio-unitario"
                                value="0"
                                min="0"
                                step="0.01"
                                required
                            >

                            <input
                                type="number"
                                name="descuento[]"
                                class="descuento"
                                value="0"
                                min="0"
                                max="100"
                                step="0.01"
                            >

                            <select
                                name="tipo_iva[]"
                                class="tipo-iva"
                            >

                                <option value="21">
                                    21%
                                </option>

                                <option value="10">
                                    10%
                                </option>

                                <option value="4">
                                    4%
                                </option>

                                <option value="0">
                                    0%
                                </option>

                            </select>

                            <div class="linea-total">
                                0,00 €
                            </div>

                            <button
                                type="button"
                                class="boton-eliminar-linea"
                                title="Eliminar línea"
                            >
                                ×
                            </button>

                        </div>

                    <?php endif; ?>

                </div>


                <button
                    type="button"
                    id="anadirLinea"
                    class="boton-anadir-linea"
                >
                    + Añadir línea
                </button>

            </section>


            <!-- ==========================================
                 IMPUESTOS Y TOTALES
            ========================================== -->

            <section class="bloque-factura">

                <h2>Impuestos y totales</h2>

                <div class="grid-factura">

                    <div class="campo-factura">

                        <label for="tipo_irpf">
                            IRPF
                        </label>

                        <select
                            id="tipo_irpf"
                            name="tipo_irpf"
                        >

                            <option
                                value="0"
                                <?= (float) $tipoIrpf === 0.0
                                    ? 'selected'
                                    : '' ?>
                            >
                                Sin IRPF
                            </option>

                            <option
                                value="7"
                                <?= (float) $tipoIrpf === 7.0
                                    ? 'selected'
                                    : '' ?>
                            >
                                7%
                            </option>

                            <option
                                value="15"
                                <?= (float) $tipoIrpf === 15.0
                                    ? 'selected'
                                    : '' ?>
                            >
                                15%
                            </option>

                        </select>

                    </div>


                    <div class="campo-factura">

                        <label for="metodo_pago">
                            Método de pago
                        </label>

                        <select
                            id="metodo_pago"
                            name="metodo_pago"
                        >

                            <option
                                value=""
                                <?= $metodoPago === ''
                                    ? 'selected'
                                    : '' ?>
                            >
                                Seleccionar
                            </option>

                            <option
                                value="Transferencia bancaria"
                                <?= $metodoPago === 'Transferencia bancaria'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Transferencia bancaria
                            </option>

                            <option
                                value="Domiciliación"
                                <?= $metodoPago === 'Domiciliación'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Domiciliación
                            </option>

                            <option
                                value="Tarjeta"
                                <?= $metodoPago === 'Tarjeta'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Tarjeta
                            </option>

                            <option
                                value="Efectivo"
                                <?= $metodoPago === 'Efectivo'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Efectivo
                            </option>

                        </select>

                    </div>

                </div>


                <div class="resumen-factura">

                    <div class="totales-factura">

                        <div class="fila-total">

                            <span>
                                Base imponible
                            </span>

                            <strong id="totalBase">
                                0,00 €
                            </strong>

                        </div>


                        <div class="fila-total">

                            <span>
                                IVA
                            </span>

                            <strong id="totalIva">
                                0,00 €
                            </strong>

                        </div>


                        <div class="fila-total">

                            <span>
                                IRPF
                            </span>

                            <strong id="totalIrpf">
                                0,00 €
                            </strong>

                        </div>


                        <div class="fila-total total-final">

                            <span>
                                TOTAL
                            </span>

                            <strong id="totalFactura">
                                0,00 €
                            </strong>

                        </div>

                    </div>

                </div>

            </section>


            <!-- ==========================================
                 OBSERVACIONES
            ========================================== -->

            <section class="bloque-factura">

                <h2>Observaciones</h2>

                <div class="campo-factura">

                    <label for="observaciones">
                        Observaciones de la factura
                    </label>

                    <textarea
                        id="observaciones"
                        name="observaciones"
                        placeholder="Información adicional que quieras incluir..."
                    ><?= htmlspecialchars(
                        $observaciones,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></textarea>

                </div>

            </section>


            <!-- ==========================================
                 TOTALES OCULTOS
            ========================================== -->

            <input
                type="hidden"
                name="base_imponible"
                id="inputBase"
                value="0.00"
            >

            <input
                type="hidden"
                name="total_iva"
                id="inputIva"
                value="0.00"
            >

            <input
                type="hidden"
                name="total_irpf"
                id="inputIrpf"
                value="0.00"
            >

            <input
                type="hidden"
                name="total"
                id="inputTotal"
                value="0.00"
            >


            <!-- ==========================================
                 BOTONES
            ========================================== -->

            <div class="acciones-formulario">

                <a
                    href="<?= htmlspecialchars($urlVolver) ?>"
                    class="boton-cancelar-factura"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="boton-guardar-factura"
                >

                    <?php if ($modoEdicion): ?>

                        Guardar cambios

                    <?php else: ?>

                        Guardar como borrador

                    <?php endif; ?>

                </button>

            </div>

        </form>

    </main>


    <!-- ==========================================
         JAVASCRIPT
    ========================================== -->

    <script src="js/crear-factura.js"></script>

</body>

</html>
