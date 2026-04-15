<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../src/error_handler.php';
require_once __DIR__ . '/../../config/db.php';

$auth = new \Repository\AuthRepository();

// Flags for rendering state
$isLoggedIn = $auth->isLoggedIn();

// 1. Parse URL and normalize to app-relative path
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/user', PHP_URL_PATH) ?: '/user';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/user/index.php')), '/');

if ($scriptDir !== '' && $scriptDir !== '.' && strpos($requestPath, $scriptDir) === 0) {
    $requestPath = substr($requestPath, strlen($scriptDir));
}

$requestPath = '/' . ltrim($requestPath, '/');
$requestPath = preg_replace('#^/(?:index\.php/?)?#', '/', $requestPath);

// 2. Extract the page name relative to /user/
$requestedPath = preg_replace('#^/user(?:/index\.php)?/?#', '/', (string) $requestPath);
// Add another layer of normalization just in case
$requestedPath = preg_replace('#^user/?#', '/', (string) $requestedPath);
$page = trim((string) $requestedPath, '/') ?: 'home';

// 2.5 Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'profile-update') {
        $auth->handleProfileUpdate();
        exit;
    }
}

// 3. Define valid routes
$routes = [
    'home' => ['template' => __DIR__ . '/../../templates/user/pages/home.php', 'title' => 'RevTrack User - Home'],
    'profile' => ['template' => __DIR__ . '/../../templates/user/pages/profile.php', 'title' => 'RevTrack User - Profile'],
];

// 4. Determine which page to load
if (array_key_exists($page, $routes)) {
    $currentPage = $routes[$page];
} else {
    $currentPage = ['template' => __DIR__ . '/../../templates/pages/404.php', 'title' => '404 - Not Found'];
}

// 5. Define the file path variable used in the HTML below
$currentPageFile = $currentPage['template'];

// Log to your dev panel
if (function_exists('log_to_dev_panel')) {
    log_to_dev_panel("Loaded user page: " . $page, "success");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <title><?= htmlspecialchars($currentPage['title']) ?></title>
</head>

<body class="bg-dark text-light">

    <?php 
    if (file_exists(__DIR__ . '/../../templates/user/components/navbar.php')) {
        require __DIR__ . '/../../templates/user/components/navbar.php'; 
    }
    ?>

    <main data-bs-theme="dark" class="min-vh-100 d-flex flex-column">

        <div class="w-100 py-1 bg-info bg-gradient shadow-sm mb-4" style="height: 3px;"></div>

        <div class="container flex-grow-1">
            <div class="content-fade-in mt-5">
                <?php 
                if (!$isLoggedIn) {
                    echo "
                    <div class='text-center mt-5'>
                        <h2 class='mb-4'>Neni ste prihláseni</h2>
                        <a href='" . htmlspecialchars(url('login')) . "' class='btn btn-primary btn-lg'>Prihlásiť sa</a>
                    </div>";
                } elseif (file_exists($currentPageFile)) {
                    require $currentPageFile; 
                } else {
                    echo "<div class='alert alert-danger'>Error: File not found at " . htmlspecialchars($currentPageFile) . "</div>";
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
    // Developer Panel inclusion
    if (defined('DEV_MODE') && DEV_MODE) {
        $devPanelPath = __DIR__ . '/../../templates/components/devpanel.php';
        if (file_exists($devPanelPath)) {
            require $devPanelPath;
        }
    }
    ?>

</body>
</html>
