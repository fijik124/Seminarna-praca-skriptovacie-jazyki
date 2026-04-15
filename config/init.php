<?php
/**
 * INIT.PHP - Global configuration and shared helpers.
 */

spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../src/';
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

define('DEV_MODE', true);

if (!isset($debug_logs) || !is_array($debug_logs)) {
    $debug_logs = [];
}

/**
 * Collect log events for the dev panel.
 */
function log_to_dev_panel($message, $type = 'info', $details = '') {
    global $debug_logs;

    $templates = [
        'error' => ['class' => 'text-bg-danger', 'icon' => 'x'],
        'warning' => ['class' => 'text-bg-warning', 'icon' => '!'],
        'info' => ['class' => 'text-bg-info', 'icon' => 'i'],
        'success' => ['class' => 'text-bg-success', 'icon' => '+'],
    ];

    $style = $templates[$type] ?? $templates['info'];

    $debug_logs[] = [
        'id' => uniqid('log_', true),
        'timestamp' => date('H:i:s'),
        'message' => $message,
        'details' => $details !== '' ? $details : 'No additional info.',
        'class' => $style['class'],
        'icon' => $style['icon'],
    ];
}

/**
 * Get base URL for the application.
 */
function url($path = '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $basePath = rtrim(dirname($scriptName), '/\\');

    // Keep links rooted at the app entrypoint even inside /dashboard or /user controllers.
    if (preg_match('#/(dashboard|user)$#', $basePath)) {
        $basePath = dirname($basePath);
    }

    $basePath = rtrim(str_replace('\\', '/', (string) $basePath), '/\\');
    return $basePath . '/' . ltrim($path, '/');
}

/**
 * Render 500 error page and stop execution.
 */
function render_server_error_page($message = 'Unexpected server error.', $details = '') {
    http_response_code(500);

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $errorMessage = $message;
    $errorDetails = ''; // Always hide debug details from users
    $errorPage = dirname(__DIR__) . '/templates/pages/500.php';

    if (file_exists($errorPage)) {
        require $errorPage;
    } else {
        echo '<h1>500 - Server Error</h1><p>Something went wrong.</p>';
    }

    exit;
}
