
<?php

session_start();

require_once 'config.php';


/* ==========================================
   COMPROBAR SESIÓN
========================================== */

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');

    exit;

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Auditoría SEO | ViziuneAI</title>

<link rel="stylesheet" href="css/style.css">

<link rel="stylesheet" href="css/herramientas.css">

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

    <span class="herramientas-etiqueta">

        VIZIUNEAI · SEO

    </span>

    <h1>

        Auditoría SEO

    </h1>

    <p>

        Analiza tu página web y descubre qué aspectos puedes
        mejorar para aumentar su visibilidad en buscadores.

    </p>

</div>


<!-- ======================================
     BLOQUE PRINCIPAL DE ANÁLISIS
======================================= -->

<article class="seo-panel">


    <!-- ==================================
         CABECERA DEL PANEL
    =================================== -->

    <div class="seo-panel-header">

        <div>

            <span class="seo-panel-kicker">

                ANÁLISIS SEO

            </span>

            <h2>

                Analiza tu página web

            </h2>

            <p>

                Introduce la URL de tu web y obtén un análisis
                SEO detallado con recomendaciones de mejora.

            </p>

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

                Estamos revisando los principales factores SEO.

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


            <!-- PUNTUACIÓN -->

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


            <!-- ESTADO GENERAL -->

            <div class="seo-summary-card">

                <span class="seo-summary-label">

                    ESTADO GENERAL

                </span>

                <strong>

                    Análisis completado

                </strong>

                <p>

                    Hemos revisado los principales elementos
                    técnicos y de contenido de tu página.

                </p>

            </div>


            <!-- RECOMENDACIONES -->

            <div class="seo-summary-card">

                <span class="seo-summary-label">

                    MEJORAS DETECTADAS

                </span>

                <strong>

                    Revisa tus recomendaciones

                </strong>

                <p>

                    Consulta los puntos que pueden mejorar
                    el posicionamiento de tu web.

                </p>

            </div>


        </div>


        <!-- ==================================
             RECOMENDACIONES
        =================================== -->

        <div class="seo-section-block">

            <div class="seo-section-heading">

                <div>

                    <span>

                        OPTIMIZACIÓN

                    </span>

                    <h3>

                        Recomendaciones

                    </h3>

                </div>

            </div>

            <div id="seoListaRecomendaciones"></div>

        </div>


        <!-- ==================================
             RESULTADO
        =================================== -->

        <div class="seo-section-block">

            <div class="seo-section-heading">

                <div>

                    <span>

                        ANÁLISIS

                    </span>

                    <h3>

                        Resultado de la auditoría

                    </h3>

                </div>

            </div>


            <!-- ==================================
                 MÉTRICAS
            =================================== -->

            <div class="seo-metricas">


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

                        Meta descripción

                    </span>

                    <strong id="seoDescripcion">

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

                        Contenido aproximado

                    </span>

                    <strong id="seoPalabras">

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


            </div>

        </div>


        <!-- ==================================
             ESTRUCTURA H1 / H2
        =================================== -->

        <div class="seo-details-grid">


            <div class="seo-details-card">

                <div class="seo-details-header">

                    <span>

                        ESTRUCTURA

                    </span>

                    <strong>

                        H1 encontrados

                    </strong>

                </div>

                <ul id="seoListaH1"></ul>

            </div>


            <div class="seo-details-card">

                <div class="seo-details-header">

                    <span>

                        ESTRUCTURA

                    </span>

                    <strong>

                        H2 encontrados

                    </strong>

                </div>

                <ul id="seoListaH2"></ul>

            </div>


        </div>


    </div>


</article>


</div>

</section>

</main>


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

        const boton =
            document.getElementById(
                "analizarSeo"
            );

        const urlInput =
            document.getElementById(
                "seoUrl"
            );

        const loading =
            document.getElementById(
                "seoLoading"
            );

        const error =
            document.getElementById(
                "seoError"
            );

        const resultado =
            document.getElementById(
                "seoResultado"
            );


        boton.addEventListener(
            "click",
            analizarSEO
        );


        urlInput.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Enter"
                ) {

                    event.preventDefault();

                    analizarSEO();

                }

            }
        );


        async function analizarSEO() {


            const url =
                urlInput.value.trim();


            /* ======================================
               VALIDAR URL
            ======================================= */

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
                !/^https?:\/\//i.test(
                    urlFinal
                )
            ) {

                urlFinal =
                    "https://" +
                    urlFinal;

            }


            try {

                new URL(
                    urlFinal
                );

            } catch (e) {

                mostrarError(
                    "La dirección introducida no es válida."
                );

                urlInput.focus();

                return;

            }


            /* ======================================
               ESTADO
            ======================================= */

            error.hidden =
                true;

            resultado.hidden =
                true;

            loading.hidden =
                false;

            boton.disabled =
                true;

            boton.textContent =
                "Analizando...";


            try {


                /* ==================================
                   PETICIÓN
                =================================== */

                const response =
                    await fetch(
                        "seo_auditoria.php",
                        {
                            method:
                                "POST",

                            headers:
                                {
                                    "Content-Type":
                                        "application/json",

                                    "Accept":
                                        "application/json"
                                },

                            body:
                                JSON.stringify(
                                    {
                                        url:
                                            urlFinal
                                    }
                                )
                        }
                    );


                const texto =
                    await response.text();


                let data;


                try {

                    data =
                        JSON.parse(
                            texto
                        );

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

                loading.hidden =
                    true;

                boton.disabled =
                    false;

                boton.textContent =
                    "Analizar mi web";

            }

        }


        /* ==========================================
           MOSTRAR ERROR
        ========================================== */

        function mostrarError(
            mensaje
        ) {

            error.textContent =
                "❌ " +
                mensaje;

            error.hidden =
                false;

            resultado.hidden =
                true;

        }


        /* ==========================================
           MOSTRAR RESULTADO
        ========================================== */

        function mostrarResultado(
            datos
        ) {


            /* ======================================
               PUNTUACIÓN
            ======================================= */

            document.getElementById(
                "seoPuntuacion"
            ).textContent =
                (datos.puntuacion ?? 0) +
                "/100";


            document.getElementById(
                "seoNivel"
            ).textContent =
                datos.nivel ||
                "Sin valorar";


            /* ======================================
               RECOMENDACIONES
            ======================================= */

            const listaRecomendaciones =
                document.getElementById(
                    "seoListaRecomendaciones"
                );


            listaRecomendaciones.innerHTML =
                "";


            if (
                datos.recomendaciones &&
                datos.recomendaciones.length > 0
            ) {


                datos.recomendaciones.forEach(
                    function (recomendacion) {


                        const elemento =
                            document.createElement(
                                "div"
                            );


                        elemento.className =
                            "seo-recomendacion " +
                            (
                                recomendacion.tipo ||
                                "warning"
                            );


                        const titulo =
                            document.createElement(
                                "strong"
                            );


                        titulo.textContent =
                            recomendacion.titulo ||
                            "Recomendación";


                        const texto =
                            document.createElement(
                                "p"
                            );


                        texto.textContent =
                            recomendacion.texto ||
                            "";


                        elemento.appendChild(
                            titulo
                        );


                        elemento.appendChild(
                            texto
                        );


                        listaRecomendaciones.appendChild(
                            elemento
                        );

                    }
                );


            } else {


                const elemento =
                    document.createElement(
                        "div"
                    );


                elemento.className =
                    "seo-recomendacion";


                elemento.textContent =
                    "No se han detectado recomendaciones adicionales.";


                listaRecomendaciones.appendChild(
                    elemento
                );

            }


            /* ======================================
               MÉTRICAS
            ======================================= */

            document.getElementById(
                "seoTitulo"
            ).textContent =
                datos.titulo ||
                "No encontrado";


            document.getElementById(
                "seoDescripcion"
            ).textContent =
                datos.meta_description ||
                "No encontrada";


            document.getElementById(
                "seoH1"
            ).textContent =
                datos.numero_h1;


            document.getElementById(
                "seoH2"
            ).textContent =
                datos.numero_h2;


            document.getElementById(
                "seoImagenes"
            ).textContent =
                datos.imagenes_total;


            document.getElementById(
                "seoImagenesAlt"
            ).textContent =
                datos.imagenes_sin_alt;


            document.getElementById(
                "seoInternos"
            ).textContent =
                datos.enlaces_internos;


            document.getElementById(
                "seoExternos"
            ).textContent =
                datos.enlaces_externos;


            document.getElementById(
                "seoPalabras"
            ).textContent =
                datos.palabras_aproximadas;


            document.getElementById(
                "seoCanonical"
            ).textContent =
                datos.canonical ||
                "No encontrada";


            /* ======================================
               H1
            ======================================= */

            const listaH1 =
                document.getElementById(
                    "seoListaH1"
                );


            listaH1.innerHTML =
                "";


            if (
                datos.h1 &&
                datos.h1.length > 0
            ) {


                datos.h1.forEach(
                    function (texto) {


                        const li =
                            document.createElement(
                                "li"
                            );


                        li.textContent =
                            texto;


                        listaH1.appendChild(
                            li
                        );

                    }
                );


            } else {


                const li =
                    document.createElement(
                        "li"
                    );


                li.textContent =
                    "No se encontraron H1.";


                listaH1.appendChild(
                    li
                );

            }


            /* ======================================
               H2
            ======================================= */

            const listaH2 =
                document.getElementById(
                    "seoListaH2"
                );


            listaH2.innerHTML =
                "";


            if (
                datos.h2 &&
                datos.h2.length > 0
            ) {


                datos.h2.forEach(
                    function (texto) {


                        const li =
                            document.createElement(
                                "li"
                            );


                        li.textContent =
                            texto;


                        listaH2.appendChild(
                            li
                        );

                    }
                );


            } else {


                const li =
                    document.createElement(
                        "li"
                    );


                li.textContent =
                    "No se encontraron H2.";


                listaH2.appendChild(
                    li
                );

            }


            /* ======================================
               MOSTRAR RESULTADO
            ======================================= */

            resultado.hidden =
                false;


            resultado.scrollIntoView(
                {
                    behavior:
                        "smooth",

                    block:
                        "start"
                }
            );

        }

    }

);

</script>


</body>

</html>
