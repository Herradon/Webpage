<?php

session_start();

header(
    "Content-Type: application/json; charset=utf-8"
);

require_once __DIR__ . "/config.php";


/* ==========================================================
   COMPROBAR SESIÓN
========================================================== */

if (!isset($_SESSION["usuario_id"])) {

    http_response_code(401);

    echo json_encode(
        [
            "success" => false,
            "error" => "Debes iniciar sesión."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


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
   USUARIO ACTUAL
========================================================== */

$usuarioId =
    (int) $_SESSION["usuario_id"];


try {

    /* ======================================================
       OBTENER REUNIONES DEL USUARIO
       
       La relación se hace mediante:
       
       reuniones.conversacion_id
                    ↓
       conversaciones.conversacion_id
                    ↓
       conversaciones.usuario_id
       
       De esta forma NO dependemos del email introducido
       en el formulario de contacto.
    ====================================================== */

    $stmt =
        $pdo->prepare(
            "
            SELECT
                r.id,
                r.conversacion_id,
                r.nombre,
                r.email,
                r.especialista,
                r.fecha,
                r.hora,
                r.duracion

            FROM reuniones AS r

            INNER JOIN conversaciones AS c
                ON c.conversacion_id = r.conversacion_id

            WHERE c.usuario_id = ?

            ORDER BY
                r.fecha ASC,
                r.hora ASC
            "
        );


    $stmt->execute(
        [
            $usuarioId
        ]
    );


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
        "Error calendario-datos.php: " .
        $e->getMessage()
    );


    http_response_code(500);

    echo json_encode(
        [
            "success" => false,
            "error" => "No se pudieron obtener las reuniones."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}