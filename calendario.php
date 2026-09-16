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
   ACCESO GRATUITO
========================================== */

/*
|--------------------------------------------------------------------------
| Todos los usuarios con una cuenta activa
| pueden utilizar el calendario gratuitamente.
|--------------------------------------------------------------------------
|
| La suscripción ya no es necesaria para acceder.
|--------------------------------------------------------------------------
*/

$_SESSION['suscripcion_activa'] = 1;

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Calendario | ViziuneAI</title>

    <link rel="stylesheet" href="css/calendario.css">

</head>

<body>


<header class="header">

    

        <?php include 'menu.php'; ?>
       
    

</header>




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