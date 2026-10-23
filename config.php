<?php
// Configuración local: crea config.local.php siguiendo el ejemplo del README.
$local = __DIR__ . '/config.local.php';
return is_file($local) ? require $local : [
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=andesur',
    'user' => 'andesur',
    'password' => '',
    'timezone' => 'America/La_Paz',
];
