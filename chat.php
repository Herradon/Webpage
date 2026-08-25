<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* ==============================
   COMPROBAR PETICIÓN
============================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Método no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==============================
   RECIBIR DATOS
============================== */

$data = json_decode(
    file_get_contents("php://input"),
    true
);


/* ==============================
   COMPROBAR ACCIÓN EMAIL
============================== */

$action = $data["action"] ?? "";


/* ==========================================================
   ENVIAR CONVERSACIÓN POR EMAIL
========================================================== */

if ($action === "email") {

    $conversacion =
        trim($data["conversacion"] ?? "");


    if ($conversacion === "") {

        echo json_encode([
            "success" => false,
            "error" =>
                "No hay ninguna conversación para enviar."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /* ==============================
       CREAR EMAIL
    ============================== */

    $asunto =
        "Nueva conversación del asistente web";


    $textoEmail =

        "Hola Alejandro.\n\n" .

        "Un cliente quiere contactar contigo " .
        "desde el asistente web.\n\n" .

        "================================\n" .
        "CONVERSACIÓN DEL CHAT\n" .
        "================================\n\n" .

        $conversacion;


    /* ==============================
       PHPMailer
    ============================== */

    $mail = new PHPMailer(true);


    try {

        /* ==============================
           CONFIGURACIÓN SMTP GMAIL
        ============================== */

        $mail->isSMTP();

        $mail->Host =
            "smtp.gmail.com";

        $mail->SMTPAuth =
            true;

        $mail->Username =
            "herradon45@gmail.com";

        /*
           IMPORTANTE:

           Esta variable debe estar
           en config.php.

           Es la CONTRASEÑA DE APLICACIÓN
           de Google, NO tu contraseña
           normal.
        */

        $mail->Password =
            $SMTP_PASSWORD;

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port =
            587;


        /* ==============================
           REMITENTE
        ============================== */

        $mail->setFrom(
            "herradon45@gmail.com",
            "Asistente Web"
        );


        /* ==============================
           DESTINATARIO
        ============================== */

        $mail->addAddress(
            "herradon45@gmail.com",
            "Alejandro Herradón"
        );


        /* ==============================
           CONTENIDO
        ============================== */

        $mail->CharSet =
            "UTF-8";

        $mail->isHTML(false);

        $mail->Subject =
            $asunto;

        $mail->Body =
            $textoEmail;


        /* ==============================
           ENVIAR
        ============================== */

        $mail->send();


        /* ==============================
           RESPUESTA CORRECTA
        ============================== */

        echo json_encode([

            "success" => true,

            "message" =>
                "La conversación se ha enviado correctamente por correo."

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
}


/* ==========================================================
   CHAT NORMAL CON OPENAI
========================================================== */

$message = trim(
    $data["message"] ?? ""
);


if ($message === "") {

    echo json_encode([
        "success" => false,
        "error" => "El mensaje está vacío."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==============================
   VALIDAR API KEY
============================== */

if (empty($OPENAI_API_KEY)) {

    echo json_encode([
        "success" => false,
        "error" =>
            "La API Key de OpenAI no está configurada."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==============================
   OPENAI
============================== */

$url =
    "https://api.openai.com/v1/responses";


$payload = [

    "model" =>
        $OPENAI_MODEL,

    "instructions" =>

        "Eres Alejandro Herradón, el asistente virtual " .
        "de una inmobiliaria. " .

        "Responde siempre en español, de forma clara, " .
        "amable y profesional. " .

        "Si no conoces una información, indica que no " .
        "tienes esa información.",

    "input" =>
        $message

];


$jsonPayload =
    json_encode($payload);


/* ==============================
   CURL
============================== */

$ch =
    curl_init($url);


curl_setopt_array($ch, [

    CURLOPT_RETURNTRANSFER =>
        true,

    CURLOPT_POST =>
        true,

    CURLOPT_POSTFIELDS =>
        $jsonPayload,

    CURLOPT_HTTPHEADER => [

        "Content-Type: application/json",

        "Authorization: Bearer " .
        $OPENAI_API_KEY

    ],

    CURLOPT_TIMEOUT =>
        60

]);


$response =
    curl_exec($ch);


$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$curlError =
    curl_error($ch);


curl_close($ch);


/* ==============================
   ERROR CURL
============================== */

if ($response === false) {

    echo json_encode([

        "success" => false,

        "error" =>
            "Error conectando con OpenAI: " .
            $curlError

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==============================
   DECODIFICAR OPENAI
============================== */

$result =
    json_decode(
        $response,
        true
    );


/* ==============================
   ERROR OPENAI
============================== */

if ($httpCode >= 400) {

    $errorMessage =
        $result["error"]["message"]
        ?? "Error desconocido de OpenAI.";


    echo json_encode([

        "success" => false,

        "error" =>
            $errorMessage

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==============================
   EXTRAER RESPUESTA
============================== */

$answer =
    $result["output"][0]["content"][0]["text"]
    ?? null;


if (!$answer) {

    echo json_encode([

        "success" => false,

        "error" =>
            "No se pudo obtener una respuesta."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==============================
   GUARDAR CONVERSACIÓN
============================== */

try {

    $stmt =
        $pdo->prepare("

            INSERT INTO conversaciones
            (
                usuario,
                respuesta
            )

            VALUES
            (
                :usuario,
                :respuesta
            )

        ");


    $stmt->execute([

        ":usuario" =>
            $message,

        ":respuesta" =>
            $answer

    ]);

} catch (PDOException $e) {

    /*
       Si falla MySQL,
       no detenemos el chatbot.
    */

}


/* ==============================
   RESPUESTA AL JAVASCRIPT
============================== */

echo json_encode([

    "success" =>
        true,

    "answer" =>
        $answer

], JSON_UNESCAPED_UNICODE);

?>