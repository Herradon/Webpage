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
                    Resuelve tus dudas rápidamente utilizando nuestro
                    asistente basado en inteligencia artificial.
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

                <span>ASISTENTE VIRTUAL</span>

                <h2>
                    ¿En qué podemos ayudarte?
                </h2>

                <p>
                    Escribe tu pregunta y nuestro asistente te responderá.
                </p>

            </div>


            <div class="chat-box">

                <div class="chat-header">

                    <div class="assistant-avatar" style="background: none; width: 70px; height: 70px;">
                        
                       <div style=" width: 50px; height: 50px; justify-content: center; align-items: center; display: flex;font-size: 50px;">🕵🏻‍♂️</div>
                    </div>

                    <div>
                        <strong>Alejandro Herradón, tu Asistente de confiaza</strong>

                        <small>
                            ● Conectado las 24 horas
                        </small>
                    </div>

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
                        required
                    >

                    <button type="submit">
                        ➤
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

                            <label>
                                Nombre
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                required
                                placeholder="Tu nombre"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Teléfono
                            </label>

                            <input
                                type="tel"
                                name="telefono"
                                required
                                placeholder="Tu teléfono"
                            >

                        </div>


                        <div class="form-group full">

                            <label>
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                required
                                placeholder="tu@email.com"
                            >

                        </div>


                        <div class="form-group full">

                            <label>
                                Mensaje
                            </label>

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