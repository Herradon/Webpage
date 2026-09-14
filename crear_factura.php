<?php

require_once 'config.php';

/* ==========================================
   CLIENTES
========================================== */

$stmtClientes = $pdo->query("
    SELECT
        id,
        nombre_razon_social,
        nif,
        email
    FROM clientes
    WHERE activo = 1
    ORDER BY nombre_razon_social ASC
");

$clientes = $stmtClientes->fetchAll();


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

$serieFactura = $empresa['serie_factura'] ?? 'A';

$fechaHoy = date('Y-m-d');

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Crear factura | ViziuneAI</title>

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

                <h1>Crear factura</h1>

                <p>
                    Crea una nueva factura y guárdala como borrador.
                </p>

            </div>

            <a
                href="facturas.php"
                class="boton-volver"
            >
                ← Volver a facturas
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
                            value="Automático al emitir"
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

                        <label for="cliente_id">
                            Cliente
                        </label>

                        <select
                            id="cliente_id"
                            name="cliente_id"
                            required
                        >

                            <option value="">
                                Selecciona un cliente
                            </option>

                            <?php foreach ($clientes as $cliente): ?>

                                <option
                                    value="<?= (int) $cliente['id'] ?>"
                                    data-nif="<?= htmlspecialchars($cliente['nif']) ?>"
                                    data-email="<?= htmlspecialchars($cliente['email'] ?? '') ?>"
                                >

                                    <?= htmlspecialchars($cliente['nombre_razon_social']) ?>
                                    —
                                    <?= htmlspecialchars($cliente['nif']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>


                <div
                    id="clienteInfo"
                    class="cliente-info"
                >

                    Selecciona un cliente para ver sus datos.

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

                            <option value="0">
                                Sin IRPF
                            </option>

                            <option value="7">
                                7%
                            </option>

                            <option value="15">
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

                            <option value="">
                                Seleccionar
                            </option>

                            <option value="Transferencia bancaria">
                                Transferencia bancaria
                            </option>

                            <option value="Domiciliación">
                                Domiciliación
                            </option>

                            <option value="Tarjeta">
                                Tarjeta
                            </option>

                            <option value="Efectivo">
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
                    ></textarea>

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
                    href="facturas.php"
                    class="boton-cancelar-factura"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="boton-guardar-factura"
                >
                    💾 Guardar como borrador
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