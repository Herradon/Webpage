<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* ==========================================================
   COMPROBAR PETICIÓN
========================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Método no permitido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   RECIBIR JSON
========================================================== */

$rawData =
    file_get_contents("php://input");

$data =
    json_decode(
        $rawData,
        true
    );


if (!is_array($data)) {

    echo json_encode([
        "success" => false,
        "error" => "Los datos recibidos no son válidos."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   DATOS
========================================================== */

$action =
    trim(
        $data["action"] ?? ""
    );


$message =
    trim(
        $data["message"] ?? ""
    );


$nombre =
    trim(
        $data["nombre"] ?? ""
    );


$email =
    trim(
        $data["email"] ?? ""
    );


$agent =
    trim(
        $data["agent"] ??
        "diseño y desarrollo web"
    );


/* ==========================================================
   AGENTES
========================================================== */

$agentPrompts = [

    "diseño y desarrollo web" =>

        "Eres Alejandro Herradón, especialista en diseño y desarrollo web.

        Tu función es asesorar al usuario sobre creación, diseño y desarrollo de páginas web profesionales.

        Puedes ayudar sobre estructura web, diseño, experiencia de usuario, funcionalidades, tecnologías, programación, responsive design, mantenimiento y optimización.

        Responde siempre en español.

        Haz preguntas cuando necesites información adicional.

        Explica las cosas de forma clara y sencilla.

        No prometas resultados garantizados de ventas, clientes, conversiones o posicionamiento.",


    "tiendas online" =>

        "Eres Alejandro Herradón, especialista en tiendas online y comercio electrónico.

        Tu función es asesorar al usuario sobre creación, diseño y desarrollo de tiendas online.

        Puedes ayudar sobre productos, categorías, carrito, pagos, pedidos, clientes, plataformas de ecommerce, diseño, experiencia de compra, seguridad y optimización.

        Responde siempre en español.

        Haz preguntas cuando necesites información adicional.

        Explica las alternativas de forma clara y objetiva.

        No garantices ventas, facturación o conversiones.",


    "asesor seo y sem" =>

        "Eres Alejandro Herradón, especialista en SEO y SEM.

        Tu función es ayudar al usuario a mejorar la visibilidad de su página web mediante posicionamiento orgánico y publicidad online.

        Puedes explicar SEO técnico, palabras clave, contenidos, enlaces, experiencia de usuario, Google Ads, SEM, campañas, métricas, tráfico y conversiones.

        Responde siempre en español.

        Haz preguntas cuando necesites información adicional.

        Diferencia claramente SEO de SEM.

        No garantices posiciones concretas en Google, tráfico, clientes o ventas.",


    "asesoramiento web" =>

        "Eres Alejandro Herradón, especialista en asesoramiento web.

        Tu función es analizar las necesidades generales del usuario relacionadas con su presencia online.

        Puedes ayudar a detectar problemas y oportunidades en páginas web, diseño, estructura, contenidos, funcionalidades, experiencia de usuario, SEO, rendimiento y estrategia digital.

        Responde siempre en español.

        Haz preguntas cuando necesites información adicional.

        Explica las diferentes alternativas de forma sencilla.

        No garantices ventas, clientes, conversiones ni posicionamiento."

];


/* ==========================================================
   COMPROBAR AGENTE
========================================================== */

if (!isset($agentPrompts[$agent])) {

    $agent =
        "diseño y desarrollo web";

}


$systemPrompt =
    $agentPrompts[$agent];


/* ==========================================================
   ENVÍO DE CONVERSACIÓN POR EMAIL
========================================================== */

if ($action === "email") {

    $conversacion =
        trim(
            $data["conversacion"] ?? ""
        );


    if ($conversacion === "") {

        echo json_encode([
            "success" => false,
            "error" => "No hay ninguna conversación para enviar."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if ($nombre === "") {

        echo json_encode([
            "success" => false,
            "error" => "El nombre es obligatorio."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if (
        $email === "" ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        echo json_encode([
            "success" => false,
            "error" => "El correo electrónico no es válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /* ======================================================
       NOMBRE DEL AGENTE
    ====================================================== */

    $nombresAgentes = [

        "diseño y desarrollo web" =>
            "Diseño y Desarrollo Web",

        "tiendas online" =>
            "Tiendas Online",

        "asesor seo y sem" =>
            "SEO y SEM",

        "asesoramiento web" =>
            "Asesoramiento Web"

    ];


    $nombreAgente =
        $nombresAgentes[$agent]
        ?? "Diseño y Desarrollo Web";


    /* ======================================================
       ID CONVERSACIÓN
    ====================================================== */

    $conversacionId =
        strtoupper(
            substr(
                bin2hex(
                    random_bytes(6)
                ),
                0,
                8
            )
        );


    /* ======================================================
       MYSQL
    ====================================================== */

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
                $conversacionId,

            ":nombre" =>
                $nombre,

            ":email" =>
                $email,

            ":usuario" =>
                $conversacion,

            ":respuesta" =>
                "CONVERSACIÓN ENVIADA - " .
                $nombreAgente

        ]);


    } catch (PDOException $e) {

        error_log(
            "Error MySQL: " .
            $e->getMessage()
        );

        echo json_encode([
            "success" => false,
            "error" => "No se pudo guardar la conversación."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /* ======================================================
       EMAIL
    ====================================================== */

    $asunto =
        "Nueva conversación - " .
        $nombreAgente .
        " - #" .
        $conversacionId;


    $textoEmail =

        "NUEVO CONTACTO DESDE VIZIUNEAI\n\n" .

        "========================================\n" .

        "DATOS DEL CLIENTE\n" .

        "========================================\n\n" .

        "Nombre: " .
        $nombre .
        "\n\n" .

        "Email: " .
        $email .
        "\n\n" .

        "Especialista seleccionado: " .
        $nombreAgente .
        "\n\n" .

        "ID conversación: " .
        $conversacionId .
        "\n\n" .

        "========================================\n" .

        "CONVERSACIÓN\n" .

        "========================================\n\n" .

        $conversacion;


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


        $mail->CharSet =
            "UTF-8";


        $mail->setFrom(
            $SMTP_FROM,
            "ViziuneAI"
        );


        $mail->addAddress(
            $SMTP_TO,
            "Alejandro Herradón"
        );


        $mail->addReplyTo(
            $email,
            $nombre
        );


        $mail->isHTML(false);

        $mail->Subject =
            $asunto;

        $mail->Body =
            $textoEmail;


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
            "PHPMailer: " .
            $mail->ErrorInfo
        );


        http_response_code(500);


        echo json_encode([

            "success" =>
                false,

            "error" =>
                "No se pudo enviar el correo."

        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

}


/* ==========================================================
   CHAT NORMAL
========================================================== */

if ($message === "") {

    echo json_encode([
        "success" => false,
        "error" => "El mensaje está vacío."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   API KEY
========================================================== */

if (empty($OPENAI_API_KEY)) {

    echo json_encode([
        "success" => false,
        "error" => "La API Key de OpenAI no está configurada."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   OPENAI
========================================================== */

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
    json_encode(
        $payload
    );


/* ==========================================================
   CURL
========================================================== */

$ch =
    curl_init(
        $url
    );


curl_setopt_array(
    $ch,
    [

        CURLOPT_RETURNTRANSFER =>
            true,

        CURLOPT_POST =>
            true,

        CURLOPT_POSTFIELDS =>
            $jsonPayload,

        CURLOPT_HTTPHEADER =>
            [

                "Content-Type: application/json",

                "Authorization: Bearer " .
                $OPENAI_API_KEY

            ],

        CURLOPT_TIMEOUT =>
            60

    ]
);


$response =
    curl_exec(
        $ch
    );


$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$curlError =
    curl_error(
        $ch
    );


curl_close(
    $ch
);


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
   RESPUESTA OPENAI
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
   OBTENER TEXTO
========================================================== */

$answer =
    null;


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


/* ==========================================================
   COMPROBAR RESPUESTA
========================================================== */

if (!$answer) {

    echo json_encode([

        "success" =>
            false,

        "error" =>
            "No se pudo obtener una respuesta de OpenAI."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================================
   GUARDAR CHAT EN MYSQL
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
            null,

        ":email" =>
            null,

        ":usuario" =>
            $message,

        ":respuesta" =>
            $answer

    ]);


} catch (PDOException $e) {

    error_log(
        "Error guardando chat: " .
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