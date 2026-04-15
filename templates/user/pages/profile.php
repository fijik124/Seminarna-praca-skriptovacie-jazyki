<?php
// Handle success/error messages
$msg = null;
if (isset($_GET['msg'])) {
    $msg = json_decode(base64_decode($_GET['msg']), true);
}

$errors = [];
if (isset($_GET['errors'])) {
    $errors = json_decode(base64_decode($_GET['errors']), true);
}
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="display-4 fw-bold mb-4">Môj Profil</h1>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <?= htmlspecialchars($msg['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card bg-secondary bg-opacity-10 border-secondary border-opacity-25 shadow-sm">
            <div class="card-header border-secondary border-opacity-25 py-3">
                <h5 class="mb-0 text-info">Upraviť Osobné Informácie</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= url('user/profile-update') ?>" method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="bg-dark border border-secondary border-opacity-25 rounded-3 d-flex flex-column align-items-center justify-content-center p-4 shadow-sm h-100">
                                <div class="bg-info bg-opacity-10 border border-info border-opacity-25 rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 120px; height: 120px;">
                                    <span class="display-3 text-info fw-bold"><?= strtoupper(substr($_SESSION['user']['first_name'], 0, 1)) ?></span>
                                </div>
                                <h4 class="mb-1"><?= htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']) ?></h4>
                                <p class="text-secondary small mb-0">
                                    <?php 
                                    $is_admin = in_array('dashboard_view', $_SESSION['user']['permissions'] ?? []);
                                    echo $is_admin ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-info">Užívateľ</span>';
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label text-secondary small fw-bold">MENO</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control bg-dark border-secondary border-opacity-50 text-light py-2 <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($_SESSION['user']['first_name']) ?>">
                                    <?php if (isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label text-secondary small fw-bold">PRIEZVISKO</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control bg-dark border-secondary border-opacity-50 text-light py-2 <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($_SESSION['user']['last_name']) ?>">
                                    <?php if (isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label text-secondary small fw-bold">EMAILOVÁ ADRESA</label>
                                    <input type="email" id="email" name="email" class="form-control bg-dark border-secondary border-opacity-50 text-light py-2 <?= (isset($errors['email']) || isset($errors['email_exists'])) ? 'is-invalid' : '' ?>" 
                                           value="<?= htmlspecialchars($_SESSION['user']['email']) ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                    <?php elseif (isset($errors['email_exists'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['email_exists']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php if (isset($errors['general'])): ?>
                                    <div class="col-12">
                                        <div class="alert alert-danger py-2 small border-0 shadow-sm"><?= htmlspecialchars($errors['general']) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-end">
                        <button type="submit" class="btn btn-info px-4 fw-bold">Uložiť Zmeny</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4 bg-secondary bg-opacity-10 border-secondary border-opacity-25 shadow-sm">
            <div class="card-header border-secondary border-opacity-25 py-3">
                <h5 class="mb-0 text-secondary">Zabezpečenie</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1">Heslo</h6>
                        <p class="text-secondary small mb-0">Zmeňte si svoje prístupové heslo k účtu.</p>
                    </div>
                    <button disabled class="btn btn-sm btn-outline-secondary">Zmeniť heslo (Už čoskoro)</button>
                </div>
            </div>
        </div>

        <div class="mt-4">
             <a href="<?= url('user') ?>" class="btn btn-outline-info">Späť na nástenku</a>
        </div>
    </div>
</div>
