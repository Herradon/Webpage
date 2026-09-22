<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioLogueado = isset($_SESSION['usuario_id']);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/menu.css">

    <title>Document</title>

</head>


<body>

    <header class="header">

        <div class="container nav">

            <!-- LOGO -->

            <div class="logo">

                <div class="logo-v">
                    V
                </div>

                <div class="logo-text">
                    IZIUNE
                </div>

            </div>


            <!-- BOTÓN HAMBURGUESA -->

            <button
                class="menu-toggle"
                type="button"
                aria-label="Abrir menú"
                aria-expanded="false"
            >

                <span></span>
                <span></span>
                <span></span>

            </button>


            <!-- NAVEGACIÓN -->

            <nav>

                <a href="index.php">
                    Inicio
                </a>

                <a href="herramientas.php">
                   Analisis
                </a>

                <a href="facturas.php">
                    Facturación
                </a>
                
                
                <a href="calendario.php">
                    Calendario
                </a>



                <?php if ($usuarioLogueado): ?>

                    <a href="mi_cuenta.php">
                        Mi cuenta
                    </a>

                    <a href="logout.php">
                        Cerrar sesión
                    </a>

                <?php else: ?>

                    <a href="login.php">
                        Iniciar sesión
                    </a>

                <?php endif; ?>

            </nav>

        </div>

    </header>


    <style>

        /* =========================================
           HEADER
           ========================================= */

        .header {

            position: fixed;

            top: 0;
            left: 0;

            width: 100%;

            z-index: 1000;

            background: #0d1a29;

            border-bottom: 1px solid #1c3045;
        }


        .container.nav {

            width: 100%;

            min-height: 80px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 40px;

            box-sizing: border-box;
        }


        /* =========================================
           LOGO
           ========================================= */

        .logo {

            display: flex;

            align-items: center;

            gap: 10px;

            flex-shrink: 0;
        }


        .logo-v {

            width: 42px;
            height: 42px;

            display: flex;

            align-items: center;

            justify-content: center;

            border: 2px solid #00cfe0;

            border-radius: 10px;

            color: #00cfe0;

            font-size: 24px;

            font-weight: 800;

            box-shadow:
                0 0 10px rgba(0, 207, 224, 0.25);
        }


        .logo-text {

            color: #ffffff;

            font-size: 21px;

            font-weight: 700;

            letter-spacing: 1px;

            white-space: nowrap;
        }


        /* =========================================
           NAVEGACIÓN
           ========================================= */

        .container.nav nav {

            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 30px;

            margin-left: auto;
        }


        .container.nav nav a {

            color: #b7c5d3;

            text-decoration: none;

            font-size: 15px;

            font-weight: 500;

            padding: 8px 4px;

            transition:
                color 0.2s ease,
                text-shadow 0.2s ease;
        }


        /* HOVER */

        .container.nav nav a:hover {

            color: #00cfe0;

            text-shadow:
                0 0 8px rgba(0, 207, 224, 0.45);
        }


        /* =========================================
           APARTADO ACTIVO
           ========================================= */

        .container.nav nav a.activo {

            color: #00cfe0;

            text-shadow:
                0 0 8px rgba(0, 207, 224, 0.45);
        }


        /* =========================================
           BOTÓN HAMBURGUESA
           ========================================= */

        .menu-toggle {

            display: none;

            width: 44px;
            height: 44px;

            padding: 0;

            background: transparent;

            border: none;

            cursor: pointer;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            gap: 5px;

            z-index: 1002;
        }


        .menu-toggle span {

            display: block;

            width: 25px;
            height: 2px;

            background: #00cfe0;

            border-radius: 2px;

            transition:
                transform 0.25s ease,
                opacity 0.25s ease;
        }


        /* =========================================
           TABLET
           ========================================= */

        @media (max-width: 900px) {

            .container.nav {

                padding: 0 25px;
            }


            .container.nav nav {

                gap: 18px;
            }


            .container.nav nav a {

                font-size: 14px;
            }
        }


        /* =========================================
           MÓVIL
           ========================================= */

        @media (max-width: 700px) {

            .container.nav {

                min-height: 70px;

                padding: 0 20px;

                position: relative;
            }


            /* Mostrar hamburguesa */

            .menu-toggle {

                display: flex;
            }


            /* Ocultar navegación */

            .container.nav nav {

                display: none;

                position: absolute;

                top: 70px;

                left: 0;

                width: 100%;

                flex-direction: column;

                align-items: center;

                justify-content: center;

                gap: 0;

                margin: 0;

                padding: 15px 0 20px;

                background: #0d1a29;

                border-bottom: 1px solid #1c3045;

                box-sizing: border-box;
            }


            /* Menú abierto */

            .container.nav nav.menu-abierto {

                display: flex;
            }


            .container.nav nav a {

                width: 100%;

                padding: 14px 20px;

                text-align: center;

                box-sizing: border-box;
            }


            /* =====================================
               ANIMACIÓN HAMBURGUESA → X
               ===================================== */

            .menu-toggle.menu-activo span:nth-child(1) {

                transform:
                    translateY(7px)
                    rotate(45deg);
            }


            .menu-toggle.menu-activo span:nth-child(2) {

                opacity: 0;
            }


            .menu-toggle.menu-activo span:nth-child(3) {

                transform:
                    translateY(-7px)
                    rotate(-45deg);
            }


            /* LOGO MÓVIL */

            .logo-v {

                width: 38px;
                height: 38px;

                font-size: 21px;
            }


            .logo-text {

                font-size: 19px;
            }
        }


        /* =========================================
           MÓVILES PEQUEÑOS
           ========================================= */

        @media (max-width: 400px) {

            .container.nav {

                padding: 0 15px;
            }


            .logo {

                gap: 8px;
            }


            .logo-v {

                width: 35px;
                height: 35px;

                font-size: 19px;
            }


            .logo-text {

                font-size: 17px;
            }
        }

    </style>


    <!-- =========================================
         JAVASCRIPT
         ========================================= -->

    <script>

        document.addEventListener("DOMContentLoaded", function () {

            const menuToggle =
                document.querySelector(".menu-toggle");

            const nav =
                document.querySelector(".container.nav nav");


            if (!menuToggle || !nav) {
                return;
            }


            /* =====================================
               MENÚ HAMBURGUESA
               ===================================== */

            menuToggle.addEventListener("click", function () {

                const abierto =
                    nav.classList.toggle("menu-abierto");

                menuToggle.classList.toggle(
                    "menu-activo",
                    abierto
                );

                menuToggle.setAttribute(
                    "aria-expanded",
                    abierto ? "true" : "false"
                );

            });


            /* =====================================
               CERRAR MENÚ AL PULSAR UN ENLACE
               ===================================== */

            nav.querySelectorAll("a").forEach(function (link) {

                link.addEventListener("click", function () {

                    nav.classList.remove("menu-abierto");

                    menuToggle.classList.remove("menu-activo");

                    menuToggle.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                });

            });


            /* =====================================
               APARTADO ACTIVO AUTOMÁTICO
               ===================================== */

            const paginaActual =
                window.location.pathname
                    .split("/")
                    .pop();


            nav.querySelectorAll("a").forEach(function (link) {

                const enlace =
                    link.getAttribute("href");

                if (!enlace) {
                    return;
                }


                const paginaEnlace =
                    enlace.split("/").pop();


                link.classList.remove("activo");


                if (paginaActual === paginaEnlace) {

                    link.classList.add("activo");

                }

            });

        });

    </script>

</body>

</html>