<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/google/analytics-data/src/V1beta/Client/BetaAnalyticsDataClient.php';

echo '<pre>';
var_dump(class_exists(
    'Google\\Analytics\\Data\\V1beta\\Client\\BetaAnalyticsDataClient'
));
echo '</pre>';

echo '<pre>';

echo 'BetaAnalyticsDataClient: ';
var_dump(class_exists(
    'Google\\Analytics\\Data\\V1beta\\Client\\BetaAnalyticsDataClient'
));

echo "\nArchivo Beta:\n";

$archivo = __DIR__ . '/vendor/google/analytics-data/src/V1beta/Client/BetaAnalyticsDataClient.php';

var_dump(file_exists($archivo));

echo "\nRuta buscada:\n";
echo $archivo;

echo '</pre>';