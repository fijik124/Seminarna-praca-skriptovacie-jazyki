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
    <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css"
>
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