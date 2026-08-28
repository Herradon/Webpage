<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* ==========================================
   COMPROBAR PETICIÓN
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
   RECIBIR JSON
========================================== */

$data = json_decode(
    file_get_contents("php://input"),
    true
);


if (!is_array($data)) {

    echo json_encode([
        "success" => false,
        "error" => "Datos recibidos incorrectamente."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   DATOS
========================================== */

$action =
    trim($data["action"] ?? "");

$nombre =
    trim($data["nombre"] ?? "");

$email =
    trim($data["email"] ?? "");

$agent =
    trim($data["agent"] ?? "asesor");


/* ==========================================================
   IDENTIDADES DEL AGENTE
========================================================== */

$agentPrompts = [

    "asesor" =>

        "Eres Alejandro Herradón, el asistente virtual " .
        "de ViziuneAI. " .

        "Tu función principal es atender al usuario, " .
        "entender sus necesidades y ofrecer asesoramiento " .
        "claro, cercano y profesional. " .

        "Responde siempre en español. " .

        "No inventes información que no conozcas. " .

        "Si necesitas más información para responder, " .
        "pregunta al usuario.",


    "ventas" =>

        "Eres Alejandro, asesor comercial de ViziuneAI. " .

        "Tu especialidad son las ventas, los servicios " .
        "de la empresa, las necesidades de los clientes " .
        "y la preparación de posibles presupuestos. " .

        "Debes explicar las ventajas de los servicios " .
        "de forma clara y profesional, sin presionar " .
        "al cliente. " .

        "Responde siempre en español. " .

        "Nunca inventes precios, servicios o condiciones " .
        "que no conozcas.",


    "ia" =>

        "Eres Alejandro, consultor especializado en " .
        "inteligencia artificial y automatización para empresas. " .

        "Tu función es analizar las necesidades del usuario " .
        "y explicar cómo la inteligencia artificial puede " .
        "ayudar a automatizar procesos, atención al cliente, " .
        "ventas, gestión y otras tareas empresariales. " .

        "Responde siempre en español. " .

        "Utiliza explicaciones sencillas y profesionales. " .

        "No inventes datos ni capacidades concretas de ViziuneAI.",


    "soporte" =>

        "Eres Alejandro, especialista de soporte técnico " .
        "de ViziuneAI. " .

        "Tu función es ayudar al usuario a solucionar " .
        "problemas técnicos relacionados con los servicios " .
        "y sistemas de la empresa. " .

        "Explica las soluciones paso a paso y de forma sencilla. " .

        "Responde siempre en español. " .

        "Si no puedes determinar la solución, solicita " .
        "la información necesaria o recomienda contactar " .
        "con el equipo técnico."

];


/*
   Si alguien manipula el valor desde el navegador,
   utilizamos asesor como opción segura.
*/

if (!isset($agentPrompts[$agent])) {

    $agent =
        "asesor";

}


$systemPrompt =
    $agentPrompts[$agent];


/* ==========================================================
   ENVIAR CONVERSACIÓN POR EMAIL
========================================================== */

if ($action === "email") {

    $conversacion =
        trim($data["conversacion"] ?? "");


    /* ==============================
       VALIDAR CONVERSACIÓN
    ============================== */

    if ($conversacion === "") {

        echo json_encode([
            "success" => false,
            "error" => "No hay ninguna conversación para enviar."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /* ==============================
       VALIDAR NOMBRE
    ============================== */

    if ($nombre === "") {

        echo json_encode([
            "success" => false,
            "error" => "El nombre es obligatorio."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /* ==============================
       VALIDAR EMAIL
    ============================== */

    if (
        $email === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        echo json_encode([
            "success" => false,
            "error" => "El correo electrónico no es válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /* ==============================
       ID CONVERSACIÓN
    ============================== */

    $conversacionId =
        strtoupper(
            substr(
                bin2hex(random_bytes(6)),
                0,
                8
            )
        );


    /* ==============================
       NOMBRE IDENTIDAD
    ============================== */

    $nombresAgentes = [

        "asesor" =>
            "Asesor",

        "ventas" =>
            "Asesor Comercial",

        "ia" =>
            "Consultor de Inteligencia Artificial",

        "soporte" =>
            "Especialista de Soporte"

    ];


    $nombreAgente =
        $nombresAgentes[$agent]
        ?? "Asistente";


    /* ======================================================
       GUARDAR EN MYSQL
    ====================================================== */

    try {

        $stmt = $pdo->prepare("

            INSERT INTO conversaciones
            (
                conversacion_id,
                nombre,
                email,
                usuario,
                respuesta
            )

            VALUES
            (
                :conversacion_id,
                :nombre,
                :email,
                :usuario,
                :respuesta
            )

        ");


        $stmt->execute([

            ":conversacion_id" =>
                $conversacionId,

            ":nombre" =>
                $nombre,

            ":email" =>
                $email,

            ":usuario" =>
                $conversacion,

            ":respuesta" =>
                "CONVERSACIÓN ENVIADA POR EL CLIENTE - " .
                $nombreAgente

        ]);


    } catch (PDOException $e) {

        error_log(
            "Error guardando conversación: " .
            $e->getMessage()
        );


        echo json_encode([

            "success" => false,

            "error" =>
                "No se pudo guardar la conversación en la base de datos."

        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /* ======================================================
       CREAR EMAIL
    ====================================================== */

    $asunto =
        "Nueva conversación - " .
        $nombreAgente .
        " - #" .
        $conversacionId;


    $textoEmail =

        "NUEVO CONTACTO DESDE EL ASISTENTE IA\n\n" .

        "========================================\n" .

        "DATOS DEL CLIENTE\n" .

        "========================================\n\n" .

        "Nombre: " .
        $nombre .
        "\n" .

        "Email: " .
        $email .
        "\n" .

        "Especialista seleccionado: " .
        $nombreAgente .
        "\n" .

        "ID DE CONVERSACIÓN: " .
        $conversacionId .
        "\n\n" .

        "========================================\n" .

        "CONVERSACIÓN\n" .

        "========================================\n\n" .

        $conversacion;


    /* ======================================================
       PHPMailer
    ====================================================== */

    $mail =
        new PHPMailer(true);


    try {

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


        /* ==============================
           REMITENTE
        ============================== */

        $mail->setFrom(
            $SMTP_FROM,
            "ViziuneAI"
        );


        /* ==============================
           DESTINATARIO
        ============================== */

        $mail->addAddress(
            $SMTP_TO,
            "Alejandro Herradón"
        );


        /* ==============================
           RESPONDER AL CLIENTE
        ============================== */

        $mail->addReplyTo(
            $email,
            $nombre
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


        echo json_encode([

            "success" =>
                true,

            "message" =>
                "La conversación se ha enviado correctamente.",

            "conversacion_id" =>
                $conversacionId

        ], JSON_UNESCAPED_UNICODE);

        exit;


    } catch (Exception $e) {

        error_log(
            "Error PHPMailer: " .
            $mail->ErrorInfo
        );


        http_response_code(500);


        echo json_encode([

            "success" =>
                false,

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

$message =
    trim(
        $data["message"] ?? ""
    );


if ($message === "") {

    echo json_encode([

        "success" =>
            false,

        "error" =>
            "El mensaje está vacío."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   OPENAI
========================================================== */

if (empty($OPENAI_API_KEY)) {

    echo json_encode([

        "success" =>
            false,

        "error" =>
            "La API Key de OpenAI no está configurada."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$url =
    "https://api.openai.com/v1/responses";


$payload = [

    "model" =>
        $OPENAI_MODEL,

    "instructions" =>
        $systemPrompt,

    "input" =>
        $message

];


$jsonPayload =
    json_encode($payload);


/* ==========================================================
   CURL
========================================================== */

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


/* ==========================================================
   ERROR CURL
========================================================== */

if ($response === false) {

    echo json_encode([

        "success" =>
            false,

        "error" =>
            "Error conectando con OpenAI: " .
            $curlError

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   DECODIFICAR
========================================================== */

$result =
    json_decode(
        $response,
        true
    );


/* ==========================================================
   ERROR OPENAI
========================================================== */

if ($httpCode >= 400) {

    $errorMessage =
        $result["error"]["message"]
        ?? "Error desconocido de OpenAI.";


    echo json_encode([

        "success" =>
            false,

        "error" =>
            $errorMessage

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   OBTENER RESPUESTA
========================================================== */

$answer = null;


if (
    isset($result["output"]) &&
    is_array($result["output"])
) {

    foreach (
        $result["output"]
        as $outputItem
    ) {

        if (
            ($outputItem["type"] ?? "")
            !== "message"
        ) {
            continue;
        }


        if (
            !isset(
                $outputItem["content"]
            )
        ) {
            continue;
        }


        foreach (
            $outputItem["content"]
            as $contentItem
        ) {

            if (
                ($contentItem["type"] ?? "")
                === "output_text"
            ) {

                $answer =
                    $contentItem["text"]
                    ?? null;

                break 2;

            }

        }

    }

}


if (!$answer) {

    echo json_encode([

        "success" =>
            false,

        "error" =>
            "No se pudo obtener una respuesta."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   GUARDAR MENSAJE EN MYSQL
========================================================== */

try {

    $stmt =
        $pdo->prepare("

            INSERT INTO conversaciones
            (
                conversacion_id,
                nombre,
                email,
                usuario,
                respuesta
            )

            VALUES
            (
                :conversacion_id,
                :nombre,
                :email,
                :usuario,
                :respuesta
            )

        ");


    $stmt->execute([

        ":conversacion_id" =>
            "CHAT-" .
            session_id(),

        ":nombre" =>
            $nombre !== ""
                ? $nombre
                : null,

        ":email" =>
            $email !== ""
                ? $email
                : null,

        ":usuario" =>
            $message,

        ":respuesta" =>
            $answer

    ]);


} catch (PDOException $e) {

    error_log(
        "Error guardando conversación: " .
        $e->getMessage()
    );

}


/* ==========================================================
   RESPUESTA AL JAVASCRIPT
========================================================== */

echo json_encode([

    "success" =>
        true,

    "answer" =>
        $answer,

    "agent" =>
        $agent

], JSON_UNESCAPED_UNICODE);

?>