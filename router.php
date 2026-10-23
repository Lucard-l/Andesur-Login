<?php
// Solo se publica la aplicación y sus estilos, nunca SQL ni configuración.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/assets/style.css') {
    header('Content-Type: text/css; charset=utf-8');
    readfile(__DIR__ . '/assets/style.css');
    return;
}
if ($path !== '/' && $path !== '/index.php') {
    http_response_code(404);
    exit('Página no encontrada');
}
require __DIR__ . '/index.php';
