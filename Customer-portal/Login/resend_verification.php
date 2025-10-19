<?php
session_start();
require_once 'db_connection.php';

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Check if token exists and isn't expired
    $result = mysqli_query($conn, 
        "SELECT id, email FROM users 
         WHERE verification_token = '$token' 
         AND token_expires_at > NOW() 
         AND email_verified = 0");
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Mark email as verified
        mysqli_query($conn, 
            "UPDATE users 
             SET email_verified = 1, 
                 verification_token = NULL,
                 token_expires_at = NULL
             WHERE id = {$user['id']}");
        
        $_SESSION['verified'] = true;
        $_SESSION['message'] = "Email verified successfully! You can now login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid or expired verification link.";
        header("Location: register.php");
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .success {
            color: #28a745;
            margin-bottom: 20px;
        }
        .error {
            color: #dc3545;
            margin-bottom: 20px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success)): ?>
            <h2 class="success">Verification Email Sent</h2>
            <p><?php echo $success; ?></p>
            <p><a href="Login.php">Back to login</a></p>
        <?php elseif (isset($error)): ?>
            <h2 class="error">Error</h2>
            <p><?php echo $error; ?></p>
            <p><a href="Login.php">Back to login</a> or <a href="contact.php">contact support</a></p>
        <?php endif; ?>
    </div>
</body>
</html>