<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../src/error_handler.php';
require_once __DIR__ . '/../../config/db.php';

$auth = new \Repository\AuthRepository();

// Flags for rendering state
$isLoggedIn = $auth->isLoggedIn();
$hasDashboardView = $isLoggedIn && $auth->hasPermission('dashboard_view');

// 1. Parse URL and normalize to app-relative path
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/dashboard', PHP_URL_PATH) ?: '/dashboard';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/dashboard/index.php')), '/');

if ($scriptDir !== '' && $scriptDir !== '.' && strpos($requestPath, $scriptDir) === 0) {
    $requestPath = substr($requestPath, strlen($scriptDir));
}

$requestPath = '/' . ltrim($requestPath, '/');
$requestPath = preg_replace('#^/(?:index\.php/?)?#', '/', $requestPath);

// 2. Extract the page name relative to /dashboard/
$requestedPath = preg_replace('#^/dashboard(?:/index\.php)?/?#', '/', (string) $requestPath);
// Add another layer of normalization just in case
$requestedPath = preg_replace('#^dashboard/?#', '/', (string) $requestedPath);
$page = trim((string) $requestedPath, '/') ?: 'home';

// 3. Define valid routes
$routes = [
    'home'    => ['template' => __DIR__ . '/../../templates/dashboard/pages/home.php',    'title' => 'RevTrack Admin - Home'],
    'tracks' => ['template' => __DIR__ . '/../../templates/dashboard/pages/tracks.php', 'title' => 'RevTrack Admin - Tracks'],
    'tracks-create' => [
        'template' => __DIR__ . '/../../templates/dashboard/pages/tracks_create.php', 
        'title' => 'RevTrack Admin - Create Track',
        'permission' => 'track_create'
    ],
    'tracks-edit' => [
        'template' => __DIR__ . '/../../templates/dashboard/pages/tracks_edit.php', 
        'title' => 'RevTrack Admin - Edit Track',
        'permission' => 'track_edit'
    ],
];

// 4. Determine which page to load
$hasPagePermission = true;
if (array_key_exists($page, $routes)) {
    $currentPage = $routes[$page];
    
    // Check page specific permission
    if (isset($currentPage['permission'])) {
        $hasPagePermission = $isLoggedIn && $auth->hasPermission($currentPage['permission']);
    }
} else {
    $currentPage = ['template' => __DIR__ . '/../../templates/pages/404.php', 'title' => '404 - Not Found'];
}

// 5. Define the file path variable used in the HTML below
$currentPageFile = $currentPage['template'];

// Log to your dev panel
if (function_exists('log_to_dev_panel')) {
    log_to_dev_panel("Loaded dashboard page: " . $page, "success");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <title><?= htmlspecialchars($currentPage['title']) ?></title>
</head>

<body class="bg-dark text-light">

    <?php 
    if (file_exists(__DIR__ . '/../../templates/dashboard/components/navbar.php')) {
        require __DIR__ . '/../../templates/dashboard/components/navbar.php'; 
    }
    ?>

    <main data-bs-theme="dark" class="min-vh-100 d-flex flex-column">

        <div class="w-100 py-1 bg-primary bg-gradient shadow-sm mb-4" style="height: 3px;"></div>

        <div class="container flex-grow-1">
            <div class="content-fade-in mt-5">
                <?php 
                if (!$isLoggedIn) {
                    echo "
                    <div class='text-center mt-5'>
                        <h2 class='mb-4'>Neni ste prihláseni</h2>
                        <a href='" . htmlspecialchars(url('login')) . "' class='btn btn-primary btn-lg'>Prihlásiť sa</a>
                    </div>";
                } elseif (!$hasDashboardView || !$hasPagePermission) {
                    echo "
                    <div class='alert alert-warning text-center mt-5 py-5 shadow-sm'>
                        <h3 class='mb-0'>Nemaťe opravnenie na to aby ste vydeli tento obsah</h3>
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
