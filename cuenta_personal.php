
<?php

session_start();

require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| DATOS DEL USUARIO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre,
        email,
        fecha_creacion,
        activo,
        suscripcion_activa,
        suscripcion_inicio,
        suscripcion_fin
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$usuarioId]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DATOS DEL CLIENTE / DATOS FISCALES
|--------------------------------------------------------------------------
*/

$stmtCliente = $pdo->prepare("
    SELECT
        id,
        tipo_persona,
        nombre_razon_social,
        nif,
        direccion,
        codigo_postal,
        ciudad,
        provincia,
        pais,
        email,
        telefono
    FROM clientes
    WHERE usuario_id = ?
      AND activo = 1
    LIMIT 1
");

$stmtCliente->execute([$usuarioId]);

$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| SUSCRIPCIÓN
|--------------------------------------------------------------------------
*/

$suscripcionActiva = (int) $usuario['suscripcion_activa'] === 1;

if ($suscripcionActiva && !empty($usuario['suscripcion_fin'])) {

    try {

        $fechaFin = new DateTime($usuario['suscripcion_fin']);
        $ahora = new DateTime();

        if ($fechaFin < $ahora) {

            $pdo->prepare("
                UPDATE usuarios
                SET suscripcion_activa = 0
                WHERE id = ?
            ")->execute([$usuarioId]);

            $suscripcionActiva = false;

        }

    } catch (Exception $e) {

        $suscripcionActiva = false;

    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Cuenta personal | ViziuneAI</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #07111f;
            color: #ffffff;
        }

       .header {

    width: 100%;

    background: #0d1a29;

    border-bottom: 1px solid #1c3045;

    position: f;

    top: 0;

    z-index: 100;

    }
    
    .nav {
    
        width: 100%;
    
        min-height: 75px;
    
        display: flex;
        justify-content: space-between;
    
        align-items: center;
    
        padding: 0 25px;
    
    }
    
    .logo {
    
        display: flex;
    
        align-items: center;
    
        font-size: 22px;
    
        font-weight: 800;
    
        white-space: nowrap;
    
    }
    
    .logo-v {
    
        color: #00cfe0;
    
        font-size: 25px;
    
    }
    
    .logo-text {
    
        font-size: 25px;
    
        color: #ffffff;
    
    }
    
    nav {
    
        flex: 1;
    
        text-align: center;
    
        margin-left: 30px;
    
    }
    
    nav a {
    
        color: #b7c5d3;
    
        text-decoration: none;
    
        font-weight: 600;
    
        margin: 0 20px;
    
    }
    
    nav a:hover {
    
        color: #00cfe0;
    
    }
        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 45px 20px 70px;
        }

        .titulo {
            margin-bottom: 30px;
        }

        .titulo h1 {
            margin: 0 0 8px;
            font-size: 32px;
        }

        .titulo p {
            margin: 0;
            color: #b7c5d3;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .card {
            background: #0d1a29;
            border: 1px solid #1c3045;
            border-radius: 14px;
            padding: 25px;
        }

        .card h2 {
            margin: 0 0 20px;
            font-size: 20px;
        }

        .dato {
            padding: 12px 0;
            border-bottom: 1px solid #1c3045;
        }

        .dato:last-child {
            border-bottom: none;
        }

        .dato-label {
            display: block;
            color: #7f91a4;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .dato-valor {
            color: #ffffff;
            font-size: 15px;
            word-break: break-word;
        }

        .estado {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .estado.activa {
            background: rgba(0, 207, 224, 0.12);
            color: #00cfe0;
        }

        .estado.inactiva {
            background: rgba(255, 80, 80, 0.12);
            color: #ff8080;
        }

        .acciones {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .boton {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #1c3045;
            color: #ffffff;
            background: #0d2633;
        }

        .boton:hover {
            border-color: #00cfe0;
            color: #00cfe0;
        }

        .boton-principal {
            background: #00cfe0;
            color: #07111f;
            border-color: #00cfe0;
        }

        .boton-principal:hover {
            color: #07111f;
            opacity: 0.9;
        }

        .suscripcion-info {
            margin-top: 15px;
            color: #b7c5d3;
            font-size: 14px;
            line-height: 1.6;
        }

        .sin-datos {
            color: #7f91a4;
            font-size: 14px;
            line-height: 1.6;
        }

        @media (max-width: 750px) {

            .header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            nav {
                gap: 15px;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            main {
                padding-top: 30px;
            }

        }

    </style>

</head>

<body>

<header class="header" style="position: fixed">

    <div class="container nav">

        <div class="logo">

            <div class="logo-v">
                V
            </div>

            <div class="logo-text">
                IZIUNE
            </div>

        </div>

        <?php include 'menu.php'; ?>
       
    </div>

</header>


<main>

    <div class="titulo">

        <h1>
            Mi cuenta
        </h1>

        <p>
            Gestiona tus datos personales, datos fiscales y tu suscripción.
        </p>

    </div>


    <div class="grid">


        <!-- DATOS DE LA CUENTA -->

        <section class="card">

            <h2>
                Datos de mi cuenta
            </h2>

            <div class="dato">

                <span class="dato-label">
                    Nombre
                </span>

                <span class="dato-valor">
                    <?= htmlspecialchars($usuario['nombre']) ?>
                </span>

            </div>


            <div class="dato">

                <span class="dato-label">
                    Correo electrónico
                </span>

                <span class="dato-valor">
                    <?= htmlspecialchars($usuario['email']) ?>
                </span>

            </div>


            <div class="dato">

                <span class="dato-label">
                    Fecha de registro
                </span>

                <span class="dato-valor">

                    <?php

                    if (!empty($usuario['fecha_creacion'])) {

                        try {

                            $fechaRegistro = new DateTime(
                                $usuario['fecha_creacion']
                            );

                            echo htmlspecialchars(
                                $fechaRegistro->format('d/m/Y')
                            );

                        } catch (Exception $e) {

                            echo htmlspecialchars(
                                $usuario['fecha_creacion']
                            );

                        }

                    } else {

                        echo 'No disponible';

                    }

                    ?>

                </span>

            </div>


            <div class="acciones">

                <a
                    href="facturas.php"
                    class="boton"
                >
                    Mis facturas
                </a>

                <a
                    href="logout.php"
                    class="boton"
                >
                    Cerrar sesión
                </a>

            </div>

        </section>


        <!-- SUSCRIPCIÓN -->

        <section class="card">

            <h2>
                Mi suscripción
            </h2>

            <?php if ($suscripcionActiva): ?>

                <span class="estado activa">
                    Suscripción activa
                </span>

                <div class="suscripcion-info">

                    <?php if (!empty($usuario['suscripcion_inicio'])): ?>

                        <div>

                            <strong>
                                Inicio:
                            </strong>

                            <?php

                            try {

                                $fechaInicio = new DateTime(
                                    $usuario['suscripcion_inicio']
                                );

                                echo htmlspecialchars(
                                    $fechaInicio->format('d/m/Y')
                                );

                            } catch (Exception $e) {

                                echo htmlspecialchars(
                                    $usuario['suscripcion_inicio']
                                );

                            }

                            ?>

                        </div>

                    <?php endif; ?>


                    <?php if (!empty($usuario['suscripcion_fin'])): ?>

                        <div>

                            <strong>
                                Finaliza:
                            </strong>

                            <?php

                            try {

                                $fechaFinMostrar = new DateTime(
                                    $usuario['suscripcion_fin']
                                );

                                echo htmlspecialchars(
                                    $fechaFinMostrar->format('d/m/Y')
                                );

                            } catch (Exception $e) {

                                echo htmlspecialchars(
                                    $usuario['suscripcion_fin']
                                );

                            }

                            ?>

                        </div>

                    <?php endif; ?>

                </div>


                <div class="acciones">

                    <a
                        href="index.php"
                        class="boton boton-principal"
                    >
                        Ir al asistente
                    </a>

                </div>


            <?php else: ?>

                <span class="estado inactiva">
                    Sin suscripción activa
                </span>

                <p class="suscripcion-info">

                    Actualmente no tienes una suscripción activa.
                    Activa una suscripción para poder utilizar el
                    asistente y las funciones de facturación.

                </p>


                <div class="acciones">

                    <a
                        href="suscripcion.php"
                        class="boton boton-principal"
                    >
                        Ver suscripción
                    </a>

                </div>

            <?php endif; ?>

        </section>


        <!-- DATOS FISCALES -->

        <section class="card">

            <h2>
                Mis datos fiscales
            </h2>

            <?php if ($cliente): ?>

                <div class="dato">

                    <span class="dato-label">
                        Nombre / Razón social
                    </span>

                    <span class="dato-valor">
                        <?= htmlspecialchars(
                            $cliente['nombre_razon_social']
                        ) ?>
                    </span>

                </div>


                <div class="dato">

                    <span class="dato-label">
                        NIF
                    </span>

                    <span class="dato-valor">
                        <?= htmlspecialchars(
                            $cliente['nif']
                        ) ?>
                    </span>

                </div>


                <div class="dato">

                    <span class="dato-label">
                        Dirección
                    </span>

                    <span class="dato-valor">

                        <?php

                        $direccion = [];

                        if (!empty($cliente['direccion'])) {
                            $direccion[] = $cliente['direccion'];
                        }

                        if (!empty($cliente['codigo_postal'])) {
                            $direccion[] = $cliente['codigo_postal'];
                        }

                        if (!empty($cliente['ciudad'])) {
                            $direccion[] = $cliente['ciudad'];
                        }

                        if (!empty($cliente['provincia'])) {
                            $direccion[] = $cliente['provincia'];
                        }

                        if (!empty($cliente['pais'])) {
                            $direccion[] = $cliente['pais'];
                        }

                        echo htmlspecialchars(
                            !empty($direccion)
                                ? implode(', ', $direccion)
                                : 'No disponible'
                        );

                        ?>

                    </span>

                </div>


                <div class="dato">

                    <span class="dato-label">
                        Teléfono
                    </span>

                    <span class="dato-valor">

                        <?= !empty($cliente['telefono'])
                            ? htmlspecialchars($cliente['telefono'])
                            : 'No disponible'
                        ?>

                    </span>

                </div>


                <div class="dato">

                    <span class="dato-label">
                        Email fiscal
                    </span>

                    <span class="dato-valor">

                        <?= !empty($cliente['email'])
                            ? htmlspecialchars($cliente['email'])
                            : 'No disponible'
                        ?>

                    </span>

                </div>


                <div class="acciones">

                    <a
                        href="facturas.php"
                        class="boton"
                    >
                        Ir a facturación
                    </a>

                </div>


            <?php else: ?>

                <p class="sin-datos">
                    No se han encontrado datos fiscales asociados
                    a tu cuenta.
                </p>

            <?php endif; ?>

        </section>


        <!-- ACCESOS -->

        <section class="card">

            <h2>
                Accesos rápidos
            </h2>

            <div class="acciones">

                <a
                    href="index.php"
                    class="boton"
                >
                    Asistente ViziuneAI
                </a>

                <a
                    href="calendario.php"
                    class="boton"
                >
                    Calendario
                </a>

                <a
                    href="facturas.php"
                    class="boton"
                >
                    Facturación
                </a>

            </div>

        </section>


    </div>

</main>

</body>

</html>