
<?php

session_start();

require_once 'config.php';


/* ==========================================
   COMPROBAR SESIÓN PRINCIPAL
========================================== */

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];


/* ==========================================
   COMPROBAR SUSCRIPCIÓN
========================================== */

/*
|--------------------------------------------------------------------------
| Cuenta oficial de ViziuneSL
|--------------------------------------------------------------------------
|
| El usuario ID 8 tiene acceso gratuito.
|--------------------------------------------------------------------------
*/

if ($usuarioId === 8) {

    $suscripcionActiva = true;

} else {

    $stmtSuscripcion = $pdo->prepare("
        SELECT
            suscripcion_activa,
            suscripcion_fin
        FROM usuarios
        WHERE id = ?
        LIMIT 1
    ");

    $stmtSuscripcion->execute([
        $usuarioId
    ]);

    $datosSuscripcion = $stmtSuscripcion->fetch(PDO::FETCH_ASSOC);

    $suscripcionActiva = false;


    if (
        $datosSuscripcion &&
        (int) $datosSuscripcion['suscripcion_activa'] === 1
    ) {

        $suscripcionActiva = true;


        /* ==========================================
           COMPROBAR FECHA DE FINALIZACIÓN
        ========================================== */

        if (!empty($datosSuscripcion['suscripcion_fin'])) {

            try {

                $fechaFin = new DateTime(
                    $datosSuscripcion['suscripcion_fin']
                );

                $ahora = new DateTime();


                if ($fechaFin < $ahora) {

                    $pdo->prepare("
                        UPDATE usuarios
                        SET suscripcion_activa = 0
                        WHERE id = ?
                    ")->execute([
                        $usuarioId
                    ]);


                    $suscripcionActiva = false;

                    $_SESSION['suscripcion_activa'] = 0;

                }

            } catch (Exception $e) {

                $suscripcionActiva = false;

            }

        }

    }

}


/* ==========================================
   BLOQUEAR FACTURACIÓN SI NO HAY SUSCRIPCIÓN
========================================== */

if (!$suscripcionActiva) {

    $_SESSION['suscripcion_activa'] = 0;

    header('Location: suscripcion.php');
    exit;

}


$_SESSION['suscripcion_activa'] = 1;


/* ==========================================
   OBTENER FACTURAS DEL USUARIO CONECTADO
========================================== */

try {

    $stmt = $pdo->prepare("
        SELECT
            f.id,
            f.serie,
            f.numero,
            f.fecha_emision,
            f.base_imponible,
            f.total_iva,
            f.total_irpf,
            f.total,
            f.estado,
            c.nombre_razon_social,
            c.nif
        FROM facturas f

        INNER JOIN clientes c
            ON f.cliente_id = c.id

        WHERE c.usuario_id = ?
          AND c.activo = 1

        ORDER BY f.fecha_emision DESC, f.id DESC
    ");

    $stmt->execute([
        $usuarioId
    ]);

    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log(
        "Error obteniendo facturas del usuario: "
        . $e->getMessage()
    );

    $facturas = [];

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

    <title>Facturación | ViziuneAI</title>

    <link
        rel="stylesheet"
        href="css/facturas.css"
    >

</head>

<body>

    <!-- ==========================================
         CABECERA
    ========================================== -->

    <header class="facturacion-header">

        <div class="cabecera-contenido">

            <div>

                <h1>
                    Facturación
                </h1>

                <p>
                    Gestión de tus facturas
                </p>

            </div>


            <div class="cabecera-acciones">

                <a
                    href="index.php"
                    class="boton-secundario"
                >
                    ← Volver
                </a>

                <a
                    href="crear_factura.php"
                    class="boton-principal"
                >
                    + Nueva factura
                </a>

            </div>

        </div>

    </header>


    <!-- ==========================================
         CONTENIDO PRINCIPAL
    ========================================== -->

    <main class="facturacion-contenedor">


        <!-- ==========================================
             ESTADÍSTICAS
        ========================================== -->

        <section class="estadisticas">

            <?php

            $totalFacturas = count($facturas);

            $totalEmitidas = 0;

            $totalBorradores = 0;

            $totalAnuladas = 0;

            $importeTotal = 0;


            foreach ($facturas as $factura) {

                if ($factura['estado'] === 'emitida') {
                    $totalEmitidas++;
                }

                if ($factura['estado'] === 'borrador') {
                    $totalBorradores++;
                }

                if ($factura['estado'] === 'anulada') {
                    $totalAnuladas++;
                }

                if ($factura['estado'] !== 'anulada') {

                    $importeTotal += (float) $factura['total'];

                }

            }

            ?>


            <div class="estadistica">

                <span class="estadistica-titulo">
                    Facturas
                </span>

                <strong>
                    <?= $totalFacturas ?>
                </strong>

            </div>


            <div class="estadistica">

                <span class="estadistica-titulo">
                    Emitidas
                </span>

                <strong>
                    <?= $totalEmitidas ?>
                </strong>

            </div>


            <div class="estadistica">

                <span class="estadistica-titulo">
                    Borradores
                </span>

                <strong>
                    <?= $totalBorradores ?>
                </strong>

            </div>


            <div class="estadistica">

                <span class="estadistica-titulo">
                    Importe
                </span>

                <strong>
                    <?= number_format(
                        $importeTotal,
                        2,
                        ',',
                        '.'
                    ) ?> €
                </strong>

            </div>

        </section>


        <!-- ==========================================
             FILTROS
        ========================================== -->

        <section class="filtros">

            <div class="campo-busqueda">

                <label for="buscarFactura">
                    Buscar
                </label>

                <input
                    type="search"
                    id="buscarFactura"
                    placeholder="Número, cliente o NIF..."
                    autocomplete="off"
                >

            </div>


            <div class="campo-filtro">

                <label for="filtroEstado">
                    Estado
                </label>

                <select id="filtroEstado">

                    <option value="todos">
                        Todos
                    </option>

                    <option value="emitida">
                        Emitidas
                    </option>

                    <option value="borrador">
                        Borradores
                    </option>

                    <option value="anulada">
                        Anuladas
                    </option>

                </select>

            </div>

        </section>


        <!-- ==========================================
             LISTADO DE FACTURAS
        ========================================== -->

        <section class="tabla-contenedor">

            <div class="tabla-cabecera">

                <h2>
                    Mis facturas
                </h2>

                <span>
                    <?= $totalFacturas ?> registros
                </span>

            </div>


            <?php if (!empty($facturas)): ?>

                <div class="tabla-responsive">

                    <table id="tablaFacturas">

                        <thead>

                            <tr>

                                <th>
                                    Número
                                </th>

                                <th>
                                    Cliente
                                </th>

                                <th>
                                    NIF
                                </th>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Base
                                </th>

                                <th>
                                    IVA
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($facturas as $factura): ?>

                                <?php

                                $numeroFactura =
                                    $factura['serie']
                                    . '-'
                                    . str_pad(
                                        $factura['numero'],
                                        6,
                                        '0',
                                        STR_PAD_LEFT
                                    );

                                ?>


                                <tr
                                    class="fila-factura"
                                    data-estado="<?= htmlspecialchars(
                                        $factura['estado'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-busqueda="<?= htmlspecialchars(
                                        strtolower(
                                            $numeroFactura
                                            . ' '
                                            . $factura['nombre_razon_social']
                                            . ' '
                                            . $factura['nif']
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >


                                    <td>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $numeroFactura,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $factura['nombre_razon_social'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $factura['nif'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </td>


                                    <td>

                                        <?php

                                        $fecha = date(
                                            'd/m/Y',
                                            strtotime(
                                                $factura['fecha_emision']
                                            )
                                        );

                                        ?>

                                        <?= $fecha ?>

                                    </td>


                                    <td>

                                        <?= number_format(
                                            (float) $factura['base_imponible'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?> €

                                    </td>


                                    <td>

                                        <?= number_format(
                                            (float) $factura['total_iva'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?> €

                                    </td>


                                    <td>

                                        <strong>

                                            <?= number_format(
                                                (float) $factura['total'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?> €

                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        switch ($factura['estado']) {

                                            case 'emitida':

                                                $textoEstado = 'Emitida';

                                                break;

                                            case 'borrador':

                                                $textoEstado = 'Borrador';

                                                break;

                                            case 'anulada':

                                                $textoEstado = 'Anulada';

                                                break;

                                            default:

                                                $textoEstado = ucfirst(
                                                    $factura['estado']
                                                );

                                        }

                                        ?>


                                        <span
                                            class="estado estado-<?= htmlspecialchars(
                                                $factura['estado'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $textoEstado,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <div class="acciones-factura">


                                            <a
                                                href="ver_factura.php?id=<?= (int) $factura['id'] ?>"
                                                class="boton-accion"
                                                title="Ver factura"
                                            >
                                                Ver
                                            </a>


                                            <?php if ($factura['estado'] === 'borrador'): ?>

                                                <a
                                                    href="crear_factura.php?id=<?= (int) $factura['id'] ?>"
                                                    class="boton-accion"
                                                    title="Editar factura"
                                                >
                                                    Editar
                                                </a>

                                            <?php endif; ?>


                                            <?php if ($factura['estado'] === 'emitida'): ?>

                                                <a
                                                    href="generar_pdf.php?id=<?= (int) $factura['id'] ?>"
                                                    class="boton-accion"
                                                    target="_blank"
                                                    title="Ver PDF"
                                                >
                                                    PDF
                                                </a>

                                            <?php endif; ?>


                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="sin-facturas">

                    <div class="sin-facturas-icono">
                        📄
                    </div>

                    <h3>
                        Todavía no hay facturas
                    </h3>

                    <p>
                        Crea tu primera factura para empezar a utilizar
                        el sistema de facturación.
                    </p>

                    <a
                        href="crear_factura.php"
                        class="boton-principal"
                    >
                        + Crear primera factura
                    </a>

                </div>


            <?php endif; ?>


        </section>

    </main>


    <!-- ==========================================
         JAVASCRIPT
    ========================================== -->

    <script src="js/facturas.js"></script>

</body>

</html>
