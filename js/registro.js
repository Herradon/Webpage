
document.addEventListener('DOMContentLoaded', () => {

    const formulario = document.getElementById('formRegistro');

    const nombre = document.getElementById('nombre');

    const email = document.getElementById('email');

    const password = document.getElementById('password');

    const passwordConfirmacion =
        document.getElementById('password_confirmacion');

    const botonRegistro =
        document.getElementById('botonRegistro');

    const mensajeError =
        document.getElementById('mensajeError');


    /* ==========================================
       MOSTRAR / OCULTAR CONTRASEÑA
    ========================================== */

    document.querySelectorAll('.toggle-password').forEach(boton => {

        boton.addEventListener('click', () => {

            const idObjetivo = boton.dataset.target;

            const campo = document.getElementById(idObjetivo);

            if (!campo) {
                return;
            }

            if (campo.type === 'password') {

                campo.type = 'text';

                boton.textContent = '◉';

                boton.setAttribute(
                    'aria-label',
                    'Ocultar contraseña'
                );

                boton.setAttribute(
                    'title',
                    'Ocultar contraseña'
                );

            } else {

                campo.type = 'password';

                boton.textContent = '◎';

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


    /* ==========================================
       FUNCIONES
    ========================================== */

    function limpiarErrores() {

        document
            .querySelectorAll('.campo input')
            .forEach(input => {

                input.classList.remove('error');

            });


        document
            .querySelectorAll('.campo small')
            .forEach(elemento => {

                elemento.textContent = '';

            });


        mensajeError.hidden = true;

        mensajeError.textContent = '';

    }


    function mostrarErrorCampo(input, elemento, mensaje) {

        input.classList.add('error');

        elemento.textContent = mensaje;

    }


    function validarEmail(valor) {

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);

    }


    /* ==========================================
       VALIDAR FORMULARIO
    ========================================== */

    function validarFormulario() {

        limpiarErrores();

        let valido = true;


        const nombreValor =
            nombre.value.trim();

        const emailValor =
            email.value.trim();

        const passwordValor =
            password.value;

        const passwordConfirmacionValor =
            passwordConfirmacion.value;


        /* ------------------------------------------
           NOMBRE
        ------------------------------------------ */

        if (nombreValor === '') {

            mostrarErrorCampo(
                nombre,
                document.getElementById('errorNombre'),
                'Introduce tu nombre.'
            );

            valido = false;

        } else if (nombreValor.length > 150) {

            mostrarErrorCampo(
                nombre,
                document.getElementById('errorNombre'),
                'El nombre es demasiado largo.'
            );

            valido = false;

        }


        /* ------------------------------------------
           EMAIL
        ------------------------------------------ */

        if (emailValor === '') {

            mostrarErrorCampo(
                email,
                document.getElementById('errorEmail'),
                'Introduce tu correo electrónico.'
            );

            valido = false;

        } else if (!validarEmail(emailValor)) {

            mostrarErrorCampo(
                email,
                document.getElementById('errorEmail'),
                'Introduce un correo electrónico válido.'
            );

            valido = false;

        }


        /* ------------------------------------------
           PASSWORD
        ------------------------------------------ */

        if (passwordValor === '') {

            mostrarErrorCampo(
                password,
                document.getElementById('errorPassword'),
                'Introduce una contraseña.'
            );

            valido = false;

        } else if (passwordValor.length < 8) {

            mostrarErrorCampo(
                password,
                document.getElementById('errorPassword'),
                'La contraseña debe tener al menos 8 caracteres.'
            );

            valido = false;

        }


        /* ------------------------------------------
           CONFIRMAR PASSWORD
        ------------------------------------------ */

        if (
            passwordConfirmacionValor === ''
        ) {

            mostrarErrorCampo(
                passwordConfirmacion,
                document.getElementById(
                    'errorPasswordConfirmacion'
                ),
                'Repite la contraseña.'
            );

            valido = false;

        } else if (
            passwordValor !==
            passwordConfirmacionValor
        ) {

            mostrarErrorCampo(
                passwordConfirmacion,
                document.getElementById(
                    'errorPasswordConfirmacion'
                ),
                'Las contraseñas no coinciden.'
            );

            valido = false;

        }


        return valido;

    }


    /* ==========================================
       ENVÍO
    ========================================== */

    formulario.addEventListener(
        'submit',
        event => {

            if (!validarFormulario()) {

                event.preventDefault();

                return;

            }


            botonRegistro.disabled = true;

            botonRegistro.textContent =
                'Creando cuenta...';

        }
    );


    /* ==========================================
       VALIDACIÓN EN TIEMPO REAL
    ========================================== */

    passwordConfirmacion.addEventListener(
        'input',
        () => {

            const confirmacion =
                passwordConfirmacion.value;


            if (
                confirmacion !== '' &&
                password.value !== confirmacion
            ) {

                passwordConfirmacion.classList.add(
                    'error'
                );

            } else {

                passwordConfirmacion.classList.remove(
                    'error'
                );

            }

        }
    );

});
