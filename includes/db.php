<?php
// One shared MySQL connection (mysqli). Use: $db = getDB();
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // errors become exceptions

function getDB(): mysqli
{
    static $conn = null;

    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            $conn->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $error) {
            http_response_code(500);
            die('Database connection failed. Is MySQL running in XAMPP, and was dejabrew.sql imported?');
        }
    }
    return $conn;
}