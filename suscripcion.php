    
<?php

session_start();

require_once 'config.php';


/* ==========================================================
   COMPROBAR SESIÓN
========================================================== */

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');
    exit;

}


$usuarioId =
    (int) $_SESSION['usuario_id'];


/* ==========================================================
   OBTENER DATOS DE LA SUSCRIPCIÓN
========================================================== */

$stmt =
    $pdo->prepare("
        SELECT
            nombre,
            email,
            suscripcion_activa,
            suscripcion_inicio,
            suscripcion_fin
        FROM usuarios
        WHERE id = ?
        LIMIT 1
    ");

$stmt->execute([
    $usuarioId
]);

$usuario =
    $stmt->fetch(PDO::FETCH_ASSOC);


if (!$usuario) {

    session_destroy();

    header('Location: login.php');
    exit;

}


/* ==========================================================
   COMPROBAR SI LA SUSCRIPCIÓN SIGUE ACTIVA
========================================================== */

$suscripcionActiva =
    (int) $usuario['suscripcion_activa'] === 1;


if (
    $suscripcionActiva &&
    !empty($usuario['suscripcion_fin'])
) {

    $fechaFin =
        new DateTime(
            $usuario['suscripcion_fin']
        );

    $ahora =
        new DateTime();


    if ($fechaFin < $ahora) {

        $suscripcionActiva = false;


        $stmtActualizar =
            $pdo->prepare("
                UPDATE usuarios
                SET suscripcion_activa = 0
                WHERE id = ?
            ");

        $stmtActualizar->execute([
            $usuarioId
        ]);

    }

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Suscripción | ViziuneAI</title>

    <link
        rel="stylesheet"
        href="css/style.css">
        

</head>

<body>


<header class="header">

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

<section class="hero">

    <div class="container hero-content">


        <?php if ($suscripcionActiva): ?>

            <h1>
                Tu suscripción está
                <span>activa</span>
            </h1>


            <p>

                Hola
                <?= htmlspecialchars($usuario['nombre']) ?>.

                Tu suscripción está activa y puedes
                utilizar los servicios de ViziuneAI.

            </p>


            <?php if (!empty($usuario['suscripcion_fin'])): ?>

                <p>

                    Tu suscripción está disponible
                    hasta el

                    <strong>
                        <?= htmlspecialchars(
                            date(
                                'd/m/Y',
                                strtotime(
                                    $usuario['suscripcion_fin']
                                )
                            )
                        ) ?>
                    </strong>

                </p>

            <?php endif; ?>


            <p>

                <a href="index.php">
                    Continuar a ViziuneAI
                </a>

            </p>


        <?php else: ?>


            <h1>

                Necesitas una
                <span>suscripción</span>

            </h1>


            <p>

                Hola
                <?= htmlspecialchars($usuario['nombre']) ?>.

                Para poder utilizar el chatbot y acceder
                a la sección de facturación necesitas
                tener una suscripción activa.

            </p>


            <div
                style="
                    margin-top: 30px;
                    padding: 30px;
                    background: #0d1a29;
                    border: 1px solid #1c3045;
                    border-radius: 15px;
                ">


                <h2>
                    Suscripción ViziuneAI
                </h2>


                <p>

                    Accede a nuestros servicios,
                    herramientas y funcionalidades
                    profesionales.

                </p>


                <p
                    style="
                        font-size: 32px;
                        font-weight: bold;
                        color: #00cfe0;
                    ">

                    Próximamente

                </p>


                <button
                    type="button"
                    disabled
                    style="
                        padding: 14px 25px;
                        border: none;
                        border-radius: 8px;
                        cursor: not-allowed;
                        opacity: 0.6;
                    ">

                    Suscribirme

                </button>


            </div>


            <p style="margin-top: 30px;">

                Cuando completes la suscripción,
                podrás continuar utilizando
                ViziuneAI.

            </p>


        <?php endif; ?>


    </div>

</section>

</main>


<footer>

    <div class="container">

        <p>

            © <?= date('Y') ?>
            ViziuneAI

        </p>

    </div>

</footer>


</body>

</html>
