<?php
use Repository\TrackRepository;
use Repository\AuthRepository;
use Entity\Track;

$authRepo = new AuthRepository();
$authRepo->requireLogin();

$trackRepo = new TrackRepository();

// ==========================================
// ACTION: POST ROUTE INTERCEPTOR (DELETE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        
        // Safety boundary validation check
        if (!$authRepo->hasPermission('track_delete')) {
            $msg = base64_encode(json_encode(['type' => 'danger', 'message' => 'You do not have permission to delete tracks.']));
            header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            $msg = base64_encode(json_encode(['type' => 'danger', 'message' => 'Invalid track ID provided.']));
            header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
            exit;
        }

        try {
            $track = $trackRepo->getById($id);
            if (!$track) {
                $msg = base64_encode(json_encode(['type' => 'danger', 'message' => 'Track not found.']));
                header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
                exit;
            }

            if ($trackRepo->delete($id)) {
                $msg = base64_encode(json_encode(['type' => 'success', 'message' => 'Track deleted successfully.']));
            } else {
                throw new Exception("Database engine dropped execution query.");
            }

            header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
            exit;

        } catch (Throwable $e) {
            if (function_exists('app_log')) {
                app_log('error', 'Dashboard track deletion failed', [
                    'exception_class' => get_class($e),
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'id' => $id,
                ]);
            } elseif (function_exists('log_to_file')) {
                log_to_file('Dashboard track deletion failed for ID ' . $id . ': ' . $e->getMessage(), 'ERROR');
            }

            $msg = base64_encode(json_encode(['type' => 'danger', 'message' => 'Failed to delete track: ' . $e->getMessage()]));
            header('Location: ' . url('dashboard/tracks?msg=' . urlencode($msg)));
            exit;
        }
    }
}