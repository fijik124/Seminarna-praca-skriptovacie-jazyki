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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    $msg = base64_encode(json_encode(['type' => 'warning', 'message' => 'Invalid track ID.']));
    header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
    exit;
}

$errors = [];
$formData = null;

try {
    $trackObj = $trackRepo->getById($id);
    if (!$trackObj) {
        $msg = base64_encode(json_encode(['type' => 'warning', 'message' => 'Track was not found.']));
        header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
        exit;
    }

    $formData = [
        'name' => $trackObj->name,
        'region' => $trackObj->region,
        'city' => $trackObj->city,
        'difficulty' => $trackObj->difficulty,
        'surface' => $trackObj->surface,
        'description' => $trackObj->description,
        'tags' => $trackObj->tags,
        'schedule' => $trackObj->schedule,
    ];
} catch (Throwable $e) {
    if (function_exists('app_log')) {
        app_log('error', 'Dashboard track load failed', [
            'track_id' => $id,
            'exception_class' => get_class($e),
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } elseif (function_exists('log_to_file')) {
        log_to_file('Dashboard track load failed: ' . $e->getMessage(), 'ERROR');
    }

    $errors[] = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['id'] = $id; // Ensure ID is passed for validation if needed
    [$trackToUpdate, $errors] = $trackRepo->validate($_POST);
    
    $formData = [
        'name' => $trackToUpdate->name,
        'region' => $trackToUpdate->region,
        'city' => $trackToUpdate->city,
        'difficulty' => $trackToUpdate->difficulty,
        'surface' => $trackToUpdate->surface,
        'description' => $trackToUpdate->description,
        'tags' => $trackToUpdate->tags,
        'schedule' => $trackToUpdate->schedule,
    ];

    if (!$errors) {
        try {
            $trackToUpdate->id = $id;
            $trackRepo->update($trackToUpdate);
            $msg = base64_encode(json_encode(['type' => 'success', 'message' => 'Track updated successfully.']));
            header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
            exit;
        } catch (Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'Dashboard track update failed', [
                    'track_id' => $id,
                    'exception_class' => get_class($e),
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            } elseif (function_exists('log_to_file')) {
                log_to_file('Dashboard track update failed: ' . $e->getMessage(), 'ERROR');
            }

            $errors[] = $e->getMessage();
        }
    }
}

if (!$formData) {
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
}

$isEdit = true;
$submitLabel = 'Save Changes';
?>

<section class="pt-5 pb-4 mt-5">
    <div class="container">
        <h1 class="h3 fw-bold mb-1">Edit Track</h1>
        <p class="text-secondary mb-0">Update track details, tags, and opening schedule.</p>
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
