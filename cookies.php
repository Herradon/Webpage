
<?php

?>


<div
    id="cookiesModal"
    class="cookies-overlay"
    hidden
>

    <div
        class="cookies-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cookiesTitulo"
    >

        <!-- ==========================================================
             AVISO PRINCIPAL
        =========================================================== -->

        <div id="cookiesPrincipal">

            <div class="cookies-header">

                <h2 id="cookiesTitulo">
                    Utilizamos cookies
                </h2>

            </div>


            <div class="cookies-contenido">

                <p>
                    En Viziune utilizamos cookies para garantizar el
                    funcionamiento de la página web, y mejorar tu experiencia
                    de navegación y si lo autorizas, obtener información
                    sobre el uso de nuestros servicios (Analiticas, marketing y generales).
                </p>

                <p>
                    Puedes aceptar todas las cookies, rechazarlas o configurar
                    tus preferencias.
                </p>


                <a
                    href="politica_cookies.php"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="cookies-enlace"
                >
                    Consultar política de cookies completa
                </a>

            </div>


            <div class="cookies-acciones">

                <button
                    type="button"
                    id="cookiesRechazar"
                    class="cookies-boton cookies-rechazar"
                >
                    Rechazar
                </button>


                <button
                    type="button"
                    id="cookiesConfigurar"
                    class="cookies-boton cookies-configurar"
                >
                    Configurar
                </button>


                <button
                    type="button"
                    id="cookiesAceptar"
                    class="cookies-boton cookies-aceptar"
                >
                    Aceptar todas
                </button>

            </div>

        </div>


        <!-- ==========================================================
             CONFIGURACIÓN
        =========================================================== -->

        <div
            id="cookiesConfiguracion"
            class="cookies-configuracion"
            hidden
        >

            <div class="cookies-header">

                <h2>
                    Configurar cookies
                </h2>

            </div>


            <div class="cookies-contenido">

                <p>
                    Puedes seleccionar qué categorías de cookies quieres
                    permitir. Las cookies necesarias no pueden desactivarse
                    porque son imprescindibles para el funcionamiento básico
                    del sitio web.
                </p>

            </div>


            <!-- ======================================================
                 NECESARIAS
            ======================================================= -->

            <div class="cookies-opcion">

                <div class="cookies-opcion-texto">

                    <strong>
                        Cookies necesarias
                    </strong>

                    <span>
                        Necesarias para el funcionamiento básico,
                        la seguridad y determinadas funciones del sitio.
                    </span>

                </div>


                <label class="cookies-switch">

                    <input
                        type="checkbox"
                        checked
                        disabled
                    >

                    <span class="cookies-slider"></span>

                </label>

            </div>


            <!-- ======================================================
                 PREFERENCIAS
            ======================================================= -->

            <div class="cookies-opcion">

                <div class="cookies-opcion-texto">

                    <strong>
                        Cookies de preferencias
                    </strong>

                    <span>
                        Permiten recordar determinadas preferencias y
                        configuraciones del usuario.
                    </span>

                </div>


                <label class="cookies-switch">

                    <input
                        type="checkbox"
                        id="cookiesPreferencias"
                    >

                    <span class="cookies-slider"></span>

                </label>

            </div>


            <!-- ======================================================
                 ANALÍTICAS
            ======================================================= -->

            <div class="cookies-opcion">

                <div class="cookies-opcion-texto">

                    <strong>
                        Cookies analíticas
                    </strong>

                    <span>
                        Permiten obtener información estadística sobre el
                        uso de la página y mejorar nuestros servicios.
                    </span>

                </div>


                <label class="cookies-switch">

                    <input
                        type="checkbox"
                        id="cookiesAnaliticas"
                    >

                    <span class="cookies-slider"></span>

                </label>

            </div>


            <!-- ======================================================
                 MARKETING
            ======================================================= -->

            <div class="cookies-opcion">

                <div class="cookies-opcion-texto">

                    <strong>
                        Cookies de marketing
                    </strong>

                    <span>
                        Permiten utilizar determinadas tecnologías
                        relacionadas con publicidad o marketing.
                    </span>

                </div>


                <label class="cookies-switch">

                    <input
                        type="checkbox"
                        id="cookiesMarketing"
                    >

                    <span class="cookies-slider"></span>

                </label>

            </div>


            <div class="cookies-configuracion-acciones">

                <button
                    type="button"
                    id="cookiesVolver"
                    class="cookies-boton cookies-rechazar"
                >
                    ← Volver
                </button>


                <button
                    type="button"
                    id="cookiesGuardar"
                    class="cookies-boton cookies-aceptar"
                >
                    Guardar preferencias
                </button>

            </div>


            <div class="cookies-politica-configuracion">

                <a
                    href="politica_cookies.php"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Leer la política de cookies completa
                </a>

            </div>

        </div>

    </div>

</div>


<style>

/* ==============================================================
   OVERLAY
============================================================== */

.cookies-overlay {

    position: fixed;

    inset: 0;

    z-index: 999998;

    display: flex;

    align-items: flex-end;

    justify-content: center;

    padding: 20px;

    box-sizing: border-box;

    background:
        rgba(0, 0, 0, 0.65);

    backdrop-filter:
        blur(5px);

}


.cookies-overlay[hidden] {

    display: none;

}


/* ==============================================================
   MODAL
============================================================== */

.cookies-modal {

    width: 100%;

    max-width: 900px;

    max-height: 90vh;

    overflow-y: auto;

    box-sizing: border-box;

    padding: 30px;

    border-radius: 18px;

    background: #0d1821;

    border:
        1px solid rgba(0, 207, 224, 0.25);

    box-shadow:
        0 25px 80px rgba(0, 0, 0, 0.55);

    color: #ffffff;

}


/* ==============================================================
   CABECERA
============================================================== */

.cookies-header {

    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 20px;

}


.cookies-icon {

    font-size: 30px;

    line-height: 1;

}


.cookies-header h2 {

    margin: 0;

    color: #00cfe0;

    font-size: 25px;

    line-height: 1.25;

}


/* ==============================================================
   CONTENIDO
============================================================== */

.cookies-contenido {

    color: #c3d0da;

    font-size: 14px;

    line-height: 1.7;

}


.cookies-contenido p {

    margin:
        0 0 14px;

}


.cookies-enlace {

    display: inline-block;

    margin-top: 3px;

    color: #00cfe0;

    text-decoration: none;

    font-weight: 600;

}


.cookies-enlace:hover {

    text-decoration: underline;

}


/* ==============================================================
   BOTONES
============================================================== */

.cookies-acciones {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 10px;

    margin-top: 25px;

    padding-top: 20px;

    border-top:
        1px solid rgba(255, 255, 255, 0.08);

}


.cookies-boton {

    min-height: 44px;

    border-radius: 9px;

    padding:
        11px 20px;

    box-sizing: border-box;

    font-family: inherit;

    font-size: 14px;

    font-weight: 700;

    cursor: pointer;

    transition:
        0.2s ease;

}


.cookies-boton:hover {

    transform:
        translateY(-1px);

}


.cookies-boton:focus-visible {

    outline:
        2px solid #00cfe0;

    outline-offset:
        2px;

}


.cookies-rechazar {

    background:
        transparent;

    color:
        #c3d0da;

    border:
        1px solid rgba(255, 255, 255, 0.18);

}


.cookies-rechazar:hover {

    background:
        rgba(255, 255, 255, 0.05);

}


.cookies-configurar {

    background:
        rgba(0, 207, 224, 0.08);

    color:
        #00cfe0;

    border:
        1px solid rgba(0, 207, 224, 0.25);

}


.cookies-configurar:hover {

    background:
        rgba(0, 207, 224, 0.14);

}


.cookies-aceptar {

    background:
        #00cfe0;

    color:
        #061018;

    border:
        1px solid #00cfe0;

}


.cookies-aceptar:hover {

    opacity:
        0.92;

}


/* ==============================================================
   CONFIGURACIÓN
============================================================== */

.cookies-configuracion[hidden] {

    display: none;

}


.cookies-opcion {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-top: 12px;

    padding: 17px 18px;

    border:
        1px solid rgba(255, 255, 255, 0.08);

    border-radius: 11px;

    background:
        rgba(255, 255, 255, 0.025);

}


.cookies-opcion-texto {

    display: flex;

    flex-direction: column;

    gap: 5px;

}


.cookies-opcion-texto strong {

    color:
        #ffffff;

    font-size:
        15px;

}


.cookies-opcion-texto span {

    color:
        #8fa0ad;

    font-size:
        13px;

    line-height:
        1.5;

}


/* ==============================================================
   SWITCH
============================================================== */

.cookies-switch {

    position:
        relative;

    display:
        inline-block;

    width:
        48px;

    min-width:
        48px;

    height:
        26px;

}


.cookies-switch input {

    width:
        0;

    height:
        0;

    opacity:
        0;

}


.cookies-slider {

    position:
        absolute;

    inset:
        0;

    cursor:
        pointer;

    border-radius:
        30px;

    background:
        #33424d;

    transition:
        0.2s ease;

}


.cookies-slider::before {

    content:
        "";

    position:
        absolute;

    width:
        20px;

    height:
        20px;

    left:
        3px;

    top:
        3px;

    border-radius:
        50%;

    background:
        #ffffff;

    transition:
        0.2s ease;

}


.cookies-switch input:checked + .cookies-slider {

    background:
        #00cfe0;

}


.cookies-switch input:checked + .cookies-slider::before {

    transform:
        translateX(22px);

}


.cookies-switch input:disabled + .cookies-slider {

    opacity:
        0.65;

    cursor:
        not-allowed;

}


/* ==============================================================
   BOTONES CONFIGURACIÓN
============================================================== */

.cookies-configuracion-acciones {

    display:
        flex;

    justify-content:
        space-between;

    gap:
        10px;

    margin-top:
        25px;

    padding-top:
        20px;

    border-top:
        1px solid rgba(255, 255, 255, 0.08);

}


.cookies-politica-configuracion {

    margin-top:
        18px;

    text-align:
        center;

}


.cookies-politica-configuracion a {

    color:
        #00cfe0;

    font-size:
        13px;

    text-decoration:
        none;

}


.cookies-politica-configuracion a:hover {

    text-decoration:
        underline;

}


/* ==============================================================
   MÓVIL
============================================================== */

@media (max-width: 650px) {

    .cookies-overlay {

        align-items:
            flex-end;

        padding:
            10px;

    }


    .cookies-modal {

        max-height:
            92vh;

        padding:
            22px 18px;

        border-radius:
            15px;

    }


    .cookies-header h2 {

        font-size:
            21px;

    }


    .cookies-contenido {

        font-size:
            13px;

    }


    .cookies-acciones {

        flex-direction:
            column;

        align-items:
            stretch;

    }


    .cookies-boton {

        width:
            100%;

    }


    .cookies-opcion {

        align-items:
            flex-start;

    }


    .cookies-opcion-texto {

        max-width:
            calc(100% - 60px);

    }


    .cookies-configuracion-acciones {

        flex-direction:
            column;

    }

}

</style>


<script>

(function () {

    'use strict';


    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN
    |--------------------------------------------------------------------------
    */

    const NOMBRE_COOKIE =
        'viz_cookie_consent';


    const DURACION_DIAS =
        365;


    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS
    |--------------------------------------------------------------------------
    */

    const modal =
        document.getElementById('cookiesModal');


    const principal =
        document.getElementById('cookiesPrincipal');


    const configuracion =
        document.getElementById('cookiesConfiguracion');


    const aceptar =
        document.getElementById('cookiesAceptar');


    const rechazar =
        document.getElementById('cookiesRechazar');


    const configurar =
        document.getElementById('cookiesConfigurar');


    const volver =
        document.getElementById('cookiesVolver');


    const guardar =
        document.getElementById('cookiesGuardar');


    const preferencias =
        document.getElementById('cookiesPreferencias');


    const analiticas =
        document.getElementById('cookiesAnaliticas');


    const marketing =
        document.getElementById('cookiesMarketing');


    if (!modal) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | LEER COOKIE
    |--------------------------------------------------------------------------
    */

    function obtenerCookie(nombre) {

        const cookies =
            document.cookie
                .split(';')
                .map(function (cookie) {

                    return cookie.trim();

                });


        const prefijo =
            nombre + '=';


        for (
            let i = 0;
            i < cookies.length;
            i++
        ) {

            if (
                cookies[i].indexOf(prefijo) === 0
            ) {

                return decodeURIComponent(
                    cookies[i].substring(
                        prefijo.length
                    )
                );

            }

        }


        return null;

    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR COOKIE
    |--------------------------------------------------------------------------
    */

    function guardarCookieConsentimiento(datos) {

        const fecha =
            new Date();


        fecha.setTime(
            fecha.getTime() +
            (
                DURACION_DIAS *
                24 *
                60 *
                60 *
                1000
            )
        );


        const valor =
            encodeURIComponent(
                JSON.stringify(datos)
            );


        document.cookie =
            NOMBRE_COOKIE +
            '=' +
            valor +
            '; expires=' +
            fecha.toUTCString() +
            '; path=/; SameSite=Lax';

    }


    /*
    |--------------------------------------------------------------------------
    | OBTENER CONSENTIMIENTO
    |--------------------------------------------------------------------------
    */

    function obtenerConsentimiento() {

        const cookie =
            obtenerCookie(
                NOMBRE_COOKIE
            );


        if (!cookie) {

            return null;

        }


        try {

            return JSON.parse(cookie);

        } catch (error) {

            return null;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CERRAR MODAL
    |--------------------------------------------------------------------------
    */

    function cerrarModal() {

        modal.hidden = true;

        document.body.classList.remove(
            'cookies-modal-abierto'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ABRIR MODAL
    |--------------------------------------------------------------------------
    */

    function abrirModal() {

        modal.hidden = false;

        document.body.classList.add(
            'cookies-modal-abierto'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | MOSTRAR PRINCIPAL
    |--------------------------------------------------------------------------
    */

    function mostrarPrincipal() {

        principal.hidden = false;

        configuracion.hidden = true;

    }


    /*
    |--------------------------------------------------------------------------
    | MOSTRAR CONFIGURACIÓN
    |--------------------------------------------------------------------------
    */

    function mostrarConfiguracion() {

        principal.hidden = true;

        configuracion.hidden = false;


        const actual =
            obtenerConsentimiento();


        if (actual) {

            preferencias.checked =
                actual.preferencias === true;


            analiticas.checked =
                actual.analiticas === true;


            marketing.checked =
                actual.marketing === true;

        } else {

            preferencias.checked =
                false;

            analiticas.checked =
                false;

            marketing.checked =
                false;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR PREFERENCIAS
    |--------------------------------------------------------------------------
    */

    function guardarPreferencias() {

        const consentimiento = {

            necesarias: true,

            preferencias:
                preferencias.checked === true,

            analiticas:
                analiticas.checked === true,

            marketing:
                marketing.checked === true,

            fecha:
                new Date().toISOString(),

            version:
                '1.0'

        };


        guardarCookieConsentimiento(
            consentimiento
        );


        aplicarConsentimiento(
            consentimiento
        );


        cerrarModal();

    }


    /*
    |--------------------------------------------------------------------------
    | ACEPTAR TODAS
    |--------------------------------------------------------------------------
    */

    function aceptarTodas() {

        const consentimiento = {

            necesarias: true,

            preferencias: true,

            analiticas: true,

            marketing: true,

            fecha:
                new Date().toISOString(),

            version:
                '1.0'

        };


        guardarCookieConsentimiento(
            consentimiento
        );


        aplicarConsentimiento(
            consentimiento
        );


        cerrarModal();

    }


    /*
    |--------------------------------------------------------------------------
    | RECHAZAR
    |--------------------------------------------------------------------------
    */

    function rechazarCookies() {

        const consentimiento = {

            necesarias: true,

            preferencias: false,

            analiticas: false,

            marketing: false,

            fecha:
                new Date().toISOString(),

            version:
                '1.0'

        };


        guardarCookieConsentimiento(
            consentimiento
        );


        aplicarConsentimiento(
            consentimiento
        );


        cerrarModal();

    }


    /*
    |--------------------------------------------------------------------------
    | APLICAR CONSENTIMIENTO
    |--------------------------------------------------------------------------
    |
    | Google Analytics queda controlado por la opción
    | "Cookies analíticas".
    |
    */

    function aplicarConsentimiento(
        consentimiento
    ) {

        /*
        |--------------------------------------------------------------------------
        | GOOGLE CONSENT MODE
        |--------------------------------------------------------------------------
        */

        if (
            typeof gtag === 'function'
        ) {

            gtag(
                'consent',
                'update',
                {

                    analytics_storage:
                        consentimiento.analiticas === true
                            ? 'granted'
                            : 'denied',

                    ad_storage:
                        consentimiento.marketing === true
                            ? 'granted'
                            : 'denied',

                    ad_user_data:
                        consentimiento.marketing === true
                            ? 'granted'
                            : 'denied',

                    ad_personalization:
                        consentimiento.marketing === true
                            ? 'granted'
                            : 'denied'

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | GUARDAR ESTADO EN JAVASCRIPT
        |--------------------------------------------------------------------------
        */

        window.viziuneCookieConsent =
            consentimiento;


        /*
        |--------------------------------------------------------------------------
        | EVENTO PERSONALIZADO
        |--------------------------------------------------------------------------
        */

        document.dispatchEvent(
            new CustomEvent(
                'viziuneCookieConsentChanged',
                {
                    detail:
                        consentimiento
                }
            )
        );

    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN ACEPTAR
    |--------------------------------------------------------------------------
    */

    if (aceptar) {

        aceptar.addEventListener(
            'click',
            aceptarTodas
        );

    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN RECHAZAR
    |--------------------------------------------------------------------------
    */

    if (rechazar) {

        rechazar.addEventListener(
            'click',
            rechazarCookies
        );

    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN CONFIGURAR
    |--------------------------------------------------------------------------
    */

    if (configurar) {

        configurar.addEventListener(
            'click',
            mostrarConfiguracion
        );

    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN VOLVER
    |--------------------------------------------------------------------------
    */

    if (volver) {

        volver.addEventListener(
            'click',
            mostrarPrincipal
        );

    }


    /*
    |--------------------------------------------------------------------------
    | BOTÓN GUARDAR
    |--------------------------------------------------------------------------
    */

    if (guardar) {

        guardar.addEventListener(
            'click',
            guardarPreferencias
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FUNCIÓN GLOBAL PARA VOLVER A ABRIR PREFERENCIAS
    |--------------------------------------------------------------------------
    */

    window.mostrarPreferenciasCookies =
        function () {

            abrirModal();

            mostrarConfiguracion();

        };


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR SI YA EXISTE UNA DECISIÓN
    |--------------------------------------------------------------------------
    */

    const consentimientoInicial =
        obtenerConsentimiento();


    if (
        consentimientoInicial
    ) {

        aplicarConsentimiento(
            consentimientoInicial
        );

        cerrarModal();

    } else {

        abrirModal();

        mostrarPrincipal();

    }


})();

</script>