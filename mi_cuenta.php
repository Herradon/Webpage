
<?php

session_start();

require_once 'config.php';


/*
|--------------------------------------------------------------------------
| Comprobar que el usuario ha iniciado sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}


$usuarioId = (int) $_SESSION['usuario_id'];


/*
|--------------------------------------------------------------------------
| Comprobar suscripción activa
|--------------------------------------------------------------------------
*/

$stmtSuscripcion = $pdo->prepare("
    SELECT
        suscripcion_activa,
        suscripcion_fin
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmtSuscripcion->execute([$usuarioId]);

$datosSuscripcion = $stmtSuscripcion->fetch(PDO::FETCH_ASSOC);

$suscripcionActiva = false;

if (
    $datosSuscripcion &&
    (int) $datosSuscripcion['suscripcion_activa'] === 1
) {

    $suscripcionActiva = true;


    /*
    |--------------------------------------------------------------------------
    | Comprobar fecha de finalización
    |--------------------------------------------------------------------------
    */

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
                ")->execute([$usuarioId]);

                $suscripcionActiva = false;

                $_SESSION['suscripcion_activa'] = 0;
            }

        } catch (Exception $e) {

            $suscripcionActiva = false;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Si no tiene suscripción, bloquear acceso
|--------------------------------------------------------------------------
*/

if (!$suscripcionActiva) {

    header('Location: suscripcion.php');
    exit;
}


$usuarioNombre = $_SESSION['usuario_nombre'] ?? '';
$usuarioEmail = $_SESSION['usuario_email'] ?? '';


/*
|--------------------------------------------------------------------------
| Buscar cliente asociado al usuario
|--------------------------------------------------------------------------
*/

$stmtCliente = $pdo->prepare("
    SELECT
        id,
        nombre_razon_social,
        nif,
        direccion,
        codigo_postal,
        ciudad,
        provincia,
        pais,
        email,
        telefono
    FROM clientes
    WHERE usuario_id = ?
      AND activo = 1
    LIMIT 1
");

$stmtCliente->execute([$usuarioId]);

$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Si no existe cliente
|--------------------------------------------------------------------------
*/

if (!$cliente) {

    $error = 'No se ha encontrado tu perfil de cliente.';
    $facturas = [];

} else {

    /*
    |--------------------------------------------------------------------------
    | Guardar cliente en sesión
    |--------------------------------------------------------------------------
    */

    $_SESSION['cliente_id'] = (int) $cliente['id'];


    /*
    |--------------------------------------------------------------------------
    | Buscar SOLO las facturas de este cliente
    |--------------------------------------------------------------------------
    */

    $stmtFacturas = $pdo->prepare("
        SELECT
            id,
            serie,
            numero,
            fecha_emision,
            base_imponible,
            total_iva,
            total_irpf,
            total,
            estado
        FROM facturas
        WHERE cliente_id = ?
        ORDER BY fecha_emision DESC, id DESC
    ");

    $stmtFacturas->execute([
        $cliente['id']
    ]);

    $facturas = $stmtFacturas->fetchAll(PDO::FETCH_ASSOC);

    $error = '';
}


/*
|--------------------------------------------------------------------------
| Calcular estadísticas
|--------------------------------------------------------------------------
*/

$totalFacturas = count($facturas);

$totalEmitidas = 0;
$totalBorradores = 0;
$totalImporte = 0;

foreach ($facturas as $factura) {

    if ($factura['estado'] === 'emitida') {
        $totalEmitidas++;
    }

    if ($factura['estado'] === 'borrador') {
        $totalBorradores++;
    }

    $totalImporte += (float) $factura['total'];
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

    <title>Mi cuenta | ViziuneAI</title>

    <link
        rel="stylesheet"
        href="css/mi-cuenta.css"
    >

</head>

<body>


<header class="topbar">

    <div class="logo">
        VIZIUNE<span>AI</span>
    </div>

    <div class="user-area">

        <span>
            Hola, <?= htmlspecialchars($usuarioNombre) ?>
        </span>

        <a href="logout.php" class="logout-button">
            Cerrar sesión
        </a>

    </div>

</header>


<main class="account-container">


    <!-- CABECERA -->

    <section class="welcome-section">

        <div>

            <h1>Mi cuenta</h1>

            <p>
                Gestiona tus datos y tus facturas desde tu área privada.
            </p>

        </div>

        <a
            href="crear_factura.php"
            class="primary-button"
        >
            + Nueva factura
        </a>

    </section>


    <?php if (!empty($error)): ?>

        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- ESTADÍSTICAS -->

    <section class="stats-grid">

        <div class="stat-card">

            <span class="stat-label">
                Total facturas
            </span>

            <strong>
                <?= $totalFacturas ?>
            </strong>

        </div>


        <div class="stat-card">

            <span class="stat-label">
                Emitidas
            </span>

            <strong>
                <?= $totalEmitidas ?>
            </strong>

        </div>


        <div class="stat-card">

            <span class="stat-label">
                Borradores
            </span>

            <strong>
                <?= $totalBorradores ?>
            </strong>

        </div>


        <div class="stat-card">

            <span class="stat-label">
                Importe total
            </span>

            <strong>
                <?= number_format($totalImporte, 2, ',', '.') ?> €
            </strong>

        </div>

    </section>


    <!-- DATOS DEL CLIENTE -->

    <section class="account-section">

        <div class="section-header">

            <div>

                <h2>Mis datos</h2>

                <p>
                    Datos asociados a tu cuenta.
                </p>

            </div>

        </div>


        <?php if ($cliente): ?>

            <div class="client-data">

                <div class="data-item">

                    <span>Nombre / Razón social</span>

                    <strong>
                        <?= htmlspecialchars(
                            $cliente['nombre_razon_social'] ?? ''
                        ) ?>
                    </strong>

                </div>


                <div class="data-item">

                    <span>NIF / DNI</span>

                    <strong>
                        <?= htmlspecialchars(
                            $cliente['nif'] ?: 'No indicado'
                        ) ?>
                    </strong>

                </div>


                <div class="data-item">

                    <span>Email</span>

                    <strong>
                        <?= htmlspecialchars(
                            $cliente['email'] ?: $usuarioEmail
                        ) ?>
                    </strong>

                </div>


                <div class="data-item">

                    <span>Teléfono</span>

                    <strong>
                        <?= htmlspecialchars(
                            $cliente['telefono'] ?: 'No indicado'
                        ) ?>
                    </strong>

                </div>


                <div class="data-item">

                    <span>Dirección</span>

                    <strong>
                        <?= htmlspecialchars(
                            $cliente['direccion'] ?: 'No indicada'
                        ) ?>
                    </strong>

                </div>


                <div class="data-item">

                    <span>Localidad</span>

                    <strong>

                        <?php

                        $localidad = [];

                        if (!empty($cliente['codigo_postal'])) {
                            $localidad[] = $cliente['codigo_postal'];
                        }

                        if (!empty($cliente['ciudad'])) {
                            $localidad[] = $cliente['ciudad'];
                        }

                        if (!empty($cliente['provincia'])) {
                            $localidad[] = $cliente['provincia'];
                        }

                        echo htmlspecialchars(
                            !empty($localidad)
                                ? implode(', ', $localidad)
                                : 'No indicada'
                        );

                        ?>

                    </strong>

                </div>

            </div>

        <?php endif; ?>

    </section>


    <!-- FACTURAS -->

    <section class="account-section">

        <div class="section-header">

            <div>

                <h2>Mis facturas</h2>

                <p>
                    Consulta y gestiona tus facturas.
                </p>

            </div>

            <a
                href="crear_factura.php"
                class="secondary-button"
            >
                + Crear factura
            </a>

        </div>


        <?php if (empty($facturas)): ?>

            <div class="empty-state">

                <div class="empty-icon">
                    📄
                </div>

                <h3>Todavía no tienes facturas</h3>

                <p>
                    Puedes crear tu primera factura desde aquí.
                </p>

                <a
                    href="crear_factura.php"
                    class="primary-button"
                >
                    Crear mi primera factura
                </a>

            </div>

        <?php else: ?>


            <div class="table-wrapper">

                <table class="invoice-table">

                    <thead>

                        <tr>

                            <th>
                                Factura
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

                            $numeroFactura = $factura['numero']
                                ? $factura['serie'] . '-' . $factura['numero']
                                : 'Borrador #' . $factura['id'];

                            $estado = $factura['estado'];

                            ?>

                            <tr>

                                <td data-label="Factura">

                                    <strong>
                                        <?= htmlspecialchars(
                                            $numeroFactura
                                        ) ?>
                                    </strong>

                                </td>


                                <td data-label="Fecha">

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $factura['fecha_emision']
                                        )
                                    ) ?>

                                </td>


                                <td data-label="Base">

                                    <?= number_format(
                                        (float) $factura['base_imponible'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?> €

                                </td>


                                <td data-label="IVA">

                                    <?= number_format(
                                        (float) $factura['total_iva'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?> €

                                </td>


                                <td data-label="Total">

                                    <strong>
                                        <?= number_format(
                                            (float) $factura['total'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?> €
                                    </strong>

                                </td>


                                <td data-label="Estado">

                                    <?php if ($estado === 'emitida'): ?>

                                        <span class="status status-issued">
                                            Emitida
                                        </span>

                                    <?php elseif ($estado === 'borrador'): ?>

                                        <span class="status status-draft">
                                            Borrador
                                        </span>

                                    <?php else: ?>

                                        <span class="status">
                                            <?= htmlspecialchars(
                                                $estado
                                            ) ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td data-label="Acciones">

                                    <div class="actions">

                                        <a
                                            href="ver_factura.php?id=<?= (int) $factura['id'] ?>"
                                            class="action-button"
                                        >
                                            Ver
                                        </a>


                                        <?php if ($estado === 'borrador'): ?>

                                            <a
                                                href="crear_factura.php?id=<?= (int) $factura['id'] ?>"
                                                class="action-button"
                                            >
                                                Editar
                                            </a>

                                        <?php else: ?>

                                            <a
                                                href="generar_pdf.php?id=<?= (int) $factura['id'] ?>"
                                                class="action-button"
                                                target="_blank"
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

        <?php endif; ?>

    </section>


</main>


<script src="js/mi-cuenta.js"></script>

</body>

</html>
