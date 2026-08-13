<?php

header("Content-Type: application/json; charset=utf-8");

require_once "config.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Método no permitido."
    ]);

    exit;
}


$nombre = trim($_POST["nombre"] ?? "");

$telefono = trim($_POST["telefono"] ?? "");

$email = trim($_POST["email"] ?? "");

$mensaje = trim($_POST["mensaje"] ?? "");


/* ==============================
   VALIDACIONES
============================== */

if (
    $nombre === "" ||
    $telefono === "" ||
    $email === "" ||
    $mensaje === ""
) {

    echo json_encode([
        "success" => false,
        "error" => "Todos los campos son obligatorios."
    ]);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "success" => false,
        "error" => "El email no es válido."
    ]);

    exit;
}


/* ==============================
   GUARDAR EN MYSQL
============================== */

try {

    $stmt = $pdo->prepare("
        INSERT INTO contactos
        (nombre, telefono, email, mensaje)
        VALUES
        (:nombre, :telefono, :email, :mensaje)
    ");


    $stmt->execute([

        ":nombre" => $nombre,

        ":telefono" => $telefono,

        ":email" => $email,

        ":mensaje" => $mensaje

    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "error" => "No se pudo guardar el formulario."
    ]);

    exit;
}


/* ==============================
   WHATSAPP
============================== */

/*
    CAMBIA ESTE NÚMERO.

    Formato internacional.

    España:

    34600123456

    SIN +
    SIN espacios
*/

$numeroWhatsApp = "650171966";


$textoWhatsApp =
    "Hola, soy " . $nombre .
    ".%0A%0A" .

    "Teléfono: " . $telefono .
    "%0A" .

    "Email: " . $email .
    "%0A%0A" .

    "Mensaje:%0A" .
    $mensaje;


$whatsappURL =
    "https://wa.me/" .
    $numeroWhatsApp .
    "?text=" .
    $textoWhatsApp;


/* ==============================
   RESPUESTA
============================== */

echo json_encode([

    "success" => true,

    "whatsapp" => $whatsappURL

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

?>