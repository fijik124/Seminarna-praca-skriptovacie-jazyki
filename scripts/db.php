<?php
/**
 * DB.PHP - Database connection bootstrap.
 */

require_once __DIR__ . '/init.php';

$db_host = '127.0.0.1';
$db_name = 'revtrack';
$db_user = 'server';
$db_pass = 'server124Pas';
$db_connected = false;
$db_info = [
    'status' => 'Disconnected',
    'host' => $db_host,
    'name' => $db_name,
    'user' => $db_user,
];

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $db_connected = true;
    $db_info = [
        'status' => 'Connected',
        'host' => $db_host,
        'name' => $db_name,
        'user' => $db_user,
        'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
        'server' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO),
        'version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'protocol' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
    ];

    log_to_dev_panel("Database '$db_name' connected.", 'success');
} catch (PDOException $e) {
    $db_connected = false;
    log_to_dev_panel('DB Connection Failed: ' . $e->getMessage(), 'error');
}
