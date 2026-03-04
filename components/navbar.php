<nav class="navbar" role="navigation" aria-label="main navigation">
  <?php $currentPage = $page ?? 'home'; ?>
  <div class="navbar-brand">
    <a class="navbar-item" href="index.php?page=home">My Project</a>

    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>

  <div id="navbarBasicExample" class="navbar-menu">
    <div class="navbar-start">
      <a class="navbar-item <?php echo $currentPage === 'home' ? 'is-active' : ''; ?>" href="index.php?page=home">
        Home
      </a>

      <a class="navbar-item <?php echo $currentPage === 'about' ? 'is-active' : ''; ?>" href="index.php?page=about">
        About
      </a>

      <a class="navbar-item <?php echo $currentPage === 'contact' ? 'is-active' : ''; ?>" href="index.php?page=contact">
        Contact
      </a>
    </div>

    <div class="navbar-end">
      <div class="navbar-item">
        <div class="buttons">
          <a class="button is-primary">
            <strong>Sign up</strong>
          </a>
          <a class="button is-light" href='/login'>
            Log in
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>