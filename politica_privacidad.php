<?php
/*
|--------------------------------------------------------------------------
| POLÍTICA DE PRIVACIDAD
|--------------------------------------------------------------------------
|
| Este archivo se incluye desde index.php cuando el usuario
| inicia sesión y todavía no ha aceptado el aviso de privacidad.
|
*/
?>

<div
    id="politicaPrivacidadModal"
    class="politica-privacidad-overlay"
>

    <div
        class="politica-privacidad-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="politicaPrivacidadTitulo"
    >

        <div class="politica-privacidad-header">

            <h2 id="politicaPrivacidadTitulo">
                Política de privacidad
            </h2>

        </div>


        <div class="politica-privacidad-contenido">

            <p>
                Antes de continuar utilizando las funciones privadas de
                Viziune, necesitamos que leas y aceptes nuestra política
                de privacidad.
            </p>

            <p>
                Trataremos los datos que nos facilites con el objetivo de
                gestionar tu cuenta, prestarte los servicios solicitados,
                gestionar tus comunicaciones y atender tus solicitudes.
            </p>

            <p>
                Tus datos serán tratados de acuerdo con nuestra política
                de privacidad y la normativa aplicable en materia de
                protección de datos.
            </p>

            <p>
                Puedes consultar la información completa sobre el tratamiento
                de tus datos antes de continuar.
            </p>


            <a
                href="politica_entera.php"
                target="_blank"
                class="politica-privacidad-enlace"
            >
                Consultar política de privacidad completa
            </a>

        </div>


        <div class="politica-privacidad-acciones">

            <button
                type="button"
                id="aceptarPoliticaPrivacidad"
                class="politica-privacidad-aceptar"
            >
                Aceptar y continuar
            </button>

        </div>

    </div>

</div>


<style>

    /* ==========================================================
       MODAL POLÍTICA DE PRIVACIDAD
    ========================================================== */

    .politica-privacidad-overlay {

        position: fixed;

        inset: 0;

        z-index: 999999;

        display: flex;

        align-items: center;

        justify-content: center;

        padding: 20px;

        box-sizing: border-box;

        background: rgba(0, 0, 0, 0.82);

        backdrop-filter: blur(6px);

    }


    .politica-privacidad-modal {

        width: 100%;

        max-width: 620px;

        max-height: 90vh;

        overflow-y: auto;

        box-sizing: border-box;

        padding: 30px;

        border-radius: 18px;

        background: #0d1821;

        border: 1px solid rgba(0, 207, 224, 0.25);

        box-shadow:
            0 25px 80px rgba(0, 0, 0, 0.55);

        color: #ffffff;

    }


    .politica-privacidad-header {

        display: flex;

        align-items: center;

        gap: 14px;

        margin-bottom: 25px;

    }


    .politica-privacidad-icon {

        font-size: 30px;

    }


    .politica-privacidad-header h2 {

        margin: 0;

        color: #00cfe0;

        font-size: 25px;

    }


    .politica-privacidad-contenido {

        color: #c3d0da;

        line-height: 1.7;

        font-size: 15px;

    }


    .politica-privacidad-contenido p {

        margin: 0 0 17px;

    }


    .politica-privacidad-enlace {

        display: inline-block;

        margin-top: 5px;

        color: #00cfe0;

        text-decoration: none;

        font-weight: 600;

    }


    .politica-privacidad-enlace:hover {

        text-decoration: underline;

    }


    .politica-privacidad-acciones {

        display: flex;

        justify-content: flex-end;

        margin-top: 28px;

        padding-top: 20px;

        border-top: 1px solid rgba(255, 255, 255, 0.08);

    }


    .politica-privacidad-aceptar {

        border: 0;

        border-radius: 9px;

        padding: 13px 24px;

        background: #00cfe0;

        color: #061018;

        font-size: 15px;

        font-weight: 700;

        cursor: pointer;

        transition: 0.2s ease;

    }


    .politica-privacidad-aceptar:hover {

        transform: translateY(-1px);

        opacity: 0.92;

    }


    .politica-privacidad-aceptar:disabled {

        opacity: 0.6;

        cursor: wait;

        transform: none;

    }


    /* ==========================================================
       MÓVIL
    ========================================================== */

    @media (max-width: 600px) {

        .politica-privacidad-overlay {

            padding: 14px;

        }


        .politica-privacidad-modal {

            padding: 22px;

            max-height: 92vh;

            border-radius: 15px;

        }


        .politica-privacidad-header h2 {

            font-size: 21px;

        }


        .politica-privacidad-contenido {

            font-size: 14px;

        }


        .politica-privacidad-acciones {

            justify-content: stretch;

        }


        .politica-privacidad-aceptar {

            width: 100%;

        }

    }

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const botonAceptar =
        document.getElementById('aceptarPoliticaPrivacidad');

    if (!botonAceptar) {
        return;
    }


    botonAceptar.addEventListener('click', function () {

        botonAceptar.disabled = true;

        botonAceptar.textContent = 'Guardando...';


        fetch('aceptar_privacidad.php', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json'
            }

        })

        .then(function (response) {

            return response.json();

        })

        .then(function (data) {

            if (data.success) {

                const modal =
                    document.getElementById('politicaPrivacidadModal');

                if (modal) {
                    modal.remove();
                }

            } else {

                botonAceptar.disabled = false;

                botonAceptar.textContent =
                    'Aceptar y continuar';

                alert(
                    'No se ha podido guardar la aceptación. Inténtalo de nuevo.'
                );

            }

        })

        .catch(function (error) {

            console.error(
                'Error aceptando la política de privacidad:',
                error
            );

            botonAceptar.disabled = false;

            botonAceptar.textContent =
                'Aceptar y continuar';

            alert(
                'No se ha podido guardar la aceptación. Comprueba tu conexión e inténtalo de nuevo.'
            );

        });

    });

});

</script>