<?php
declare(strict_types=1);

/**
 * ERROR_HANDLER.PHP - Register global error/exception/fatal handlers.
 */

require_once __DIR__ . '/../config/init.php';

if (!defined('ERROR_HANDLERS_REGISTERED')) {
    define('ERROR_HANDLERS_REGISTERED', true);

    // V produkcii nechceme vypisovať chyby do HTML (vytvára to záseky hlavičiek a biele stránky)
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);

    if (!function_exists('log_to_file')) {
        /**
         * Log message to a file.
         */
        function log_to_file(string $message, string $level = 'ERROR', array $context = []): void {
            if (function_exists('app_log')) {
                app_log($level, $message, $context);
                return;
            }

            error_log(sprintf('[%s] [%s] %s', date('Y-m-d H:i:s'), strtoupper($level), $message));
        }
    }

    // 1. GLOBAL ERROR HANDLER
    set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Moderné riešenie pomocou match() namiesto poľa. Vyhýbame sa priamemu zápisu E_STRICT.
        $type = match ($errno) {
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
            2048                => 'Strict Notice', // Použijeme priamo int hodnotu starého E_STRICT (bezpečné pre PHP 8.4+)
            default             => 'Unknown Error',
        };

        $message = "[$type] $errstr in $errfile on line $errline";
        $context = [
            'error_type' => $type,
            'errno'      => $errno,
            'message'    => $errstr,
            'file'       => $errfile,
            'line'       => $errline,
        ];

        // Rozhodnutie o dôležitosti logu (LogLevel)
        $level = match ($errno) {
            E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, 2048 => 'warning',
            default => 'error',
        };

        log_to_file($message, $level, $context);

        if (function_exists('log_to_dev_panel')) {
            $summary = function_exists('app_context_summary')
                ? app_context_summary($context)
                : ('File: ' . basename($errfile) . ' | Line: ' . $errline);

            log_to_dev_panel("PHP $type: $errstr", $level, $summary);
        }

        // Ak ide o kritickú užívateľskú alebo obnoviteľnú chybu, renderujeme error page
        if ($errno === E_USER_ERROR || $errno === E_RECOVERABLE_ERROR) {
            render_server_error_page('Kritická chyba aplikácie.');
        }

        return true;
    });

    // 2. GLOBAL EXCEPTION HANDLER
    set_exception_handler(function (Throwable $e): void {
        $details = $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString();
        $message = "Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();

        $context = [
            'exception_class' => $e::class, // Moderný zápis PHP 8.0+ namiesto get_class($e)
            'message'         => $e->getMessage(),
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
            'trace'           => $e->getTraceAsString(),
        ];

        log_to_file($message, 'EXCEPTION', $context);

        if (function_exists('log_to_dev_panel')) {
            log_to_dev_panel('Uncaught exception: ' . $e->getMessage(), 'error', $details);
        }

        render_server_error_page('Aplikácia narazila na neočakávanú výnimku.');
    });

    // 3. SHUTDOWN FUNCTION (Pre zachytenie fatálnych pádov)
    register_shutdown_function(function (): void {
        $error = error_get_last();

        if ($error !== null) {
            // Cez match overíme, či ide o fatálny typ chyby, ktorý spôsobil pád skriptu
            $isFatal = match ($error['type']) {
                E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => true,
                default => false,
            };

            if ($isFatal) {
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
                        'file'       => $error['file'],
                        'line'       => $error['line'],
                        'message'    => $error['message'],
                    ]
                );

                if (function_exists('log_to_dev_panel')) {
                    log_to_dev_panel('Fatal shutdown error occurred.', 'error', $details);
                }

                render_server_error_page('Server narazil na fatálnu chybu pri ukončovaní.');
            }
        }
    });
}