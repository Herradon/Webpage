<?php

/* ==========================================
   BASE DE DATOS
========================================== */

$host = "localhost";
$dbname = "u445133904_chatbot_web";

$username = "u445133904_viziuneai";
$password = "Viziune_2026"; 


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
   KIMI
========================================== */

$KIMI_API_KEY = "sk-7qC4snxp2G9bC07wAkOB7Iaqkwx9GdqSnTFbemyFYdnkzBdN";

$KIMI_MODEL = "kimi-k2.5";

$KIMI_API_URL = "https://api.moonshot.ai/v1/chat/completions";


/* ==========================================
   GMAIL SMTP
========================================== */

$SMTP_HOST = "smtp.gmail.com";

$SMTP_PORT = 587;

$SMTP_USERNAME = "viziune.ainteligent@gmail.com";

$SMTP_PASSWORD = "pcnmcpffaaxsijit";


/* ==========================================
   REMITENTE
========================================== */

$SMTP_FROM = "viziune.ainteligent@gmail.com";


/* ==========================================
   DESTINATARIO
========================================== */

$SMTP_TO = "viziune.ainteligent@gmail.com";

?>