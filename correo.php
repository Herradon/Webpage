<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* ==========================================
   COMPROBAR MÉTODO
========================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Método no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   RECIBIR CONVERSACIÓN
========================================== */

$conversacion = trim(
    $_POST["conversacion"] ?? ""
);


if ($conversacion === "") {

    echo json_encode([
        "success" => false,
        "error" => "No hay ninguna conversación para enviar."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   CREAR EMAIL
========================================== */

$asunto = "Nueva conversación del asistente web";


$textoEmail =
    "Hola Alejandro.\n\n" .

    "Un cliente ha iniciado una conversación " .
    "con el asistente web.\n\n" .

    "====================================\n" .
    "CONVERSACIÓN DEL CHAT\n" .
    "====================================\n\n" .

    $conversacion;


/* ==========================================
   PHPMailer
========================================== */

$mail = new PHPMailer(true);


try {

    /* ======================================
       SMTP GMAIL
    ====================================== */

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
        $SMTP_PORT;


    /* ======================================
       REMITENTE
    ====================================== */

    $mail->setFrom(
        $SMTP_FROM,
        "Asistente Web"
    );


    /* ======================================
       DESTINATARIO
    ====================================== */

    $mail->addAddress(
        $SMTP_TO,
        "Alejandro Herradón"
    );


    /* ======================================
       EMAIL
    ====================================== */

    $mail->CharSet =
        "UTF-8";

    $mail->isHTML(false);

    $mail->Subject =
        $asunto;

    $mail->Body =
        $textoEmail;


    /* ======================================
       ENVIAR
    ====================================== */

    $mail->send();


    /* ======================================
       ÉXITO
    ====================================== */

    echo json_encode([
        "success" => true,
        "message" =>
            "La conversación se ha enviado correctamente."
    ], JSON_UNESCAPED_UNICODE);

    exit;


} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" =>
            "No se pudo enviar el correo: " .
            $mail->ErrorInfo
    ], JSON_UNESCAPED_UNICODE);

    exit;
}
?>