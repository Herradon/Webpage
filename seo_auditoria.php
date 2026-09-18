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
   EVALUACIÓN SEO
========================================================== */

$puntuacion =
    0;


$problemas =
    [];


$correctos =
    [];


/* ----------------------------------------------------------
   TÍTULO
---------------------------------------------------------- */

$longitudTitulo =
    mb_strlen(
        $title
    );


if ($title === "") {

    $problemas[] =
        "La página no tiene etiqueta <title>.";

} elseif (
    $longitudTitulo >= 30 &&
    $longitudTitulo <= 60
) {

    $puntuacion += 15;

    $correctos[] =
        "El título SEO tiene una longitud adecuada.";

} elseif (
    $longitudTitulo > 0 &&
    $longitudTitulo <= 70
) {

    $puntuacion += 10;

    $problemas[] =
        "El título existe, pero su longitud podría optimizarse.";

} else {

    $puntuacion += 5;

    $problemas[] =
        "El título SEO es demasiado largo.";

}


/* ----------------------------------------------------------
   META DESCRIPTION
---------------------------------------------------------- */

$longitudMeta =
    mb_strlen(
        $metaDescription
    );


if ($metaDescription === "") {

    $problemas[] =
        "La página no tiene meta description.";

} elseif (
    $longitudMeta >= 120 &&
    $longitudMeta <= 160
) {

    $puntuacion += 15;

    $correctos[] =
        "La meta description tiene una longitud adecuada.";

} elseif (
    $longitudMeta > 0 &&
    $longitudMeta <= 180
) {

    $puntuacion += 10;

    $problemas[] =
        "La meta description existe, pero su longitud podría optimizarse.";

} else {

    $puntuacion += 5;

    $problemas[] =
        "La meta description es demasiado larga.";

}


/* ----------------------------------------------------------
   H1
---------------------------------------------------------- */

$numeroH1 =
    count(
        $h1
    );


if ($numeroH1 === 1) {

    $puntuacion += 15;

    $correctos[] =
        "La página tiene un único H1.";

} elseif ($numeroH1 === 0) {

    $problemas[] =
        "La página no tiene ningún H1.";

} else {

    $puntuacion += 8;

    $problemas[] =
        "La página tiene varios H1; conviene revisar la estructura.";

}


/* ----------------------------------------------------------
   CONTENIDO
---------------------------------------------------------- */

if ($palabras >= 600) {

    $puntuacion += 15;

    $correctos[] =
        "La página dispone de una cantidad de contenido suficiente para el análisis.";

} elseif ($palabras >= 300) {

    $puntuacion += 10;

    $problemas[] =
        "La cantidad de contenido podría ampliarse.";

} elseif ($palabras > 0) {

    $puntuacion += 5;

    $problemas[] =
        "La página tiene poco contenido textual.";

} else {

    $problemas[] =
        "No se ha detectado contenido textual visible.";

}


/* ----------------------------------------------------------
   IMÁGENES Y ALT
---------------------------------------------------------- */

if ($imagenesTotal === 0) {

    $puntuacion += 10;

    $correctos[] =
        "No se han detectado imágenes que requieran revisión de atributos ALT.";

} elseif ($imagenesSinAlt === 0) {

    $puntuacion += 10;

    $correctos[] =
        "Todas las imágenes detectadas tienen atributo ALT.";

} else {

    $imagenesConAlt =
        $imagenesTotal -
        $imagenesSinAlt;

    $porcentajeAlt =
        ($imagenesConAlt / $imagenesTotal) *
        100;


    if ($porcentajeAlt >= 75) {

        $puntuacion += 7;

    } elseif ($porcentajeAlt >= 50) {

        $puntuacion += 5;

    } else {

        $puntuacion += 2;

    }


    $problemas[] =
        "Hay " .
        $imagenesSinAlt .
        " imagen(es) sin atributo ALT.";

}


/* ----------------------------------------------------------
   CANONICAL
---------------------------------------------------------- */

if ($canonical !== "") {

    $puntuacion += 10;

    $correctos[] =
        "La página dispone de etiqueta canonical.";

} else {

    $problemas[] =
        "No se ha detectado una etiqueta canonical.";

}


/* ----------------------------------------------------------
   ROBOTS
---------------------------------------------------------- */

if ($robots !== "") {

    $robotsMinusculas =
        strtolower(
            $robots
        );


    if (
        strpos(
            $robotsMinusculas,
            "noindex"
        ) !== false
    ) {

        $puntuacion += 0;

        $problemas[] =
            "La etiqueta robots contiene noindex; la página podría no aparecer en los buscadores.";

    } else {

        $puntuacion += 5;

        $correctos[] =
            "La página dispone de configuración de robots sin noindex detectado.";

    }

} else {

    $puntuacion += 3;

    $correctos[] =
        "No se ha detectado una etiqueta robots restrictiva.";

}


/* ----------------------------------------------------------
   OPEN GRAPH
---------------------------------------------------------- */

$ogElementos =
    0;


if ($ogTitle !== "") {

    $ogElementos++;

}


if ($ogDescription !== "") {

    $ogElementos++;

}


if ($ogImage !== "") {

    $ogElementos++;

}


if ($ogElementos === 3) {

    $puntuacion += 10;

    $correctos[] =
        "La página tiene configurados título, descripción e imagen Open Graph.";

} elseif ($ogElementos > 0) {

    $puntuacion += 5;

    $problemas[] =
        "Las etiquetas Open Graph están incompletas.";

} else {

    $problemas[] =
        "No se han detectado etiquetas Open Graph.";

}


/* ----------------------------------------------------------
   H2
---------------------------------------------------------- */

$numeroH2 =
    count(
        $h2
    );


if ($numeroH2 > 0) {

    $puntuacion += 5;

    $correctos[] =
        "La página utiliza etiquetas H2 para estructurar el contenido.";

} else {

    $problemas[] =
        "No se han detectado etiquetas H2.";

}


/* ==========================================================
   ASEGURAR PUNTUACIÓN ENTRE 0 Y 100
========================================================== */

$puntuacion =
    max(
        0,
        min(
            100,
            (int) $puntuacion
        )
    );


/* ==========================================================
   VALORACIÓN
========================================================== */

if ($puntuacion >= 90) {

    $valoracion =
        "Excelente";

    $descripcionValoracion =
        "La página presenta una base SEO muy sólida y no se han detectado problemas importantes en los aspectos analizados.";

} elseif ($puntuacion >= 75) {

    $valoracion =
        "Buena";

    $descripcionValoracion =
        "La página presenta una base SEO buena, aunque todavía existen algunos aspectos que pueden optimizarse.";

} elseif ($puntuacion >= 50) {

    $valoracion =
        "Mejorable";

    $descripcionValoracion =
        "La página tiene una base SEO aceptable, pero presenta varios aspectos que conviene mejorar.";

} else {

    $valoracion =
        "Deficiente";

    $descripcionValoracion =
        "La página presenta varios problemas SEO que deberían revisarse y corregirse.";

}


/* ==========================================================
   RESUMEN GENERAL
========================================================== */

$resumenGeneral =
    "La página " .
    $url .
    " obtiene una valoración SEO de " .
    $puntuacion .
    "/100 (" .
    $valoracion .
    "). " .
    $descripcionValoracion;


/* ==========================================================
   RESUMEN PARA EL CHAT
========================================================== */

$resumenChat =
    "AUDITORÍA SEO - VIZIUNEAI\n\n" .

    "URL: " .
    $url .
    "\n\n" .

    "VALORACIÓN SEO: " .
    $puntuacion .
    "/100 - " .
    $valoracion .
    "\n\n" .

    "RESUMEN:\n" .
    $resumenGeneral .
    "\n\n" .

    "DATOS ANALIZADOS:\n" .

    "- Título: " .
    (
        $title !== ""
            ? $title
            : "No encontrado"
    ) .
    "\n" .

    "- Longitud del título: " .
    $longitudTitulo .
    " caracteres\n" .

    "- Meta description: " .
    (
        $metaDescription !== ""
            ? $metaDescription
            : "No encontrada"
    ) .
    "\n" .

    "- Longitud de meta description: " .
    $longitudMeta .
    " caracteres\n" .

    "- H1 encontrados: " .
    $numeroH1 .
    "\n" .

    "- H2 encontrados: " .
    $numeroH2 .
    "\n" .

    "- Palabras aproximadas: " .
    $palabras .
    "\n" .

    "- Imágenes: " .
    $imagenesTotal .
    "\n" .

    "- Imágenes sin ALT: " .
    $imagenesSinAlt .
    "\n" .

    "- Enlaces totales: " .
    $enlacesTotal .
    "\n" .

    "- Enlaces internos: " .
    $enlacesInternos .
    "\n" .

    "- Enlaces externos: " .
    $enlacesExternos .
    "\n" .

    "- Canonical: " .
    (
        $canonical !== ""
            ? $canonical
            : "No encontrada"
    ) .
    "\n" .

    "- Robots: " .
    (
        $robots !== ""
            ? $robots
            : "No especificado"
    ) .
    "\n" .

    "- Open Graph title: " .
    (
        $ogTitle !== ""
            ? "Sí"
            : "No"
    ) .
    "\n" .

    "- Open Graph description: " .
    (
        $ogDescription !== ""
            ? "Sí"
            : "No"
    ) .
    "\n" .

    "- Open Graph image: " .
    (
        $ogImage !== ""
            ? "Sí"
            : "No"
    ) .
    "\n\n" .


    "ASPECTOS CORRECTOS:\n";


if (count($correctos) > 0) {

    foreach ($correctos as $correcto) {

        $resumenChat .=
            "✓ " .
            $correcto .
            "\n";

    }

} else {

    $resumenChat .=
        "No se han identificado aspectos especialmente favorables en los criterios analizados.\n";

}


$resumenChat .=
    "\nPROBLEMAS DETECTADOS:\n";


if (count($problemas) > 0) {

    foreach ($problemas as $problema) {

        $resumenChat .=
            "• " .
            $problema .
            "\n";

    }

} else {

    $resumenChat .=
        "No se han detectado problemas en los criterios analizados.\n";

}


$resumenChat .=
    "\nSOLICITUD PARA EL CHAT:\n" .

    "Analiza esta auditoría SEO y ayúdame a gestionar y mejorar esta página. " .
    "Quiero que me indiques qué cambios debo realizar, priorizando los problemas más importantes. " .
    "Explícame exactamente qué debo modificar en la página y cómo hacerlo.";


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
        $longitudTitulo,

    "meta_description" =>
        $metaDescription,

    "longitud_meta_description" =>
        $longitudMeta,

    "robots" =>
        $robots,

    "h1" =>
        $h1,

    "numero_h1" =>
        $numeroH1,

    "h2" =>
        $h2,

    "numero_h2" =>
        $numeroH2,

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
        $ogImage,

    "puntuacion" =>
        $puntuacion,

    "valoracion" =>
        $valoracion,

    "descripcion_valoracion" =>
        $descripcionValoracion,

    "problemas" =>
        $problemas,

    "correctos" =>
        $correctos,

    "resumen_general" =>
        $resumenGeneral,

    "resumen_chat" =>
        $resumenChat

];


/* ==========================================================
   GUARDAR ÚLTIMA AUDITORÍA EN SESIÓN
========================================================== */

$_SESSION["ultima_auditoria_seo"] =
    $analisis;


/* ==========================================================
   RESPUESTA JSON
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