<?php
session_start();
require_once 'db_connection.php';

// Check if 2FA session exists
if (!isset($_SESSION['2fa_code']) || !isset($_SESSION['2fa_user_id'])) {
    header("Location: Login.php");
    exit();
}

// Check if code has expired
if (time() > $_SESSION['2fa_expires']) {
    unset($_SESSION['2fa_code']);
    unset($_SESSION['2fa_user_id']);
    unset($_SESSION['2fa_user_type']);
    unset($_SESSION['2fa_expires']);
    header("Location: Login.php?error=Code expired. Please login again.");
    exit();
}

$error = '';
$resent = false;

// Handle code verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $entered_code = trim($_POST['verification_code']);
    
    if ($entered_code === $_SESSION['2fa_code']) {
        // Code is correct, complete login
        $user_id = $_SESSION['2fa_user_id'];
        $user_type = $_SESSION['2fa_user_type'];
        $remember_me = $_SESSION['2fa_remember_me'] ?? false;
        
        // Clear 2FA session data
        unset($_SESSION['2fa_code']);
        unset($_SESSION['2fa_expires']);
        unset($_SESSION['2fa_remember_me']);
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        if ($user_type === 'user') {
            $is_admin = $_SESSION['2fa_is_admin'];
            unset($_SESSION['2fa_is_admin']);
            unset($_SESSION['2fa_user_type']);
            
            // Get user data
            $stmt = $conn->prepare("SELECT first_name, last_name, email, username FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['last_activity'] = time();
            
            // Handle remember me
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 30);
                $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                $conn->query("INSERT INTO auth_tokens (user_id, token_hash, expires_at) 
                             VALUES ($user_id, '$hashedToken', FROM_UNIXTIME($expiry))");
                setcookie('remember_me', $user_id . ':' . $token, $expiry, "/", "", true, true);
            }
            
            // Redirect based on user type
            if ($is_admin) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['user_type'] = 'admin';
                header("Location: ../../admin/index.php");
                exit();
            } else {
                $_SESSION['community_logged_in'] = true;
                $_SESSION['user_type'] = 'community';
                header("Location: ../index.php");
                exit();
            }
        } else {
            // Employee login
            unset($_SESSION['2fa_user_type']);
            
            $stmt = $conn->prepare("SELECT first_name, last_name, email, username FROM employees WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $employee = $result->fetch_assoc();
            
            $_SESSION['employee_id'] = $user_id;
            $_SESSION['employee_email'] = $employee['email'];
            $_SESSION['employee_username'] = $employee['username'];
            $_SESSION['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
            $_SESSION['last_activity'] = time();
            
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 30);
                $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                $conn->query("INSERT INTO employee_auth_tokens (employee_id, token_hash, expires_at) 
                             VALUES ($user_id, '$hashedToken', FROM_UNIXTIME($expiry))");
                setcookie('remember_me_employee', $user_id . ':' . $token, $expiry, "/", "", true, true);
            }
            
            $_SESSION['employee_logged_in'] = true;
            $_SESSION['user_type'] = 'employee';
            header("Location: ../../employee/index.php");
            exit();
        }
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}

// Handle resend code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_code'])) {
    // Check if PHPMailer is available
    $phpmailerAvailable = true;
    try {
        // Check if PHPMailer files exist and can be loaded
        $phpmailerPath = __DIR__ . '/vendor/autoload.php';
        if (file_exists($phpmailerPath)) {
            require_once $phpmailerPath;
            
            // Check if PHPMailer class exists
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                // Generate new code
                $verification_code = sprintf("%06d", mt_rand(0, 999999));
                $_SESSION['2fa_code'] = $verification_code;
                $_SESSION['2fa_expires'] = time() + 300; // Reset expiration
                
                // Get user email
                $user_id = $_SESSION['2fa_user_id'];
                $user_type = $_SESSION['2fa_user_type'];
                
                if ($user_type === 'user') {
                    $stmt = $conn->prepare("SELECT first_name, email FROM users WHERE id = ?");
                } else {
                    $stmt = $conn->prepare("SELECT first_name, email FROM employees WHERE id = ?");
                }
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                // Send new code using PHPMailer
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'Stephenviray12@gmail.com';
                    $mail->Password   = 'bubr nckn tgqf lvus';
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    
                    $mail->setFrom('Stephenviray12@gmail.com', 'JunkValue Security');
                    $mail->addAddress($user['email'], $user['first_name']);
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Your New JunkValue Verification Code';
                    $mail->Body    = "Hi {$user['first_name']},<br><br>"
                                    . "Your new verification code is: <strong style='font-size: 24px; color: #6A7F46; letter-spacing: 3px;'>{$verification_code}</strong><br><br>"
                                    . "This code will expire in 5 minutes.<br><br>"
                                    . "Thanks,<br>The JunkValue Security Team";
                    
                    $mail->send();
                    $resent = true;
                } catch (Exception $e) {
                    $error = "Failed to resend code. Please try again.";
                }
            } else {
                $phpmailerAvailable = false;
            }
        } else {
            $phpmailerAvailable = false;
        }
    } catch (Exception $e) {
        $phpmailerAvailable = false;
    }
    
    // Fallback if PHPMailer is not available
    if (!$phpmailerAvailable) {
        $error = "Email service is currently unavailable. Please try again later.";
    }
}

$time_remaining = $_SESSION['2fa_expires'] - time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - JunkValue</title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../img/Background.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .verification-container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .shield-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6A7F46, #5a6f36);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 10px 30px rgba(106, 127, 70, 0.3);
        }
        
        .shield-icon i {
            font-size: 40px;
            color: white;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .code-input-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }
        
        .code-input {
            width: 60px;
            height: 70px;
            font-size: 32px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-weight: bold;
            color: #2c3e50;
            transition: all 0.3s;
        }
        
        .code-input:focus {
            outline: none;
            border-color: #6A7F46;
            box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
        }
        
        .timer {
            background-color: rgba(106, 127, 70, 0.1);
            border: 1px solid rgba(106, 127, 70, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .timer i {
            color: #6A7F46;
            font-size: 20px;
        }
        
        .timer-text {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .btn {
            padding: 15px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6A7F46, #5a6f36);
            color: white;
            margin-bottom: 15px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(106, 127, 70, 0.3);
        }
        
        .btn-outline {
            background-color: white;
            border: 2px solid #ddd;
            color: #2c3e50;
        }
        
        .btn-outline:hover {
            border-color: #6A7F46;
            color: #6A7F46;
            background-color: rgba(106, 127, 70, 0.05);
        }
        
        .error-message {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-message {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .back-link {
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #6A7F46;
        }
        
        @media (max-width: 576px) {
            .verification-container {
                padding: 30px 20px;
            }
            
            .code-input {
                width: 45px;
                height: 55px;
                font-size: 24px;
            }
            
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="shield-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <h1>Two-Factor Authentication</h1>
        <p class="subtitle">
            We've sent a 6-digit verification code to your email address. Please enter it below to complete your login.
        </p>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($resent): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span>A new verification code has been sent to your email!</span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="verifyForm">
            <div class="code-input-container">
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
            </div>
            
            <input type="hidden" name="verification_code" id="fullCode">
            
            <div class="timer">
                <i class="fas fa-clock"></i>
                <span class="timer-text">Code expires in <span id="countdown"><?php echo $time_remaining; ?></span> seconds</span>
            </div>
            
            <button type="submit" name="verify_code" class="btn btn-primary">
                <i class="fas fa-check"></i> Verify Code
            </button>
        </form>
        
        <form method="POST" action="">
            <button type="submit" name="resend_code" class="btn btn-outline">
                <i class="fas fa-redo"></i> Resend Code
            </button>
        </form>
        
        <a href="Login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
    </div>
    
    <script>
        // Auto-focus and auto-advance code inputs
        const inputs = document.querySelectorAll('.code-input');
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                
                // Combine all inputs
                let fullCode = '';
                inputs.forEach(inp => fullCode += inp.value);
                document.getElementById('fullCode').value = fullCode;
                
                // Auto-submit when all filled
                if (fullCode.length === 6) {
                    document.getElementById('verifyForm').submit();
                }
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Only allow numbers
            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });
        
        // Auto-focus first input
        inputs[0].focus();
        
        // Countdown timer
        let timeRemaining = <?php echo $time_remaining; ?>;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            timeRemaining--;
            countdownElement.textContent = timeRemaining;
            
            if (timeRemaining <= 0) {
                clearInterval(timer);
                window.location.href = 'Login.php?error=Code expired. Please login again.';
            }
        }, 1000);
    </script>
</body>
</html>