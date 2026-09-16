
/* ==========================================================================
   VIZIUNEAI - SEGURIDAD DE LA CUENTA
   MOSTRAR / OCULTAR CONTRASEÑA
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    const botones = document.querySelectorAll('.toggle-password');


    botones.forEach(function (boton) {

        boton.addEventListener('click', function (evento) {

            evento.preventDefault();


            /* ==============================================================
               OBTENER CAMPO
               ============================================================== */

            const idCampo = boton.dataset.target;

            const campo = document.getElementById(idCampo);


            if (!campo) {
                return;
            }


            /* ==============================================================
               MOSTRAR CONTRASEÑA
               ============================================================== */

            if (campo.type === 'password') {

                campo.type = 'text';

                boton.textContent = '(o)';

                boton.setAttribute(
                    'aria-label',
                    'Ocultar contraseña'
                );

                boton.setAttribute(
                    'title',
                    'Ocultar contraseña'
                );


            /* ==============================================================
               OCULTAR CONTRASEÑA
               ============================================================== */

            } else {

                campo.type = 'password';

                boton.textContent = '()';

                boton.setAttribute(
                    'aria-label',
                    'Mostrar contraseña'
                );

                boton.setAttribute(
                    'title',
                    'Mostrar contraseña'
                );

            }

        });

    });

});
