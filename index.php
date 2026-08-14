<!-- http://localhost:8080/chatbot/#chat -->

<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mi Web | Asistente IA</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="header">

    <div class="container nav">

        <div class="logo">
         Virsual Solutions
        </div>

        <nav>
            <a href="#inicio">Inicio</a>
            <a href="#chat">Asistente IA</a>
            <a href="#contacto">Contacto</a>
        </nav>

    </div>

</header>


<main>

    <!-- HERO -->

    <section id="inicio" class="hero">

        <div class="container hero-content">

            <div>

                <h1>
                    Habla con nuestro
                    <span>asistente inteligente</span>
                </h1>

                <p>
                    Transformamos la forma en la que trabajan usando agentes de inteligencia artificial capaces de atender, responder y automatizar tareas de forma inteligente. Nuestros agentes pueden interactuar con clientes, resolver consultas, gestionar solicitudes, recopilar información y asistir en diferentes procesos del negocio durante las 24 horas del día. El objetivo es combinar la potencia de la inteligencia artificial con una experiencia cercana y personalizada, ayudando a las empresas a ahorrar tiempo, mejorar su atención al cliente y aumentar su productividad.
                    Creamos agentes adaptados a las necesidades de cada negocio para aplicarlos en una página werb aportando soluciones integradas con WhatsApp y otros servicios del mismo.
                </p>

                <a href="#chat" class="btn">
                    Hablar con IA
                </a>

            </div>

        </div>

    </section>


    <!-- CHAT -->

    <section id="chat" class="chat-section">

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

                <div class="chat-header">

                    <div class="assistant-avatar">
                        
                        <img src="img/asset.png" alt="">

                    </div>

                    <div>
                        <strong>Alejandro Herradón, tu Asistente de confiaza</strong>

                        <small>
                            ● Asesoramiento personalizado las 24 horas
                        </small>
                    </div>

                      <button type="button" id="resetChat" class="reset-chat">
                            🔄
                        </button>

                </div>


                <div id="chatMessages" class="chat-messages">

                    <div class="message bot">

                    

                    </div>

                </div>


                <form id="chatForm" class="chat-input">

                    <input
                        type="text"
                        id="message"
                        name="message"
                        placeholder="Escribe tu mensaje..."
                        autocomplete="off"
                        required>


                    <button type="submit">
                        ▶️
                    </button>

                </form>

            </div>

        </div>

    </section>


    <!-- CONTACTO -->

    <section id="contacto" class="contact-section">

        <div class="container">

            <div class="section-title">

                <span>CONTACTO</span>

                <h2>
                    ¿Quieres hablar con nosotros?
                </h2>

                <p>
                    Rellena el formulario y continúa la conversación
                    directamente por WhatsApp.
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

                           

                            <input type="email"
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
                        class="whatsapp-button"
                    >

                        <span>💬</span>

                        Contactar por WhatsApp

                    </button>

                    <div id="formResult"></div>

                </form>

            </div>

        </div>

    </section>

</main>


<footer>

    <div class="container">

        <p>
            © <?php echo date("Y"); ?> Mi Empresa
        </p>

    </div>

</footer>


<script src="js/app.js"></script>

</body>
</html>