/* =========================================================
   VIZIUNEAI - FACTURACIÓN
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       ELEMENTOS
    ===================================================== */

    const buscarFactura = document.getElementById("buscarFactura");
    const filtroEstado = document.getElementById("filtroEstado");
    const filasFactura = document.querySelectorAll(".fila-factura");


    /* =====================================================
       FILTRAR FACTURAS
    ===================================================== */

    function filtrarFacturas() {

        const textoBusqueda = buscarFactura
            ? buscarFactura.value.trim().toLowerCase()
            : "";

        const estadoSeleccionado = filtroEstado
            ? filtroEstado.value
            : "todos";


        filasFactura.forEach(function (fila) {

            const textoFila =
                fila.dataset.busqueda || "";

            const estadoFila =
                fila.dataset.estado || "";


            /* =============================================
               COMPROBAR BÚSQUEDA
            ============================================= */

            const coincideBusqueda =
                textoBusqueda === "" ||
                textoFila.includes(textoBusqueda);


            /* =============================================
               COMPROBAR ESTADO
            ============================================= */

            const coincideEstado =
                estadoSeleccionado === "todos" ||
                estadoFila === estadoSeleccionado;


            /* =============================================
               MOSTRAR / OCULTAR
            ============================================= */

            if (coincideBusqueda && coincideEstado) {

                fila.style.display = "";

            } else {

                fila.style.display = "none";

            }

        });


        actualizarContadorResultados();

    }


    /* =====================================================
       CONTADOR DE RESULTADOS
    ===================================================== */

    function actualizarContadorResultados() {

        const filasVisibles = Array.from(filasFactura)
            .filter(function (fila) {

                return fila.style.display !== "none";

            });


        const contador = document.querySelector(
            ".tabla-cabecera span"
        );


        if (contador) {

            contador.textContent =
                filasVisibles.length +
                (
                    filasVisibles.length === 1
                        ? " registro"
                        : " registros"
                );

        }

    }


    /* =====================================================
       EVENTO BUSCADOR
    ===================================================== */

    if (buscarFactura) {

        buscarFactura.addEventListener(
            "input",
            filtrarFacturas
        );

    }


    /* =====================================================
       EVENTO FILTRO ESTADO
    ===================================================== */

    if (filtroEstado) {

        filtroEstado.addEventListener(
            "change",
            filtrarFacturas
        );

    }


    /* =====================================================
       LIMPIAR FILTROS AL RECARGAR
    ===================================================== */

    if (buscarFactura) {

        buscarFactura.value = "";

    }

    if (filtroEstado) {

        filtroEstado.value = "todos";

    }


    /* =====================================================
       INICIALIZAR
    ===================================================== */

    filtrarFacturas();

});