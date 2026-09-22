<?php

session_start();

require_once 'config.php';

$usuarioLogueado = isset($_SESSION['usuario_id']);

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Auditoría SEO | ViziuneAI</title>

<link rel="stylesheet" href="css/style.css">

<link rel="stylesheet" href="css/herramientas.css">

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

    text-align: center;

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
   AVISO LOGIN / MODAL
========================================================== */

.login-aviso {

    position: fixed;

    inset: 0;

    z-index: 9999;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background: rgba(0, 0, 0, 0.72);

    backdrop-filter: blur(5px);

    box-sizing: border-box;

}

.login-aviso[hidden] {

    display: none;

}

.login-aviso-contenido {

    position: relative;

    width: 100%;

    max-width: 430px;

    padding: 35px 30px;

    background: #0d1a29;

    border: 1px solid #1c3045;

    border-radius: 16px;

    text-align: center;

    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.45);

    box-sizing: border-box;

}

.login-aviso-cerrar {

    position: absolute;

    top: 12px;

    right: 15px;

    width: 35px;

    height: 35px;

    border: none;

    background: transparent;

    color: #b7c5d3;

    font-size: 28px;

    cursor: pointer;

}

.login-aviso-cerrar:hover {

    color: #00cfe0;

}

.login-aviso-icono {

    font-size: 42px;

    margin-bottom: 15px;

}

.login-aviso-contenido h2 {

    margin: 0 0 12px;

    color: #ffffff;

}

.login-aviso-contenido p {

    margin: 0 0 25px;

    color: #b7c5d3;

    line-height: 1.6;

}

.login-aviso-botones {

    display: flex;

    justify-content: center;

    gap: 12px;

    flex-wrap: wrap;

}

.login-aviso-login,
.login-aviso-continuar {

    border: none;

    border-radius: 8px;

    padding: 12px 20px;

    cursor: pointer;

    font-size: 14px;

}

.login-aviso-login {

    background: #00cfe0;

    color: #061018;

    font-weight: 700;

}

.login-aviso-login:hover {

    opacity: 0.9;

}

.login-aviso-continuar {

    background: #1c3045;

    color: #ffffff;

}

.login-aviso-continuar:hover {

    background: #29445d;

}


/* ==========================================================
   RESULTADO SEO
========================================================== */

.seo-diagnostico {

    margin-top: 25px;

    padding: 25px;

    width: 100%;

    max-width: 100%;

    box-sizing: border-box;

    background: rgba(255,255,255,0.025);

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 14px;

    overflow: hidden;

}

.seo-diagnostico h3 {

    margin: 0 0 12px;

    max-width: 100%;

    color: #ffffff;

    font-size: 20px;

    line-height: 1.4;

    overflow-wrap: anywhere;

    word-break: break-word;

}

.seo-diagnostico p {

    margin: 0;

    width: 100%;

    max-width: 100%;

    box-sizing: border-box;

    color: #b7c5d3;

    line-height: 1.8;

    white-space: normal;

    overflow-wrap: anywhere;

    word-break: break-word;

}

.seo-diagnostico * {

    max-width: 100%;

    box-sizing: border-box;

    overflow-wrap: anywhere;

    word-break: break-word;

}


/* ==========================================================
   IDENTIFICACIÓN DE LA AUDITORÍA
========================================================== */

.seo-auditoria-info {

    margin-top: 25px;

    display: grid;

    grid-template-columns: minmax(0, 1fr) 180px;

    gap: 15px;

}

.seo-auditoria-info-card {

    padding: 18px 20px;

    background: rgba(255,255,255,0.025);

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 12px;

    min-width: 0;

}

.seo-auditoria-info-card span {

    display: block;

    margin-bottom: 8px;

    color: #7f92a5;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 0.08em;

    text-transform: uppercase;

}

.seo-auditoria-info-card strong {

    display: block;

    color: #ffffff;

    font-size: 15px;

    line-height: 1.5;

    word-break: break-word;

}


/* ==========================================================
   ESTADO POR ÁREAS
========================================================== */

.seo-areas-section {

    margin-top: 25px;

}

.seo-areas-header {

    margin-bottom: 15px;

}

.seo-areas-header span {

    display: block;

    margin-bottom: 5px;

    color: #00cfe0;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 0.08em;

}

.seo-areas-header h3 {

    margin: 0;

    color: #ffffff;

    font-size: 20px;

}

.seo-areas-grid {

    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 14px;

    max-height: 470px;

    overflow-y: auto;

    overflow-x: hidden;

    padding: 4px;

    margin: 0 -4px;

    scrollbar-width: thin;

    scrollbar-color: #29445d transparent;

}

.seo-areas-grid::-webkit-scrollbar {

    width: 7px;

}

.seo-areas-grid::-webkit-scrollbar-track {

    background: transparent;

}

.seo-areas-grid::-webkit-scrollbar-thumb {

    background: #29445d;

    border-radius: 10px;

}

.seo-area-card {

    position: relative;

    min-height: 145px;

    padding: 20px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,0.035),
            rgba(255,255,255,0.015)
        );

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 14px;

    box-sizing: border-box;

    transition:
        transform 0.2s ease,
        border-color 0.2s ease,
        background 0.2s ease;

}

.seo-area-card:hover {

    transform: translateY(-2px);

    border-color: rgba(0,207,224,0.35);

    background:
        linear-gradient(
            145deg,
            rgba(0,207,224,0.06),
            rgba(255,255,255,0.02)
        );

}

.seo-area-card.success {

    border-left: 3px solid #58d68d;

}

.seo-area-card.warning {

    border-left: 3px solid #ffd166;

}

.seo-area-card.error {

    border-left: 3px solid #ff6464;

}

.seo-area-card-header {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 12px;

}

.seo-area-card-name {

    color: #ffffff;

    font-size: 14px;

    font-weight: 700;

    line-height: 1.4;

}

.seo-area-card-score {

    flex-shrink: 0;

    color: #ffffff;

    font-size: 21px;

    font-weight: 800;

}

.seo-area-card-status {

    display: flex;

    align-items: center;

    gap: 7px;

    margin-top: 13px;

    color: #b7c5d3;

    font-size: 13px;

}

.seo-area-card-status-dot {

    width: 8px;

    height: 8px;

    flex-shrink: 0;

    border-radius: 50%;

    background: #7f92a5;

}

.seo-area-card.success .seo-area-card-status-dot {

    background: #58d68d;

}

.seo-area-card.warning .seo-area-card-status-dot {

    background: #ffd166;

}

.seo-area-card.error .seo-area-card-status-dot {

    background: #ff6464;

}

.seo-area-card-description {

    margin-top: 12px;

    color: #7f92a5;

    font-size: 12px;

    line-height: 1.5;

}


/* ==========================================================
   BLOQUES DE DIAGNÓSTICO
========================================================== */

.seo-bloques-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px;

    margin-top: 25px;

}

.seo-info-card {

    padding: 22px;

    background: rgba(255,255,255,0.025);

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 14px;

}

.seo-info-card h4 {

    margin: 0 0 15px;

    color: #ffffff;

    font-size: 17px;

}

.seo-info-card ul {

    margin: 0;

    padding-left: 20px;

    color: #b7c5d3;

    line-height: 1.7;

}

.seo-info-card li {

    margin-bottom: 7px;

}

.seo-info-card .seo-ok {

    color: #7ee2a8;

}

.seo-info-card .seo-warning {

    color: #ffd166;

}

.seo-info-card .seo-critical {

    color: #ff7b7b;

}

.seo-prioridad {

    margin-top: 25px;

}

.seo-prioridad h4 {

    margin: 0 0 15px;

    color: #ffffff;

}

.seo-prioridad-alta {

    border-left: 4px solid #ff6464;

}

.seo-prioridad-media {

    border-left: 4px solid #ffd166;

}

.seo-oportunidades {

    border-left: 4px solid #00cfe0;

}


/* ==========================================================
   PUNTUACIONES
========================================================== */

.seo-score-grid {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 12px;

    margin-top: 20px;

}

.seo-score-card {

    padding: 15px;

    background: rgba(255,255,255,0.025);

    border: 1px solid rgba(255,255,255,0.07);

    border-radius: 10px;

}

.seo-score-card span {

    display: block;

    color: #8fa2b4;

    font-size: 12px;

    margin-bottom: 7px;

}

.seo-score-card strong {

    color: #ffffff;

    font-size: 20px;

}


/* ==========================================================
   ESTRUCTURA
========================================================== */

.seo-estructura {

    margin-top: 20px;

    padding: 18px;

    background: #08131f;

    border-radius: 10px;

    overflow-x: auto;

}

.seo-estructura pre {

    margin: 0;

    color: #b7c5d3;

    font-family: monospace;

    font-size: 13px;

    line-height: 1.6;

    white-space: pre-wrap;

}


/* ==========================================================
   RESUMEN PARA COPIAR
========================================================== */

.seo-resumen-copiar {

    display: flex;

    flex-direction: column;

    gap: 15px;

}

.seo-resumen-copiar textarea {

    width: 100%;

    min-height: 420px;

    resize: vertical;

    box-sizing: border-box;

    padding: 18px;

    background: #07121d;

    border: 1px solid #20364b;

    border-radius: 10px;

    color: #d9e4ee;

    line-height: 1.6;

    font-family: monospace;

}

.seo-metrica strong {

    word-break: break-word;

}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (max-width: 1050px) {

    .seo-areas-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }

}

@media (max-width: 900px) {

    .seo-auditoria-info {

        grid-template-columns: 1fr;

    }

}

@media (max-width: 800px) {

    .seo-bloques-grid {

        grid-template-columns: 1fr;

    }

    .seo-score-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }

    .seo-areas-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        max-height: 520px;

    }

}

@media (max-width: 550px) {

    .seo-areas-grid {

        grid-template-columns: 1fr;

        max-height: 600px;

    }

}

@media (max-width: 500px) {

    .seo-score-grid {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<!-- ==========================================
     CABECERA
========================================== -->

<header class="header" style="position:fixed;">

<?php include 'menu.php'; ?>

</header>


<!-- ==========================================
     CONTENIDO
========================================== -->

<main class="herramientas-page">

<section class="herramientas-section">

<div class="container">


<!-- ======================================
     CABECERA
======================================= -->

<div class="herramientas-header">

    <h1>

        Auditoría SEO

    </h1>

</div>


<!-- ======================================
     AVISO DE ACCESO PRIVADO
======================================= -->

<?php if (!$usuarioLogueado): ?>

    <div class="acceso-privado-aviso">

        <strong>

            🔐 Auditoría SEO privada

        </strong>

        <p>

            Puedes consultar esta sección sin iniciar sesión.
            Para realizar una auditoría SEO de una página web
            necesitas acceder a tu cuenta.

        </p>

        <a
            href="login.php"
            class="boton-iniciar-sesion"
        >

            Iniciar sesión

        </a>

    </div>

<?php endif; ?>


<!-- ======================================
     BLOQUE PRINCIPAL
======================================= -->

<article class="seo-panel">


<!-- ==================================
     CABECERA
=================================== -->

<div class="seo-panel-header">

    <div>

        <span class="seo-panel-kicker">

            ANÁLISIS SEO

        </span>

        <h2>

            Analiza tu página web y descubre que puedes mejorar.

        </h2>

        <br>

        <p>

           Introduce la URL de tu página y deja que ViziuneAI analice su estado a nivel SEO.

Revisaremos los principales aspectos que pueden influir en la visibilidad y el funcionamiento de tu web: SEO técnico, estructura, contenido, imágenes, enlaces, metadatos, indexación y otros elementos importantes para los buscadores y tus usuarios.

Al finalizar recibirás un diagnóstico completo y fácil de entender, con una valoración general de tu web, los aspectos que están funcionando correctamente, los problemas detectados, las prioridades que conviene revisar primero y las oportunidades que puedes aprovechar para seguir mejorando.

Obtén una visión clara de cómo está tu web, qué necesita mejorar y por dónde empezar.

        </p>

        <br>

    </div>

</div>


<!-- ==================================
     FORMULARIO
=================================== -->

<div class="seo-form">

    <label for="seoUrl">

        URL de tu página web

    </label>

    <div class="seo-input-row">

        <input
            type="url"
            id="seoUrl"
            placeholder="https://www.tuweb.com"
            autocomplete="url"
        >

        <button
            type="button"
            class="herramienta-button"
            id="analizarSeo"
        >

            🔎 Analizar mi web

        </button>

    </div>

</div>


<!-- ==================================
     CARGANDO
=================================== -->

<div
    id="seoLoading"
    class="seo-loading"
    hidden
>

    <div class="seo-loading-spinner"></div>

    <div>

        <strong>

            Analizando tu página web...

        </strong>

        <span>

            Estamos revisando los principales factores técnicos,
            de contenido y estructura.

        </span>

    </div>

</div>


<!-- ==================================
     ERROR
=================================== -->

<div
    id="seoError"
    class="seo-error"
    hidden
></div>


<!-- ==================================
     RESULTADO
=================================== -->

<div
    id="seoResultado"
    class="seo-resultado"
    hidden
>


<!-- ==================================
     RESUMEN SUPERIOR
=================================== -->

<div class="seo-summary-grid">


<div class="seo-puntuacion">

    <div class="seo-puntuacion-label">

        Puntuación SEO

    </div>

    <strong id="seoPuntuacion">

        -

    </strong>

    <div
        id="seoNivel"
        class="seo-nivel"
    >

        -

    </div>

</div>


<div class="seo-summary-card">

    <span class="seo-summary-label">

        ESTADO GENERAL

    </span>

    <strong id="seoEstadoGeneral">

        Análisis completado

    </strong>

    <p id="seoDescripcionGeneral">

        Hemos revisado los principales elementos
        técnicos, de contenido y estructura.

    </p>

</div>


<div class="seo-summary-card">

    <span class="seo-summary-label">

        INFORME

    </span>

    <strong>

        Informe para ViziuneAI

    </strong>

    <p>

        Puedes copiar el informe completo y utilizarlo
        directamente en el chat de ViziuneAI para continuar
        analizando las necesidades de tu proyecto.

    </p>

</div>


</div>


<!-- ==================================
     IDENTIFICACIÓN DE LA AUDITORÍA
=================================== -->

<div class="seo-auditoria-info">

    <div class="seo-auditoria-info-card">

        <span>

            URL ANALIZADA

        </span>

        <strong id="seoUrlResultado">

            -

        </strong>

    </div>


    <div class="seo-auditoria-info-card">

        <span>

            FECHA DEL ANÁLISIS

        </span>

        <strong id="seoFechaAnalisis">

            -

        </strong>

    </div>

</div>


<!-- ==================================
     DIAGNÓSTICO GENERAL
=================================== -->

<div class="seo-diagnostico">

    <h3>

        🧠 Diagnóstico general

    </h3>

    <p id="seoDiagnosticoTexto">

        -

    </p>

</div>


<!-- ==================================
     ESTADO POR ÁREAS
=================================== -->

<div class="seo-areas-section">

    <div class="seo-areas-header">

        <span>

            EVALUACIÓN

        </span>

        <h3>

            Estado por áreas

        </h3>

    </div>


    <div
        id="seoAreasGrid"
        class="seo-areas-grid"
    >

        <!-- Las áreas se generan mediante JavaScript -->

    </div>

</div>


<!-- ==================================
     PUNTUACIONES INTERNAS
=================================== -->

<div class="seo-section-block">

    <div class="seo-section-heading">

        <div>

            <span>

                PUNTUACIÓN

            </span>

            <h3>

                Desglose de puntuación

            </h3>

        </div>

    </div>


    <div class="seo-score-grid">

        <div class="seo-score-card">

            <span>

                SEO técnico

            </span>

            <strong id="scoreTecnico">

                -

            </strong>

        </div>


        <div class="seo-score-card">

            <span>

                SEO on-page

            </span>

            <strong id="scoreOnPage">

                -

            </strong>

        </div>


        <div class="seo-score-card">

            <span>

                Contenido

            </span>

            <strong id="scoreContenido">

                -

            </strong>

        </div>


        <div class="seo-score-card">

            <span>

                Estructura

            </span>

            <strong id="scoreEstructura">

                -

            </strong>

        </div>

    </div>

</div>


<!-- ==================================
     DATOS DE LA AUDITORÍA
=================================== -->

<div class="seo-section-block">

    <div class="seo-section-heading">

        <div>

            <span>

                DATOS

            </span>

            <h3>

                Detalle del análisis

            </h3>

        </div>

    </div>


<div class="seo-metricas">


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Código HTTP

    </span>

    <strong id="seoHttpCode">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        HTTPS

    </span>

    <strong id="seoHttps">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Tiempo de respuesta

    </span>

    <strong id="seoTiempoRespuesta">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Tamaño aproximado

    </span>

    <strong id="seoTamano">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Idioma declarado

    </span>

    <strong id="seoIdioma">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Viewport móvil

    </span>

    <strong id="seoViewport">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Título

    </span>

    <strong id="seoTitulo">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Longitud del título

    </span>

    <strong id="seoLongitudTitulo">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Meta descripción

    </span>

    <strong id="seoDescripcion">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Longitud de meta description

    </span>

    <strong id="seoLongitudDescripcion">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        H1

    </span>

    <strong id="seoH1">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        H2

    </span>

    <strong id="seoH2">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        H3

    </span>

    <strong id="seoH3">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Párrafos

    </span>

    <strong id="seoParrafos">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Contenido aproximado

    </span>

    <strong id="seoPalabras">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Imágenes

    </span>

    <strong id="seoImagenes">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Imágenes sin ALT

    </span>

    <strong id="seoImagenesAlt">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Enlaces internos

    </span>

    <strong id="seoInternos">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Enlaces externos

    </span>

    <strong id="seoExternos">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Canonical

    </span>

    <strong id="seoCanonical">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Robots

    </span>

    <strong id="seoRobots">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        robots.txt

    </span>

    <strong id="seoRobotsTxt">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        sitemap.xml

    </span>

    <strong id="seoSitemap">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Open Graph

    </span>

    <strong id="seoOpenGraph">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Twitter Card

    </span>

    <strong id="seoTwitterCard">

        -

    </strong>

</div>


</div>

</div>


<!-- ==================================
     ESTRUCTURA
=================================== -->

<div class="seo-section-block">

    <div class="seo-section-heading">

        <div>

            <span>

                ESTRUCTURA

            </span>

            <h3>

                Encabezados encontrados

            </h3>

        </div>

    </div>


    <div class="seo-details-grid">


        <div class="seo-details-card">

            <div class="seo-details-header">

                <span>

                    H1

                </span>

                <strong>

                    Encabezados principales

                </strong>

            </div>

            <ul id="seoListaH1"></ul>

        </div>


        <div class="seo-details-card">

            <div class="seo-details-header">

                <span>

                    H2

                </span>

                <strong>

                    Subapartados

                </strong>

            </div>

            <ul id="seoListaH2"></ul>

        </div>


        <div class="seo-details-card">

            <div class="seo-details-header">

                <span>

                    H3

                </span>

                <strong>

                    Subniveles

                </strong>

            </div>

            <ul id="seoListaH3"></ul>

        </div>


    </div>


    <div class="seo-estructura">

        <pre id="seoArbolEstructura">-</pre>

    </div>

</div>


<!-- ==================================
     PROBLEMAS / CORRECTOS
=================================== -->

<div class="seo-bloques-grid">


    <div class="seo-info-card">

        <h4>

            🔴 Problemas detectados

        </h4>

        <ul id="seoProblemas"></ul>

    </div>


    <div class="seo-info-card">

        <h4>

            🟢 Aspectos correctos

        </h4>

        <ul id="seoCorrectos"></ul>

    </div>

</div>


<!-- ==================================
     PRIORIDAD ALTA
=================================== -->

<div class="seo-info-card seo-prioridad seo-prioridad-alta">

    <h4>

        🔴 Prioridad alta

    </h4>

    <ul id="seoPrioridadAlta"></ul>

</div>


<!-- ==================================
     PRIORIDAD MEDIA
=================================== -->

<div class="seo-info-card seo-prioridad seo-prioridad-media">

    <h4>

        🟠 Prioridad media

    </h4>

    <ul id="seoPrioridadMedia"></ul>

</div>


<!-- ==================================
     OPORTUNIDADES
=================================== -->

<div class="seo-info-card seo-prioridad seo-oportunidades">

    <h4>

        💡 Oportunidades de mejora

    </h4>

    <ul id="seoOportunidades"></ul>

</div>


<!-- ==================================
     RESUMEN PARA VIZIUNEAI
=================================== -->

<div class="seo-section-block">

    <div class="seo-section-heading">

        <div>

            <span>

                INFORME

            </span>

            <h3>

                Informe completo para ViziuneAI

            </h3>

        </div>

    </div>


    <div class="seo-resumen-copiar">

        <textarea
            id="seoResumenChat"
            readonly
            rows="20"
        ></textarea>


        <button
            type="button"
            class="herramienta-button"
            id="copiarResumenSeo"
        >

            📋 Copiar informe completo

        </button>

    </div>

</div>


</div>


</article>


</div>

</section>

</main>


<!-- ==========================================
     AVISO DE INICIO DE SESIÓN
========================================== -->

<div
    id="loginAviso"
    class="login-aviso"
    hidden
>

    <div class="login-aviso-contenido">

        <button
            type="button"
            id="cerrarLoginAviso"
            class="login-aviso-cerrar"
            aria-label="Cerrar"
        >

            ×

        </button>


        <div class="login-aviso-icono">

            🔐

        </div>


        <h2>

            Inicia sesión

        </h2>


        <p>

            Para utilizar la Auditoría SEO necesitas iniciar sesión.

        </p>


        <div class="login-aviso-botones">

            <button
                type="button"
                id="irLogin"
                class="login-aviso-login"
            >

                Iniciar sesión

            </button>


            <button
                type="button"
                id="seguirSinLogin"
                class="login-aviso-continuar"
            >

                Continuar

            </button>

        </div>

    </div>

</div>


<!-- ==========================================
     FOOTER
========================================== -->

<footer class="footer">

<div class="container">

<p>

    © <?php echo date('Y'); ?> ViziuneAI.
    Todos los derechos reservados.

</p>

</div>

</footer>


<!-- ==========================================
     JAVASCRIPT AUDITORÍA SEO
========================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const usuarioLogueado =
            <?php echo $usuarioLogueado ? 'true' : 'false'; ?>;


        const boton =
            document.getElementById("analizarSeo");

        const urlInput =
            document.getElementById("seoUrl");

        const loading =
            document.getElementById("seoLoading");

        const error =
            document.getElementById("seoError");

        const resultado =
            document.getElementById("seoResultado");

        const botonCopiar =
            document.getElementById("copiarResumenSeo");

        const resumenChat =
            document.getElementById("seoResumenChat");

        const loginAviso =
            document.getElementById("loginAviso");

        const cerrarLoginAviso =
            document.getElementById("cerrarLoginAviso");

        const irLogin =
            document.getElementById("irLogin");

        const seguirSinLogin =
            document.getElementById("seguirSinLogin");

        const areasGrid =
            document.getElementById("seoAreasGrid");


        function mostrarAvisoLogin() {

            loginAviso.hidden = false;

        }


        function cerrarAvisoLogin() {

            loginAviso.hidden = true;

        }


        cerrarLoginAviso.addEventListener(
            "click",
            cerrarAvisoLogin
        );


        seguirSinLogin.addEventListener(
            "click",
            cerrarAvisoLogin
        );


        irLogin.addEventListener(
            "click",
            function () {

                window.location.href =
                    "login.php";

            }
        );


        window.mostrarAvisoLogin =
            mostrarAvisoLogin;


        window.usuarioLogueado =
            usuarioLogueado;


        boton.addEventListener(
            "click",
            analizarSEO
        );


        urlInput.addEventListener(
            "keydown",
            function (event) {

                if (event.key === "Enter") {

                    event.preventDefault();

                    analizarSEO();

                }

            }
        );


        botonCopiar.addEventListener(
            "click",
            copiarResumen
        );


        async function analizarSEO() {

            if (!usuarioLogueado) {

                mostrarAvisoLogin();

                return;

            }


            const url =
                urlInput.value.trim();


            if (url === "") {

                mostrarError(
                    "Introduce la dirección de tu página web."
                );

                urlInput.focus();

                return;

            }


            let urlFinal =
                url;


            if (
                !/^https?:\/\//i.test(urlFinal)
            ) {

                urlFinal =
                    "https://" +
                    urlFinal;

            }


            try {

                new URL(urlFinal);

            } catch (e) {

                mostrarError(
                    "La dirección introducida no es válida."
                );

                urlInput.focus();

                return;

            }


            error.hidden = true;

            resultado.hidden = true;

            loading.hidden = false;

            boton.disabled = true;

            boton.textContent =
                "Analizando...";


            try {

                const response =
                    await fetch(
                        "seo_auditoria.php",
                        {
                            method: "POST",

                            headers: {

                                "Content-Type":
                                    "application/json",

                                "Accept":
                                    "application/json"

                            },

                            body:
                                JSON.stringify({
                                    url: urlFinal
                                })

                        }
                    );


                const texto =
                    await response.text();


                let data;


                try {

                    data =
                        JSON.parse(texto);

                } catch (e) {

                    throw new Error(
                        "El servidor devolvió una respuesta no válida."
                    );

                }


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.error ||
                        "No se pudo realizar el análisis."
                    );

                }


                mostrarResultado(
                    data.analisis
                );


            } catch (e) {

                mostrarError(
                    e.message ||
                    "Se produjo un error durante el análisis."
                );


            } finally {

                loading.hidden = true;

                boton.disabled = false;

                boton.textContent =
                    "Analizar mi web";

            }

        }


        function mostrarError(mensaje) {

            error.textContent =
                "❌ " + mensaje;

            error.hidden = false;

            resultado.hidden = true;

        }


        function pintarLista(
            id,
            elementos,
            textoVacio
        ) {

            const lista =
                document.getElementById(id);

            lista.innerHTML = "";


            if (
                Array.isArray(elementos) &&
                elementos.length > 0
            ) {

                elementos.forEach(
                    function (texto) {

                        const li =
                            document.createElement("li");

                        li.textContent =
                            texto;

                        lista.appendChild(li);

                    }
                );

            } else {

                const li =
                    document.createElement("li");

                li.textContent =
                    textoVacio;

                lista.appendChild(li);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | CREAR TARJETAS DE ESTADO POR ÁREA
        |--------------------------------------------------------------------------
        */

        function crearArea(
            nombre,
            puntuacion,
            maximo,
            descripcion
        ) {

            const porcentaje =
                maximo > 0
                    ? (puntuacion / maximo) * 100
                    : 0;


            let clase =
                "error";

            let estado =
                "Necesita atención";


            if (porcentaje >= 80) {

                clase =
                    "success";

                estado =
                    "Correcto";

            } else if (porcentaje >= 55) {

                clase =
                    "warning";

                estado =
                    "Mejorable";

            }


            const tarjeta =
                document.createElement("div");

            tarjeta.className =
                "seo-area-card " + clase;


            const encabezado =
                document.createElement("div");

            encabezado.className =
                "seo-area-card-header";


            const nombreElemento =
                document.createElement("div");

            nombreElemento.className =
                "seo-area-card-name";

            nombreElemento.textContent =
                nombre;


            const puntuacionElemento =
                document.createElement("div");

            puntuacionElemento.className =
                "seo-area-card-score";

            puntuacionElemento.textContent =
                puntuacion + "/" + maximo;


            encabezado.appendChild(
                nombreElemento
            );

            encabezado.appendChild(
                puntuacionElemento
            );


            const estadoElemento =
                document.createElement("div");

            estadoElemento.className =
                "seo-area-card-status";


            const punto =
                document.createElement("span");

            punto.className =
                "seo-area-card-status-dot";


            const textoEstado =
                document.createElement("span");

            textoEstado.textContent =
                estado;


            estadoElemento.appendChild(
                punto
            );

            estadoElemento.appendChild(
                textoEstado
            );


            const descripcionElemento =
                document.createElement("div");

            descripcionElemento.className =
                "seo-area-card-description";

            descripcionElemento.textContent =
                descripcion || "";


            tarjeta.appendChild(
                encabezado
            );

            tarjeta.appendChild(
                estadoElemento
            );

            tarjeta.appendChild(
                descripcionElemento
            );


            areasGrid.appendChild(
                tarjeta
            );

        }


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR ÁREAS
        |--------------------------------------------------------------------------
        */

        function mostrarAreas(datos) {

            areasGrid.innerHTML = "";


            const puntuaciones =
                datos.puntuaciones || {};


            crearArea(
                "SEO técnico",
                Number(
                    puntuaciones.tecnico ?? 0
                ),
                25,
                "HTTPS, respuesta, robots, canonical y elementos técnicos."
            );


            crearArea(
                "SEO on-page",
                Number(
                    puntuaciones.on_page ?? 0
                ),
                25,
                "Título, meta descripción y elementos principales de la página."
            );


            crearArea(
                "Contenido",
                Number(
                    puntuaciones.contenido ?? 0
                ),
                20,
                "Cantidad de contenido, imágenes, ALT y señales de contenido."
            );


            crearArea(
                "Estructura",
                Number(
                    puntuaciones.estructura ?? 0
                ),
                10,
                "Jerarquía de encabezados y organización del contenido."
            );


            const imagenes =
                Number(
                    datos.imagenes_total ?? 0
                );

            const imagenesSinAlt =
                Number(
                    datos.imagenes_sin_alt ?? 0
                );

            let scoreImagenes =
                0;


            if (imagenes === 0) {

                scoreImagenes = 10;

            } else {

                scoreImagenes =
                    Math.round(
                        (
                            (imagenes - imagenesSinAlt) /
                            imagenes
                        ) * 10
                    );

            }


            crearArea(
                "Imágenes",
                scoreImagenes,
                10,
                imagenes === 0
                    ? "No se han encontrado imágenes."
                    : imagenesSinAlt === 0
                        ? "Todas las imágenes tienen atributo ALT."
                        : imagenesSinAlt +
                          " imagen(es) no tienen ALT."
            );


            const internos =
                Number(
                    datos.enlaces_internos ?? 0
                );

            const externos =
                Number(
                    datos.enlaces_externos ?? 0
                );

            const totalEnlaces =
                internos + externos;


            let scoreEnlaces =
                0;


            if (totalEnlaces > 0) {

                scoreEnlaces =
                    Math.min(
                        10,
                        Math.round(
                            Math.min(
                                totalEnlaces / 10,
                                1
                            ) * 10
                        )
                    );

            }


            crearArea(
                "Enlaces",
                scoreEnlaces,
                10,
                internos +
                " internos · " +
                externos +
                " externos."
            );


            let scoreIndexacion =
                0;

            let detalleIndexacion =
                "Revisar configuración de indexación.";


            if (
                datos.robots &&
                !String(
                    datos.robots
                ).toLowerCase().includes(
                    "noindex"
                )
            ) {

                scoreIndexacion += 5;

            }


            if (datos.robots_txt) {

                scoreIndexacion += 2;

            }


            if (datos.sitemap) {

                scoreIndexacion += 3;

            }


            if (scoreIndexacion >= 8) {

                detalleIndexacion =
                    "Elementos de indexación detectados correctamente.";

            } else if (scoreIndexacion >= 5) {

                detalleIndexacion =
                    "La configuración de indexación puede mejorarse.";

            }


            crearArea(
                "Indexación",
                scoreIndexacion,
                10,
                detalleIndexacion
            );


            let scoreSocial =
                0;


            const openGraph =
                String(
                    datos.open_graph_estado || ""
                ).toLowerCase();


            const twitterCard =
                String(
                    datos.twitter_card || ""
                ).toLowerCase();


            if (
                openGraph &&
                !openGraph.includes(
                    "no detect"
                )
            ) {

                scoreSocial += 5;

            }


            if (
                twitterCard &&
                !twitterCard.includes(
                    "no detect"
                )
            ) {

                scoreSocial += 5;

            }


            crearArea(
                "Metadatos sociales",
                scoreSocial,
                10,
                "Open Graph y Twitter Card."
            );


            crearArea(
                "Experiencia móvil",
                datos.viewport ? 10 : 0,
                10,
                datos.viewport
                    ? "Se ha detectado viewport móvil."
                    : "No se ha detectado una configuración viewport."
            );

        }


        function mostrarResultado(datos) {

            /*
            |--------------------------------------------------------------------------
            | PUNTUACIÓN GENERAL
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoPuntuacion"
            ).textContent =
                (datos.puntuacion ?? 0) +
                "/100";


            document.getElementById(
                "seoNivel"
            ).textContent =
                datos.valoracion ||
                "Sin valorar";


            document.getElementById(
                "seoEstadoGeneral"
            ).textContent =
                datos.valoracion ||
                "Análisis completado";


            document.getElementById(
                "seoDescripcionGeneral"
            ).textContent =
                datos.descripcion_valoracion ||
                "Análisis completado.";


            /*
            |--------------------------------------------------------------------------
            | DIAGNÓSTICO
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoDiagnosticoTexto"
            ).textContent =
                datos.resumen_general ||
                "No se ha podido generar el diagnóstico.";


            /*
            |--------------------------------------------------------------------------
            | URL
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoUrlResultado"
            ).textContent =
                datos.url ||
                "-";


            /*
            |--------------------------------------------------------------------------
            | FECHA
            |--------------------------------------------------------------------------
            */

            const ahora =
                new Date();


            document.getElementById(
                "seoFechaAnalisis"
            ).textContent =
                ahora.toLocaleDateString(
                    "es-ES",
                    {
                        day: "2-digit",
                        month: "2-digit",
                        year: "numeric"
                    }
                ) +
                " · " +
                ahora.toLocaleTimeString(
                    "es-ES",
                    {
                        hour: "2-digit",
                        minute: "2-digit"
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | DATOS TÉCNICOS
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoHttpCode"
            ).textContent =
                datos.http_code || "-";


            document.getElementById(
                "seoHttps"
            ).textContent =
                datos.https
                    ? "Sí"
                    : "No";


            document.getElementById(
                "seoTiempoRespuesta"
            ).textContent =
                datos.tiempo_respuesta_ms !== null
                    ? datos.tiempo_respuesta_ms +
                      " ms"
                    : "No disponible";


            document.getElementById(
                "seoTamano"
            ).textContent =
                datos.tamano_pagina_formateado ||
                "No disponible";


            document.getElementById(
                "seoIdioma"
            ).textContent =
                datos.idioma ||
                "No declarado";


            document.getElementById(
                "seoViewport"
            ).textContent =
                datos.viewport
                    ? "Detectado"
                    : "No detectado";


            /*
            |--------------------------------------------------------------------------
            | SEO ON-PAGE
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoTitulo"
            ).textContent =
                datos.titulo ||
                "No encontrado";


            document.getElementById(
                "seoLongitudTitulo"
            ).textContent =
                (datos.longitud_titulo ?? 0) +
                " caracteres";


            document.getElementById(
                "seoDescripcion"
            ).textContent =
                datos.meta_description ||
                "No encontrada";


            document.getElementById(
                "seoLongitudDescripcion"
            ).textContent =
                (datos.longitud_meta_description ?? 0) +
                " caracteres";


            /*
            |--------------------------------------------------------------------------
            | ESTRUCTURA
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoH1"
            ).textContent =
                datos.numero_h1 ?? 0;


            document.getElementById(
                "seoH2"
            ).textContent =
                datos.numero_h2 ?? 0;


            document.getElementById(
                "seoH3"
            ).textContent =
                datos.numero_h3 ?? 0;


            document.getElementById(
                "seoParrafos"
            ).textContent =
                datos.parrafos ?? 0;


            document.getElementById(
                "seoPalabras"
            ).textContent =
                datos.palabras_aproximadas ?? 0;


            /*
            |--------------------------------------------------------------------------
            | IMÁGENES Y ENLACES
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoImagenes"
            ).textContent =
                datos.imagenes_total ?? 0;


            document.getElementById(
                "seoImagenesAlt"
            ).textContent =
                datos.imagenes_sin_alt ?? 0;


            document.getElementById(
                "seoInternos"
            ).textContent =
                datos.enlaces_internos ?? 0;


            document.getElementById(
                "seoExternos"
            ).textContent =
                datos.enlaces_externos ?? 0;


            /*
            |--------------------------------------------------------------------------
            | INDEXACIÓN
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoCanonical"
            ).textContent =
                datos.canonical ||
                "No encontrada";


            document.getElementById(
                "seoRobots"
            ).textContent =
                datos.robots ||
                "No detectado";


            document.getElementById(
                "seoRobotsTxt"
            ).textContent =
                datos.robots_txt
                    ? "Detectado"
                    : "No detectado";


            document.getElementById(
                "seoSitemap"
            ).textContent =
                datos.sitemap
                    ? "Detectado"
                    : "No detectado";


            /*
            |--------------------------------------------------------------------------
            | REDES SOCIALES
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoOpenGraph"
            ).textContent =
                datos.open_graph_estado ||
                "No detectado";


            document.getElementById(
                "seoTwitterCard"
            ).textContent =
                datos.twitter_card ||
                "No detectada";


            /*
            |--------------------------------------------------------------------------
            | PUNTUACIONES
            |--------------------------------------------------------------------------
            */

            const puntuaciones =
                datos.puntuaciones || {};


            document.getElementById(
                "scoreTecnico"
            ).textContent =
                (puntuaciones.tecnico ?? 0) +
                "/25";


            document.getElementById(
                "scoreOnPage"
            ).textContent =
                (puntuaciones.on_page ?? 0) +
                "/25";


            document.getElementById(
                "scoreContenido"
            ).textContent =
                (puntuaciones.contenido ?? 0) +
                "/20";


            document.getElementById(
                "scoreEstructura"
            ).textContent =
                (puntuaciones.estructura ?? 0) +
                "/10";


            /*
            |--------------------------------------------------------------------------
            | LISTAS DE ENCABEZADOS
            |--------------------------------------------------------------------------
            */

            pintarLista(
                "seoListaH1",
                datos.h1,
                "No se han encontrado H1."
            );


            pintarLista(
                "seoListaH2",
                datos.h2,
                "No se han encontrado H2."
            );


            pintarLista(
                "seoListaH3",
                datos.h3,
                "No se han encontrado H3."
            );


            document.getElementById(
                "seoArbolEstructura"
            ).textContent =
                datos.arbol_estructura ||
                "No se ha podido generar el árbol de estructura.";


            /*
            |--------------------------------------------------------------------------
            | DIAGNÓSTICOS
            |--------------------------------------------------------------------------
            */

            pintarLista(
                "seoProblemas",
                datos.problemas,
                "No se han detectado problemas importantes."
            );


            pintarLista(
                "seoCorrectos",
                datos.correctos,
                "No se han registrado aspectos destacados."
            );


            pintarLista(
                "seoPrioridadAlta",
                datos.prioridad_alta,
                "No se han detectado prioridades altas."
            );


            pintarLista(
                "seoPrioridadMedia",
                datos.prioridad_media,
                "No se han detectado prioridades medias."
            );


            pintarLista(
                "seoOportunidades",
                datos.oportunidades,
                "No se han detectado oportunidades adicionales."
            );


            /*
            |--------------------------------------------------------------------------
            | INFORME COMPLETO PARA VIZIUNEAI
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            | El informe se divide en dos partes:
            |
            | 1. INSTRUCCIONES:
            |    Explican a ViziuneAI cómo debe interpretar los datos.
            |
            | 2. DATOS:
            |    Contienen los resultados reales de la auditoría.
            |
            */

            let resumen = "";


            /*
            |--------------------------------------------------------------------------
            | CONTEXTO
            |--------------------------------------------------------------------------
            */


            resumen +=
                "CONTEXTO PARA VIZIUNEAI\n";


            resumen +=
                "El contenido que aparece a continuación procede de una auditoría SEO automática realizada sobre una página web.\n\n";


            resumen +=
                "Este documento debe utilizarse como CONTEXTO TÉCNICO para responder a las preguntas del usuario relacionadas con esta página web, su SEO, estructura, contenido, indexación, rendimiento y oportunidades de mejora.\n\n";


            /*
            |--------------------------------------------------------------------------
            | INSTRUCCIONES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "INSTRUCCIONES PARA VIZIUNEAI\n";


            resumen +=
                "ROL:\n";

            resumen +=
                "Actúa como un asesor especializado en SEO, desarrollo web y optimización de páginas web.\n\n";


            resumen +=
                "OBJETIVO:\n";

            resumen +=
                "Utiliza los resultados de esta auditoría para ayudar al usuario a comprender el estado actual de su página web y determinar qué aspectos debería revisar, corregir u optimizar.\n\n";


            resumen +=
                "REGLAS DE INTERPRETACIÓN:\n\n";


            resumen +=
                "1. Utiliza los datos de esta auditoría como fuente principal de contexto sobre la página analizada.\n";


            resumen +=
                "2. No inventes datos, errores, configuraciones, métricas o problemas que no aparezcan en la auditoría.\n";


            resumen +=
                "3. Si un dato no está disponible, indica claramente que no ha sido comprobado o que la auditoría no dispone de esa información.\n";


            resumen +=
                "4. No supongas que un elemento está mal configurado simplemente porque no exista información suficiente para comprobarlo.\n";


            resumen +=
                "5. Interpreta los datos. No te limites a repetirlos.\n";


            resumen +=
                "6. Explica qué significa cada problema detectado y qué consecuencias puede tener para el SEO, la indexación, la experiencia del usuario o la visibilidad de la página.\n";


            resumen +=
                "7. Propón acciones concretas y realistas para solucionar los problemas detectados.\n";


            resumen +=
                "8. Da prioridad a los elementos incluidos en PRIORIDAD ALTA.\n";


            resumen +=
                "9. Después analiza los elementos incluidos en PRIORIDAD MEDIA.\n";


            resumen +=
                "10. Utiliza las OPORTUNIDADES DE MEJORA como acciones complementarias.\n";


            resumen +=
                "11. Ten en cuenta también los ASPECTOS CORRECTOS para saber qué elementos ya funcionan correctamente y evitar recomendar cambios innecesarios.\n";


            resumen +=
                "12. Cuando existan varios problemas relacionados entre sí, agrúpalos y evita recomendar acciones duplicadas.\n";


            resumen +=
                "13. Si el usuario pregunta qué debería solucionar primero, utiliza las prioridades de la auditoría y explica el motivo del orden propuesto.\n";


            resumen +=
                "14. Si el usuario quiere mejorar la web paso a paso, convierte los resultados de la auditoría en un plan de trabajo ordenado.\n";


            resumen +=
                "15. Si el usuario solicita una solución técnica, explica qué debería modificarse y por qué.\n";


            resumen +=
                "16. Si para solucionar un problema necesitas ver código, archivos o configuración que no aparecen en este informe, solicita al usuario esa información en lugar de inventarla.\n";


            resumen +=
                "17. Distingue siempre entre DATOS DETECTADOS, INTERPRETACIÓN y RECOMENDACIONES.\n";


            resumen +=
                "18. No confundas la puntuación general con las puntuaciones parciales.\n";


            resumen +=
                "19. Interpreta las puntuaciones junto con los problemas y datos concretos de la auditoría.\n";


            resumen +=
                "20. Utiliza un lenguaje claro y comprensible para el usuario, evitando tecnicismos innecesarios.\n\n";


            /*
            |--------------------------------------------------------------------------
            | FORMA DE RESPONDER
            |--------------------------------------------------------------------------
            */

            resumen +=
                "FORMA DE RESPONDER AL USUARIO\n";


            resumen +=
                "Cuando el usuario pregunte por esta auditoría:\n\n";


            resumen +=
                "1. Comienza explicando brevemente el estado general de la página según la puntuación y el diagnóstico.\n";


            resumen +=
                "2. Identifica los problemas más importantes.\n";


            resumen +=
                "3. Explica por qué cada problema puede ser relevante.\n";


            resumen +=
                "4. Indica cómo podría solucionarse.\n";


            resumen +=
                "5. Prioriza las acciones para que el usuario sepa por dónde empezar.\n";


            resumen +=
                "6. Indica qué elementos ya están correctamente configurados.\n";


            resumen +=
                "7. Explica las oportunidades de mejora que puedan aportar valor adicional.\n";


            resumen +=
                "8. Si el usuario solicita ayuda para realizar las mejoras, ofrece instrucciones prácticas basadas en los datos disponibles.\n\n";


            /*
            |--------------------------------------------------------------------------
            | REGLA FUNDAMENTAL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "REGLA FUNDAMENTAL:\n";

            resumen +=
                "Si la auditoría no contiene información suficiente para responder con seguridad a una pregunta concreta, debes indicarlo claramente y solicitar los datos necesarios. No debes inventar información para completar la respuesta.\n\n";


            /*
            |--------------------------------------------------------------------------
            | OBJETIVO FINAL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "OBJETIVO FINAL:\n";

            resumen +=
                "Transformar los resultados técnicos de esta auditoría en una explicación comprensible y en acciones concretas que ayuden al usuario a mejorar progresivamente su página web.\n\n";


            /*
            |--------------------------------------------------------------------------
            | DATOS DE LA AUDITORÍA
            |--------------------------------------------------------------------------
            */

            resumen +=
                "DATOS REALES DE LA AUDITORÍA SEO\n";


            resumen +=
                "AUDITORÍA SEO\n\n";


            resumen +=
                "URL: " +
                (datos.url || "-") +
                "\n";


            resumen +=
                "PUNTUACIÓN GENERAL: " +
                (datos.puntuacion ?? 0) +
                "/100\n";


            resumen +=
                "VALORACIÓN: " +
                (datos.valoracion || "-") +
                "\n";


            resumen +=
                "DESCRIPCIÓN DE LA VALORACIÓN: " +
                (datos.descripcion_valoracion || "-") +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | DIAGNÓSTICO GENERAL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "DIAGNÓSTICO GENERAL:\n";


            resumen +=
                (datos.resumen_general || "-") +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | PUNTUACIONES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "PUNTUACIONES POR ÁREA:\n";


            resumen +=
                "- SEO técnico: " +
                (puntuaciones.tecnico ?? 0) +
                "/25\n";


            resumen +=
                "- SEO on-page: " +
                (puntuaciones.on_page ?? 0) +
                "/25\n";


            resumen +=
                "- Contenido: " +
                (puntuaciones.contenido ?? 0) +
                "/20\n";


            resumen +=
                "- Estructura: " +
                (puntuaciones.estructura ?? 0) +
                "/10\n\n";


            /*
            |--------------------------------------------------------------------------
            | DATOS TÉCNICOS
            |--------------------------------------------------------------------------
            */

            resumen +=
                "DATOS TÉCNICOS:\n";


            resumen +=
                "- Código HTTP: " +
                (datos.http_code || "-") +
                "\n";


            resumen +=
                "- HTTPS: " +
                (datos.https ? "Sí" : "No") +
                "\n";


            resumen +=
                "- Tiempo de respuesta: " +
                (
                    datos.tiempo_respuesta_ms !== null
                        ? datos.tiempo_respuesta_ms + " ms"
                        : "No disponible"
                ) +
                "\n";


            resumen +=
                "- Tamaño: " +
                (
                    datos.tamano_pagina_formateado ||
                    "No disponible"
                ) +
                "\n";


            resumen +=
                "- Idioma: " +
                (
                    datos.idioma ||
                    "No declarado"
                ) +
                "\n";


            resumen +=
                "- Viewport: " +
                (
                    datos.viewport
                        ? "Detectado"
                        : "No detectado"
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | SEO ON-PAGE
            |--------------------------------------------------------------------------
            */

            resumen +=
                "SEO ON-PAGE:\n";


            resumen +=
                "- Título: " +
                (
                    datos.titulo ||
                    "No encontrado"
                ) +
                "\n";


            resumen +=
                "- Longitud título: " +
                (
                    datos.longitud_titulo ?? 0
                ) +
                " caracteres\n";


            resumen +=
                "- Meta description: " +
                (
                    datos.meta_description ||
                    "No encontrada"
                ) +
                "\n";


            resumen +=
                "- Longitud meta description: " +
                (
                    datos.longitud_meta_description ?? 0
                ) +
                " caracteres\n\n";


            /*
            |--------------------------------------------------------------------------
            | ESTRUCTURA
            |--------------------------------------------------------------------------
            */

            resumen +=
                "ESTRUCTURA:\n";


            resumen +=
                "- H1: " +
                (
                    datos.numero_h1 ?? 0
                ) +
                "\n";


            resumen +=
                "- H2: " +
                (
                    datos.numero_h2 ?? 0
                ) +
                "\n";


            resumen +=
                "- H3: " +
                (
                    datos.numero_h3 ?? 0
                ) +
                "\n";


            resumen +=
                "- Párrafos: " +
                (
                    datos.parrafos ?? 0
                ) +
                "\n";


            resumen +=
                "- Palabras aproximadas: " +
                (
                    datos.palabras_aproximadas ?? 0
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | ENCABEZADOS
            |--------------------------------------------------------------------------
            */

            resumen +=
                "ENCABEZADOS ENCONTRADOS:\n\n";


            resumen +=
                "H1:\n";


            if (
                Array.isArray(datos.h1) &&
                datos.h1.length > 0
            ) {

                datos.h1.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No se han encontrado H1.\n";

            }


            resumen +=
                "\nH2:\n";


            if (
                Array.isArray(datos.h2) &&
                datos.h2.length > 0
            ) {

                datos.h2.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No se han encontrado H2.\n";

            }


            resumen +=
                "\nH3:\n";


            if (
                Array.isArray(datos.h3) &&
                datos.h3.length > 0
            ) {

                datos.h3.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No se han encontrado H3.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | ÁRBOL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nÁRBOL DE ESTRUCTURA:\n";


            resumen +=
                (
                    datos.arbol_estructura ||
                    "No disponible"
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | IMÁGENES Y ENLACES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "IMÁGENES Y ENLACES:\n";


            resumen +=
                "- Imágenes: " +
                (
                    datos.imagenes_total ?? 0
                ) +
                "\n";


            resumen +=
                "- Imágenes sin ALT: " +
                (
                    datos.imagenes_sin_alt ?? 0
                ) +
                "\n";


            resumen +=
                "- Enlaces internos: " +
                (
                    datos.enlaces_internos ?? 0
                ) +
                "\n";


            resumen +=
                "- Enlaces externos: " +
                (
                    datos.enlaces_externos ?? 0
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | INDEXACIÓN
            |--------------------------------------------------------------------------
            */

            resumen +=
                "INDEXACIÓN:\n";


            resumen +=
                "- Canonical: " +
                (
                    datos.canonical ||
                    "No encontrada"
                ) +
                "\n";


            resumen +=
                "- Robots: " +
                (
                    datos.robots ||
                    "No detectado"
                ) +
                "\n";


            resumen +=
                "- robots.txt: " +
                (
                    datos.robots_txt
                        ? "Detectado"
                        : "No detectado"
                ) +
                "\n";


            resumen +=
                "- sitemap.xml: " +
                (
                    datos.sitemap
                        ? "Detectado"
                        : "No detectado"
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | METADATOS SOCIALES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "METADATOS SOCIALES:\n";


            resumen +=
                "- Open Graph: " +
                (
                    datos.open_graph_estado ||
                    "No detectado"
                ) +
                "\n";


            resumen +=
                "- Twitter Card: " +
                (
                    datos.twitter_card ||
                    "No detectada"
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | PROBLEMAS
            |--------------------------------------------------------------------------
            */

            resumen +=
                "PROBLEMAS DETECTADOS:\n";


            if (
                Array.isArray(datos.problemas) &&
                datos.problemas.length > 0
            ) {

                datos.problemas.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- Ninguno destacado.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | CORRECTOS
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nASPECTOS CORRECTOS:\n";


            if (
                Array.isArray(datos.correctos) &&
                datos.correctos.length > 0
            ) {

                datos.correctos.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No especificados.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | PRIORIDAD ALTA
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nPRIORIDAD ALTA:\n";


            if (
                Array.isArray(datos.prioridad_alta) &&
                datos.prioridad_alta.length > 0
            ) {

                datos.prioridad_alta.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- Ninguna.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | PRIORIDAD MEDIA
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nPRIORIDAD MEDIA:\n";


            if (
                Array.isArray(datos.prioridad_media) &&
                datos.prioridad_media.length > 0
            ) {

                datos.prioridad_media.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- Ninguna.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | OPORTUNIDADES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nOPORTUNIDADES DE MEJORA:\n";


            if (
                Array.isArray(datos.oportunidades) &&
                datos.oportunidades.length > 0
            ) {

                datos.oportunidades.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No especificadas.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | INSTRUCCIÓN FINAL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "INSTRUCCIÓN FINAL PARA VIZIUNEAI\n";


            resumen +=
                "Utiliza toda la información anterior como contexto de esta auditoría SEO.\n";


            resumen +=
                "Cuando el usuario haga preguntas relacionadas con esta web, analiza primero estos datos antes de responder.\n";


            resumen +=
                "No te limites a repetir el informe: interpreta los resultados, explica los problemas y proporciona recomendaciones prácticas.\n";


            resumen +=
                "Si el usuario quiere solucionar los problemas, conviértelos en acciones concretas y ordénalas según su prioridad e impacto.\n";


            resumen +=
                "Si necesitas información que no aparece en este informe, solicítala al usuario antes de realizar suposiciones.\n";


            resumenChat.value =
                resumen;


            /*
            |--------------------------------------------------------------------------
            | MOSTRAR ÁREAS
            |--------------------------------------------------------------------------
            */

            mostrarAreas(
                datos
            );


            resultado.hidden =
                false;


            resultado.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });

        }


        async function copiarResumen() {

            const texto =
                resumenChat.value.trim();


            if (texto === "") {

                return;

            }


            try {

                await navigator.clipboard.writeText(
                    texto
                );


                const textoOriginal =
                    botonCopiar.textContent;


                botonCopiar.textContent =
                    "✅ Informe copiado";


                setTimeout(
                    function () {

                        botonCopiar.textContent =
                            textoOriginal;

                    },
                    2000
                );


            } catch (e) {

                resumenChat.select();

                document.execCommand(
                    "copy"
                );


                const textoOriginal =
                    botonCopiar.textContent;


                botonCopiar.textContent =
                    "✅ Informe copiado";


                setTimeout(
                    function () {

                        botonCopiar.textContent =
                            textoOriginal;

                    },
                    2000
                );

            }

        }

    }
);

</script>


</body>

</html>