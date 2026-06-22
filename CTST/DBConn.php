<?php
declare(strict_types=1);

/**
 * Centralized MySQLi connection for CTST.
 * Update these values to match your local WAMP/XAMPP MySQL settings.
 */
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'ctst_db';
const DB_PORT = 3306;

function getDbConnection(): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
