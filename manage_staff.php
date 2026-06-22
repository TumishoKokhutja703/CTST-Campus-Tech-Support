<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/DBConn.php';

if (!in_array($_SESSION['staff_role'] ?? '', ['admin','senior'])) {
    header('Location: dashboard.php'); exit;
}

$activePage = 'manage_staff';
$conn       = getDbConnection();
require_once __DIR__ . '/includes/appData.php';

$message = ''; $messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_staff') {
        $fullName = trim($_POST['full_name']       ?? '');
        $email    = trim($_POST['email']           ?? '');
        $password =      $_POST['password']        ?? '';
        $confirm  =      $_POST['confirm_password'] ?? '';
        $role     =      $_POST['role']            ?? 'technician';

        if ($fullName==='' || $email==='' || $password==='') {
            $message = 'Please fill in all fields.'; $messageClass = 'bad';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.'; $messageClass = 'bad';
        } elseif ($password !== $confirm) {
            $message = 'Passwords do not match.'; $messageClass = 'bad';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.'; $messageClass = 'bad';
        } else {
            $chk = $conn->prepare("SELECT staff_id FROM tblStaff WHERE email=?");
            $chk->bind_param('s', $email); $chk->execute(); $chk->store_result();
            if ($chk->num_rows > 0) {
                $message = 'An account with that email already exists.'; $messageClass = 'bad';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins  = $conn->prepare("INSERT INTO tblStaff (full_name,email,password_hash,role) VALUES (?,?,?,?)");
                $ins->bind_param('ssss', $fullName, $email, $hash, $role);
                $message      = $ins->execute() ? 'Staff account created successfully.' : 'Something went wrong.';
                $messageClass = $ins->execute() ? 'ok' : 'bad';
                $ins->close();
                if ($messageClass === 'ok') $message = 'Staff account created successfully.';
            }
            $chk->close();
        }
    }

    if ($action === 'deactivate') {
        $targetId = (int)($_POST['staff_id'] ?? 0);
        if ($targetId === (int)$_SESSION['staff_id']) {
            $message = 'You cannot deactivate your own account.'; $messageClass = 'warn';
        } elseif ($targetId > 0) {
            $s = $conn->prepare("UPDATE tblStaff SET role='inactive' WHERE staff_id=?");
            $s->bind_param('i', $targetId); $s->execute(); $s->close();
            $message = 'Staff account deactivated.'; $messageClass = 'warn';
        }
    }
}

$staffList = $conn->query("SELECT staff_id, full_name, email, role, created_at FROM tblStaff ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | Manage Staff</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .modal-overlay{display:none;position:fixed;inset:0;z-index:200;background:rgba(15,23,42,.55);align-items:center;justify-content:center}
        .modal-overlay.open{display:flex}
        .modal{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px;width:460px;max-width:95vw;box-shadow:0 20px 60px rgba(15,45,82,.2)}
        .modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
        .modal-title{font-size:16px;font-weight:600;color:var(--text-primary)}
        .modal-close{background:none;border:none;cursor:pointer;color:var(--text-muted);display:flex;align-items:center;border-radius:6px;padding:4px;transition:background .12s}
        .modal-close:hover{background:var(--bg);color:var(--text-primary)}
        .modal-note{font-size:12px;color:var(--text-muted);background:var(--bg);border-radius:8px;padding:10px 12px;margin-bottom:16px;line-height:1.5}
        .modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:20px}
        .btn-deactivate{height:30px;padding:0 12px;border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;border:1px solid var(--border-strong);background:var(--white);color:var(--critical);font-family:var(--font);transition:background .12s}
        .btn-deactivate:hover{background:var(--critical-bg)}
        .btn-deactivate:disabled{opacity:.4;cursor:not-allowed}
        .inactive-row{opacity:.5}
    </style>
</head>
<body>
<div class="app-shell">
<?php require __DIR__ . '/nav.php'; ?>

    <div class="page-header">
        <div>
            <div class="page-title">Manage Staff</div>
            <div class="page-sub">Create and manage IT support staff accounts</div>
        </div>
        <button class="btn-sm primary" onclick="openModal()">+ Add Staff Member</button>
    </div>

    <?php if ($message !== ''): ?>
        <div class="msg <?= $messageClass ?>" style="margin-bottom:18px"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="table-card">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Date Added</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($staffList)): ?>
                <tr><td colspan="5"><div class="empty-state"><div class="empty-state-title">No staff accounts</div></div></td></tr>
            <?php endif; ?>
            <?php foreach ($staffList as $s): ?>
            <tr class="<?= $s['role']==='inactive'?'inactive-row':'' ?>">
                <td>
                    <div class="assignee-cell">
                        <div class="assignee-dot"><?= initials($s['full_name']) ?></div>
                        <?= htmlspecialchars($s['full_name']) ?>
                    </div>
                </td>
                <td style="font-family:var(--mono);font-size:12px"><?= htmlspecialchars($s['email']) ?></td>
                <td>
                    <?php
                    $rc = match($s['role']) { 'admin'=>'badge-critical','senior'=>'badge-progress','technician'=>'badge-resolved',default=>'badge-open' };
                    $rl = match($s['role']) { 'admin'=>'Admin','senior'=>'Senior Tech','technician'=>'Technician','inactive'=>'Inactive',default=>ucfirst($s['role']) };
                    ?>
                    <span class="badge <?= $rc ?>"><?= $rl ?></span>
                </td>
                <td style="font-family:var(--mono);font-size:12px;color:var(--text-muted)"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                <td>
                    <?php if ($s['role'] !== 'inactive'): ?>
                    <form method="post" onsubmit="return confirm('Deactivate this account?')">
                        <input type="hidden" name="action"   value="deactivate">
                        <input type="hidden" name="staff_id" value="<?= (int)$s['staff_id'] ?>">
                        <button type="submit" class="btn-deactivate"
                            <?= ((int)$s['staff_id']===(int)$_SESSION['staff_id'])?'disabled title="Cannot deactivate yourself"':'' ?>>
                            Deactivate
                        </button>
                    </form>
                    <?php else: ?>
                        <span style="font-size:12px;color:var(--text-muted)">Inactive</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>
</div>
</div>

<!-- Add Staff Modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Add Staff Member</div>
            <button class="modal-close" onclick="closeModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="add_staff">
            <div class="form-group"><label class="form-label">Full Name</label><input class="form-input" type="text" name="full_name" required placeholder="e.g. T. Nkosi"></div>
            <div class="form-group"><label class="form-label">Email Address</label><input class="form-input" type="email" name="email" required placeholder="staff@rosebankcollege.co.za"></div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select class="select-input" name="role" style="width:100%">
                    <option value="technician">Technician</option>
                    <option value="senior">Senior Technician</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Temporary Password</label><input class="form-input" type="password" name="password" required minlength="6" placeholder="Min 6 characters"></div>
            <div class="form-group"><label class="form-label">Confirm Password</label><input class="form-input" type="password" name="confirm_password" required placeholder="Repeat the password"></div>
            <div class="modal-note">Staff will use these credentials to log in. Share the password securely.</div>
            <div class="modal-actions">
                <button type="button" class="btn-sm" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-sm primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal()  { document.getElementById('modalOverlay').classList.add('open'); }
function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }
document.getElementById('modalOverlay').addEventListener('click', e => { if(e.target===e.currentTarget) closeModal(); });
<?php if ($message!=='' && $messageClass==='bad'): ?>openModal();<?php endif; ?>
</script>
<script src="assets/theme.js"></script>
</body>
</html>
