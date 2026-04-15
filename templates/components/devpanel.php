<?php
$db_connected = $db_connected ?? false;
$db_info = $db_info ?? [];
$db_name = $db_name ?? ($db_info['name'] ?? 'N/A');
$debug_logs = $debug_logs ?? [];
?>
<footer class="fixed-bottom bg-dark border-top border-secondary py-2" style="z-index: 1050; background-color: #0b0b0b !important;">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            
            <div class="flex-shrink-0">
                <div class="dropup">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-database me-2 <?= $db_connected ? 'text-success' : 'text-danger' ?>"></i>
                        <span class="small text-secondary">DB: <?= htmlspecialchars($db_name) ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-dark p-3 shadow-lg" style="min-width: 300px; border: 1px solid #444;">
                        <h6 class="dropdown-header ps-0 text-white border-bottom border-secondary mb-2 pb-2">Database Engine</h6>
                        
                        <div class="mb-2">
                            <span class="badge bg-black text-white border border-secondary">Driver</span>
                            <span class="badge bg-primary"><?= $db_info['driver'] ?? 'N/A' ?></span>
                        </div>

                        <div class="small text-secondary">
                            <p class="mb-1"><strong class="text-white">Host:</strong> <?= htmlspecialchars($db_info['host'] ?? 'N/A') ?></p>
                            <p class="mb-1"><strong class="text-white">User:</strong> <?= htmlspecialchars($db_info['user'] ?? 'N/A') ?></p>
                            <p class="mb-1"><strong class="text-white">Server:</strong> <?= htmlspecialchars($db_info['version'] ?? 'N/A') ?></p>
                            
                            <hr class="my-2 border-secondary">
                            
                            <p class="fst-italic mb-0">
                                <i class="fas fa-network-wired me-1"></i>
                                <?= htmlspecialchars($db_info['protocol'] ?? 'No connection') ?>
                            </p>
                        </div>

                        <hr class="dropdown-divider border-secondary">

                        <div class="d-flex gap-2">
                            <span class="badge bg-black text-white border border-secondary">PHP <?= phpversion() ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-grow-1 mx-4">
                <div class="bg-black border border-secondary rounded p-1" style="height: 40px; overflow-x: auto; white-space: nowrap;">
                    <div id="dev-console" class="d-flex align-items-center h-100 px-2 gap-2" style="font-family: 'SFMono-Regular', Consolas, monospace; font-size: 0.75rem;">
                        
                        <?php if (empty($debug_logs)): ?>
                            <span class="text-muted small">> System Idle</span>
                        <?php else: ?>
                            <?php foreach ($debug_logs as $log): ?>
                                <span class="badge rounded-pill <?= str_replace('is-', 'text-bg-', $log['class']) ?> p-2 shadow-sm" 
                                      onclick="openDevModal('<?= $log['id'] ?>')" 
                                      style="cursor: pointer;">
                                    <strong class="me-1"><?= $log['icon'] ?></strong> <?= htmlspecialchars($log['message']) ?>
                                </span>

                                <template id="data-<?= $log['id'] ?>">
                                    <div class="p-1">
                                        <p class="mb-1"><strong>Time:</strong> <span class="text-muted"><?= $log['timestamp'] ?></span></p>
                                        <p class="mb-2"><strong>Message:</strong> <?= htmlspecialchars($log['message']) ?></p>
                                        <pre class="bg-dark text-success p-3 rounded border border-secondary"><code><?= htmlspecialchars($log['details']) ?></code></pre>
                                    </div>
                                </template>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 text-end" style="min-width: 150px;">
    <?php if (session_status() === PHP_SESSION_ACTIVE): ?>
        <?php
            $sessionPayload = empty($_SESSION)
                ? 'Session is active but contains no keys.'
                : json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($sessionPayload === false) {
                $sessionPayload = 'Unable to encode session data.';
            }

            $sessionPopoverContent = "<div class='small text-start'>"
                . "<strong>ID:</strong> <code class='text-info'>" . htmlspecialchars(session_id(), ENT_QUOTES, 'UTF-8') . "</code><br>"
                . "<strong>Status:</strong> <span class='badge bg-success'>Active</span><br>"
                . "<strong>Items:</strong> " . count($_SESSION) . " vars"
                . "<hr class='my-2'>"
                . "<strong class='d-block mb-1'>Session Data</strong>"
                . "<pre class='mb-0 p-2 bg-dark text-info border border-secondary rounded' style='max-height:220px; overflow:auto; white-space:pre-wrap;'>"
                . htmlspecialchars($sessionPayload, ENT_QUOTES, 'UTF-8')
                . "</pre>"
                . "</div>";
        ?>
        <form action="<?= url('reset-session') ?>" method="post" class="d-inline-block me-2">
            <button type="submit" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-rotate-left me-1"></i>Reset Session
            </button>
        </form>
        <button type="button" 
                class="btn btn-sm btn-outline-secondary border-0 d-flex align-items-center gap-2 ms-auto"
                data-bs-toggle="popover" 
                data-bs-title="Session Info" 
                data-bs-html="true"
                data-bs-content="<?= htmlspecialchars($sessionPopoverContent, ENT_QUOTES, 'UTF-8') ?>">
            <i class="fas fa-user-tag text-secondary"></i>
            <span class="text-secondary small" style="font-family: monospace;">
                ID: <?= substr(session_id(), 0, 8) ?>...
            </span>
        </button>
    <?php else: ?>
        <span class="text-muted small italic">Session inactive</span>
    <?php endif; ?>
</div>


        </div>
    </div>
</footer>

<div class="modal fade" id="dev-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary py-2">
                <h5 class="modal-title fs-6">Log Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-content-area">
                </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.openDevModal = function(id) {
        const template = document.getElementById('data-' + id);
        if (!template) return;
        document.getElementById('modal-content-area').innerHTML = template.innerHTML;

        const myModal = new bootstrap.Modal(document.getElementById('dev-modal'));
        myModal.show();
    };

    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>