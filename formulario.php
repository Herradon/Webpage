<?php

header("Content-Type: application/json; charset=utf-8");

require_once "config.php";


/* ==============================
   COMPROBAR MÉTODO
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
    Número de WhatsApp.

    España:
    34650171966

    SIN +
    SIN espacios
    SIN guiones
*/

$numeroWhatsApp = "34650171966";


/* ==============================
   CREAR MENSAJE
============================== */

$textoWhatsApp =
    "Hola, soy " . $nombre . ".\n\n" .
    "Teléfono: " . $telefono . "\n" .
    "Email: " . $email . "\n\n" .
    "Mensaje:\n" . $mensaje;


/* ==============================
   CREAR URL DE WHATSAPP
============================== */

$whatsappURL =
    "https://wa.me/" .
    $numeroWhatsApp .
    "?text=" .
    urlencode($textoWhatsApp);


/* ==============================
   RESPUESTA AL JAVASCRIPT
============================== */

echo json_encode([

    "success" => true,

    "whatsapp" => $whatsappURL

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

?>