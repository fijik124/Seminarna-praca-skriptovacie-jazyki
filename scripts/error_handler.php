<?php
/**
 * INIT.PHP - Global Configuration
 */

define('DEV_MODE', true); // Toggle this for production
$debug_logs = [];

/**
 * Global Log Function
 */
function log_to_dev_panel($message, $type = 'info', $details = '') {
    global $debug_logs;

    $templates = [
        'error'   => ['class' => 'text-bg-danger', 'icon' => '×'],
        'warning' => ['class' => 'text-bg-warning', 'icon' => '!'],
        'info'    => ['class' => 'text-bg-info', 'icon' => 'i'],
        'success' => ['class' => 'text-bg-success', 'icon' => '✓']
    ];

    $style = $templates[$type] ?? $templates['info'];

    $debug_logs[] = [
        'id'        => uniqid('log_'),
        'timestamp' => date('H:i:s'),
        'message'   => $message,
        'details'   => $details ?: 'No additional info.',
        'class'     => $style['class'],
        'icon'      => $style['icon']
    ];
}

if (DEV_MODE) {
    // 1. Catch Warnings/Notices
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) return false;
        log_to_dev_panel("PHP Error: $errstr", "error", "File: " . basename($errfile) . " | Line: $errline");
        return false;
    });

    // 2. Catch Fatal Exceptions (like PDO failing)
    set_exception_handler(function ($e) {
        log_to_dev_panel("Fatal: " . $e->getMessage(), "error", $e->getTraceAsString());
        // Force the panel to show before dying
        include_once __DIR__ . '../components/devpanel.php'; 
        exit;
    });

    // 3. Automatically include the panel at the end of every successful page load
    register_shutdown_function(function() {
        // We check if the file exists to prevent a secondary error
        $panelPath = __DIR__ . '../components/devpanel.php';
        if (file_exists($panelPath)) {
            include_once $panelPath;
        }
    });
}