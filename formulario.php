<?php

header("Content-Type: application/json; charset=utf-8");

require_once "config.php";


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
   NÚMERO DE WHATSAPP
========================================== */

$numeroWhatsApp = "34650171966";


/* ==========================================
   COMPROBAR SI VIENE DEL CHATBOT
========================================== */

$conversacion =
    trim($_POST["conversacion"] ?? "");


/* ==========================================
   SI VIENE DEL CHATBOT
========================================== */

if ($conversacion !== "") {

    $textoWhatsApp =
        "Hola Alejandro.\n\n" .
        "Un cliente quiere contactar contigo " .
        "desde el asistente virtual.\n\n" .
        "==============================\n" .
        "CONVERSACIÓN DEL CHAT\n" .
        "==============================\n\n" .
        $conversacion;


    $whatsappURL =
        "https://wa.me/" .
        $numeroWhatsApp .
        "?text=" .
        urlencode($textoWhatsApp);


    echo json_encode([

        "success" => true,

        "whatsapp" => $whatsappURL

    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   RECIBIR FORMULARIO
========================================== */

$nombre =
    trim($_POST["nombre"] ?? "");


$empresa =
    trim($_POST["Empresa"] ?? "");


$email =
    trim($_POST["email"] ?? "");


$mensaje =
    trim($_POST["mensaje"] ?? "");


$telefono =
    trim($_POST["telefono"] ?? "");


/* ==========================================
   VALIDAR CAMPOS
========================================== */

if (
    $nombre === "" ||
    $empresa === "" ||
    $email === "" ||
    $mensaje === ""
) {

    http_response_code(400);

    echo json_encode([

        "success" => false,

        "error" =>
            "Todos los campos obligatorios deben estar completos."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   VALIDAR EMAIL
========================================== */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([

        "success" => false,

        "error" =>
            "El correo electrónico no es válido."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   GUARDAR EN MYSQL
========================================== */

try {

    $mensajeCompleto =
        "Empresa: " .
        $empresa .
        "\n\n" .
        $mensaje;


    $stmt = $pdo->prepare("

        INSERT INTO contactos
        (
            nombre,
            telefono,
            email,
            mensaje
        )

        VALUES
        (
            :nombre,
            :telefono,
            :email,
            :mensaje
        )

    ");


    $stmt->execute([

        ":nombre" =>
            $nombre,

        ":telefono" =>
            $telefono,

        ":email" =>
            $email,

        ":mensaje" =>
            $mensajeCompleto

    ]);


} catch (PDOException $e) {

    error_log(
        "Error al guardar contacto: " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "error" =>
            "El formulario no se pudo guardar en la base de datos."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   CREAR MENSAJE WHATSAPP
========================================== */

$textoWhatsApp =

    "Hola Alejandro.\n\n" .

    "Nuevo contacto desde la página web.\n\n" .

    "==============================\n" .

    "DATOS DEL CLIENTE\n" .

    "==============================\n\n" .

    "Nombre: " .
    $nombre .
    "\n" .

    "Empresa: " .
    $empresa .
    "\n" .

    "Email: " .
    $email .
    "\n";


if ($telefono !== "") {

    $textoWhatsApp .=

        "Teléfono: " .
        $telefono .
        "\n";
}


$textoWhatsApp .=

    "\n" .

    "==============================\n" .

    "MENSAJE\n" .

    "==============================\n\n" .

    $mensaje;


/* ==========================================
   CREAR URL WHATSAPP
========================================== */

$whatsappURL =

    "https://wa.me/" .
    $numeroWhatsApp .
    "?text=" .
    urlencode($textoWhatsApp);


/* ==========================================
   RESPUESTA FINAL
========================================== */

echo json_encode([

    "success" => true,

    "message" =>
        "Contacto guardado correctamente.",

    "whatsapp" =>
        $whatsappURL

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

exit;

?>