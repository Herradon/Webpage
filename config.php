<?php

/* ==========================================
   CONFIGURACIÓN DE LA BASE DE DATOS
========================================== */

$host = "localhost";
$dbname = "chatbot_web";
$username = "root";
$password = "";


/* ==========================================
   CONEXIÓN CON MYSQL
========================================== */

try {

    $pdo = new PDO(
        "mysql:host=" . $host .
        ";dbname=" . $dbname .
        ";charset=utf8mb4",

        $username,
        $password,

        [
            PDO::ATTR_ERRMODE =>
                PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES =>
                false
        ]
    );

} catch (PDOException $e) {

    /*
        No mostramos el error interno de MySQL
        al usuario por seguridad.

        Guardamos el error en el log de PHP.
    */

    error_log(
        "Error MySQL: " . $e->getMessage()
    );

    /*
        IMPORTANTE:
        No utilizamos die(), porque contacto.php
        necesita devolver JSON.
    */

    http_response_code(500);

    echo json_encode(
        [
            "success" => false,
            "error" =>
                "No se pudo conectar con la base de datos."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;

}


/* ==========================================
   OPENAI
========================================== */

/*
    IMPORTANTE:

    NO pongas aquí la API KEY que has publicado
    anteriormente.

    Revócala y crea una nueva.

    Después sustituye:

    PON_AQUI_TU_NUEVA_API_KEY

    por la nueva clave.
*/

$OPENAI_API_KEY =
    "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";


/* ==========================================
   MODELO DE OPENAI
========================================== */

$OPENAI_MODEL =
    "gpt-5";

?>