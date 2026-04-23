<?php
/**
 * ERROR_HANDLER.PHP - Register global error/exception/fatal handlers.
 */

require_once __DIR__ . '/../config/init.php';

if (!defined('ERROR_HANDLERS_REGISTERED')) {
    define('ERROR_HANDLERS_REGISTERED', true);

    // Ensure display_errors is off in production (or generally to avoid white page / leaked details)
    // In dev mode we might want it on, but user specifically asked not to show white page.
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);

    if (!function_exists('log_to_file')) {
        /**
         * Log message to a file.
         */
        function log_to_file($message, $level = 'ERROR', array $context = []) {
            if (function_exists('app_log')) {
                app_log((string) $level, (string) $message, $context);
                return;
            }

            error_log(sprintf('[%s] [%s] %s', date('Y-m-d H:i:s'), strtoupper((string) $level), (string) $message));
        }
    }

    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorTypes = [
            E_ERROR             => 'Fatal Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
        ];

        // E_STRICT is deprecated since PHP 8.4, handle it safely
        if (defined('E_STRICT')) {
            $errorTypes[E_STRICT] = 'Strict Notice';
        }

        $type = $errorTypes[$errno] ?? 'Unknown Error';
        $message = "[$type] $errstr in $errfile on line $errline";
        $context = [
            'error_type' => $type,
            'errno' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
        ];

        $level = in_array($errno, [E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED], true)
            ? 'warning'
            : 'error';

        log_to_file($message, $level, $context);

        if (function_exists('log_to_dev_panel')) {
            log_to_dev_panel(
                'PHP ' . $type . ': ' . $errstr,
                $level,
                function_exists('app_context_summary')
                    ? app_context_summary($context)
                    : ('File: ' . basename($errfile) . ' | Line: ' . $errline)
            );
        }

        // Only treat truly fatal user/recoverable errors as render-worthy here.
        if (in_array($errno, [E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
            render_server_error_page('Kritická chyba aplikácie.');
        }

        // Return true to prevent the standard PHP error handler from running
        return true;
    });

    set_exception_handler(function ($e) {
        $details = $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString();
        $message = "Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
        $context = [
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        log_to_file($message, 'EXCEPTION', $context);

        if (function_exists('log_to_dev_panel')) {
            log_to_dev_panel('Uncaught exception: ' . $e->getMessage(), 'error', $details);
        }

        render_server_error_page('Aplikácia narazila na neočakávanú výnimku.');
    });

    register_shutdown_function(function () {
        $error = error_get_last();
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

        if ($error && in_array($error['type'], $fatalTypes, true)) {
            $details = sprintf(
                'File: %s | Line: %d | Message: %s',
                $error['file'],
                $error['line'],
                $error['message']
            );

            log_to_file(
                "Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'],
                'FATAL',
                [
                    'fatal_type' => $error['type'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'message' => $error['message'],
                ]
            );

            if (function_exists('log_to_dev_panel')) {
                log_to_dev_panel('Fatal shutdown error occurred.', 'error', $details);
            }

            render_server_error_page('Server narazil na fatálnu chybu pri ukončovaní.');
        }
    });
}
