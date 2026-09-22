
<?php

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = '';
$mensaje = '';


/*
|--------------------------------------------------------------------------
| PROCESAR FORMULARIO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDAR EMAIL
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $error = 'Introduce tu correo electrónico.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Introduce un correo electrónico válido.';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | COMPROBAR PDO
            |--------------------------------------------------------------------------
            */

            if (!isset($pdo) || !($pdo instanceof PDO)) {

                throw new Exception(
                    'La conexión con la base de datos no está disponible.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | BUSCAR USUARIO
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    nombre,
                    email,
                    activo
                FROM usuarios
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([
                $email
            ]);

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


            /*
            |--------------------------------------------------------------------------
            | USUARIO NO ENCONTRADO
            |--------------------------------------------------------------------------
            */

            if (!$usuario) {

                $error =
                    'No se ha encontrado ningún usuario con ese correo.';

            }


            /*
            |--------------------------------------------------------------------------
            | USUARIO DESACTIVADO
            |--------------------------------------------------------------------------
            */

            elseif ((int) $usuario['activo'] !== 1) {

                $error =
                    'Esta cuenta está desactivada.';

            }


            /*
            |--------------------------------------------------------------------------
            | USUARIO CORRECTO
            |--------------------------------------------------------------------------
            */

            else {

                /*
                |--------------------------------------------------------------------------
                | ELIMINAR TOKENS ANTERIORES
                |--------------------------------------------------------------------------
                */

                $stmtEliminar = $pdo->prepare("
                    DELETE FROM recuperacion_password
                    WHERE usuario_id = ?
                ");

                $stmtEliminar->execute([
                    $usuario['id']
                ]);


                /*
                |--------------------------------------------------------------------------
                | GENERAR TOKEN
                |--------------------------------------------------------------------------
                */

                $token = bin2hex(
                    random_bytes(32)
                );


                /*
                |--------------------------------------------------------------------------
                | HASH DEL TOKEN
                |--------------------------------------------------------------------------
                */

                $tokenHash = hash(
                    'sha256',
                    $token
                );


                /*
                |--------------------------------------------------------------------------
                | EXPIRACIÓN
                |--------------------------------------------------------------------------
                */

                $fechaExpiracion = date(
                    'Y-m-d H:i:s',
                    time() + 3600
                );


                /*
                |--------------------------------------------------------------------------
                | GUARDAR TOKEN
                |--------------------------------------------------------------------------
                */

                $stmtToken = $pdo->prepare("
                    INSERT INTO recuperacion_password
                    (
                        usuario_id,
                        token_hash,
                        fecha_expiracion,
                        usado
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        0
                    )
                ");

                $stmtToken->execute([
                    $usuario['id'],
                    $tokenHash,
                    $fechaExpiracion
                ]);


                /*
                |--------------------------------------------------------------------------
                | CONSTRUIR ENLACE
                |--------------------------------------------------------------------------
                */

                $protocolo =
                    (
                        isset($_SERVER['HTTPS']) &&
                        $_SERVER['HTTPS'] !== 'off'
                    )
                    ? 'https'
                    : 'http';


                $host =
                    $_SERVER['HTTP_HOST']
                    ?? 'localhost';


                $script =
                    $_SERVER['SCRIPT_NAME']
                    ?? '';


                $directorio =
                    dirname($script);


                if ($directorio === '/' || $directorio === '\\') {

                    $directorio = '';

                }


                $directorio =
                    rtrim(
                        $directorio,
                        '/'
                    );


                $enlace =
                    $protocolo .
                    '://' .
                    $host .
                    $directorio .
                    '/restablecer_password.php?token=' .
                    urlencode($token);


                /*
                |--------------------------------------------------------------------------
                | COMPROBAR CONFIGURACIÓN SMTP
                |--------------------------------------------------------------------------
                */

                if (
                    !isset($SMTP_HOST) ||
                    !isset($SMTP_USERNAME) ||
                    !isset($SMTP_PASSWORD) ||
                    !isset($SMTP_PORT) ||
                    !isset($SMTP_FROM)
                ) {

                    throw new Exception(
                        'La configuración SMTP no está completa en config.php.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | CREAR PHPMailer
                |--------------------------------------------------------------------------
                */

                $mail = new PHPMailer(true);


                /*
                |--------------------------------------------------------------------------
                | CONFIGURACIÓN SMTP
                |--------------------------------------------------------------------------
                */

                $mail->isSMTP();

                $mail->Host =
                    $SMTP_HOST;

                $mail->SMTPAuth =
                    true;

                $mail->Username =
                    $SMTP_USERNAME;

                $mail->Password =
                    $SMTP_PASSWORD;

                $mail->SMTPSecure =
                    PHPMailer::ENCRYPTION_STARTTLS;

                $mail->Port =
                    (int) $SMTP_PORT;

                $mail->CharSet =
                    'UTF-8';


                /*
                |--------------------------------------------------------------------------
                | DEBUG SMTP
                |--------------------------------------------------------------------------
                */

                $mail->SMTPDebug = 0;


                /*
                |--------------------------------------------------------------------------
                | REMITENTE
                |--------------------------------------------------------------------------
                */

                $mail->setFrom(
                    $SMTP_FROM,
                    'ViziuneAI'
                );


                /*
                |--------------------------------------------------------------------------
                | DESTINATARIO
                |--------------------------------------------------------------------------
                */

                $mail->addAddress(
                    $usuario['email'],
                    $usuario['nombre']
                );


                /*
                |--------------------------------------------------------------------------
                | ASUNTO
                |--------------------------------------------------------------------------
                */

                $mail->Subject =
                    'Restablecer contraseña | ViziuneAI';


                /*
                |--------------------------------------------------------------------------
                | HTML
                |--------------------------------------------------------------------------
                */

                $mail->isHTML(true);


                $nombreUsuario =
                    htmlspecialchars(
                        $usuario['nombre'],
                        ENT_QUOTES,
                        'UTF-8'
                    );


                $enlaceSeguro =
                    htmlspecialchars(
                        $enlace,
                        ENT_QUOTES,
                        'UTF-8'
                    );


                $mail->Body = '

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

</head>

<body
    style="
        margin:0;
        padding:0;
        background:#0b0b0f;
        font-family:Arial,sans-serif;
        color:#ffffff;
    "
>

    <div
        style="
            max-width:600px;
            margin:40px auto;
            padding:40px;
            background:#15151c;
            border-radius:12px;
        "
    >

        <h1
            style="
                margin:0 0 25px;
                color:#ffffff;
            "
        >
            VIZIUNE<span
                style="color:#00f3ff;"
            >AI</span>
        </h1>


        <h2
            style="
                color:#ffffff;
                font-weight:500;
            "
        >
            Restablecer contraseña
        </h2>


        <p
            style="
                color:#cccccc;
                line-height:1.6;
            "
        >
            Hola ' .
            $nombreUsuario .
            ',
        </p>


        <p
            style="
                color:#cccccc;
                line-height:1.6;
            "
        >
            Hemos recibido una solicitud para
            restablecer la contraseña de tu cuenta
            de ViziuneAI.
        </p>


        <p
            style="
                color:#cccccc;
                line-height:1.6;
            "
        >
            Pulsa el siguiente botón para crear
            una nueva contraseña:
        </p>


        <p
            style="
                margin:30px 0;
            "
        >

            <a
                href="' .
                $enlaceSeguro .
                '"
                style="
                    display:inline-block;
                    padding:14px 25px;
                    background:#00f3ff;
                    color:#000000;
                    text-decoration:none;
                    border-radius:7px;
                    font-weight:bold;
                "
            >
                Restablecer contraseña
            </a>

        </p>


        <p
            style="
                color:#999999;
                line-height:1.6;
                font-size:14px;
            "
        >
            Este enlace será válido durante
            60 minutos.
        </p>


        <p
            style="
                color:#999999;
                line-height:1.6;
                font-size:14px;
            "
        >
            Si tú no has solicitado este cambio,
            puedes ignorar este correo.
        </p>


        <hr
            style="
                border:0;
                border-top:1px solid #292933;
                margin:30px 0;
            "
        >


        <p
            style="
                color:#777777;
                font-size:12px;
            "
        >
            Generado mediante ViziuneAI.
        </p>

    </div>

</body>

</html>

                ';


                /*
                |--------------------------------------------------------------------------
                | TEXTO ALTERNATIVO
                |--------------------------------------------------------------------------
                */

                $mail->AltBody =
                    "VIZIUNEAI\n\n" .
                    "Restablecer contraseña\n\n" .
                    "Hola " .
                    $usuario['nombre'] .
                    ",\n\n" .
                    "Hemos recibido una solicitud para " .
                    "restablecer la contraseña de tu cuenta.\n\n" .
                    "Utiliza el siguiente enlace:\n\n" .
                    $enlace .
                    "\n\n" .
                    "Este enlace será válido durante 60 minutos.\n\n" .
                    "Si tú no has solicitado este cambio, " .
                    "puedes ignorar este correo.\n";


                /*
                |--------------------------------------------------------------------------
                | ENVIAR CORREO
                |--------------------------------------------------------------------------
                */

                $mail->send();


                /*
                |--------------------------------------------------------------------------
                | ENVÍO CORRECTO
                |--------------------------------------------------------------------------
                */

                $mensaje =
                    'El correo de recuperación se ha enviado correctamente. ' .
                    'Comprueba también la carpeta de Spam.';

            }

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | GUARDAR ERROR EN LOG
            |--------------------------------------------------------------------------
            */

            error_log(
                'ERROR RECUPERACION PASSWORD: ' .
                $e->getMessage() .
                ' | Archivo: ' .
                $e->getFile() .
                ' | Línea: ' .
                $e->getLine()
            );


            /*
            |--------------------------------------------------------------------------
            | COMPROBAR ERROR PHPMailer
            |--------------------------------------------------------------------------
            */

            $detalleMail = '';


            if (
                isset($mail) &&
                $mail instanceof PHPMailer &&
                !empty($mail->ErrorInfo)
            ) {

                $detalleMail =
                    $mail->ErrorInfo;

            }


            /*
            |--------------------------------------------------------------------------
            | MOSTRAR ERROR REAL DURANTE LA PRUEBA
            |--------------------------------------------------------------------------
            */

            if ($detalleMail !== '') {

                $error =
                    'ERROR SMTP: ' .
                    $detalleMail;

            } else {

                $error =
                    'ERROR PHP: ' .
                    $e->getMessage() .
                    ' | Archivo: ' .
                    basename($e->getFile()) .
                    ' | Línea: ' .
                    $e->getLine();

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

    <title>
        Recuperar contraseña | ViziuneAI
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
                    Recuperar contraseña
                </h1>


                <p>
                    Introduce el correo electrónico asociado
                    a tu cuenta.
                </p>

            </div>


            <?php if ($mensaje): ?>

                <div class="login-success">

                    <?= htmlspecialchars(
                        $mensaje,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="login-error">

                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            <?php endif; ?>


            <form
                action="recuperar_password.php"
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
                        value="<?= htmlspecialchars(
                            $_POST['email'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    id="loginButton"
                >
                    Enviar enlace
                </button>

            </form>


            <div class="login-footer">

                <div class="forgot-password">

                    <a href="login.php">
                        ← Volver a iniciar sesión
                    </a>

                </div>

            </div>

        </section>

    </main>

</body>

</html>
