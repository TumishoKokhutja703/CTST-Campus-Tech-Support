<?php
declare(strict_types=1);
/**
 * includes/DBConn.php
 * Single place to manage the database connection.
 * Edit ONLY the four constants below to match your server.
 */

const DB_HOST = '127.0.0.1';   // Use your InfinityFree host when deploying online
const DB_USER = 'root';         // InfinityFree: epiz_XXXXXXXX
const DB_PASS = '';             // InfinityFree: your DB password
const DB_NAME = 'ctst_db';     // InfinityFree: epiz_XXXXXXXX_ctst_db
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
