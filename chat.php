<?php

header("Content-Type: application/json; charset=utf-8");

require_once "config.php";


/* ==============================
   COMPROBAR PETICIÓN
============================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Método no permitido."
    ]);

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
   COMPROBAR ACCIÓN WHATSAPP
============================== */

$action = $data["action"] ?? "";


if ($action === "whatsapp") {

    $conversacion =
        trim($data["conversacion"] ?? "");


    if ($conversacion === "") {

        echo json_encode([
            "success" => false,
            "error" => "No hay ninguna conversación para enviar."
        ]);

        exit;
    }


    /* ==============================
       NÚMERO DE WHATSAPP
    ============================== */

    $numeroWhatsApp = "34650171966";


    /* ==============================
       CREAR MENSAJE
    ============================== */

    $textoWhatsApp =
        "Hola Alejandro.\n\n" .

        "Un cliente quiere contactar contigo " .
        "desde el asistente web.\n\n" .

        "CONVERSACIÓN:\n\n" .

        $conversacion;


    /* ==============================
       CREAR URL
    ============================== */

    $whatsappURL =
        "https://wa.me/" .
        $numeroWhatsApp .
        "?text=" .
        urlencode($textoWhatsApp);


    echo json_encode([

        "success" => true,

        "whatsapp" => $whatsappURL

    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


    exit;
}


/* ==============================
   RECIBIR MENSAJE NORMAL
============================== */

$message = trim(
    $data["message"] ?? ""
);


if ($message === "") {

    echo json_encode([
        "success" => false,
        "error" => "El mensaje está vacío."
    ]);

    exit;
}


/* ==============================
   VALIDAR API KEY
============================== */

if (empty($OPENAI_API_KEY)) {

    echo json_encode([
        "success" => false,
        "error" => "La API Key de OpenAI no está configurada."
    ]);

    exit;
}


/* ==============================
   PREPARAR PETICIÓN
============================== */

$url =
    "https://api.openai.com/v1/responses";


$payload = [

    "model" => $OPENAI_MODEL,

    "instructions" =>

        "Eres Alejandro Herradón, el asistente virtual " .
        "de una inmobiliaria. " .

        "Responde siempre en español, de forma clara, " .
        "amable y profesional. " .

        "Si no conoces una información, indica que no " .
        "tienes esa información.",

    "input" => $message

];


$jsonPayload =
    json_encode($payload);


/* ==============================
   CURL
============================== */

$ch =
    curl_init($url);


curl_setopt_array($ch, [

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS => $jsonPayload,

    CURLOPT_HTTPHEADER => [

        "Content-Type: application/json",

        "Authorization: Bearer " .
        $OPENAI_API_KEY

    ],

    CURLOPT_TIMEOUT => 60

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
    ]);

    exit;
}


/* ==============================
   DECODIFICAR RESPUESTA
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

        "error" => $errorMessage

    ]);

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
    ]);

    exit;
}


/* ==============================
   GUARDAR CONVERSACIÓN
============================== */

try {

    $stmt = $pdo->prepare("
        INSERT INTO conversaciones
        (usuario, respuesta)
        VALUES
        (:usuario, :respuesta)
    ");


    $stmt->execute([

        ":usuario" => $message,

        ":respuesta" => $answer

    ]);

} catch (PDOException $e) {

    // No detenemos el chatbot
    // si falla el registro.

}


/* ==============================
   RESPUESTA
============================== */

echo json_encode([

    "success" => true,

    "answer" => $answer

], JSON_UNESCAPED_UNICODE);

?>