 <?php
    // Use ltrim to remove the leading slash so "/about" becomes "about"
    $requestedPath = ltrim($_SERVER['REQUEST_URI'], '/');
    
    // Fallback to 'home' if the path is empty
    $page = $requestedPath ?: 'home';

    $routes = [
      'home'    => __DIR__ . '/pages/home.php',
      'about'   => __DIR__ . '/pages/about.php',
      'contact' => __DIR__ . '/pages/contact.php',
      'login' => __DIR__ . '/pages/login.php',
      'signup' => __DIR__ . '/pages/signup.php',
    ];

    $pageTitles = [
      'home' => "Home - All Notes",
    ];

    

    // Check if the file exists in our routes, otherwise 404
    $currentPageFile = $routes[$page] ?? __DIR__ . '/pages/404.php';
    $title = $pageTitles[$page] ?? "All Notes";

    $devMode = true;
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
    <title><?php $title ?></title>
</head>
<body>
   

    <?php require __DIR__ . '/components/navbar.php'; ?>

    <main class="section">
      <div class="container">
        <?php require $currentPageFile; ?>
      </div>
    </main>

    <?php if ($devMode) {
      require __DIR__ . '/components/devpanel.php';
    };?>
</body>
</html>