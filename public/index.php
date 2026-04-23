<?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!ob_get_level()) {
        ob_start();
    }

    require_once __DIR__ . '/../config/init.php';
    require_once __DIR__ . '/../src/error_handler.php';
    require_once __DIR__ . '/../config/db.php';

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    if ($scriptDir !== '' && $scriptDir !== '.' && strpos($requestPath, $scriptDir) === 0) {
        $requestPath = substr($requestPath, strlen($scriptDir));
    }

    $requestPath = '/' . ltrim($requestPath, '/');
    $requestPath = preg_replace('#^/(?:index\.php/?)?#', '/', $requestPath);

    // Fallback to 'home' if the path is empty
    $page = trim((string) $requestPath, '/') ?: 'home';

    $routes = [
      'home' => [ 'template'=> __DIR__ . '/../templates/pages/home.php', 'title' => 'RevTrack - Home'],
      'about' => [ 'template'=> __DIR__ . '/../templates/pages/about.php', 'title' => 'RevTrack - About'],
      'tracks' => [ 'template'=> __DIR__ . '/../templates/pages/tracks.php', 'title' => 'RevTrack - Tracks'],
      'events' => [ 'template'=> __DIR__ . '/../templates/pages/events.php', 'title' => 'RevTrack - Events'],
      'event' => [ 'template'=> __DIR__ . '/../templates/pages/event.php', 'title' => 'RevTrack - Event Detail'],
      'contact' => [ 'template'=> __DIR__ . '/../templates/pages/contact.php', 'title' => 'RevTrack - Contact'],
      'login' => [ 'template'=> __DIR__ . '/../templates/pages/login.php', 'title' => 'RevTrack - Login'],
      'signup' => [ 'template'=> __DIR__ . '/../templates/pages/signup.php', 'title' => 'RevTrack - Signup'],
      'login-process' => [ 'action' => 'handleLogin', 'title' => 'RevTrack - Login Process'],
      'register-process' => [ 'action' => 'handleRegister', 'title' => 'RevTrack - Register Process'],
      'logout-process' => [ 'action' => 'handleLogout', 'title' => 'RevTrack - Logout Process'],
      'reset-session' => [ 'action' => 'handleResetSession', 'title' => 'RevTrack - Reset Session'],
    ];

    // Check if the file exists in our routes, otherwise 404
    $route = $routes[$page] ?? null;

    // Special handling for subfolders if they are reached through this script
    if (!$route) {
        if (strpos($page, 'dashboard/') === 0 || $page === 'dashboard') {
            require __DIR__ . '/dashboard/index.php';
            exit;
        }
        if (strpos($page, 'user/') === 0 || $page === 'user') {
            require __DIR__ . '/user/index.php';
            exit;
        }
    }

    // Handle OOP actions if present in route
    if (isset($route['action'])) {
        $authRepo = new \Repository\AuthRepository();
        $action = $route['action'];
        if (method_exists($authRepo, $action)) {
            $authRepo->$action();
            exit;
        }
    }

    $currentPageFile = $route['template'] ?? __DIR__ . '/../templates/pages/404.php';

    // If it's a process script, handle it before any HTML output
    if (strpos($page, '-process') !== false && !isset($route['action'])) {
        if (file_exists($currentPageFile)) {
            require_once $currentPageFile;
            exit;
        }
    }

    // Your app logic
    if (function_exists('log_to_dev_panel')) {
        log_to_dev_panel("Page loaded", "success");
    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <title><?= htmlspecialchars($routes[$page]['title'] ?? 'RevTrack') ?></title>
</head>
<body>
   

    <?php require __DIR__ . '/../templates/components/navbar.php'; ?>

    <main data-bs-theme="dark" class="bg-dark text-light min-vh-100 d-flex flex-column">
    
    <div class="w-100 py-1 bg-primary bg-gradient shadow-sm mb-4" style="height: 3px;"></div>

    <div class="container flex-grow-1">
        <div class="content-fade-in mt-5">
            <?php
            try {
                if (!file_exists($currentPageFile)) {
                    throw new RuntimeException('Page template not found: ' . $currentPageFile);
                }

                require $currentPageFile;
            } catch (Throwable $e) {
                if (function_exists('app_log')) {
                    app_log('error', 'Public page rendering failed', [
                        'page' => $page,
                        'template' => $currentPageFile,
                        'exception_class' => get_class($e),
                        'exception' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }

                render_server_error_page('Nepodarilo sa vykresliť stránku.');
            }
            ?>
        </div>
    </div>

    <footer class="py-4 mt-auto border-top border-secondary border-opacity-25">
        <div class="container text-center">
            <p class="text-secondary small mb-0">&copy; <?= date('Y') ?> RevTrack. All rights reserved.</p>
        </div>
    </footer>
</main>

<?php 
    if (defined('DEV_MODE') && DEV_MODE) {
        require __DIR__ . '/../templates/components/devpanel.php';
    }
    ?>

</body>
</html>
