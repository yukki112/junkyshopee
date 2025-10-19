<?php
// Define database constants
define('DB_HOST', 'sql301.infinityfree.com');
define('DB_NAME', 'if0_39632973_if0_39632973_');
define('DB_USER', 'if0_39632973');
define('DB_PASS', '12Spykekyle12');

// Keep the original variables for backward compatibility
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