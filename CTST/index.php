<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/DBConn.php';

$conn = getDbConnection();
$message = '';
$messageClass = '';
$sticky = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $sticky['email'] = $email;

    $stmt = $conn->prepare("SELECT * FROM tblStaff WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $message = 'No account found for that email address.';
        $messageClass = 'bad';
    } else {
        $row = $result->fetch_assoc();
        if (!password_verify($password, $row['password_hash'])) {
            $message = 'Incorrect password. Please try again.';
            $messageClass = 'bad';
        } else {
            $_SESSION['staff_id']   = (int)$row['staff_id'];
            $_SESSION['staff_name'] = $row['full_name'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | Staff Portal Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-logo">
        <div class="login-logo-icon">
            <svg viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="3"/>
                <path d="M9 12l2 2 4-4"/>
                <path d="M3 9h18"/>
            </svg>
        </div>
        <div class="login-brand">CTST</div>
    </div>
    <div class="login-subtitle">
        Campus Tech Support Ticket System<br>
        IIE Rosebank College — Staff Portal
    </div>

    <?php if ($message !== ''): ?>
        <div class="msg <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input
                class="form-input"
                type="email"
                id="email"
                name="email"
                required
                placeholder="staff@rosebankcollege.co.za"
                value="<?= htmlspecialchars($sticky['email']) ?>"
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="password-wrap">
                <input class="form-input" type="password" id="password" name="password" required minlength="6">
                <button class="pw-toggle" type="button" onclick="togglePw(this)" aria-label="Show or hide password">
                    <svg id="eye-icon" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
        </div>

        <button class="btn-primary" type="submit">Sign In</button>
    </form>

    <div class="login-footnote">
        Rosebank College IT Department &middot; Confidential System<br>
        Access issues? Email <a href="mailto:helpdesk@rosebankcollege.co.za">helpdesk@rosebankcollege.co.za</a>
    </div>
</div>

<script>
function togglePw(btn) {
    const input = document.getElementById('password');
    input.type = (input.type === 'password') ? 'text' : 'password';
}
</script>
</body>
</html>
