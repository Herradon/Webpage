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
   
    <meta charset="UTF-8">
    
    <link rel="icon" type="image/png" href="img/iconv.png">
    
   <link rel="stylesheet" href="css/satoshi.css">
   
    <link rel="stylesheet" href="css/ranade.css">

    <link rel="stylesheet" href="css/plein.css">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    
     <meta name="google-site-verification" content="gAC20XPhZpBfAy1K2QxKwRTgZXFDY8MqzfiiPGJk910" />
    
    <meta
        name="description"
        content="Viziune es una consultoría web y de servicios digitales especializada en diseño y desarrollo web, tiendas online, SEO, SEM y soluciones digitales con inteligencia artificial.">

    <link
        rel="canonical"
        href="https://viziuneai.es/">

    <title>
        Viziune | Consultoría Web, Diseño Web, SEO y Soluciones Digitales
    </title>
    
    <meta name="application-name" content="Viziune">
    
    <meta property="og:site_name" content="Viziune">

    <link rel="stylesheet" href="css/style.css?v=2">
        
       <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "Viziune",
            "url": "https://viziuneai.es/"
        }
        </script>
        
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Viziune",
            "legalName": "Viziune SL",
            "url": "https://viziuneai.es/"
        }
        </script>

<!-- Google tag (gtag.js) -->
  <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-DPY8CEKPEF"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-DPY8CEKPEF');
    </script>

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


        /* ==========================================================
           ENLACES LEGALES DEL FOOTER
        ========================================================== */

        .footer-enlaces-legales {

            display: flex;
            
            width: 100%;

            justify-content: center;

            align-items: center;

            flex-wrap: wrap;

            gap: 10px 20px;

            margin-top: 10px;

        }


        .footer-enlaces-legales .configurar-cookies-footer {

            margin-top: 0;

        }


        .configurar-cookies-footer {

            display: inline-block;

            color: #00cfe0;

            text-decoration: none;

            font-size: 14px;

            cursor: pointer;

        }


        .configurar-cookies-footer:hover {

            text-decoration: underline;

        }
        
        .terminoscondiciones {

            display: inline-block;

            color: #00cfe0;

            text-decoration: none;

            font-size: 14px;

            cursor: pointer;

        }


        .terminoscondiciones:hover {

            text-decoration: underline;

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
     CÓMO TRABAJA TU PRESENCIA DIGITAL
========================================================= -->

<section class="presencia-negocio">

    <div class="presencia-header">

        <h1>
            ¿Tu presencia digital está trabajando para ti?
        </h1>

        <p>
            Tener una web, aparecer en Google o estar presente en internet
            no garantiza conseguir clientes. Lo importante es que cada parte
            de tu presencia digital trabaje en conjunto para atraer,
            convencer y convertir oportunidades.
        </p>

    </div>


    <div class="presencia-recorrido">

        <article class="presencia-card">

            <div class="presencia-top">
                <span class="presencia-numero">01</span>
                <span class="presencia-linea"></span>
            </div>

            <div class="presencia-icono">↗</div>

            <h3>
                Que te encuentren
            </h3>

            <p>
                Aumenta tu visibilidad ante las personas que realmente
                buscan los productos o servicios que ofreces.
            </p>

            <span class="presencia-servicios">
                SEO · SEM · POSICIONAMIENTO
            </span>

        </article>


        <article class="presencia-card">

            <div class="presencia-top">
                <span class="presencia-numero">02</span>
                <span class="presencia-linea"></span>
            </div>

            <div class="presencia-icono">◇</div>

            <h3>
                Que confíen en ti
            </h3>

            <p>
                Una presencia digital profesional ayuda a transmitir
                el valor de tu negocio desde el primer contacto.
            </p>

            <span class="presencia-servicios">
                WEB · UX/UI · IDENTIDAD
            </span>

        </article>


        <article class="presencia-card">

            <div class="presencia-top">
                <span class="presencia-numero">03</span>
                <span class="presencia-linea"></span>
            </div>

            <div class="presencia-icono">→</div>

            <h3>
                Que contacten contigo
            </h3>

            <p>
                Diseñamos experiencias y puntos de contacto pensados
                para transformar visitas en oportunidades comerciales.
            </p>

            <span class="presencia-servicios">
                ESTRATEGIA · CONVERSIÓN · AUTOMATIZACIÓN
            </span>

        </article>


        <article class="presencia-card">

            <div class="presencia-top">
                <span class="presencia-numero">04</span>
                <span class="presencia-linea"></span>
            </div>

            <div class="presencia-icono">✓</div>

            <h3>
                Que se conviertan en clientes
            </h3>

            <p>
                Optimizamos cada parte del proceso para que tu ecosistema
                digital acompañe al cliente hasta la decisión.
            </p>

            <span class="presencia-servicios">
                OPTIMIZACIÓN · ANALÍTICA · SOLUCIONES DIGITALES
            </span>

        </article>

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

    <h2>
        De nuestro asistente
        <span>al correo</span>
    </h2>

    <p>
        A partir de aquí es donde todo tu negocio pasa a otro nivel. Puedes
        elegir qué especialista quieres consultar y cuando
        termines enviar toda la conversación a nuestro equipo.
    </p>

    <details class="agent-info-acordeon">

        <summary>
            Como usar el chat
        </summary>

        <div class="agent-info-contenido">

            <p class="agent-selector-description">

                - 1) Si ya tienes una página web y quieres saber qué
                tal está o qué aspectos puedes mejorar, pásate primero
                por nuestra Auditoría SEO. Analizaremos tu web y te
                proporcionaremos un resumen con los principales aspectos
                a mejorar. Una vez lo hayas copiado, pégalo en nuestro
                Asesor SEO/SEM para que pueda ayudarte a trabajar sobre ellos.

                <br><br>

                - 2) Si todavía no tienes página web, estás en el lugar indicado:

                <br><br>

                - Habla con nuestro asesor y cuéntale qué necesitas para tu proyecto.

                <br><br>

                - Cuando hayas terminado la conversación,
                si quieres dar el siguiente paso, escribe «quiero contactar»,
                «quiero hablar» o «quiero pedir un presupuesto». Aparecerá un botón
                debajo del chat desde el que podrás enviarnos la conversación,
                permitiéndonos conocer tus necesidades y dudas para poder ofrecerte
                una atención más personalizada.

            </p>

        </div>

    </details>

</div>



        <?php if (!$usuarioLogueado): ?>

            <!-- =================================================
                 AVISO DE ACCESO PRIVADO
            ================================================== -->

            <div class="acceso-privado-aviso">

                <p>
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

                            <!--
                            =================================================
                            AVATAR ACTUALIZADO
                            Se utiliza asesoramiento.png y no asset.png.
                            El ?v=2 evita que el navegador utilice una
                            versión antigua guardada en caché.
                            =================================================
                            -->

                            <img
                                src="img/asesoramiento.png?v=2"
                                alt="Asistente de Diseño y Desarrollo Web">

                        </div>


                        <div class="chat-intro-info">

                            <strong id="assistantName">

                                Diseño y Desarrollo Web
                                
                                <br>

                            </strong>
                            
                             <span id="assistantDescription">
                                Soluciones digitales para crear, mejorar y hacer crecer tu negocio online.
                                </span>

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

            <h2>
                CONTACTO
            </h2>


            <h3>
                ¿Quieres hablar con nosotros?
            </h3>


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
                
                 <button
                type="button"
                id="closeReunionModal"
                class="reunion-modal-close"
                aria-label="Cerrar">
                     
                Cerrar

            </button>


                <div> 

            </div>



        </div>


        <div class="reunion-modal-body">


            <p>

                Selecciona la fecha y hora que prefieres para la reunión.

            </p>


            <div class="form-group">


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
            © <?php echo date("Y"); ?> Viziune
        </p>


        <div class="footer-enlaces-legales">


            <!-- POLÍTICA DE PRIVACIDAD -->

            <a
                href="politica_entera.php"
                class="configurar-cookies-footer">

                Política de privacidad

            </a>


            <!-- POLÍTICA DE COOKIES -->

            <a
                href="politica_cookies.php"
                class="configurar-cookies-footer">

                Política de cookies

            </a>
    
            <a href="terminos.php" class="terminoscondiciones">Términos y condiciones</a>
        



            <!-- CONFIGURAR COOKIES -->

            <a
                href="#"
                class="configurar-cookies-footer"
                onclick="if (typeof window.mostrarPreferenciasCookies === 'function') { window.mostrarPreferenciasCookies(); } return false;">

                Configurar cookies

            </a>


        </div>

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


<!-- =========================================================
     SISTEMA PROPIO DE COOKIES
     SIN COOKIEBOT
========================================================= -->

<?php include 'cookies.php'; ?>


</body>

</html>