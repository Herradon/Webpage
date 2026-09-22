<?php

session_start();

require_once 'config.php';

/*
|--------------------------------------------------------------------------
| COMPROBAR SI EL USUARIO HA INICIADO SESIÓN
|--------------------------------------------------------------------------
*/

$usuarioLogueado = isset($_SESSION['usuario_id']);


/*
|--------------------------------------------------------------------------
| COMPROBAR SI DEBE MOSTRARSE LA POLÍTICA DE PRIVACIDAD
|--------------------------------------------------------------------------
*/

$mostrarPoliticaPrivacidad = (
    $usuarioLogueado &&
    isset($_SESSION['mostrar_politica_privacidad']) &&
    $_SESSION['mostrar_politica_privacidad'] === true
);

?>

<?php if ($mostrarPoliticaPrivacidad): ?>

    <?php include 'politica_privacidad.php'; ?>

<?php endif; ?>


<!DOCTYPE html>
<html lang="es">

<head>

    <script
        id="Cookiebot"
        src="https://consent.cookiebot.com/uc.js"
        data-cbid="c73a4920-9b86-4f59-b4e9-a3f8e1dfba1d"
        data-blockingmode="auto"
        type="text/javascript">
    </script>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>ViziuneAI</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        /* ==========================================================
           AVISO DE ACCESO PRIVADO
        ========================================================== */

        .acceso-privado-aviso {

            max-width: 1000px;

            margin: 25px auto;

            padding: 20px 25px;

            color: #ffffff;

            box-sizing: border-box;

        }

        .acceso-privado-aviso strong {

            display: block;

            margin-bottom: 8px;

            color: #00cfe0;

            font-size: 18px;

        }

        .acceso-privado-aviso p {

            margin: 0 0 15px;

            color: #b7c5d3;

            line-height: 1.6;

        }

        .boton-iniciar-sesion {

            display: inline-block;

            padding: 10px 18px;

            background: #00cfe0;

            color: #061018;

            text-decoration: none;

            border-radius: 8px;

            font-weight: 700;

        }

        .boton-iniciar-sesion:hover {

            opacity: 0.9;

        }

    </style>

</head>


<body>


<header
    class="header"
    style="position:fixed;">

    <?php include 'menu.php'; ?>

</header>


<main>


<!-- =========================================================
     HERO
========================================================= -->

<section id="inicio" class="hero">

    <canvas id="neural-canvas"></canvas>


    <div class="container hero-content">

        <h1>
            Descubre lo que la inteligencia artificial puede hacer por tu negocio
        </h1>


        <p>

            No necesitas saber exactamente qué necesitas. En ViziuneAI te ayudamos a descubrirlo.
            Si ya tienes una página web, empieza analizándola con nuestra Auditoría SEO. Obtendrás una visión de su estado y un resumen de los principales aspectos que puedes mejorar.
            Después, lleva ese análisis a ViziuneAI y habla con nuestros agentes de inteligencia artificial. Podrás plantear tus dudas, explorar ideas y descubrir qué soluciones pueden encajar mejor con tu negocio.
            Si todavía no tienes una web o simplemente tienes una idea, puedes empezar directamente hablando con ViziuneAI.
            Cuando tengas claro lo que necesitas, podrás solicitar presupuesto sin tener que empezar de nuevo: la conversación recoge todo el contexto, las dudas y las ideas que han surgido durante el proceso.
            Analiza tu web. Explora tus posibilidades. Encuentra la solución.

        </p>

    </div>

</section>


<!-- =========================================================
     ESPECIALIDADES
========================================================= -->

<section>

    <div class="text-intro">


        <div class="d1">

            <img
                src="img/agentes.png"
                alt="">

            <h1>
                Agentes personalizados
            </h1>

            <p>

                Tenemos agentes de inteligencia artificial adaptados a las necesidades de tu negocio, con funciones y respuestas personalizadas para ayudarte a automatizar tareas, atender a tus clientes y mejorar diferentes procesos de tu día a día.

            </p>

        </div>


        <div class="d2">

            <img
                src="img/facturacion.png"
                alt="">

            <h1>
                Facturación
            </h1>

            <p>

                Genera y gestiona tus facturas de forma sencilla, organizada y profesional, manteniendo toda la información necesaria para llevar el control de tu facturación.

            </p>

        </div>


        <div class="d3">

            <img
                src="img/seo.png"
                alt="">

            <h1>
                Auditoria SEO
            </h1>

            <p>

               Analiza tu página web y descubre los principales aspectos que puedes mejorar para optimizar su posicionamiento, visibilidad y rendimiento en los buscadores.

            </p>

        </div>


        <div class="d4">

            <img
                src="img/seguridad.png"
                alt="">

            <h1>
                Seguridad
            </h1>

            <p>

               Protege tu presencia online revisando los aspectos esenciales de seguridad de tu página web y mantén tu información y tus sistemas más protegidos.

            </p>

        </div>


    </div>

</section>


<!-- =========================================================
     CHAT
========================================================= -->

<section
    id="chat"
    class="chat-section">

    <div class="container">


        <div class="section-title">

            <h1>

                De nuestro asistente
                <span>al correo</span>

            </h1>


            <p>

                A partir de aquí es donde toda la conversación
                con nuestro asistente pasa a otro nivel, puedes
                elegir qué especialista quieres consultar y cuando
                termines enviar toda la conversación a nuestro equipo.

            </p>


            <br>


            <p class="agent-selector-description">

                - 1) Si ya tienes una página web y quieres saber qué
                tal está o qué aspectos puedes mejorar, pásate primero
                por nuestra Auditoría SEO. Analizaremos tu web y te
                proporcionaremos un resumen con los principales aspectos
                a mejorar. Una vez lo hayas copiado, pégalo en nuestro
                Asesor SEO/SEM para que pueda ayudarte a trabajar sobre ellos.

                <br><br>

                - 2) Si todavía no tienes página web, estás en el lugar indicado:

                <br>

                - Habla con nuestro asesor y cuéntale qué necesitas para tu proyecto.

                <br>

                - Si prefieres una atención más personalizada, puedes solicitar
                una reunión para que te llamemos o realizar una videoconferencia
                y resolver tus dudas directamente.

                <br>

                - Cuando hayas terminado la conversación y hayas solicitado tu cita,
                si quieres dar el siguiente paso, escribe «quiero contactar»,
                «quiero hablar» o «quiero pedir un presupuesto». Aparecerá un botón
                debajo del chat desde el que podrás enviarnos la conversación,
                permitiéndonos conocer tus necesidades y dudas para poder ofrecerte
                una atención más personalizada.

            </p>

        </div>


        <?php if (!$usuarioLogueado): ?>

            <!-- =================================================
                 AVISO DE ACCESO PRIVADO
            ================================================== -->

            <div class="acceso-privado-aviso">

                <strong>
                    🔐 Funciones privadas del asistente
                </strong>

                <p>

                    Puedes consultar y utilizar el asistente sin iniciar sesión.
                    Para solicitar una reunión o enviar la conversación a nuestro
                    equipo y poder recibir atención personalizada, necesitas
                    acceder a tu cuenta.

                </p>

                <a
                    href="login.php"
                    class="boton-iniciar-sesion">

                    Iniciar sesión

                </a>

            </div>

        <?php endif; ?>


        <div class="agent-selector">


            <div class="chat-box">


                <!-- =================================================
                     CABECERA
                ================================================== -->

                <div class="chat-header">


                    <div class="chat-intro">


                        <div class="assistant-avatar">

                            <img
                                src="img/asesoramiento.png"
                                alt="Asistente de Diseño y Desarrollo Web">

                        </div>


                        <div class="chat-intro-info">

                            <strong id="assistantName">

                                Diseño y Desarrollo Web

                            </strong>

                        </div>


                        <button
                            type="button"
                            id="resetChat"
                            class="reset-chat"
                            title="Reiniciar chat">

                            <img
                                src="img/reload.svg"
                                alt="Reiniciar chat">

                        </button>


                    </div>


                    <div class="agent-buttons">


                        <button
                            type="button"
                            class="agent-button active"
                            data-agent="diseño y desarrollo web">

                            Diseño y desarrollo web

                        </button>


                        <button
                            type="button"
                            class="agent-button"
                            data-agent="tiendas online">

                            Tiendas online

                        </button>


                        <button
                            type="button"
                            class="agent-button"
                            data-agent="asesor seo y sem">

                            Asesor SEO y SEM

                        </button>


                        <button
                            type="button"
                            class="agent-button"
                            data-agent="asesoramiento web">

                            Asesoramiento web

                        </button>


                    </div>

                </div>


                <!-- =================================================
                     MENSAJES
                ================================================== -->

                <div
                    id="chatMessages"
                    class="chat-messages">
                </div>


                <!-- =================================================
                     FORMULARIO CHAT
                ================================================== -->

                <form
                    id="chatForm"
                    class="chat-input">


                    <input
                        type="text"
                        id="message"
                        name="message"
                        placeholder="Escribe tu mensaje..."
                        autocomplete="off"
                        required>


                    <label class="reunion-check">

                        <input
                            type="checkbox"
                            id="chatReunion">

                        <h3>
                            Solicitar reunión
                        </h3>

                    </label>


                    <button
                        type="submit"
                        title="Enviar mensaje">

                        <img
                            id="arrow"
                            src="img/arrow.svg"
                            alt="Enviar">

                    </button>


                </form>


            </div>


            <!-- =================================================
                 BOTÓN EMAIL
            ================================================== -->

            <div class="whatsapp-tittle">


                <div class="chat-whatsapp-container">

                    <?php if ($usuarioLogueado): ?>

                        <button
                            type="button"
                            id="sendChatEmail"
                            hidden>

                            Enviar conversación por correo

                        </button>

                    <?php endif; ?>

                </div>


                <!-- =================================================
                     FORMULARIO EMAIL
                ================================================== -->

                <?php if ($usuarioLogueado): ?>

                    <div
                        id="chatEmailForm"
                        class="chat-email-form"
                        hidden>


                        <div class="form-group">

                            <input
                                type="text"
                                id="chatNombre"
                                name="chatNombre"
                                placeholder="Tu nombre"
                                autocomplete="name">

                        </div>


                        <div class="form-group">

                            <input
                                type="email"
                                id="chatEmail"
                                name="chatEmail"
                                placeholder="Tu correo electrónico"
                                autocomplete="email">

                        </div>


                        <div class="form-group">

                            <input
                                type="file"
                                id="chatFile"
                                name="chatFile"
                                accept="image/*,.pdf">

                        </div>


                        <button
                            type="button"
                            id="confirmSendChatEmail">

                            Enviar conversación

                        </button>


                    </div>

                <?php endif; ?>


                <p>


            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     CONTACTO
========================================================= -->

<section
    id="contacto"
    class="contact-section">


    <div class="container">


        <div class="section-title">

            <h1>
                CONTACTO
            </h1>


            <h2>
                ¿Quieres hablar con nosotros?
            </h2>


            <p>
                Rellena el formulario y nos pondremos en contacto contigo.
            </p>

        </div>


        <div class="contact-card">


            <form id="contactForm">


                <div class="form-grid">


                    <div class="form-group">

                        <input
                            type="text"
                            name="nombre"
                            required
                            placeholder="Tu nombre">

                    </div>


                    <div class="form-group">

                        <input
                            type="text"
                            name="Empresa"
                            required
                            placeholder="Nombre de la empresa u organización">

                    </div>


                    <div class="form-group full">

                        <input
                            type="email"
                            name="email"
                            required
                            placeholder="Correo electrónico de contacto">

                    </div>


                    <div class="form-group full">

                        <textarea
                            name="mensaje"
                            rows="5"
                            required
                            placeholder="Cuéntanos qué necesitas..."></textarea>

                    </div>


                </div>


                <button
                    type="submit"
                    class="whatsapp-button">

                    Contactar por WhatsApp

                </button>


                <div id="formResult"></div>


            </form>


        </div>

    </div>

</section>

</main>


<!-- =========================================================
     MODAL REUNIÓN
========================================================= -->

<div
    id="reunionModal"
    class="reunion-modal"
    aria-hidden="true">


    <div
        class="reunion-modal-overlay"
        id="reunionModalOverlay">
    </div>


    <div
        class="reunion-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="reunionModalTitle">


        <div class="reunion-modal-header">


            <div>

                <span class="reunion-modal-label">
                    REUNIÓN
                </span>


                <h2 id="reunionModalTitle">
                    Solicitar una reunión
                </h2>

            </div>


            <button
                type="button"
                id="closeReunionModal"
                class="reunion-modal-close"
                aria-label="Cerrar">

                &times;

            </button>


        </div>


        <div class="reunion-modal-body">


            <p>

                Selecciona la fecha y hora que prefieres para la reunión.

            </p>


            <div class="form-group">


                <label>
                    Fecha de la reunión
                </label>


                <div class="calendar-container">


                    <div class="calendar-header">


                        <button
                            type="button"
                            id="calendarPrev"
                            class="calendar-nav"
                            aria-label="Mes anterior">

                            ‹

                        </button>


                        <h3 id="calendarMonth">
                            Septiembre 2026
                        </h3>


                        <button
                            type="button"
                            id="calendarNext"
                            class="calendar-nav"
                            aria-label="Mes siguiente">

                            ›

                        </button>


                    </div>


                    <div class="calendar-weekdays">

                        <span>L</span>
                        <span>M</span>
                        <span>X</span>
                        <span>J</span>
                        <span>V</span>
                        <span>S</span>
                        <span>D</span>

                    </div>


                    <div
                        id="calendarDays"
                        class="calendar-days">
                    </div>


                </div>


                <input
                    type="hidden"
                    id="chatFechaReunion"
                    name="chatFechaReunion">


                <p
                    id="selectedDate"
                    class="selected-date">

                    Selecciona una fecha

                </p>


            </div>


            <div class="form-group">


                <label for="chatHoraReunion">
                    Hora de la reunión
                </label>


                <input
                    type="time"
                    id="chatHoraReunion"
                    name="chatHoraReunion">


            </div>


            <button
                type="button"
                id="confirmReunion"
                class="reunion-confirm-button">

                Confirmar reunión

            </button>


        </div>

    </div>

</div>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer>

    <div class="container">

        <p>
            © <?php echo date("Y"); ?> ViziuneAI
        </p>

    </div>

</footer>


<!-- =========================================================
     ESTADO DE SESIÓN PARA JAVASCRIPT
========================================================= -->

<script>

    window.usuarioLogueado =
        <?php echo $usuarioLogueado ? 'true' : 'false'; ?>;

</script>


<script src="js/app.js"></script>


</body>

</html>