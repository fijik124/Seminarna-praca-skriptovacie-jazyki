<?php

use Repository\TrackRepository;
use Repository\AuthRepository;
use Entity\Track;

$authRepo = new AuthRepository();
$authRepo->requireLogin();

$trackRepo = new TrackRepository();
$regions = TrackRepository::getRegions();
$tags = TrackRepository::getDefaultTags();
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

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
    [$track, $errors] = $trackRepo->validate($_POST);
    $formData = [
        'name' => $track->name,
        'region' => $track->region,
        'city' => $track->city,
        'difficulty' => $track->difficulty,
        'surface' => $track->surface,
        'description' => $track->description,
        'tags' => $track->tags,
        'schedule' => $track->schedule,
    ];

    if (!$errors) {
        try {
            $trackRepo->create($track);
            $msg = base64_encode(json_encode(['type' => 'success', 'message' => 'Track created successfully.']));
            header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
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
