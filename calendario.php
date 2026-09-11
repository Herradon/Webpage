<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Calendario - ViziuneAI</title>

    <link rel="stylesheet" href="css/calendario.css">

</head>


<body>


    <main class="calendar-page">


        <!-- ==================================================
             CABECERA
        ================================================== -->

        <header class="calendar-header">

            <div>

                <span class="calendar-eyebrow">
                    VIZIUNEAI
                </span>

                <h1>
                    Calendario de reuniones
                </h1>

                <p>
                    Consulta y gestiona las reuniones programadas.
                </p>

                <a href="index.php">Volver</a>


            </div>


            <div class="calendar-actions">

                <button
                    type="button"
                    id="btnHoy"
                    class="calendar-button"
                >
                    Hoy
                </button>

                <button
                    type="button"
                    id="btnAnterior"
                    class="calendar-button calendar-button-icon"
                    aria-label="Mes anterior"
                >
                    ‹
                </button>

                <button
                    type="button"
                    id="btnSiguiente"
                    class="calendar-button calendar-button-icon"
                    aria-label="Mes siguiente"
                >
                    ›
                </button>

            </div>

        </header>


        <!-- ==================================================
             CALENDARIO
        ================================================== -->

        <section class="calendar-container">


            <div class="calendar-title">

                <h2 id="mesActual">
                    Cargando...
                </h2>

            </div>


            <!-- DÍAS DE LA SEMANA -->

            <div class="calendar-weekdays">

                <div>Lun</div>
                <div>Mar</div>
                <div>Mié</div>
                <div>Jue</div>
                <div>Vie</div>
                <div>Sáb</div>
                <div>Dom</div>

            </div>


            <!-- DÍAS -->

            <div
                id="calendarGrid"
                class="calendar-grid"
            >

                <!-- JavaScript generará los días -->

            </div>

        </section>


        <!-- ==================================================
             INFORMACIÓN DE LA REUNIÓN
        ================================================== -->

        <aside
            id="reunionPanel"
            class="meeting-panel"
        >

            <button
                type="button"
                id="cerrarPanel"
                class="meeting-close"
                aria-label="Cerrar"
            >
                ×
            </button>


            <div class="meeting-panel-content">

                <span class="meeting-label">
                    REUNIÓN
                </span>

                <h2 id="reunionNombre">
                    —
                </h2>


                <div class="meeting-info">

                    <div class="meeting-info-item">

                        <span>
                            📅
                        </span>

                        <div>

                            <small>
                                Fecha
                            </small>

                            <strong id="reunionFecha">
                                —
                            </strong>

                        </div>

                    </div>


                    <div class="meeting-info-item">

                        <span>
                            🕐
                        </span>

                        <div>

                            <small>
                                Hora
                            </small>

                            <strong id="reunionHora">
                                —
                            </strong>

                        </div>

                    </div>


                    <div class="meeting-info-item">

                        <span>
                            👤
                        </span>

                        <div>

                            <small>
                                Especialista
                            </small>

                            <strong id="reunionEspecialista">
                                —
                            </strong>

                        </div>

                    </div>


                    <div class="meeting-info-item">

                        <span>
                            ✉
                        </span>

                        <div>

                            <small>
                                Email
                            </small>

                            <strong id="reunionEmail">
                                —
                            </strong>

                        </div>

                    </div>


                    <div class="meeting-info-item">

                        <span>
                            ⏱
                        </span>

                        <div>

                            <small>
                                Duración
                            </small>

                            <strong id="reunionDuracion">
                                60 minutos
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </aside>


    </main>


    <!-- ======================================================
         JAVASCRIPT
    ====================================================== -->

    <script src="calendario.js"></script>


</body>

</html>