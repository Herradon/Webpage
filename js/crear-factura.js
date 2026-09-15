
/* ==========================================
   VIZIUNEAI - CREAR FACTURA
========================================== */

document.addEventListener("DOMContentLoaded", function () {

    const formularioFactura =
        document.getElementById("formularioFactura");

    const lineasFactura =
        document.getElementById("lineasFactura");

    const anadirLinea =
        document.getElementById("anadirLinea");

    const tipoIrpf =
        document.getElementById("tipo_irpf");

    const totalBase =
        document.getElementById("totalBase");

    const totalIva =
        document.getElementById("totalIva");

    const totalIrpf =
        document.getElementById("totalIrpf");

    const totalFactura =
        document.getElementById("totalFactura");

    const inputBase =
        document.getElementById("inputBase");

    const inputIva =
        document.getElementById("inputIva");

    const inputIrpf =
        document.getElementById("inputIrpf");

    const inputTotal =
        document.getElementById("inputTotal");


    /* ==========================================
       FORMATO MONEDA
    ========================================== */

    function formatoMoneda(valor) {

        return valor.toLocaleString("es-ES", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + " €";

    }


    /* ==========================================
       CALCULAR TOTALES
    ========================================== */

    function calcularTotales() {

        const lineas =
            document.querySelectorAll(".linea-factura");

        let baseTotal = 0;

        let ivaTotal = 0;


        lineas.forEach(function (linea) {

            const cantidadElemento =
                linea.querySelector(".cantidad");

            const precioElemento =
                linea.querySelector(".precio-unitario");

            const descuentoElemento =
                linea.querySelector(".descuento");

            const ivaElemento =
                linea.querySelector(".tipo-iva");

            const totalElemento =
                linea.querySelector(".linea-total");


            const cantidad =
                parseFloat(cantidadElemento.value) || 0;

            const precio =
                parseFloat(precioElemento.value) || 0;

            const descuento =
                parseFloat(descuentoElemento.value) || 0;

            const iva =
                parseFloat(ivaElemento.value) || 0;


            /* ------------------------------------------
               IMPORTE BRUTO
            ------------------------------------------ */

            const bruto =
                cantidad * precio;


            /* ------------------------------------------
               DESCUENTO
            ------------------------------------------ */

            const importeDescuento =
                bruto * descuento / 100;


            /* ------------------------------------------
               BASE DE LA LINEA
            ------------------------------------------ */

            const baseLinea =
                bruto - importeDescuento;


            /* ------------------------------------------
               IVA DE LA LINEA
            ------------------------------------------ */

            const ivaLinea =
                baseLinea * iva / 100;


            /* ------------------------------------------
               TOTAL DE LA LINEA
            ------------------------------------------ */

            const totalLinea =
                baseLinea + ivaLinea;


            if (totalElemento) {

                totalElemento.textContent =
                    formatoMoneda(totalLinea);

            }


            baseTotal += baseLinea;

            ivaTotal += ivaLinea;

        });


        /* ==========================================
           IRPF
        ========================================== */

        const porcentajeIrpf =
            parseFloat(tipoIrpf.value) || 0;


        const importeIrpf =
            baseTotal * porcentajeIrpf / 100;


        /* ==========================================
           TOTAL FINAL
        ========================================== */

        const total =
            baseTotal +
            ivaTotal -
            importeIrpf;


        /* ==========================================
           MOSTRAR TOTALES
        ========================================== */

        totalBase.textContent =
            formatoMoneda(baseTotal);

        totalIva.textContent =
            formatoMoneda(ivaTotal);


        if (importeIrpf > 0) {

            totalIrpf.textContent =
                "-" + formatoMoneda(importeIrpf);

        } else {

            totalIrpf.textContent =
                formatoMoneda(0);

        }


        totalFactura.textContent =
            formatoMoneda(total);


        /* ==========================================
           CAMPOS OCULTOS
        ========================================== */

        inputBase.value =
            baseTotal.toFixed(2);

        inputIva.value =
            ivaTotal.toFixed(2);

        inputIrpf.value =
            importeIrpf.toFixed(2);

        inputTotal.value =
            total.toFixed(2);

    }


    /* ==========================================
       CREAR NUEVA LINEA
    ========================================== */

    function crearLinea() {

        const linea =
            document.createElement("div");

        linea.className =
            "linea-factura";


        linea.innerHTML = `

            <input
                type="text"
                name="descripcion[]"
                placeholder="Descripción del producto o servicio"
                required
            >

            <input
                type="number"
                name="cantidad[]"
                class="cantidad"
                value="1"
                min="0.001"
                step="0.001"
                required
            >

            <input
                type="number"
                name="precio_unitario[]"
                class="precio-unitario"
                value="0"
                min="0"
                step="0.01"
                required
            >

            <input
                type="number"
                name="descuento[]"
                class="descuento"
                value="0"
                min="0"
                max="100"
                step="0.01"
            >

            <select
                name="tipo_iva[]"
                class="tipo-iva"
            >

                <option value="21">
                    21%
                </option>

                <option value="10">
                    10%
                </option>

                <option value="4">
                    4%
                </option>

                <option value="0">
                    0%
                </option>

            </select>

            <div class="linea-total">
                0,00 €
            </div>

            <button
                type="button"
                class="boton-eliminar-linea"
                title="Eliminar línea"
            >
                ×
            </button>

        `;


        lineasFactura.appendChild(linea);

        conectarLinea(linea);

        calcularTotales();

    }


    /* ==========================================
       CONECTAR EVENTOS DE UNA LINEA
    ========================================== */

    function conectarLinea(linea) {

        const campos =
            linea.querySelectorAll(
                "input, select"
            );


        campos.forEach(function (campo) {

            campo.addEventListener(
                "input",
                calcularTotales
            );

            campo.addEventListener(
                "change",
                calcularTotales
            );

        });


        const botonEliminar =
            linea.querySelector(
                ".boton-eliminar-linea"
            );


        if (botonEliminar) {

            botonEliminar.addEventListener(
                "click",
                function () {

                    const todasLasLineas =
                        document.querySelectorAll(
                            ".linea-factura"
                        );


                    if (todasLasLineas.length <= 1) {

                        alert(
                            "Debe existir al menos una línea en la factura."
                        );

                        return;

                    }


                    linea.remove();

                    calcularTotales();

                }
            );

        }

    }


    /* ==========================================
       AÑADIR LINEA
    ========================================== */

    if (anadirLinea) {

        anadirLinea.addEventListener(
            "click",
            crearLinea
        );

    }


    /* ==========================================
       IRPF
    ========================================== */

    if (tipoIrpf) {

        tipoIrpf.addEventListener(
            "change",
            calcularTotales
        );

    }


    /* ==========================================
       CONECTAR PRIMERA LINEA
    ========================================== */

    const primeraLinea =
        document.querySelector(
            ".linea-factura"
        );


    if (primeraLinea) {

        conectarLinea(
            primeraLinea
        );

    }


    /* ==========================================
       VALIDAR FECHAS
    ========================================== */

    const fechaEmision =
        document.getElementById(
            "fecha_emision"
        );

    const fechaVencimiento =
        document.getElementById(
            "fecha_vencimiento"
        );


    if (
        fechaEmision &&
        fechaVencimiento
    ) {

        fechaEmision.addEventListener(
            "change",
            function () {

                fechaVencimiento.min =
                    fechaEmision.value;

            }
        );

    }


    /* ==========================================
       VALIDACIÓN DEL FORMULARIO
    ========================================== */

    if (formularioFactura) {

        formularioFactura.addEventListener(
            "submit",
            function (evento) {

                /*
                |--------------------------------------------------------------------------
                | Ya NO comprobamos cliente_id.
                |
                | El cliente se introduce manualmente mediante:
                |
                | cliente_nombre_razon_social
                | cliente_nif
                | cliente_email
                | cliente_telefono
                | cliente_direccion
                | etc.
                |--------------------------------------------------------------------------
                */

                calcularTotales();


                /* ==========================================
                   COMPROBAR CLIENTE MANUAL
                ========================================== */

                const clienteNombre =
                    document.getElementById(
                        "cliente_nombre_razon_social"
                    );


                if (
                    !clienteNombre ||
                    clienteNombre.value.trim() === ""
                ) {

                    evento.preventDefault();

                    alert(
                        "Introduce el nombre o razón social del cliente."
                    );

                    if (clienteNombre) {

                        clienteNombre.focus();

                    }

                    return;

                }


                /* ==========================================
                   COMPROBAR LINEAS
                ========================================== */

                const lineas =
                    document.querySelectorAll(
                        ".linea-factura"
                    );


                if (lineas.length === 0) {

                    evento.preventDefault();

                    alert(
                        "La factura debe contener al menos una línea."
                    );

                    return;

                }


                let lineaValida = true;


                lineas.forEach(function (linea) {

                    const descripcion =
                        linea.querySelector(
                            'input[name="descripcion[]"]'
                        );

                    const cantidad =
                        parseFloat(
                            linea.querySelector(
                                ".cantidad"
                            ).value
                        ) || 0;

                    const precio =
                        parseFloat(
                            linea.querySelector(
                                ".precio-unitario"
                            ).value
                        );


                    if (
                        !descripcion ||
                        descripcion.value.trim() === ""
                    ) {

                        lineaValida = false;

                    }


                    if (
                        cantidad <= 0
                    ) {

                        lineaValida = false;

                    }


                    if (
                        isNaN(precio) ||
                        precio < 0
                    ) {

                        lineaValida = false;

                    }

                });


                if (!lineaValida) {

                    evento.preventDefault();

                    alert(
                        "Revisa las líneas de la factura. La descripción es obligatoria y la cantidad debe ser mayor que cero."
                    );

                    return;

                }

            }
        );

    }


    /* ==========================================
       CALCULO INICIAL
    ========================================== */

    calcularTotales();

});