<?php

session_start();

require_once __DIR__ . '/config.php';

$error = '';
$mensaje = '';
$tokenValido = false;
$token = '';

/*
|--------------------------------------------------------------------------
| RECIBIR TOKEN
|--------------------------------------------------------------------------
*/

$token = trim($_GET['token'] ?? '');

if ($token === '') {

    $error = 'El enlace de recuperación no es válido.';

} elseif (
    !preg_match('/^[a-f0-9]{64}$/i', $token)
) {

    $error = 'El enlace de recuperación no es válido.';

} else {

    /*
    |--------------------------------------------------------------------------
    | GENERAR HASH DEL TOKEN RECIBIDO
    |--------------------------------------------------------------------------
    */

    $tokenHash = hash(
        'sha256',
        $token
    );


    /*
    |--------------------------------------------------------------------------
    | BUSCAR TOKEN
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            rp.id,
            rp.usuario_id,
            rp.fecha_expiracion,
            rp.usado,
            u.nombre,
            u.email
        FROM recuperacion_password rp
        INNER JOIN usuarios u
            ON u.id = rp.usuario_id
        WHERE rp.token_hash = ?
        LIMIT 1
    ");

    $stmt->execute([
        $tokenHash
    ]);

    $recuperacion =
        $stmt->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR TOKEN
    |--------------------------------------------------------------------------
    */

    if (!$recuperacion) {

        $error =
            'El enlace de recuperación no es válido o ya no existe.';

    } elseif ((int) $recuperacion['usado'] === 1) {

        $error =
            'Este enlace de recuperación ya ha sido utilizado.';

    } elseif (
        strtotime(
            $recuperacion['fecha_expiracion']
        ) < time()
    ) {

        $error =
            'El enlace de recuperación ha caducado.';

    } elseif (
        !isset($recuperacion['usuario_id'])
    ) {

        $error =
            'No se ha podido identificar la cuenta.';

    } else {

        $tokenValido = true;
    }
}


/*
|--------------------------------------------------------------------------
| PROCESAR NUEVA CONTRASEÑA
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $tokenValido
) {

    $password =
        $_POST['password'] ?? '';

    $passwordConfirmacion =
        $_POST['password_confirmacion'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | VALIDAR CONTRASEÑA
    |--------------------------------------------------------------------------
    */

    if (
        $password === '' ||
        $passwordConfirmacion === ''
    ) {

        $error =
            'Debes introducir la nueva contraseña dos veces.';

    } elseif (
        strlen($password) < 8
    ) {

        $error =
            'La contraseña debe tener al menos 8 caracteres.';

    } elseif (
        $password !== $passwordConfirmacion
    ) {

        $error =
            'Las contraseñas no coinciden.';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | GENERAR HASH SEGURO
            |--------------------------------------------------------------------------
            */

            $passwordHash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR CONTRASEÑA
            |--------------------------------------------------------------------------
            */

            $pdo->beginTransaction();


            $stmtPassword =
                $pdo->prepare("
                    UPDATE usuarios
                    SET password = ?
                    WHERE id = ?
                    LIMIT 1
                ");

            $stmtPassword->execute([
                $passwordHash,
                $recuperacion['usuario_id']
            ]);


            /*
            |--------------------------------------------------------------------------
            | INVALIDAR TOKEN
            |--------------------------------------------------------------------------
            */

            $stmtToken =
                $pdo->prepare("
                    UPDATE recuperacion_password
                    SET usado = 1
                    WHERE id = ?
                    LIMIT 1
                ");

            $stmtToken->execute([
                $recuperacion['id']
            ]);


            /*
            |--------------------------------------------------------------------------
            | ELIMINAR OTROS TOKENS DEL USUARIO
            |--------------------------------------------------------------------------
            */

            $stmtEliminar =
                $pdo->prepare("
                    DELETE FROM recuperacion_password
                    WHERE usuario_id = ?
                      AND id <> ?
                ");

            $stmtEliminar->execute([
                $recuperacion['usuario_id'],
                $recuperacion['id']
            ]);


            /*
            |--------------------------------------------------------------------------
            | CONFIRMAR CAMBIOS
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            /*
            |--------------------------------------------------------------------------
            | MENSAJE DE ÉXITO
            |--------------------------------------------------------------------------
            */

            $mensaje =
                'Tu contraseña se ha cambiado correctamente. ' .
                'Ya puedes iniciar sesión.';


            /*
            |--------------------------------------------------------------------------
            | INVALIDAR TOKEN EN ESTA PÁGINA
            |--------------------------------------------------------------------------
            */

            $tokenValido = false;


        } catch (PDOException $e) {

            /*
            |--------------------------------------------------------------------------
            | DESHACER CAMBIOS SI HAY ERROR
            |--------------------------------------------------------------------------
            */

            if (
                $pdo->inTransaction()
            ) {

                $pdo->rollBack();
            }


            error_log(
                'Error restableciendo contraseña: ' .
                $e->getMessage()
            );


            $error =
                'No se pudo cambiar la contraseña. ' .
                'Inténtalo de nuevo más tarde.';
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

    <title>
        Nueva contraseña | ViziuneAI
    </title>

    <link
        rel="stylesheet"
        href="css/login.css"
    >

</head>

<body>

    <main class="login-container">

        <section class="login-card">

            <div class="login-header">

                <div class="logo">
                    VIZIUNE<span>AI</span>
                </div>


                <h1>
                    Nueva contraseña
                </h1>


                <?php if ($mensaje): ?>

                    <p>
                        <?= htmlspecialchars(
                            $mensaje,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                <?php elseif ($tokenValido): ?>

                    <p>
                        Introduce tu nueva contraseña.
                    </p>

                <?php endif; ?>

            </div>


            <?php if ($error): ?>

                <div class="login-error">

                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            <?php endif; ?>


            <?php if ($tokenValido): ?>

                <form
                    action="restablecer_password.php?token=<?= urlencode($token) ?>"
                    method="POST"
                >

                    <!-- NUEVA CONTRASEÑA -->

                    <div class="form-group">

                        <label for="password">
                            Nueva contraseña
                        </label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Nueva contraseña"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                            <button
                                type="button"
                                class="toggle-password"
                                data-target="password"
                                aria-label="Mostrar contraseña"
                                title="Mostrar contraseña"
                            >◎</button>

                        </div>

                    </div>


                    <!-- REPETIR CONTRASEÑA -->

                    <div class="form-group">

                        <label for="password_confirmacion">
                            Repetir contraseña
                        </label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="password_confirmacion"
                                name="password_confirmacion"
                                placeholder="Repite tu contraseña"
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
                            >◎</button>

                        </div>

                    </div>


                    <button
                        type="submit"
                        id="loginButton"
                    >
                        Cambiar contraseña
                    </button>

                </form>


            <?php elseif ($mensaje): ?>

                <div class="login-footer">

                    <a href="login.php">
                        Ir a iniciar sesión
                    </a>

                </div>


            <?php else: ?>

                <div class="login-footer">

                    <a href="recuperar_password.php">
                        Solicitar un nuevo enlace
                    </a>

                    <div class="volver-inicio">

                        <a href="login.php">
                            ← Volver a iniciar sesión
                        </a>

                    </div>

                </div>

            <?php endif; ?>

        </section>

    </main>


    <script src="js/login.js"></script>

</body>

</html>