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
            "error" => "Debes iniciar sesión para utilizar esta herramienta."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   SOLO POST
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
   RECIBIR DATOS
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
            "success" => false,
            "error" => "Los datos recibidos no son válidos."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   URL
========================================================== */

$url =
    trim(
        $data["url"] ?? ""
    );


if ($url === "") {

    echo json_encode(
        [
            "success" => false,
            "error" => "Debes introducir una URL."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   AÑADIR HTTPS SI NO EXISTE
========================================================== */

if (
    !preg_match(
        "#^https?://#i",
        $url
    )
) {

    $url =
        "https://" .
        $url;

}


/* ==========================================================
   VALIDAR URL
========================================================== */

if (
    !filter_var(
        $url,
        FILTER_VALIDATE_URL
    )
) {

    echo json_encode(
        [
            "success" => false,
            "error" => "La URL introducida no es válida."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   COMPROBAR ESQUEMA
========================================================== */

$urlInfo =
    parse_url(
        $url
    );


$scheme =
    strtolower(
        $urlInfo["scheme"] ?? ""
    );


if (
    $scheme !== "http" &&
    $scheme !== "https"
) {

    echo json_encode(
        [
            "success" => false,
            "error" => "La URL debe comenzar por http:// o https://."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   DESCARGAR PÁGINA
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

        CURLOPT_FOLLOWLOCATION =>
            true,

        CURLOPT_MAXREDIRS =>
            5,

        CURLOPT_CONNECTTIMEOUT =>
            10,

        CURLOPT_TIMEOUT =>
            20,

        CURLOPT_USERAGENT =>
            "ViziuneAI SEO Auditor/1.0",

        CURLOPT_HTTPHEADER =>
            [
                "Accept: text/html,application/xhtml+xml"
            ],

        CURLOPT_SSL_VERIFYPEER =>
            true,

        CURLOPT_SSL_VERIFYHOST =>
            2

    ]
);


$html =
    curl_exec(
        $ch
    );


$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$contentType =
    curl_getinfo(
        $ch,
        CURLINFO_CONTENT_TYPE
    );


$curlError =
    curl_error(
        $ch
    );


curl_close(
    $ch
);


/* ==========================================================
   COMPROBAR CURL
========================================================== */

if (
    $html === false ||
    trim($html) === ""
) {

    echo json_encode(
        [
            "success" => false,
            "error" =>
                "No se ha podido acceder a la página indicada.",
            "detalle" =>
                $curlError
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   COMPROBAR RESPUESTA HTTP
========================================================== */

if (
    $httpCode < 200 ||
    $httpCode >= 400
) {

    echo json_encode(
        [
            "success" => false,
            "error" =>
                "La página ha respondido con el código HTTP " .
                $httpCode .
                "."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   COMPROBAR HTML
========================================================== */

if (
    $contentType !== null &&
    stripos(
        $contentType,
        "text/html"
    ) === false &&
    stripos(
        $contentType,
        "application/xhtml+xml"
    ) === false
) {

    echo json_encode(
        [
            "success" => false,
            "error" =>
                "La URL indicada no parece contener una página HTML."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ==========================================================
   DOM
========================================================== */

libxml_use_internal_errors(
    true
);


$dom =
    new DOMDocument();


$dom->loadHTML(
    $html,
    LIBXML_NOERROR |
    LIBXML_NOWARNING |
    LIBXML_NONET
);


$xpath =
    new DOMXPath(
        $dom
    );


/* ==========================================================
   TÍTULO
========================================================== */

$title =
    "";


$titleNodes =
    $xpath->query(
        "//title"
    );


if (
    $titleNodes &&
    $titleNodes->length > 0
) {

    $title =
        trim(
            $titleNodes->item(0)->textContent
        );

}


/* ==========================================================
   META DESCRIPTION
========================================================== */

$metaDescription =
    "";


$descriptionNodes =
    $xpath->query(
        "//meta[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='description']/@content"
    );


if (
    $descriptionNodes &&
    $descriptionNodes->length > 0
) {

    $metaDescription =
        trim(
            $descriptionNodes->item(0)->nodeValue
        );

}


/* ==========================================================
   META ROBOTS
========================================================== */

$robots =
    "";


$robotsNodes =
    $xpath->query(
        "//meta[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='robots']/@content"
    );


if (
    $robotsNodes &&
    $robotsNodes->length > 0
) {

    $robots =
        trim(
            $robotsNodes->item(0)->nodeValue
        );

}


/* ==========================================================
   H1
========================================================== */

$h1 =
    [];


$h1Nodes =
    $xpath->query(
        "//h1"
    );


if ($h1Nodes) {

    foreach (
        $h1Nodes as $node
    ) {

        $texto =
            trim(
                preg_replace(
                    "/\s+/",
                    " ",
                    $node->textContent
                )
            );


        if ($texto !== "") {

            $h1[] =
                $texto;

        }

    }

}


/* ==========================================================
   H2
========================================================== */

$h2 =
    [];


$h2Nodes =
    $xpath->query(
        "//h2"
    );


if ($h2Nodes) {

    foreach (
        $h2Nodes as $node
    ) {

        $texto =
            trim(
                preg_replace(
                    "/\s+/",
                    " ",
                    $node->textContent
                )
            );


        if ($texto !== "") {

            $h2[] =
                $texto;

        }

    }

}


/* ==========================================================
   IMÁGENES
========================================================== */

$imagenesTotal =
    0;


$imagenesSinAlt =
    0;


$imageNodes =
    $xpath->query(
        "//img"
    );


if ($imageNodes) {

    $imagenesTotal =
        $imageNodes->length;


    foreach (
        $imageNodes as $imagen
    ) {

        $alt =
            trim(
                $imagen->getAttribute(
                    "alt"
                )
            );


        if ($alt === "") {

            $imagenesSinAlt++;

        }

    }

}


/* ==========================================================
   ENLACES
========================================================== */

$enlacesTotal =
    0;


$enlacesInternos =
    0;


$enlacesExternos =
    0;


$linkNodes =
    $xpath->query(
        "//a[@href]"
    );


$hostPrincipal =
    strtolower(
        $urlInfo["host"] ?? ""
    );


if ($linkNodes) {

    $enlacesTotal =
        $linkNodes->length;


    foreach (
        $linkNodes as $link
    ) {

        $href =
            trim(
                $link->getAttribute(
                    "href"
                )
            );


        if (
            $href === "" ||
            strpos(
                $href,
                "#"
            ) === 0 ||
            strpos(
                $href,
                "mailto:"
            ) === 0 ||
            strpos(
                $href,
                "tel:"
            ) === 0
        ) {

            continue;

        }


        $hrefCompleto =
            $href;


        if (
            strpos(
                $href,
                "//"
            ) === 0
        ) {

            $hrefCompleto =
                $scheme .
                ":" .
                $href;

        } elseif (
            strpos(
                $href,
                "http://"
            ) !== 0 &&
            strpos(
                $href,
                "https://"
            ) !== 0
        ) {

            $hrefCompleto =
                rtrim(
                    $url,
                    "/"
                ) .
                "/" .
                ltrim(
                    $href,
                    "/"
                );

        }


        $linkInfo =
            parse_url(
                $hrefCompleto
            );


        $linkHost =
            strtolower(
                $linkInfo["host"] ?? ""
            );


        if (
            $linkHost === "" ||
            $linkHost === $hostPrincipal
        ) {

            $enlacesInternos++;

        } else {

            $enlacesExternos++;

        }

    }

}


/* ==========================================================
   TEXTO VISIBLE
========================================================== */

$bodyTexto =
    "";


$bodyNodes =
    $xpath->query(
        "//body"
    );


if (
    $bodyNodes &&
    $bodyNodes->length > 0
) {

    $bodyTexto =
        trim(
            preg_replace(
                "/\s+/",
                " ",
                $bodyNodes->item(0)->textContent
            )
        );

}


/* ==========================================================
   LONGITUD DEL CONTENIDO
========================================================== */

$palabras =
    0;


if ($bodyTexto !== "") {

    $palabras =
        str_word_count(
            $bodyTexto,
            0,
            "áéíóúüñÁÉÍÓÚÜÑ"
        );

}


/* ==========================================================
   CANONICAL
========================================================== */

$canonical =
    "";


$canonicalNodes =
    $xpath->query(
        "//link[translate(@rel,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='canonical']/@href"
    );


if (
    $canonicalNodes &&
    $canonicalNodes->length > 0
) {

    $canonical =
        trim(
            $canonicalNodes->item(0)->nodeValue
        );

}


/* ==========================================================
   OPEN GRAPH
========================================================== */

$ogTitle =
    "";


$ogDescription =
    "";


$ogImage =
    "";


$ogTitleNodes =
    $xpath->query(
        "//meta[@property='og:title']/@content"
    );


if (
    $ogTitleNodes &&
    $ogTitleNodes->length > 0
) {

    $ogTitle =
        trim(
            $ogTitleNodes->item(0)->nodeValue
        );

}


$ogDescriptionNodes =
    $xpath->query(
        "//meta[@property='og:description']/@content"
    );


if (
    $ogDescriptionNodes &&
    $ogDescriptionNodes->length > 0
) {

    $ogDescription =
        trim(
            $ogDescriptionNodes->item(0)->nodeValue
        );

}


$ogImageNodes =
    $xpath->query(
        "//meta[@property='og:image']/@content"
    );


if (
    $ogImageNodes &&
    $ogImageNodes->length > 0
) {

    $ogImage =
        trim(
            $ogImageNodes->item(0)->nodeValue
        );

}


/* ==========================================================
   LIMPIAR ERRORES DOM
========================================================== */

libxml_clear_errors();


/* ==========================================================
   RESULTADO
========================================================== */

$analisis = [

    "url" =>
        $url,

    "http_code" =>
        $httpCode,

    "titulo" =>
        $title,

    "longitud_titulo" =>
        mb_strlen(
            $title
        ),

    "meta_description" =>
        $metaDescription,

    "longitud_meta_description" =>
        mb_strlen(
            $metaDescription
        ),

    "robots" =>
        $robots,

    "h1" =>
        $h1,

    "numero_h1" =>
        count(
            $h1
        ),

    "h2" =>
        $h2,

    "numero_h2" =>
        count(
            $h2
        ),

    "imagenes_total" =>
        $imagenesTotal,

    "imagenes_sin_alt" =>
        $imagenesSinAlt,

    "enlaces_total" =>
        $enlacesTotal,

    "enlaces_internos" =>
        $enlacesInternos,

    "enlaces_externos" =>
        $enlacesExternos,

    "palabras_aproximadas" =>
        $palabras,

    "canonical" =>
        $canonical,

    "og_title" =>
        $ogTitle,

    "og_description" =>
        $ogDescription,

    "og_image" =>
        $ogImage

];


/* ==========================================================
   RESPUESTA
========================================================== */

echo json_encode(
    [

        "success" =>
            true,

        "message" =>
            "La página se ha analizado correctamente.",

        "analisis" =>
            $analisis

    ],
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

exit;

?>
