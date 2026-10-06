
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Google\Analytics\Admin\V1beta\Client\AnalyticsAdminServiceClient;
use Google\Analytics\Admin\V1beta\ListAccountsRequest;
use Google\Analytics\Admin\V1beta\ListPropertiesRequest;
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
// 3. Crear credenciales OAuth del usuario
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
// 4. Crear cliente Google Analytics Admin
// ======================================================

$analyticsAdmin = new AnalyticsAdminServiceClient([
    'credentials' => $credentials,
    'transport' => 'rest'
]);


// ======================================================
// 5. Obtener cuentas y propiedades de Google Analytics
// ======================================================

try {

    $request = new ListAccountsRequest();

    $response = $analyticsAdmin->listAccounts($request);

    echo '<h1>Google Analytics</h1>';

    $encontradas = false;

    foreach ($response->iterateAllElements() as $account) {

        $encontradas = true;

        $accountName = $account->getName();

        echo '<h2>';
        echo htmlspecialchars(
            $account->getDisplayName(),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '</h2>';

        echo '<p>';
        echo '<strong>ID de cuenta:</strong> ';
        echo htmlspecialchars(
            $accountName,
            ENT_QUOTES,
            'UTF-8'
        );
        echo '</p>';

        echo '<hr>';

        echo '<h3>Buscando propiedades...</h3>';

        // ==============================================
        // Obtener propiedades de la cuenta
        // ==============================================

        try {

            $propertyRequest = new ListPropertiesRequest();

            $propertyRequest->setFilter(
                'parent:' . $accountName
            );

            $propertiesResponse =
                $analyticsAdmin->listProperties(
                    $propertyRequest
                );

            $propiedadesEncontradas = false;

            foreach (
                $propertiesResponse->iterateAllElements()
                as $property
            ) {

                $propiedadesEncontradas = true;

                echo '<div style="
                    border:1px solid #ccc;
                    padding:15px;
                    margin:10px 0;
                    border-radius:8px;
                ">';

                echo '<strong>Nombre:</strong> ';

                echo htmlspecialchars(
                    $property->getDisplayName(),
                    ENT_QUOTES,
                    'UTF-8'
                );

                echo '<br>';

                echo '<strong>ID:</strong> ';

                echo htmlspecialchars(
                    $property->getName(),
                    ENT_QUOTES,
                    'UTF-8'
                );

                echo '<br>';

                echo '<strong>Cuenta:</strong> ';

                echo htmlspecialchars(
                    $property->getParent(),
                    ENT_QUOTES,
                    'UTF-8'
                );

                echo '</div>';
            }

            if (!$propiedadesEncontradas) {

                echo '<p>';
                echo '<strong>
                    No se encontraron propiedades para esta cuenta.
                </strong>';
                echo '</p>';
            }

        } catch (Throwable $e) {

            echo '<h3>Error al obtener propiedades</h3>';

            echo '<pre>';
            echo htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );
            echo '</pre>';
        }
    }

    if (!$encontradas) {

        echo '<p>';
        echo 'No se encontraron cuentas de Google Analytics.';
        echo '</p>';
    }

} catch (Throwable $e) {

    echo '<h2>Error de Google Analytics</h2>';

    echo '<pre>';
    echo htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</pre>';
}