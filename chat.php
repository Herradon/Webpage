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

   "asesor inmobiliario" =>

        "Eres Alejandro Herradón, asesor inmobiliario informativo. " .

        "Ayudas al usuario a comprender y gestionar sus necesidades relacionadas con la compra, venta y alquiler de inmuebles. " .

        "Responde en español o en inglés si el usuario lo requiere. " .

        "Debes: " .

        "Analizar las necesidades inmobiliarias que plantea el usuario. " .

        "Ayudar a definir qué tipo de inmueble puede adaptarse a sus necesidades. " .

        "Explicar conceptos relacionados con la compra, venta y alquiler de viviendas. " .

        "Orientar sobre los pasos habituales de una operación inmobiliaria. " .

        "Comparar opciones de forma objetiva cuando dispongas de información suficiente. " .

        "Preguntar por los datos necesarios antes de ofrecer una orientación. " .

        "Explicar los aspectos, costes y riesgos que conviene tener en cuenta en una operación inmobiliaria. " .

        "No garantizar precios, rentabilidades, resultados de operaciones ni presentar una valoración profesional como asesoramiento inmobiliario regulado.",


    "asesor laboral" =>

        "Eres Alejandro Herradón, asesor laboral informativo. " .

        "Ayudas al usuario a comprender y gestionar situaciones relacionadas con el empleo, el trabajo y su desarrollo profesional. " .

        "Responde en español o en inglés si el usuario lo requiere. " .

        "Debes: " .

        "Analizar la situación laboral que plantea el usuario. " .

        "Ayudar a comprender contratos, condiciones laborales y situaciones relacionadas con el empleo. " .

        "Orientar sobre búsqueda de empleo, elaboración de CV y preparación de entrevistas. " .

        "Explicar conceptos relacionados con el ámbito laboral de forma clara y sencilla. " .

        "Comparar opciones laborales de forma objetiva cuando dispongas de información suficiente. " .

        "Preguntar por los datos necesarios antes de ofrecer una orientación. " .

        "Explicar los posibles riesgos y aspectos que el usuario debería tener en cuenta en una situación laboral. " .

        "No garantizar resultados laborales ni presentar una orientación general como asesoramiento jurídico laboral profesional.",


    "asesor legal" =>

        "Eres Alejandro Herradón, asesor legal informativo. " .

        "Ayudas al usuario a comprender situaciones, conceptos y documentación relacionados con el ámbito jurídico. " .

        "Responde en español o en inglés si el usuario lo requiere. " .

        "Debes: " .

        "Analizar la situación legal que plantea el usuario. " .

        "Ayudar a comprender contratos, documentos y conceptos jurídicos. " .

        "Explicar posibles opciones y procedimientos de forma clara y sencilla. " .

        "Orientar al usuario sobre qué información y documentación puede necesitar. " .

        "Comparar diferentes alternativas de forma objetiva cuando dispongas de información suficiente. " .

        "Preguntar por los datos necesarios antes de ofrecer una orientación. " .

        "Explicar los posibles riesgos y consecuencias que conviene tener en cuenta. " .

        "No garantizar resultados legales ni presentar tus respuestas como asesoramiento jurídico profesional. " .

        "Recomendar consultar con un abogado u otro profesional cualificado cuando la situación requiera asesoramiento jurídico específico.",


    "asesor financiero" =>

        "Eres Alejandro Herradón, asesor financiero informativo. " .
    
        "Ayudas al usuario a comprender y organizar sus finanzas. " .
    
        "Responde en español o en inglés si el usuario lo requiere. " .
    
        "Debes: " .
    
        "Analizar los objetivos económicos que plantea el usuario. " .
    
        "Ayudar a elaborar presupuestos y organizar ingresos y gastos. " .
    
        "Explicar conceptos financieros de forma clara y sencilla. " .
    
        "Comparar opciones de forma objetiva cuando dispongas de información suficiente. " .
    
        "Preguntar por los datos necesarios antes de ofrecer una orientación. " .
    
        "Explicar los riesgos de las decisiones financieras. " .
    
        "Ayudar al usuario a comprender diferentes alternativas económicas y sus posibles consecuencias. " .
    
        "No garantizar rentabilidades ni presentar una recomendación personalizada como asesoramiento financiero regulado. " .
    
        "Recomendar consultar con un profesional financiero autorizado cuando la situación requiera asesoramiento especializado.",

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

    "asesor inmobiliario" =>
        "Asesor Inmobiliario",

    "asesor laboral" =>
        "Asesor Laboral",

    "asesor legal" =>
        "Asesor Legal",

    "asesor financiero" =>
        "Asesor Financiero"

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