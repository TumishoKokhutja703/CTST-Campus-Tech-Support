<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/appData.php';

$activePage = 'dashboard';
$today = date('l, d F Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">

    <?php require __DIR__ . '/nav.php'; ?>

        <!-- Page header -->
        <div class="page-header">
            <div>
                <div class="page-title">Overview</div>
                <div class="page-date"><?= htmlspecialchars($today) ?></div>
            </div>
        </div>

        <!-- Metric cards -->
        <div class="metrics-row">
            <div class="metric-card open">
                <div class="metric-label">Open</div>
                <div class="metric-value"><?= $countOpen ?></div>
                <div class="metric-sub">Awaiting assignment</div>
            </div>
            <div class="metric-card progress">
                <div class="metric-label">In Progress</div>
                <div class="metric-value"><?= $countInProgress ?></div>
                <div class="metric-sub">Actively being worked</div>
            </div>
            <div class="metric-card resolved">
                <div class="metric-label">Resolved</div>
                <div class="metric-value"><?= $countResolved ?></div>
                <div class="metric-sub">Closed this week</div>
            </div>
            <div class="metric-card critical">
                <div class="metric-label">Critical</div>
                <div class="metric-value"><?= $countCritical ?></div>
                <div class="metric-sub">High-priority open</div>
            </div>
        </div>

        <!-- Recent tickets -->
        <div class="section-header">
            <div class="section-label">Recent Tickets</div>
            <a class="view-all-link" href="tickets.php">View all tickets &rarr;</a>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Issue</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($tickets, 0, 4) as $t): ?>
                    <tr>
                        <td>
                            <a class="ticket-id" href="ticket_detail.php?id=<?= urlencode($t['id']) ?>">
                                #<?= htmlspecialchars($t['id']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($t['description']) ?></td>
                        <td>
                            <div class="priority-cell">
                                <div class="priority-dot <?= priorityDotClass($t['priority']) ?>"></div>
                                <?= htmlspecialchars($t['priority']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= statusBadgeClass($t['status']) ?>">
                                <?= htmlspecialchars($t['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</div><!-- .app-body -->
</div><!-- .app-shell -->
</body>
</html>
