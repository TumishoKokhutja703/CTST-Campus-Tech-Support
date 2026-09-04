<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

define('DB_HOST', 'sql308.infinityfree.com');
define('DB_USER', 'if0_42829833');
define('DB_PASS', 'XoNJ0oPhzL');
define('DB_NAME', 'if0_42829833_ctst_db');
define('DB_PORT', 3306);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}
$conn->set_charset('utf8mb4');
