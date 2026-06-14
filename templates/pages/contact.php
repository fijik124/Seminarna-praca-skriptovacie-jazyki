<?php
// templates/pages/contact.php

$statusParam = $_GET['status'] ?? null;
$status = null;
$message = null;

if ($statusParam === 'success') {
    $status = 'success';
    $message = 'Your message has been saved successfully!';
} elseif ($statusParam === 'invalid') {
    $status = 'danger';
    $message = 'Please fill out all fields correctly.';
} elseif ($statusParam === 'error') {
    $status = 'danger';
    $message = 'An error occurred while saving your message.';
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <h1 class="title text-center mb-2">Contact</h1>
        <p class="subtitle text-center text-muted mb-5">Got a question or feedback? We would love to hear from you.</p>

        <?php if ($status && $message): ?>
            <div class="alert alert-<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 bg-dark text-light p-4">
            <div class="card-body">
                <form action="" method="POST">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control bg-secondary text-white border-0" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control bg-secondary text-white border-0" required>
                        </div>

                        <div class="col-12">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" name="subject" id="subject" class="form-control bg-secondary text-white border-0" required>
                        </div>

                        <div class="col-12">
                            <label for="message" class="form-label">Your Message</label>
                            <textarea name="message" id="message" rows="5" class="form-control bg-secondary text-white border-0" required></textarea>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-outline-light px-4 mt-2">Send Message</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>