
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


/* ==========================================
   BLOQUEAR CALENDARIO SIN SUSCRIPCIÓN
========================================== */

if (!$suscripcionActiva) {

    $_SESSION['suscripcion_activa'] = 0;

    header('Location: suscripcion.php');

    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Calendario | ViziuneAI</title>

    <link rel="stylesheet" href="css/calendario.css">

</head>

<header>

<?php include 'menu.php'; ?>

</header>

<body>


    <main class="calendario-contenedor">


        <header class="calendario-header">


            <button
                type="button"
                id="mesAnterior"
                class="boton-mes"
            >
                ‹
            </button>

            <h1 id="mesActual">
                Cargando...
            </h1>

            

            <button
                type="button"
                id="mesSiguiente"
                class="boton-mes"
            >
                ›
            </button>

        </header>


        <section class="calendario">

            <div class="dias-semana">

                <div>Lun</div>
                <div>Mar</div>
                <div>Mié</div>
                <div>Jue</div>
                <div>Vie</div>
                <div>Sáb</div>
                <div>Dom</div>

            </div>


            <div
                id="diasCalendario"
                class="dias-calendario"
            >
            </div>

        </section>


        <section
            id="detalleReunion"
            class="detalle-reunion"
            hidden
        >

            <button
                type="button"
                id="cerrarDetalle"
                class="cerrar-detalle"
            >
                ×
            </button>

            <h2>Reunión</h2>

            <div id="contenidoReunion"></div>

        </section>

    </main>


    <script src="js/calendario.js"></script>

</body>

</html>