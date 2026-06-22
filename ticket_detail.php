<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/DBConn.php';

$activePage = 'tickets';
$conn       = getDbConnection();
require_once __DIR__ . '/includes/appData.php';

$ticketId = (int)($_GET['id'] ?? 0);
if ($ticketId === 0) { header('Location: tickets.php'); exit; }

// Fetch ticket
$stmt = $conn->prepare(
    "SELECT t.*, COALESCE(s.full_name,'Unassigned') AS assigned_name,
            u.full_name AS submitted_by
     FROM tblTickets t
     LEFT JOIN tblStaff s ON s.staff_id = t.assigned_to
     LEFT JOIN tblUsers  u ON u.user_id  = t.user_id
     WHERE t.ticket_id = ? LIMIT 1"
);
$stmt->bind_param('i', $ticketId);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$ticket) { header('Location: tickets.php'); exit; }

// Staff list for assignment
$staffList = $conn->query(
    "SELECT staff_id, full_name FROM tblStaff WHERE role != 'inactive' ORDER BY full_name"
)->fetch_all(MYSQLI_ASSOC);

$message = '';
$messageClass = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_status') {
        $newStatus = $_POST['status'] ?? '';
        $allowed   = ['Open','In Progress','Resolved','Closed'];
        if (in_array($newStatus, $allowed, true)) {
            $resolution  = trim($_POST['resolution'] ?? '');
            $dateResolved = ($newStatus === 'Resolved') ? date('Y-m-d H:i:s') : null;

            if (in_array($newStatus, ['Resolved','Closed']) && $resolution === '') {
                $message = 'Please enter resolution notes before marking as resolved.';
                $messageClass = 'bad';
            } else {
                $stmt = $conn->prepare(
                    "UPDATE tblTickets SET status=?, resolution=?, date_resolved=? WHERE ticket_id=?"
                );
                $stmt->bind_param('sssi', $newStatus, $resolution, $dateResolved, $ticketId);
                $stmt->execute();
                $stmt->close();
                $message = 'Ticket status updated to ' . $newStatus . '.';
                $messageClass = 'ok';
                $ticket['status']     = $newStatus;
                $ticket['resolution'] = $resolution;
            }
        }
    }

    if ($_POST['action'] === 'assign') {
        $staffId = (int)($_POST['staff_id'] ?? 0);
        $stmt    = $conn->prepare("UPDATE tblTickets SET assigned_to=? WHERE ticket_id=?");
        $stmt->bind_param('ii', $staffId, $ticketId);
        $stmt->execute();
        $stmt->close();
        header("Location: ticket_detail.php?id=$ticketId&msg=assigned");
        exit;
    }

    if ($_POST['action'] === 'add_comment') {
        $comment = trim($_POST['comment'] ?? '');
        if ($comment !== '') {
            $sid  = $_SESSION['staff_id'];
            $stmt = $conn->prepare(
                "INSERT INTO tblComments (ticket_id, staff_id, comment) VALUES (?,?,?)"
            );
            $stmt->bind_param('iis', $ticketId, $sid, $comment);
            $stmt->execute();
            $stmt->close();
            header("Location: ticket_detail.php?id=$ticketId#activity");
            exit;
        }
    }
}

// Fetch comments
$comments = $conn->query(
    "SELECT c.comment, c.created_at, s.full_name
     FROM tblComments c
     JOIN tblStaff s ON s.staff_id = c.staff_id
     WHERE c.ticket_id = $ticketId
     ORDER BY c.created_at ASC"
)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | #<?= htmlspecialchars($ticket['ticket_ref']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app-shell">
<?php require __DIR__ . '/nav.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="tickets.php">Tickets</a>
        <span class="breadcrumb-sep">/</span>
        <span>#<?= htmlspecialchars($ticket['ticket_ref']) ?></span>
    </div>

    <!-- Title row -->
    <div class="ticket-header-row">
        <div class="page-title">#<?= htmlspecialchars($ticket['ticket_ref']) ?></div>
        <span class="badge <?= statusBadgeClass($ticket['status']) ?>"><?= htmlspecialchars($ticket['status']) ?></span>
    </div>
    <div class="ticket-header-title"><?= htmlspecialchars(mb_strimwidth($ticket['description'], 0, 90, '…')) ?></div>

    <?php if ($message !== ''): ?>
        <div class="msg <?= $messageClass ?>" style="margin-bottom:18px"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'assigned'): ?>
        <div class="msg ok" style="margin-bottom:18px">Ticket reassigned successfully.</div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

        <!-- Left: details + activity -->
        <div>
            <div class="detail-card">
                <div class="detail-row"><div class="detail-key">Submitted by</div><div class="detail-val"><?= htmlspecialchars($ticket['submitted_by'] ?? 'Unknown') ?></div></div>
                <div class="detail-row"><div class="detail-key">Category</div><div class="detail-val"><?= htmlspecialchars($ticket['category']) ?></div></div>
                <div class="detail-row"><div class="detail-key">Device</div><div class="detail-val"><?= htmlspecialchars($ticket['device_type'] ?? '—') ?></div></div>
                <div class="detail-row"><div class="detail-key">Priority</div><div class="detail-val"><div class="priority-cell"><div class="priority-dot <?= priorityDotClass($ticket['priority']) ?>"></div><?= htmlspecialchars($ticket['priority']) ?></div></div></div>
                <div class="detail-row"><div class="detail-key">Location</div><div class="detail-val"><?= htmlspecialchars($ticket['location'] ?? '—') ?></div></div>
                <div class="detail-row"><div class="detail-key">Assigned to</div><div class="detail-val"><div class="assignee-cell"><div class="assignee-dot"><?= initials($ticket['assigned_name']) ?></div><?= htmlspecialchars($ticket['assigned_name']) ?></div></div></div>
                <div class="detail-row"><div class="detail-key">Date opened</div><div class="detail-val" style="font-family:var(--mono);font-size:13px"><?= date('d M Y, H:i', strtotime($ticket['date_opened'])) ?></div></div>
                <?php if ($ticket['date_resolved']): ?>
                <div class="detail-row"><div class="detail-key">Date resolved</div><div class="detail-val" style="font-family:var(--mono);font-size:13px"><?= date('d M Y, H:i', strtotime($ticket['date_resolved'])) ?></div></div>
                <?php endif; ?>
                <div class="detail-row" style="border-bottom:none">
                    <div class="detail-key">Description</div>
                    <div class="detail-val">
                        <div class="description-block"><?= nl2br(htmlspecialchars($ticket['description'])) ?></div>
                    </div>
                </div>
            </div>

            <!-- Activity -->
            <div class="detail-card" id="activity">
                <div class="activity-header">Activity &amp; Comments</div>

                <?php if (empty($comments)): ?>
                    <div style="color:var(--text-muted);font-size:13px;padding:12px 0">No comments yet.</div>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                    <div class="comment-item">
                        <div class="comment-avatar"><?= initials($c['full_name']) ?></div>
                        <div class="comment-bubble">
                            <div class="comment-meta">
                                <span class="comment-author"><?= htmlspecialchars($c['full_name']) ?></span>
                                <span class="comment-time"><?= date('d M, H:i', strtotime($c['created_at'])) ?></span>
                            </div>
                            <div class="comment-text"><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($ticket['resolution']): ?>
                <div class="comment-item" style="margin-top:8px">
                    <div class="comment-avatar" style="background:var(--resolved-bg);color:var(--resolved)">✓</div>
                    <div class="comment-bubble" style="border-color:var(--resolved);background:var(--resolved-bg)">
                        <div class="comment-meta"><span class="comment-author" style="color:var(--resolved)">Resolution Notes</span></div>
                        <div class="comment-text"><?= nl2br(htmlspecialchars($ticket['resolution'])) ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Add comment -->
                <form method="post" style="margin-top:18px">
                    <input type="hidden" name="action" value="add_comment">
                    <div class="add-comment-label">Add a comment</div>
                    <div class="comment-input-row">
                        <textarea class="comment-input" name="comment" placeholder="Write a comment or update…"></textarea>
                        <button type="submit" class="btn-sm primary">Post</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: actions -->
        <div>
            <!-- Update status -->
            <div class="detail-card">
                <div class="activity-header">Update Status</div>
                <form method="post">
                    <input type="hidden" name="action" value="update_status">
                    <div class="form-group">
                        <label class="form-label">New status</label>
                        <select class="select-input" name="status" style="width:100%">
                            <?php foreach (['Open','In Progress','Resolved','Closed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $ticket['status']===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="resolutionWrap">
                        <label class="form-label">Resolution notes <span style="color:var(--critical)">*</span></label>
                        <textarea class="comment-input" name="resolution" placeholder="Describe how the issue was resolved…" style="height:90px"><?= htmlspecialchars($ticket['resolution'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn-sm primary" style="width:100%">Update Status</button>
                </form>
            </div>

            <!-- Assign staff -->
            <div class="detail-card" style="margin-top:16px">
                <div class="activity-header">Assign Technician</div>
                <form method="post">
                    <input type="hidden" name="action" value="assign">
                    <div class="form-group">
                        <label class="form-label">Assign to</label>
                        <select class="select-input" name="staff_id" style="width:100%">
                            <option value="0">— Unassigned —</option>
                            <?php foreach ($staffList as $s): ?>
                            <option value="<?= $s['staff_id'] ?>" <?= $ticket['assigned_to']==$s['staff_id']?'selected':'' ?>>
                                <?= htmlspecialchars($s['full_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-sm" style="width:100%">Save Assignment</button>
                </form>
            </div>
        </div>
    </div>

</main>
</div>
</div>
<script src="assets/theme.js"></script>
</body>
</html>
