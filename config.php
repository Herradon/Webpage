<?php

/* ==========================================
   BASE DE DATOS HOSTINGER
========================================== */

$host = "localhost";

$dbname = "u671328244_chatbot_web";

$username = "u671328244_viziuneai";

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
        "error" => "ERROR MYSQL REAL: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

/* ==========================================
   KIMI
========================================== */

$KIMI_API_KEY = "sk-zKyp1zO2MwPbuWXEV25oF7TzT8uesbTyqVMfZLtDwWdtfYvA";

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