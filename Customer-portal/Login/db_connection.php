<?php
// Database configuration
define('DB_SERVER', 'sql301.infinityfree.com');
define('DB_USERNAME', 'if0_39632973'); // Default XAMPP username
define('DB_PASSWORD', '12Spykekyle12'); // Default XAMPP has no password
define('DB_NAME', 'if0_39632973_if0_39632973_');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>