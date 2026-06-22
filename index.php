<?php
declare(strict_types=1);
/**
 * index.php — Login page for IT Staff
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['staff_id'])) { header('Location: dashboard.php'); exit; }

require_once __DIR__ . '/includes/DBConn.php';

$error       = '';
$stickyEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $stickyEmail = htmlspecialchars($email);

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT staff_id, full_name, role, password_hash FROM tblStaff
             WHERE email = ? AND role != 'inactive' LIMIT 1"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$row) {
            $error = 'No active account found for that email.';
        } elseif (!password_verify($password, $row['password_hash'])) {
            $error = 'Incorrect password. Please try again.';
        } else {
            session_regenerate_id(true);
            $_SESSION['staff_id']   = (int)$row['staff_id'];
            $_SESSION['staff_name'] = $row['full_name'];
            $_SESSION['staff_role'] = $row['role'];
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-logo">
        <div class="login-logo-icon">
            <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
        </div>
        <div class="login-brand">CTST</div>
    </div>
    <div class="login-subtitle">
        Campus Tech Support Ticket System<br>
        <strong>IIE Rosebank College</strong> — Staff Portal
    </div>

    <?php if ($error !== ''): ?>
        <div class="msg bad"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="form-group">
            <label class="form-label" for="email">Email address</label>
            <input class="form-input" type="email" id="email" name="email"
                   required autocomplete="email"
                   placeholder="staff@rosebankcollege.co.za"
                   value="<?= $stickyEmail ?>">
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="password-wrap">
                <input class="form-input" type="password" id="password" name="password"
                       required autocomplete="current-password"
                       placeholder="Enter your password">
                <button type="button" class="pw-toggle" onclick="togglePw()">
                    <svg id="pwIcon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>
        <button type="submit" class="btn-primary">Sign In</button>
    </form>

    <div class="login-footnote">
        IT Staff accounts are managed by your system administrator.<br>
        Contact your admin if you cannot log in.
    </div>
</div>

<script>
function togglePw() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
<script src="assets/theme.js"></script>
</body>
</html>
