<footer class="footer is-fixed-bottom p-2 has-background-black-bis" style="border-top: 1px solid #444; z-index: 1000;">
    <div class="container is-fluid">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <div class="dropdown is-up is-hoverable">
                        <div class="dropdown-trigger">
                            <button class="button is-small is-dark is-outlined" aria-haspopup="true">
                                <span class="icon is-small has-text-<?= $db_connected ? 'success' : 'danger' ?>">
                                    <i class="fas fa-database"></i>
                                </span>
                                <span class="has-text-grey-light">DB: <?= htmlspecialchars($db_name) ?></span>
                            </button>
                        </div>
                        <div class="dropdown-menu" role="menu" style="min-width: 300px;">
                            <div class="dropdown-content has-background-dark p-4">
                                <h6 class="title is-6 has-text-white mb-2">Database Engine</h6>

                                <div class="tags has-addons mb-2">
                                    <span class="tag is-black">Driver</span>
                                    <span class="tag is-info"><?= $db_info['driver'] ?? 'N/A' ?></span>
                                </div>

                                <div class="is-size-7 has-text-grey-light">
                                    <p><strong class="has-text-white">Host:</strong>
                                        <?= htmlspecialchars($db_info['host'] ?? 'N/A') ?></p>
                                    <p><strong class="has-text-white">User:</strong>
                                        <?= htmlspecialchars($db_info['user'] ?? 'N/A') ?></p>
                                    <p><strong class="has-text-white">Server:</strong>
                                        <?= htmlspecialchars($db_info['version'] ?? 'N/A') ?></p>

                                    <hr class="my-2" style="background-color: #444; height: 1px;">

                                    <p class="is-italic has-text-grey">
                                        <i class="fas fa-network-wired mr-1"></i>
                                        <?= htmlspecialchars($db_info['protocol'] ?? 'No connection') ?>
                                    </p>
                                </div>

                                <hr class="dropdown-divider" style="background-color: #444;">

                                <div class="field is-grouped is-grouped-multiline">
                                    <div class="control">
                                        <div class="tags has-addons">
                                            <span class="tag is-black">PHP</span>
                                            <span class="tag is-primary"><?= phpversion() ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="level-item" style="flex-grow: 2; margin: 0 30px;">
                <div class="has-background-black p-2" style="width: 100%; border-radius: 4px; border: 1px solid #333;">
                    <div id="dev-console" class="is-size-7 is-flex"
                        style="gap: 5px; height: 35px; overflow-x: auto; white-space: nowrap; align-items: center;">

                        <?php if (empty($debug_logs)): ?>
                            <span class="has-text-grey-dark">> System Idle</span>
                        <?php else: ?>
                            <?php foreach ($debug_logs as $log): ?>
                                <span class="tag <?= $log['class'] ?> is-clickable" onclick="openDevModal('<?= $log['id'] ?>')"
                                    style="font-family: monospace;">
                                    <strong><?= $log['icon'] ?></strong> &nbsp; <?= htmlspecialchars($log['message']) ?>
                                </span>

                                <template id="data-<?= $log['id'] ?>">
                                    <div class="content">
                                        <p><strong>Time:</strong> <?= $log['timestamp'] ?></p>
                                        <p><strong>Message:</strong> <?= htmlspecialchars($log['message']) ?></p>
                                        <pre
                                            class="has-background-black has-text-success"><?= htmlspecialchars($log['details']) ?></pre>
                                    </div>
                                </template>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<div id="dev-modal" class="modal">
    <div class="modal-background" onclick="closeDevModal()"></div>
    <div class="modal-card">
        <header class="modal-card-head py-3">
            <p class="modal-card-title is-size-5">Log Details</p>
            <button class="delete" aria-label="close" onclick="closeDevModal()"></button>
        </header>
        <section class="modal-card-body" id="modal-content-area">
        </section>
    </div>
</div>

<script>
    function openDevModal(id) {
        const data = document.getElementById('data-' + id).innerHTML;
        document.getElementById('modal-content-area').innerHTML = data;
        document.getElementById('dev-modal').classList.add('is-active');
    }

    function closeDevModal() {
        document.getElementById('dev-modal').classList.remove('is-active');
    }
</script>