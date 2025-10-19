<?php
// Define database constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'frsm_junkvalue');
define('DB_USER', 'frsm_junkvalue');
define('DB_PASS', 'Admin123');


$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}
?>