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
        
        :root {
            --bg-gradient-start: #2C2416;
            --bg-gradient-mid: #3C342C;
            --bg-gradient-end: #6A7F46;
            --container-bg: rgba(255, 255, 255, 0.98);
            --text-primary: #2C2416;
            --text-secondary: #6c757d;
            --border-color: #e9ecef;
            --shadow-color: rgba(0,0,0,0.3);
            --watermark-opacity: 0.12;
            --accent-gold: #FFD700;
            --accent-green: #6A7F46;
            --accent-bronze: #CD7F32;
        }
        
        body.dark-mode {
            --bg-gradient-start: #0a0a0a;
            --bg-gradient-mid: #1a1a1a;
            --bg-gradient-end: #1f2a14;
            --container-bg: rgba(25, 25, 25, 0.98);
            --text-primary: #e8e8e8;
            --text-secondary: #a8a8a8;
            --border-color: #404040;
            --shadow-color: rgba(0,0,0,0.7);
            --watermark-opacity: 0.06;
        }
        
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-mid) 50%, var(--bg-gradient-end) 100%);
            position: relative;
            overflow-x: hidden;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Enhanced animated background with mesh gradient effect */
        .bg-decoration {
            position: fixed;
            border-radius: 50%;
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
            filter: blur(80px);
        }
        
        .bg-decoration-1 {
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, var(--accent-gold) 0%, transparent 70%);
            top: -250px;
            left: -250px;
            animation: float 25s ease-in-out infinite;
        }
        
        .bg-decoration-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--accent-green) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            animation: float 20s ease-in-out infinite reverse;
        }
        
        .bg-decoration-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--accent-bronze) 0%, transparent 70%);
            top: 40%;
            left: 20%;
            animation: float 30s ease-in-out infinite;
        }
        
        .bg-decoration-4 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, transparent 70%);
            top: 60%;
            right: 25%;
            animation: float 22s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(60px, -60px) scale(1.15) rotate(120deg); }
            66% { transform: translate(-40px, 40px) scale(0.85) rotate(240deg); }
        }
        
        /* Enhanced watermark with glow effect */
        .watermark-logo {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 700px;
            height: 700px;
            opacity: var(--watermark-opacity);
            z-index: 0;
            pointer-events: none;
            transition: opacity 0.6s ease;
            animation: floatWatermark 25s ease-in-out infinite;
            filter: drop-shadow(0 0 60px rgba(255, 215, 0, 0.3));
        }
        
        @keyframes floatWatermark {
            0%, 100% { transform: translate(-50%, -50%) scale(1) rotate(0deg); }
            50% { transform: translate(-50%, -52%) scale(1.08) rotate(5deg); }
        }
        
        /* Enhanced dark mode toggle with gradient */
        .dark-mode-toggle {
            position: fixed;
            top: 40px;
            right: 20px;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.25), rgba(106, 127, 70, 0.25));
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.35);
            color: white;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 22px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            opacity: 0;
            animation: slideInRight 0.8s ease forwards 0.3s;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .dark-mode-toggle:hover {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.4), rgba(106, 127, 70, 0.4));
            transform: rotate(180deg) scale(1.15);
            box-shadow: 0 12px 35px rgba(255, 215, 0, 0.4);
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.25), rgba(106, 127, 70, 0.25));
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.35);
            color: white;
            padding: 14px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            opacity: 0;
            animation: slideInLeft 0.8s ease forwards 0.5s;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            font-size: 15px;
        }
        
        .back-button:hover {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.4), rgba(106, 127, 70, 0.4));
            transform: translateX(-8px);
            box-shadow: 0 12px 35px rgba(255, 215, 0, 0.4);
        }
        
        /* Enhanced left side branding with glass morphism */
        .logo-left {
            position: fixed;
            top: 50%;
            left: 180px;
            transform: translateY(-50%);
            z-index: 1;
            opacity: 0;
            animation: fadeInScale 1.2s ease forwards 1s;
            text-align: center;
        }
        
        .logo-left img {
            width: 240px;
            height: auto;
            filter: drop-shadow(0 20px 50px rgba(0,0,0,0.5)) drop-shadow(0 0 30px rgba(255, 215, 0, 0.3));
            margin-bottom: 35px;
            animation: pulse 4s ease-in-out infinite;
        }
        
        .logo-left h1 {
            color: white;
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 18px;
            text-shadow: 0 6px 25px rgba(0,0,0,0.4), 0 0 40px rgba(255, 215, 0, 0.3);
            letter-spacing: 3px;
            background: linear-gradient(135deg, #FFD700, #FFF, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo-left .tagline {
            color: rgba(255, 255, 255, 0.95);
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 45px;
            text-shadow: 0 3px 15px rgba(0,0,0,0.3);
            letter-spacing: 1px;
        }
        
        /* Enhanced security features with gradient borders */
        .security-features {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.12), rgba(106, 127, 70, 0.12));
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            padding: 35px;
            margin-top: 25px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.2);
            position: relative;
            overflow: hidden;
        }
        
        .security-features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }
        
        .security-features h3 {
            color: white;
            font-size: 22px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
        }
        
        .security-item {
            display: flex;
            align-items: center;
            gap: 18px;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 18px;
            font-size: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 8px;
            border-radius: 12px;
        }
        
        .security-item:last-child {
            margin-bottom: 0;
        }
        
        .security-item:hover {
            transform: translateX(8px);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .security-item i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, rgba(106, 127, 70, 0.4), rgba(255, 215, 0, 0.3));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(106, 127, 70, 0.3);
            transition: all 0.3s ease;
        }
        
        .security-item:hover i {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.5);
        }
        
        /* Enhanced verification container with glass morphism and organic shape */
        .verification-container {
            position: fixed;
            right: 100px;
            top: 50%;
            transform: translateY(-50%);
            width: 440px;
            background: var(--container-bg);
            backdrop-filter: blur(25px);
            border-radius: 45px 35px 45px 35px;
            padding: 45px 40px;
            box-shadow: 0 30px 80px var(--shadow-color), 
                        0 0 0 1px rgba(255, 255, 255, 0.25),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
            z-index: 10;
            opacity: 0;
            animation: slideInRight 1.2s ease forwards 1.5s;
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: all 0.6s ease;
            overflow: hidden;
            text-align: center;
        }
        
        .verification-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 215, 0, 0.05), transparent);
            animation: rotate-gradient 10s linear infinite;
        }
        
        @keyframes rotate-gradient {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .verification-container > * {
            position: relative;
            z-index: 1;
        }
        
        /* Enhanced logo with bounce animation */
        .verification-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .verification-logo img {
            width: 100px;
            height: auto;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.3)) drop-shadow(0 0 20px rgba(255, 215, 0, 0.2));
            animation: bounce 3s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .verification-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .verification-header h2 {
            color: var(--text-primary);
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 800;
            transition: color 0.3s ease;
            background: linear-gradient(135deg, var(--text-primary), var(--accent-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .verification-header p {
            color: var(--text-secondary);
            font-size: 15px;
            transition: color 0.3s ease;
            font-weight: 500;
            line-height: 1.6;
        }
        
        /* Enhanced shield icon */
        .shield-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #2C2416 0%, #3C342C 50%, #6A7F46 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 15px 40px rgba(60, 52, 44, 0.5), 0 0 30px rgba(255, 215, 0, 0.3);
            animation: pulse 3s infinite;
        }
        
        .shield-icon i {
            font-size: 50px;
            color: white;
        }
        
        /* Enhanced code input */
        .code-input-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin: 30px 0;
        }
        
        .code-input {
            width: 60px;
            height: 70px;
            font-size: 32px;
            text-align: center;
            border: 2px solid var(--border-color);
            border-radius: 15px;
            font-weight: bold;
            color: var(--text-primary);
            background: var(--container-bg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .code-input:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 5px rgba(106, 127, 70, 0.15), 0 8px 20px rgba(106, 127, 70, 0.1);
            transform: translateY(-3px);
        }
        
        /* Enhanced timer */
        .timer {
            background: linear-gradient(135deg, rgba(106, 127, 70, 0.15), rgba(106, 127, 70, 0.08));
            border: 2px solid rgba(106, 127, 70, 0.5);
            border-radius: 15px;
            padding: 15px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(106, 127, 70, 0.2);
        }
        
        .timer i {
            color: var(--accent-green);
            font-size: 20px;
        }
        
        .timer-text {
            color: var(--text-primary);
            font-weight: 700;
        }
        
        /* Enhanced button with gradient and ripple effect */
        .btn-primary {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #2C2416 0%, #3C342C 50%, #6A7F46 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(60, 52, 44, 0.5);
            position: relative;
            overflow: hidden;
            letter-spacing: 1px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            transform: translate(-50%, -50%);
            transition: width 0.8s, height 0.8s;
        }
        
        .btn-primary:hover::before {
            width: 400px;
            height: 400px;
        }
        
        .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(60, 52, 44, 0.6), 0 0 30px rgba(255, 215, 0, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(-2px);
        }
        
        .btn-outline {
            width: 100%;
            padding: 15px;
            background: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 15px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-outline:hover {
            border-color: var(--accent-green);
            color: var(--accent-green);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(106, 127, 70, 0.2);
        }
        
        /* Enhanced error message */
        .error-message {
            color: #dc3545;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.15), rgba(220, 53, 69, 0.08));
            border: 2px solid rgba(220, 53, 69, 0.5);
            padding: 14px 18px;
            border-radius: 15px;
            margin-bottom: 22px;
            font-size: 14px;
            animation: shake 0.6s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-12px); }
            75% { transform: translateX(12px); }
        }
        
        /* Enhanced success message */
        .success-message {
            color: #28a745;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.15), rgba(40, 167, 69, 0.08));
            border: 2px solid rgba(40, 167, 69, 0.5);
            padding: 14px 18px;
            border-radius: 15px;
            margin-bottom: 22px;
            font-size: 14px;
            animation: slideDown 0.6s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .back-link {
            margin-top: 20px;
            color: var(--text-secondary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .back-link:hover {
            color: var(--accent-green);
        }
        
        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* Enhanced loading screen */
        .loading-screen {
            display: flex;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2C2416 0%, #3C342C 50%, #6A7F46 100%);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .loading-logo {
            width: 200px;
            height: 200px;
            margin-bottom: 50px;
            animation: rotate 2.5s linear infinite;
            filter: drop-shadow(0 15px 40px rgba(0,0,0,0.6)) drop-shadow(0 0 40px rgba(255, 215, 0, 0.4));
        }
        
        .loading-text {
            color: #FFD700;
            font-size: 56px;
            font-weight: 900;
            margin-bottom: 18px;
            text-align: center;
            letter-spacing: 6px;
            text-shadow: 0 6px 25px rgba(0,0,0,0.5), 0 0 40px rgba(255, 215, 0, 0.5);
            animation: glow 2.5s ease-in-out infinite;
        }
        
        @keyframes glow {
            0%, 100% { text-shadow: 0 6px 25px rgba(0,0,0,0.5), 0 0 40px rgba(255, 215, 0, 0.5); }
            50% { text-shadow: 0 6px 25px rgba(0,0,0,0.5), 0 0 60px rgba(255, 215, 0, 0.9); }
        }
        
        .loading-subtitle {
            color: rgba(255, 255, 255, 0.95);
            font-size: 22px;
            margin-bottom: 60px;
            font-weight: 700;
            text-shadow: 0 3px 15px rgba(0,0,0,0.4);
            letter-spacing: 2px;
        }
        
        .loading-spinner {
            display: flex;
            gap: 18px;
            margin-bottom: 25px;
        }
        
        .loading-dot {
            width: 20px;
            height: 20px;
            background: #FFD700;
            border-radius: 50%;
            animation: bounce-dot 1.6s infinite ease-in-out both;
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.6);
        }
        
        .loading-dot:nth-child(1) { animation-delay: -0.32s; }
        .loading-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes bounce-dot {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.2); }
        }
        
        .loading-footer {
            position: absolute;
            bottom: 50px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-60px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translate(60px, -50%);
            }
            to {
                opacity: 1;
                transform: translate(0, -50%);
            }
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: translateY(-50%) scale(0.85);
            }
            to {
                opacity: 1;
                transform: translateY(-50%) scale(1);
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-60px) scale(0.85);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        @media (max-width: 1400px) {
            .logo-left {
                left: 60px;
            }
            
            .verification-container {
                right: 60px;
            }
        }
        
        @media (max-width: 1200px) {
            .logo-left {
                left: 40px;
            }
            
            .logo-left img {
                width: 200px;
            }
            
            .logo-left h1 {
                font-size: 38px;
            }
            
            .logo-left .tagline {
                font-size: 18px;
            }
            
            .verification-container {
                right: 40px;
                width: 400px;
            }
        }
        
        @media (max-width: 768px) {
            .logo-left {
                top: 100px;
                left: 50%;
                transform: translateX(-50%);
            }
            
            .logo-left img {
                width: 160px;
            }
            
            .logo-left h1 {
                font-size: 32px;
            }
            
            .logo-left .tagline {
                font-size: 16px;
            }
            
            .security-features {
                display: none;
            }
            
            .verification-container {
                right: 50%;
                transform: translate(50%, -50%);
                width: 90%;
                max-width: 420px;
                margin-top: 200px;
            }
            
            .watermark-logo {
                width: 600px;
                height: 600px;
            }
            
            .code-input {
                width: 45px;
                height: 55px;
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    
    <div class="bg-decoration bg-decoration-1"></div>
    <div class="bg-decoration bg-decoration-2"></div>
    <div class="bg-decoration bg-decoration-3"></div>
    <div class="bg-decoration bg-decoration-4"></div>
    
    <img src="img/MainLogo.svg" alt="JunkValue Watermark" class="watermark-logo">
    
    <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
        <i class="fas fa-moon"></i>
    </button>
    
    
    
   
    <div class="logo-left">
        <img src="img/MainLogo.svg" alt="JunkValue Logo">
        <h1>JUNKVALUE</h1>
        <p class="tagline">Turn Your Junk Into Value</p>
        
        <div class="security-features">
            <h3><i class="fas fa-shield-alt"></i> Security Features</h3>
            <div class="security-item">
                <i class="fas fa-lock"></i>
                <span>Password Hash</span>
            </div>
            <div class="security-item">
                <i class="fas fa-user-shield"></i>
                <span>Two-Factor Authentication</span>
            </div>
            <div class="security-item">
                <i class="fas fa-database"></i>
                <span>Secure Data Storage</span>
            </div>
            <div class="security-item">
                <i class="fas fa-check-circle"></i>
                <span>reCAPTCHA Protection</span>
            </div>
        </div>
    </div>
    
     
    <div class="verification-container">
        <div class="verification-logo">
            <img src="img/MainLogo.svg" alt="JunkValue">
        </div>
        
        <div class="verification-header">
            <h2>Two-Factor Authentication</h2>
            <p>We've sent a 6-digit verification code to your email address. Please enter it below to complete your login.</p>
        </div>
        
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
        
        <div class="shield-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
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
        
        <div class="footer">
            © 2025 junkvalue
        </div>
    </div>
    

    <div class="loading-screen" id="loadingScreen">
        <img src="img/MainLogo.svg" alt="JunkValue" class="loading-logo">
        <div class="loading-text">JUNKVALUE</div>
        <div class="loading-subtitle">Turn Your Junk Into Value</div>
        <div class="loading-spinner">
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
            <div class="loading-dot"></div>
        </div>
        <div class="loading-footer">junkvalue 2025</div>
    </div>
    
    <script>
        // Show loading screen on page load, then hide after 1.5 seconds
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loadingScreen').style.display = 'none';
            }, 1500);
        });
        
        // Dark mode toggle functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        const darkModeIcon = darkModeToggle.querySelector('i');
        
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            darkModeIcon.classList.remove('fa-moon');
            darkModeIcon.classList.add('fa-sun');
        }
        
        darkModeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                darkModeIcon.classList.remove('fa-moon');
                darkModeIcon.classList.add('fa-sun');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                darkModeIcon.classList.remove('fa-sun');
                darkModeIcon.classList.add('fa-moon');
                localStorage.setItem('darkMode', 'disabled');
            }
        });
        
        // Auto-focus and auto-advance code inputs
        const inputs = document.querySelectorAll('.code-input');
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Only allow single digit
                if (e.target.value.length > 1) {
                    e.target.value = e.target.value.slice(0, 1);
                }
                
                // Auto-advance to next input
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                
                // Update hidden field with combined code
                let fullCode = '';
                inputs.forEach(inp => fullCode += inp.value);
                document.getElementById('fullCode').value = fullCode;
            });
            
            input.addEventListener('keydown', (e) => {
                // Go back on backspace if current input is empty
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
            
            // Prevent paste of non-numeric content
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text');
                const numbers = pastedData.replace(/\D/g, '').slice(0, 6);
                
                // Fill inputs with pasted numbers
                for (let i = 0; i < numbers.length && (index + i) < inputs.length; i++) {
                    inputs[index + i].value = numbers[i];
                }
                
                // Update hidden field
                let fullCode = '';
                inputs.forEach(inp => fullCode += inp.value);
                document.getElementById('fullCode').value = fullCode;
                
                // Focus appropriate input
                const nextIndex = Math.min(index + numbers.length, inputs.length - 1);
                inputs[nextIndex].focus();
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
        
        // Enhanced input animations
        const codeInputs = document.querySelectorAll('.code-input');
        codeInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'scale(1.05)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>