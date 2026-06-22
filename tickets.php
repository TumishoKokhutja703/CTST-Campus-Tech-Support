<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/DBConn.php';

$activePage = 'tickets';
$conn       = getDbConnection();
require_once __DIR__ . '/includes/appData.php';

// Filters
$filterStatus   = $_GET['status']   ?? 'all';
$filterPriority = $_GET['priority'] ?? 'all';
$filterMine     = ($_GET['filter']  ?? '') === 'mine';

$where  = ['1=1'];
$params = [];
$types  = '';

if ($filterMine) {
    $where[] = 't.assigned_to = ?';
    $params[] = $_SESSION['staff_id'];
    $types   .= 'i';
}
if ($filterStatus !== 'all') {
    $where[] = 't.status = ?';
    $params[] = $filterStatus;
    $types   .= 's';
}
if ($filterPriority !== 'all') {
    $where[] = 't.priority = ?';
    $params[] = $filterPriority;
    $types   .= 's';
}

$sql  = "SELECT t.ticket_id, t.ticket_ref, t.category, t.priority, t.status,
                t.description, t.location, t.date_opened,
                COALESCE(s.full_name,'Unassigned') AS assigned_name
         FROM tblTickets t
         LEFT JOIN tblStaff s ON s.staff_id = t.assigned_to
         WHERE " . implode(' AND ', $where) . "
         ORDER BY FIELD(t.priority,'Critical','High','Medium','Low'), t.date_opened DESC";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tickets = $stmt->get_result();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | Tickets</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app-shell">
<?php require __DIR__ . '/nav.php'; ?>

    <div class="page-header">
        <div>
            <div class="page-title"><?= $filterMine ? 'Assigned to Me' : 'All Tickets' ?></div>
            <div class="page-sub"><?= $totalTickets ?> ticket<?= $totalTickets !== 1 ? 's' : '' ?> in the system</div>
        </div>
    </div>

    <!-- Filter row -->
    <form method="get" class="filter-row" style="margin-bottom:16px;">
        <?php if ($filterMine): ?><input type="hidden" name="filter" value="mine"><?php endif; ?>
        <select class="select-input" name="status" onchange="this.form.submit()">
            <option value="all"        <?= $filterStatus==='all'         ? 'selected':'' ?>>All Statuses</option>
            <option value="Open"       <?= $filterStatus==='Open'        ? 'selected':'' ?>>Open</option>
            <option value="In Progress"<?= $filterStatus==='In Progress' ? 'selected':'' ?>>In Progress</option>
            <option value="Resolved"   <?= $filterStatus==='Resolved'    ? 'selected':'' ?>>Resolved</option>
            <option value="Closed"     <?= $filterStatus==='Closed'      ? 'selected':'' ?>>Closed</option>
        </select>
        <select class="select-input" name="priority" onchange="this.form.submit()">
            <option value="all"    <?= $filterPriority==='all'      ? 'selected':'' ?>>All Priorities</option>
            <option value="Critical"<?= $filterPriority==='Critical' ? 'selected':'' ?>>Critical</option>
            <option value="High"   <?= $filterPriority==='High'     ? 'selected':'' ?>>High</option>
            <option value="Medium" <?= $filterPriority==='Medium'   ? 'selected':'' ?>>Medium</option>
            <option value="Low"    <?= $filterPriority==='Low'      ? 'selected':'' ?>>Low</option>
        </select>
        <?php if ($filterStatus!=='all' || $filterPriority!=='all'): ?>
            <a class="btn-sm" href="tickets.php<?= $filterMine?'?filter=mine':'' ?>">Clear filters</a>
        <?php endif; ?>
    </form>

    <!-- Tickets table -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Opened</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($tickets->num_rows === 0): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                            <div class="empty-state-title">No tickets found</div>
                            <div class="empty-state-sub">No tickets match the current filters.<br>Tickets submitted via the mobile app will appear here.</div>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($t = $tickets->fetch_assoc()): ?>
                <tr onclick="location.href='ticket_detail.php?id=<?= $t['ticket_id'] ?>'" style="cursor:pointer">
                    <td><a class="ticket-id" href="ticket_detail.php?id=<?= $t['ticket_id'] ?>">#<?= htmlspecialchars($t['ticket_ref']) ?></a></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars($t['category']) ?></td>
                    <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($t['location'] ?? '—') ?></td>
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
