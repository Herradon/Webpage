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
    ]);

    exit;
}


/* ==========================================
   NÚMERO DE WHATSAPP
========================================== */

/*
    España:

    34650171966

    SIN +
    SIN espacios
    SIN guiones
*/

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


    /* ==============================
       CREAR MENSAJE WHATSAPP
    ============================== */

    $textoWhatsApp =
        "Hola Alejandro.\n\n" .
        "Un cliente quiere contactar contigo " .
        "desde el asistente virtual.\n\n" .
        "==============================\n" .
        "CONVERSACIÓN DEL CHAT\n" .
        "==============================\n\n" .
        $conversacion;


    /* ==============================
       CREAR URL WHATSAPP
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


    exit;
}


/* ==========================================
   FORMULARIO DE CONTACTO
========================================== */

$nombre =
    trim($_POST["nombre"] ?? "");


/*
    En tu HTML tienes actualmente:

    name="Empresa"

    Por eso recibimos ese campo aquí.
*/

$empresa =
    trim($_POST["Empresa"] ?? "");


$email =
    trim($_POST["email"] ?? "");


$mensaje =
    trim($_POST["mensaje"] ?? "");


/*
    Tu formulario HTML actual NO tiene
    teléfono.

    Por eso no lo hacemos obligatorio.
*/

$telefono =
    trim($_POST["telefono"] ?? "");


/* ==========================================
   VALIDACIONES
========================================== */

if (
    $nombre === "" ||
    $empresa === "" ||
    $email === "" ||
    $mensaje === ""
) {

    echo json_encode([

        "success" => false,

        "error" =>
            "Todos los campos son obligatorios."

    ]);

    exit;
}


/* ==========================================
   VALIDAR EMAIL
========================================== */

if (!filter_var(
    $email,
    FILTER_VALIDATE_EMAIL
)) {

    echo json_encode([

        "success" => false,

        "error" =>
            "El email no es válido."

    ]);

    exit;
}


/* ==========================================
   GUARDAR EN MYSQL
========================================== */

try {


    $stmt =
        $pdo->prepare("

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


    /*
        Guardamos la empresa junto
        con el mensaje.

        Así no necesitamos cambiar
        todavía la estructura de tu
        tabla MySQL.
    */

    $mensajeCompleto =
        "Empresa: " .
        $empresa .
        "\n\n" .
        $mensaje;


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


    echo json_encode([

        "success" => false,

        "error" =>
            "No se pudo guardar el formulario."

    ]);

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


/*
    Añadir teléfono solamente
    si el usuario lo ha proporcionado.
*/

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

    urlencode(
        $textoWhatsApp
    );


/* ==========================================
   RESPUESTA AL JAVASCRIPT
========================================== */

echo json_encode([

    "success" => true,

    "whatsapp" => $whatsappURL

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

?>