<?php
session_start();
session_unset();
session_destroy();
header("Location: ../Customer-portal/Login/Login.php"); // Redirect back to main website
exit();
?>