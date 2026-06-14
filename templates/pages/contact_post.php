<?php
// templates/pages/contact_post.php

use Entity\Contact;
use Repository\ContactRepository;

$email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$subject = trim((string)($_POST['subject'] ?? ''));

if (!$email || $subject === '') {
    $status = 'invalid';

    // Redirect with the validation error status
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $currentScript = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

    header("Location: " . $protocol . "://" . $host . $currentScript . "?status=" . $status);
    exit;
} else {
    try {
        $contactRepo = new ContactRepository();

        $contact = new Contact([
            'email' => $email,
            'subject' => $subject
        ]);

        $contactRepo->create($contact);

        // Redirect with success status
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $currentScript = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

        header("Location: " . $protocol . "://" . $host . $currentScript . "?status=success");
        exit;

    } catch (Throwable $e) {
        // --- PASS TO GLOBAL ERROR ENGINE ---
        // This stops the redirect loop and forces your global error handler
        // to process the crash, write to app.log, and show the error page.
        throw $e;
    }
}