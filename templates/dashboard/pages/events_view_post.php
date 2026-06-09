<?php

use Repository\AuthRepository;
use Repository\EventRepository;

$auth = new AuthRepository();
$currentUser = $auth->getCurrentUser();
$currentUserId = (int)($currentUser['id'] ?? 0);

$isAdmin = $auth->isAdmin();
$isOrganizer = $auth->isOrganizer();

$canManageMessages = $isAdmin
    || $isOrganizer
    || $auth->hasPermission('event_message_reply');

$canReviewRegistrations = $isAdmin
    || $isOrganizer
    || $auth->hasPermission('event_registration_request_review');

$eventRepo = new EventRepository();

$slug = trim((string)($_GET['event'] ?? ''));
$event = $slug !== ''
    ? $eventRepo->findAccessibleBySlug($slug, $currentUserId, $isAdmin)
    : null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$event) {
    return;
}

$action = trim((string)($_POST['action'] ?? ''));

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? 'https'
    : 'http';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$baseUrl = $protocol . '://' . $host . '/dashboard/events-view';

try {

    if ($action === 'assign_organizer' && $isAdmin) {

        $assignedUserId = (int)($_POST['assigned_user_id'] ?? 0);
        $assignmentType = trim((string)($_POST['assignment_type'] ?? 'assigned'));
        $notes = trim((string)($_POST['notes'] ?? ''));

        if ($assignedUserId > 0) {

            $eventRepo->assignUserToEvent(
                (int)$event->id,
                $assignedUserId,
                $currentUserId,
                $assignmentType,
                $notes
            );

            header(
                'Location: ' .
                $baseUrl .
                '?event=' . urlencode($event->slug) .
                '&type=success&msg=' . urlencode('Organizer assignment updated.')
            );

            exit;
        }
    }

    elseif ($action === 'mark_message_read' && $canManageMessages) {

        $messageId = (int)($_POST['message_id'] ?? 0);

        if ($messageId > 0) {

            $eventRepo->markMessageRead($messageId, $currentUserId);

            header(
                'Location: ' .
                $baseUrl .
                '?event=' . urlencode($event->slug) .
                '&type=info&msg=' . urlencode('Message marked as read.')
            );

            exit;
        }
    }

    elseif ($action === 'reply_message' && $canManageMessages) {

        $messageId = (int)($_POST['message_id'] ?? 0);
        $replyBody = trim((string)($_POST['reply_body'] ?? ''));

        if ($messageId <= 0 || $replyBody === '') {

            header(
                'Location: ' .
                $baseUrl .
                '?event=' . urlencode($event->slug) .
                '&type=danger&msg=' . urlencode('Reply body cannot be empty.')
            );

            exit;
        }

        $eventRepo->addMessageReply(
            $messageId,
            $currentUserId,
            $replyBody
        );

        $eventRepo->markMessageRead(
            $messageId,
            $currentUserId
        );

        header(
            'Location: ' .
            $baseUrl .
            '?event=' . urlencode($event->slug) .
            '&type=success&msg=' . urlencode('Reply sent.')
        );

        exit;
    }

    elseif ($action === 'review_registration' && $canReviewRegistrations) {

        $requestId = (int)($_POST['request_id'] ?? 0);
        $decision = trim((string)($_POST['decision'] ?? ''));
        $reviewNote = trim((string)($_POST['review_note'] ?? ''));

        if (
            $requestId > 0
            && in_array($decision, ['approved', 'rejected'], true)
        ) {

            $eventRepo->reviewRegistrationRequest(
                $requestId,
                $currentUserId,
                $decision,
                $reviewNote
            );

            header(
                'Location: ' .
                $baseUrl .
                '?event=' . urlencode($event->slug) .
                '&type=success&msg=' . urlencode('Registration request updated.')
            );

            exit;
        }
    }

} catch (Throwable $e) {

    if (function_exists('app_log')) {

        app_log('error', 'Dashboard event action failed', [
            'event_slug' => $event->slug,
            'action' => $action,
            'exception' => $e->getMessage(),
        ]);
    }

    header(
        'Location: ' .
        $baseUrl .
        '?event=' . urlencode($event->slug) .
        '&type=danger&msg=' .
        urlencode('An error occurred: ' . $e->getMessage())
    );

    exit;
}