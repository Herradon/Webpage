<?php

/* ==============================
   BASE DE DATOS
============================== */

$host = "localhost";
$dbname = "chatbot_web";
$username = "root";
$password = "";


/* ==============================
   CONEXIÓN MYSQL
============================== */

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {

    die("Error de conexión con MySQL.");

}


/* ==============================
   OPENAI
============================== */

/*
    PON AQUÍ TU API KEY DE OPENAI.

    Ejemplo:

    sk-proj-xxxxxxxxxxxxxxxx
*/

$OPENAI_API_KEY = "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";


/*
    Modelo utilizado.
*/

$OPENAI_MODEL = "gpt-5";