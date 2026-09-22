<?php

session_start();

require_once 'config.php';


/* ==========================================
   COMPROBAR SESIÓN
========================================== */

$usuarioLogueado = isset($_SESSION['usuario_id']);

$usuarioId = $usuarioLogueado
    ? (int) $_SESSION['usuario_id']
    : 0;


/* ==========================================
   ACCESO GRATUITO
========================================== */

/*
|--------------------------------------------------------------------------
| Los usuarios con una cuenta activa
| pueden utilizar el calendario gratuitamente.
|--------------------------------------------------------------------------
|
| Los visitantes pueden visualizar el calendario,
| pero las acciones que requieran cuenta deberán
| comprobar la sesión.
|--------------------------------------------------------------------------
*/

if ($usuarioLogueado) {

    $_SESSION['suscripcion_activa'] = 1;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Calendario | ViziuneAI</title>

    <link rel="stylesheet" href="css/calendario.css">


    <style>

        /* ==========================================
           AVISO PRIVADO DE CALENDARIO
        ========================================== */

        .acceso-privado-aviso {

            max-width: 100%;

            margin-top: 80px;

            padding: 20px 25px;

            color: #ffffff;

            box-sizing: border-box;
            
            text-align: center;

        }


        .acceso-privado-aviso strong {

            display: block;

            margin-bottom: 8px;

            color: #00cfe0;

            font-size: 18px;

        }


        .acceso-privado-aviso p {

            margin: 0 0 15px;

            color: #b7c5d3;

            line-height: 1.6;

        }


        .boton-iniciar-sesion {

            display: inline-block;

            padding: 10px 18px;

            background: #00cfe0;

            color: #061018;

            text-decoration: none;

            border-radius: 8px;

            font-weight: 700;

        }


        .boton-iniciar-sesion:hover {

            opacity: 0.9;

        }


        /* ==========================================
           AVISO DE LOGIN
        ========================================== */

        .login-aviso {

            position: fixed;

            inset: 0;

            z-index: 9999;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;

            background: rgba(0, 0, 0, 0.72);

            backdrop-filter: blur(5px);

            box-sizing: border-box;

        }


        .login-aviso[hidden] {

            display: none;

        }


        .login-aviso-contenido {

            position: relative;

            width: 100%;

            max-width: 430px;

            padding: 35px 30px;

            background: #0d1a29;

            border: 1px solid #1c3045;

            border-radius: 16px;

            text-align: center;

            box-shadow:
                0 15px 50px rgba(0, 0, 0, 0.45);

            box-sizing: border-box;

        }


        .login-aviso-cerrar {

            position: absolute;

            top: 12px;

            right: 15px;

            width: 35px;

            height: 35px;

            border: none;

            background: transparent;

            color: #b7c5d3;

            font-size: 28px;

            cursor: pointer;

        }


        .login-aviso-cerrar:hover {

            color: #00cfe0;

        }


        .login-aviso-icono {

            font-size: 42px;

            margin-bottom: 15px;

        }


        .login-aviso-contenido h2 {

            margin: 0 0 12px;

            color: #ffffff;

        }


        .login-aviso-contenido p {

            margin: 0 0 25px;

            color: #b7c5d3;

            line-height: 1.6;

        }


        .login-aviso-botones {

            display: flex;

            justify-content: center;

            gap: 12px;

            flex-wrap: wrap;

        }


        .login-aviso-login,
        .login-aviso-continuar {

            border: none;

            border-radius: 8px;

            padding: 12px 20px;

            cursor: pointer;

            font-size: 14px;

        }


        .login-aviso-login {

            background: #00cfe0;

            color: #061018;

            font-weight: 700;

        }


        .login-aviso-login:hover {

            opacity: 0.9;

        }


        .login-aviso-continuar {

            background: #1c3045;

            color: #ffffff;

        }


        .login-aviso-continuar:hover {

            background: #29445d;

        }

    </style>

</head>

<body>


<header class="header">

    <?php include 'menu.php'; ?>

</header>


<?php if (!$usuarioLogueado): ?>

    <!-- ==========================================
         AVISO CALENDARIO PRIVADO
    ========================================== -->

    <div class="acceso-privado-aviso">

        <strong>
            🔐 Calendario privado
        </strong>

        <p>
            Puedes consultar esta sección sin iniciar sesión.
            Para crear, consultar o gestionar tus citas y eventos
            necesitas acceder a tu cuenta.
        </p>

        <a
            href="login.php"
            class="boton-iniciar-sesion"
        >
            Iniciar sesión
        </a>

    </div>

<?php endif; ?>


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


        <h2>

            Reunión

        </h2>


        <div id="contenidoReunion"></div>

    </section>


</main>


<!-- ==========================================
     AVISO DE INICIO DE SESIÓN
========================================== -->

<div
    id="loginAviso"
    class="login-aviso"
    hidden
>

    <div class="login-aviso-contenido">


        <button
            type="button"
            id="cerrarLoginAviso"
            class="login-aviso-cerrar"
            aria-label="Cerrar"
        >

            ×

        </button>


        <div class="login-aviso-icono">

            🔐

        </div>


        <h2>

            Calendario privado

        </h2>


        <p>

            Puedes consultar esta sección sin iniciar sesión.
            Para crear, consultar o gestionar tus citas y eventos
            necesitas acceder a tu cuenta.

        </p>


        <div class="login-aviso-botones">


            <button
                type="button"
                id="irLogin"
                class="login-aviso-login"
            >

                Iniciar sesión

            </button>


            <button
                type="button"
                id="seguirSinLogin"
                class="login-aviso-continuar"
            >

                Continuar

            </button>


        </div>

    </div>

</div>


<script>

    window.usuarioLogueado =
        <?php echo $usuarioLogueado ? 'true' : 'false'; ?>;


    window.usuarioId =
        <?php echo $usuarioId; ?>;


    /* ==========================================
       AVISO LOGIN
    ========================================== */

    window.mostrarAvisoLogin = function () {

        const aviso =
            document.getElementById(
                "loginAviso"
            );


        if (aviso) {

            aviso.hidden = false;

        }

    };


    document.addEventListener(
        "DOMContentLoaded",
        function () {


            const aviso =
                document.getElementById(
                    "loginAviso"
                );


            const cerrar =
                document.getElementById(
                    "cerrarLoginAviso"
                );


            const continuar =
                document.getElementById(
                    "seguirSinLogin"
                );


            const login =
                document.getElementById(
                    "irLogin"
                );


            if (cerrar) {

                cerrar.addEventListener(
                    "click",
                    function () {

                        aviso.hidden = true;

                    }
                );

            }


            if (continuar) {

                continuar.addEventListener(
                    "click",
                    function () {

                        aviso.hidden = true;

                    }
                );

            }


            if (login) {

                login.addEventListener(
                    "click",
                    function () {

                        window.location.href =
                            "login.php";

                    }
                );

            }

        }
    );

</script>


<script src="js/calendario.js"></script>


</body>

</html>