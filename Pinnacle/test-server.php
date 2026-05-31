<?php
/**
 * test-server.php
 * Endpoint de diagnóstico. Retorna status do servidor,
 * versão do PHP, diretório e permissões do uploads.
 */

header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'server_time' => time(),
    'php_version' => phpversion(),
    'directory' => __DIR__,
    'uploads_dir_exists' => is_dir(__DIR__ . '/uploads'),
    'uploads_writable' => is_writable(__DIR__ . '/uploads') || is_writable(__DIR__)
]);
?>
