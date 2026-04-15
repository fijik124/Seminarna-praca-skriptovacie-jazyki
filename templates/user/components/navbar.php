<?php $page = $page ?? 'home'; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary border-opacity-25">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="<?= url('user') ?>">
            <span class="text-white">Rev</span>Track <small class="text-info fw-normal" style="font-size: 0.7em;">User</small>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($page === 'home') ? 'active' : '' ?>" href="<?= url('user') ?>">Domov</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page === 'profile') ? 'active' : '' ?>" href="<?= url('user/profile') ?>">Profil</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-secondary me-3 small">
                    <?= htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']) ?>
                </span>
                <form action="<?= url('logout-process') ?>" method="POST" class="m-0">
                    <button type="submit" class="btn btn-outline-info btn-sm">Odhlásiť sa</button>
                </form>
            </div>
        </div>
    </div>
</nav>
