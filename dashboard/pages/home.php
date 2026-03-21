<?php
$user = $_SESSION['user'] ?? [];
$firstName = trim((string) ($user['first_name'] ?? 'User'));
?>

<main data-bs-theme="dark" class="bg-dark text-light min-vh-100 pb-5">

    <section class="pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <p class="text-secondary text-uppercase small mb-2">Dashboard Overview</p>
                    <h1 class="display-6 fw-bold mb-2">Welcome back, <?= htmlspecialchars($firstName) ?>.</h1>
                    <p class="text-secondary mb-0">Here is your latest project health, activity, and pending work.</p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex justify-content-lg-end gap-2">
                        <a href="/dashboard/contact" class="btn btn-outline-light">Create Ticket</a>
                        <a href="/dashboard/about" class="btn btn-primary">View Docs</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-4">
        <div class="container">
            <div class="row g-3">
                <div class="col-6 col-xl-3">
                    <div class="card bg-body-tertiary border-secondary border-opacity-25 h-100">
                        <div class="card-body">
                            <p class="text-secondary small mb-2">Total Requests</p>
                            <h3 class="fw-bold mb-1">14,892</h3>
                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i>+8.3% this week</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card bg-body-tertiary border-secondary border-opacity-25 h-100">
                        <div class="card-body">
                            <p class="text-secondary small mb-2">Active Sessions</p>
                            <h3 class="fw-bold mb-1">389</h3>
                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i>+23 live users</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card bg-body-tertiary border-secondary border-opacity-25 h-100">
                        <div class="card-body">
                            <p class="text-secondary small mb-2">Error Rate</p>
                            <h3 class="fw-bold mb-1">0.18%</h3>
                            <small class="text-success"><i class="fas fa-arrow-down me-1"></i>-0.04% vs yesterday</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card bg-body-tertiary border-secondary border-opacity-25 h-100">
                        <div class="card-body">
                            <p class="text-secondary small mb-2">Avg. Response</p>
                            <h3 class="fw-bold mb-1">138ms</h3>
                            <small class="text-warning"><i class="fas fa-gauge me-1"></i>Performance stable</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-4">
        <div class="container">
            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card bg-body-tertiary border-secondary border-opacity-25 h-100">
                        <div class="card-header d-flex justify-content-between align-items-center border-secondary border-opacity-25">
                            <h5 class="mb-0">Recent Activity</h5>
                            <span class="badge text-bg-primary">Live</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Event</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th class="text-end pe-3">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3">Deployment completed</td>
                                            <td>API Gateway</td>
                                            <td><span class="badge text-bg-success">Healthy</span></td>
                                            <td class="text-end pe-3 text-secondary">2 min ago</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3">Database backup</td>
                                            <td>PostgreSQL</td>
                                            <td><span class="badge text-bg-success">Success</span></td>
                                            <td class="text-end pe-3 text-secondary">18 min ago</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3">Rate limit warning</td>
                                            <td>Auth Service</td>
                                            <td><span class="badge text-bg-warning">Investigate</span></td>
                                            <td class="text-end pe-3 text-secondary">44 min ago</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3">Nightly test suite</td>
                                            <td>CI Pipeline</td>
                                            <td><span class="badge text-bg-info">Running</span></td>
                                            <td class="text-end pe-3 text-secondary">1 hr ago</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card bg-body-tertiary border-secondary border-opacity-25 mb-3">
                        <div class="card-header border-secondary border-opacity-25">
                            <h5 class="mb-0">System Health</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1"><small>CPU Usage</small><small class="text-secondary">42%</small></div>
                                <div class="progress" role="progressbar" aria-label="CPU usage" aria-valuenow="42" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-info" style="width: 42%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1"><small>Memory</small><small class="text-secondary">67%</small></div>
                                <div class="progress" role="progressbar" aria-label="Memory usage" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-primary" style="width: 67%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1"><small>Storage</small><small class="text-secondary">78%</small></div>
                                <div class="progress" role="progressbar" aria-label="Storage usage" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-warning" style="width: 78%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-body-tertiary border-secondary border-opacity-25">
                        <div class="card-header border-secondary border-opacity-25">
                            <h5 class="mb-0">Your Tasks</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" checked>
                                <label class="form-check-label">Review login analytics</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox">
                                <label class="form-check-label">Prepare weekly report</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                                <label class="form-check-label">Audit inactive accounts</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>