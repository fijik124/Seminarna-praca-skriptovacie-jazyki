<?php

use Repository\TrackRepository;
use Repository\AuthRepository;
use Entity\Track;

$authRepo = new AuthRepository();
$authRepo->requireLogin();

$trackRepo = new TrackRepository();
$tracks = [];
$error = '';

try {
    $tracks = $trackRepo->getAll();
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$flash = null;
if (isset($_GET['msg'])) {
    $decodedMsg = base64_decode($_GET['msg']);
    if ($decodedMsg) {
        $flash = json_decode($decodedMsg, true);
    }
}

$openDaysCount = static function (Track $track): int {
    $count = 0;
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ($days as $day) {
        if (!empty($track->schedule[$day])) {
            $count++;
        }
    }
    return $count;
};
?>

<section class="pt-5 pb-4 mt-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="text-secondary text-uppercase small mb-1">Admin Panel</p>
                <h1 class="h3 fw-bold mb-0">Track Management</h1>
            </div>
            <?php if ($authRepo->hasPermission('track_create')): ?>
                <a href="<?= url('dashboard/tracks-create') ?>" class="btn btn-primary">Create New Track</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> mb-3">
                <?= htmlspecialchars($flash['message'] ?? '') ?>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <div class="card bg-body-tertiary border-secondary border-opacity-25">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Track</th>
                                    <th>Region</th>
                                    <th>Tags</th>
                                    <th>Open Days</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$tracks): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-4">No tracks yet. Create your first one.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($tracks as $track): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold"><?= htmlspecialchars($track->name) ?></div>
                                            <small class="text-secondary"><?= htmlspecialchars($track->city) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($track->region) ?></td>
                                        <td>
                                            <?php foreach ($track->tags as $tag): ?>
                                                <span class="badge text-bg-secondary me-1 mb-1"><?= htmlspecialchars($tag) ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td><?= $openDaysCount($track) ?> / 7</td>
                                        <td class="text-end pe-3">
                                            <?php if ($authRepo->hasPermission('track_edit')): ?>
                                                <a href="<?= url('dashboard/tracks-edit?id=' . (int) $track->id) ?>" class="btn btn-sm btn-outline-info">Edit</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
