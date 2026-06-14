<?php

use Repository\TrackRepository;

$trackRepo = new TrackRepository();

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
            if (function_exists('app_log')) {
                app_log('error', 'Dashboard track creation failed', [
                    'exception_class' => get_class($e),
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            } elseif (function_exists('log_to_file')) {
                log_to_file('Dashboard track creation failed: ' . $e->getMessage(), 'ERROR');
            }

            $errors[] = $e->getMessage();
        }
    }
}