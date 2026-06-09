<?php

use Repository\AuthRepository;
use Repository\EventRepository;

if (!function_exists('event_detail_redirect')) {
    function event_detail_redirect(string $slug, array $payload = [], string $type = 'msg'): void {
        $query = ['event' => $slug];

        if (!empty($payload)) {
            $query[$type] = base64_encode(json_encode($payload));
        }

        header('Location: ' . url('event?' . http_build_query($query)));
        exit;
    }
}

$auth = new AuthRepository();
$events = new EventRepository();

$auth->ensureSession();

$slug = trim((string) ($_GET['event'] ?? ''));
$event = $slug !== '' ? $events->findBySlug($slug) : null;
$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$event) {
    return;
}

$action = trim((string) ($_POST['action'] ?? ''));
$token = trim((string) ($_POST['idempotency_token'] ?? ''));

if (!app_consume_idempotency_token('event_detail_' . $event->slug, $token)) {
    event_detail_redirect($event->slug, [
        'type' => 'info',
        'text' => 'Action was already processed or expired. Please try again.',
    ]);
}

try {
    $handled = false;

    if ($action === 'send_message') {
        $handled = true;

        $auth->requireLogin();

        $subject = trim((string) ($_POST['subject'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($message === '') {
            event_detail_redirect($event->slug, [
                'type' => 'error',
                'text' => 'Message text is required.',
            ], 'error');
        }

        if ($subject === '') {
            $subject = 'Message about ' . $event->title;
        }

        $events->addMessage(
            (int) $event->id,
            (int) $currentUser['id'],
            (string) (($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')),
            (string) ($currentUser['email'] ?? ''),
            $subject,
            $message
        );

        event_detail_redirect($event->slug, [
            'type' => 'success',
            'text' => 'Your message was sent to the event organizer.',
        ]);
    }

    if ($action === 'request_registration') {
        $handled = true;

        $auth->requirePermission('event_registration_request_create');

        $note = trim((string) ($_POST['note'] ?? ''));

        $result = $events->requestRegistration(
            (int) $event->id,
            (int) $currentUser['id'],
            (string) ($currentUser['first_name'] ?? ''),
            (string) ($currentUser['last_name'] ?? ''),
            (string) ($currentUser['email'] ?? ''),
            $note
        );

        if (!empty($result['created'])) {
            event_detail_redirect($event->slug, [
                'type' => 'success',
                'text' => 'Your marshal registration request is pending organizer approval.',
            ]);
        }

        event_detail_redirect($event->slug, [
            'type' => 'info',
            'text' => 'You already have a registration request on this event.',
        ]);
    }

    if ($action === 'review_registration') {
        $handled = true;

        $auth->requirePermission('event_registration_request_review');

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $decision = trim((string) ($_POST['decision'] ?? ''));
        $reviewNote = trim((string) ($_POST['review_note'] ?? ''));

        if ($requestId <= 0 || !in_array($decision, ['approved', 'rejected'], true)) {
            event_detail_redirect($event->slug, [
                'type' => 'error',
                'text' => 'Invalid review request.',
            ], 'error');
        }

        if ($events->reviewRegistrationRequest($requestId, (int) $currentUser['id'], $decision, $reviewNote)) {
            event_detail_redirect($event->slug, [
                'type' => 'success',
                'text' => 'Registration request updated.',
            ]);
        }

        event_detail_redirect($event->slug, [
            'type' => 'error',
            'text' => 'Unable to update registration request.',
        ], 'error');
    }

    if (!$handled) {
        event_detail_redirect($event->slug, [
            'type' => 'error',
            'text' => 'Unknown action.',
        ], 'error');
    }

} catch (Throwable $e) {
    if (function_exists('app_log')) {
        app_log('error', 'Event detail action failed', [
            'event_slug' => $event->slug,
            'action' => $action,
            'exception_class' => get_class($e),
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    event_detail_redirect($event->slug, [
        'type' => 'error',
        'text' => 'Something went wrong while processing your request.',
    ], 'error');
}