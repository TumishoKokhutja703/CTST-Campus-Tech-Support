<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/DBConn.php';

$activePage = 'dashboard';
$conn       = getDbConnection();

require_once __DIR__ . '/includes/appData.php';

// Recent tickets
$recent = $conn->query(
    "SELECT t.ticket_id, t.ticket_ref, t.category, t.priority, t.status,
            t.description, t.date_opened,
            COALESCE(s.full_name, 'Unassigned') AS assigned_name
     FROM tblTickets t
     LEFT JOIN tblStaff s ON s.staff_id = t.assigned_to
     ORDER BY t.date_opened DESC LIMIT 6"
);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app-shell">
<?php require __DIR__ . '/nav.php'; ?>

    <div class="page-header">
        <div>
            <div class="page-title">Dashboard</div>
            <div class="page-date"><?= date('l, d F Y') ?> &mdash; Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['staff_name'])[0]) ?></div>
        </div>
        <a class="btn-sm primary" href="tickets.php">View all tickets &rarr;</a>
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
            <div class="metric-sub">Being worked on</div>
        </div>
        <div class="metric-card resolved">
            <div class="metric-label">Resolved</div>
            <div class="metric-value"><?= $countResolved ?></div>
            <div class="metric-sub">All time</div>
        </div>
        <div class="metric-card critical">
            <div class="metric-label">Critical Open</div>
            <div class="metric-value"><?= $countCritical ?></div>
            <div class="metric-sub">Needs immediate action</div>
        </div>
    </div>

    <!-- Recent tickets -->
    <div class="section-header">
        <div class="section-label">Recent Tickets</div>
        <a class="view-all-link" href="tickets.php">View all &rarr;</a>
    </div>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Opened</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($recent->num_rows === 0): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                            <div class="empty-state-title">No tickets yet</div>
                            <div class="empty-state-sub">Tickets submitted via the mobile app will appear here.</div>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($t = $recent->fetch_assoc()): ?>
                <tr onclick="location.href='ticket_detail.php?id=<?= $t['ticket_id'] ?>'" style="cursor:pointer">
                    <td><a class="ticket-id" href="ticket_detail.php?id=<?= $t['ticket_id'] ?>">#<?= htmlspecialchars($t['ticket_ref']) ?></a></td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars($t['category']) ?></td>
                    <td><div class="priority-cell"><div class="priority-dot <?= priorityDotClass($t['priority']) ?>"></div><?= htmlspecialchars($t['priority']) ?></div></td>
                    <td><span class="badge <?= statusBadgeClass($t['status']) ?>"><?= htmlspecialchars($t['status']) ?></span></td>
                    <td>
                        <div class="assignee-cell">
                            <div class="assignee-dot"><?= initials($t['assigned_name']) ?></div>
                            <?= htmlspecialchars($t['assigned_name']) ?>
                        </div>
                    </td>
                    <td style="font-family:var(--mono);font-size:12px;color:var(--text-muted)"><?= timeAgo($t['date_opened']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</div>
</div>

<script src="assets/theme.js"></script>
</body>
</html>
