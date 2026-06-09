<?php
/**
 * Event Header Component
 * Displays event title, type, status, and navigation
 *
 * @var object $event
 * @var array|null $flash
 */
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-secondary text-uppercase small mb-1">Dashboard Event Management</p>
        <h1 class="h3 fw-bold mb-2"><?= htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge <?= htmlspecialchars($event->typeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($event->type, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge text-bg-secondary"><?= htmlspecialchars($event->status, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('dashboard/events') ?>" class="btn btn-outline-light">Back to list</a>
        <a href="<?= url('event?event=' . urlencode($event->slug)) ?>" class="btn btn-outline-info">Public page</a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($flash['text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

