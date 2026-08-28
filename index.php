<!-- http://localhost:8080/chatbot/#chat -->
<!-- http://localhost:8080/phpmyadmin/ -->

<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>ViziuneAI.com</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>


<header class="header">

    <div class="container nav">

        <div class="logo">
            ViziuneAI
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


<main>


<!-- ==========================================
     HERO
=========================================== -->

<section id="inicio"
         class="hero">

    <div class="container hero-content">

        <h1>

            Habla con nuestro

            <span>
                asistente inteligente
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
            negocio para aplicarlos en una página web aportando
            soluciones integradas con WhatsApp y otros servicios.

        </p>

    </div>

</section>


<!-- ==========================================
     CHAT
=========================================== -->

<section id="chat"
         class="chat-section">

    <div class="container">


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


        <!-- ==========================================
             SELECTOR DE IDENTIDAD
        =========================================== -->

        <div class="agent-selector">

            <h3>
                ¿Con quién quieres hablar?
            </h3>

            <p class="agent-selector-description">

                Selecciona el especialista que mejor se adapte
                a lo que necesitas.

            </p>


        <!-- ==========================================
             CHAT BOX
        =========================================== -->

        <div class="chat-box">


            <!-- CABECERA -->

            <div class="chat-header">


                <div class="chat-intro">

                    <div class="assistant-avatar">

                    <img
                        id="assistantAvatar" src="img/asset.png" alt="Asistente">

                    </div>


                    <div>

                        <strong id="assistantName">

                        Alejandro Herradón,
                        tu Asistente de confianza

                        </strong>


                        <small id="assistantDescription">

                        ● Asesoramiento personalizado
                        las 24 horas

                        </small>

                    </div>


                    <button
                        type="button"
                        id="resetChat"
                        class="reset-chat"
                        title="Reiniciar chat">

                        🔄

                    </button>

                </div>

                <!-- ==========================================
                     Identidades
                =========================================== -->

                <div class="agent-buttons">

    <button
        type="button"
        class="agent-button active"
        data-agent="asesor inmobiliario">
        Asesor inmobiliario
    </button>


    <button
        type="button"
        class="agent-button"
        data-agent="asesor laboral">
        Asesor laboral
    </button>


    <button
        type="button"
        class="agent-button"
        data-agent="asesor legal">
        Asesor legal
    </button>


    <button
        type="button"
        class="agent-button"
        data-agent="asesor financiero">
        Asesor financiero
    </button>

</div>

            </div>


            <!-- MENSAJES -->

            <div
                id="chatMessages"
                class="chat-messages">

            </div>


            <!-- INPUT -->

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


                <button
                    type="submit"
                    title="Enviar mensaje">

                    ▶️

                </button>

            </form>

        </div>

    </div>


    <!-- ==========================================
         ENVÍO DE CONVERSACIÓN
    =========================================== -->

    <div class="whatsapp-tittle">


        <div class="chat-whatsapp-container">

            <button
                type="button"
                id="sendChatEmail"
                hidden>

                📧 Enviar conversación por correo

            </button>

        </div>


        <!-- DATOS CLIENTE -->

        <div
            id="chatEmailForm"
            class="chat-email-form"
            hidden>


            <p>

                Para poder enviarte la conversación
                y que nuestro equipo pueda contactar
                contigo, introduce tus datos:

            </p>


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


            <button
                type="button"
                id="confirmSendChatEmail">

                📧 Enviar conversación

            </button>


        </div>


        <p>

            En el caso de que tengas algún problema con
            el soporte o con el asistente, puedes enviarnos
            un mensaje directamente a nuestro WhatsApp
            completando el formulario y te atenderemos
            lo antes posible.

        </p>

    </div>

</section>


<!-- ==========================================
     CONTACTO
=========================================== -->

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

                Rellena el formulario y continúa
                la conversación directamente
                por WhatsApp.

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
                            placeholder="Tu nombre del solicitante">

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


<script src="js/app.js"></script>

</body>

</html>