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
   FUNCIONES AUXILIARES
========================================================== */

function responderError($mensaje, $codigo = 400)
{
    http_response_code($codigo);

    echo json_encode(
        [
            "success" => false,
            "error" => $mensaje
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


function obtenerTextoNodo($node)
{
    return trim(
        preg_replace(
            "/\s+/",
            " ",
            $node->textContent
        )
    );
}


function comprobarURLPublica($url)
{
    $info = parse_url($url);

    if (!$info || empty($info["host"])) {
        return false;
    }

    $host = strtolower($info["host"]);

    /*
     * Permitir localhost únicamente para evitar
     * confusiones, pero bloquearlo para auditorías.
     */
    if (
        $host === "localhost" ||
        $host === "localhost.localdomain"
    ) {
        return false;
    }

    /*
     * Si el host ya es una IP, comprobarla directamente.
     */
    if (filter_var($host, FILTER_VALIDATE_IP)) {

        if (
            filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE |
                FILTER_FLAG_NO_RES_RANGE
            ) === false
        ) {
            return false;
        }

        return true;
    }

    /*
     * Resolver DNS para comprobar que no apunta
     * directamente a una IP privada/reservada.
     */
    $ips = [];

    if (function_exists("gethostbynamel")) {

        $resueltos =
            @gethostbynamel($host);

        if (is_array($resueltos)) {

            $ips = array_merge(
                $ips,
                $resueltos
            );

        }

    }

    if (empty($ips)) {

        return false;

    }

    foreach ($ips as $ip) {

        if (
            filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE |
                FILTER_FLAG_NO_RES_RANGE
            ) === false
        ) {

            return false;

        }

    }

    return true;
}


function descargarURL(
    $url,
    $timeout = 15,
    $maxBytes = 5000000
) {

    $inicio =
        microtime(true);

    $ch =
        curl_init($url);

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
                $timeout,

            CURLOPT_USERAGENT =>
                "ViziuneAI SEO Auditor/2.0",

            CURLOPT_HTTPHEADER =>
                [
                    "Accept: text/html,application/xhtml+xml,text/plain,*/*;q=0.8"
                ],

            CURLOPT_SSL_VERIFYPEER =>
                true,

            CURLOPT_SSL_VERIFYHOST =>
                2,

            CURLOPT_ENCODING =>
                ""

        ]
    );


    $contenido =
        curl_exec($ch);


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


    $contentLength =
        curl_getinfo(
            $ch,
            CURLINFO_SIZE_DOWNLOAD
        );


    $finalUrl =
        curl_getinfo(
            $ch,
            CURLINFO_EFFECTIVE_URL
        );


    $curlError =
        curl_error($ch);


    curl_close($ch);


    $tiempo =
        round(
            (microtime(true) - $inicio) * 1000
        );


    if ($contenido === false) {

        return [
            "success" => false,
            "contenido" => "",
            "http_code" => $httpCode,
            "content_type" => $contentType,
            "content_length" => $contentLength,
            "final_url" => $finalUrl,
            "tiempo_ms" => $tiempo,
            "error" => $curlError
        ];

    }


    /*
     * Protección adicional contra respuestas
     * excesivamente grandes.
     */
    if (
        strlen($contenido) >
        $maxBytes
    ) {

        $contenido =
            substr(
                $contenido,
                0,
                $maxBytes
            );

    }


    return [
        "success" => true,
        "contenido" => $contenido,
        "http_code" => $httpCode,
        "content_type" => $contentType,
        "content_length" =>
            strlen($contenido),
        "final_url" => $finalUrl,
        "tiempo_ms" => $tiempo,
        "error" => ""
    ];
}


function comprobarRecurso($url)
{
    $ch =
        curl_init($url);

    curl_setopt_array(
        $ch,
        [

            CURLOPT_RETURNTRANSFER =>
                true,

            CURLOPT_FOLLOWLOCATION =>
                true,

            CURLOPT_MAXREDIRS =>
                3,

            CURLOPT_CONNECTTIMEOUT =>
                5,

            CURLOPT_TIMEOUT =>
                8,

            CURLOPT_USERAGENT =>
                "ViziuneAI SEO Auditor/2.0",

            CURLOPT_SSL_VERIFYPEER =>
                true,

            CURLOPT_SSL_VERIFYHOST =>
                2,

            CURLOPT_NOBODY =>
                true

        ]
    );

    curl_exec($ch);

    $httpCode =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

    curl_close($ch);

    return (
        $httpCode >= 200 &&
        $httpCode < 400
    );
}


function limpiarLista($lista)
{
    $resultado = [];

    foreach ($lista as $elemento) {

        $elemento =
            trim(
                preg_replace(
                    "/\s+/",
                    " ",
                    $elemento
                )
            );

        if (
            $elemento !== "" &&
            !in_array(
                $elemento,
                $resultado,
                true
            )
        ) {

            $resultado[] =
                $elemento;

        }

    }

    return $resultado;
}


function crearEstado(
    $correcto,
    $textoCorrecto,
    $textoProblema
) {

    return $correcto
        ? [
            "correcto" => true,
            "texto" => $textoCorrecto
        ]
        : [
            "correcto" => false,
            "texto" => $textoProblema
        ];
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

    responderError(
        "Los datos recibidos no son válidos."
    );

}


/* ==========================================================
   URL
========================================================== */

$url =
    trim(
        $data["url"] ?? ""
    );


if ($url === "") {

    responderError(
        "Debes introducir una URL."
    );

}


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


if (
    !filter_var(
        $url,
        FILTER_VALIDATE_URL
    )
) {

    responderError(
        "La URL introducida no es válida."
    );

}


$urlInfo =
    parse_url($url);


$scheme =
    strtolower(
        $urlInfo["scheme"] ?? ""
    );


if (
    $scheme !== "http" &&
    $scheme !== "https"
) {

    responderError(
        "La URL debe comenzar por http:// o https://."
    );

}


/* ==========================================================
   PROTECCIÓN CONTRA DESTINOS INTERNOS
========================================================== */

if (
    !comprobarURLPublica($url)
) {

    responderError(
        "La URL indicada no puede analizarse porque no corresponde a un destino web público."
    );

}


/* ==========================================================
   DESCARGAR PÁGINA
========================================================== */

$resultadoDescarga =
    descargarURL(
        $url,
        20,
        5000000
    );


if (
    !$resultadoDescarga["success"] ||
    trim(
        $resultadoDescarga["contenido"]
    ) === ""
) {

    responderError(
        "No se ha podido acceder a la página indicada."
    );

}


$html =
    $resultadoDescarga["contenido"];


$httpCode =
    $resultadoDescarga["http_code"];


$contentType =
    $resultadoDescarga["content_type"];


$tiempoRespuesta =
    $resultadoDescarga["tiempo_ms"];


$finalUrl =
    $resultadoDescarga["final_url"];


$tamanoPagina =
    $resultadoDescarga["content_length"];


if (
    $httpCode < 200 ||
    $httpCode >= 400
) {

    responderError(
        "La página ha respondido con el código HTTP " .
        $httpCode .
        "."
    );

}


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

    responderError(
        "La URL indicada no parece contener una página HTML."
    );

}


/* ==========================================================
   DOM
========================================================== */

libxml_use_internal_errors(true);


$dom =
    new DOMDocument();


$dom->loadHTML(
    $html,
    LIBXML_NOERROR |
    LIBXML_NOWARNING |
    LIBXML_NONET
);


$xpath =
    new DOMXPath($dom);


/* ==========================================================
   HTML / IDIOMA
========================================================== */

$htmlNode =
    $xpath->query(
        "/html"
    );


$idioma =
    "";


if (
    $htmlNode &&
    $htmlNode->length > 0
) {

    $idioma =
        trim(
            $htmlNode
                ->item(0)
                ->getAttribute("lang")
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
            $viewportNodes
                ->item(0)
                ->nodeValue
        );

}


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
        obtenerTextoNodo(
            $titleNodes->item(0)
        );

}


$longitudTitulo =
    mb_strlen($title);


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
            $descriptionNodes
                ->item(0)
                ->nodeValue
        );

}


$longitudMeta =
    mb_strlen(
        $metaDescription
    );


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
            $robotsNodes
                ->item(0)
                ->nodeValue
        );

}


$robotsMinusculas =
    strtolower($robots);


$noindex =
    strpos(
        $robotsMinusculas,
        "noindex"
    ) !== false;


$nofollow =
    strpos(
        $robotsMinusculas,
        "nofollow"
    ) !== false;


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
            obtenerTextoNodo($node);

        if ($texto !== "") {

            $h1[] =
                $texto;

        }

    }

}


$h1 =
    limpiarLista($h1);


$numeroH1 =
    count($h1);


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
            obtenerTextoNodo($node);

        if ($texto !== "") {

            $h2[] =
                $texto;

        }

    }

}


$h2 =
    limpiarLista($h2);


$numeroH2 =
    count($h2);


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
            obtenerTextoNodo($node);

        if ($texto !== "") {

            $h3[] =
                $texto;

        }

    }

}


$h3 =
    limpiarLista($h3);


$numeroH3 =
    count($h3);


/* ==========================================================
   PÁRRAFOS
========================================================== */

$parrafos =
    0;


$paragraphNodes =
    $xpath->query(
        "//p"
    );


if ($paragraphNodes) {

    $parrafos =
        $paragraphNodes->length;

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

        if (
            $imagen->hasAttribute("alt")
        ) {

            $alt =
                trim(
                    $imagen->getAttribute("alt")
                );

        } else {

            $alt =
                "";

        }


        if ($alt === "") {

            $imagenesSinAlt++;

        } else {

            $imagenesConAlt++;

        }

    }

}


$porcentajeAlt =
    $imagenesTotal > 0
        ? round(
            (
                $imagenesConAlt /
                $imagenesTotal
            ) * 100
        )
        : 100;


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
                $link->getAttribute("href")
            );


        if (
            $href === "" ||
            strpos($href, "#") === 0 ||
            stripos($href, "mailto:") === 0 ||
            stripos($href, "tel:") === 0 ||
            stripos($href, "javascript:") === 0
        ) {

            continue;

        }


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

            $base =
                $finalUrl !== ""
                    ? $finalUrl
                    : $url;

            $partesBase =
                parse_url($base);

            $hostBase =
                $partesBase["scheme"] .
                "://" .
                $partesBase["host"];

            if (
                isset(
                    $partesBase["port"]
                )
            ) {

                $hostBase .=
                    ":" .
                    $partesBase["port"];

            }

            $hrefCompleto =
                rtrim(
                    $hostBase,
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
                $bodyNodes
                    ->item(0)
                    ->textContent
            )
        );

}


/* ==========================================================
   PALABRAS
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
        "//link[
            translate(
                @rel,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='canonical'
        ]/@href"
    );


if (
    $canonicalNodes &&
    $canonicalNodes->length > 0
) {

    $canonical =
        trim(
            $canonicalNodes
                ->item(0)
                ->nodeValue
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


$ogUrl =
    "";


$ogType =
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
            $ogTitleNodes
                ->item(0)
                ->nodeValue
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
            $ogDescriptionNodes
                ->item(0)
                ->nodeValue
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
            $ogImageNodes
                ->item(0)
                ->nodeValue
        );

}


$ogUrlNodes =
    $xpath->query(
        "//meta[
            translate(
                @property,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='og:url'
        ]/@content"
    );


if (
    $ogUrlNodes &&
    $ogUrlNodes->length > 0
) {

    $ogUrl =
        trim(
            $ogUrlNodes
                ->item(0)
                ->nodeValue
        );

}


$ogTypeNodes =
    $xpath->query(
        "//meta[
            translate(
                @property,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='og:type'
        ]/@content"
    );


if (
    $ogTypeNodes &&
    $ogTypeNodes->length > 0
) {

    $ogType =
        trim(
            $ogTypeNodes
                ->item(0)
                ->nodeValue
        );

}


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

if ($ogUrl !== "") {
    $ogElementos++;
}


/* ==========================================================
   TWITTER CARD
========================================================== */

$twitterCard =
    "";


$twitterTitle =
    "";


$twitterDescription =
    "";


$twitterImage =
    "";


$twitterCardNodes =
    $xpath->query(
        "//meta[
            translate(
                @name,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='twitter:card'
        ]/@content"
    );


if (
    $twitterCardNodes &&
    $twitterCardNodes->length > 0
) {

    $twitterCard =
        trim(
            $twitterCardNodes
                ->item(0)
                ->nodeValue
        );

}


$twitterTitleNodes =
    $xpath->query(
        "//meta[
            translate(
                @name,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='twitter:title'
        ]/@content"
    );


if (
    $twitterTitleNodes &&
    $twitterTitleNodes->length > 0
) {

    $twitterTitle =
        trim(
            $twitterTitleNodes
                ->item(0)
                ->nodeValue
        );

}


$twitterDescriptionNodes =
    $xpath->query(
        "//meta[
            translate(
                @name,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='twitter:description'
        ]/@content"
    );


if (
    $twitterDescriptionNodes &&
    $twitterDescriptionNodes->length > 0
) {

    $twitterDescription =
        trim(
            $twitterDescriptionNodes
                ->item(0)
                ->nodeValue
        );

}


$twitterImageNodes =
    $xpath->query(
        "//meta[
            translate(
                @name,
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'abcdefghijklmnopqrstuvwxyz'
            )='twitter:image'
        ]/@content"
    );


if (
    $twitterImageNodes &&
    $twitterImageNodes->length > 0
) {

    $twitterImage =
        trim(
            $twitterImageNodes
                ->item(0)
                ->nodeValue
        );

}


/* ==========================================================
   ROBOTS.TXT Y SITEMAP
========================================================== */

$baseUrl =
    $scheme .
    "://" .
    ($urlInfo["host"] ?? "");


if (isset($urlInfo["port"])) {

    $baseUrl .=
        ":" .
        $urlInfo["port"];

}


$robotsUrl =
    rtrim(
        $baseUrl,
        "/"
    ) .
    "/robots.txt";


$sitemapUrl =
    rtrim(
        $baseUrl,
        "/"
    ) .
    "/sitemap.xml";


$robotsTxt =
    false;


$sitemap =
    false;


if (
    comprobarURLPublica(
        $robotsUrl
    )
) {

    $robotsTxt =
        comprobarRecurso(
            $robotsUrl
        );

}


if (
    comprobarURLPublica(
        $sitemapUrl
    )
) {

    $sitemap =
        comprobarRecurso(
            $sitemapUrl
        );

}


/* ==========================================================
   HTTPS
========================================================== */

$https =
    $scheme === "https";


/* ==========================================================
   EVALUACIÓN
========================================================== */

$problemas =
    [];


$correctos =
    [];


$prioridadAlta =
    [];


$prioridadMedia =
    [];


$oportunidades =
    [];


/* ==========================================================
   PUNTUACIONES POR ÁREA
========================================================== */

$puntosTecnico =
    0;

$puntosOnPage =
    0;

$puntosContenido =
    0;

$puntosEstructura =
    0;


/* ==========================================================
   SEO TÉCNICO
   MÁXIMO 25
========================================================== */


/* HTTPS - 5 */

if ($https) {

    $puntosTecnico += 5;

    $correctos[] =
        "La página utiliza HTTPS.";

} else {

    $problemas[] =
        "La página no utiliza HTTPS.";

    $prioridadAlta[] =
        "Configurar HTTPS y utilizar una conexión segura en toda la web.";

}


/* HTTP - 3 */

if ($httpCode === 200) {

    $puntosTecnico += 3;

    $correctos[] =
        "La página principal responde correctamente con código HTTP 200.";

} else {

    $problemas[] =
        "La página responde con el código HTTP " .
        $httpCode .
        ".";

    $prioridadAlta[] =
        "Revisar el código de respuesta HTTP de la página.";

}


/* Canonical - 4 */

if ($canonical !== "") {

    $puntosTecnico += 4;

    $correctos[] =
        "Se ha detectado una etiqueta canonical.";

} else {

    $problemas[] =
        "No se ha detectado una etiqueta canonical.";

    $prioridadMedia[] =
        "Añadir o revisar la etiqueta canonical.";

}


/* Robots - 3 */

if ($noindex) {

    $problemas[] =
        "La etiqueta robots contiene noindex.";

    $prioridadAlta[] =
        "Revisar la directiva noindex porque puede impedir que la página aparezca en los buscadores.";

} else {

    $puntosTecnico += 3;

    $correctos[] =
        "No se ha detectado una directiva noindex.";

}


/* robots.txt - 3 */

if ($robotsTxt) {

    $puntosTecnico += 3;

    $correctos[] =
        "Se ha detectado un archivo robots.txt.";

} else {

    $problemas[] =
        "No se ha detectado un robots.txt accesible.";

    $prioridadMedia[] =
        "Revisar si el sitio necesita un archivo robots.txt correctamente configurado.";

}


/* sitemap - 3 */

if ($sitemap) {

    $puntosTecnico += 3;

    $correctos[] =
        "Se ha detectado un sitemap.xml accesible.";

} else {

    $problemas[] =
        "No se ha detectado un sitemap.xml accesible.";

    $prioridadMedia[] =
        "Crear o revisar el sitemap XML del sitio.";

}


/* Viewport - 2 */

if ($viewport !== "") {

    $puntosTecnico += 2;

    $correctos[] =
        "Se ha detectado una configuración viewport para dispositivos móviles.";

} else {

    $problemas[] =
        "No se ha detectado una etiqueta viewport.";

    $prioridadMedia[] =
        "Añadir una configuración viewport para mejorar la adaptación móvil.";

}


/* Idioma - 2 */

if ($idioma !== "") {

    $puntosTecnico += 2;

    $correctos[] =
        "La página declara el idioma del documento.";

} else {

    $problemas[] =
        "La página no declara un idioma mediante el atributo lang.";

    $prioridadMedia[] =
        "Declarar correctamente el idioma principal de la página.";

}


/* ==========================================================
   SEO ON-PAGE
   MÁXIMO 25
========================================================== */


/* TITLE - 6 */

if ($title === "") {

    $problemas[] =
        "La página no tiene etiqueta title.";

    $prioridadAlta[] =
        "Crear un title único y descriptivo para la página.";

} elseif (
    $longitudTitulo >= 30 &&
    $longitudTitulo <= 60
) {

    $puntosOnPage += 6;

    $correctos[] =
        "El title tiene una longitud orientativa adecuada.";

} elseif (
    $longitudTitulo > 0 &&
    $longitudTitulo <= 70
) {

    $puntosOnPage += 4;

    $problemas[] =
        "El title existe, pero su longitud podría optimizarse.";

    $prioridadMedia[] =
        "Revisar la longitud y el contenido del title.";

} else {

    $puntosOnPage += 2;

    $problemas[] =
        "El title es demasiado largo.";

    $prioridadMedia[] =
        "Optimizar la longitud del title.";

}


/* DESCRIPTION - 6 */

if ($metaDescription === "") {

    $problemas[] =
        "La página no tiene meta description.";

    $prioridadAlta[] =
        "Crear una meta description descriptiva y orientada al contenido de la página.";

} elseif (
    $longitudMeta >= 120 &&
    $longitudMeta <= 160
) {

    $puntosOnPage += 6;

    $correctos[] =
        "La meta description tiene una longitud orientativa adecuada.";

} elseif (
    $longitudMeta > 0 &&
    $longitudMeta <= 180
) {

    $puntosOnPage += 4;

    $problemas[] =
        "La meta description existe, pero puede optimizarse.";

    $prioridadMedia[] =
        "Revisar la longitud y el contenido de la meta description.";

} else {

    $puntosOnPage += 2;

    $problemas[] =
        "La meta description es demasiado larga.";

    $prioridadMedia[] =
        "Optimizar la meta description.";

}


/* H1 - 5 */

if ($numeroH1 === 1) {

    $puntosOnPage += 5;

    $correctos[] =
        "La página tiene un único H1.";

} elseif ($numeroH1 === 0) {

    $problemas[] =
        "La página no tiene ningún H1.";

    $prioridadAlta[] =
        "Añadir un H1 descriptivo que identifique el contenido principal de la página.";

} else {

    $puntosOnPage += 2;

    $problemas[] =
        "La página tiene varios H1.";

    $prioridadMedia[] =
        "Revisar la estructura de H1 para mantener una jerarquía clara.";

}


/* Imágenes - 4 */

if ($imagenesTotal === 0) {

    $puntosOnPage += 4;

    $correctos[] =
        "No se han detectado imágenes que requieran revisión de ALT.";

} elseif ($imagenesSinAlt === 0) {

    $puntosOnPage += 4;

    $correctos[] =
        "Todas las imágenes detectadas tienen atributo ALT.";

} elseif ($porcentajeAlt >= 75) {

    $puntosOnPage += 3;

    $problemas[] =
        "Hay " .
        $imagenesSinAlt .
        " imagen(es) sin atributo ALT.";

    $prioridadMedia[] =
        "Añadir atributos ALT descriptivos a las imágenes que carecen de ellos.";

} elseif ($porcentajeAlt >= 50) {

    $puntosOnPage += 2;

    $problemas[] =
        "Una parte importante de las imágenes no tiene atributo ALT.";

    $prioridadMedia[] =
        "Revisar los atributos ALT de las imágenes.";

} else {

    $puntosOnPage += 1;

    $problemas[] =
        "La mayoría de las imágenes no tiene atributo ALT.";

    $prioridadAlta[] =
        "Revisar los atributos ALT de las imágenes principales.";

}


/* Open Graph - 4 */

if ($ogElementos >= 4) {

    $puntosOnPage += 4;

    $correctos[] =
        "Las principales etiquetas Open Graph están configuradas.";

} elseif ($ogElementos > 0) {

    $puntosOnPage += 2;

    $problemas[] =
        "La configuración Open Graph está incompleta.";

    $oportunidades[] =
        "Completar las etiquetas Open Graph para mejorar la presentación al compartir la web.";

} else {

    $problemas[] =
        "No se han detectado etiquetas Open Graph.";

    $oportunidades[] =
        "Añadir Open Graph para controlar cómo aparecen las páginas al compartirse.";

}


/* ==========================================================
   CONTENIDO
   MÁXIMO 20
========================================================== */


/* Cantidad */

if ($palabras >= 1000) {

    $puntosContenido += 8;

    $correctos[] =
        "La página contiene una cantidad considerable de texto visible.";

} elseif ($palabras >= 600) {

    $puntosContenido += 7;

    $correctos[] =
        "La página dispone de una cantidad de contenido razonable.";

} elseif ($palabras >= 300) {

    $puntosContenido += 5;

    $problemas[] =
        "La cantidad de contenido podría ampliarse.";

    $prioridadMedia[] =
        "Revisar si la página necesita contenido adicional para responder mejor a las búsquedas de los usuarios.";

} elseif ($palabras > 0) {

    $puntosContenido += 2;

    $problemas[] =
        "La página contiene poco texto visible.";

    $prioridadAlta[] =
        "Revisar y ampliar el contenido principal de la página.";

} else {

    $problemas[] =
        "No se ha detectado contenido textual visible.";

    $prioridadAlta[] =
        "Revisar el contenido principal de la página.";

}


/* Párrafos - 4 */

if ($parrafos >= 10) {

    $puntosContenido += 4;

    $correctos[] =
        "La página utiliza una estructura de párrafos suficientemente desarrollada.";

} elseif ($parrafos >= 5) {

    $puntosContenido += 3;

} elseif ($parrafos > 0) {

    $puntosContenido += 1;

    $oportunidades[] =
        "Desarrollar mejor los bloques de contenido y la información ofrecida al usuario.";

} else {

    $problemas[] =
        "No se han detectado párrafos de contenido.";

}


/* Enlaces - 4 */

if ($enlacesInternos >= 5) {

    $puntosContenido += 4;

    $correctos[] =
        "La página dispone de varios enlaces internos.";

} elseif ($enlacesInternos > 0) {

    $puntosContenido += 2;

    $oportunidades[] =
        "Revisar la estructura de enlaces internos y conectar las páginas relevantes.";

} else {

    $problemas[] =
        "No se han detectado enlaces internos.";

    $prioridadMedia[] =
        "Revisar la estrategia de enlazado interno.";

}


/* Términos y contenido */

if ($palabras > 0) {

    $oportunidades[] =
        "Analizar si el contenido responde realmente a las necesidades y búsquedas del público objetivo.";

}


/* ==========================================================
   ESTRUCTURA
   MÁXIMO 10
========================================================== */


/* H2 - 4 */

if ($numeroH2 > 0) {

    $puntosEstructura += 4;

    $correctos[] =
        "La página utiliza H2 para estructurar parte de su contenido.";

} else {

    $problemas[] =
        "No se han detectado H2.";

    $prioridadMedia[] =
        "Organizar el contenido mediante encabezados H2 cuando la extensión de la página lo requiera.";

}


/* H3 - 2 */

if ($numeroH3 > 0) {

    $puntosEstructura += 2;

    $correctos[] =
        "La página utiliza H3 para desarrollar subapartados.";

} else {

    $puntosEstructura += 1;

}


/* Jerarquía */

$jerarquiaCorrecta =
    true;


$ultimoNivel =
    1;


$headingNodes =
    $xpath->query(
        "//h1 | //h2 | //h3 | //h4 | //h5 | //h6"
    );


$arbolEncabezados =
    "";


if ($headingNodes) {

    foreach (
        $headingNodes as $heading
    ) {

        $tag =
            strtolower(
                $heading->nodeName
            );

        $nivel =
            (int) substr(
                $tag,
                1
            );


        $texto =
            obtenerTextoNodo(
                $heading
            );


        if ($texto === "") {

            continue;

        }


        $arbolEncabezados .=
            str_repeat(
                "  ",
                max(
                    0,
                    $nivel - 1
                )
            ) .
            $tag .
            ": " .
            $texto .
            "\n";


        if (
            $nivel >
            $ultimoNivel + 1
        ) {

            $jerarquiaCorrecta =
                false;

        }


        $ultimoNivel =
            $nivel;

    }

}


if ($jerarquiaCorrecta) {

    $puntosEstructura += 4;

    $correctos[] =
        "No se han detectado saltos evidentes en la jerarquía de encabezados analizada.";

} else {

    $puntosEstructura += 1;

    $problemas[] =
        "Se han detectado posibles saltos en la jerarquía de encabezados.";

    $prioridadMedia[] =
        "Revisar la jerarquía de H1, H2, H3 y siguientes niveles.";

}


/* ==========================================================
   ASEGURAR LÍMITES
========================================================== */

$puntosTecnico =
    min(
        25,
        max(
            0,
            $puntosTecnico
        )
    );


$puntosOnPage =
    min(
        25,
        max(
            0,
            $puntosOnPage
        )
    );


$puntosContenido =
    min(
        20,
        max(
            0,
            $puntosContenido
        )
    );


$puntosEstructura =
    min(
        10,
        max(
            0,
            $puntosEstructura
        )
    );


/*
 * Bloques adicionales hasta completar 100.
 */

$puntosImagenes =
    0;


if ($imagenesTotal === 0) {

    $puntosImagenes = 5;

} elseif ($porcentajeAlt >= 90) {

    $puntosImagenes = 5;

} elseif ($porcentajeAlt >= 75) {

    $puntosImagenes = 4;

} elseif ($porcentajeAlt >= 50) {

    $puntosImagenes = 3;

} else {

    $puntosImagenes = 1;

}


$puntosEnlaces =
    0;


if ($enlacesInternos >= 10) {

    $puntosEnlaces = 5;

} elseif ($enlacesInternos >= 5) {

    $puntosEnlaces = 4;

} elseif ($enlacesInternos > 0) {

    $puntosEnlaces = 2;

}


$puntosSocial =
    0;


if (
    $ogElementos >= 4 &&
    $twitterCard !== ""
) {

    $puntosSocial = 5;

} elseif ($ogElementos >= 2) {

    $puntosSocial = 3;

} elseif ($ogElementos > 0) {

    $puntosSocial = 2;

}


$puntosMovil =
    $viewport !== ""
        ? 5
        : 0;


if ($viewport === "") {

    $problemas[] =
        "No se ha detectado configuración viewport para móviles.";

}


$puntuacion =
    $puntosTecnico +
    $puntosOnPage +
    $puntosContenido +
    $puntosEstructura +
    $puntosImagenes +
    $puntosEnlaces +
    $puntosSocial +
    $puntosMovil;


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
        "La página presenta una base SEO muy sólida en los aspectos analizados, aunque siempre pueden existir oportunidades específicas de mejora.";

} elseif ($puntuacion >= 75) {

    $valoracion =
        "Buena";

    $descripcionValoracion =
        "La página presenta una base SEO buena, aunque existen varios aspectos que pueden optimizarse.";

} elseif ($puntuacion >= 50) {

    $valoracion =
        "Mejorable";

    $descripcionValoracion =
        "La página tiene una base SEO funcional, pero presenta diferentes aspectos que conviene revisar y mejorar.";

} else {

    $valoracion =
        "Deficiente";

    $descripcionValoracion =
        "La página presenta varios aspectos SEO que deberían revisarse y corregirse.";

}


/* ==========================================================
   ELIMINAR DUPLICADOS
========================================================== */

$problemas =
    array_values(
        array_unique(
            $problemas
        )
    );


$correctos =
    array_values(
        array_unique(
            $correctos
        )
    );


$prioridadAlta =
    array_values(
        array_unique(
            $prioridadAlta
        )
    );


$prioridadMedia =
    array_values(
        array_unique(
            $prioridadMedia
        )
    );


$oportunidades =
    array_values(
        array_unique(
            $oportunidades
        )
    );


/* ==========================================================
   DIAGNÓSTICO GENERAL
========================================================== */

$resumenGeneral =
    "La página " .
    $url .
    " obtiene una puntuación SEO de " .
    $puntuacion .
    "/100 y una valoración \"" .
    $valoracion .
    "\". " .
    $descripcionValoracion .
    " Se han revisado aspectos técnicos, SEO on-page, contenido, estructura, imágenes, enlaces, elementos sociales y adaptación básica a dispositivos móviles.";


/* ==========================================================
   FORMATO TAMAÑO
========================================================== */

if ($tamanoPagina >= 1048576) {

    $tamanoPaginaFormateado =
        round(
            $tamanoPagina / 1048576,
            2
        ) .
        " MB";

} elseif ($tamanoPagina >= 1024) {

    $tamanoPaginaFormateado =
        round(
            $tamanoPagina / 1024,
            2
        ) .
        " KB";

} else {

    $tamanoPaginaFormateado =
        $tamanoPagina .
        " bytes";

}


/* ==========================================================
   ESTADO OPEN GRAPH
========================================================== */

if ($ogElementos >= 4) {

    $openGraphEstado =
        "Completo";

} elseif ($ogElementos > 0) {

    $openGraphEstado =
        "Parcial";

} else {

    $openGraphEstado =
        "No detectado";

}


/* ==========================================================
   INFORME PARA VIZIUNEAI
========================================================== */

$resumenChat =
    "AUDITORÍA SEO — VIZIUNEAI\n\n" .

    "==================================================\n" .
    "INFORMACIÓN DEL PROYECTO\n" .
    "==================================================\n\n" .

    "URL ANALIZADA: " .
    $url .
    "\n" .

    "URL FINAL: " .
    (
        $finalUrl !== ""
            ? $finalUrl
            : $url
    ) .
    "\n" .

    "CÓDIGO HTTP: " .
    $httpCode .
    "\n" .

    "HTTPS: " .
    (
        $https
            ? "Sí"
            : "No"
    ) .
    "\n" .

    "TIEMPO DE RESPUESTA: " .
    $tiempoRespuesta .
    " ms\n" .

    "TAMAÑO APROXIMADO: " .
    $tamanoPaginaFormateado .
    "\n\n" .


    "==================================================\n" .
    "DIAGNÓSTICO GENERAL\n" .
    "==================================================\n\n" .

    "PUNTUACIÓN SEO: " .
    $puntuacion .
    "/100\n" .

    "VALORACIÓN: " .
    $valoracion .
    "\n\n" .

    $resumenGeneral .
    "\n\n" .


    "==================================================\n" .
    "SEO TÉCNICO\n" .
    "==================================================\n\n" .

    "HTTPS: " .
    (
        $https
            ? "Sí"
            : "No"
    ) .
    "\n" .

    "Código HTTP: " .
    $httpCode .
    "\n" .

    "Canonical: " .
    (
        $canonical !== ""
            ? $canonical
            : "No encontrada"
    ) .
    "\n" .

    "Robots: " .
    (
        $robots !== ""
            ? $robots
            : "No especificado"
    ) .
    "\n" .

    "Noindex: " .
    (
        $noindex
            ? "Sí"
            : "No"
    ) .
    "\n" .

    "Nofollow: " .
    (
        $nofollow
            ? "Sí"
            : "No"
    ) .
    "\n" .

    "robots.txt: " .
    (
        $robotsTxt
            ? "Detectado"
            : "No detectado"
    ) .
    "\n" .

    "sitemap.xml: " .
    (
        $sitemap
            ? "Detectado"
            : "No detectado"
    ) .
    "\n" .

    "Idioma declarado: " .
    (
        $idioma !== ""
            ? $idioma
            : "No declarado"
    ) .
    "\n" .

    "Viewport: " .
    (
        $viewport !== ""
            ? $viewport
            : "No detectado"
    ) .
    "\n\n" .


    "==================================================\n" .
    "SEO ON-PAGE\n" .
    "==================================================\n\n" .

    "TITLE: " .
    (
        $title !== ""
            ? $title
            : "No encontrado"
    ) .
    "\n" .

    "Longitud TITLE: " .
    $longitudTitulo .
    " caracteres\n" .

    "META DESCRIPTION: " .
    (
        $metaDescription !== ""
            ? $metaDescription
            : "No encontrada"
    ) .
    "\n" .

    "Longitud META DESCRIPTION: " .
    $longitudMeta .
    " caracteres\n" .

    "H1: " .
    $numeroH1 .
    "\n" .

    "H2: " .
    $numeroH2 .
    "\n" .

    "H3: " .
    $numeroH3 .
    "\n\n";


$resumenChat .=
    "H1 ENCONTRADOS:\n";


if (!empty($h1)) {

    foreach ($h1 as $item) {

        $resumenChat .=
            "- " .
            $item .
            "\n";

    }

} else {

    $resumenChat .=
        "- Ninguno\n";

}


$resumenChat .=
    "\nH2 ENCONTRADOS:\n";


if (!empty($h2)) {

    foreach ($h2 as $item) {

        $resumenChat .=
            "- " .
            $item .
            "\n";

    }

} else {

    $resumenChat .=
        "- Ninguno\n";

}


$resumenChat .=
    "\nH3 ENCONTRADOS:\n";


if (!empty($h3)) {

    foreach ($h3 as $item) {

        $resumenChat .=
            "- " .
            $item .
            "\n";

    }

} else {

    $resumenChat .=
        "- Ninguno\n";

}


$resumenChat .=
    "\n" .
    "==================================================\n" .
    "CONTENIDO\n" .
    "==================================================\n\n" .

    "Palabras aproximadas: " .
    $palabras .
    "\n" .

    "Párrafos: " .
    $parrafos .
    "\n" .

    "Imágenes: " .
    $imagenesTotal .
    "\n" .

    "Imágenes con ALT: " .
    $imagenesConAlt .
    "\n" .

    "Imágenes sin ALT: " .
    $imagenesSinAlt .
    "\n" .

    "Porcentaje de imágenes con ALT: " .
    $porcentajeAlt .
    "%\n\n" .


    "==================================================\n" .
    "ENLACES\n" .
    "==================================================\n\n" .

    "Enlaces totales detectados: " .
    $enlacesTotal .
    "\n" .

    "Enlaces internos: " .
    $enlacesInternos .
    "\n" .

    "Enlaces externos: " .
    $enlacesExternos .
    "\n\n" .


    "==================================================\n" .
    "REDES SOCIALES\n" .
    "==================================================\n\n" .

    "Open Graph: " .
    $openGraphEstado .
    "\n" .

    "og:title: " .
    (
        $ogTitle !== ""
            ? $ogTitle
            : "No detectado"
    ) .
    "\n" .

    "og:description: " .
    (
        $ogDescription !== ""
            ? $ogDescription
            : "No detectado"
    ) .
    "\n" .

    "og:image: " .
    (
        $ogImage !== ""
            ? $ogImage
            : "No detectado"
    ) .
    "\n" .

    "og:url: " .
    (
        $ogUrl !== ""
            ? $ogUrl
            : "No detectado"
    ) .
    "\n" .

    "og:type: " .
    (
        $ogType !== ""
            ? $ogType
            : "No detectado"
    ) .
    "\n" .

    "Twitter Card: " .
    (
        $twitterCard !== ""
            ? $twitterCard
            : "No detectada"
    ) .
    "\n" .

    "Twitter title: " .
    (
        $twitterTitle !== ""
            ? $twitterTitle
            : "No detectado"
    ) .
    "\n" .

    "Twitter description: " .
    (
        $twitterDescription !== ""
            ? $twitterDescription
            : "No detectada"
    ) .
    "\n" .

    "Twitter image: " .
    (
        $twitterImage !== ""
            ? $twitterImage
            : "No detectada"
    ) .
    "\n\n";


$resumenChat .=
    "==================================================\n" .
    "ESTRUCTURA DE ENCABEZADOS\n" .
    "==================================================\n\n" .
    (
        $arbolEncabezados !== ""
            ? $arbolEncabezados
            : "No se han encontrado encabezados.\n"
    ) .
    "\n";


$resumenChat .=
    "==================================================\n" .
    "ASPECTOS CORRECTOS\n" .
    "==================================================\n\n";


if (!empty($correctos)) {

    foreach ($correctos as $correcto) {

        $resumenChat .=
            "✓ " .
            $correcto .
            "\n";

    }

} else {

    $resumenChat .=
        "No se han identificado aspectos especialmente favorables.\n";

}


$resumenChat .=
    "\n" .
    "==================================================\n" .
    "PROBLEMAS DETECTADOS\n" .
    "==================================================\n\n";


if (!empty($problemas)) {

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
    "\n" .
    "==================================================\n" .
    "PRIORIDAD ALTA\n" .
    "==================================================\n\n";


if (!empty($prioridadAlta)) {

    foreach ($prioridadAlta as $item) {

        $resumenChat .=
            "🔴 " .
            $item .
            "\n";

    }

} else {

    $resumenChat .=
        "No se han identificado problemas de prioridad alta.\n";

}


$resumenChat .=
    "\n" .
    "==================================================\n" .
    "PRIORIDAD MEDIA\n" .
    "==================================================\n\n";


if (!empty($prioridadMedia)) {

    foreach ($prioridadMedia as $item) {

        $resumenChat .=
            "🟠 " .
            $item .
            "\n";

    }

} else {

    $resumenChat .=
        "No se han identificado problemas de prioridad media.\n";

}


$resumenChat .=
    "\n" .
    "==================================================\n" .
    "OPORTUNIDADES DE MEJORA\n" .
    "==================================================\n\n";


if (!empty($oportunidades)) {

    foreach ($oportunidades as $item) {

        $resumenChat .=
            "💡 " .
            $item .
            "\n";

    }

} else {

    $resumenChat .=
        "No se han identificado oportunidades adicionales.\n";

}


$resumenChat .=
    "\n" .
    "==================================================\n" .
    "CONTEXTO PARA VIZIUNEAI\n" .
    "==================================================\n\n" .

    "Utiliza esta auditoría como contexto inicial del proyecto.\n\n" .

    "No te limites a repetir los datos técnicos. Explica al usuario " .
    "qué significa cada problema detectado, por qué puede ser " .
    "importante y qué alternativas existen para solucionarlo.\n\n" .

    "Prioriza los problemas de mayor impacto y diferencia entre " .
    "errores técnicos, oportunidades de mejora y recomendaciones.\n\n" .

    "Ten en cuenta que las recomendaciones SEO deben adaptarse " .
    "a los objetivos, servicios, público y tipo de negocio del usuario. " .
    "No asumas que todos los cambios son necesarios sin conocer el contexto.\n\n" .

    "Ayuda al usuario a convertir este diagnóstico en acciones " .
    "concretas para mejorar su página y su presencia online.";


/* ==========================================================
   RESULTADO FINAL
========================================================== */

$analisis = [

    "url" =>
        $url,

    "url_final" =>
        $finalUrl,

    "http_code" =>
        $httpCode,

    "https" =>
        $https,

    "tiempo_respuesta_ms" =>
        $tiempoRespuesta,

    "tamano_pagina" =>
        $tamanoPagina,

    "tamano_pagina_formateado" =>
        $tamanoPaginaFormateado,

    "idioma" =>
        $idioma,

    "viewport" =>
        $viewport,

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

    "noindex" =>
        $noindex,

    "nofollow" =>
        $nofollow,

    "robots_txt" =>
        $robotsTxt,

    "sitemap" =>
        $sitemap,

    "h1" =>
        $h1,

    "numero_h1" =>
        $numeroH1,

    "h2" =>
        $h2,

    "numero_h2" =>
        $numeroH2,

    "h3" =>
        $h3,

    "numero_h3" =>
        $numeroH3,

    "parrafos" =>
        $parrafos,

    "imagenes_total" =>
        $imagenesTotal,

    "imagenes_con_alt" =>
        $imagenesConAlt,

    "imagenes_sin_alt" =>
        $imagenesSinAlt,

    "porcentaje_alt" =>
        $porcentajeAlt,

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

    "og_url" =>
        $ogUrl,

    "og_type" =>
        $ogType,

    "twitter_card" =>
        $twitterCard,

    "twitter_title" =>
        $twitterTitle,

    "twitter_description" =>
        $twitterDescription,

    "twitter_image" =>
        $twitterImage,

    "open_graph_estado" =>
        $openGraphEstado,

    "puntuacion" =>
        $puntuacion,

    "valoracion" =>
        $valoracion,

    "descripcion_valoracion" =>
        $descripcionValoracion,

    "puntuaciones" =>
        [

            "tecnico" =>
                $puntosTecnico,

            "on_page" =>
                $puntosOnPage,

            "contenido" =>
                $puntosContenido,

            "estructura" =>
                $puntosEstructura,

            "imagenes" =>
                $puntosImagenes,

            "enlaces" =>
                $puntosEnlaces,

            "social" =>
                $puntosSocial,

            "movil" =>
                $puntosMovil

        ],

    "problemas" =>
        $problemas,

    "correctos" =>
        $correctos,

    "prioridad_alta" =>
        $prioridadAlta,

    "prioridad_media" =>
        $prioridadMedia,

    "oportunidades" =>
        $oportunidades,

    "arbol_encabezados" =>
        $arbolEncabezados,

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