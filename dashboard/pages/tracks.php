<?php
require_once __DIR__ . '/../../scripts/track_repository.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$tracks = [];
$error = '';

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('Database connection is not available.');
    }

    $tracks = track_fetch_all($pdo);
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$flash = $_SESSION['tracks_flash'] ?? null;
unset($_SESSION['tracks_flash']);

$openDaysCount = static function (array $schedule): int {
    $count = 0;
    foreach (track_days() as $day) {
        if (!empty($schedule[$day]) && is_array($schedule[$day])) {
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
            <a href="/dashboard/tracks-create" class="btn btn-primary">Create New Track</a>
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
                                            <div class="fw-semibold"><?= htmlspecialchars($track['name']) ?></div>
                                            <small class="text-secondary"><?= htmlspecialchars($track['city']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($track['region']) ?></td>
                                        <td>
                                            <?php foreach ($track['tags'] as $tag): ?>
                                                <span class="badge text-bg-secondary me-1 mb-1"><?= htmlspecialchars($tag) ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td><?= $openDaysCount($track['schedule']) ?> / 7</td>
                                        <td class="text-end pe-3">
                                            <a href="/dashboard/tracks-edit?id=<?= (int) $track['id'] ?>" class="btn btn-sm btn-outline-info">Edit</a>
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
