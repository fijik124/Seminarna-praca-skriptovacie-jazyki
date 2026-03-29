<?php
require_once __DIR__ . '/../../scripts/track_repository.php';

if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$regions = track_regions();
$tags = track_default_tags();
$days = track_days();

$formData = [
    'name' => '',
    'region' => '',
    'city' => '',
    'difficulty' => '',
    'surface' => '',
    'description' => '',
    'tags' => [],
    'schedule' => array_fill_keys($days, null),
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$payload, $errors] = track_validate_payload($_POST);
    $formData = $payload;

    if (!$errors) {
        try {
            if (!isset($pdo) || !($pdo instanceof PDO)) {
                throw new RuntimeException('Database connection is not available.');
            }

            track_insert($pdo, $payload);
            $_SESSION['tracks_flash'] = ['type' => 'success', 'message' => 'Track created successfully.'];
            header('Location: /dashboard/tracks');
            exit;
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$isEdit = false;
$submitLabel = 'Create Track';
?>

<section class="pt-5 pb-4 mt-5">
    <div class="container">
        <h1 class="h3 fw-bold mb-1">Create Track</h1>
        <p class="text-secondary mb-0">Add a new motocross track for Slovakia listing and filtering.</p>
    </div>
</section>

<section class="pb-5">
    <div class="container">
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card bg-body-tertiary border-secondary border-opacity-25">
            <div class="card-body p-4">
                <?php require __DIR__ . '/tracks_form.php'; ?>
            </div>
        </div>
    </div>
</section>
