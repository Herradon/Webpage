
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Calendario | ViziuneAI</title>

    <link rel="stylesheet" href="css/calendario.css">

</head>

<body>

 <a href="index.php">Volver a inicio</a>

    <main class="calendario-contenedor">


        <header class="calendario-header">


            <button
                type="button"
                id="mesAnterior"
                class="boton-mes"
            >
                ‹
            </button>

            <h1 id="mesActual">
                Cargando...
            </h1>

            <button
                type="button"
                id="mesSiguiente"
                class="boton-mes"
            >
                ›
            </button>

        </header>


        <section class="calendario">

            <div class="dias-semana">

                <div>Lun</div>
                <div>Mar</div>
                <div>Mié</div>
                <div>Jue</div>
                <div>Vie</div>
                <div>Sáb</div>
                <div>Dom</div>

            </div>


            <div
                id="diasCalendario"
                class="dias-calendario"
            >
            </div>

        </section>


        <section
            id="detalleReunion"
            class="detalle-reunion"
            hidden
        >

            <button
                type="button"
                id="cerrarDetalle"
                class="cerrar-detalle"
            >
                ×
            </button>

            <h2>Reunión</h2>

            <div id="contenidoReunion"></div>

        </section>

    </main>


    <script src="js/calendario.js"></script>

</body>

</html>

