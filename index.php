 <?php
    session_start();
    require './scripts/error_handler.php';
    // Use ltrim to remove the leading slash so "/about" becomes "about"
    $requestedPath = ltrim($_SERVER['REQUEST_URI'], '/');
    
    // Fallback to 'home' if the path is empty
    $page = $requestedPath ?: 'home';

    $routes = [
      'home' => [ 'route'=> __DIR__ . '/pages/home.php', 'title' => 'RevTrack - Home'],
      'about' => [ 'route'=> __DIR__ . '/pages/about.php', 'title' => 'RevTrack - About'],
      'contact' => [ 'route'=> __DIR__ . '/pages/contact.php', 'title' => 'RevTrack - Contact'],
      'login' => [ 'route'=> __DIR__ . '/pages/login.php', 'title' => 'RevTrack - Login'],
      'signup' => [ 'route'=> __DIR__ . '/pages/signup.php', 'title' => 'RevTrack - Signup'],
    ];

    

    // Check if the file exists in our routes, otherwise 404
    $currentPageFile = $routes[$page]['route'] ?? __DIR__ . '/pages/404.php';

    include './scripts/init.php'; // Contains the log_to_dev_panel function
  
  // Your app logic
  log_to_dev_panel("Page loaded", "success");
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
   

    <?php require __DIR__ . '/components/navbar.php'; ?>

    <main data-bs-theme="dark" class="bg-dark text-light min-vh-100 d-flex flex-column">
    
    <div class="w-100 py-1 bg-primary bg-gradient shadow-sm mb-4" style="height: 3px;"></div>

    <div class="container flex-grow-1">
        <div class="content-fade-in mt-5">
            <?php require $currentPageFile;?>
        </div>
    </div>

    <footer class="py-4 mt-auto border-top border-secondary border-opacity-25">
        <div class="container text-center">
            <p class="text-secondary small mb-0">&copy; 2026 RevTrack. All rights reserved.</p>
        </div>
    </footer>
</main>

<?php 
    if (defined('DEV_MODE') && DEV_MODE) {
        require __DIR__ . '/components/devpanel.php';
    }
    ?>

</body>
</html>