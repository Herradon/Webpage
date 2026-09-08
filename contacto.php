<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";


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

    echo json_encode([
        "success" => false,
        "error" => "Todos los campos son obligatorios."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   VALIDAR EMAIL
========================================== */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "success" => false,
        "error" => "El email no es válido."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   GUARDAR EN MYSQL
========================================== */

try {

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
            "Empresa: " .
            $empresa .
            "\n\n" .
            $mensaje

    ]);

} catch (PDOException $e) {

    error_log(
        "Error guardando contacto: " .
        $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "error" =>
            "No se pudieron guardar los datos en la base de datos."

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   NÚMERO DE WHATSAPP
========================================== */

/*
   PON AQUÍ EL NÚMERO DE WHATSAPP
   DE TU EMPRESA.

   IMPORTANTE:
   - Sin +
   - Sin espacios
   - Sin guiones

   Ejemplo España:

   34600123456
*/

$numeroWhatsApp =
    "34689976427";


/* ==========================================
   CREAR MENSAJE PARA WHATSAPP
========================================== */

$mensajeWhatsApp =
    "Hola, soy " .
    $nombre .
    ".%0A%0A" .

    "Empresa: " .
    $empresa .
    "%0A" .

    "Email: " .
    $email .
    "%0A%0A" .

    "Consulta:%0A" .
    $mensaje;


/* ==========================================
   CREAR URL DE WHATSAPP
========================================== */

$urlWhatsApp =
    "https://wa.me/" .
    $numeroWhatsApp .
    "?text=" .
    $mensajeWhatsApp;


/* ==========================================
   RESPUESTA CORRECTA
========================================== */

echo json_encode([

    "success" => true,

    "message" =>
        "Formulario guardado correctamente.",

    "whatsapp" =>
        $urlWhatsApp

], JSON_UNESCAPED_UNICODE);

exit;

?>