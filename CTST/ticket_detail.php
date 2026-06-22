<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/appData.php';

$activePage = 'tickets';

// Look up ticket by ID from query string
$ticketId = $_GET['id'] ?? '';
$ticket = null;

foreach ($tickets as $t) {
    if ($t['id'] === $ticketId) {
        $ticket = $t;
        break;
    }
}

// Fallback to first ticket if not found (prototype)
if ($ticket === null) {
    $ticket = $tickets[0];
}

// Handle status change form submission
$statusMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $newStatus = trim($_POST['new_status']);
    $allowed   = ['Open', 'In Progress', 'Resolved', 'Closed'];
    if (in_array($newStatus, $allowed, true)) {
        // In production: UPDATE tblTickets SET status = ? WHERE ticket_id = ?
        $ticket['status'] = $newStatus;
        $statusMessage = 'Status updated to "' . $newStatus . '".';
    }
}

// Handle mark resolved
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_resolved'])) {
    $ticket['status'] = 'Resolved';
    $statusMessage = 'Ticket marked as Resolved.';
}

// Handle comment submission
$commentMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $commentText = trim($_POST['comment']);
    if ($commentText !== '') {
        $staffName = $_SESSION['staff_name'] ?? 'Staff';
        $initials  = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $staffName))));
        $initials  = substr($initials, 0, 2);
        // In production: INSERT INTO tblComments ...
        array_push($ticket['comments'], [
            'initials' => $initials,
            'author'   => $staffName,
            'time'     => 'Just now',
            'text'     => $commentText,
        ]);
        $commentMessage = 'Comment posted.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | #<?= htmlspecialchars($ticket['id']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">

    <?php require __DIR__ . '/nav.php'; ?>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="tickets.php">&larr; All Tickets</a>
            <span class="breadcrumb-sep">/</span>
            <span style="font-family:var(--mono); font-size:12px;">#<?= htmlspecialchars($ticket['id']) ?></span>
        </div>

        <!-- Ticket heading -->
        <div class="ticket-header-row">
            <div class="page-title" style="font-family:var(--mono);">#<?= htmlspecialchars($ticket['id']) ?></div>
            <span class="badge <?= statusBadgeClass($ticket['status']) ?>"><?= htmlspecialchars($ticket['status']) ?></span>
        </div>
        <div class="ticket-header-title"><?= htmlspecialchars($ticket['description']) ?></div>

        <?php if ($statusMessage !== ''): ?>
            <div class="msg ok" style="margin-bottom:16px;"><?= htmlspecialchars($statusMessage) ?></div>
        <?php endif; ?>

        <!-- Action row -->
        <div class="action-row">
            <form method="post" style="display:flex; align-items:center; gap:10px;">
                <label class="form-label" style="margin:0;">Change Status:</label>
                <select class="select-input" name="new_status">
                    <?php foreach (['Open','In Progress','Resolved','Closed'] as $s): ?>
                    <option value="<?= $s ?>" <?= $ticket['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-sm" type="submit">Apply</button>
            </form>

            <form method="post">
                <button class="btn-sm primary" type="submit" name="mark_resolved" value="1">&#10003; Mark Resolved</button>
            </form>
        </div>

        <!-- Detail card -->
        <div class="detail-card">
            <div class="detail-row">
                <div class="detail-key">Submitted By</div>
                <div class="detail-val"><?= htmlspecialchars($ticket['submitted_by']) ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Category</div>
                <div class="detail-val"><?= htmlspecialchars($ticket['category']) ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Priority</div>
                <div class="detail-val">
                    <div class="priority-cell">
                        <div class="priority-dot <?= priorityDotClass($ticket['priority']) ?>"></div>
                        <strong style="color:var(--priority-<?= strtolower($ticket['priority'] === 'Medium' ? 'med' : strtolower($ticket['priority'])) ?>);">
                            <?= htmlspecialchars($ticket['priority']) ?>
                        </strong>
                    </div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Date Opened</div>
                <div class="detail-val" style="font-family:var(--mono); font-size:13px;"><?= htmlspecialchars($ticket['date_opened']) ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-key">Assigned To</div>
                <div class="detail-val">
                    <div class="assignee-cell">
                        <div class="assignee-dot" style="width:28px;height:28px;font-size:10px;"><?= htmlspecialchars($ticket['assigned_initials']) ?></div>
                        <?= htmlspecialchars($ticket['assigned_to']) ?>
                    </div>
                </div>
            </div>
            <div class="detail-row" style="border-bottom:none;">
                <div class="detail-key">Description</div>
                <div class="detail-val">
                    <div class="description-block"><?= htmlspecialchars($ticket['detail']) ?></div>
                </div>
            </div>
        </div>

        <!-- Activity / Comments -->
        <div class="detail-card">
            <div class="activity-header">Activity &amp; Comments</div>

            <?php if (empty($ticket['comments'])): ?>
                <p style="font-size:13px; color:var(--text-muted);">No comments yet. Be the first to add an update.</p>
            <?php else: ?>
                <?php foreach ($ticket['comments'] as $c): ?>
                <div class="comment-item">
                    <div class="comment-avatar"><?= htmlspecialchars($c['initials']) ?></div>
                    <div class="comment-bubble">
                        <div class="comment-meta">
                            <span class="comment-author"><?= htmlspecialchars($c['author']) ?></span>
                            <span class="comment-time"><?= htmlspecialchars($c['time']) ?></span>
                        </div>
                        <div class="comment-text"><?= htmlspecialchars($c['text']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($commentMessage !== ''): ?>
                <div class="msg ok" style="margin-top:12px;"><?= htmlspecialchars($commentMessage) ?></div>
            <?php endif; ?>

            <!-- Post comment form -->
            <div style="border-top:1px solid var(--border); padding-top:16px; margin-top:12px;">
                <div class="add-comment-label">Add Comment</div>
                <form method="post">
                    <div class="comment-input-row">
                        <textarea
                            class="comment-input"
                            name="comment"
                            placeholder="Add a note, update, or resolution detail..."
                        ></textarea>
                        <button class="btn-sm primary" type="submit" style="height:80px; padding:0 20px;">Post</button>
                    </div>
                </form>
            </div>
        </div>

    </main>
</div><!-- .app-body -->
</div><!-- .app-shell -->
</body>
</html>
