<?php

/* ==========================================
   BASE DE DATOS
========================================== */

$host = "localhost";
$dbname = "chatbot_web";

$username = "root";
$password = "";


/* ==========================================
   CONEXIÓN MYSQL
========================================== */

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    error_log("Error MySQL: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "No se pudo conectar con la base de datos."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* ==========================================
   OPENAI
========================================== */

$OPENAI_API_KEY = "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";

$OPENAI_MODEL = "gpt-5";


/* ==========================================
   GMAIL SMTP
========================================== */

$SMTP_HOST = "smtp.gmail.com";

$SMTP_PORT = 587;

$SMTP_USERNAME = "herradon45@gmail.com";

/*
   IMPORTANTE:
   NO pongas aquí tu contraseña normal de Gmail.

   Debes poner la CONTRASEÑA DE APLICACIÓN
   generada por Google.
*/

$SMTP_PASSWORD = "ijepvhhoyvoskdyq";


/* ==========================================
   REMITENTE
========================================== */

$SMTP_FROM = "herradon45@gmail.com";


/* ==========================================
   DESTINATARIO
========================================== */

$SMTP_TO = "herradon45@gmail.com";

?>