<?php

session_start();

header(
    "Content-Type: application/json; charset=utf-8"
);

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* ==========================================================
   COMPROBAR PETICIÓN
========================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

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
   COMPROBAR ACTION
========================================================== */

$action =
    trim(
        $_POST["action"] ?? ""
    );


/* ==========================================================
   ENVÍO DE CONVERSACIÓN POR EMAIL
========================================================== */

if ($action === "email") {


    /* ======================================================
       DATOS
    ====================================================== */

    $conversacion =
        trim(
            $_POST["conversacion"] ?? ""
        );


    $nombre =
        trim(
            $_POST["nombre"] ?? ""
        );


    $email =
        trim(
            $_POST["email"] ?? ""
        );


    $agent =
        trim(
            $_POST["agent"] ??
            "diseño y desarrollo web"
        );


    /* ======================================================
       REUNIÓN

       Estos nombres coinciden con app.js:

       fecha_reunion
       hora_reunion
    ====================================================== */

    $quiereCita =
        trim(
            $_POST["quiereCita"] ?? "0"
        );


    $fechaReunion =
        trim(
            $_POST["fecha_reunion"] ?? ""
        );


    $horaReunion =
        trim(
            $_POST["hora_reunion"] ?? ""
        );


    /* ======================================================
       COMPROBAR CONVERSACIÓN
    ====================================================== */

    if ($conversacion === "") {

        echo json_encode(
            [
                "success" => false,
                "error" =>
                    "No hay ninguna conversación para enviar."
            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /* ======================================================
       COMPROBAR NOMBRE
    ====================================================== */

    if ($nombre === "") {

        echo json_encode(
            [
                "success" => false,
                "error" =>
                    "El nombre es obligatorio."
            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /* ======================================================
       COMPROBAR EMAIL
    ====================================================== */

    if (
        $email === "" ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        echo json_encode(
            [
                "success" => false,
                "error" =>
                    "El correo electrónico no es válido."
            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /* ======================================================
       DETERMINAR SI HAY REUNIÓN
    ====================================================== */

    $reunionSolicitada =
        ($quiereCita === "1");


    /* ======================================================
       VALIDAR FECHA Y HORA
    ====================================================== */

    $fechaValida = null;


    if ($reunionSolicitada) {


        if (
            $fechaReunion === "" ||
            $horaReunion === ""
        ) {

            echo json_encode(
                [
                    "success" => false,
                    "error" =>
                        "Para solicitar una reunión debes seleccionar una fecha y una hora."
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        /* ================================================
           FECHA
        ================================================= */

        $fechaValida =
            DateTime::createFromFormat(
                "Y-m-d",
                $fechaReunion
            );


        if (
            !$fechaValida ||
            $fechaValida->format("Y-m-d") !==
                $fechaReunion
        ) {

            echo json_encode(
                [
                    "success" => false,
                    "error" =>
                        "La fecha seleccionada no es válida."
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }


        /* ================================================
           HORA
        ================================================= */

        $horaValida =
            DateTime::createFromFormat(
                "H:i",
                $horaReunion
            );


        if (
            !$horaValida ||
            $horaValida->format("H:i") !==
                $horaReunion
        ) {

            echo json_encode(
                [
                    "success" => false,
                    "error" =>
                        "La hora seleccionada no es válida."
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }

    }


    /* ======================================================
       AGENTES
    ====================================================== */

    $nombresAgentes = [

        "diseño y desarrollo web" =>
            "Diseño y Desarrollo Web",

        "tiendas online" =>
            "Tiendas Online",

        "asesor seo y sem" =>
            "SEO y SEM",

        "asesoramiento web" =>
            "Asesoramiento Web"

    ];


    $nombreAgente =
        $nombresAgentes[$agent]
        ?? "Diseño y Desarrollo Web";


    /* ======================================================
       ID CONVERSACIÓN
    ====================================================== */

    $conversacionId =
        strtoupper(
            substr(
                bin2hex(
                    random_bytes(6)
                ),
                0,
                8
            )
        );


    /* ======================================================
       INFORMACIÓN REUNIÓN
    ====================================================== */

    $informacionReunion = "";


    if ($reunionSolicitada) {

        $fechaFormateada =
            $fechaValida->format(
                "d/m/Y"
            );


        $informacionReunion =

            "\n\n" .

            "========================================\n" .

            "REUNIÓN SOLICITADA\n" .

            "========================================\n\n" .

            "Fecha de la reunión: " .
            $fechaFormateada .
            "\n\n" .

            "Hora de la reunión: " .
            $horaReunion .
            "\n\n" .

            "Duración: 60 minutos\n\n";

    }


    /* ======================================================
       MYSQL
    ====================================================== */

    try {

        $stmt =
            $pdo->prepare(
                "
                INSERT INTO conversaciones
                (
                    conversacion_id,
                    nombre,
                    email,
                    usuario,
                    respuesta
                )

                VALUES
                (
                    :conversacion_id,
                    :nombre,
                    :email,
                    :usuario,
                    :respuesta
                )
                "
            );


        $respuestaBase =
            "CONVERSACIÓN ENVIADA - " .
            $nombreAgente;


        if ($reunionSolicitada) {

            $respuestaBase .=

                " | REUNIÓN: " .
                $fechaReunion .
                " " .
                $horaReunion;

        }


        $stmt->execute(
            [

                ":conversacion_id" =>
                    $conversacionId,

                ":nombre" =>
                    $nombre,

                ":email" =>
                    $email,

                ":usuario" =>
                    $conversacion,

                ":respuesta" =>
                    $respuestaBase

            ]
        );


    } catch (PDOException $e) {

        error_log(
            "Error MySQL: " .
            $e->getMessage()
        );


        echo json_encode(
            [
                "success" => false,
                "error" =>
                    "No se pudo guardar la conversación."
            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /* ======================================================
       GUARDAR REUNIÓN EN MYSQL

       IMPORTANTE:
       Este bloque pertenece únicamente al flujo EMAIL,
       porque aquí existen las variables de la reunión.
    ====================================================== */

    if ($reunionSolicitada) {

        try {

            $stmtReunion =
                $pdo->prepare(
                    "
                    INSERT INTO reuniones
                    (
                        conversacion_id,
                        nombre,
                        email,
                        especialista,
                        fecha,
                        hora,
                        duracion
                    )

                    VALUES
                    (
                        :conversacion_id,
                        :nombre,
                        :email,
                        :especialista,
                        :fecha,
                        :hora,
                        :duracion
                    )
                    "
                );


            $stmtReunion->execute(
                [

                    ":conversacion_id" =>
                        $conversacionId,

                    ":nombre" =>
                        $nombre,

                    ":email" =>
                        $email,

                    ":especialista" =>
                        $nombreAgente,

                    ":fecha" =>
                        $fechaReunion,

                    ":hora" =>
                        $horaReunion,

                    ":duracion" =>
                        60

                ]
            );


        } catch (PDOException $e) {

            error_log(
                "Error guardando reunión: " .
                $e->getMessage()
            );


            echo json_encode(
                [
                    "success" => false,
                    "error" =>
                        "No se pudo guardar la reunión."
                ],
                JSON_UNESCAPED_UNICODE
            );

            exit;
        }

    }


    /* ======================================================
       PREPARAR EMAIL
    ====================================================== */

    $asunto =
        "Nueva conversación - " .
        $nombreAgente .
        " - #" .
        $conversacionId;


    $textoEmail =

        "NUEVO CONTACTO DESDE VIZIUNEAI\n\n" .

        "========================================\n" .

        "DATOS DEL CLIENTE\n" .

        "========================================\n\n" .

        "Nombre: " .
        $nombre .
        "\n\n" .

        "Email: " .
        $email .
        "\n\n" .

        "Especialista seleccionado: " .
        $nombreAgente .
        "\n\n" .

        "ID conversación: " .
        $conversacionId .
        "\n\n" .

        $informacionReunion .

        "========================================\n" .

        "CONVERSACIÓN\n" .

        "========================================\n\n" .

        $conversacion;


    /* ======================================================
       CREAR PHPMailer
    ====================================================== */

    $mail =
        new PHPMailer(true);


    try {


        /* ==================================================
           SMTP
        ================================================== */

        $mail->isSMTP();


        $mail->Host =
            $SMTP_HOST;


        $mail->SMTPAuth =
            true;


        $mail->Username =
            $SMTP_USERNAME;


        $mail->Password =
            $SMTP_PASSWORD;


        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;


        $mail->Port =
            $SMTP_PORT;


        $mail->CharSet =
            "UTF-8";


        /* ==================================================
           REMITENTE
        ================================================== */

        $mail->setFrom(
            $SMTP_FROM,
            "ViziuneAI"
        );


        /* ==================================================
           DESTINATARIO
        ================================================== */

        $mail->addAddress(
            $SMTP_TO,
            "Alejandro Herradón"
        );


        /* ==================================================
           RESPONDER AL CLIENTE
        ================================================== */

        $mail->addReplyTo(
            $email,
            $nombre
        );


        /* ==================================================
           CONTENIDO
        ================================================== */

        $mail->isHTML(false);


        $mail->Subject =
            $asunto;


        $mail->Body =
            $textoEmail;


        /* ==================================================
           ARCHIVO ADJUNTO
        ================================================== */

        if (
            isset($_FILES["chatFile"]) &&
            $_FILES["chatFile"]["error"] !==
                UPLOAD_ERR_NO_FILE
        ) {


            /* ==============================================
               ERROR DE SUBIDA
            ============================================== */

            if (
                $_FILES["chatFile"]["error"] !==
                    UPLOAD_ERR_OK
            ) {

                throw new Exception(
                    "Se produjo un error al subir el archivo."
                );

            }


            /* ==============================================
               DATOS
            ============================================== */

            $archivoTmp =
                $_FILES["chatFile"]["tmp_name"];


            $nombreArchivo =
                $_FILES["chatFile"]["name"];


            $tamanoArchivo =
                $_FILES["chatFile"]["size"];


            /* ==============================================
               COMPROBAR SUBIDA
            ============================================== */

            if (
                !is_uploaded_file(
                    $archivoTmp
                )
            ) {

                throw new Exception(
                    "El archivo recibido no es válido."
                );

            }


            /* ==============================================
               LÍMITE 10 MB
            ============================================== */

            if (
                $tamanoArchivo >
                10 * 1024 * 1024
            ) {

                throw new Exception(
                    "El archivo no puede superar los 10 MB."
                );

            }


            /* ==============================================
               DETECTAR MIME REAL
            ============================================== */

            $finfo =
                finfo_open(
                    FILEINFO_MIME_TYPE
                );


            if (!$finfo) {

                throw new Exception(
                    "No se pudo comprobar el tipo de archivo."
                );

            }


            $mime =
                finfo_file(
                    $finfo,
                    $archivoTmp
                );


            /* ==============================================
               TIPOS PERMITIDOS
            ============================================== */

            $tiposPermitidos = [

                "application/pdf",

                "image/jpeg",

                "image/png",

                "image/gif",

                "image/webp"

            ];


            if (
                !in_array(
                    $mime,
                    $tiposPermitidos,
                    true
                )
            ) {

                throw new Exception(
                    "El tipo de archivo no está permitido. Solo se permiten imágenes y PDF."
                );

            }


            /* ==============================================
               LIMPIAR NOMBRE
            ============================================== */

            $nombreArchivo =
                basename(
                    $nombreArchivo
                );


            /* ==============================================
               ADJUNTAR
            ============================================== */

            $mail->addAttachment(
                $archivoTmp,
                $nombreArchivo
            );

        }


        /* ==================================================
           ENVIAR
        ================================================== */

        $mail->send();


        /* ==================================================
           RESPUESTA CORRECTA
        ================================================== */

        echo json_encode(
            [

                "success" =>
                    true,

                "message" =>
                    "La conversación se ha enviado correctamente.",

                "conversacion_id" =>
                    $conversacionId,

                "reunion" =>
                    $reunionSolicitada,

                "fecha_reunion" =>
                    $fechaReunion,

                "hora_reunion" =>
                    $horaReunion

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;


    } catch (Exception $e) {

        error_log(
            "PHPMailer: " .
            $mail->ErrorInfo .
            " | " .
            $e->getMessage()
        );


        http_response_code(500);


        echo json_encode(
            [

                "success" =>
                    false,

                "error" =>
                    $e->getMessage()

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;

    }

}


/* ==========================================================
   CHAT NORMAL
   KIMI / MOONSHOT

   ESTA PARTE RECIBE JSON
========================================================== */


/* ==========================================================
   RECIBIR JSON
========================================================== */

$rawData =
    file_get_contents(
        "php://input"
    );


$data =
    json_decode(
        $rawData,
        true
    );


if (!is_array($data)) {

    echo json_encode(
        [

            "success" =>
                false,

            "error" =>
                "Los datos recibidos no son válidos."

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   DATOS
========================================================== */

$action =
    trim(
        $data["action"] ?? ""
    );


$message =
    trim(
        $data["message"] ?? ""
    );


$nombre =
    trim(
        $data["nombre"] ?? ""
    );


$email =
    trim(
        $data["email"] ?? ""
    );


$agent =
    trim(
        $data["agent"] ??
        "diseño y desarrollo web"
    );


/* ==========================================================
   AGENTES
========================================================== */

$agentPrompts = [

    "diseño y desarrollo web" =>

        "Eres Alejandro Herradón, especialista en diseño y desarrollo web.

        Tu función es asesorar al usuario sobre creación, diseño y desarrollo de páginas web profesionales.

        Puedes ayudar sobre estructura web, diseño, experiencia de usuario, funcionalidades, tecnologías, programación, responsive design, mantenimiento y optimización.

        Responde siempre en español.

        Haz preguntas cuando necesites información adicional.

        Explica las cosas de forma clara y sencilla.

        No prometas resultados garantizados de ventas, clientes, conversiones o posicionamiento.",


    "tiendas online" =>

        "Eres Alejandro Herradón, especialista en tiendas online y comercio electrónico.

        Tu función es asesorar al usuario sobre creación, diseño y desarrollo de tiendas online.

        Puedes ayudar sobre productos, categorías, carrito, pagos, pedidos, clientes, plataformas de ecommerce, diseño, experiencia de compra, seguridad y optimización.

        Responde siempre en español.

        Haz preguntas cuando necesites información adicional.

        Explica las alternativas de forma clara y objetiva.

        No garantices ventas, facturación o conversiones.",


    "asesor seo y sem" =>

        "Eres Alejandro Herradón, especialista en SEO y SEM.

        Tu función es ayudar al usuario a mejorar la visibilidad de su página web mediante posicionamiento orgánico y publicidad online.

        Puedes explicar SEO técnico, palabras clave, contenidos, enlaces, experiencia de usuario, Google Ads, SEM, campañas, métricas, tráfico y conversiones.

        Responde siempre en español.

        Haz preguntas cuando necesites información adicional.

        Diferencia claramente SEO de SEM.

        No garantices posiciones concretas en Google, tráfico, clientes o ventas.",


    "asesoramiento web" =>

        "Eres Alejandro Herradón, especialista en asesoramiento web.

        Tu función es analizar las necesidades generales del usuario relacionadas con su presencia online.

        Puedes ayudar a detectar problemas y oportunidades en páginas web, diseño, estructura, contenidos, funcionalidades, experiencia de usuario, SEO, rendimiento y estrategia digital.

        Responde siempre en español.

        Haz preguntas cuando necesites información adicional.

        Explica las diferentes alternativas de forma sencilla.

        No garantices ventas, clientes, conversiones ni posicionamiento."

];


/* ==========================================================
   COMPROBAR AGENTE
========================================================== */

if (
    !isset(
        $agentPrompts[$agent]
    )
) {

    $agent =
        "diseño y desarrollo web";

}


$systemPrompt =
    $agentPrompts[$agent];


/* ==========================================================
   COMPROBAR MENSAJE
========================================================== */

if ($message === "") {

    echo json_encode(
        [

            "success" =>
                false,

            "error" =>
                "El mensaje está vacío."

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   COMPROBAR API KEY KIMI
========================================================== */

if (
    empty(
        $KIMI_API_KEY
    )
) {

    echo json_encode(
        [

            "success" =>
                false,

            "error" =>
                "La API Key de Kimi no está configurada."

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   URL KIMI
========================================================== */

$url =
    $KIMI_API_URL;


/* ==========================================================
   PAYLOAD KIMI
========================================================== */

$payload = [

    "model" =>
        $KIMI_MODEL,

    "messages" => [

        [

            "role" =>
                "system",

            "content" =>
                $systemPrompt

        ],

        [

            "role" =>
                "user",

            "content" =>
                $message

        ]

    ],

    "temperature" =>
        0.7

];


$jsonPayload =
    json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
    );


/* ==========================================================
   COMPROBAR JSON PAYLOAD
========================================================== */

if ($jsonPayload === false) {

    echo json_encode(
        [

            "success" =>
                false,

            "error" =>
                "No se pudo preparar la petición para Kimi."

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   CURL KIMI
========================================================== */

$ch =
    curl_init(
        $url
    );


curl_setopt_array(
    $ch,
    [

        CURLOPT_RETURNTRANSFER =>
            true,

        CURLOPT_POST =>
            true,

        CURLOPT_POSTFIELDS =>
            $jsonPayload,

        CURLOPT_HTTPHEADER =>
            [

                "Content-Type: application/json",

                "Authorization: Bearer " .
                $KIMI_API_KEY

            ],

        CURLOPT_TIMEOUT =>
            60

    ]
);


$response =
    curl_exec(
        $ch
    );


$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$curlError =
    curl_error(
        $ch
    );

/* ==========================================================
   ERROR CURL
========================================================== */

if (
    $response === false
) {

    echo json_encode(
        [

            "success" =>
                false,

            "error" =>
                "Error conectando con Kimi: " .
                $curlError

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   DECODIFICAR RESPUESTA
========================================================== */

$result =
    json_decode(
        $response,
        true
    );


/* ==========================================================
   ERROR RESPUESTA JSON
========================================================== */

if (!is_array($result)) {

    echo json_encode(
        [

            "success" =>
                false,

            "error" =>
                "Kimi devolvió una respuesta no válida."

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   ERROR KIMI
========================================================== */

if (
    $httpCode >= 400
) {

    $errorMessage =
        $result["error"]["message"]
        ??
        "Error desconocido de Kimi.";


    echo json_encode(
        [

            "success" =>
                false,

            "error" =>
                "Kimi ha devuelto un error: " .
                $errorMessage

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   OBTENER RESPUESTA KIMI
========================================================== */

$answer =
    $result["choices"][0]["message"]["content"]
    ??
    null;


/* ==========================================================
   COMPROBAR RESPUESTA
========================================================== */

if (
    !is_string($answer) ||
    trim($answer) === ""
) {

    echo json_encode(
        [

            "success" =>
                false,

            "error" =>
                "Kimi no devolvió ninguna respuesta."

        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   GUARDAR CHAT EN MYSQL
========================================================== */

try {

    $stmt =
        $pdo->prepare(
            "
            INSERT INTO conversaciones
            (
                conversacion_id,
                nombre,
                email,
                usuario,
                respuesta
            )

            VALUES
            (
                :conversacion_id,
                :nombre,
                :email,
                :usuario,
                :respuesta
            )
            "
        );


    $stmt->execute(
        [

            ":conversacion_id" =>
                "CHAT-" .
                session_id(),

            ":nombre" =>
                null,

            ":email" =>
                null,

            ":usuario" =>
                $message,

            ":respuesta" =>
                $answer

        ]
    );


} catch (PDOException $e) {

    error_log(
        "Error guardando chat: " .
        $e->getMessage()
    );

}


/* ==========================================================
   RESPUESTA JAVASCRIPT
========================================================== */

echo json_encode(
    [

        "success" =>
            true,

        "answer" =>
            $answer,

        "agent" =>
            $agent

    ],
    JSON_UNESCAPED_UNICODE
);

?>