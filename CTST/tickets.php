<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/appData.php';

$activePage = 'tickets';

// Filter by status tab
$statusFilter = $_GET['status'] ?? 'all';

// Filter for "Assigned to Me"
$assignedFilter = isset($_GET['filter']) && $_GET['filter'] === 'mine';

$filtered = $tickets;

if ($assignedFilter) {
    $staffName = $_SESSION['staff_name'] ?? '';
    // Match by last name initial (prototype: just use initials from session)
    $myInitials = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $staffName))));
    $myInitials = substr($myInitials, 0, 2);
    $filtered = array_filter($filtered, fn($t) => $t['assigned_initials'] === $myInitials);
}

if ($statusFilter === 'open') {
    $filtered = array_filter($filtered, fn($t) => $t['status'] === 'Open');
} elseif ($statusFilter === 'progress') {
    $filtered = array_filter($filtered, fn($t) => $t['status'] === 'In Progress');
}

$countAll      = count($tickets);
$countOpen     = count(array_filter($tickets, fn($t) => $t['status'] === 'Open'));
$countProgress = count(array_filter($tickets, fn($t) => $t['status'] === 'In Progress'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | All Tickets</title>
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
                <div class="page-title"><?= $assignedFilter ? 'Assigned to Me' : 'All Tickets' ?></div>
                <div class="page-sub"><?= $countAll ?> tickets total</div>
            </div>
            <div class="filter-row">
                <select class="select-input" onchange="applyStatusFilter(this.value)">
                    <option value="all"      <?= $statusFilter === 'all'      ? 'selected' : '' ?>>All Statuses</option>
                    <option value="open"     <?= $statusFilter === 'open'     ? 'selected' : '' ?>>Open</option>
                    <option value="progress" <?= $statusFilter === 'progress' ? 'selected' : '' ?>>In Progress</option>
                </select>
                <a class="btn-sm primary" href="new_ticket.php">+ New Ticket</a>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tab-bar">
            <a class="tab-item <?= $statusFilter === 'all' ? 'active' : '' ?>" href="?status=all">
                All <span class="tab-count"><?= $countAll ?></span>
            </a>
            <a class="tab-item <?= $statusFilter === 'open' ? 'active' : '' ?>" href="?status=open">
                Open <span class="tab-count"><?= $countOpen ?></span>
            </a>
            <a class="tab-item <?= $statusFilter === 'progress' ? 'active' : '' ?>" href="?status=progress">
                In Progress <span class="tab-count"><?= $countProgress ?></span>
            </a>
        </div>

        <!-- Table -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filtered as $t): ?>
                    <tr>
                        <td>
                            <a class="ticket-id" href="ticket_detail.php?id=<?= urlencode($t['id']) ?>">
                                #<?= htmlspecialchars($t['id']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($t['description']) ?></td>
                        <td style="font-size:12px; color:var(--text-secondary);"><?= htmlspecialchars($t['category']) ?></td>
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
                        <td>
                            <div class="assignee-cell">
                                <div class="assignee-dot"><?= htmlspecialchars($t['assigned_initials']) ?></div>
                                <?= htmlspecialchars($t['assigned_to']) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($filtered)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--text-muted); padding: 32px;">
                            No tickets match this filter.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div><!-- .app-body -->
</div><!-- .app-shell -->

<script>
function applyStatusFilter(val) {
    window.location.href = '?status=' + encodeURIComponent(val);
}
</script>
</body>
</html>
