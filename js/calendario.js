/* ==========================================================
   VARIABLES
========================================================== */

let fechaActual = new Date();

let reuniones = [];

let fechaSeleccionada = null;


/* ==========================================================
   ELEMENTOS
========================================================== */

const calendario =
    document.getElementById(
        "calendario"
    );


const mesActual =
    document.getElementById(
        "mesActual"
    );


const listaReuniones =
    document.getElementById(
        "listaReuniones"
    );


const botonAnterior =
    document.getElementById(
        "mesAnterior"
    );


const botonSiguiente =
    document.getElementById(
        "mesSiguiente"
    );


/* ==========================================================
   NOMBRES MESES
========================================================== */

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


/* ==========================================================
   CARGAR REUNIONES
========================================================== */

async function cargarReuniones() {

    try {

        const respuesta =
            await fetch(
                "calendario.php",
                {
                    method: "GET"
                }
            );


        if (!respuesta.ok) {

            throw new Error(
                "No se pudieron cargar las reuniones."
            );

        }


        const datos =
            await respuesta.json();


        if (
            !datos.success
        ) {

            throw new Error(
                datos.error ||
                "Error cargando el calendario."
            );

        }


        reuniones =
            datos.reuniones || [];


        generarCalendario();


    } catch (error) {

        console.error(
            error
        );


        listaReuniones.innerHTML =

            `<div class="error-calendario">
                ${escapeHtml(error.message)}
            </div>`;

    }

}


/* ==========================================================
   GENERAR CALENDARIO
========================================================== */

function generarCalendario() {

    const año =
        fechaActual.getFullYear();


    const mes =
        fechaActual.getMonth();


    mesActual.textContent =
        `${nombresMeses[mes]} ${año}`;


    calendario.innerHTML =
        "";


    const primerDia =
        new Date(
            año,
            mes,
            1
        );


    const ultimoDia =
        new Date(
            año,
            mes + 1,
            0
        );


    /*
     * JavaScript:
     * domingo = 0
     *
     * Nosotros queremos:
     * lunes = 0
     */

    let primerDiaSemana =
        primerDia.getDay();


    if (
        primerDiaSemana === 0
    ) {

        primerDiaSemana = 6;

    } else {

        primerDiaSemana--;

    }


    /* ======================================================
       ESPACIOS ANTERIORES
    ====================================================== */

    for (
        let i = 0;
        i < primerDiaSemana;
        i++
    ) {

        const celda =
            document.createElement(
                "div"
            );


        celda.className =
            "dia vacio";


        calendario.appendChild(
            celda
        );

    }


    /* ======================================================
       DÍAS
    ====================================================== */

    for (
        let dia = 1;
        dia <= ultimoDia.getDate();
        dia++
    ) {

        const celda =
            document.createElement(
                "div"
            );


        celda.className =
            "dia";


        const fecha =
            crearFechaLocal(
                año,
                mes,
                dia
            );


        const fechaTexto =
            formatearFecha(
                fecha
            );


        if (
            esHoy(fecha)
        ) {

            celda.classList.add(
                "hoy"
            );

        }


        if (
            fechaSeleccionada ===
            fechaTexto
        ) {

            celda.classList.add(
                "seleccionado"
            );

        }


        celda.innerHTML =

            `<div class="numero-dia">
                ${dia}
            </div>`;


        const reunionesDia =
            reuniones.filter(
                reunion =>
                    reunion.fecha ===
                    fechaTexto
            );


        if (
            reunionesDia.length > 0
        ) {

            for (
                let i = 0;
                i < Math.min(
                    reunionesDia.length,
                    3
                );
                i++
            ) {

                const indicador =
                    document.createElement(
                        "span"
                    );


                indicador.className =
                    "reunion-indicador";


                celda.appendChild(
                    indicador
                );

            }

        }


        celda.addEventListener(
            "click",
            () => {

                seleccionarDia(
                    fechaTexto
                );

            }
        );


        calendario.appendChild(
            celda
        );

    }

}


/* ==========================================================
   SELECCIONAR DÍA
========================================================== */

function seleccionarDia(
    fecha
) {

    fechaSeleccionada =
        fecha;


    generarCalendario();


    mostrarReuniones(
        fecha
    );

}


/* ==========================================================
   MOSTRAR REUNIONES
========================================================== */

function mostrarReuniones(
    fecha
) {

    const reunionesDia =
        reuniones.filter(
            reunion =>
                reunion.fecha ===
                fecha
        );


    if (
        reunionesDia.length === 0
    ) {

        listaReuniones.innerHTML =

            `<div class="sin-reuniones">

                <p>
                    No hay reuniones programadas
                    para el ${formatearFechaBonita(fecha)}.
                </p>

            </div>`;

        return;

    }


    let html = "";


    html +=

        `<div class="reuniones-titulo">

            <h2>
                Reuniones del
                ${formatearFechaBonita(fecha)}
            </h2>

        </div>`;


    reunionesDia.forEach(
        reunion => {

            html +=

                `<article class="reunion-card">

                    <h3>
                        ${escapeHtml(
                            reunion.nombre ||
                            "Cliente"
                        )}
                    </h3>

                    <div class="reunion-dato">
                        <strong>Hora:</strong>
                        ${escapeHtml(
                            reunion.hora ||
                            ""
                        )}
                    </div>

                    <div class="reunion-dato">
                        <strong>Email:</strong>
                        ${escapeHtml(
                            reunion.email ||
                            ""
                        )}
                    </div>

                    <div class="reunion-dato">
                        <strong>Especialista:</strong>
                        ${escapeHtml(
                            reunion.especialista ||
                            ""
                        )}
                    </div>

                    <div class="reunion-dato">
                        <strong>Duración:</strong>
                        ${escapeHtml(
                            String(
                                reunion.duracion ||
                                60
                            )
                        )}
                        minutos
                    </div>

                </article>`;

        }
    );


    listaReuniones.innerHTML =
        html;

}


/* ==========================================================
   CAMBIAR MES - ANTERIOR
========================================================== */

botonAnterior.addEventListener(
    "click",
    () => {

        fechaActual.setMonth(
            fechaActual.getMonth() - 1
        );


        generarCalendario();

    }
);


/* ==========================================================
   CAMBIAR MES - SIGUIENTE
========================================================== */

botonSiguiente.addEventListener(
    "click",
    () => {

        fechaActual.setMonth(
            fechaActual.getMonth() + 1
        );


        generarCalendario();

    }
);


/* ==========================================================
   CREAR FECHA LOCAL
========================================================== */

function crearFechaLocal(
    año,
    mes,
    dia
) {

    return new Date(
        año,
        mes,
        dia
    );

}


/* ==========================================================
   FORMATEAR FECHA
========================================================== */

function formatearFecha(
    fecha
) {

    const año =
        fecha.getFullYear();


    const mes =
        String(
            fecha.getMonth() + 1
        ).padStart(
            2,
            "0"
        );


    const dia =
        String(
            fecha.getDate()
        ).padStart(
            2,
            "0"
        );


    return `${año}-${mes}-${dia}`;

}


/* ==========================================================
   FORMATEAR FECHA BONITA
========================================================== */

function formatearFechaBonita(
    fechaTexto
) {

    const partes =
        fechaTexto.split("-");


    if (
        partes.length !== 3
    ) {

        return fechaTexto;

    }


    const año =
        Number(
            partes[0]
        );


    const mes =
        Number(
            partes[1]
        ) - 1;


    const dia =
        Number(
            partes[2]
        );


    const fecha =
        new Date(
            año,
            mes,
            dia
        );


    return fecha.toLocaleDateString(
        "es-ES",
        {
            day: "numeric",
            month: "long",
            year: "numeric"
        }
    );

}


/* ==========================================================
   COMPROBAR SI ES HOY
========================================================== */

function esHoy(
    fecha
) {

    const hoy =
        new Date();


    return (

        fecha.getFullYear() ===
        hoy.getFullYear()

        &&

        fecha.getMonth() ===
        hoy.getMonth()

        &&

        fecha.getDate() ===
        hoy.getDate()

    );

}


/* ==========================================================
   ESCAPAR HTML
========================================================== */

function escapeHtml(
    texto
) {

    const div =
        document.createElement(
            "div"
        );


    div.textContent =
        texto;


    return div.innerHTML;

}


/* ==========================================================
   INICIAR
========================================================== */

cargarReuniones();