<?php

session_start();

if (isset($_SESSION['usuario_id'])) {

    unset($_SESSION['mostrar_politica_privacidad']);

}

header('Content-Type: application/json');

echo json_encode([
    'success' => true
]);

exit;