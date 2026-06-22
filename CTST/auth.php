<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['staff_id'])) {
    header('Location: index.php');
    exit;
}
