<!-- http://localhost:8080/chatbot/#chat -->
<!--http://localhost:8080/phpmyadmin/-->

<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>vizuineAI.com</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>


<body>


<header class="header">

    <div class="container nav">

        <div class="logo">
            Visual Solutions
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

            <div>

                <h1>

                    Habla con nuestro

                    <span>
                        asistente inteligente
                    </span>

                </h1>


                <p>

                    Transformamos la forma en la que trabajan usando agentes de inteligencia artificial capaces de atender, responder y automatizar tareas de forma inteligente. Nuestros agentes pueden interactuar con clientes, resolver consultas, gestionar solicitudes, recopilar información y asistir en diferentes procesos del negocio durante las 24 horas del día. El objetivo es combinar la potencia de la inteligencia artificial con una experiencia cercana y personalizada, ayudando a las empresas a ahorrar tiempo, mejorar su atención al cliente y aumentar su productividad.

                    Creamos agentes adaptados a las necesidades de cada negocio para aplicarlos en una página web aportando soluciones integradas con WhatsApp y otros servicios del mismo.

                </p>

            </div>

        </div>

    </section>



    <!-- ==========================================
         CHAT
    =========================================== -->

    <section id="chat"
             class="chat-section">

        <div class="container">


            <div class="section-title">

                <h2>
                    ¿En qué podemos ayudarte?
                </h2>


                <p>
                    Escribe tu pregunta y nuestro asistente te responderá.
                </p>

            </div>



            <div class="chat-box">


                <!-- ==================================
                     CABECERA DEL CHAT
                =================================== -->

                <div class="chat-header">


                    <div class="assistant-avatar">

                        <img
                            src="img/asset.png"
                            alt="Alejandro Herradón">

                    </div>


                    <div>

                        <strong>
                            Alejandro Herradón,
                            tu Asistente de confianza
                        </strong>


                        <small>

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



                <!-- ==================================
                     MENSAJES
                =================================== -->

                <div
                    id="chatMessages"
                    class="chat-messages">


                    <!--
                        Los mensajes del chatbot
                        aparecerán aquí mediante JS.
                    -->


                </div>



                <!-- ==================================
                     INPUT DEL CHAT
                =================================== -->

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


        <div class="whatsapp-tittle">

         <h1>

                    De nuestro asistente

                    <span>
                        a tu whatsapp
                    </span>

                </h1>


                <p>

                    A partir de aquí es donde toda la conversación con nuestro asistente pasa a otro nivel, ya que podrás 
                    terminar la conversación con nuestro equipo de soporte y atención al cliente.
                    Si estas de acuerdo, pulsa el botón de abajo y envía toda la conversación a nuestro whatsapp, para finalizar tu proyecto, asesoramiento o consulta.
                    En el caso de que tengas algun problema con el soporte o con el asistente, puedes enviarnos un mensaje directamente a nuestro whatsapp completando el formulario y te atenderemos lo antes posible.
                </p>

        </div>

         <!-- ==================================
                     BOTÓN WHATSAPP
                =================================== -->

                <div
                    class="chat-whatsapp-container">
                        
                    <button
                        type="button"
                        id="sendChatWhatsApp"
                        class="chat-whatsapp-button"
                        hidden>

                       Enviar chat por whatssap 📩

                    </button>


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
            Mi Empresa

        </p>


    </div>


</footer>



<script src="js/app.js"></script>


</body>

</html>