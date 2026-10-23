<?php
// Configuración local: copia config.local.example.php a config.local.php.
$local = __DIR__ . '/config.local.php';
return is_file($local) ? require $local : [
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=andesur',
    'user' => 'andesur',
    'password' => '',
    'timezone' => 'America/La_Paz',
];
