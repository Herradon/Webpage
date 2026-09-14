document.addEventListener("DOMContentLoaded", function () {

    const botones = document.querySelectorAll(".boton");

    botones.forEach(function (boton) {

        boton.addEventListener("click", function () {

            boton.style.opacity = "0.7";

        });

    });

});