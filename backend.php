<?php

// 🔥 DEBUG PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =====================================================
   📄 LOG SYSTEM
===================================================== */

$logFile = __DIR__ . "/log.txt";

function logCRM($msg) {
    global $logFile;
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

logCRM("ENTRÓ EN PHP");

/* =====================================================
   CONEXIÓN BD
===================================================== */

$conn = new mysqli("localhost", "root", "", "mi_web");

if ($conn->connect_error) {
    logCRM("ERROR BD: " . $conn->connect_error);
    die(json_encode(["status" => "error", "msg" => "DB error"]));
}

/* =====================================================
   PANEL (GET)
===================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    $result = $conn->query("SELECT * FROM leads ORDER BY fecha DESC");

    if (!$result) {
        logCRM("ERROR SQL: " . $conn->error);
        die("ERROR SQL");
    }

    echo "<h1>📊 CRM PRO</h1>";

    echo "<table border='1' cellpadding='10'>
            <tr>
                <th>Client ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Mensaje</th>
                <th>Fecha</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['client_id']) . "</td>
                <td>" . htmlspecialchars($row['nombre']) . "</td>
                <td>" . htmlspecialchars($row['email']) . "</td>
                <td>" . htmlspecialchars($row['mensaje']) . "</td>
                <td>" . htmlspecialchars($row['fecha']) . "</td>
              </tr>";
    }

    echo "</table>";
    exit;
}

/* =====================================================
   RECIBIR DATOS (POST DESDE JS)
===================================================== */

logCRM("POST: " . print_r($_POST, true));

$client_id = $_POST['client_id'] ?? 'default';
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

/* =====================================================
   VALIDACIÓN
===================================================== */

if (!$nombre || !$email || !$mensaje) {
    logCRM("FALTAN DATOS");
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logCRM("EMAIL INVALIDO");
    echo json_encode(["status" => "error", "msg" => "Email inválido"]);
    exit;
}

/* =====================================================
   INSERT EN BD
===================================================== */

$stmt = $conn->prepare("
INSERT INTO leads (client_id, nombre, email, mensaje)
VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    logCRM("ERROR PREPARE: " . $conn->error);
    echo json_encode(["status" => "error", "msg" => "Prepare error"]);
    exit;
}

$stmt->bind_param("ssss", $client_id, $nombre, $email, $mensaje);

if ($stmt->execute()) {
    logCRM("GUARDADO OK");

    echo json_encode([
        "status" => "ok",
        "msg" => "Guardado correctamente"
    ]);
} else {
    logCRM("ERROR INSERT: " . $stmt->error);

    echo json_encode([
        "status" => "error",
        "msg" => "Error al guardar"
    ]);
}

$stmt->close();
$conn->close();

?>