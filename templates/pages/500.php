<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body class="bg-dark text-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container py-5">
        <div class="mx-auto" style="max-width: 760px;">
            <div class="card bg-black border-danger shadow-lg">
                <div class="card-body p-4 p-md-5">
                    <span class="badge text-bg-danger mb-3">500</span>
                    <h1 class="h2 mb-3">Server Error</h1>
                    <p class="text-secondary mb-4">
                        <?= htmlspecialchars($errorMessage ?? 'Unexpected server error. Please try again in a moment.') ?>
                    </p>

                    <?php if (!empty($errorDetails)): ?>
                        <div class="alert alert-warning small mb-4" role="alert">
                            <strong class="d-block mb-2">Debug details</strong>
                            <pre class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($errorDetails) ?></pre>
                        </div>
                    <?php endif; ?>

                    <a href="<?= url('/') ?>" class="btn btn-outline-light">Go to homepage</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
