
<?php

session_start();

require_once 'config.php';

$error = '';

/*
|--------------------------------------------------------------------------
| Si ya hay una sesión iniciada
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['usuario_id'])) {
    header('Location: mi_cuenta.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Procesar registro
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $nif = trim($_POST['nif'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirmacion = $_POST['password_confirmacion'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */

    if ($nombre === '') {

        $error = 'Introduce tu nombre.';

    } elseif (mb_strlen($nombre) > 150) {

        $error = 'El nombre es demasiado largo.';

    } elseif ($nif === '') {

        $error = 'Introduce tu NIF/CIF.';

    } elseif (mb_strlen($nif) > 50) {

        $error = 'El NIF/CIF es demasiado largo.';

    } elseif ($email === '') {

        $error = 'Introduce tu correo electrónico.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Introduce un correo electrónico válido.';

    } elseif ($password === '') {

        $error = 'Introduce una contraseña.';

    } elseif (strlen($password) < 8) {

        $error = 'La contraseña debe tener al menos 8 caracteres.';

    } elseif ($password !== $passwordConfirmacion) {

        $error = 'Las contraseñas no coinciden.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Comprobar si el correo ya existe
        |--------------------------------------------------------------------------
        */

        try {

            $stmt = $pdo->prepare("
                SELECT id
                FROM usuarios
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);

            $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);


            if ($usuarioExistente) {

                $error = 'Ya existe una cuenta con ese correo electrónico.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Comenzar transacción
                |--------------------------------------------------------------------------
                */

                $pdo->beginTransaction();


                /*
                |--------------------------------------------------------------------------
                | Crear contraseña segura
                |--------------------------------------------------------------------------
                */

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                if ($passwordHash === false) {
                    throw new Exception(
                        'No se pudo generar la contraseña.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Crear usuario
                |--------------------------------------------------------------------------
                */

                $stmtUsuario = $pdo->prepare("
                    INSERT INTO usuarios (
                        nombre,
                        email,
                        password,
                        activo
                    )
                    VALUES (?, ?, ?, 1)
                ");

                $stmtUsuario->execute([
                    $nombre,
                    $email,
                    $passwordHash
                ]);


                /*
                |--------------------------------------------------------------------------
                | Obtener ID del usuario
                |--------------------------------------------------------------------------
                */

                $usuarioId = (int) $pdo->lastInsertId();


                if ($usuarioId <= 0) {
                    throw new Exception(
                        'No se pudo obtener el ID del usuario creado.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Crear cliente asociado
                |--------------------------------------------------------------------------
                */

                $stmtCliente = $pdo->prepare("
                    INSERT INTO clientes (
                        usuario_id,
                        tipo_persona,
                        nombre_razon_social,
                        nif,
                        email,
                        activo
                    )
                    VALUES (?, ?, ?, ?, ?, 1)
                ");

                $stmtCliente->execute([
                    $usuarioId,
                    'particular',
                    $nombre,
                    $nif,
                    $email
                ]);


                /*
                |--------------------------------------------------------------------------
                | Confirmar transacción
                |--------------------------------------------------------------------------
                */

                $pdo->commit();


                /*
                |--------------------------------------------------------------------------
                | Registro correcto
                |--------------------------------------------------------------------------
                */

                header('Location: login.php?registro=ok');
                exit;
            }

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Cancelar transacción si estaba activa
            |--------------------------------------------------------------------------
            */

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }


            /*
            |--------------------------------------------------------------------------
            | Mostrar error
            |--------------------------------------------------------------------------
            */

            $error = 'No se ha podido crear la cuenta. ' . $e->getMessage();
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

    <title>Crear cuenta | ViziuneAI</title>

    <link
        rel="stylesheet"
        href="css/registro.css"
    >

</head>

<body>

    <main class="registro">

        <div class="registro-cabecera">

            <h1>ViziuneAI</h1>

            <p>
                Crea tu cuenta
            </p>

        </div>


        <?php if ($error): ?>

            <div
                id="mensajeError"
                class="mensaje-error"
            >
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>

        <?php else: ?>

            <div
                id="mensajeError"
                class="mensaje-error"
                hidden
            ></div>

        <?php endif; ?>


        <form
            id="formRegistro"
            action="registro.php"
            method="POST"
            novalidate
        >

            <div class="campo">

                <label for="nombre">
                    Nombre
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Tu nombre"
                    maxlength="150"
                    autocomplete="name"
                    value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

                <small id="errorNombre"></small>

            </div>


            <div class="campo">

                <label for="nif">
                    NIF / CIF
                </label>

                <input
                    type="text"
                    id="nif"
                    name="nif"
                    placeholder="Tu NIF o CIF"
                    maxlength="50"
                    autocomplete="off"
                    value="<?= htmlspecialchars($_POST['nif'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

                <small id="errorNif"></small>

            </div>


            <div class="campo">

                <label for="email">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="tu@email.com"
                    autocomplete="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >

                <small id="errorEmail"></small>

            </div>


            <div class="campo">

                <label for="password">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Mínimo 8 caracteres"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <small id="errorPassword"></small>

            </div>


            <div class="campo">

                <label for="password_confirmacion">
                    Repetir contraseña
                </label>

                <input
                    type="password"
                    id="password_confirmacion"
                    name="password_confirmacion"
                    placeholder="Repite tu contraseña"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <small id="errorPasswordConfirmacion"></small>

            </div>


            <button
                type="submit"
                id="botonRegistro"
            >
                Crear cuenta
            </button>

        </form>


        <div class="enlace-login">

            ¿Ya tienes una cuenta?

            <a href="login.php">
                Iniciar sesión
            </a>

        </div>

    </main>


    <script src="js/registro.js"></script>

</body>

</html>
