<?php
$db_connected = $db_connected ?? false;
$db_info = $db_info ?? [];
$db_name = $db_name ?? ($db_info['name'] ?? 'N/A');
$debug_logs = isset($debug_logs) && is_array($debug_logs) ? $debug_logs : [];

$fileLogs = function_exists('app_read_recent_log_entries') ? app_read_recent_log_entries(null, 80) : [];
$rawLogs = array_merge($debug_logs, $fileLogs);

$normalize = static function ($log): ?array {
    if (!is_array($log)) {
        return null;
    }

    $level = function_exists('app_normalize_log_level')
        ? app_normalize_log_level((string) ($log['level'] ?? $log['type'] ?? 'info'))
        : strtolower((string) ($log['level'] ?? $log['type'] ?? 'info'));

    if (!in_array($level, ['error', 'warning', 'info', 'success'], true)) {
        $level = 'info';
    }

    $message = trim((string) ($log['message'] ?? ''));
    if ($message === '') {
        return null;
    }

    $details = $log['details'] ?? '';
    if (is_array($details) || is_object($details)) {
        $details = json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        if ($details === false) {
            $details = 'Unable to render details.';
        }
    }

    $context = is_array($log['context'] ?? null) ? $log['context'] : [];
    if (($details === '' || $details === null) && !empty($context) && function_exists('app_context_summary')) {
        $details = app_context_summary($context);
    }
    if ($details === '' || $details === null) {
        $details = 'No additional info.';
    }

    $styles = [
        'error' => ['class' => 'text-bg-danger', 'icon' => 'x'],
        'warning' => ['class' => 'text-bg-warning', 'icon' => '!'],
        'info' => ['class' => 'text-bg-info', 'icon' => 'i'],
        'success' => ['class' => 'text-bg-success', 'icon' => '+'],
    ];
    $style = $styles[$level];

    $source = strtolower((string) ($log['source'] ?? 'panel'));
    $sourceLabelMap = [
        'app_log' => 'Runtime',
        'file' => 'History',
        'devpanel' => 'Panel',
        'panel' => 'Panel',
    ];

    $timestamp = (string) ($log['timestamp'] ?? date('H:i:s'));
    $sortKey = is_numeric($timestamp) ? (int) $timestamp : (int) (strtotime($timestamp) ?: time());
    if ($sortKey <= 0) {
        $sortKey = time();
    }

    $requestId = is_array($context) ? (string) ($context['request_id'] ?? '') : '';
    $signature = $requestId !== ''
        ? $requestId . '|' . $timestamp . '|' . $level . '|' . $message
        : $timestamp . '|' . $level . '|' . $message . '|' . $source;

    return [
        'id' => (string) ($log['id'] ?? uniqid('log_', true)),
        'timestamp' => $timestamp,
        'sort_key' => $sortKey,
        'message' => $message,
        'details' => (string) $details,
        'class' => $style['class'],
        'icon' => $style['icon'],
        'level' => $level,
        'source' => $source,
        'source_label' => $sourceLabelMap[$source] ?? ucfirst($source),
        'context' => $context,
        'signature' => $signature,
    ];
};

$panelLogsMap = [];
foreach ($rawLogs as $log) {
    $normalized = $normalize($log);
    if (!$normalized) {
        continue;
    }
    $panelLogsMap[$normalized['signature']] = $normalized;
}

$panelLogs = array_values($panelLogsMap);
usort($panelLogs, static function (array $a, array $b): int {
    $cmp = $b['sort_key'] <=> $a['sort_key'];
    if ($cmp !== 0) {
        return $cmp;
    }
    return strcmp($b['timestamp'], $a['timestamp']);
});

$levelCounts = ['error' => 0, 'warning' => 0, 'info' => 0, 'success' => 0];
$sourceCounts = ['Runtime' => 0, 'History' => 0, 'Panel' => 0];
foreach ($panelLogs as $log) {
    $levelCounts[$log['level']] = ($levelCounts[$log['level']] ?? 0) + 1;
    $sourceCounts[$log['source_label']] = ($sourceCounts[$log['source_label']] ?? 0) + 1;
}

$sessionActive = session_status() === PHP_SESSION_ACTIVE;
$totalLogs = count($panelLogs);
?>
<footer id="dev-panel" class="fixed-bottom bg-dark border-top border-secondary py-2" style="z-index: 1050; background-color: #0b0b0b !important;">
    <div id="dev-panel-expanded" class="container-fluid px-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="dropup">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-database <?= $db_connected ? 'text-success' : 'text-danger' ?>"></i>
                        <span class="small text-secondary">DB: <?= htmlspecialchars((string) $db_name, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-dark p-3 shadow-lg" style="min-width: 320px; border: 1px solid #444;">
                        <h6 class="dropdown-header ps-0 text-white border-bottom border-secondary mb-2 pb-2">Database Engine</h6>
                        <div class="mb-2 d-flex flex-wrap gap-2">
                            <span class="badge bg-black text-white border border-secondary">Driver <?= htmlspecialchars((string) ($db_info['driver'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="badge <?= $db_connected ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $db_connected ? 'Connected' : 'Disconnected' ?></span>
                        </div>
                        <div class="small text-secondary">
                            <p class="mb-1"><strong class="text-white">Host:</strong> <?= htmlspecialchars((string) ($db_info['host'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mb-1"><strong class="text-white">User:</strong> <?= htmlspecialchars((string) ($db_info['user'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mb-1"><strong class="text-white">Server:</strong> <?= htmlspecialchars((string) ($db_info['version'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></p>
                            <hr class="my-2 border-secondary">
                            <p class="fst-italic mb-0">
                                <i class="fas fa-network-wired me-1"></i>
                                <?= htmlspecialchars((string) ($db_info['protocol'] ?? 'No connection'), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                        <hr class="dropdown-divider border-secondary">
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-black text-white border border-secondary">PHP <?= htmlspecialchars(phpversion(), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="badge text-bg-secondary">Logs <?= count($panelLogs) ?></span>
                        </div>
                    </div>
                </div>

                <span class="badge text-bg-danger">E <?= $levelCounts['error'] ?></span>
                <span class="badge text-bg-warning">W <?= $levelCounts['warning'] ?></span>
                <span class="badge text-bg-info">I <?= $levelCounts['info'] ?></span>
                <span class="badge text-bg-success">S <?= $levelCounts['success'] ?></span>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 320px;">
                    <span class="input-group-text bg-black text-secondary border-secondary">Search</span>
                    <input id="dev-log-filter" type="search" class="form-control form-control-sm bg-dark text-light border-secondary" placeholder="message, details, source">
                </div>

                <button type="button" id="dev-panel-minimize" class="btn btn-sm btn-outline-light" title="Minimize dev panel" aria-expanded="true">
                    Minimize
                </button>

                <div class="btn-group btn-group-sm" role="group" aria-label="Log filters">
                    <button type="button" class="btn btn-outline-light active" data-log-filter="all">All</button>
                    <button type="button" class="btn btn-outline-danger" data-log-filter="error">Error</button>
                    <button type="button" class="btn btn-outline-warning" data-log-filter="warning">Warn</button>
                    <button type="button" class="btn btn-outline-info" data-log-filter="info">Info</button>
                    <button type="button" class="btn btn-outline-success" data-log-filter="success">Success</button>
                </div>

                <div class="btn-group btn-group-sm" role="group" aria-label="Source filters">
                    <button type="button" class="btn btn-outline-secondary active" data-source-filter="all">All sources</button>
                    <button type="button" class="btn btn-outline-secondary" data-source-filter="app_log">Runtime</button>
                    <button type="button" class="btn btn-outline-secondary" data-source-filter="file">History</button>
                </div>
            </div>
        </div>

        <div class="bg-black border border-secondary rounded p-2" style="max-height: 170px; overflow-y: auto;">
            <div id="dev-console" class="d-flex flex-column gap-2" style="font-family: 'SFMono-Regular', Consolas, monospace; font-size: 0.78rem;">
                <?php if (empty($panelLogs)): ?>
                    <div class="text-muted small py-2">System idle — no logs available.</div>
                <?php else: ?>
                    <?php foreach ($panelLogs as $log): ?>
                        <?php $modalId = 'data-' . $log['id']; ?>
                        <button
                            type="button"
                            class="dev-log-item btn btn-sm btn-outline-light text-start w-100 border-secondary d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2"
                            data-log-id="<?= htmlspecialchars($log['id'], ENT_QUOTES, 'UTF-8') ?>"
                            data-log-level="<?= htmlspecialchars($log['level'], ENT_QUOTES, 'UTF-8') ?>"
                            data-log-source="<?= htmlspecialchars($log['source'], ENT_QUOTES, 'UTF-8') ?>"
                            data-log-search="<?= htmlspecialchars(strtolower($log['message'] . ' ' . $log['details'] . ' ' . $log['source_label']), ENT_QUOTES, 'UTF-8') ?>"
                            onclick="openDevModal('<?= htmlspecialchars($log['id'], ENT_QUOTES, 'UTF-8') ?>')"
                        >
                            <span class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge <?= htmlspecialchars($log['class'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($log['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="text-light"><?= htmlspecialchars($log['message'], ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                            <span class="d-flex align-items-center gap-2 flex-wrap text-secondary small">
                                <span><?= htmlspecialchars($log['timestamp'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge text-bg-secondary"><?= htmlspecialchars($log['source_label'], ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                        </button>

                        <template id="<?= htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="p-1 text-start">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge <?= htmlspecialchars($log['class'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(strtoupper($log['level']), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="badge text-bg-secondary"><?= htmlspecialchars($log['source_label'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="badge bg-black text-white border border-secondary"><?= htmlspecialchars($log['timestamp'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>

                                <p class="mb-2"><strong>Message:</strong><br><?= htmlspecialchars($log['message'], ENT_QUOTES, 'UTF-8') ?></p>

                                <div class="mb-2"><strong>Details:</strong></div>
                                <pre class="bg-dark text-success p-3 rounded border border-secondary mb-3" style="white-space: pre-wrap; overflow-wrap: anywhere;"><code><?= htmlspecialchars($log['details'], ENT_QUOTES, 'UTF-8') ?></code></pre>

                                <?php if (!empty($log['context'])): ?>
                                    <div class="mb-2"><strong>Context:</strong></div>
                                    <pre class="bg-dark text-info p-3 rounded border border-secondary" style="white-space: pre-wrap; overflow-wrap: anywhere;"><code><?= htmlspecialchars(json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) ?: 'Unable to render context.', ENT_QUOTES, 'UTF-8') ?></code></pre>
                                <?php endif; ?>
                            </div>
                        </template>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge text-bg-secondary">Runtime <?= $sourceCounts['Runtime'] ?></span>
                <span class="badge text-bg-secondary">History <?= $sourceCounts['History'] ?></span>
                <span class="badge text-bg-secondary">Panel <?= $sourceCounts['Panel'] ?></span>
            </div>

            <div class="text-end" style="min-width: 150px;">
                <?php if ($sessionActive): ?>
                    <?php
                        $sessionPayload = empty($_SESSION)
                            ? 'Session is active but contains no keys.'
                            : json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

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
                    <form action="<?= htmlspecialchars(url('reset-session'), ENT_QUOTES, 'UTF-8') ?>" method="post" class="d-inline-block me-2">
                        <button type="submit" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-rotate-left me-1"></i>Reset Session
                        </button>
                    </form>
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary border-0 d-inline-flex align-items-center gap-2 ms-auto"
                            data-bs-toggle="popover"
                            data-bs-title="Session Info"
                            data-bs-html="true"
                            data-bs-content="<?= htmlspecialchars($sessionPopoverContent, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-user-tag text-secondary"></i>
                        <span class="text-secondary small" style="font-family: monospace;">
                            ID: <?= htmlspecialchars(substr(session_id(), 0, 8), ENT_QUOTES, 'UTF-8') ?>...
                        </span>
                    </button>
                <?php else: ?>
                    <span class="text-muted small italic">Session inactive</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="dev-panel-collapsed" class="container-fluid px-3 d-none">
        <div class="d-flex align-items-center justify-content-between gap-2 py-1">
            <div class="d-flex align-items-center gap-2 small text-secondary">
                <span class="badge text-bg-secondary">Dev Panel</span>
                <span><?= $totalLogs ?> logs</span>
                <span class="badge text-bg-danger">E <?= $levelCounts['error'] ?></span>
                <span class="badge text-bg-warning">W <?= $levelCounts['warning'] ?></span>
            </div>
            <button type="button" id="dev-panel-restore" class="btn btn-sm btn-outline-light" aria-expanded="false">
                Open Panel
            </button>
        </div>
    </div>
</footer>

<div class="modal fade" id="dev-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary py-2">
                <h5 class="modal-title fs-6">Log Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-content-area"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const panelRoot = document.getElementById('dev-panel');
    const panelExpanded = document.getElementById('dev-panel-expanded');
    const panelCollapsed = document.getElementById('dev-panel-collapsed');
    const minimizeButton = document.getElementById('dev-panel-minimize');
    const restoreButton = document.getElementById('dev-panel-restore');
    const modalEl = document.getElementById('dev-modal');
    const modalBody = document.getElementById('modal-content-area');
    const filterInput = document.getElementById('dev-log-filter');
    const logItems = Array.from(panelRoot ? panelRoot.querySelectorAll('.dev-log-item') : []);
    const levelButtons = Array.from(panelRoot ? panelRoot.querySelectorAll('[data-log-filter]') : []);
    const sourceButtons = Array.from(panelRoot ? panelRoot.querySelectorAll('[data-source-filter]') : []);
    const panelStateKey = 'revtrack.devpanel.state';

    function applyPanelState(state) {
        const minimized = state === 'minimized';
        if (panelExpanded) {
            panelExpanded.classList.toggle('d-none', minimized);
        }
        if (panelCollapsed) {
            panelCollapsed.classList.toggle('d-none', !minimized);
        }
        if (minimizeButton) {
            minimizeButton.setAttribute('aria-expanded', minimized ? 'false' : 'true');
        }
        if (restoreButton) {
            restoreButton.setAttribute('aria-expanded', minimized ? 'false' : 'true');
        }
    }

    function savePanelState(state) {
        try {
            localStorage.setItem(panelStateKey, state);
        } catch (e) {
            // Ignore storage errors and keep runtime state only.
        }
    }

    function readPanelState() {
        try {
            const state = localStorage.getItem(panelStateKey);
            return state === 'minimized' ? 'minimized' : 'expanded';
        } catch (e) {
            return 'expanded';
        }
    }

    function setPanelState(state) {
        const normalized = state === 'minimized' ? 'minimized' : 'expanded';
        applyPanelState(normalized);
        savePanelState(normalized);
    }

    if (minimizeButton) {
        minimizeButton.addEventListener('click', function() {
            setPanelState('minimized');
        });
    }

    if (restoreButton) {
        restoreButton.addEventListener('click', function() {
            setPanelState('expanded');
        });
    }

    function openDevModal(id) {
        setPanelState('expanded');
        const template = document.getElementById('data-' + id);
        if (!template || !modalBody) return;
        modalBody.innerHTML = template.innerHTML;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    window.openDevModal = openDevModal;

    function setActiveButton(buttons, currentButton) {
        buttons.forEach(function(button) {
            button.classList.toggle('active', button === currentButton);
        });
    }

    function applyFilters() {
        const search = (filterInput ? filterInput.value : '').trim().toLowerCase();
        const activeLevelButton = levelButtons.find(button => button.classList.contains('active'));
        const activeSourceButton = sourceButtons.find(button => button.classList.contains('active'));
        const levelFilter = activeLevelButton ? activeLevelButton.getAttribute('data-log-filter') : 'all';
        const sourceFilter = activeSourceButton ? activeSourceButton.getAttribute('data-source-filter') : 'all';

        logItems.forEach(function(item) {
            const text = (item.getAttribute('data-log-search') || '').toLowerCase();
            const level = item.getAttribute('data-log-level') || 'info';
            const source = item.getAttribute('data-log-source') || 'panel';

            const matchesSearch = search === '' || text.includes(search);
            const matchesLevel = levelFilter === 'all' || level === levelFilter;
            const matchesSource = sourceFilter === 'all' || source === sourceFilter;

            item.classList.toggle('d-none', !(matchesSearch && matchesLevel && matchesSource));
        });
    }

    if (filterInput) {
        filterInput.addEventListener('input', applyFilters);
    }

    levelButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            setActiveButton(levelButtons, button);
            applyFilters();
        });
    });

    sourceButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            setActiveButton(sourceButtons, button);
            applyFilters();
        });
    });

    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(popoverTriggerEl) {
        new bootstrap.Popover(popoverTriggerEl);
    });

    applyPanelState(readPanelState());
    applyFilters();
});
</script>