<?php
declare(strict_types=1);
/**
 * includes/auth.php
 * Include at the top of every protected page.
 * Redirects to login if no active session.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['staff_id'])) {
    header('Location: ' . str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/', 1) - 1) . 'index.php');
    exit;
}
