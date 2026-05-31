<?php
// Requer: composer install
require_once __DIR__ . '/vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$texto = $_GET['texto'] ?? '';
if ($texto === '') {
    http_response_code(400);
    exit('Texto obrigatório.');
}

$options = new QROptions([
    'outputType' => QRCode::OUTPUT_MARKUP_SVG,
    'scale' => 8,
    'outputBase64' => false,
    'imageBase64' => false,
]);

header('Content-Type: image/svg+xml');
echo (new QRCode($options))->render($texto);
