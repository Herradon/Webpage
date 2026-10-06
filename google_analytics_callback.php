<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';


// ======================================================
// 1. Comprobar que el usuario está conectado
// ======================================================

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];


// ======================================================
// 2. Crear cliente de Google
// ======================================================

$client = new Google\Client();

$client->setClientId(
    '308953097944-3m125i1nmler7gs8l8d4e977mrvt6pa4.apps.googleusercontent.com'
);

/*
 * IMPORTANTE:
 * Mantén aquí tu Client Secret actual en el servidor.
 * No lo compartas públicamente.
 */
$client->setClientSecret(
    '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'
);

$client->setRedirectUri(
    'https://viziuneai.es/google_analytics_callback.php'
);


// ======================================================
// 3. Comprobar el estado OAuth
// ======================================================

if (
    !isset($_GET['state']) ||
    !isset($_SESSION['google_analytics_oauth_state']) ||
    !hash_equals(
        $_SESSION['google_analytics_oauth_state'],
        $_GET['state']
    )
) {

    exit('Error de seguridad: estado OAuth no válido.');
}


// ======================================================
// 4. Comprobar si Google devolvió un error
// ======================================================

if (isset($_GET['error'])) {

    $error = htmlspecialchars(
        $_GET['error'],
        ENT_QUOTES,
        'UTF-8'
    );

    unset($_SESSION['google_analytics_oauth_state']);

    exit(
        'Google no autorizó la conexión. Error: ' . $error
    );
}


// ======================================================
// 5. Comprobar que Google ha enviado el código
// ======================================================

if (!isset($_GET['code'])) {

    exit(
        'No se recibió el código de autorización de Google.'
    );
}


// ======================================================
// 6. Intercambiar el código por tokens
// ======================================================

$token = $client->fetchAccessTokenWithAuthCode(
    $_GET['code']
);


// ======================================================
// 7. Comprobar errores
// ======================================================

if (isset($token['error'])) {

    $error = htmlspecialchars(
        $token['error'],
        ENT_QUOTES,
        'UTF-8'
    );

    exit(
        'No se pudieron obtener las credenciales de Google: '
        . $error
    );
}


// ======================================================
// 8. Obtener tokens
// ======================================================

$accessToken = $token['access_token'] ?? null;
$refreshToken = $token['refresh_token'] ?? null;
$expiresIn = isset($token['expires_in'])
    ? (int) $token['expires_in']
    : 3600;


// ======================================================
// 9. Comprobar access token
// ======================================================

if (!$accessToken) {

    exit(
        'Google no devolvió un token de acceso válido.'
    );
}


// ======================================================
// 10. Calcular cuándo caduca el access token
// ======================================================

$tokenExpiraEn = date(
    'Y-m-d H:i:s',
    time() + $expiresIn
);


// ======================================================
// 11. Buscar conexión existente
// ======================================================
// ======================================================
// 11. Buscar conexión existente
// ======================================================

$stmt = $pdo->prepare("
    SELECT id, refresh_token
    FROM google_analytics_conexiones
    WHERE usuario_id = ?
    LIMIT 1
");

$stmt->execute([$usuario_id]);

$conexion = $stmt->fetch();


// ======================================================
// 12. Conservar refresh token anterior si Google
//     no devuelve uno nuevo
// ======================================================

if (!$refreshToken && $conexion) {

    $refreshToken = $conexion['refresh_token'];
}


// ======================================================
// 13. Guardar / actualizar conexión
// ======================================================

if ($conexion) {

    $stmt = $pdo->prepare("
        UPDATE google_analytics_conexiones
        SET
            access_token = ?,
            refresh_token = ?,
            token_expira_en = ?,
            actualizado_en = CURRENT_TIMESTAMP
        WHERE usuario_id = ?
    ");

    $stmt->execute([
        $accessToken,
        $refreshToken,
        $tokenExpiraEn,
        $usuario_id
    ]);

} else {

    $stmt = $pdo->prepare("
        INSERT INTO google_analytics_conexiones
        (
            usuario_id,
            access_token,
            refresh_token,
            token_expira_en
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $usuario_id,
        $accessToken,
        $refreshToken,
        $tokenExpiraEn
    ]);
}
// ======================================================
// 14. Guardar también temporalmente en sesión
// ======================================================

$_SESSION['google_analytics_access_token'] =
    $accessToken;

if ($refreshToken) {

    $_SESSION['google_analytics_refresh_token'] =
        $refreshToken;
}


// ======================================================
// 15. Eliminar el estado OAuth utilizado
// ======================================================

unset(
    $_SESSION['google_analytics_oauth_state']
);


// ======================================================
// 16. Volver a herramientas.php
// ======================================================

header(
    'Location: herramientas.php?analytics=connected'
);

exit;