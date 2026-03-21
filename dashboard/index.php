<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Fixed paths with leading slashes
require_once __DIR__ . '/../scripts/error_handler.php';
require_once __DIR__ . '/../scripts/db.php';

// 1. Parse the URL
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Extract the page name relative to /dashboard/
$requestedPath = str_replace('/dashboard/', '', $requestUri);
$page = trim($requestedPath, '/') ?: 'home';

// 3. Define valid routes
$routes = [
    'home'    => ['route' => __DIR__ . '/pages/home.php',    'title' => 'RevTrack Admin - Home'],
];

// 4. Determine which page to load
if (array_key_exists($page, $routes)) {
    $currentPage = $routes[$page];
} else {
    $currentPage = ['route' => __DIR__ . '/../pages/404.php', 'title' => '404 - Not Found'];
}

// 5. Define the file path variable used in the HTML below
$currentPageFile = $currentPage['route'];

// Log to your dev panel
if (function_exists('log_to_dev_panel')) {
    log_to_dev_panel("Loaded page: " . $page, "success");
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
    // Ensure this component exists in /dashboard/components/navbar.php
    if (file_exists(__DIR__ . '/components/navbar.php')) {
        require __DIR__ . '/components/navbar.php'; 
    }
    ?>

    <main data-bs-theme="dark" class="min-vh-100 d-flex flex-column">

        <div class="w-100 py-1 bg-primary bg-gradient shadow-sm mb-4" style="height: 3px;"></div>

        <div class="container flex-grow-1">
            <div class="content-fade-in mt-5">
                <?php 
                if (file_exists($currentPageFile)) {
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
        $devPanelPath = __DIR__ . '/../components/devpanel.php';
        if (file_exists($devPanelPath)) {
            require $devPanelPath;
        }
    }
    ?>

</body>
</html>