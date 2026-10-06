<?php

require_once __DIR__ . '/vendor/autoload.php';

echo '<pre>';

$clase = 'Google\\Analytics\\Admin\\V1beta\\Client\\AnalyticsAdminServiceClient';

echo $clase . ' => ';
echo class_exists($clase) ? 'EXISTE' : 'NO EXISTE';

echo '</pre>';