
<?php

session_start();

require_once 'config.php';

$error = '';
$mensaje = '';

/*
|--------------------------------------------------------------------------
| Mensaje después del registro
|--------------------------------------------------------------------------
*/

if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
    $mensaje = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';
}


/*
|--------------------------------------------------------------------------
| Procesar login
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */

    if ($email === '' || $password === '') {

        $error = 'Debes introducir tu correo electrónico y contraseña.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Introduce un correo electrónico válido.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Buscar usuario
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT
                id,
                nombre,
                email,
                password,
                activo,
                suscripcion_activa,
                suscripcion_inicio,
                suscripcion_fin
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


        /*
        |--------------------------------------------------------------------------
        | Comprobar usuario y contraseña
        |--------------------------------------------------------------------------
        */

        if (!$usuario || !$usuario['activo']) {

            $error = 'El correo o la contraseña son incorrectos.';

        } elseif (!password_verify($password, $usuario['password'])) {

            $error = 'El correo o la contraseña son incorrectos.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Crear sesión segura
            |--------------------------------------------------------------------------
            */

            session_regenerate_id(true);

            $_SESSION['usuario_id'] = (int) $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];


            /*
            |--------------------------------------------------------------------------
            | Buscar cliente relacionado con el usuario
            |--------------------------------------------------------------------------
            */

            $stmtCliente = $pdo->prepare("
                SELECT id
                FROM clientes
                WHERE usuario_id = ?
                  AND activo = 1
                LIMIT 1
            ");

            $stmtCliente->execute([
                $usuario['id']
            ]);

            $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);


            /*
            |--------------------------------------------------------------------------
            | Guardar cliente en sesión
            |--------------------------------------------------------------------------
            */

            if ($cliente) {

                $_SESSION['cliente_id'] = (int) $cliente['id'];

            } else {

                /*
                | Si por algún motivo todavía no existe el cliente,
                | dejamos la sesión de usuario creada.
                */

                unset($_SESSION['cliente_id']);
            }


            /*
            |--------------------------------------------------------------------------
            | COMPROBAR SUSCRIPCIÓN
            |--------------------------------------------------------------------------
            |
            | La cuenta de ViziuneSL (usuario ID 8) tiene
            | acceso gratuito.
            |
            | Todos los demás usuarios necesitan una
            | suscripción activa.
            |--------------------------------------------------------------------------
            */

            if ((int) $usuario['id'] === 8) {

                /*
                | Cuenta oficial de ViziuneSL:
                | acceso gratuito.
                */

                $suscripcionActiva = true;

            } else {

                /*
                | Resto de usuarios:
                | comprobar su suscripción.
                */

                $suscripcionActiva =
                    (int) $usuario['suscripcion_activa'] === 1;


                /*
                |--------------------------------------------------------------------------
                | Comprobar fecha de finalización
                |--------------------------------------------------------------------------
                */

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


                    /*
                    | Si la suscripción ha caducado,
                    | la desactivamos.
                    */

                    if ($fechaFin < $ahora) {

                        $suscripcionActiva = false;


                        $stmtActualizar =
                            $pdo->prepare("
                                UPDATE usuarios
                                SET suscripcion_activa = 0
                                WHERE id = ?
                            ");

                        $stmtActualizar->execute([
                            $usuario['id']
                        ]);

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Guardar estado de suscripción en sesión
            |--------------------------------------------------------------------------
            */

            $_SESSION['suscripcion_activa'] =
                $suscripcionActiva ? 1 : 0;


            /*
            |--------------------------------------------------------------------------
            | ENTRADA AL ÁREA PRIVADA
            |--------------------------------------------------------------------------
            */

            if ($suscripcionActiva) {

                /*
                | Usuario con suscripción o cuenta ViziuneSL:
                | entra normalmente a la web.
                */

                header('Location: index.php');
                exit;

            } else {

                /*
                | Usuario sin suscripción:
                | va a la página de suscripción.
                */

                header('Location: suscripcion.php');
                exit;

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

    <title>Iniciar sesión | ViziuneAI</title>

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

                <h1>Iniciar sesión</h1>

                <p>
                    Accede a tu área de cliente para gestionar tus facturas.
                </p>

            </div>


            <?php if ($mensaje): ?>

                <div class="login-success">
                    <?= htmlspecialchars($mensaje) ?>
                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="login-error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form
                id="loginForm"
                action="login.php"
                method="POST"
            >

                <div class="form-group">

                    <label for="email">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="tu@email.com"
                        autocomplete="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Tu contraseña"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    id="loginButton"
                >
                    Iniciar sesión
                </button>

            </form>


            <div class="login-footer">

                <p>
                    ¿Todavía no tienes una cuenta?
                </p>

                <a href="registro.php">
                    Crear una cuenta
                </a>

                <div class="volver-inicio">

                    <a href="index.php">
                        ← Volver al inicio
                    </a>

                </div>

            </div>

        </section>

    </main>


    <script src="js/login.js"></script>

</body>

</html>
