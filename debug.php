<?php
/**
 * debug.php — CTST Password Hash Debugger & Auto-Fixer
 * Place this file in your CTST folder (same level as index.php)
 * Open it at: http://localhost/CTST/debug.php
 * DELETE THIS FILE immediately after fixing.
 */
require_once __DIR__ . '/includes/DBConn.php';
$conn = getDbConnection();

$fixed   = 0;
$errors  = [];

// ── Auto-fix all staff passwords to "password123" ────────────────────────────
if (isset($_POST['fix'])) {
    $newHash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt    = $conn->prepare("UPDATE tblStaff SET password_hash = ?");
    $stmt->bind_param('s', $newHash);
    if ($stmt->execute()) {
        $fixed = $stmt->affected_rows;
    } else {
        $errors[] = 'UPDATE failed: ' . $conn->error;
    }
    $stmt->close();
}

// ── Custom password fix ──────────────────────────────────────────────────────
if (isset($_POST['custom_fix'])) {
    $targetEmail    = trim($_POST['target_email']    ?? '');
    $customPassword = trim($_POST['custom_password'] ?? '');
    if ($targetEmail !== '' && $customPassword !== '') {
        $newHash = password_hash($customPassword, PASSWORD_DEFAULT);
        $stmt    = $conn->prepare("UPDATE tblStaff SET password_hash = ? WHERE email = ?");
        $stmt->bind_param('ss', $newHash, $targetEmail);
        $stmt->execute();
        $fixed = $stmt->affected_rows;
        $stmt->close();
    }
}

// ── Fetch all staff with verification results ────────────────────────────────
$result    = $conn->query("SELECT staff_id, full_name, email, role, password_hash FROM tblStaff");
$staffRows = $result->fetch_all(MYSQLI_ASSOC);

// ── Generate a fresh hash for display ───────────────────────────────────────
$freshHash = password_hash('password123', PASSWORD_DEFAULT);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST Debug — Password Fixer</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; padding: 32px 20px; }
        .wrap { max-width: 860px; margin: 0 auto; }

        .banner {
            background: #dc2626; color: white;
            border-radius: 10px; padding: 14px 20px;
            font-weight: 600; font-size: 14px;
            margin-bottom: 24px; display: flex;
            align-items: center; gap: 10px;
        }

        h1 { font-size: 22px; font-weight: 700; color: #f8fafc; margin-bottom: 4px; }
        .sub { font-size: 13px; color: #64748b; margin-bottom: 28px; }

        .card {
            background: #1e293b; border: 1px solid #334155;
            border-radius: 12px; padding: 24px; margin-bottom: 20px;
        }
        .card h2 { font-size: 14px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: .06em; color: #94a3b8; margin-bottom: 16px; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; padding: 10px 12px; background: #0f172a;
             color: #64748b; font-weight: 600; font-size: 11px;
             text-transform: uppercase; letter-spacing: .05em; }
        td { padding: 11px 12px; border-top: 1px solid #1e293b; vertical-align: middle; }
        tr:nth-child(even) td { background: #1a2744; }

        .ok   { background: #166534; color: #bbf7d0; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .fail { background: #991b1b; color: #fecaca; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .role-badge { background: #1e3a5f; color: #93c5fd; padding: 2px 8px; border-radius: 6px; font-size: 11px; }

        .hash-preview { font-family: monospace; font-size: 11px; color: #475569;
                        word-break: break-all; max-width: 260px; }

        .btn {
            display: inline-block; padding: 11px 22px; border-radius: 8px;
            border: none; cursor: pointer; font-size: 14px; font-weight: 600;
            font-family: inherit; transition: opacity .15s;
        }
        .btn:hover { opacity: .88; }
        .btn-red   { background: #dc2626; color: white; }
        .btn-blue  { background: #2563eb; color: white; }
        .btn-green { background: #16a34a; color: white; }

        input[type=email], input[type=text], input[type=password] {
            background: #0f172a; border: 1px solid #334155; color: #e2e8f0;
            border-radius: 7px; padding: 9px 12px; font-size: 13px;
            font-family: inherit; width: 100%; margin-bottom: 10px;
        }
        input:focus { outline: none; border-color: #3b82f6; }

        .success-banner {
            background: #166534; border: 1px solid #22c55e;
            border-radius: 10px; padding: 14px 20px;
            margin-bottom: 20px; font-size: 14px; font-weight: 600; color: #bbf7d0;
        }
        .hash-box {
            background: #0f172a; border: 1px solid #334155;
            border-radius: 8px; padding: 12px 14px;
            font-family: monospace; font-size: 12px; color: #7dd3fc;
            word-break: break-all; margin-top: 8px;
        }
        .delete-warning {
            background: #7c2d12; border: 1px solid #ea580c;
            border-radius: 10px; padding: 16px 20px; margin-top: 24px;
            font-size: 13px; line-height: 1.7; color: #fed7aa;
        }
        .delete-warning strong { color: #f97316; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="banner">
        ⚠️ DEBUG FILE — Delete this immediately after fixing your passwords
    </div>

    <h1>CTST — Password Hash Debugger</h1>
    <div class="sub">Diagnoses and fixes password_hash / password_verify issues in your database.</div>

    <!-- Success message -->
    <?php if ($fixed > 0): ?>
    <div class="success-banner">
        ✅ <?= $fixed ?> account<?= $fixed !== 1 ? 's' : '' ?> updated successfully. Try logging in now.
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="banner"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <!-- Staff verification table -->
    <div class="card">
        <h2>Staff Accounts — Hash Verification</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Hash stored</th>
                    <th>Verifies "password123"</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($staffRows as $s):
                $verifies = password_verify('password123', $s['password_hash']);
            ?>
            <tr>
                <td style="color:#475569;font-family:monospace"><?= $s['staff_id'] ?></td>
                <td style="font-weight:500"><?= htmlspecialchars($s['full_name']) ?></td>
                <td style="font-family:monospace;font-size:12px;color:#94a3b8"><?= htmlspecialchars($s['email']) ?></td>
                <td><span class="role-badge"><?= htmlspecialchars($s['role']) ?></span></td>
                <td>
                    <div class="hash-preview">
                        <?php
                        $h = $s['password_hash'];
                        // Show first 30 chars and last 6
                        echo htmlspecialchars(strlen($h) > 36
                            ? substr($h, 0, 30) . '…' . substr($h, -6)
                            : $h);
                        ?>
                    </div>
                </td>
                <td>
                    <?php if ($verifies): ?>
                        <span class="ok">✓ MATCH</span>
                    <?php else: ?>
                        <span class="fail">✗ NO MATCH</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($staffRows)): ?>
            <tr><td colspan="6" style="text-align:center;color:#475569;padding:24px">No staff accounts found in database.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Fix all to password123 -->
    <div class="card">
        <h2>Fix 1 — Reset ALL staff passwords to "password123"</h2>
        <p style="font-size:13px;color:#94a3b8;margin-bottom:16px;line-height:1.6">
            This generates a fresh correct hash from <code style="color:#7dd3fc">password_hash('password123', PASSWORD_DEFAULT)</code>
            and updates every staff account in one go. Use this to get everyone working immediately.
        </p>
        <div class="hash-box"><?= htmlspecialchars($freshHash) ?></div>
        <p style="font-size:12px;color:#475569;margin:10px 0 16px">↑ This is the hash that will be written to all accounts.</p>
        <form method="post">
            <button type="submit" name="fix" class="btn btn-red">
                Reset ALL passwords → password123
            </button>
        </form>
    </div>

    <!-- Fix one account -->
    <div class="card">
        <h2>Fix 2 — Set a specific password for one account</h2>
        <p style="font-size:13px;color:#94a3b8;margin-bottom:16px;line-height:1.6">
            Use this to set a custom password for a specific staff member without affecting others.
        </p>
        <form method="post">
            <label style="font-size:12px;color:#94a3b8;display:block;margin-bottom:4px">Staff Email</label>
            <input type="email" name="target_email" placeholder="j.mokoena@rosebankcollege.co.za">
            <label style="font-size:12px;color:#94a3b8;display:block;margin-bottom:4px">New Password</label>
            <input type="text" name="custom_password" placeholder="Enter the new password">
            <button type="submit" name="custom_fix" class="btn btn-blue">
                Set Password for This Account
            </button>
        </form>
    </div>

    <!-- Manual SQL -->
    <div class="card">
        <h2>Fix 3 — Manual SQL (run in phpMyAdmin)</h2>
        <p style="font-size:13px;color:#94a3b8;margin-bottom:12px;line-height:1.6">
            If the buttons above don't work, copy and run this directly in phpMyAdmin → SQL tab.
        </p>
        <div class="hash-box">
UPDATE tblStaff SET password_hash = '<?= htmlspecialchars($freshHash) ?>';<br><br>
-- Verify it worked:<br>
SELECT email, password_hash FROM tblStaff;
        </div>
    </div>

    <!-- Diagnostic info -->
    <div class="card">
        <h2>Diagnostic Info</h2>
        <table>
            <tr><td style="color:#64748b;width:200px">PHP Version</td><td style="font-family:monospace;color:#7dd3fc"><?= PHP_VERSION ?></td></tr>
            <tr><td style="color:#64748b">password_hash() works</td><td><?= function_exists('password_hash') ? '<span class="ok">✓ YES</span>' : '<span class="fail">✗ NO</span>' ?></td></tr>
            <tr><td style="color:#64748b">password_verify() works</td><td><?= function_exists('password_verify') ? '<span class="ok">✓ YES</span>' : '<span class="fail">✗ NO</span>' ?></td></tr>
            <tr><td style="color:#64748b">DB connected</td><td><span class="ok">✓ YES</span></td></tr>
            <tr><td style="color:#64748b">Staff accounts found</td><td style="font-family:monospace;color:#7dd3fc"><?= count($staffRows) ?></td></tr>
            <tr><td style="color:#64748b">Accounts with correct hash</td><td style="font-family:monospace;color:#7dd3fc"><?= count(array_filter($staffRows, fn($s) => password_verify('password123', $s['password_hash']))) ?> / <?= count($staffRows) ?></td></tr>
            <tr><td style="color:#64748b">PASSWORD_DEFAULT algo</td><td style="font-family:monospace;color:#7dd3fc">bcrypt (cost 10)</td></tr>
            <tr><td style="color:#64748b">Generated hash starts with</td><td style="font-family:monospace;color:#7dd3fc"><?= substr($freshHash, 0, 7) ?>…</td></tr>
        </table>
    </div>

    <div class="delete-warning">
        <strong>⚠️ IMPORTANT — Delete this file now.</strong><br>
        Once your passwords are fixed and you can log in, delete <code>debug.php</code> from your project.
        Leaving it in place is a security risk — anyone who visits the URL can reset all your passwords.
        <br><br>
        <strong>File to delete:</strong> <code><?= htmlspecialchars(__FILE__) ?></code>
    </div>

</div>
</body>
</html>
