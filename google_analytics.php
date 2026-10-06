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

        // IMPORTANTE:
        // Mantén aquí tu client secret actual.
        // No lo publiques ni lo compartas.
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
// 5. Guardar propiedad seleccionada
// ======================================================

$mensajeSeleccion = null;
$errorSeleccion = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $propertySeleccionada = trim(
        $_POST['property_id'] ?? ''
    );

    if ($propertySeleccionada === '') {

        $errorSeleccion = 'Selecciona una propiedad de Google Analytics.';

    } elseif (
        !preg_match(
            '/^properties\/[0-9]+$/',
            $propertySeleccionada
        )
    ) {

        $errorSeleccion = 'La propiedad seleccionada no es válida.';

    } else {

        try {

            /*
             * Antes de guardar la propiedad comprobamos que
             * realmente pertenece a alguna de las cuentas
             * accesibles por el usuario.
             */

            $cuentasResponse = $analyticsAdmin->listAccounts(
                new ListAccountsRequest()
            );

            $propiedadValida = false;

            foreach (
                $cuentasResponse->iterateAllElements()
                as $account
            ) {

                $accountName = $account->getName();

                $propertyRequest = new ListPropertiesRequest();

                $propertyRequest->setFilter(
                    'parent:' . $accountName
                );

                $propertiesResponse =
                    $analyticsAdmin->listProperties(
                        $propertyRequest
                    );

                foreach (
                    $propertiesResponse->iterateAllElements()
                    as $property
                ) {

                    if (
                        $property->getName() ===
                        $propertySeleccionada
                    ) {

                        $propiedadValida = true;
                        break 2;
                    }
                }
            }


            // ==============================================
            // Guardar propiedad
            // ==============================================

            if ($propiedadValida) {

                $guardarProperty = $pdo->prepare("
                    UPDATE google_analytics_conexiones
                    SET property_id = ?
                    WHERE usuario_id = ?
                ");

                $guardarProperty->execute([
                    $propertySeleccionada,
                    $usuario_id
                ]);

                $conexion['property_id'] =
                    $propertySeleccionada;

                $mensajeSeleccion =
                    'Propiedad de Google Analytics conectada correctamente.';

            } else {

                $errorSeleccion =
                    'La propiedad seleccionada no está disponible para esta cuenta de Google.';
            }

        } catch (Throwable $e) {

            $errorSeleccion =
                'No se ha podido guardar la propiedad de Google Analytics: '
                . $e->getMessage();
        }
    }
}


// ======================================================
// 6. Obtener cuentas y propiedades disponibles
// ======================================================

$cuentasEncontradas = [];
$propiedadesDisponibles = [];

try {

    $request = new ListAccountsRequest();

    $response = $analyticsAdmin->listAccounts($request);


    foreach (
        $response->iterateAllElements()
        as $account
    ) {

        $accountName =
            $account->getName();

        $accountDisplayName =
            $account->getDisplayName();


        // ==============================================
        // Obtener propiedades de la cuenta
        // ==============================================

        $propertyRequest =
            new ListPropertiesRequest();

        $propertyRequest->setFilter(
            'parent:' . $accountName
        );

        $propertiesResponse =
            $analyticsAdmin->listProperties(
                $propertyRequest
            );


        $propiedadesCuenta = [];


        foreach (
            $propertiesResponse->iterateAllElements()
            as $property
        ) {

            $propertyName =
                $property->getName();

            $propertyDisplayName =
                $property->getDisplayName();

            $propertyType =
                $property->getPropertyType();


            $propiedadesCuenta[] = [
                'name' => $propertyName,
                'display_name' => $propertyDisplayName,
                'type' => $propertyType
            ];


            $propiedadesDisponibles[] = [
                'account_name' => $accountName,
                'account_display_name' => $accountDisplayName,
                'name' => $propertyName,
                'display_name' => $propertyDisplayName,
                'type' => $propertyType
            ];
        }


        $cuentasEncontradas[] = [
            'name' => $accountName,
            'display_name' => $accountDisplayName,
            'properties' => $propiedadesCuenta
        ];
    }


} catch (Throwable $e) {

    $errorGeneral =
        'Error de Google Analytics: '
        . $e->getMessage();
}

?>


<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Google Analytics | Viziune</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px 20px;
            background: #061018;
            color: #ffffff;
            font-family: system-ui, -apple-system, BlinkMacSystemFont,
                "Segoe UI", sans-serif;
        }

        .analytics-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .analytics-header {
            margin-bottom: 35px;
        }

        .analytics-kicker {
            display: inline-block;
            margin-bottom: 10px;
            color: #00cfe0;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.14em;
        }

        .analytics-header h1 {
            margin: 0 0 12px;
            font-size: 34px;
            line-height: 1.15;
        }

        .analytics-header p {
            max-width: 700px;
            margin: 0;
            color: #9fb1c1;
            line-height: 1.7;
        }

        .analytics-message {
            margin-bottom: 25px;
            padding: 16px 18px;
            border: 1px solid rgba(0, 207, 224, 0.25);
            border-radius: 12px;
            background: rgba(0, 207, 224, 0.08);
            color: #dffcff;
        }

        .analytics-error {
            margin-bottom: 25px;
            padding: 16px 18px;
            border: 1px solid rgba(255, 100, 100, 0.35);
            border-radius: 12px;
            background: rgba(255, 80, 80, 0.08);
            color: #ffd7d7;
        }

        .analytics-account {
            margin-bottom: 25px;
            padding: 24px;
            border: 1px solid #1c3045;
            border-radius: 16px;
            background: #0d1a29;
        }

        .analytics-account-header {
            margin-bottom: 20px;
        }

        .analytics-account-header span {
            display: block;
            margin-bottom: 6px;
            color: #00cfe0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
        }

        .analytics-account-header h2 {
            margin: 0;
            font-size: 21px;
        }

        .analytics-property {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-top: 12px;
            padding: 18px;
            border: 1px solid #1c3045;
            border-radius: 12px;
            background: #07111f;
        }

        .analytics-property-info {
            min-width: 0;
        }

        .analytics-property-info strong {
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .analytics-property-info small {
            display: block;
            color: #9fb1c1;
            word-break: break-word;
        }

        .analytics-property-info .property-type {
            margin-top: 5px;
            color: #71889a;
            font-size: 12px;
        }

        .analytics-property-button {
            flex-shrink: 0;
            padding: 11px 18px;
            border: 0;
            border-radius: 9px;
            background: #00cfe0;
            color: #061018;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .analytics-property-button:hover {
            opacity: 0.9;
        }

        .analytics-connected {
            margin-bottom: 30px;
            padding: 22px;
            border: 1px solid rgba(0, 207, 224, 0.25);
            border-radius: 16px;
            background: rgba(0, 207, 224, 0.06);
        }

        .analytics-connected-label {
            margin-bottom: 7px;
            color: #00cfe0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
        }

        .analytics-connected strong {
            display: block;
            margin-bottom: 6px;
            font-size: 18px;
        }

        .analytics-connected span {
            color: #9fb1c1;
            font-size: 14px;
        }

        .analytics-empty {
            padding: 25px;
            border: 1px solid #1c3045;
            border-radius: 16px;
            background: #0d1a29;
            color: #9fb1c1;
        }

        @media (max-width: 650px) {

            body {
                padding: 25px 15px;
            }

            .analytics-header h1 {
                font-size: 28px;
            }

            .analytics-property {
                flex-direction: column;
                align-items: stretch;
            }

            .analytics-property-button {
                width: 100%;
            }
        }

    </style>

</head>


<body>

<div class="analytics-container">


    <!-- ==================================================
         CABECERA
    =================================================== -->

    <header class="analytics-header">

        <span class="analytics-kicker">
            GOOGLE ANALYTICS 4
        </span>

        <h1>
            Selecciona tu propiedad
        </h1>

        <p>
            Elige la propiedad de Google Analytics que quieres
            utilizar en Viziune para consultar el rendimiento
            de tu proyecto web.
        </p>

    </header>


    <?php if (!empty($mensajeSeleccion)): ?>

        <div class="analytics-message">

            <?= htmlspecialchars(
                $mensajeSeleccion,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($errorSeleccion)): ?>

        <div class="analytics-error">

            <?= htmlspecialchars(
                $errorSeleccion,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($errorGeneral)): ?>

        <div class="analytics-error">

            <?= htmlspecialchars(
                $errorGeneral,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <!-- ==================================================
         PROPIEDAD ACTUAL
    =================================================== -->

    <?php if (!empty($conexion['property_id'])): ?>

        <?php

        $propiedadActual =
            $conexion['property_id'];

        $nombrePropiedadActual =
            $propiedadActual;

        foreach (
            $propiedadesDisponibles
            as $propiedad
        ) {

            if (
                $propiedad['name'] ===
                $propiedadActual
            ) {

                $nombrePropiedadActual =
                    $propiedad['display_name'];

                break;
            }
        }

        ?>

        <div class="analytics-connected">

            <div class="analytics-connected-label">
                PROPIEDAD CONECTADA
            </div>

            <strong>
                <?= htmlspecialchars(
                    $nombrePropiedadActual,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>

            <span>
                <?= htmlspecialchars(
                    $propiedadActual,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>

        </div>

    <?php endif; ?>


    <!-- ==================================================
         CUENTAS Y PROPIEDADES
    =================================================== -->

    <?php if (!empty($cuentasEncontradas)): ?>

        <?php foreach (
            $cuentasEncontradas
            as $cuenta
        ): ?>

            <section class="analytics-account">

                <div class="analytics-account-header">

                    <span>
                        CUENTA DE GOOGLE ANALYTICS
                    </span>

                    <h2>
                        <?= htmlspecialchars(
                            $cuenta['display_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h2>

                </div>


                <?php if (!empty($cuenta['properties'])): ?>

                    <?php foreach (
                        $cuenta['properties']
                        as $property
                    ): ?>

                        <div class="analytics-property">

                            <div class="analytics-property-info">

                                <strong>
                                    <?= htmlspecialchars(
                                        $property['display_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>

                                <small>
                                    <?= htmlspecialchars(
                                        $property['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </small>

                                <div class="property-type">

                                    Tipo:
                                    <?= htmlspecialchars(
                                        $property['type'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                            </div>


                            <form
                                method="POST"
                                style="margin:0;"
                            >

                                <input
                                    type="hidden"
                                    name="property_id"
                                    value="<?= htmlspecialchars(
                                        $property['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                                <button
                                    type="submit"
                                    class="analytics-property-button"
                                >
                                    <?=
                                        $conexion['property_id']
                                        === $property['name']
                                            ? 'Propiedad seleccionada'
                                            : 'Seleccionar'
                                    ?>
                                </button>

                            </form>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="analytics-empty">

                        No se encontraron propiedades GA4
                        accesibles para esta cuenta.

                    </div>

                <?php endif; ?>

            </section>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="analytics-empty">

            No se encontraron cuentas de Google Analytics
            accesibles para este usuario.

        </div>

    <?php endif; ?>


</div>

</body>

</html>