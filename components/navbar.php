<nav class="navbar navbar-expand-lg fixed-top py-3" data-bs-theme="dark" 
     style="background: rgba(33, 37, 41, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1);">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/">
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
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect" href="/">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect" href="/about">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 fw-medium position-relative nav-hover-effect" href="/tracks">Trate</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle px-3 fw-medium" href="#" data-bs-toggle="dropdown">Services</a>
          <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-secondary mt-3 animate slideIn">
            <li><a class="dropdown-item py-2" href="#"><i class="fas fa-layer-group me-2 opacity-50"></i>Web Dev</a></li>
            <li><a class="dropdown-item py-2" href="#"><i class="fas fa-database me-2 opacity-50"></i>Database</a></li>
            <li><hr class="dropdown-divider border-secondary"></li>
            <li><a class="dropdown-item py-2 text-danger" href="#"><i class="fas fa-bug me-2 opacity-50"></i>Report Bug</a></li>
          </ul>
        </li>
      </ul>
      
      <div class="d-flex align-items-center gap-3">
        <form class="d-none d-xl-flex" role="search">
          <div class="input-group">
            <span class="input-group-text bg-dark border-secondary text-secondary border-end-0 rounded-start-pill px-3">
                <i class="fas fa-search"></i>
            </span>
            <input class="form-control bg-dark border-secondary text-white border-start-0 rounded-end-pill ps-0" 
                   type="search" placeholder="Search notes..." style="width: 150px;">
          </div>
        </form>
        
        <div class="d-flex gap-2">
          <a href="/login" class="btn btn-link text-white text-decoration-none fw-medium px-3">Login</a>
          <a href="/signup" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Sign Up</a>
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