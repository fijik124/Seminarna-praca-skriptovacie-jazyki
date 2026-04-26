<?php
/**
 * DB.PHP - Database connection bootstrap.
 */

require_once __DIR__ . '/init.php';

$db_host = (string) app_env('DB_HOST', '127.0.0.1');
$db_port = (string) app_env('DB_PORT', '3306');
$db_name = (string) app_env('DB_NAME', 'name');
$db_user = (string) app_env('DB_USER', 'user');
$db_pass = (string) app_env('DB_PASSWORD', 'pass');
$db_connected = false;
$db_info = [
    'status' => 'Disconnected',
    'host' => $db_host,
    'port' => $db_port,
    'name' => $db_name,
    'user' => $db_user,
];
$db_error = null;

try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $db_connected = true;
    $db_info = [
        'status' => 'Connected',
        'host' => $db_host,
        'port' => $db_port,
        'name' => $db_name,
        'user' => $db_user,
        'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
        'server' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO),
        'version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'protocol' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
    ];

    if (function_exists('app_log')) {
        app_log('info', "Database '$db_name' connected.", [
            'db_host' => $db_host,
            'db_port' => $db_port,
            'db_name' => $db_name,
            'db_user' => $db_user,
            'driver' => $db_info['driver'],
            'server' => $db_info['server'],
            'version' => $db_info['version'],
        ]);
    }

    log_to_dev_panel("Database '$db_name' connected.", 'success');
} catch (PDOException $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
    log_to_dev_panel('DB Connection Failed: ' . $db_error, 'error');
    if (function_exists('app_log')) {
        app_log('error', 'DB Connection Failed', [
            'db_host' => $db_host,
            'db_port' => $db_port,
            'db_name' => $db_name,
            'db_user' => $db_user,
            'exception' => $db_error,
        ]);
    } elseif (function_exists('log_to_file')) {
        log_to_file("DB Connection Failed: $db_error", 'ERROR');
    }
}
