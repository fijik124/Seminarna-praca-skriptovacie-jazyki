<nav class="navbar navbar-expand-lg fixed-top py-3" data-bs-theme="dark" 
     style="background: rgba(33, 37, 41, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1);">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= url('/') ?>">
      <div class="bg-primary bg-gradient rounded-3 p-2 me-2 d-flex align-items-center justify-content-center shadow-primary" style="width: 35px; height: 35px;">
        <i class="fas fa-rocket text-white fs-6"></i>
      </div>
      <span class="fw-black tracking-tight text-white">Rev<span class="text-primary">Track</span></span>
    </a>
    
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect" href="<?= url('/') ?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect" href="<?= url('about') ?>">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect" href="<?= url('tracks') ?>">Trate</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect" href="<?= url('events') ?>">Events</a>
        </li>
      </ul>
      
      <div class="d-flex align-items-center gap-3">

        
        <div class="d-flex gap-2 align-items-center">
          <?php if (isset($_SESSION['user'])): ?>
            <div class="d-flex align-items-center me-2">
              <div class="bg-info bg-opacity-10 border border-info border-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                <span class="text-info fw-bold" style="font-size: 0.8rem;"><?= strtoupper(substr($_SESSION['user']['first_name'], 0, 1)) ?></span>
              </div>
              <span class="text-white small d-none d-sm-inline fw-medium"><?= htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']) ?></span>
            </div>
            <?php if (in_array('dashboard_view', $_SESSION['user']['permissions'] ?? [])): ?>
              <a href="<?= url('dashboard') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">Admin Dashboard</a>
            <?php endif; ?>
            <a href="<?= url('user/profile') ?>" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">Profil</a>
            <form action="<?= url('logout-process') ?>" method="POST" class="m-0">
              <button type="submit" class="btn btn-link text-white text-decoration-none fw-medium px-2 small">Logout</button>
            </form>
          <?php else: ?>
            <a href="<?= url('login') ?>" class="btn btn-link text-white text-decoration-none fw-medium px-3">Login</a>
            <a href="<?= url('signup') ?>" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Sign Up</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</nav>

<style>
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
</style>