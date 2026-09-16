
<?php

session_start();

require_once 'config.php';


/* ==========================================================================
   COMPROBAR SESIÓN
   ========================================================================== */

if (!isset($_SESSION['usuario_id'])) {

    header('Location: login.php');
    exit;

}


$usuarioId = (int) $_SESSION['usuario_id'];

$error = '';
$mensaje = '';


/* ==========================================================================
   PROCESAR CAMBIO DE CONTRASEÑA
   ========================================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $passwordActual = $_POST['password_actual'] ?? '';
    $passwordNueva = $_POST['password_nueva'] ?? '';
    $passwordConfirmacion = $_POST['password_confirmacion'] ?? '';


    /* ----------------------------------------------------------------------
       Validar campos
       ---------------------------------------------------------------------- */

    if (
        $passwordActual === '' ||
        $passwordNueva === '' ||
        $passwordConfirmacion === ''
    ) {

        $error = 'Debes completar todos los campos.';

    } elseif ($passwordNueva !== $passwordConfirmacion) {

        $error = 'Las nuevas contraseñas no coinciden.';

    } elseif (strlen($passwordNueva) < 8) {

        $error = 'La nueva contraseña debe tener al menos 8 caracteres.';

    } elseif ($passwordActual === $passwordNueva) {

        $error = 'La nueva contraseña debe ser diferente de la actual.';

    } else {

        /* ------------------------------------------------------------------
           Buscar contraseña actual
           ------------------------------------------------------------------ */

        $stmt = $pdo->prepare("
            SELECT password
            FROM usuarios
            WHERE id = ?
              AND activo = 1
            LIMIT 1
        ");

        $stmt->execute([$usuarioId]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


        /* ------------------------------------------------------------------
           Comprobar contraseña actual
           ------------------------------------------------------------------ */

        if (!$usuario) {

            $error = 'No se ha encontrado tu cuenta.';

        } elseif (!password_verify($passwordActual, $usuario['password'])) {

            $error = 'La contraseña actual no es correcta.';

        } else {

            /* --------------------------------------------------------------
               Crear nuevo hash
               -------------------------------------------------------------- */

            $nuevoHash = password_hash(
                $passwordNueva,
                PASSWORD_DEFAULT
            );


            /* --------------------------------------------------------------
               Guardar nueva contraseña
               -------------------------------------------------------------- */

            try {

                $stmtUpdate = $pdo->prepare("
                    UPDATE usuarios
                    SET password = ?
                    WHERE id = ?
                      AND activo = 1
                ");

                $stmtUpdate->execute([
                    $nuevoHash,
                    $usuarioId
                ]);


                $mensaje = 'Tu contraseña se ha cambiado correctamente.';


            } catch (Throwable $e) {

                $error = 'No se ha podido cambiar la contraseña.';

            }

        }

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

    <title>Seguridad de la cuenta | ViziuneAI</title>

    <link
        rel="stylesheet"
        href="css/seguridad.css"
    >

</head>

<body>

    <main class="security-container">

        <section class="security-card">

            <div class="security-header">

                <span class="security-kicker">
                    SEGURIDAD
                </span>

                <h1>
                    Seguridad de la cuenta
                </h1>

                <p>
                    Gestiona la contraseña de acceso a tu cuenta de ViziuneAI.
                </p>

            </div>


            <?php if ($mensaje): ?>

                <div class="security-success">
                    <?= htmlspecialchars($mensaje) ?>
                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="security-error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form
                action="seguridad.php"
                method="POST"
                class="password-form"
            >

                <!-- ======================================================
                     CONTRASEÑA ACTUAL
                     ====================================================== -->

                <div class="form-group">

                    <label for="password_actual">
                        Contraseña actual
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password_actual"
                            name="password_actual"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password_actual"
                            aria-label="Mostrar contraseña"
                            title="Mostrar contraseña"
                        >()</button>

                    </div>

                </div>


                <!-- ======================================================
                     NUEVA CONTRASEÑA
                     ====================================================== -->

                <div class="form-group">

                    <label for="password_nueva">
                        Nueva contraseña
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password_nueva"
                            name="password_nueva"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password_nueva"
                            aria-label="Mostrar contraseña"
                            title="Mostrar contraseña"
                        >()</button>

                    </div>

                    <small>
                        Mínimo 8 caracteres.
                    </small>

                </div>


                <!-- ======================================================
                     CONFIRMAR NUEVA CONTRASEÑA
                     ====================================================== -->

                <div class="form-group">

                    <label for="password_confirmacion">
                        Repetir nueva contraseña
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password_confirmacion"
                            name="password_confirmacion"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password_confirmacion"
                            aria-label="Mostrar contraseña"
                            title="Mostrar contraseña"
                        >()</button>

                    </div>

                </div>


                <!-- ======================================================
                     BOTÓN
                     ====================================================== -->

                <button
                    type="submit"
                    class="change-password-button"
                >
                    🔐 Cambiar contraseña
                </button>

            </form>


            <!-- ==========================================================
                 VOLVER
                 ========================================================== -->

            <div class="security-footer">

                <a href="mi_cuenta.php">
                    ← Volver a Mi cuenta
                </a>

            </div>

        </section>

    </main>


    <!-- ================================================================
         JAVASCRIPT
         ================================================================ -->

    <script src="js/seguridad.js"></script>

</body>

</html>
