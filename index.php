<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>ViziuneAI</title>

    <link rel="stylesheet" href="css/style.css?v=4">

</head>


<body>


<!-- ==========================================
     HEADER
========================================== -->

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



<main>


<!-- ==========================================
     HERO
========================================== -->

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
            negocio para aplicarlos en una página web, aportando
            soluciones integradas con WhatsApp, correo electrónico
            y otros servicios.

        </p>

    </div>

</section>



<!-- ==========================================
     CHAT
========================================== -->

<section id="chat"
         class="chat-section">

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
             SELECTOR DE ESPECIALIDAD
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
                     CABECERA DEL CHAT
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



                        <!-- INFORMACIÓN DEL AGENTE -->

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



                        <!-- BOTÓN REINICIAR -->

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
                         BOTONES DE ESPECIALIDAD
                    ================================== -->

                    <div class="agent-buttons">


                        <!-- DISEÑO Y DESARROLLO WEB -->

                        <button
                            type="button"
                            class="agent-button active"
                            data-agent="diseño y desarrollo web">

                            Diseño y desarrollo web

                        </button>



                        <!-- TIENDAS ONLINE -->

                        <button
                            type="button"
                            class="agent-button"
                            data-agent="tiendas online">

                            Tiendas online

                        </button>



                        <!-- SEO Y SEM -->

                        <button
                            type="button"
                            class="agent-button"
                            data-agent="asesor seo y sem">

                            Asesor SEO y SEM

                        </button>



                        <!-- ASESORAMIENTO WEB -->

                        <button
                            type="button"
                            class="agent-button"
                            data-agent="asesoramiento web">

                            Asesoramiento web

                        </button>


                    </div>


                </div>



                <!-- ==================================
                     MENSAJES DEL CHAT
                ================================== -->

                <div
                    id="chatMessages"
                    class="chat-messages">

                </div>



                <!-- ==================================
                     FORMULARIO DEL CHAT
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
             ENVÍO DE CONVERSACIÓN
        =========================================== -->

        <div class="whatsapp-tittle">


            <div class="chat-whatsapp-container">


                <!-- BOTÓN OCULTO INICIALMENTE -->

                <button
                    type="button"
                    id="sendChatEmail"
                    hidden>

                    📧 Enviar conversación por correo

                </button>


            </div>



            <!-- ======================================
                 FORMULARIO DATOS DEL CLIENTE
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



                <!-- CONFIRMAR ENVÍO -->

                <button
                    type="button"
                    id="confirmSendChatEmail">

                    📧 Enviar conversación

                </button>


            </div>



            <!-- TEXTO INFORMATIVO -->

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


        <!-- ======================================
             TÍTULO CONTACTO
        ======================================= -->

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



        <!-- ======================================
             TARJETA CONTACTO
        ======================================= -->

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



                <!-- ==================================
                     BOTÓN WHATSAPP
                ================================== -->

                <button
                    type="submit"
                    class="whatsapp-button">

                    <span>
                        💬
                    </span>

                    Contactar por WhatsApp

                </button>



                <!-- RESULTADO -->

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



<!-- ==========================================
     JAVASCRIPT
========================================== -->

<script src="js/app.js?v=4"></script>


</body>

</html>