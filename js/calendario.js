document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* ==================================================
           ELEMENTOS
        ================================================== */

        const mesActual =
            document.getElementById(
                "mesActual"
            );


        const diasCalendario =
            document.getElementById(
                "diasCalendario"
            );


        const mesAnterior =
            document.getElementById(
                "mesAnterior"
            );


        const mesSiguiente =
            document.getElementById(
                "mesSiguiente"
            );


        const detalleReunion =
            document.getElementById(
                "detalleReunion"
            );


        const contenidoReunion =
            document.getElementById(
                "contenidoReunion"
            );


        const cerrarDetalle =
            document.getElementById(
                "cerrarDetalle"
            );


        /* ==================================================
           FECHA ACTUAL
        ================================================== */

        let fechaActual =
            new Date();


        /* ==================================================
           REUNIONES
        ================================================== */

        let reuniones = [];


        /* ==================================================
           MESES
        ================================================== */

        const nombresMeses = [

            "enero",
            "febrero",
            "marzo",
            "abril",
            "mayo",
            "junio",
            "julio",
            "agosto",
            "septiembre",
            "octubre",
            "noviembre",
            "diciembre"

        ];


        /* ==================================================
           CARGAR REUNIONES
        ================================================== */

        async function cargarReuniones() {

            try {

                const respuesta =
                    await fetch(
                        "calendario-datos.php"
                    );


                const datos =
                    await respuesta.json();


                if (
                    !datos.success
                ) {

                    console.error(
                        datos.error
                    );

                    return;

                }


                reuniones =
                    datos.reuniones || [];


                generarCalendario();


            } catch (error) {

                console.error(
                    "Error cargando reuniones:",
                    error
                );

            }

        }


        /* ==================================================
           GENERAR CALENDARIO
        ================================================== */

        function generarCalendario() {


            diasCalendario.innerHTML =
                "";


            const año =
                fechaActual.getFullYear();


            const mes =
                fechaActual.getMonth();


            /* ==============================================
               TÍTULO
            ============================================== */

            mesActual.textContent =
                nombresMeses[mes] +
                " " +
                año;


            /* ==============================================
               PRIMER DÍA
            ============================================== */

            const primerDia =
                new Date(
                    año,
                    mes,
                    1
                );


            let diaSemana =
                primerDia.getDay();


            /*
             * JavaScript:
             *
             * Domingo = 0
             * Lunes = 1
             *
             * Nuestro calendario empieza en lunes.
             */

            if (
                diaSemana === 0
            ) {

                diaSemana = 7;

            }


            /* ==============================================
               DÍAS DEL MES
            ============================================== */

            const ultimoDia =
                new Date(
                    año,
                    mes + 1,
                    0
                );


            const numeroDias =
                ultimoDia.getDate();


            /* ==============================================
               DÍAS DEL MES ANTERIOR
            ============================================== */

            const ultimoDiaMesAnterior =
                new Date(
                    año,
                    mes,
                    0
                ).getDate();


            for (
                let i = diaSemana - 1;
                i > 0;
                i--
            ) {

                const numero =
                    ultimoDiaMesAnterior -
                    i +
                    1;


                crearDia(
                    numero,
                    true,
                    año,
                    mes - 1
                );

            }


            /* ==============================================
               DÍAS ACTUALES
            ============================================== */

            for (
                let dia = 1;
                dia <= numeroDias;
                dia++
            ) {

                crearDia(
                    dia,
                    false,
                    año,
                    mes
                );

            }


            /* ==============================================
               DÍAS SIGUIENTES
            ============================================== */

            const totalCeldas =
                diasCalendario.children.length;


            const diasRestantes =
                42 -
                totalCeldas;


            for (
                let dia = 1;
                dia <= diasRestantes;
                dia++
            ) {

                crearDia(
                    dia,
                    true,
                    año,
                    mes + 1
                );

            }

        }


        /* ==================================================
           CREAR DÍA
        ================================================== */

        function crearDia(
            numero,
            otroMes,
            año,
            mes
        ) {


            const div =
                document.createElement(
                    "div"
                );


            div.className =
                "dia";


            if (
                otroMes
            ) {

                div.classList.add(
                    "otro-mes"
                );

            }


            /* ==============================================
               NÚMERO
            ============================================== */

            const numeroDia =
                document.createElement(
                    "span"
                );


            numeroDia.className =
                "numero-dia";


            numeroDia.textContent =
                numero;


            div.appendChild(
                numeroDia
            );


            /* ==============================================
               FECHA
            ============================================== */

            const fecha =
                new Date(
                    año,
                    mes,
                    numero
                );


            const añoFecha =
                fecha.getFullYear();


            const mesFecha =
                String(
                    fecha.getMonth() + 1
                ).padStart(
                    2,
                    "0"
                );


            const diaFecha =
                String(
                    fecha.getDate()
                ).padStart(
                    2,
                    "0"
                );


            const fechaTexto =
                añoFecha +
                "-" +
                mesFecha +
                "-" +
                diaFecha;


            /* ==============================================
               HOY
            ============================================== */

            const hoy =
                new Date();


            const hoyTexto =
                hoy.getFullYear() +
                "-" +
                String(
                    hoy.getMonth() + 1
                ).padStart(
                    2,
                    "0"
                ) +
                "-" +
                String(
                    hoy.getDate()
                ).padStart(
                    2,
                    "0"
                );


            if (
                fechaTexto ===
                hoyTexto
            ) {

                div.classList.add(
                    "hoy"
                );

            }


            /* ==============================================
               REUNIONES DEL DÍA
            ============================================== */

            const reunionesDia =
                reuniones.filter(
                    function (reunion) {

                        return (
                            reunion.fecha ===
                            fechaTexto
                        );

                    }
                );


            reunionesDia.forEach(
                function (reunion) {

                    crearReunion(
                        div,
                        reunion
                    );

                }
            );


            /* ==============================================
               CLICK
            ============================================== */

            div.addEventListener(
                "click",
                function () {

                    if (
                        reunionesDia.length >
                        0
                    ) {

                        mostrarDetalle(
                            reunionesDia
                        );

                    }

                }
            );


            diasCalendario.appendChild(
                div
            );

        }


        /* ==================================================
           CREAR REUNIÓN
        ================================================== */

        function crearReunion(
            contenedor,
            reunion
        ) {


            const elemento =
                document.createElement(
                    "div"
                );


            elemento.className =
                "reunion";


            /* ==============================================
               HORA
            ============================================== */

            const hora =
                document.createElement(
                    "div"
                );


            hora.className =
                "reunion-hora";


            hora.textContent =
                reunion.hora;


            /* ==============================================
               NOMBRE
            ============================================== */

            const nombre =
                document.createElement(
                    "div"
                );


            nombre.className =
                "reunion-nombre";


            nombre.textContent =
                reunion.nombre;


            elemento.appendChild(
                hora
            );


            elemento.appendChild(
                nombre
            );


            contenedor.appendChild(
                elemento
            );

        }


        /* ==================================================
           MOSTRAR DETALLE
        ================================================== */

        function mostrarDetalle(
            reunionesDia
        ) {


            contenidoReunion.innerHTML =
                "";


            reunionesDia.forEach(
                function (reunion) {


                    const bloque =
                        document.createElement(
                            "div"
                        );


                    bloque.innerHTML =

                        "<p><strong>Nombre:</strong> " +
                        escaparHTML(
                            reunion.nombre
                        ) +
                        "</p>" +

                        "<p><strong>Email:</strong> " +
                        escaparHTML(
                            reunion.email
                        ) +
                        "</p>" +

                        "<p><strong>Especialista:</strong> " +
                        escaparHTML(
                            reunion.especialista
                        ) +
                        "</p>" +

                        "<p><strong>Fecha:</strong> " +
                        formatearFecha(
                            reunion.fecha
                        ) +
                        "</p>" +

                        "<p><strong>Hora:</strong> " +
                        escaparHTML(
                            reunion.hora
                        ) +
                        "</p>" +

                        "<p><strong>Duración:</strong> " +
                        escaparHTML(
                            String(
                                reunion.duracion
                            )
                        ) +
                        " minutos</p>";


                    contenidoReunion.appendChild(
                        bloque
                    );

                }
            );


            detalleReunion.hidden =
                false;

        }


        /* ==================================================
           CERRAR DETALLE
        ================================================== */

        cerrarDetalle.addEventListener(
            "click",
            function () {

                detalleReunion.hidden =
                    true;

            }
        );


        /* ==================================================
           MES ANTERIOR
        ================================================== */

        mesAnterior.addEventListener(
            "click",
            function () {

                fechaActual.setMonth(
                    fechaActual.getMonth() - 1
                );


                generarCalendario();

            }
        );


        /* ==================================================
           MES SIGUIENTE
        ================================================== */

        mesSiguiente.addEventListener(
            "click",
            function () {

                fechaActual.setMonth(
                    fechaActual.getMonth() + 1
                );


                generarCalendario();

            }
        );


        /* ==================================================
           FORMATEAR FECHA
        ================================================== */

        function formatearFecha(
            fecha
        ) {

            const partes =
                fecha.split("-");


            if (
                partes.length !== 3
            ) {

                return fecha;

            }


            return (
                partes[2] +
                "/" +
                partes[1] +
                "/" +
                partes[0]
            );

        }


        /* ==================================================
           ESCAPAR HTML
        ================================================== */

        function escaparHTML(
            texto
        ) {

            const elemento =
                document.createElement(
                    "div"
                );


            elemento.textContent =
                texto ?? "";


            return elemento.innerHTML;

        }


        /* ==================================================
           INICIAR
        ================================================== */

        cargarReuniones();

    }
);