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

if (!function_exists('app_request_context')) {
    function app_request_context(array $extra = []): array {
        $context = [
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid('req_', true),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'path' => isset($_SERVER['REQUEST_URI']) ? (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/') : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];

        if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user']) && is_array($_SESSION['user'])) {
            $context['user_id'] = $_SESSION['user']['id'] ?? null;
            $context['user_email'] = $_SESSION['user']['email'] ?? null;
            $context['group_id'] = $_SESSION['user']['group_id'] ?? null;
        }

        return array_merge($context, $extra);
    }
}

if (!function_exists('app_context_summary')) {
    function app_context_summary(array $context = []): string {
        $context = $context ?: app_request_context();
        $parts = [];

        foreach ($context as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            }

            $parts[] = $key . '=' . (string) $value;
        }

        return implode(' | ', $parts);
    }
}

if (!function_exists('app_normalize_log_level')) {
    function app_normalize_log_level(string $level): string {
        $level = strtolower(trim($level));
        $aliases = [
            'fatal' => 'error',
            'exception' => 'error',
            'err' => 'error',
            'warn' => 'warning',
            'debug' => 'info',
        ];

        $level = $aliases[$level] ?? $level;
        return in_array($level, ['error', 'warning', 'info', 'success'], true) ? $level : 'info';
    }
}

if (!function_exists('app_stringify_log_details')) {
    function app_stringify_log_details($details = '', array $context = []): string {
        if (is_string($details) && $details !== '') {
            return $details;
        }

        if (is_array($details) || is_object($details)) {
            $encoded = json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            if ($encoded !== false) {
                return $encoded;
            }
        }

        if (!empty($context)) {
            return app_context_summary($context);
        }

        return 'No additional info.';
    }
}

if (!function_exists('app_make_dev_log_entry')) {
    function app_make_dev_log_entry(string $message, string $level = 'info', $details = '', array $context = [], string $source = 'devpanel', ?string $timestamp = null): array {
        $level = app_normalize_log_level($level);
        $styles = [
            'error' => ['class' => 'text-bg-danger', 'icon' => 'x'],
            'warning' => ['class' => 'text-bg-warning', 'icon' => '!'],
            'info' => ['class' => 'text-bg-info', 'icon' => 'i'],
            'success' => ['class' => 'text-bg-success', 'icon' => '+'],
        ];

        $style = $styles[$level] ?? $styles['info'];
        $detailsText = app_stringify_log_details($details, $context);

        return [
            'id' => uniqid('log_', true),
            'timestamp' => $timestamp ?: date('H:i:s'),
            'message' => (string) $message,
            'details' => $detailsText,
            'class' => $style['class'],
            'icon' => $style['icon'],
            'level' => $level,
            'source' => $source,
            'context' => $context,
        ];
    }
}

if (!function_exists('app_append_dev_log')) {
    function app_append_dev_log(array $entry): void {
        global $debug_logs;

        if (!isset($debug_logs) || !is_array($debug_logs)) {
            $debug_logs = [];
        }

        $debug_logs[] = $entry;
    }
}

if (!function_exists('app_read_recent_log_entries')) {
    function app_read_recent_log_entries(?string $logFile = null, int $maxEntries = 50, int $maxBytes = 65536): array {
        $logFile = $logFile ?: __DIR__ . '/../logs/app.log';

        if (!is_file($logFile) || !is_readable($logFile) || $maxEntries <= 0) {
            return [];
        }

        $handle = @fopen($logFile, 'rb');
        if (!$handle) {
            return [];
        }

        $entries = [];
        try {
            fseek($handle, 0, SEEK_END);
            $size = ftell($handle);
            if ($size === false) {
                return [];
            }

            $start = max(0, $size - $maxBytes);
            fseek($handle, $start);

            if ($start > 0) {
                fgets($handle); // Drop a partial line after the seek.
            }

            while (!feof($handle)) {
                $line = fgets($handle);
                if ($line === false) {
                    break;
                }

                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $decoded = json_decode($line, true);
                if (!is_array($decoded)) {
                    continue;
                }

                $level = app_normalize_log_level((string) ($decoded['level'] ?? 'info'));
                $message = (string) ($decoded['message'] ?? 'Log entry');
                $timestampRaw = (string) ($decoded['timestamp'] ?? date('Y-m-d H:i:s'));
                $parsedTimestamp = strtotime($timestampRaw);
                $timestamp = $parsedTimestamp !== false ? date('H:i:s', $parsedTimestamp) : date('H:i:s');
                $context = is_array($decoded['context'] ?? null) ? $decoded['context'] : [];

                $entries[] = app_make_dev_log_entry(
                    $message,
                    $level,
                    $context,
                    $context,
                    'file',
                    $timestamp
                );
            }
        } finally {
            fclose($handle);
        }

        if (count($entries) > $maxEntries) {
            $entries = array_slice($entries, -$maxEntries);
        }

        return $entries;
    }
}

if (!function_exists('app_issue_idempotency_token')) {
    function app_issue_idempotency_token(string $scope = 'default'): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        if (!isset($_SESSION['_idempotency_tokens']) || !is_array($_SESSION['_idempotency_tokens'])) {
            $_SESSION['_idempotency_tokens'] = [];
        }

        if (!isset($_SESSION['_idempotency_tokens'][$scope]) || !is_array($_SESSION['_idempotency_tokens'][$scope])) {
            $_SESSION['_idempotency_tokens'][$scope] = [];
        }

        // Keep the token bucket small to avoid unbounded session growth.
        if (count($_SESSION['_idempotency_tokens'][$scope]) > 50) {
            $_SESSION['_idempotency_tokens'][$scope] = array_slice($_SESSION['_idempotency_tokens'][$scope], -25, null, true);
        }

        $token = bin2hex(random_bytes(16));
        $_SESSION['_idempotency_tokens'][$scope][$token] = time();
        return $token;
    }
}

if (!function_exists('app_consume_idempotency_token')) {
    function app_consume_idempotency_token(string $scope, string $token, int $ttlSeconds = 1800): bool {
        if ($token === '') {
            return false;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $bucket = $_SESSION['_idempotency_tokens'][$scope] ?? null;
        if (!is_array($bucket) || !isset($bucket[$token])) {
            return false;
        }

        $issuedAt = (int) $bucket[$token];
        unset($_SESSION['_idempotency_tokens'][$scope][$token]);

        if ($issuedAt <= 0) {
            return false;
        }

        return (time() - $issuedAt) <= $ttlSeconds;
    }
}

if (!function_exists('app_log')) {
    function app_log(string $level, string $message, array $context = []): void {
        $logDir = __DIR__ . '/../logs';
        $level = app_normalize_log_level($level);
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => app_request_context($context),
        ];

        if (!is_dir($logDir) && !@mkdir($logDir, 0777, true) && !is_dir($logDir)) {
            error_log(sprintf('[%s] [%s] %s', $entry['timestamp'], strtoupper($level), $message));
            app_append_dev_log(app_make_dev_log_entry($message, $level, $entry['context'], $entry['context'], 'app_log', date('H:i:s')));
            return;
        }

        $payload = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

        if ($payload === false) {
            error_log(sprintf('[%s] [%s] %s', $entry['timestamp'], strtoupper($level), $message));
        } else {
            $written = @file_put_contents($logDir . '/app.log', $payload . PHP_EOL, FILE_APPEND | LOCK_EX);
            if ($written === false) {
                error_log($payload);
            }
        }

        app_append_dev_log(app_make_dev_log_entry($message, $level, $entry['context'], $entry['context'], 'app_log', date('H:i:s')));
    }
}

/**
 * Collect log events for the dev panel.
 */
function log_to_dev_panel($message, $type = 'info', $details = '') {
    app_append_dev_log(app_make_dev_log_entry((string) $message, (string) $type, $details, function_exists('app_request_context') ? app_request_context() : [], 'devpanel'));
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
    static $rendering = false;

    http_response_code(500);

    if (function_exists('app_log')) {
        app_log('error', 'Rendering 500 error page', [
            'error_message' => $message,
            'error_details' => $details,
        ]);
    }

    if ($rendering) {
        echo '<h1>500 - Server Error</h1><p>Something went wrong.</p>';
        exit;
    }

    $rendering = true;

    try {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $errorMessage = $message;
        $errorDetails = ''; // Always hide debug details from users
        $errorPage = dirname(__DIR__) . '/templates/pages/500.php';

        if (file_exists($errorPage)) {
            require $errorPage;
        } else {
            echo '<h1>500 - Server Error</h1><p>Something went wrong.</p>';
        }
    } catch (Throwable $renderError) {
        error_log('500 page rendering failed: ' . $renderError->getMessage());
        echo '<h1>500 - Server Error</h1><p>Something went wrong.</p>';
    }

    exit;
}
