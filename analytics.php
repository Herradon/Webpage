<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\RunRealtimeReportRequest;
use Google\Auth\Credentials\UserRefreshCredentials;


// ======================================================
// 1. RESPUESTA JSON
// ======================================================

header('Content-Type: application/json; charset=utf-8');


// ======================================================
// 2. COMPROBAR USUARIO
// ======================================================

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'error' => 'Usuario no conectado.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];


// ======================================================
// 3. OBTENER PERIODO
// ======================================================

$periodo = isset($_GET['periodo'])
    ? (int) $_GET['periodo']
    : 30;

if (!in_array($periodo, [7, 30, 90], true)) {
    $periodo = 30;
}


// ======================================================
// 4. BUSCAR CONEXIÓN GOOGLE ANALYTICS
// ======================================================

$stmt = $pdo->prepare("
    SELECT
        access_token,
        refresh_token,
        token_expira_en,
        property_id
    FROM google_analytics_conexiones
    WHERE usuario_id = ?
    LIMIT 1
");

$stmt->execute([$usuario_id]);

$conexion = $stmt->fetch();


// ======================================================
// 5. COMPROBAR CONEXIÓN
// ======================================================

if (!$conexion) {

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'error' => 'No existe una conexión de Google Analytics para este usuario.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (empty($conexion['refresh_token'])) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error' => 'No existe un refresh token de Google Analytics.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


if (empty($conexion['property_id'])) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error' => 'No hay ninguna propiedad de Google Analytics seleccionada.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================================
// 6. CREDENCIALES OAUTH
// ======================================================

$scopes = [
    'https://www.googleapis.com/auth/analytics.readonly'
];

$credentials = new UserRefreshCredentials(
    $scopes,
    [
        'client_id' =>
            '308953097944-3m125i1nmler7gs8l8d4e977mrvt6pa4.apps.googleusercontent.com',

        'client_secret' =>
            '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',

        'refresh_token' =>
            $conexion['refresh_token'],

        'type' =>
            'authorized_user'
    ]
);


// ======================================================
// 7. CLIENTE GOOGLE ANALYTICS
// ======================================================

$analyticsData = new BetaAnalyticsDataClient([
    'credentials' => $credentials,
    'transport' => 'rest'
]);


// ======================================================
// 8. PROPIEDAD
// ======================================================

$propertyId = $conexion['property_id'];


// ======================================================
// 9. FECHAS
// ======================================================

$startDate = $periodo . 'daysAgo';

$endDate = 'yesterday';


// ======================================================
// FUNCIONES AUXILIARES
// ======================================================

function obtenerValorMetric(
    $row,
    int $indice,
    int $porDefecto = 0
): int {

    $values = $row->getMetricValues();

    if (isset($values[$indice])) {

        return (int) $values[$indice]->getValue();
    }

    return $porDefecto;
}


function obtenerFilasDimension(
    $response,
    string $nombreDimension,
    string $nombreMetrica,
    int $limite = 10
): array {

    $resultado = [];

    if ($response->getRowCount() <= 0) {
        return $resultado;
    }

    foreach ($response->getRows() as $row) {

        $dimensionValues = $row->getDimensionValues();
        $metricValues = $row->getMetricValues();

        $dimension = '';

        if (isset($dimensionValues[0])) {
            $dimension = trim(
                $dimensionValues[0]->getValue()
            );
        }

        if ($dimension === '') {
            $dimension = 'Sin datos';
        }

        $valor = 0;

        if (isset($metricValues[0])) {
            $valor = (int) $metricValues[0]->getValue();
        }

        $resultado[] = [
            'nombre' => $dimension,
            'valor' => $valor
        ];

        if (count($resultado) >= $limite) {
            break;
        }
    }

    return $resultado;
}


// ======================================================
// 10. VALORES PRINCIPALES
// ======================================================

$usuarios = 0;
$sesiones = 0;
$paginas = 0;
$eventos = 0;


// ======================================================
// 11. DATOS DETALLADOS
// ======================================================

$paginasMasVisitadas = [];

$dispositivos = [];

$paises = [];

$canales = [];


// ======================================================
// 12. TIEMPO REAL
// ======================================================

$activosTiempoReal = 0;


// ======================================================
// 13. CONSULTA PRINCIPAL
// ======================================================

try {

    $request = (new RunReportRequest())
        ->setProperty($propertyId)

        ->setDateRanges([
            new DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate
            ])
        ])

        ->setMetrics([

            new Metric([
                'name' => 'activeUsers'
            ]),

            new Metric([
                'name' => 'sessions'
            ]),

            new Metric([
                'name' => 'screenPageViews'
            ]),

            new Metric([
                'name' => 'eventCount'
            ])

        ]);


    $response = $analyticsData->runReport($request);


    if ($response->getRowCount() > 0) {

        $row = $response->getRows()[0];

        $usuarios = obtenerValorMetric($row, 0);

        $sesiones = obtenerValorMetric($row, 1);

        $paginas = obtenerValorMetric($row, 2);

        $eventos = obtenerValorMetric($row, 3);
    }


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([

        'success' => false,

        'error' =>
            'Error de Google Analytics Data API: '
            . $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ======================================================
// 14. PÁGINAS MÁS VISITADAS
// ======================================================

try {

    $requestPaginas = (new RunReportRequest())
        ->setProperty($propertyId)

        ->setDateRanges([
            new DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate
            ])
        ])

        ->setDimensions([
            new Dimension([
                'name' => 'pageTitle'
            ])
        ])

        ->setMetrics([
            new Metric([
                'name' => 'screenPageViews'
            ])
        ])

        ->setLimit(10);


    $responsePaginas =
        $analyticsData->runReport($requestPaginas);


    $paginasMasVisitadas =
        obtenerFilasDimension(
            $responsePaginas,
            'pageTitle',
            'screenPageViews',
            10
        );


} catch (Throwable $e) {

    // Si falla este bloque no rompemos todo Analytics.
    $paginasMasVisitadas = [];
}


// ======================================================
// 15. DISPOSITIVOS
// ======================================================

try {

    $requestDispositivos = (new RunReportRequest())
        ->setProperty($propertyId)

        ->setDateRanges([
            new DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate
            ])
        ])

        ->setDimensions([
            new Dimension([
                'name' => 'deviceCategory'
            ])
        ])

        ->setMetrics([
            new Metric([
                'name' => 'activeUsers'
            ])
        ])

        ->setLimit(10);


    $responseDispositivos =
        $analyticsData->runReport($requestDispositivos);


    $dispositivos =
        obtenerFilasDimension(
            $responseDispositivos,
            'deviceCategory',
            'activeUsers',
            10
        );


} catch (Throwable $e) {

    $dispositivos = [];
}


// ======================================================
// 16. PAÍSES
// ======================================================

try {

    $requestPaises = (new RunReportRequest())
        ->setProperty($propertyId)

        ->setDateRanges([
            new DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate
            ])
        ])

        ->setDimensions([
            new Dimension([
                'name' => 'country'
            ])
        ])

        ->setMetrics([
            new Metric([
                'name' => 'activeUsers'
            ])
        ])

        ->setLimit(10);


    $responsePaises =
        $analyticsData->runReport($requestPaises);


    $paises =
        obtenerFilasDimension(
            $responsePaises,
            'country',
            'activeUsers',
            10
        );


} catch (Throwable $e) {

    $paises = [];
}


// ======================================================
// 17. CANALES DE TRÁFICO
// ======================================================

try {

    $requestCanales = (new RunReportRequest())
        ->setProperty($propertyId)

        ->setDateRanges([
            new DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate
            ])
        ])

        ->setDimensions([
            new Dimension([
                'name' => 'sessionDefaultChannelGroup'
            ])
        ])

        ->setMetrics([
            new Metric([
                'name' => 'sessions'
            ])
        ])

        ->setLimit(10);


    $responseCanales =
        $analyticsData->runReport($requestCanales);


    $canales =
        obtenerFilasDimension(
            $responseCanales,
            'sessionDefaultChannelGroup',
            'sessions',
            10
        );


} catch (Throwable $e) {

    $canales = [];
}


// ======================================================
// 18. USUARIOS EN TIEMPO REAL
// ======================================================

try {

    $requestRealtime = (new RunRealtimeReportRequest())
        ->setProperty($propertyId)

        ->setMetrics([
            new Metric([
                'name' => 'activeUsers'
            ])
        ]);


    $responseRealtime =
        $analyticsData->runRealtimeReport(
            $requestRealtime
        );


    if ($responseRealtime->getRowCount() > 0) {

        foreach ($responseRealtime->getRows() as $row) {

            $metricValues =
                $row->getMetricValues();

            if (isset($metricValues[0])) {

                $activosTiempoReal +=
                    (int) $metricValues[0]->getValue();
            }
        }
    }


} catch (Throwable $e) {

    // No hacemos fallar todo Analytics si
    // únicamente falla el informe en tiempo real.

    $activosTiempoReal = null;
}


// ======================================================
// 19. NOMBRE DEL PROYECTO
// ======================================================

$nombreProyecto = 'Google Analytics';


try {

    $stmtProyecto = $pdo->prepare("
        SELECT property_id
        FROM google_analytics_conexiones
        WHERE usuario_id = ?
        LIMIT 1
    ");

    $stmtProyecto->execute([
        $usuario_id
    ]);

    $propertyData =
        $stmtProyecto->fetch();


    if (
        $propertyData &&
        !empty($propertyData['property_id'])
    ) {

        $nombreProyecto =
            $propertyData['property_id'];
    }


} catch (Throwable $e) {

    // No interrumpimos la respuesta.
}


// ======================================================
// 20. RESPUESTA FINAL
// ======================================================

echo json_encode([

    'success' => true,

    'proyecto' =>
        $nombreProyecto,

    'property' =>
        $propertyId,

    'periodo' =>
        $periodo,

    // ------------------------------------------
    // RESUMEN
    // ------------------------------------------

    'usuarios' =>
        $usuarios,

    'sesiones' =>
        $sesiones,

    'paginas' =>
        $paginas,

    'eventos' =>
        $eventos,

    'activos' =>
        $usuarios,

    // ------------------------------------------
    // TIEMPO REAL
    // ------------------------------------------

    'activos_tiempo_real' =>
        $activosTiempoReal,

    // ------------------------------------------
    // PÁGINAS
    // ------------------------------------------

    'paginas_mas_visitadas' =>
        $paginasMasVisitadas,

    // ------------------------------------------
    // DISPOSITIVOS
    // ------------------------------------------

    'dispositivos' =>
        $dispositivos,

    // ------------------------------------------
    // PAÍSES
    // ------------------------------------------

    'paises' =>
        $paises,

    // ------------------------------------------
    // CANALES
    // ------------------------------------------

    'canales' =>
        $canales

], JSON_UNESCAPED_UNICODE);