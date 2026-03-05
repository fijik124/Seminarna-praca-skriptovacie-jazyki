<nav class="navbar navbar-expand-lg fixed-top border-bottom bg-white shadow-sm py-2">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <i class="fas fa-rocket text-primary me-2"></i>
      <span class="fw-bold">All Notes</span>
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link px-3" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link px-3" href="#">About</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle px-3" href="#" data-bs-toggle="dropdown">Services</a>
          <ul class="dropdown-menu shadow-sm border-0 mt-2">
            <li><a class="dropdown-item" href="#">Web Dev</a></li>
            <li><a class="dropdown-item" href="#">Database</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#">Report Bug</a></li>
          </ul>
        </li>
      </ul>
      
      <form class="d-flex me-3" role="search">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
          <input class="form-control bg-light border-start-0" type="search" placeholder="Search...">
        </div>
      </form>
      
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-primary rounded-pill px-4">Sign Up</button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-4">Login</button>
      </div>
    </div>
  </div>
</nav>