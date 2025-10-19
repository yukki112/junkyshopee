<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);
require_once 'db_connection.php';

$errors = [];
$success = false;
$validToken = false;
$showForm = false;

// Check if token exists in URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Validate token format (64-character hex string)
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        $errors[] = "Invalid token format";
    } else {
        // Check if token exists and is not expired
        $currentDateTime = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_token_expires > ?");
        $stmt->bind_param("ss", $token, $currentDateTime);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $userId = $user['id'];
            $validToken = true;
            $showForm = true;
            
            // Process password reset form
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'];
                $confirmPassword = $_POST['confirm_password'];
                
                // Validate inputs
                if (empty($password)) {
                    $errors[] = "Password is required";
                } elseif (strlen($password) < 8) {
                    $errors[] = "Password must be at least 8 characters";
                } elseif (!preg_match('/[A-Z]/', $password)) {
                    $errors[] = "Password must contain at least one uppercase letter";
                } elseif (!preg_match('/[a-z]/', $password)) {
                    $errors[] = "Password must contain at least one lowercase letter";
                } elseif (!preg_match('/[0-9]/', $password)) {
                    $errors[] = "Password must contain at least one number";
                }
                
                if ($password !== $confirmPassword) {
                    $errors[] = "Passwords do not match";
                }
                
                if (empty($errors)) {
                    // Update password and clear reset token
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                    $updateStmt->bind_param("si", $hashedPassword, $userId);
                    
                    if ($updateStmt->execute()) {
                        $success = true;
                        $showForm = false;
                        
                        // Invalidate all existing sessions for this user
                        session_destroy();
                    } else {
                        $errors[] = "Failed to reset password. Please try again.";
                    }
                    $updateStmt->close();
                }
            }
        } else {
            $errors[] = "Invalid or expired reset token";
        }
        $stmt->close();
    }
} else {
    $errors[] = "No reset token provided";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - JunkValue</title>
    <link rel="icon" type="image/png" href="../img/MainLogo.svg">
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
        
        /* Enhanced reset container with glass morphism and organic shape */
        .reset-container {
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
        }
        
        .reset-container::before {
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
        
        .reset-container > * {
            position: relative;
            z-index: 1;
        }
        
        /* Enhanced logo with bounce animation */
        .reset-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .reset-logo img {
            width: 100px;
            height: auto;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.3)) drop-shadow(0 0 20px rgba(255, 215, 0, 0.2));
            animation: bounce 3s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-header h2 {
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
        
        .reset-header p {
            color: var(--text-secondary);
            font-size: 15px;
            transition: color 0.3s ease;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: var(--text-primary);
            font-size: 14px;
            transition: color 0.3s ease;
            letter-spacing: 0.5px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid var(--border-color);
            border-radius: 15px;
            font-size: 15px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--container-bg);
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 5px rgba(106, 127, 70, 0.15), 0 8px 20px rgba(106, 127, 70, 0.1);
            transform: translateY(-3px);
        }
        
        .form-group input:focus + .input-wrapper i {
            color: var(--accent-green);
            transform: translateY(-50%) scale(1.15);
        }
        
        .password-toggle {
            position: absolute;
            right: 55px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 17px;
            transition: all 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--accent-green);
            transform: translateY(-50%) scale(1.25);
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
        
        .info-message {
            color: #0c5460;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.15), rgba(13, 110, 253, 0.08));
            border: 2px solid rgba(13, 110, 253, 0.5);
            padding: 14px 18px;
            border-radius: 15px;
            margin-bottom: 22px;
            font-size: 14px;
            animation: slideDown 0.6s ease;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
            font-weight: 600;
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
            
            .reset-container {
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
            
            .reset-container {
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
            
            .reset-container {
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
        }
    </style>
</head>
<body>
    
    <div class="bg-decoration bg-decoration-1"></div>
    <div class="bg-decoration bg-decoration-2"></div>
    <div class="bg-decoration bg-decoration-3"></div>
    <div class="bg-decoration bg-decoration-4"></div>
    
    <img src="../img/MainLogo.svg" alt="JunkValue Watermark" class="watermark-logo">
    
    <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
        <i class="fas fa-moon"></i>
    </button>
    

    
   
    <div class="logo-left">
        <img src="../img/MainLogo.svg" alt="JunkValue Logo">
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
    
     
    <div class="reset-container">
        <div class="reset-logo">
            <img src="../img/MainLogo.svg" alt="JunkValue">
        </div>
        
        <div class="reset-header">
            <h2>Reset Your Password</h2>
            <p>Create a new secure password</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <p><i class="fas fa-check-circle"></i> Your password has been reset successfully. You can now <a href="login.php">login</a> with your new password.</p>
            </div>
        <?php elseif ($showForm): ?>
            <form method="POST" action="" id="resetForm">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter new password" required minlength="8">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8">
                        <button type="button" class="password-toggle" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
        <?php else: ?>
            <div class="info-message">
                <p><i class="fas fa-info-circle"></i> Invalid or expired password reset link. Please request a new password reset link from the <a href="Login.php">login page</a>.</p>
            </div>
            <a href="Login.php" class="btn btn-primary">Back to Login</a>
        <?php endif; ?>
        
        <div class="footer">
            © 2025 junkvalue
        </div>
    </div>
    

    <div class="loading-screen" id="loadingScreen">
        <img src="../img/MainLogo.svg" alt="JunkValue" class="loading-logo">
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
        
        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }
        
        if (toggleConfirmPassword && confirmPasswordInput) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }
        
        // Client-side password validation
        const form = document.getElementById('resetForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long');
                    return false;
                }
                
                if (!/[A-Z]/.test(password)) {
                    e.preventDefault();
                    alert('Password must contain at least one uppercase letter');
                    return false;
                }
                
                if (!/[a-z]/.test(password)) {
                    e.preventDefault();
                    alert('Password must contain at least one lowercase letter');
                    return false;
                }
                
                if (!/[0-9]/.test(password)) {
                    e.preventDefault();
                    alert('Password must contain at least one number');
                    return false;
                }
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match');
                    return false;
                }
                
                return true;
            });
        }
        
        // Enhanced input animations
        const inputs = document.querySelectorAll('.form-group input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>