<?php
session_start();

if (!isset($_SESSION['receipt_path']) || !isset($_SESSION['transaction_id'])) {
    header("Location: transaction_logging.php");
    exit();
}

$filepath = $_SESSION['receipt_path'];
$filename = 'Receipt_' . $_SESSION['transaction_id'] . '.pdf';

if (file_exists($filepath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    
    // Optionally delete the file after download
    // unlink($filepath);
    
    // Clear the session variables
    unset($_SESSION['receipt_path'], $_SESSION['transaction_id']);
    exit();
} else {
    $_SESSION['error'] = "Receipt file not found. Please contact support.";
    header("Location: transaction_logging.php");
    exit();
}
?>