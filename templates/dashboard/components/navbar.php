<?php
$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']['id']);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/dashboard/home', PHP_URL_PATH) ?: '/dashboard/home';
$dashboardPath = preg_replace('#^/dashboard/?#', '', $requestPath);
$currentPage = trim($dashboardPath, '/') ?: 'home';

$navActive = static function (string $page) use ($currentPage): string {
    return $currentPage === $page ? ' active text-primary' : '';
};

$user = $_SESSION['user'] ?? [];
$firstName = trim((string) ($user['first_name'] ?? 'User'));
$lastName = trim((string) ($user['last_name'] ?? ''));
$email = trim((string) ($user['email'] ?? ''));
$fullName = trim($firstName . ' ' . $lastName);
$displayName = $fullName !== '' ? $fullName : 'User';
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
if ($initials === '') {
  $initials = 'U';
}
?>

<nav class="navbar navbar-expand-lg fixed-top py-3" data-bs-theme="dark"
     style="background: rgba(33, 37, 41, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1);">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= url('dashboard/home') ?>">
      <div class="bg-primary bg-gradient rounded-3 p-2 me-2 d-flex align-items-center justify-content-center shadow-primary" style="width: 35px; height: 35px;">
        <i class="fas fa-gauge-high text-white fs-6"></i>
      </div>
      <span class="fw-black tracking-tight text-white">RevTrack <span class="text-primary">Dashboard</span></span>
    </a>

    <?php if ($isLoggedIn): ?>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect<?= $navActive('home') ?>" href="<?= url('dashboard/home') ?>"<?= $currentPage === 'home' ? ' aria-current="page"' : '' ?>>Overview</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect<?= $navActive('tracks') ?>" href="<?= url('dashboard/tracks') ?>"<?= $currentPage === 'tracks' ? ' aria-current="page"' : '' ?>>Tracks</a>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-3">
        <form class="d-none d-xl-flex" role="search" method="get" action="<?= url('dashboard/home') ?>">
          <label for="dashboard_nav_search" class="visually-hidden">Search dashboard</label>
          <div class="input-group">
            <span class="input-group-text bg-dark border-secondary text-secondary border-end-0 rounded-start-pill px-3">
                <i class="fas fa-search"></i>
            </span>
            <input class="form-control bg-dark border-secondary text-white border-start-0 rounded-end-pill ps-0"
                   id="dashboard_nav_search"
                   name="q" type="search" placeholder="Search dashboard..." style="width: 170px;">
          </div>
        </form>

        <div class="d-flex gap-2 align-items-center">

          <div class="dropdown">
            <button class="btn btn-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm dropdown-toggle p-0 profile-toggle"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User profile menu">
              <span class="fw-bold small"><?= htmlspecialchars($initials) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border-secondary mt-3">
              <li><h6 class="dropdown-header text-white"><?= htmlspecialchars($displayName) ?></h6></li>
              <?php if (!empty($user['group_id'])): ?>
                <li><span class="dropdown-item-text text-info small">Group: <?= (int)$user['group_id'] === 1 ? 'Admin' : 'User' ?></span></li>
              <?php endif; ?>
              <?php if ($email !== ''): ?>
                <li><span class="dropdown-item-text text-secondary small"><?= htmlspecialchars($email) ?></span></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider border-secondary"></li>
              <li><a class="dropdown-item py-2" href="<?= url('dashboard/home') ?>"><i class="fas fa-gauge-high me-2 opacity-50"></i>Dashboard</a></li>
              <li>
                <form action="<?= url('logout-process') ?>" method="post" class="m-0">
                  <button type="submit" class="dropdown-item py-2 text-danger">
                    <i class="fas fa-right-from-bracket me-2 opacity-50"></i>Log out
                  </button>
                </form>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="ms-auto">
        <a href="<?= url('login') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">Login</a>
    </div>
    <?php endif; ?>
  </div>
</nav>

<span class="d-none nav-hover-effect nav-link active" aria-hidden="true"></span>

<style>
  /*noinspection CssUnusedSymbol*/
  .fw-black { font-weight: 900; letter-spacing: -0.5px; }
  
  /* Glassmorphism effect for dropdowns */
  .dropdown-menu {
      background: rgba(33, 37, 41, 0.95) !important;
      backdrop-filter: blur(15px);
  }

  /* Soft glow for the primary brand icon */
  .shadow-primary {
      box-shadow: 0 0 15px rgba(13, 110, 253, 0.4);
  }

  /* Underline hover effect */
  .nav-hover-effect::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 5px;
      left: 15%;
      background: #0d6efd;
      transition: width 0.3s ease;
  }
  .nav-hover-effect:hover::after {
      width: 70%;
  }

  .nav-link.active::after {
      width: 70%;
  }

  .profile-toggle {
      width: 42px;
      height: 42px;
  }
</style>