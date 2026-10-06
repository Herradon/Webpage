<?php

session_start();

require_once __DIR__ . "/config.php";

$usuarioLogueado = isset($_SESSION["usuario_id"]);


/* ==========================================================
   PROCESAR AUDITORÍA SOLO EN POST
========================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header("Content-Type: application/json; charset=utf-8");

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
                "Viziune SEO Auditor/2.0",

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
                "Viziune SEO Auditor/2.0",

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


$puntuacionBase =
    $puntosTecnico +
    $puntosOnPage +
    $puntosContenido +
    $puntosEstructura +
    $puntosImagenes +
    $puntosEnlaces +
    $puntosSocial +
    $puntosMovil;


/*
|--------------------------------------------------------------------------
| CONVERTIR LA PUNTUACIÓN REAL A UNA ESCALA DE 0 A 100
|--------------------------------------------------------------------------
| El sistema utiliza un máximo teórico de 110 puntos:
| 25 + 25 + 20 + 10 + 10 + 10 + 5 + 5.
| Se normaliza para que la puntuación mostrada siempre sea /100.
|--------------------------------------------------------------------------
*/

$puntuacion =
    (int) round(
        ($puntuacionBase / 110) * 100
    );


$puntuacion =
    max(
        0,
        min(
            100,
            $puntuacion
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
    "AUDITORÍA SEO — VIZIUNE\n\n" .

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
    "CONTEXTO PARA VIZIUNE\n" .
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

}

?>


<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Auditoría SEO | Viziune</title>

<link rel="stylesheet" href="css/style.css">

<link rel="stylesheet" href="css/herramientas.css">

<style>

/* ==========================================================
   VIZIUNEAI - CSS PREMIUM SEO RESTAURADO
   Recupera el diseño de puntuación esférica/circular y tarjetas
========================================================== */


/* ==========================================
   VIZIUNEAI - AUDITORÍA SEO PREMIUM
========================================== */

.herramientas-page {
    min-height: 100vh;
    padding-top: 105px;
    background:
        radial-gradient(
            circle at 50% -10%,
            rgba(0, 243, 255, 0.12),
            transparent 35%
        ),
        radial-gradient(
            circle at 0% 50%,
            rgba(0, 120, 255, 0.06),
            transparent 30%
        ),
        #040a12;
    color: #ffffff;
}

/* ==========================================
   SECCIÓN
========================================== */

.herramientas-section {
    width: 100%;
    padding: 65px 0 110px;
}

.herramientas-section .container {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ==========================================
   CABECERA
========================================== */

.herramientas-header {
    max-width: 850px;
    margin: 0 auto 50px;
    text-align: center;
}

.herramientas-etiqueta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
    padding: 7px 15px;
    border: 1px solid rgba(0, 243, 255, 0.28);
    border-radius: 30px;
    background: rgba(0, 243, 255, 0.055);
    color: #00f3ff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    box-shadow:
        0 0 25px rgba(0, 243, 255, 0.04);
}

.herramientas-etiqueta::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #35d07f;
    box-shadow:
        0 0 10px rgba(53, 208, 127, 0.7);
}

.herramientas-header h1 {
    margin: 0 0 18px;
    color: #ffffff;
    font-size: 48px;
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -1.5px;
}

.herramientas-header h1::after {
    content: "";
    display: block;
    width: 55px;
    height: 3px;
    margin: 18px auto 0;
    border-radius: 5px;
    background: #00f3ff;
    box-shadow:
        0 0 15px rgba(0, 243, 255, 0.45);
}

.herramientas-header p {
    max-width: 690px;
    margin: 0 auto;
    color: #8f9eae;
    font-size: 16px;
    line-height: 1.75;
}



/* ==========================================
   PANEL PRINCIPAL
========================================== */
.seo-panel{
justify-items: center;
}

.seo-panel-header{
width: 100%;
text-align: center;
}
.seo-panel-header div{
width: 100%;
text-align: center;
}

.herramienta-card {
    position: relative;
    width: 100%;
    box-sizing: border-box;
    padding: 45px;
    background:
        linear-gradient(
            145deg,
            rgba(10, 25, 40, 0.97),
            rgba(4, 12, 21, 0.99)
        );
    border: 1px solid rgba(0, 243, 255, 0.14);
    border-radius: 26px;
    box-shadow:
        0 30px 80px rgba(0, 0, 0, 0.38),
        inset 0 1px 0 rgba(255, 255, 255, 0.025);
    overflow: hidden;
}

.herramienta-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 12%;
    width: 76%;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(0, 243, 255, 0.8),
        transparent
    );
}

.herramienta-card::after {
    content: "";
    position: absolute;
    top: -170px;
    right: -100px;
    width: 330px;
    height: 330px;
    border-radius: 50%;
    background: rgba(0, 243, 255, 0.045);
    filter: blur(30px);
    pointer-events: none;
}

/* ==========================================
   ICONO
========================================== */

.herramienta-icon {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
    margin: 0 auto 22px;
    border: 1px solid rgba(0, 243, 255, 0.28);
    border-radius: 20px;
    background:
        linear-gradient(
            145deg,
            rgba(0, 243, 255, 0.14),
            rgba(0, 243, 255, 0.025)
        );
    box-shadow:
        0 0 35px rgba(0, 243, 255, 0.07),
        inset 0 1px 0 rgba(255, 255, 255, 0.05);
    font-size: 30px;
}

.herramienta-card > h2 {
    position: relative;
    z-index: 1;
    margin: 0 0 10px;
    color: #ffffff;
    text-align: center;
    font-size: 27px;
    font-weight: 800;
    letter-spacing: -0.4px;
}

.herramienta-card > p {
    position: relative;
    z-index: 1;
    max-width: 680px;
    margin: 0 auto 32px;
    color: #8797a8;
    text-align: center;
    font-size: 14px;
    line-height: 1.7;
}

/* ==========================================
   FORMULARIO
========================================== */

.seo-form {
    position: relative;
    z-index: 2;
    max-width: 860px;
    margin: 0 auto 40px;
    padding: 7px;
    background: rgba(1, 7, 13, 0.72);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 15px;
    box-shadow:
        0 15px 40px rgba(0, 0, 0, 0.18);
}

.seo-form label {
    display: block;
    padding: 12px 14px 7px;
    color: #8f9eae;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.7px;
    text-transform: uppercase;
}

.seo-form input {
    display: block;
    width: 100%;
    min-height: 52px;
    box-sizing: border-box;
    padding: 0 17px;
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 10px;
    outline: none;
    background: #07121d;
    color: #ffffff;
    font-family: inherit;
    font-size: 14px;
    transition:
        border-color 0.25s ease,
        box-shadow 0.25s ease,
        background 0.25s ease;
}

.seo-form input::placeholder {
    color: #536576;
}

.seo-form input:focus {
    background: #081722;
    border-color: rgba(0, 243, 255, 0.5);
    box-shadow:
        0 0 0 3px rgba(0, 243, 255, 0.06);
}

.seo-form button,
.herramienta-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 52px;
    margin-top: 7px;
    padding: 13px 20px;
    border: 1px solid #00f3ff;
    border-radius: 10px;
    background: linear-gradient(
        135deg,
        #00f3ff,
        #00cbd6
    );
    color: #031018;
    font-family: inherit;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    box-shadow:
        0 8px 25px rgba(0, 243, 255, 0.12);
    transition:
        transform 0.2s ease,
        box-shadow 0.25s ease,
        filter 0.25s ease;
}

.seo-form button:hover,
.herramienta-button:hover {
    transform: translateY(-2px);
    filter: brightness(1.06);
    box-shadow:
        0 12px 32px rgba(0, 243, 255, 0.22);
}

.seo-form button:active,
.herramienta-button:active {
    transform: translateY(0);
}

.seo-form button:disabled {
    opacity: 0.55;
    cursor: wait;
    transform: none;
}

/* ==========================================
   LOADING
========================================== */

#seoLoading {
    margin: 18px auto 0;
    color: #00f3ff;
    text-align: center;
    font-size: 13px;
    font-weight: 700;
}

/* ==========================================
   ERROR
========================================== */

#seoError {
    max-width: 860px;
    margin: 20px auto;
    padding: 14px 17px;
    box-sizing: border-box;
    border: 1px solid rgba(255, 92, 92, 0.28);
    border-radius: 11px;
    background: rgba(255, 92, 92, 0.06);
    color: #ff8d8d;
    font-size: 13px;
    line-height: 1.5;
}

/* ==========================================
   RESULTADO
========================================== */

#seoResultado {
    position: relative;
    z-index: 2;
    margin-top: 42px;
    padding-top: 42px;
    border-top: 1px solid rgba(255, 255, 255, 0.07);
}


/* =========================================================
   RESUMEN SUPERIOR
========================================================= */

.seo-summary-grid {
    display: grid;
    grid-template-columns: 240px 1fr 1fr;
    gap: 16px;
    margin-bottom: 28px;
    align-items: stretch;
}


/* =========================================================
   PUNTUACIÓN
========================================================= */

.seo-puntuacion {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 240px;
    padding: 25px;
    box-sizing: border-box;

    border: 1px solid rgba(0, 243, 255, 0.22);
    border-radius: 22px;

    background:
        radial-gradient(
            circle at 50% 35%,
            rgba(0, 243, 255, 0.13),
            rgba(0, 243, 255, 0.035) 42%,
            rgba(3, 12, 21, 0.96) 75%
        );

    box-shadow:
        0 18px 45px rgba(0, 0, 0, 0.25),
        inset 0 1px 0 rgba(255, 255, 255, 0.035);

    overflow: hidden;
}


/* brillo superior */

.seo-puntuacion::before {
    content: "";
    position: absolute;
    top: 0;
    left: 15%;
    width: 70%;
    height: 2px;

    background: linear-gradient(
        90deg,
        transparent,
        #00f3ff,
        transparent
    );

    box-shadow:
        0 0 16px rgba(0, 243, 255, 0.5);
}


/* círculo interior */

.seo-puntuacion::after {
    content: "";
    position: absolute;
    width: 165px;
    height: 165px;

    border: 1px solid rgba(0, 243, 255, 0.12);
    border-radius: 50%;

    pointer-events: none;
}


.seo-puntuacion-label {
    position: relative;
    z-index: 2;

    margin-bottom: 10px;

    color: #8193a5;

    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.6px;
    text-transform: uppercase;
}


.seo-puntuacion strong {
    position: relative;
    z-index: 2;

    color: #ffffff;

    font-size: 43px;
    font-weight: 900;
    line-height: 1;

    letter-spacing: -1.5px;

    text-shadow:
        0 0 25px rgba(0, 243, 255, 0.25);
}


.seo-nivel {
    position: relative;
    z-index: 2;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 70px;
    min-height: 28px;

    margin-top: 12px;
    padding: 4px 13px;

    border: 1px solid rgba(53, 208, 127, 0.28);
    border-radius: 20px;

    background: rgba(53, 208, 127, 0.08);

    color: #5de59a;

    font-size: 11px;
    font-weight: 800;
}


/* =========================================================
   TARJETAS DEL RESUMEN
========================================================= */

.seo-summary-card {
    position: relative;

    display: flex;
    flex-direction: column;
    justify-content: center;

    min-height: 240px;
    padding: 28px;

    box-sizing: border-box;

    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 22px;

    background:
        linear-gradient(
            145deg,
            rgba(11, 27, 42, 0.92),
            rgba(4, 13, 22, 0.96)
        );

    box-shadow:
        0 18px 45px rgba(0, 0, 0, 0.18);

    overflow: hidden;

    transition:
        transform 0.25s ease,
        border-color 0.25s ease,
        box-shadow 0.25s ease;
}


.seo-summary-card::before {
    content: "";

    position: absolute;

    top: 0;
    left: 0;

    width: 55px;
    height: 2px;

    background: #00f3ff;

    box-shadow:
        0 0 14px rgba(0, 243, 255, 0.45);
}


.seo-summary-card::after {
    content: "";

    position: absolute;

    right: -70px;
    bottom: -90px;

    width: 180px;
    height: 180px;

    border-radius: 50%;

    background: rgba(0, 243, 255, 0.035);

    filter: blur(15px);

    pointer-events: none;
}


.seo-summary-card:hover {
    transform: translateY(-3px);

    border-color:
        rgba(0, 243, 255, 0.18);

    box-shadow:
        0 22px 50px rgba(0, 0, 0, 0.25);
}


.seo-summary-label {
    position: relative;
    z-index: 2;

    display: inline-block;

    margin-bottom: 13px;

    color: #00cbd6;

    font-size: 9px;
    font-weight: 900;

    letter-spacing: 1.4px;

    text-transform: uppercase;
}


.seo-summary-card strong {
    position: relative;
    z-index: 2;

    display: block;

    margin-bottom: 11px;

    color: #ffffff;

    font-size: 18px;
    font-weight: 800;

    line-height: 1.35;
}


.seo-summary-card p {
    position: relative;
    z-index: 2;

    margin: 0;

    color: #8191a2;

    font-size: 13px;

    line-height: 1.65;
}


/* =========================================================
   BLOQUES DE RESULTADOS
========================================================= */

.seo-section-block {
    position: relative;

    margin-bottom: 20px;
    padding: 27px;

    border: 1px solid rgba(255, 255, 255, 0.065);
    border-radius: 20px;

    background:
        linear-gradient(
            145deg,
            rgba(7, 19, 31, 0.9),
            rgba(3, 11, 19, 0.94)
        );

    box-shadow:
        0 15px 40px rgba(0, 0, 0, 0.16);

    overflow: hidden;
}


.seo-section-block::before {
    content: "";

    position: absolute;

    top: 0;
    left: 25px;

    width: 80px;
    height: 1px;

    background: #00f3ff;

    box-shadow:
        0 0 12px rgba(0, 243, 255, 0.4);
}


/* =========================================================
   CABECERAS DE SECCIÓN
========================================================= */

.seo-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;

    margin-bottom: 22px;
}


.seo-section-heading span {
    display: block;

    margin-bottom: 6px;

    color: #00cbd6;

    font-size: 9px;
    font-weight: 900;

    letter-spacing: 1.5px;

    text-transform: uppercase;
}


.seo-section-heading h3 {
    margin: 0;

    color: #ffffff;

    font-size: 20px;
    font-weight: 800;

    letter-spacing: -0.3px;
}


.seo-heading-icon {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 42px;
    height: 42px;

    border: 1px solid rgba(0, 243, 255, 0.16);
    border-radius: 12px;

    background:
        rgba(0, 243, 255, 0.045);

    font-size: 18px;
}


/* =========================================================
   RECOMENDACIONES
========================================================= */

#seoListaRecomendaciones {
    display: flex;
    flex-direction: column;

    gap: 10px;
}


.seo-recomendacion {
    position: relative;

    display: grid;

    grid-template-columns: 1fr;

    padding: 17px 19px 17px 21px;

    border: 1px solid rgba(255, 255, 255, 0.06);
    border-left: 3px solid #f5c542;

    border-radius: 13px;

    background:
        rgba(255, 255, 255, 0.025);

    transition:
        transform 0.2s ease,
        background 0.2s ease,
        border-color 0.2s ease;
}


.seo-recomendacion:hover {
    transform: translateX(3px);

    background:
        rgba(255, 255, 255, 0.04);
}


.seo-recomendacion strong {
    display: block;

    margin-bottom: 6px;

    color: #ffffff;

    font-size: 13px;
    font-weight: 800;

    line-height: 1.4;
}


.seo-recomendacion p {
    margin: 0;

    color: #8494a5;

    font-size: 12px;

    line-height: 1.65;
}


/* estados */

.seo-recomendacion.warning {
    border-left-color: #f5c542;
}

.seo-recomendacion.error {
    border-left-color: #ff5c5c;
}

.seo-recomendacion.success {
    border-left-color: #35d07f;
}


/* =========================================================
   MÉTRICAS
========================================================= */

.seo-metricas {
    display: grid;

    grid-template-columns:
        repeat(5, minmax(0, 1fr));

    gap: 11px;

    margin: 0;
}


.seo-metrica {
    position: relative;

    min-height: 112px;

    padding: 18px;

    box-sizing: border-box;

    border: 1px solid rgba(255, 255, 255, 0.065);
    border-radius: 14px;

    background:
        linear-gradient(
            145deg,
            rgba(11, 28, 44, 0.85),
            rgba(5, 14, 24, 0.92)
        );

    overflow: hidden;

    transition:
        transform 0.22s ease,
        border-color 0.22s ease,
        box-shadow 0.22s ease;
}


.seo-metrica::before {
    content: "";

    position: absolute;

    top: 0;
    left: 0;

    width: 30px;
    height: 2px;

    background: #00f3ff;

    box-shadow:
        0 0 10px rgba(0, 243, 255, 0.4);
}


.seo-metrica:hover {
    transform: translateY(-4px);

    border-color:
        rgba(0, 243, 255, 0.2);

    box-shadow:
        0 12px 28px rgba(0, 0, 0, 0.22);
}


.seo-metrica-label {
    display: block;

    margin-bottom: 13px;

    color: #6f8294;

    font-size: 9px;
    font-weight: 800;

    letter-spacing: 0.8px;

    line-height: 1.4;

    text-transform: uppercase;
}


.seo-metrica strong {
    display: block;

    color: #ffffff;

    font-size: 17px;
    font-weight: 800;

    line-height: 1.4;

    word-break: break-word;
}


/* Los valores numéricos y datos importantes */

#seoH1,
#seoH2,
#seoImagenes,
#seoImagenesAlt,
#seoInternos,
#seoExternos,
#seoPalabras {
    color: #00f3ff;
}


/* =========================================================
   TÍTULO Y META DESCRIPCIÓN
========================================================= */

#seoTitulo,
#seoDescripcion,
#seoCanonical {
    color: #dce6ee;

    font-size: 13px;

    line-height: 1.5;
}


/* =========================================================
   H1 / H2
========================================================= */

.seo-details-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 16px;

    margin-top: 20px;
}


.seo-details-card {
    position: relative;

    padding: 25px;

    border: 1px solid rgba(255, 255, 255, 0.065);
    border-radius: 20px;

    background:
        linear-gradient(
            145deg,
            rgba(8, 21, 34, 0.9),
            rgba(3, 11, 19, 0.95)
        );

    box-shadow:
        0 15px 35px rgba(0, 0, 0, 0.14);

    overflow: hidden;
}


.seo-details-card::before {
    content: "";

    position: absolute;

    top: 0;
    left: 0;

    width: 45px;
    height: 2px;

    background: #00f3ff;

    box-shadow:
        0 0 12px rgba(0, 243, 255, 0.4);
}


.seo-details-header {
    display: flex;

    flex-direction: column;

    gap: 5px;

    margin-bottom: 17px;
}


.seo-details-header span {
    color: #00cbd6;

    font-size: 9px;
    font-weight: 900;

    letter-spacing: 1.4px;

    text-transform: uppercase;
}


.seo-details-header strong {
    color: #ffffff;

    font-size: 17px;
    font-weight: 800;
}


.seo-details-card ul {
    display: flex;

    flex-direction: column;

    gap: 8px;

    margin: 0;
    padding: 0;

    list-style: none;
}


.seo-details-card li {
    position: relative;

    padding: 12px 14px 12px 34px;

    border: 1px solid rgba(255, 255, 255, 0.055);
    border-radius: 10px;

    background:
        rgba(255, 255, 255, 0.025);

    color: #93a3b3;

    font-size: 12px;

    line-height: 1.5;

    word-break: break-word;

    transition:
        background 0.2s ease,
        border-color 0.2s ease;
}


.seo-details-card li:hover {
    background:
        rgba(0, 243, 255, 0.035);

    border-color:
        rgba(0, 243, 255, 0.12);
}


.seo-details-card li::before {
    content: "✓";

    position: absolute;

    left: 12px;
    top: 50%;

    transform: translateY(-50%);

    display: flex;

    align-items: center;
    justify-content: center;

    width: 15px;
    height: 15px;

    border-radius: 50%;

    background:
        rgba(0, 243, 255, 0.09);

    color: #00f3ff;

    font-size: 8px;
    font-weight: 900;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1050px) {

    .seo-summary-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .seo-puntuacion {
        min-height: 220px;
    }

    .seo-metricas {
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

}


/* =========================================================
   MÓVIL
========================================================= */

@media (max-width: 700px) {

    #seoResultado {
        margin-top: 32px;
        padding-top: 32px;
    }


    .seo-summary-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }


    .seo-puntuacion {
        min-height: 205px;
    }


    .seo-summary-card {
        min-height: auto;
        padding: 23px;
    }


    .seo-section-block {
        padding: 20px 15px;
        border-radius: 17px;
    }


    .seo-section-heading {
        margin-bottom: 18px;
    }


    .seo-section-heading h3 {
        font-size: 18px;
    }


    .seo-metricas {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 9px;
    }


    .seo-metrica {
        min-height: 100px;
        padding: 14px;
    }


    .seo-metrica strong {
        font-size: 15px;
    }


    .seo-details-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }


    .seo-details-card {
        padding: 20px 16px;
        border-radius: 17px;
    }

}


/* =========================================================
   MÓVIL PEQUEÑO
========================================================= */

@media (max-width: 430px) {

    .seo-metricas {
        grid-template-columns: 1fr;
    }


    .seo-puntuacion strong {
        font-size: 38px;
    }


    .seo-summary-card strong {
        font-size: 16px;
    }


    .seo-section-block {
        padding: 18px 12px;
    }


    .seo-metrica {
        min-height: 88px;
    }

}


/* ==========================================================
   FIN CSS PREMIUM SEO RESTAURADO
========================================================== */


/* ==========================================================
   AVISO DE ACCESO PRIVADO
========================================================== */

.acceso-privado-aviso {

    max-width: 1000px;

    margin: 25px auto;

    padding: 20px 25px;

    color: #ffffff;

    box-sizing: border-box;

    text-align: center;

}

.acceso-privado-aviso strong {

    display: block;

    margin-bottom: 8px;

    color: #00cfe0;

    font-size: 18px;

}

.acceso-privado-aviso p {

    margin: 0 0 15px;

    color: #b7c5d3;

    line-height: 1.6;

}

.boton-iniciar-sesion {

    display: inline-block;

    padding: 10px 18px;

    background: #00cfe0;

    color: #061018;

    text-decoration: none;

    border-radius: 8px;

    font-weight: 700;

}

.boton-iniciar-sesion:hover {

    opacity: 0.9;

}


/* ==========================================================
   AVISO LOGIN / MODAL
========================================================== */

.login-aviso {

    position: fixed;

    inset: 0;

    z-index: 9999;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background: rgba(0, 0, 0, 0.72);

    backdrop-filter: blur(5px);

    box-sizing: border-box;

}

.login-aviso[hidden] {

    display: none;

}

.login-aviso-contenido {

    position: relative;

    width: 100%;

    max-width: 430px;

    padding: 35px 30px;

    background: #0d1a29;

    border: 1px solid #1c3045;

    border-radius: 16px;

    text-align: center;

    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.45);

    box-sizing: border-box;

}

.login-aviso-cerrar {

    position: absolute;

    top: 12px;

    right: 15px;

    width: 35px;

    height: 35px;

    border: none;

    background: transparent;

    color: #b7c5d3;

    font-size: 28px;

    cursor: pointer;

}

.login-aviso-cerrar:hover {

    color: #00cfe0;

}

.login-aviso-icono {

    font-size: 42px;

    margin-bottom: 15px;

}

.login-aviso-contenido h2 {

    margin: 0 0 12px;

    color: #ffffff;

}

.login-aviso-contenido p {

    margin: 0 0 25px;

    color: #b7c5d3;

    line-height: 1.6;

}

.login-aviso-botones {

    display: flex;

    justify-content: center;

    gap: 12px;

    flex-wrap: wrap;

}

.login-aviso-login,
.login-aviso-continuar {

    border: none;

    border-radius: 8px;

    padding: 12px 20px;

    cursor: pointer;

    font-size: 14px;

}

.login-aviso-login {

    background: #00cfe0;

    color: #061018;

    font-weight: 700;

}

.login-aviso-login:hover {

    opacity: 0.9;

}

.login-aviso-continuar {

    background: #1c3045;

    color: #ffffff;

}

.login-aviso-continuar:hover {

    background: #29445d;

}


/* ==========================================================
   RESULTADO SEO
========================================================== */

.seo-diagnostico {

    margin-top: 25px;

    padding: 25px;

    width: 100%;

    max-width: 100%;

    box-sizing: border-box;

    background: rgba(255,255,255,0.025);

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 14px;

    overflow: hidden;

}

.seo-diagnostico h3 {

    margin: 0 0 12px;

    max-width: 100%;

    color: #ffffff;

    font-size: 20px;

    line-height: 1.4;

    overflow-wrap: anywhere;

    word-break: break-word;

}

.seo-diagnostico p {

    margin: 0;

    width: 100%;

    max-width: 100%;

    box-sizing: border-box;

    color: #b7c5d3;

    line-height: 1.8;

    white-space: normal;

    overflow-wrap: anywhere;

    word-break: break-word;

}

.seo-diagnostico * {

    max-width: 100%;

    box-sizing: border-box;

    overflow-wrap: anywhere;

    word-break: break-word;

}


/* ==========================================================
   IDENTIFICACIÓN DE LA AUDITORÍA
========================================================== */

.seo-auditoria-info {

    margin-top: 25px;

    display: grid;

    grid-template-columns: minmax(0, 1fr) 180px;

    gap: 15px;

}

.seo-auditoria-info-card {

    padding: 18px 20px;

    background: rgba(255,255,255,0.025);

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 12px;

    min-width: 0;

}

.seo-auditoria-info-card span {

    display: block;

    margin-bottom: 8px;

    color: #7f92a5;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 0.08em;

    text-transform: uppercase;

}

.seo-auditoria-info-card strong {

    display: block;

    color: #ffffff;

    font-size: 15px;

    line-height: 1.5;

    word-break: break-word;

}


/* ==========================================================
   ESTADO POR ÁREAS
========================================================== */

.seo-areas-section {

    margin-top: 25px;

}

.seo-areas-header {

    margin-bottom: 15px;

}

.seo-areas-header span {

    display: block;

    margin-bottom: 5px;

    color: #00cfe0;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 0.08em;

}

.seo-areas-header h3 {

    margin: 0;

    color: #ffffff;

    font-size: 20px;

}

.seo-areas-grid {

    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 14px;

    max-height: 470px;

    overflow-y: auto;

    overflow-x: hidden;

    padding: 4px;

    margin: 0 -4px;

    scrollbar-width: thin;

    scrollbar-color: #29445d transparent;

}

.seo-areas-grid::-webkit-scrollbar {

    width: 7px;

}

.seo-areas-grid::-webkit-scrollbar-track {

    background: transparent;

}

.seo-areas-grid::-webkit-scrollbar-thumb {

    background: #29445d;

    border-radius: 10px;

}

.seo-area-card {

    position: relative;

    min-height: 145px;

    padding: 20px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,0.035),
            rgba(255,255,255,0.015)
        );

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 14px;

    box-sizing: border-box;

    transition:
        transform 0.2s ease,
        border-color 0.2s ease,
        background 0.2s ease;

}

.seo-area-card:hover {

    transform: translateY(-2px);

    border-color: rgba(0,207,224,0.35);

    background:
        linear-gradient(
            145deg,
            rgba(0,207,224,0.06),
            rgba(255,255,255,0.02)
        );

}

.seo-area-card.success {

    border-left: 3px solid #58d68d;

}

.seo-area-card.warning {

    border-left: 3px solid #ffd166;

}

.seo-area-card.error {

    border-left: 3px solid #ff6464;

}

.seo-area-card-header {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 12px;

}

.seo-area-card-name {

    color: #ffffff;

    font-size: 14px;

    font-weight: 700;

    line-height: 1.4;

}

.seo-area-card-score {

    flex-shrink: 0;

    color: #ffffff;

    font-size: 21px;

    font-weight: 800;

}

.seo-area-card-status {

    display: flex;

    align-items: center;

    gap: 7px;

    margin-top: 13px;

    color: #b7c5d3;

    font-size: 13px;

}

.seo-area-card-status-dot {

    width: 8px;

    height: 8px;

    flex-shrink: 0;

    border-radius: 50%;

    background: #7f92a5;

}

.seo-area-card.success .seo-area-card-status-dot {

    background: #58d68d;

}

.seo-area-card.warning .seo-area-card-status-dot {

    background: #ffd166;

}

.seo-area-card.error .seo-area-card-status-dot {

    background: #ff6464;

}

.seo-area-card-description {

    margin-top: 12px;

    color: #7f92a5;

    font-size: 12px;

    line-height: 1.5;

}


/* ==========================================================
   BLOQUES DE DIAGNÓSTICO
========================================================== */

.seo-bloques-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px;

    margin-top: 25px;

}

.seo-info-card {

    padding: 22px;

    background: rgba(255,255,255,0.025);

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 14px;

}

/* Ocultar tarjetas diagnósticas cuando JS las marca como vacías */
.seo-info-card[hidden] {

    display: none !important;

}

.seo-info-card h4 {

    margin: 0 0 15px;

    color: #ffffff;

    font-size: 17px;

}

.seo-info-card ul {

    margin: 0;

    padding-left: 20px;

    color: #b7c5d3;

    line-height: 1.7;

}

.seo-info-card li {

    margin-bottom: 7px;

}

.seo-info-card .seo-ok {

    color: #7ee2a8;

}

.seo-info-card .seo-warning {

    color: #ffd166;

}

.seo-info-card .seo-critical {

    color: #ff7b7b;

}

.seo-prioridad {

    margin-top: 25px;

}

.seo-prioridad h4 {

    margin: 0 0 15px;

    color: #ffffff;

}

.seo-prioridad-alta {

    border-left: 4px solid #ff6464;

}

.seo-prioridad-media {

    border-left: 4px solid #ffd166;

}

.seo-oportunidades {

    border-left: 4px solid #00cfe0;

}


/* ==========================================================
   PUNTUACIONES
========================================================== */

.seo-score-grid {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 12px;

    margin-top: 20px;

}

.seo-score-card {

    padding: 15px;

    background: rgba(255,255,255,0.025);

    border: 1px solid rgba(255,255,255,0.07);

    border-radius: 10px;

}

.seo-score-card span {

    display: block;

    color: #8fa2b4;

    font-size: 12px;

    margin-bottom: 7px;

}

.seo-score-card strong {

    color: #ffffff;

    font-size: 20px;

}


/* ==========================================================
   ESTRUCTURA
========================================================== */

.seo-estructura {

    margin-top: 20px;

    padding: 18px;

    background: #08131f;

    border-radius: 10px;

    overflow-x: auto;

}

.seo-estructura pre {

    margin: 0;

    color: #b7c5d3;

    font-family: monospace;

    font-size: 13px;

    line-height: 1.6;

    white-space: pre-wrap;

}


/* ==========================================================
   RESUMEN PARA COPIAR
========================================================== */

.seo-resumen-copiar {

    display: flex;

    flex-direction: column;

    gap: 15px;

}

.seo-resumen-copiar textarea {

    width: 100%;

    min-height: 420px;

    resize: vertical;

    box-sizing: border-box;

    padding: 18px;

    background: #07121d;

    border: 1px solid #20364b;

    border-radius: 10px;

    color: #d9e4ee;

    line-height: 1.6;

    font-family: monospace;

}

.seo-metrica strong {

    word-break: break-word;

}


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (max-width: 1050px) {

    .seo-areas-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }

}

@media (max-width: 900px) {

    .seo-auditoria-info {

        grid-template-columns: 1fr;

    }

}

@media (max-width: 800px) {

    .seo-bloques-grid {

        grid-template-columns: 1fr;

    }

    .seo-score-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }

    .seo-areas-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        max-height: 520px;

    }

}

@media (max-width: 550px) {

    .seo-areas-grid {

        grid-template-columns: 1fr;

        max-height: 600px;

    }

}

@media (max-width: 500px) {

    .seo-score-grid {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<!-- ==========================================
     CABECERA
========================================== -->

<header class="header" style="position:fixed;">

<?php include 'menu.php'; ?>

</header>


<!-- ==========================================
     CONTENIDO
========================================== -->

<main class="herramientas-page">

<section class="herramientas-section">

<div class="container">


<!-- ======================================
     CABECERA
======================================= -->

<div class="herramientas-header">

    <h1>

        Auditoría SEO

    </h1>

</div>


<!-- ======================================
     AVISO DE ACCESO PRIVADO
======================================= -->

<?php if (!$usuarioLogueado): ?>

    <div class="acceso-privado-aviso">

        <strong>

            🔐 Auditoría SEO privada

        </strong>

        <p>

            Puedes consultar esta sección sin iniciar sesión.
            Para realizar una auditoría SEO de una página web
            necesitas acceder a tu cuenta.

        </p>

        <a
            href="login.php"
            class="boton-iniciar-sesion"
        >

            Iniciar sesión

        </a>

    </div>

<?php endif; ?>


<!-- ======================================
     BLOQUE PRINCIPAL
======================================= -->

<article class="seo-panel">


<!-- ==================================
     CABECERA
=================================== -->

<div class="seo-panel-header">

    <div>

        <span class="seo-panel-kicker">

            ANÁLISIS SEO

        </span>

        <h2>

            Analiza tu página web y descubre que puedes mejorar.

        </h2>

        <br>

        <p>

           Introduce la URL de tu página y deja que Viziune analice su estado a nivel SEO.

Revisaremos los principales aspectos que pueden influir en la visibilidad y el funcionamiento de tu web: SEO técnico, estructura, contenido, imágenes, enlaces, metadatos, indexación y otros elementos importantes para los buscadores y tus usuarios.

Al finalizar recibirás un diagnóstico completo y fácil de entender, con una valoración general de tu web, los aspectos que están funcionando correctamente, los problemas detectados, las prioridades que conviene revisar primero y las oportunidades que puedes aprovechar para seguir mejorando.

Obtén una visión clara de cómo está tu web, qué necesita mejorar y por dónde empezar.

        </p>

        <br>

    </div>

</div>


<!-- ==================================
     FORMULARIO
=================================== -->

<div class="seo-form">

    <label for="seoUrl">

        URL de tu página web

    </label>

    <div class="seo-input-row">

        <input
            type="url"
            id="seoUrl"
            placeholder="https://www.tuweb.com"
            autocomplete="url"
        >

        <button
            type="button"
            class="herramienta-button"
            id="analizarSeo"
        >

            🔎 Analizar mi web

        </button>

    </div>

</div>


<!-- ==================================
     CARGANDO
=================================== -->

<div
    id="seoLoading"
    class="seo-loading"
    hidden
>

    <div class="seo-loading-spinner"></div>

    <div>

        <strong>

            Analizando tu página web...

        </strong>

        <span>

            Estamos revisando los principales factores técnicos,
            de contenido y estructura.

        </span>

    </div>

</div>


<!-- ==================================
     ERROR
=================================== -->

<div
    id="seoError"
    class="seo-error"
    hidden
></div>


<!-- ==================================
     RESULTADO
=================================== -->

<div
    id="seoResultado"
    class="seo-resultado"
    hidden
>


<!-- ==================================
     RESUMEN SUPERIOR
=================================== -->

<div class="seo-summary-grid">


<div class="seo-puntuacion">

    <div class="seo-puntuacion-label">

        Puntuación SEO

    </div>

    <strong id="seoPuntuacion">

        -

    </strong>

    <div
        id="seoNivel"
        class="seo-nivel"
    >

        -

    </div>

</div>


<div class="seo-summary-card">

    <span class="seo-summary-label">

        ESTADO GENERAL

    </span>

    <strong id="seoEstadoGeneral">

        Análisis completado

    </strong>

    <p id="seoDescripcionGeneral">

        Hemos revisado los principales elementos
        técnicos, de contenido y estructura.

    </p>

</div>


<div class="seo-summary-card">

    <span class="seo-summary-label">

        INFORME

    </span>

    <strong>

        Informe para Viziune

    </strong>

    <p>

        Puedes copiar el informe completo y utilizarlo
        directamente en el chat de Viziune para continuar
        analizando las necesidades de tu proyecto.

    </p>

</div>


</div>


<!-- ==================================
     IDENTIFICACIÓN DE LA AUDITORÍA
=================================== -->

<div class="seo-auditoria-info">

    <div class="seo-auditoria-info-card">

        <span>

            URL ANALIZADA

        </span>

        <strong id="seoUrlResultado">

            -

        </strong>

    </div>


    <div class="seo-auditoria-info-card">

        <span>

            FECHA DEL ANÁLISIS

        </span>

        <strong id="seoFechaAnalisis">

            -

        </strong>

    </div>

</div>


<!-- ==================================
     DIAGNÓSTICO GENERAL
=================================== -->

<div class="seo-diagnostico">

    <h3>

        🧠 Diagnóstico general

    </h3>

    <p id="seoDiagnosticoTexto">

        -

    </p>

</div>


<!-- ==================================
     ESTADO POR ÁREAS
=================================== -->

<div class="seo-areas-section">

    <div class="seo-areas-header">

        <span>

            EVALUACIÓN

        </span>

        <h3>

            Estado por áreas

        </h3>

    </div>


    <div
        id="seoAreasGrid"
        class="seo-areas-grid"
    >

        <!-- Las áreas se generan mediante JavaScript -->

    </div>

</div>


<!-- ==================================
     PUNTUACIONES INTERNAS
=================================== -->

<div class="seo-section-block">

    <div class="seo-section-heading">

        <div>

            <span>

                PUNTUACIÓN

            </span>

            <h3>

                Desglose de puntuación

            </h3>

        </div>

    </div>


    <div class="seo-score-grid">

        <div class="seo-score-card">

            <span>

                SEO técnico

            </span>

            <strong id="scoreTecnico">

                -

            </strong>

        </div>


        <div class="seo-score-card">

            <span>

                SEO on-page

            </span>

            <strong id="scoreOnPage">

                -

            </strong>

        </div>


        <div class="seo-score-card">

            <span>

                Contenido

            </span>

            <strong id="scoreContenido">

                -

            </strong>

        </div>


        <div class="seo-score-card">

            <span>

                Estructura

            </span>

            <strong id="scoreEstructura">

                -

            </strong>

        </div>

    </div>

</div>


<!-- ==================================
     DATOS DE LA AUDITORÍA
=================================== -->

<div class="seo-section-block">

    <div class="seo-section-heading">

        <div>

            <span>

                DATOS

            </span>

            <h3>

                Detalle del análisis

            </h3>

        </div>

    </div>


<div class="seo-metricas">


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Código HTTP

    </span>

    <strong id="seoHttpCode">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        HTTPS

    </span>

    <strong id="seoHttps">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Tiempo de respuesta

    </span>

    <strong id="seoTiempoRespuesta">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Tamaño aproximado

    </span>

    <strong id="seoTamano">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Idioma declarado

    </span>

    <strong id="seoIdioma">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Viewport móvil

    </span>

    <strong id="seoViewport">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Título

    </span>

    <strong id="seoTitulo">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Longitud del título

    </span>

    <strong id="seoLongitudTitulo">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Meta descripción

    </span>

    <strong id="seoDescripcion">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Longitud de meta description

    </span>

    <strong id="seoLongitudDescripcion">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        H1

    </span>

    <strong id="seoH1">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        H2

    </span>

    <strong id="seoH2">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        H3

    </span>

    <strong id="seoH3">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Párrafos

    </span>

    <strong id="seoParrafos">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Contenido aproximado

    </span>

    <strong id="seoPalabras">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Imágenes

    </span>

    <strong id="seoImagenes">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Imágenes sin ALT

    </span>

    <strong id="seoImagenesAlt">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Enlaces internos

    </span>

    <strong id="seoInternos">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Enlaces externos

    </span>

    <strong id="seoExternos">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Canonical

    </span>

    <strong id="seoCanonical">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Robots

    </span>

    <strong id="seoRobots">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        robots.txt

    </span>

    <strong id="seoRobotsTxt">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        sitemap.xml

    </span>

    <strong id="seoSitemap">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Open Graph

    </span>

    <strong id="seoOpenGraph">

        -

    </strong>

</div>


<div class="seo-metrica">

    <span class="seo-metrica-label">

        Twitter Card

    </span>

    <strong id="seoTwitterCard">

        -

    </strong>

</div>


</div>

</div>


<!-- ==================================
     ESTRUCTURA
=================================== -->

<div class="seo-section-block">

    <div class="seo-section-heading">

        <div>

            <span>

                ESTRUCTURA

            </span>

            <h3>

                Encabezados encontrados

            </h3>

        </div>

    </div>


    <div class="seo-details-grid">


        <div class="seo-details-card">

            <div class="seo-details-header">

                <span>

                    H1

                </span>

                <strong>

                    Encabezados principales

                </strong>

            </div>

            <ul id="seoListaH1"></ul>

        </div>


        <div class="seo-details-card">

            <div class="seo-details-header">

                <span>

                    H2

                </span>

                <strong>

                    Subapartados

                </strong>

            </div>

            <ul id="seoListaH2"></ul>

        </div>


        <div class="seo-details-card">

            <div class="seo-details-header">

                <span>

                    H3

                </span>

                <strong>

                    Subniveles

                </strong>

            </div>

            <ul id="seoListaH3"></ul>

        </div>


    </div>


    <div class="seo-estructura">

        <pre id="seoArbolEstructura">-</pre>

    </div>

</div>


<!-- ==================================
     PROBLEMAS / CORRECTOS
=================================== -->

<div class="seo-bloques-grid">


    <div class="seo-info-card">

        <h4>

            🔴 Problemas detectados

        </h4>

        <ul id="seoProblemas"></ul>

    </div>


    <div class="seo-info-card">

        <h4>

            🟢 Aspectos correctos

        </h4>

        <ul id="seoCorrectos"></ul>

    </div>

</div>


<!-- ==================================
     PRIORIDAD ALTA
=================================== -->

<div class="seo-info-card seo-prioridad seo-prioridad-alta">

    <h4>

        🔴 Prioridad alta

    </h4>

    <ul id="seoPrioridadAlta"></ul>

</div>


<!-- ==================================
     PRIORIDAD MEDIA
=================================== -->

<div class="seo-info-card seo-prioridad seo-prioridad-media">

    <h4>

        🟠 Prioridad media

    </h4>

    <ul id="seoPrioridadMedia"></ul>

</div>


<!-- ==================================
     OPORTUNIDADES
=================================== -->

<div class="seo-info-card seo-prioridad seo-oportunidades">

    <h4>

        💡 Oportunidades de mejora

    </h4>

    <ul id="seoOportunidades"></ul>

</div>


<!-- ==================================
     RESUMEN PARA VIZIUNEAI
=================================== -->

<div class="seo-section-block">

    <div class="seo-section-heading">

        <div>

            <span>

                INFORME

            </span>

            <h3>

                Informe completo para Viziune

            </h3>

        </div>

    </div>


    <div class="seo-resumen-copiar">

        <textarea
            id="seoResumenChat"
            readonly
            rows="20"
        ></textarea>


        <button
            type="button"
            class="herramienta-button"
            id="copiarResumenSeo"
        >

            📋 Copiar informe completo

        </button>

    </div>

</div>


</div>


</article>


</div>

</section>

</main>


<!-- ==========================================
     AVISO DE INICIO DE SESIÓN
========================================== -->

<div
    id="loginAviso"
    class="login-aviso"
    hidden
>

    <div class="login-aviso-contenido">

        <button
            type="button"
            id="cerrarLoginAviso"
            class="login-aviso-cerrar"
            aria-label="Cerrar"
        >

            ×

        </button>


        <div class="login-aviso-icono">

            🔐

        </div>


        <h2>

            Inicia sesión

        </h2>


        <p>

            Para utilizar la Auditoría SEO necesitas iniciar sesión.

        </p>


        <div class="login-aviso-botones">

            <button
                type="button"
                id="irLogin"
                class="login-aviso-login"
            >

                Iniciar sesión

            </button>


            <button
                type="button"
                id="seguirSinLogin"
                class="login-aviso-continuar"
            >

                Continuar

            </button>

        </div>

    </div>

</div>


<!-- ==========================================
     FOOTER
========================================== -->

<footer class="footer">

<div class="container">

<p>

    © <?php echo date('Y'); ?> Viziune
    Todos los derechos reservados.

</p>

</div>

</footer>


<!-- ==========================================
     JAVASCRIPT AUDITORÍA SEO
========================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const usuarioLogueado =
            <?php echo $usuarioLogueado ? 'true' : 'false'; ?>;



        function registrarEventoAnalytics(
            nombreEvento,
            parametros = {}
        ) {

            if (
                typeof window.gtag !== "function"
            ) {
                return;
            }

            try {

                window.gtag(
                    "event",
                    nombreEvento,
                    parametros
                );

            } catch (error) {

                console.warn(
                    "Google Analytics no pudo registrar el evento:",
                    error
                );

            }

        }


        const boton =
            document.getElementById("analizarSeo");

        const urlInput =
            document.getElementById("seoUrl");

        const loading =
            document.getElementById("seoLoading");

        const error =
            document.getElementById("seoError");

        const resultado =
            document.getElementById("seoResultado");

        const botonCopiar =
            document.getElementById("copiarResumenSeo");

        const resumenChat =
            document.getElementById("seoResumenChat");

        const loginAviso =
            document.getElementById("loginAviso");

        const cerrarLoginAviso =
            document.getElementById("cerrarLoginAviso");

        const irLogin =
            document.getElementById("irLogin");

        const seguirSinLogin =
            document.getElementById("seguirSinLogin");

        const areasGrid =
            document.getElementById("seoAreasGrid");


        function mostrarAvisoLogin() {

            loginAviso.hidden = false;

        }


        function cerrarAvisoLogin() {

            loginAviso.hidden = true;

        }


        cerrarLoginAviso.addEventListener(
            "click",
            cerrarAvisoLogin
        );


        seguirSinLogin.addEventListener(
            "click",
            cerrarAvisoLogin
        );


        irLogin.addEventListener(
            "click",
            function () {

                window.location.href =
                    "login.php";

            }
        );


        window.mostrarAvisoLogin =
            mostrarAvisoLogin;


        window.usuarioLogueado =
            usuarioLogueado;


        boton.addEventListener(
            "click",
            analizarSEO
        );


        urlInput.addEventListener(
            "keydown",
            function (event) {

                if (event.key === "Enter") {

                    event.preventDefault();

                    analizarSEO();

                }

            }
        );


        botonCopiar.addEventListener(
            "click",
            copiarResumen
        );


        async function analizarSEO() {

            if (!usuarioLogueado) {

                mostrarAvisoLogin();

                return;

            }


            const url =
                urlInput.value.trim();


            if (url === "") {

                mostrarError(
                    "Introduce la dirección de tu página web."
                );

                urlInput.focus();

                return;

            }


            let urlFinal =
                url;


            if (
                !/^https?:\/\//i.test(urlFinal)
            ) {

                urlFinal =
                    "https://" +
                    urlFinal;

            }


            try {

                new URL(urlFinal);

            } catch (e) {

                mostrarError(
                    "La dirección introducida no es válida."
                );

                urlInput.focus();

                return;

            }


            registrarEventoAnalytics(
                "seo_audit_started"
            );


            error.hidden = true;

            resultado.hidden = true;

            loading.hidden = false;

            boton.disabled = true;

            boton.textContent =
                "Analizando...";


            try {

                const response =
                    await fetch(
                        "seo_auditoria.php",
                        {
                            method: "POST",

                            headers: {

                                "Content-Type":
                                    "application/json",

                                "Accept":
                                    "application/json"

                            },

                            body:
                                JSON.stringify({
                                    url: urlFinal
                                })

                        }
                    );


                const texto =
                    await response.text();


                let data;


                try {

                    data =
                        JSON.parse(texto);

                } catch (e) {

                    throw new Error(
                        "El servidor devolvió una respuesta no válida."
                    );

                }


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.error ||
                        "No se pudo realizar el análisis."
                    );

                }


                mostrarResultado(
                    data.analisis
                );


                registrarEventoAnalytics(
                    "seo_audit_completed"
                );


            } catch (e) {

                registrarEventoAnalytics(
                    "seo_audit_error"
                );

                mostrarError(
                    e.message ||
                    "Se produjo un error durante el análisis."
                );


            } finally {

                loading.hidden = true;

                boton.disabled = false;

                boton.textContent =
                    "Analizar mi web";

            }

        }


        function mostrarError(mensaje) {

            error.textContent =
                "❌ " + mensaje;

            error.hidden = false;

            resultado.hidden = true;

        }


        function pintarLista(
            id,
            elementos,
            textoVacio,
            ocultarSiVacio = false
        ) {

            const lista =
                document.getElementById(id);

            if (!lista) return;

            const tarjeta =
                lista.closest(".seo-info-card");

            lista.innerHTML = "";

            const tieneContenido =
                Array.isArray(elementos) &&
                elementos.length > 0;


            /*
            |----------------------------------------------------------
            | OCULTAR TARJETAS VACÍAS
            |----------------------------------------------------------
            | Las tarjetas de problemas, correctos y prioridades no se
            | muestran cuando la auditoría no devuelve contenido para
            | ellas. Las listas de H1/H2/H3 siguen mostrando su mensaje
            | informativo cuando están vacías.
            */

            if (tarjeta && ocultarSiVacio) {

                tarjeta.hidden = !tieneContenido;

            }


            if (tieneContenido) {

                elementos.forEach(
                    function (texto) {

                        const li =
                            document.createElement("li");

                        li.textContent =
                            texto;

                        lista.appendChild(li);

                    }
                );

            } else if (!ocultarSiVacio) {

                const li =
                    document.createElement("li");

                li.textContent =
                    textoVacio;

                lista.appendChild(li);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | CREAR TARJETAS DE ESTADO POR ÁREA
        |--------------------------------------------------------------------------
        */

        function crearArea(
            nombre,
            puntuacion,
            maximo,
            descripcion
        ) {

            const porcentaje =
                maximo > 0
                    ? (puntuacion / maximo) * 100
                    : 0;


            let clase =
                "error";

            let estado =
                "Necesita atención";


            if (porcentaje >= 80) {

                clase =
                    "success";

                estado =
                    "Correcto";

            } else if (porcentaje >= 55) {

                clase =
                    "warning";

                estado =
                    "Mejorable";

            }


            const tarjeta =
                document.createElement("div");

            tarjeta.className =
                "seo-area-card " + clase;


            const encabezado =
                document.createElement("div");

            encabezado.className =
                "seo-area-card-header";


            const nombreElemento =
                document.createElement("div");

            nombreElemento.className =
                "seo-area-card-name";

            nombreElemento.textContent =
                nombre;


            const puntuacionElemento =
                document.createElement("div");

            puntuacionElemento.className =
                "seo-area-card-score";

            puntuacionElemento.textContent =
                puntuacion + "/" + maximo;


            encabezado.appendChild(
                nombreElemento
            );

            encabezado.appendChild(
                puntuacionElemento
            );


            const estadoElemento =
                document.createElement("div");

            estadoElemento.className =
                "seo-area-card-status";


            const punto =
                document.createElement("span");

            punto.className =
                "seo-area-card-status-dot";


            const textoEstado =
                document.createElement("span");

            textoEstado.textContent =
                estado;


            estadoElemento.appendChild(
                punto
            );

            estadoElemento.appendChild(
                textoEstado
            );


            const descripcionElemento =
                document.createElement("div");

            descripcionElemento.className =
                "seo-area-card-description";

            descripcionElemento.textContent =
                descripcion || "";


            tarjeta.appendChild(
                encabezado
            );

            tarjeta.appendChild(
                estadoElemento
            );

            tarjeta.appendChild(
                descripcionElemento
            );


            areasGrid.appendChild(
                tarjeta
            );

        }


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR ÁREAS
        |--------------------------------------------------------------------------
        */

        function mostrarAreas(datos) {

            areasGrid.innerHTML = "";


            const puntuaciones =
                datos.puntuaciones || {};


            crearArea(
                "SEO técnico",
                Number(
                    puntuaciones.tecnico ?? 0
                ),
                25,
                "HTTPS, respuesta, robots, canonical y elementos técnicos."
            );


            crearArea(
                "SEO on-page",
                Number(
                    puntuaciones.on_page ?? 0
                ),
                25,
                "Título, meta descripción y elementos principales de la página."
            );


            crearArea(
                "Contenido",
                Number(
                    puntuaciones.contenido ?? 0
                ),
                20,
                "Cantidad de contenido, imágenes, ALT y señales de contenido."
            );


            crearArea(
                "Estructura",
                Number(
                    puntuaciones.estructura ?? 0
                ),
                10,
                "Jerarquía de encabezados y organización del contenido."
            );


            const imagenes =
                Number(
                    datos.imagenes_total ?? 0
                );

            const imagenesSinAlt =
                Number(
                    datos.imagenes_sin_alt ?? 0
                );

            let scoreImagenes =
                0;


            if (imagenes === 0) {

                scoreImagenes = 10;

            } else {

                scoreImagenes =
                    Math.round(
                        (
                            (imagenes - imagenesSinAlt) /
                            imagenes
                        ) * 10
                    );

            }


            crearArea(
                "Imágenes",
                scoreImagenes,
                10,
                imagenes === 0
                    ? "No se han encontrado imágenes."
                    : imagenesSinAlt === 0
                        ? "Todas las imágenes tienen atributo ALT."
                        : imagenesSinAlt +
                          " imagen(es) no tienen ALT."
            );


            const internos =
                Number(
                    datos.enlaces_internos ?? 0
                );

            const externos =
                Number(
                    datos.enlaces_externos ?? 0
                );

            const totalEnlaces =
                internos + externos;


            let scoreEnlaces =
                0;


            if (totalEnlaces > 0) {

                scoreEnlaces =
                    Math.min(
                        10,
                        Math.round(
                            Math.min(
                                totalEnlaces / 10,
                                1
                            ) * 10
                        )
                    );

            }


            crearArea(
                "Enlaces",
                scoreEnlaces,
                10,
                internos +
                " internos · " +
                externos +
                " externos."
            );


            let scoreIndexacion =
                0;

            let detalleIndexacion =
                "Revisar configuración de indexación.";


            if (
                datos.robots &&
                !String(
                    datos.robots
                ).toLowerCase().includes(
                    "noindex"
                )
            ) {

                scoreIndexacion += 5;

            }


            if (datos.robots_txt) {

                scoreIndexacion += 2;

            }


            if (datos.sitemap) {

                scoreIndexacion += 3;

            }


            if (scoreIndexacion >= 8) {

                detalleIndexacion =
                    "Elementos de indexación detectados correctamente.";

            } else if (scoreIndexacion >= 5) {

                detalleIndexacion =
                    "La configuración de indexación puede mejorarse.";

            }


            crearArea(
                "Indexación",
                scoreIndexacion,
                10,
                detalleIndexacion
            );


            let scoreSocial =
                0;


            const openGraph =
                String(
                    datos.open_graph_estado || ""
                ).toLowerCase();


            const twitterCard =
                String(
                    datos.twitter_card || ""
                ).toLowerCase();


            if (
                openGraph &&
                !openGraph.includes(
                    "no detect"
                )
            ) {

                scoreSocial += 5;

            }


            if (
                twitterCard &&
                !twitterCard.includes(
                    "no detect"
                )
            ) {

                scoreSocial += 5;

            }


            crearArea(
                "Metadatos sociales",
                scoreSocial,
                10,
                "Open Graph y Twitter Card."
            );


            crearArea(
                "Experiencia móvil",
                datos.viewport ? 10 : 0,
                10,
                datos.viewport
                    ? "Se ha detectado viewport móvil."
                    : "No se ha detectado una configuración viewport."
            );

        }


        function mostrarResultado(datos) {

            /*
            |--------------------------------------------------------------------------
            | PUNTUACIÓN GENERAL
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoPuntuacion"
            ).textContent =
                (datos.puntuacion ?? 0) +
                "/100";


            document.getElementById(
                "seoNivel"
            ).textContent =
                datos.valoracion ||
                "Sin valorar";


            document.getElementById(
                "seoEstadoGeneral"
            ).textContent =
                datos.valoracion ||
                "Análisis completado";


            document.getElementById(
                "seoDescripcionGeneral"
            ).textContent =
                datos.descripcion_valoracion ||
                "Análisis completado.";


            /*
            |--------------------------------------------------------------------------
            | DIAGNÓSTICO
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoDiagnosticoTexto"
            ).textContent =
                datos.resumen_general ||
                "No se ha podido generar el diagnóstico.";


            /*
            |--------------------------------------------------------------------------
            | URL
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoUrlResultado"
            ).textContent =
                datos.url ||
                "-";


            /*
            |--------------------------------------------------------------------------
            | FECHA
            |--------------------------------------------------------------------------
            */

            const ahora =
                new Date();


            document.getElementById(
                "seoFechaAnalisis"
            ).textContent =
                ahora.toLocaleDateString(
                    "es-ES",
                    {
                        day: "2-digit",
                        month: "2-digit",
                        year: "numeric"
                    }
                ) +
                " · " +
                ahora.toLocaleTimeString(
                    "es-ES",
                    {
                        hour: "2-digit",
                        minute: "2-digit"
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | DATOS TÉCNICOS
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoHttpCode"
            ).textContent =
                datos.http_code || "-";


            document.getElementById(
                "seoHttps"
            ).textContent =
                datos.https
                    ? "Sí"
                    : "No";


            document.getElementById(
                "seoTiempoRespuesta"
            ).textContent =
                datos.tiempo_respuesta_ms !== null
                    ? datos.tiempo_respuesta_ms +
                      " ms"
                    : "No disponible";


            document.getElementById(
                "seoTamano"
            ).textContent =
                datos.tamano_pagina_formateado ||
                "No disponible";


            document.getElementById(
                "seoIdioma"
            ).textContent =
                datos.idioma ||
                "No declarado";


            document.getElementById(
                "seoViewport"
            ).textContent =
                datos.viewport
                    ? "Detectado"
                    : "No detectado";


            /*
            |--------------------------------------------------------------------------
            | SEO ON-PAGE
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoTitulo"
            ).textContent =
                datos.titulo ||
                "No encontrado";


            document.getElementById(
                "seoLongitudTitulo"
            ).textContent =
                (datos.longitud_titulo ?? 0) +
                " caracteres";


            document.getElementById(
                "seoDescripcion"
            ).textContent =
                datos.meta_description ||
                "No encontrada";


            document.getElementById(
                "seoLongitudDescripcion"
            ).textContent =
                (datos.longitud_meta_description ?? 0) +
                " caracteres";


            /*
            |--------------------------------------------------------------------------
            | ESTRUCTURA
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoH1"
            ).textContent =
                datos.numero_h1 ?? 0;


            document.getElementById(
                "seoH2"
            ).textContent =
                datos.numero_h2 ?? 0;


            document.getElementById(
                "seoH3"
            ).textContent =
                datos.numero_h3 ?? 0;


            document.getElementById(
                "seoParrafos"
            ).textContent =
                datos.parrafos ?? 0;


            document.getElementById(
                "seoPalabras"
            ).textContent =
                datos.palabras_aproximadas ?? 0;


            /*
            |--------------------------------------------------------------------------
            | IMÁGENES Y ENLACES
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoImagenes"
            ).textContent =
                datos.imagenes_total ?? 0;


            document.getElementById(
                "seoImagenesAlt"
            ).textContent =
                datos.imagenes_sin_alt ?? 0;


            document.getElementById(
                "seoInternos"
            ).textContent =
                datos.enlaces_internos ?? 0;


            document.getElementById(
                "seoExternos"
            ).textContent =
                datos.enlaces_externos ?? 0;


            /*
            |--------------------------------------------------------------------------
            | INDEXACIÓN
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoCanonical"
            ).textContent =
                datos.canonical ||
                "No encontrada";


            document.getElementById(
                "seoRobots"
            ).textContent =
                datos.robots ||
                "No detectado";


            document.getElementById(
                "seoRobotsTxt"
            ).textContent =
                datos.robots_txt
                    ? "Detectado"
                    : "No detectado";


            document.getElementById(
                "seoSitemap"
            ).textContent =
                datos.sitemap
                    ? "Detectado"
                    : "No detectado";


            /*
            |--------------------------------------------------------------------------
            | REDES SOCIALES
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                "seoOpenGraph"
            ).textContent =
                datos.open_graph_estado ||
                "No detectado";


            document.getElementById(
                "seoTwitterCard"
            ).textContent =
                datos.twitter_card ||
                "No detectada";


            /*
            |--------------------------------------------------------------------------
            | PUNTUACIONES
            |--------------------------------------------------------------------------
            */

            const puntuaciones =
                datos.puntuaciones || {};


            document.getElementById(
                "scoreTecnico"
            ).textContent =
                (puntuaciones.tecnico ?? 0) +
                "/25";


            document.getElementById(
                "scoreOnPage"
            ).textContent =
                (puntuaciones.on_page ?? 0) +
                "/25";


            document.getElementById(
                "scoreContenido"
            ).textContent =
                (puntuaciones.contenido ?? 0) +
                "/20";


            document.getElementById(
                "scoreEstructura"
            ).textContent =
                (puntuaciones.estructura ?? 0) +
                "/10";


            /*
            |--------------------------------------------------------------------------
            | LISTAS DE ENCABEZADOS
            |--------------------------------------------------------------------------
            */

            pintarLista(
                "seoListaH1",
                datos.h1,
                "No se han encontrado H1."
            );


            pintarLista(
                "seoListaH2",
                datos.h2,
                "No se han encontrado H2."
            );


            pintarLista(
                "seoListaH3",
                datos.h3,
                "No se han encontrado H3."
            );


            document.getElementById(
                "seoArbolEstructura"
            ).textContent =
                datos.arbol_estructura ||
                "No se ha podido generar el árbol de estructura.";


            /*
            |--------------------------------------------------------------------------
            | DIAGNÓSTICOS
            |--------------------------------------------------------------------------
            */

            pintarLista(
                "seoProblemas",
                datos.problemas,
                "No se han detectado problemas importantes.",
                true
            );


            pintarLista(
                "seoCorrectos",
                datos.correctos,
                "No se han registrado aspectos destacados.",
                true
            );


            pintarLista(
                "seoPrioridadAlta",
                datos.prioridad_alta,
                "No se han detectado prioridades altas.",
                true
            );


            pintarLista(
                "seoPrioridadMedia",
                datos.prioridad_media,
                "No se han detectado prioridades medias.",
                true
            );


            pintarLista(
                "seoOportunidades",
                datos.oportunidades,
                "No se han detectado oportunidades adicionales.",
                true
            );


            /*
            |--------------------------------------------------------------------------
            | INFORME COMPLETO PARA VIZIUNEAI
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            | El informe se divide en dos partes:
            |
            | 1. INSTRUCCIONES:
            |    Explican a ViziuneAI cómo debe interpretar los datos.
            |
            | 2. DATOS:
            |    Contienen los resultados reales de la auditoría.
            |
            */

            let resumen = "";


            /*
            |--------------------------------------------------------------------------
            | CONTEXTO
            |--------------------------------------------------------------------------
            */


            resumen +=
                "CONTEXTO PARA VIZIUNE\n";


            resumen +=
                "El contenido que aparece a continuación procede de una auditoría SEO automática realizada sobre una página web.\n\n";


            resumen +=
                "Este documento debe utilizarse como CONTEXTO TÉCNICO para responder a las preguntas del usuario relacionadas con esta página web, su SEO, estructura, contenido, indexación, rendimiento y oportunidades de mejora.\n\n";


            /*
            |--------------------------------------------------------------------------
            | INSTRUCCIONES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "INSTRUCCIONES PARA VIZIUNE\n";


            resumen +=
                "ROL:\n";

            resumen +=
                "Actúa como un asesor especializado en SEO, desarrollo web y optimización de páginas web.\n\n";


            resumen +=
                "OBJETIVO:\n";

            resumen +=
                "Utiliza los resultados de esta auditoría para ayudar al usuario a comprender el estado actual de su página web y determinar qué aspectos debería revisar, corregir u optimizar.\n\n";


            resumen +=
                "REGLAS DE INTERPRETACIÓN:\n\n";


            resumen +=
                "1. Utiliza los datos de esta auditoría como fuente principal de contexto sobre la página analizada.\n";


            resumen +=
                "2. No inventes datos, errores, configuraciones, métricas o problemas que no aparezcan en la auditoría.\n";


            resumen +=
                "3. Si un dato no está disponible, indica claramente que no ha sido comprobado o que la auditoría no dispone de esa información.\n";


            resumen +=
                "4. No supongas que un elemento está mal configurado simplemente porque no exista información suficiente para comprobarlo.\n";


            resumen +=
                "5. Interpreta los datos. No te limites a repetirlos.\n";


            resumen +=
                "6. Explica qué significa cada problema detectado y qué consecuencias puede tener para el SEO, la indexación, la experiencia del usuario o la visibilidad de la página.\n";


            resumen +=
                "7. Propón acciones concretas y realistas para solucionar los problemas detectados.\n";


            resumen +=
                "8. Da prioridad a los elementos incluidos en PRIORIDAD ALTA.\n";


            resumen +=
                "9. Después analiza los elementos incluidos en PRIORIDAD MEDIA.\n";


            resumen +=
                "10. Utiliza las OPORTUNIDADES DE MEJORA como acciones complementarias.\n";


            resumen +=
                "11. Ten en cuenta también los ASPECTOS CORRECTOS para saber qué elementos ya funcionan correctamente y evitar recomendar cambios innecesarios.\n";


            resumen +=
                "12. Cuando existan varios problemas relacionados entre sí, agrúpalos y evita recomendar acciones duplicadas.\n";


            resumen +=
                "13. Si el usuario pregunta qué debería solucionar primero, utiliza las prioridades de la auditoría y explica el motivo del orden propuesto.\n";


            resumen +=
                "14. Si el usuario quiere mejorar la web paso a paso, convierte los resultados de la auditoría en un plan de trabajo ordenado.\n";


            resumen +=
                "15. Si el usuario solicita una solución técnica, explica qué debería modificarse y por qué.\n";


            resumen +=
                "16. Si para solucionar un problema necesitas ver código, archivos o configuración que no aparecen en este informe, solicita al usuario esa información en lugar de inventarla.\n";


            resumen +=
                "17. Distingue siempre entre DATOS DETECTADOS, INTERPRETACIÓN y RECOMENDACIONES.\n";


            resumen +=
                "18. No confundas la puntuación general con las puntuaciones parciales.\n";


            resumen +=
                "19. Interpreta las puntuaciones junto con los problemas y datos concretos de la auditoría.\n";


            resumen +=
                "20. Utiliza un lenguaje claro y comprensible para el usuario, evitando tecnicismos innecesarios.\n\n";


            /*
            |--------------------------------------------------------------------------
            | FORMA DE RESPONDER
            |--------------------------------------------------------------------------
            */

            resumen +=
                "FORMA DE RESPONDER AL USUARIO\n";


            resumen +=
                "Cuando el usuario pregunte por esta auditoría:\n\n";


            resumen +=
                "1. Comienza explicando brevemente el estado general de la página según la puntuación y el diagnóstico.\n";


            resumen +=
                "2. Identifica los problemas más importantes.\n";


            resumen +=
                "3. Explica por qué cada problema puede ser relevante.\n";


            resumen +=
                "4. Indica cómo podría solucionarse.\n";


            resumen +=
                "5. Prioriza las acciones para que el usuario sepa por dónde empezar.\n";


            resumen +=
                "6. Indica qué elementos ya están correctamente configurados.\n";


            resumen +=
                "7. Explica las oportunidades de mejora que puedan aportar valor adicional.\n";


            resumen +=
                "8. Si el usuario solicita ayuda para realizar las mejoras, ofrece instrucciones prácticas basadas en los datos disponibles.\n\n";


            /*
            |--------------------------------------------------------------------------
            | REGLA FUNDAMENTAL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "REGLA FUNDAMENTAL:\n";

            resumen +=
                "Si la auditoría no contiene información suficiente para responder con seguridad a una pregunta concreta, debes indicarlo claramente y solicitar los datos necesarios. No debes inventar información para completar la respuesta.\n\n";


            /*
            |--------------------------------------------------------------------------
            | OBJETIVO FINAL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "OBJETIVO FINAL:\n";

            resumen +=
                "Transformar los resultados técnicos de esta auditoría en una explicación comprensible y en acciones concretas que ayuden al usuario a mejorar progresivamente su página web.\n\n";


            /*
            |--------------------------------------------------------------------------
            | DATOS DE LA AUDITORÍA
            |--------------------------------------------------------------------------
            */

            resumen +=
                "DATOS REALES DE LA AUDITORÍA SEO\n";


            resumen +=
                "AUDITORÍA SEO\n\n";


            resumen +=
                "URL: " +
                (datos.url || "-") +
                "\n";


            resumen +=
                "PUNTUACIÓN GENERAL: " +
                (datos.puntuacion ?? 0) +
                "/100\n";


            resumen +=
                "VALORACIÓN: " +
                (datos.valoracion || "-") +
                "\n";


            resumen +=
                "DESCRIPCIÓN DE LA VALORACIÓN: " +
                (datos.descripcion_valoracion || "-") +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | DIAGNÓSTICO GENERAL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "DIAGNÓSTICO GENERAL:\n";


            resumen +=
                (datos.resumen_general || "-") +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | PUNTUACIONES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "PUNTUACIONES POR ÁREA:\n";


            resumen +=
                "- SEO técnico: " +
                (puntuaciones.tecnico ?? 0) +
                "/25\n";


            resumen +=
                "- SEO on-page: " +
                (puntuaciones.on_page ?? 0) +
                "/25\n";


            resumen +=
                "- Contenido: " +
                (puntuaciones.contenido ?? 0) +
                "/20\n";


            resumen +=
                "- Estructura: " +
                (puntuaciones.estructura ?? 0) +
                "/10\n\n";


            /*
            |--------------------------------------------------------------------------
            | DATOS TÉCNICOS
            |--------------------------------------------------------------------------
            */

            resumen +=
                "DATOS TÉCNICOS:\n";


            resumen +=
                "- Código HTTP: " +
                (datos.http_code || "-") +
                "\n";


            resumen +=
                "- HTTPS: " +
                (datos.https ? "Sí" : "No") +
                "\n";


            resumen +=
                "- Tiempo de respuesta: " +
                (
                    datos.tiempo_respuesta_ms !== null
                        ? datos.tiempo_respuesta_ms + " ms"
                        : "No disponible"
                ) +
                "\n";


            resumen +=
                "- Tamaño: " +
                (
                    datos.tamano_pagina_formateado ||
                    "No disponible"
                ) +
                "\n";


            resumen +=
                "- Idioma: " +
                (
                    datos.idioma ||
                    "No declarado"
                ) +
                "\n";


            resumen +=
                "- Viewport: " +
                (
                    datos.viewport
                        ? "Detectado"
                        : "No detectado"
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | SEO ON-PAGE
            |--------------------------------------------------------------------------
            */

            resumen +=
                "SEO ON-PAGE:\n";


            resumen +=
                "- Título: " +
                (
                    datos.titulo ||
                    "No encontrado"
                ) +
                "\n";


            resumen +=
                "- Longitud título: " +
                (
                    datos.longitud_titulo ?? 0
                ) +
                " caracteres\n";


            resumen +=
                "- Meta description: " +
                (
                    datos.meta_description ||
                    "No encontrada"
                ) +
                "\n";


            resumen +=
                "- Longitud meta description: " +
                (
                    datos.longitud_meta_description ?? 0
                ) +
                " caracteres\n\n";


            /*
            |--------------------------------------------------------------------------
            | ESTRUCTURA
            |--------------------------------------------------------------------------
            */

            resumen +=
                "ESTRUCTURA:\n";


            resumen +=
                "- H1: " +
                (
                    datos.numero_h1 ?? 0
                ) +
                "\n";


            resumen +=
                "- H2: " +
                (
                    datos.numero_h2 ?? 0
                ) +
                "\n";


            resumen +=
                "- H3: " +
                (
                    datos.numero_h3 ?? 0
                ) +
                "\n";


            resumen +=
                "- Párrafos: " +
                (
                    datos.parrafos ?? 0
                ) +
                "\n";


            resumen +=
                "- Palabras aproximadas: " +
                (
                    datos.palabras_aproximadas ?? 0
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | ENCABEZADOS
            |--------------------------------------------------------------------------
            */

            resumen +=
                "ENCABEZADOS ENCONTRADOS:\n\n";


            resumen +=
                "H1:\n";


            if (
                Array.isArray(datos.h1) &&
                datos.h1.length > 0
            ) {

                datos.h1.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No se han encontrado H1.\n";

            }


            resumen +=
                "\nH2:\n";


            if (
                Array.isArray(datos.h2) &&
                datos.h2.length > 0
            ) {

                datos.h2.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No se han encontrado H2.\n";

            }


            resumen +=
                "\nH3:\n";


            if (
                Array.isArray(datos.h3) &&
                datos.h3.length > 0
            ) {

                datos.h3.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No se han encontrado H3.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | ÁRBOL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nÁRBOL DE ESTRUCTURA:\n";


            resumen +=
                (
                    datos.arbol_estructura ||
                    "No disponible"
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | IMÁGENES Y ENLACES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "IMÁGENES Y ENLACES:\n";


            resumen +=
                "- Imágenes: " +
                (
                    datos.imagenes_total ?? 0
                ) +
                "\n";


            resumen +=
                "- Imágenes sin ALT: " +
                (
                    datos.imagenes_sin_alt ?? 0
                ) +
                "\n";


            resumen +=
                "- Enlaces internos: " +
                (
                    datos.enlaces_internos ?? 0
                ) +
                "\n";


            resumen +=
                "- Enlaces externos: " +
                (
                    datos.enlaces_externos ?? 0
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | INDEXACIÓN
            |--------------------------------------------------------------------------
            */

            resumen +=
                "INDEXACIÓN:\n";


            resumen +=
                "- Canonical: " +
                (
                    datos.canonical ||
                    "No encontrada"
                ) +
                "\n";


            resumen +=
                "- Robots: " +
                (
                    datos.robots ||
                    "No detectado"
                ) +
                "\n";


            resumen +=
                "- robots.txt: " +
                (
                    datos.robots_txt
                        ? "Detectado"
                        : "No detectado"
                ) +
                "\n";


            resumen +=
                "- sitemap.xml: " +
                (
                    datos.sitemap
                        ? "Detectado"
                        : "No detectado"
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | METADATOS SOCIALES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "METADATOS SOCIALES:\n";


            resumen +=
                "- Open Graph: " +
                (
                    datos.open_graph_estado ||
                    "No detectado"
                ) +
                "\n";


            resumen +=
                "- Twitter Card: " +
                (
                    datos.twitter_card ||
                    "No detectada"
                ) +
                "\n\n";


            /*
            |--------------------------------------------------------------------------
            | PROBLEMAS
            |--------------------------------------------------------------------------
            */

            resumen +=
                "PROBLEMAS DETECTADOS:\n";


            if (
                Array.isArray(datos.problemas) &&
                datos.problemas.length > 0
            ) {

                datos.problemas.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- Ninguno destacado.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | CORRECTOS
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nASPECTOS CORRECTOS:\n";


            if (
                Array.isArray(datos.correctos) &&
                datos.correctos.length > 0
            ) {

                datos.correctos.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No especificados.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | PRIORIDAD ALTA
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nPRIORIDAD ALTA:\n";


            if (
                Array.isArray(datos.prioridad_alta) &&
                datos.prioridad_alta.length > 0
            ) {

                datos.prioridad_alta.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- Ninguna.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | PRIORIDAD MEDIA
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nPRIORIDAD MEDIA:\n";


            if (
                Array.isArray(datos.prioridad_media) &&
                datos.prioridad_media.length > 0
            ) {

                datos.prioridad_media.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- Ninguna.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | OPORTUNIDADES
            |--------------------------------------------------------------------------
            */

            resumen +=
                "\nOPORTUNIDADES DE MEJORA:\n";


            if (
                Array.isArray(datos.oportunidades) &&
                datos.oportunidades.length > 0
            ) {

                datos.oportunidades.forEach(
                    function (item) {

                        resumen +=
                            "- " +
                            item +
                            "\n";

                    }
                );

            } else {

                resumen +=
                    "- No especificadas.\n";

            }


            /*
            |--------------------------------------------------------------------------
            | INSTRUCCIÓN FINAL
            |--------------------------------------------------------------------------
            */

            resumen +=
                "INSTRUCCIÓN FINAL PARA VIZIUNE\n";


            resumen +=
                "Utiliza toda la información anterior como contexto de esta auditoría SEO.\n";


            resumen +=
                "Cuando el usuario haga preguntas relacionadas con esta web, analiza primero estos datos antes de responder.\n";


            resumen +=
                "No te limites a repetir el informe: interpreta los resultados, explica los problemas y proporciona recomendaciones prácticas.\n";


            resumen +=
                "Si el usuario quiere solucionar los problemas, conviértelos en acciones concretas y ordénalas según su prioridad e impacto.\n";


            resumen +=
                "Si necesitas información que no aparece en este informe, solicítala al usuario antes de realizar suposiciones.\n";


            resumenChat.value =
                resumen;


            /*
            |--------------------------------------------------------------------------
            | MOSTRAR ÁREAS
            |--------------------------------------------------------------------------
            */

            mostrarAreas(
                datos
            );


            resultado.hidden =
                false;


            resultado.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });

        }


        async function copiarResumen() {

            const texto =
                resumenChat.value.trim();


            if (texto === "") {

                return;

            }


            try {

                await navigator.clipboard.writeText(
                    texto
                );


                const textoOriginal =
                    botonCopiar.textContent;


                botonCopiar.textContent =
                    "✅ Informe copiado";


                setTimeout(
                    function () {

                        botonCopiar.textContent =
                            textoOriginal;

                    },
                    2000
                );


            } catch (e) {

                resumenChat.select();

                document.execCommand(
                    "copy"
                );


                const textoOriginal =
                    botonCopiar.textContent;


                botonCopiar.textContent =
                    "✅ Informe copiado";


                setTimeout(
                    function () {

                        botonCopiar.textContent =
                            textoOriginal;

                    },
                    2000
                );

            }

        }

    }
);

</script>


</body>

</html>