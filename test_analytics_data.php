<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Auth\Credentials\UserRefreshCredentials;

// ======================================================
// 1. Comprobar usuario
// ======================================================

if (!isset($_SESSION['usuario_id'])) {
    exit('Usuario no conectado.');
}

$usuario_id = (int) $_SESSION['usuario_id'];


// ======================================================
// 2. Buscar conexión de Google Analytics
// ======================================================

$stmt = $pdo->prepare("
    SELECT access_token, refresh_token, token_expira_en
    FROM google_analytics_conexiones
    WHERE usuario_id = ?
    LIMIT 1
");

$stmt->execute([$usuario_id]);

$conexion = $stmt->fetch();

if (!$conexion) {
    exit('No existe una conexión de Google Analytics para este usuario.');
}

if (empty($conexion['refresh_token'])) {
    exit('No existe refresh token para este usuario.');
}


// ======================================================
// 3. Credenciales OAuth
// ======================================================

$scopes = [
    'https://www.googleapis.com/auth/analytics.readonly'
];

$credentials = new UserRefreshCredentials(
    $scopes,
    [
        'client_id' => '308953097944-3m125i1nmler7gs8l8d4e977mrvt6pa4.apps.googleusercontent.com',
        'client_secret' => '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',
        'refresh_token' => $conexion['refresh_token'],
        'type' => 'authorized_user'
    ]
);


// ======================================================
// 4. Cliente Google Analytics Data API
// ======================================================

$analyticsData = new BetaAnalyticsDataClient([
    'credentials' => $credentials,
    'transport' => 'rest'
]);


// ======================================================
// 5. Propiedad GA4
// ======================================================

$propertyId = 'properties/555540703';


// ======================================================
// 6. Obtener datos
// ======================================================

try {

    $request = (new RunReportRequest())
        ->setProperty($propertyId)
        ->setDateRanges([
            new DateRange([
                'start_date' => '30daysAgo',
                'end_date' => 'yesterday'
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
                'name' => 'eventCount'
            ])
        ]);

    $response = $analyticsData->runReport($request);


    // ==================================================
    // 7. Mostrar resultado
    // ==================================================

    echo '<h1>Google Analytics</h1>';

    echo '<p>';
    echo '<strong>Propiedad:</strong> ';
    echo htmlspecialchars(
        $propertyId,
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</p>';

    echo '<p>';
    echo '<strong>Periodo:</strong> ';
    echo 'Últimos 30 días';
    echo '</p>';

    echo '<hr>';

    if ($response->getRowCount() > 0) {

        foreach ($response->getRows() as $row) {

            $values = $row->getMetricValues();

            echo '<h2>Resultados</h2>';

            echo '<p>';
            echo '<strong>Usuarios activos:</strong> ';
            echo htmlspecialchars(
                $values[0]->getValue(),
                ENT_QUOTES,
                'UTF-8'
            );
            echo '</p>';

            echo '<p>';
            echo '<strong>Sesiones:</strong> ';
            echo htmlspecialchars(
                $values[1]->getValue(),
                ENT_QUOTES,
                'UTF-8'
            );
            echo '</p>';

            echo '<p>';
            echo '<strong>Eventos:</strong> ';
            echo htmlspecialchars(
                $values[2]->getValue(),
                ENT_QUOTES,
                'UTF-8'
            );
            echo '</p>';
        }

    } else {

        echo '<p>';
        echo 'Google Analytics no ha devuelto datos para este periodo.';
        echo '</p>';
    }


} catch (Throwable $e) {

    echo '<h2>Error de Google Analytics Data API</h2>';

    echo '<pre>';
    echo htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</pre>';
}