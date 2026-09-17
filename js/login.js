
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("loginForm");

    const email = document.getElementById("email");

    const password = document.getElementById("password");

    const loginButton = document.getElementById("loginButton");


    /* ==========================================================================
       MOSTRAR / OCULTAR CONTRASEÑA
       ========================================================================== */

    const togglePassword = document.querySelector(".toggle-password");

    if (togglePassword && password) {

        togglePassword.addEventListener("click", function (event) {

            event.preventDefault();

            if (password.type === "password") {

                password.type = "text";

                togglePassword.textContent = "◉";

                togglePassword.setAttribute(
                    "aria-label",
                    "Ocultar contraseña"
                );

                togglePassword.setAttribute(
                    "title",
                    "Ocultar contraseña"
                );

            } else {

                password.type = "password";

                togglePassword.textContent = "◎";

                togglePassword.setAttribute(
                    "aria-label",
                    "Mostrar contraseña"
                );

                togglePassword.setAttribute(
                    "title",
                    "Mostrar contraseña"
                );

            }

        });

    }


    /* ==========================================================================
       VALIDACIÓN DEL FORMULARIO
       ========================================================================== */

    if (!form) {

        return;

    }

    form.addEventListener("submit", function (event) {

        const emailValue = email.value.trim();

        const passwordValue = password.value;

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


        if (!emailValue) {

            event.preventDefault();

            alert("Introduce tu correo electrónico.");

            email.focus();

            return;

        }


        if (!emailRegex.test(emailValue)) {

            event.preventDefault();

            alert("Introduce un correo electrónico válido.");

            email.focus();

            return;

        }


        if (!passwordValue) {

            event.preventDefault();

            alert("Introduce tu contraseña.");

            password.focus();

            return;

        }


        loginButton.disabled = true;

        loginButton.textContent = "Iniciando sesión...";

    });

});

