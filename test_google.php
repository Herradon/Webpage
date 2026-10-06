<?php

echo "<h2>Prueba Google API</h2>";

$autoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    die("❌ No existe vendor/autoload.php");
}

$loader = require_once $autoload;

echo "Autoload: <strong style='color:green'>OK</strong><br><br>";

echo "<strong>Composer ClassLoader:</strong> ";
echo is_object($loader)
    ? "<strong style='color:green'>OK</strong><br>"
    : "<strong style='color:red'>NO</strong><br>";

echo "<br><strong>Google\\Client buscado por Composer:</strong><br>";

$archivo = $loader->findFile('Google\\Client');

if ($archivo) {
    echo "<strong style='color:green'>ENCONTRADO</strong><br>";
    echo "<code>" . htmlspecialchars($archivo) . "</code><br>";
} else {
    echo "<strong style='color:red'>NO ENCONTRADO</strong><br>";
}

echo "<br><strong>class_exists:</strong> ";

if (class_exists('Google\\Client')) {
    echo "<strong style='color:green'>SÍ</strong>";
} else {
    echo "<strong style='color:red'>NO</strong>";
}
?>