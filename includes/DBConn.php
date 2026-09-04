<?php
declare(strict_types=1);

const DB_HOST = 'sql308.infinityfree.com';   
const DB_USER = 'if0_42829833_XXX';               
const DB_PASS = 'XoNJ0oPhzL';               
const DB_NAME = 'if0_42829833_ctst_db';       
const DB_PORT = 3306;

function getDbConnection(): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        // In production, log this instead of echoing
        die(json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
