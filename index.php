<?php
session_start();
?>

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


</head>

<body>

<!-- ==========================================
     HEADER
========================================== -->

<!--
<header class="header">

    <div class="container nav">

        <div class="logo">

            <div class="logo-v">
                V
            </div>

            <div class="logo-text">
                IZIUNE
            </div>

        </div>

        <nav>

            <a href="#inicio">
                Inicio
            </a>

            <a href="#chat">
                Asistente IA
            </a>

            <a href="#contacto">
                Contacto
            </a>

        </nav>

    </div>

</header>
-->

<main>

<!-- ==========================================
     HERO
========================================== -->

<section id="inicio" class="hero">



<div class="container hero-content">

    <h1>

        Bienvenido a VIZIUNE AI

        <span>
            la red neuronal de tus futuros agentes de confianza
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

        <br><br>

        Creamos agentes adaptados a las necesidades de cada
        negocio para aplicarlos en una página web, aportando
        soluciones integradas con WhatsApp, correo electrónico
        y otros servicios.

    </p>

</div>

</section>

<!-- ==========================================
     ESPECIALIDADES
========================================== -->

<section>

<div class="text-intro">

    <div class="d1">

        <h1>
            Desarrollo
        </h1>

        <p>
            Informamos y asesoramos sobre desarrollo web,
            programación, funcionalidades, tecnología y
            creación de páginas web.
        </p>

    </div>


    <div class="d2">

        <h1>
            Ventas
        </h1>

        <p>
            Orientamos al cliente sobre servicios, estructura,
            necesidades, presupuestos, contratación y posibles
            soluciones.
        </p>

    </div>


    <div class="d3">

        <h1>
            Análisis
        </h1>

        <p>
            Analizamos la situación del cliente, detectamos
            problemas, necesidades y oportunidades de mejora.
        </p>

    </div>


    <div class="d4">

        <h1>
            Asesoramiento
        </h1>

        <p>
            Ofrecemos orientación general y ayudamos al cliente
            a determinar qué solución puede necesitar.
        </p>

    </div>

</div>

</section>

<!-- ==========================================
     CHAT
========================================== -->

<section id="chat" class="chat-section">

    <canvas id="neural-canvas"></canvas>

    <div class="container">


    <!-- ======================================
         TÍTULO
    ======================================= -->

    <div class="section-title">

        <h1>

            De nuestro asistente

            <span>
                al correo
            </span>

        </h1>

        <p>

            A partir de aquí es donde toda la conversación
            con nuestro asistente pasa a otro nivel. Puedes
            elegir qué especialista quieres consultar y,
            cuando termines, enviar toda la conversación
            a nuestro equipo.

        </p>

    </div>


    <!-- ======================================
         SELECTOR
    ======================================= -->

    <div class="agent-selector">

        <h3>
            ¿Qué necesitas?
        </h3>


        <p class="agent-selector-description">

            Selecciona el área que mejor se adapte
            a lo que necesitas.

        </p>


        <!-- ==================================
             CHAT BOX
        ================================== -->

        <div class="chat-box">


            <!-- ==================================
                 CABECERA
            ================================== -->

            <div class="chat-header">


                <div class="chat-intro">


                    <!-- AVATAR -->

                    <div class="assistant-avatar">

                        <img
                            id="assistantAvatar"
                            src="img/asset.png"
                            alt="Asistente de Diseño y Desarrollo Web">

                    </div>


                    <!-- INFORMACIÓN -->

                    <div class="chat-intro-info">

                        <strong id="assistantName">

                            Alejandro Herradón,
                            tu Asesor en Diseño y Desarrollo Web

                        </strong>


                        <small id="assistantDescription">

                            ● Diseño y desarrollo de páginas web
                            profesionales, modernas y adaptadas
                            a las necesidades de tu negocio.

                        </small>

                    </div>


                    <!-- REINICIAR -->

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


                <!-- ==================================
                     BOTONES DE AGENTE
                ================================== -->

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


            <!-- ==================================
                 MENSAJES
            ================================== -->

            <div
                id="chatMessages"
                class="chat-messages">
            </div>


            <!-- ==================================
                 FORMULARIO CHAT
            ================================== -->

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


                <!-- ==================================
                     SOLICITAR REUNIÓN
                ================================== -->

                <label class="reunion-check">

                    <input
                        type="checkbox"
                        id="chatReunion">

                    Solicitar reunión

                </label>


                <!-- ==================================
                     BOTÓN ENVIAR
                ================================== -->

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


    </div>


    <!-- ==========================================
         ENVÍO CONVERSACIÓN
    =========================================== -->

    <div class="whatsapp-tittle">


        <div class="chat-whatsapp-container">


            <button
                type="button"
                id="sendChatEmail"
                hidden>

                Enviar conversación por correo

            </button>


        </div>


        <!-- ======================================
             DATOS CLIENTE
        ======================================= -->

        <div
            id="chatEmailForm"
            class="chat-email-form"
            hidden>


            <p>

                Para poder enviar la conversación
                a nuestro equipo y que podamos
                contactar contigo, introduce tus datos:

            </p>


            <!-- NOMBRE -->

            <div class="form-group">

                <input
                    type="text"
                    id="chatNombre"
                    name="chatNombre"
                    placeholder="Tu nombre"
                    autocomplete="name">

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <input
                    type="email"
                    id="chatEmail"
                    name="chatEmail"
                    placeholder="Tu correo electrónico"
                    autocomplete="email">

            </div>


            <!-- ARCHIVO -->

            <div class="form-group">

                <input
                    type="file"
                    id="chatFile"
                    name="chatFile"
                    accept="image/*,.pdf">

            </div>


            <!-- CONFIRMAR -->

            <button
                type="button"
                id="confirmSendChatEmail">

                Enviar conversación

            </button>


        </div>


        <!-- TEXTO -->

        <p>

            Si durante la conversación necesitas
            contactar directamente con nuestro equipo,
            puedes enviar la conversación por correo
            electrónico y nos pondremos en contacto
            contigo.

        </p>


    </div>


    </div>


</section>

<!-- ==========================================
     CONTACTO
========================================== -->

<section
    id="contacto"
    class="contact-section">


<div class="container">


    <div class="section-title">

        <span>
            CONTACTO
        </span>

        <h2>
            ¿Quieres hablar con nosotros?
        </h2>

        <p>

            Rellena el formulario y nos pondremos
            en contacto contigo.

        </p>

    </div>


    <div class="contact-card">


        <form id="contactForm">


            <div class="form-grid">


                <!-- NOMBRE -->

                <div class="form-group">

                    <input
                        type="text"
                        name="nombre"
                        required
                        placeholder="Tu nombre">

                </div>


                <!-- EMPRESA -->

                <div class="form-group">

                    <input
                        type="text"
                        name="Empresa"
                        required
                        placeholder="Nombre de la empresa u organización">

                </div>


                <!-- EMAIL -->

                <div class="form-group full">

                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="Correo electrónico de contacto">

                </div>


                <!-- MENSAJE -->

                <div class="form-group full">

                    <textarea
                        name="mensaje"
                        rows="5"
                        required
                        placeholder="Cuéntanos qué necesitas..."
                    ></textarea>

                </div>


            </div>


            <button
                type="submit"
                class="whatsapp-button">

                <span>
                    💬
                </span>

                Contactar por WhatsApp

            </button>


            <div id="formResult"></div>


        </form>


    </div>


</div>

</section>

</main>

<!-- ==========================================
     MODAL SOLICITAR REUNIÓN
========================================== -->

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


    <!-- CABECERA -->

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


    <!-- CONTENIDO -->

    <div class="reunion-modal-body">

        <p>
            Selecciona la fecha y hora que prefieres
            para la reunión.
        </p>


        <!-- ======================================
             CALENDARIO
        ======================================= -->

        <div class="form-group">

            <label>
                Fecha de la reunión
            </label>


            <div class="calendar-container">


                <!-- CABECERA CALENDARIO -->

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


                <!-- DÍAS DE LA SEMANA -->

                <div class="calendar-weekdays">

                    <span>L</span>
                    <span>M</span>
                    <span>X</span>
                    <span>J</span>
                    <span>V</span>
                    <span>S</span>
                    <span>D</span>

                </div>


                <!-- DÍAS -->

                <div
                    id="calendarDays"
                    class="calendar-days">
                </div>


            </div>


            <!-- ==================================
                 CAMPO OCULTO
                 Aquí se guarda:
                 YYYY-MM-DD
            ================================== -->

            <input
                type="hidden"
                id="chatFechaReunion"
                name="chatFechaReunion">


            <!-- FECHA SELECCIONADA -->

            <p
                id="selectedDate"
                class="selected-date">

                Selecciona una fecha

            </p>

        </div>


        <!-- ======================================
             HORA
        ======================================= -->

        <div class="form-group">

            <label for="chatHoraReunion">
                Hora de la reunión
            </label>

            <input
                type="time"
                id="chatHoraReunion"
                name="chatHoraReunion">

        </div>


        <!-- ======================================
             CONFIRMAR
        ======================================= -->

        <button
            type="button"
            id="confirmReunion"
            class="reunion-confirm-button">

            Confirmar reunión

        </button>


    </div>

</div>


</div>

<!-- ==========================================
     FOOTER
========================================== -->

<footer>


<div class="container">

    <p>

        © <?php echo date("Y"); ?>
        ViziuneAI

    </p>

</div>


</footer>

<!-- ==========================================
     JAVASCRIPT
========================================== -->

<script src="js/app.js"></script>

</body>

</html>
