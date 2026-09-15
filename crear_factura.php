
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

$usuarioConectado = true;
$usuarioId = (int) $_SESSION['usuario_id'];
$clienteUsuario = null;


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
   CLIENTE CONECTADO
========================================== */

$stmtClienteUsuario = $pdo->prepare("
    SELECT
        id,
        nombre_razon_social,
        nif,
        email
    FROM clientes
    WHERE usuario_id = ?
      AND activo = 1
    LIMIT 1
");

$stmtClienteUsuario->execute([
    $usuarioId
]);

$clienteUsuario = $stmtClienteUsuario->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Si hay usuario conectado pero no tiene cliente asociado
|--------------------------------------------------------------------------
*/

if (!$clienteUsuario) {

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
            <h2>No se ha encontrado tu perfil de cliente</h2>

            <p>
                Tu usuario todavía no tiene un perfil de cliente asociado.
            </p>

            <a href="mi_cuenta.php">
                Volver a mi cuenta
            </a>
        </div>
    ');
}


/* ==========================================
   EDITAR FACTURA
========================================== */

if ($facturaId) {

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

                <a href="mi_cuenta.php">
                    Volver a mi cuenta
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


    /*
    |--------------------------------------------------------------------------
    | Obtener líneas
    |--------------------------------------------------------------------------
    */

    $stmtLineasEditar = $pdo->prepare("
        SELECT *
        FROM factura_lineas
        WHERE factura_id = ?
        ORDER BY orden ASC, id ASC
    ");

    $stmtLineasEditar->execute([
        $facturaId
    ]);

    $lineasEditar = $stmtLineasEditar->fetchAll(PDO::FETCH_ASSOC);

    $modoEdicion = true;
}


/* ==========================================
   CLIENTES
========================================== */

/*
|--------------------------------------------------------------------------
| El usuario ya está identificado mediante la sesión.
| No cargamos otros clientes.
|--------------------------------------------------------------------------
*/

$clientes = [];


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

$fechaVencimiento = $facturaEditar['fecha_vencimiento']
    ?? '';

$tipoIrpf = 0;


/* ==========================================
   RECUPERAR IRPF AL EDITAR
========================================== */

if ($modoEdicion && $facturaEditar) {

    $baseFactura = (float) (
        $facturaEditar['base_imponible']
        ?? 0
    );

    $totalIrpfFactura = (float) (
        $facturaEditar['total_irpf']
        ?? 0
    );

    if ($baseFactura > 0 && $totalIrpfFactura > 0) {

        $porcentajeIrpf =
            ($totalIrpfFactura / $baseFactura) * 100;

        if (abs($porcentajeIrpf - 7) < 0.01) {

            $tipoIrpf = 7;

        } elseif (abs($porcentajeIrpf - 15) < 0.01) {

            $tipoIrpf = 15;
        }
    }
}


$metodoPago = $facturaEditar['metodo_pago']
    ?? '';

$observaciones = $facturaEditar['observaciones']
    ?? '';


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
                            value="<?= htmlspecialchars($serieFactura) ?>"
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
                            value="<?= htmlspecialchars($fechaHoy) ?>"
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
                            value="<?= htmlspecialchars($fechaVencimiento) ?>"
                        >

                    </div>

                </div>

            </section>


            <!-- ==========================================
                 CLIENTE
            ========================================== -->

            <section class="bloque-factura">

                <h2>Cliente</h2>

                <div class="grid-factura">

                    <div class="campo-factura completo">

                        <label>
                            Cliente
                        </label>

                        <div
                            class="cliente-info"
                            style="
                                margin-top: 0;
                                padding: 15px;
                                border: 1px solid rgba(0, 243, 255, 0.25);
                                border-radius: 8px;
                            "
                        >

                            <strong>
                                <?= htmlspecialchars(
                                    $clienteUsuario['nombre_razon_social']
                                ) ?>
                            </strong>

                            <br>

                            <span>
                                NIF:
                                <?= htmlspecialchars(
                                    $clienteUsuario['nif'] ?: 'No indicado'
                                ) ?>
                            </span>

                            <?php if (!empty($clienteUsuario['email'])): ?>

                                <br>

                                <span>
                                    <?= htmlspecialchars(
                                        $clienteUsuario['email']
                                    ) ?>
                                </span>

                            <?php endif; ?>

                        </div>


                        <input
                            type="hidden"
                            id="cliente_id"
                            name="cliente_id"
                            value="<?= (int) $clienteUsuario['id'] ?>"
                        >

                    </div>

                </div>


                <div
                    id="clienteInfo"
                    class="cliente-info"
                >

                    <?= htmlspecialchars(
                        $clienteUsuario['nombre_razon_social']
                    ) ?>

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

                    <?php if ($modoEdicion && !empty($lineasEditar)): ?>

                        <?php foreach ($lineasEditar as $linea): ?>

                            <div class="linea-factura">

                                <input
                                    type="text"
                                    name="descripcion[]"
                                    placeholder="Descripción del producto o servicio"
                                    value="<?= htmlspecialchars(
                                        $linea['descripcion']
                                    ) ?>"
                                    required
                                >

                                <input
                                    type="number"
                                    name="cantidad[]"
                                    class="cantidad"
                                    value="<?= htmlspecialchars(
                                        $linea['cantidad']
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
                                        $linea['precio_unitario']
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
                                        $linea['descuento']
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
                                        <?= (float) $linea['tipo_iva'] === 21.0
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        21%
                                    </option>

                                    <option
                                        value="10"
                                        <?= (float) $linea['tipo_iva'] === 10.0
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        10%
                                    </option>

                                    <option
                                        value="4"
                                        <?= (float) $linea['tipo_iva'] === 4.0
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        4%
                                    </option>

                                    <option
                                        value="0"
                                        <?= (float) $linea['tipo_iva'] === 0.0
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        0%
                                    </option>

                                </select>

                                <div class="linea-total">
                                    <?= number_format(
                                        (float) $linea['total_linea'],
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
                    ><?= htmlspecialchars($observaciones) ?></textarea>

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
