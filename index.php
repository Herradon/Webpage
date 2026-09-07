<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
</head>

    <!-- ==========================================
     HEADER
========================================== -->

<header class="header">


        <!--<nav>

            <a href="home.php">
                Inicio
            </a>

            <a href="index.php">
                Asistente IA
            </a>

            <a href="contacto.php">
                Contacto
            </a>

        </nav>-->

</header>

  <canvas id="neural-network"></canvas>


<section id="inicio" class="home">

    <div class="container home-content">

        <h1>

             Nuestra red neuronal de

            <span>
            inteligencia artificial
            </span>

        </h1>


        <p>

            Transformamos la forma en la que trabajan usando
            agentes de inteligencia artificial capaces de atender,
            responder y automatizar tareas de forma inteligente.

            Nuestros agentes pueden interactuar con clientes,
            resolver consultas, gestionar solicitudes, recopilar
            información y asistir en diferentes procesos del negocio
            durante las 24 horas del día.

        </p>

    </div>

        <div class="box">

            <div>

                 <img  class="img1" src="img/asesor.png" alt="Asistente de Diseño y Desarrollo Web">
                <h1>Asesoramos</h1>
                <p>Identificamos las oportunidades ocultas de tu negocio mediante la Inteligencia Artificial.</p>

            </div>

            <div>

                <img  class="img2" src="img/desarrollo.png" alt="Asistente de Diseño y Desarrollo Web">
                <h1>Desarrollamos</h1>
                <p>Construimos el cerebro digital que tu empresa necesita.</p>

            </div>

            <div>
                <img  class="img3" src="img/seo.png" alt="Asistente de Diseño y Desarrollo Web">
                <h1>Implementamos</h1>
                <p>Llevamos la teoría a la práctica integrando la IA directamente en tu día a día.</p>

            </div>

        

        </div>

        <a href="home.php" id="btn-probar" class="btn"> Acceder a la red </a>

</section>


<body>
    
</body>
</html>

<script src="js/app.js"></script>

