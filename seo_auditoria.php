
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
   AÑADIR HTTPS
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
   INFORMACIÓN URL
========================================================== */

$urlInfo =
    parse_url(
        $url
    );


$scheme =
    strtolower(
        $urlInfo["scheme"] ?? ""
    );


$hostPrincipal =
    strtolower(
        $urlInfo["host"] ?? ""
    );


if (
    $scheme !== "http" &&
    $scheme !== "https"
) {

    echo json_encode(
        [
            "success" => false,
            "error" => "La URL debe utilizar HTTP o HTTPS."
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
            25,

        CURLOPT_USERAGENT =>
            "Mozilla/5.0 (compatible; ViziuneAI-SEO-Auditor/1.0)",

        CURLOPT_HTTPHEADER =>
            [
                "Accept: text/html,application/xhtml+xml"
            ],

        CURLOPT_ENCODING =>
            "",

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


$finalUrl =
    curl_getinfo(
        $ch,
        CURLINFO_EFFECTIVE_URL
    );


$curlError =
    curl_error(
        $ch
    );


curl_close(
    $ch
);


/* ==========================================================
   ERROR CURL
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
   COMPROBAR HTTP
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
   DOM
========================================================== */

libxml_use_internal_errors(
    true
);


$dom =
    new DOMDocument();


$dom->loadHTML(
    '<?xml encoding="UTF-8">' .
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
   TITLE
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
            preg_replace(
                "/\s+/u",
                " ",
                $titleNodes->item(0)->textContent
            )
        );

}


/* ==========================================================
   META DESCRIPTION
========================================================== */

$metaDescription =
    "";


$descriptionNodes =
    $xpath->query(
        "//meta[
            translate(
                @name,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='description'
        ]/@content"
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
        "//meta[
            translate(
                @name,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='robots'
        ]/@content"
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
   VIEWPORT
========================================================== */

$viewport =
    "";


$viewportNodes =
    $xpath->query(
        "//meta[
            translate(
                @name,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='viewport'
        ]/@content"
    );


if (
    $viewportNodes &&
    $viewportNodes->length > 0
) {

    $viewport =
        trim(
            $viewportNodes->item(0)->nodeValue
        );

}


/* ==========================================================
   IDIOMA HTML
========================================================== */

$idioma =
    "";


$htmlNodes =
    $xpath->query(
        "/html"
    );


if (
    $htmlNodes &&
    $htmlNodes->length > 0
) {

    $idioma =
        trim(
            $htmlNodes->item(0)->getAttribute(
                "lang"
            )
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
                    "/\s+/u",
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
                    "/\s+/u",
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
   H3
========================================================== */

$h3 =
    [];


$h3Nodes =
    $xpath->query(
        "//h3"
    );


if ($h3Nodes) {

    foreach (
        $h3Nodes as $node
    ) {

        $texto =
            trim(
                preg_replace(
                    "/\s+/u",
                    " ",
                    $node->textContent
                )
            );


        if ($texto !== "") {

            $h3[] =
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


$imagenesConAlt =
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

        } else {

            $imagenesConAlt++;

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


if ($linkNodes) {

    foreach (
        $linkNodes as $link
    ) {

        $href =
            trim(
                $link->getAttribute(
                    "href"
                )
            );


        if ($href === "") {

            continue;

        }


        $enlacesTotal++;


        if (
            strpos(
                $href,
                "#"
            ) === 0 ||
            stripos(
                $href,
                "mailto:"
            ) === 0 ||
            stripos(
                $href,
                "tel:"
            ) === 0 ||
            stripos(
                $href,
                "javascript:"
            ) === 0
        ) {

            continue;

        }


        /*
         * Enlaces relativos
         */

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
            preg_match(
                "#^https?://#i",
                $href
            )
        ) {

            $hrefCompleto =
                $href;

        } else {

            $rutaBase =
                $finalUrl !== ""
                ? $finalUrl
                : $url;


            $hrefCompleto =
                rtrim(
                    dirname(
                        $rutaBase
                    ),
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


        /*
         * www y dominio sin www
         */

        $hostLimpio =
            preg_replace(
                "/^www\./i",
                "",
                $hostPrincipal
            );


        $linkHostLimpio =
            preg_replace(
                "/^www\./i",
                "",
                $linkHost
            );


        if (
            $linkHostLimpio === "" ||
            $linkHostLimpio === $hostLimpio
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
                "/\s+/u",
                " ",
                $bodyNodes->item(0)->textContent
            )
        );

}


/* ==========================================================
   PALABRAS
========================================================== */

$palabras =
    0;


if ($bodyTexto !== "") {

    $palabrasArray =
        preg_split(
            "/\s+/u",
            $bodyTexto,
            -1,
            PREG_SPLIT_NO_EMPTY
        );


    $palabras =
        count(
            $palabrasArray
        );

}


/* ==========================================================
   CANONICAL
========================================================== */

$canonical =
    "";


$canonicalNodes =
    $xpath->query(
        "//link[
            contains(
                translate(
                    @rel,
                    'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    'abcdefghijklmnopqrstuvwxyz'
                ),
                'canonical'
            )
        ]/@href"
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
        "//meta[
            translate(
                @property,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='og:title'
        ]/@content"
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
        "//meta[
            translate(
                @property,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='og:description'
        ]/@content"
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
        "//meta[
            translate(
                @property,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='og:image'
        ]/@content"
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
   HTTPS
========================================================== */

$usaHttps =
    (
        strtolower(
            parse_url(
                $finalUrl !== ""
                ? $finalUrl
                : $url,
                PHP_URL_SCHEME
            ) ?? ""
        ) === "https"
    );


/* ==========================================================
   TAMAÑO HTML
========================================================== */

$tamanoHtml =
    strlen(
        $html
    );


$tamanoHtmlKb =
    round(
        $tamanoHtml / 1024,
        2
    );


/* ==========================================================
   PUNTUACIÓN SEO
========================================================== */

$puntuacion =
    0;


$recomendaciones =
    [];


/* ----------------------------------------------------------
   HTTPS — 15 PUNTOS
---------------------------------------------------------- */

if ($usaHttps) {

    $puntuacion += 15;

} else {

    $recomendaciones[] = [
        "tipo" => "error",
        "titulo" => "HTTPS no detectado",
        "texto" =>
            "La página no utiliza HTTPS. Comprueba que el sitio tenga un certificado SSL activo."
    ];

}


/* ----------------------------------------------------------
   TITLE — 15 PUNTOS
---------------------------------------------------------- */

if ($title !== "") {

    $longitudTitulo =
        mb_strlen(
            $title
        );


    if (
        $longitudTitulo >= 30 &&
        $longitudTitulo <= 65
    ) {

        $puntuacion += 15;

    } else {

        $puntuacion += 8;

        $recomendaciones[] = [
            "tipo" => "warning",
            "titulo" => "Título mejorable",
            "texto" =>
                "El título existe, pero su longitud (" .
                $longitudTitulo .
                " caracteres) podría optimizarse."
        ];

    }

} else {

    $recomendaciones[] = [
        "tipo" => "error",
        "titulo" => "Falta el título",
        "texto" =>
            "La página no tiene una etiqueta title."
    ];

}


/* ----------------------------------------------------------
   META DESCRIPTION — 15 PUNTOS
---------------------------------------------------------- */

if ($metaDescription !== "") {

    $longitudDescripcion =
        mb_strlen(
            $metaDescription
        );


    if (
        $longitudDescripcion >= 120 &&
        $longitudDescripcion <= 165
    ) {

        $puntuacion += 15;

    } else {

        $puntuacion += 8;

        $recomendaciones[] = [
            "tipo" => "warning",
            "titulo" => "Meta descripción mejorable",
            "texto" =>
                "La meta descripción tiene " .
                $longitudDescripcion .
                " caracteres. Conviene revisar su longitud y contenido."
        ];

    }

} else {

    $recomendaciones[] = [
        "tipo" => "error",
        "titulo" => "Falta la meta descripción",
        "texto" =>
            "Añade una meta descripción relevante para explicar el contenido de la página."
    ];

}


/* ----------------------------------------------------------
   H1 — 15 PUNTOS
---------------------------------------------------------- */

$numeroH1 =
    count(
        $h1
    );


if ($numeroH1 === 1) {

    $puntuacion += 15;

} elseif ($numeroH1 === 0) {

    $recomendaciones[] = [
        "tipo" => "error",
        "titulo" => "No se ha encontrado H1",
        "texto" =>
            "Añade un H1 que describa claramente el contenido principal de la página."
    ];

} else {

    $puntuacion += 8;

    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "Hay varios H1",
        "texto" =>
            "Se han encontrado " .
            $numeroH1 .
            " etiquetas H1. Revisa que la estructura de encabezados sea coherente."
    ];

}


/* ----------------------------------------------------------
   H2 — 10 PUNTOS
---------------------------------------------------------- */

$numeroH2 =
    count(
        $h2
    );


if ($numeroH2 >= 2) {

    $puntuacion += 10;

} elseif ($numeroH2 === 1) {

    $puntuacion += 6;

    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "Pocos H2",
        "texto" =>
            "Considera utilizar más encabezados H2 si el contenido necesita una mayor organización."
    ];

} else {

    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "No se han encontrado H2",
        "texto" =>
            "Utiliza encabezados H2 para organizar las diferentes secciones del contenido."
    ];

}


/* ----------------------------------------------------------
   IMÁGENES ALT — 10 PUNTOS
---------------------------------------------------------- */

if ($imagenesTotal === 0) {

    $puntuacion += 10;

} elseif ($imagenesSinAlt === 0) {

    $puntuacion += 10;

} else {

    $porcentajeAlt =
        $imagenesConAlt /
        $imagenesTotal;


    $puntuacion +=
        round(
            10 *
            $porcentajeAlt
        );


    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "Imágenes sin ALT",
        "texto" =>
            "Hay " .
            $imagenesSinAlt .
            " imágenes sin atributo ALT."
    ];

}


/* ----------------------------------------------------------
   CONTENIDO — 10 PUNTOS
---------------------------------------------------------- */

if ($palabras >= 1000) {

    $puntuacion += 10;

} elseif ($palabras >= 500) {

    $puntuacion += 7;

    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "Contenido moderado",
        "texto" =>
            "Se han detectado aproximadamente " .
            $palabras .
            " palabras. Revisa si el contenido responde completamente a la intención de búsqueda."
    ];

} elseif ($palabras > 0) {

    $puntuacion += 4;

    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "Poco contenido",
        "texto" =>
            "Se han detectado aproximadamente " .
            $palabras .
            " palabras."
    ];

} else {

    $recomendaciones[] = [
        "tipo" => "error",
        "titulo" => "No se ha detectado contenido",
        "texto" =>
            "No se ha podido detectar contenido textual suficiente."
    ];

}


/* ==========================================================
   CANONICAL
========================================================== */

if ($canonical === "") {

    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "Canonical no encontrada",
        "texto" =>
            "Comprueba si la página debería incluir una etiqueta canonical."
    ];

}


/* ==========================================================
   VIEWPORT
========================================================== */

if ($viewport === "") {

    $recomendaciones[] = [
        "tipo" => "error",
        "titulo" => "Viewport no detectado",
        "texto" =>
            "Añade una etiqueta viewport para mejorar la adaptación a dispositivos móviles."
    ];

}


/* ==========================================================
   IDIOMA
========================================================== */

if ($idioma === "") {

    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "Idioma no definido",
        "texto" =>
            "Comprueba que la etiqueta HTML incluya correctamente el atributo lang."
    ];

}


/* ==========================================================
   OPEN GRAPH
========================================================== */

if ($ogTitle === "") {

    $recomendaciones[] = [
        "tipo" => "warning",
        "titulo" => "Open Graph no detectado",
        "texto" =>
            "Considera añadir etiquetas Open Graph para mejorar la presentación de la página al compartirla en redes sociales."
    ];

}


/* ==========================================================
   ASEGURAR PUNTUACIÓN
========================================================== */

$puntuacion =
    max(
        0,
        min(
            100,
            $puntuacion
        )
    );


/* ==========================================================
   NIVEL
========================================================== */

if ($puntuacion >= 90) {

    $nivel =
        "Excelente";

} elseif ($puntuacion >= 75) {

    $nivel =
        "Bueno";

} elseif ($puntuacion >= 50) {

    $nivel =
        "Mejorable";

} else {

    $nivel =
        "Necesita mejoras";

}


/* ==========================================================
   RESULTADO
========================================================== */

$analisis = [

    "url" =>
        $url,

    "url_final" =>
        $finalUrl,

    "http_code" =>
        $httpCode,

    "puntuacion" =>
        $puntuacion,

    "nivel" =>
        $nivel,

    "recomendaciones" =>
        $recomendaciones,

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

    "viewport" =>
        $viewport,

    "idioma" =>
        $idioma,

    "https" =>
        $usaHttps,

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

    "h3" =>
        $h3,

    "numero_h3" =>
        count(
            $h3
        ),

    "imagenes_total" =>
        $imagenesTotal,

    "imagenes_con_alt" =>
        $imagenesConAlt,

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
        $ogImage,

    "tamano_html_kb" =>
        $tamanoHtmlKb

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
