<?php

session_start();

header(
    "Content-Type: application/json; charset=utf-8"
);

require_once __DIR__ . "/config.php";


/* ==========================================================
   COMPROBAR PETICIÓN
========================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode(
        [
            "success" => false,
            "error" => "Método no permitido."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   OBTENER REUNIONES
========================================================== */

try {

    $stmt =
        $pdo->prepare(
            "
            SELECT
                id,
                conversacion_id,
                nombre,
                email,
                especialista,
                fecha,
                hora,
                duracion

            FROM reuniones

            ORDER BY
                fecha ASC,
                hora ASC
            "
        );


    $stmt->execute();


    $reuniones =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


    /* ======================================================
       RESPUESTA
    ====================================================== */

    echo json_encode(
        [
            "success" => true,
            "reuniones" => $reuniones
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;


} catch (PDOException $e) {

    error_log(
        "Error calendario: " .
        $e->getMessage()
    );


    http_response_code(500);


    echo json_encode(
        [
            "success" => false,
            "error" =>
                "No se pudieron obtener las reuniones."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}