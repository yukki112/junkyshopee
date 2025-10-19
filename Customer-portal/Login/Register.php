<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
    'use_cookies' => true,
    'use_only_cookies' => true
]);

header('Access-Control-Allow-Origin: https://www.facebook.com');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connection.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('RECAPTCHA_SITE_KEY', '6LeYjuorAAAAAPbR8cTtzeaLz05h_yRz2sEfsqfO');
define('RECAPTCHA_SECRET_KEY', '6LeYjuorAAAAAGS0RH6BiwKoS-muwQyzdzFS121K');

$errors = [];
$success = false;
$verificationSent = false;
$firstName = $lastName = $username = $email = $phone = $address = $userType = $referralCode = '';
$verificationMethod = '';
$emailError = false;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid form submission";
    } else {
        // Check if this is a verification code submission
        if (isset($_POST['verify_code'])) {
            $submittedCode = implode('', $_POST['verification_code']);
            $storedCode = $_SESSION['verification_code'];
            $verificationMethod = $_SESSION['verification_method'];
            
            if ($submittedCode === $storedCode) {
                // Code matches - complete registration
                $firstName = $_SESSION['reg_data']['firstName'];
                $lastName = $_SESSION['reg_data']['lastName'];
                $username = $_SESSION['reg_data']['username'];
                $email = $_SESSION['reg_data']['email'];
                $phone = $_SESSION['reg_data']['phone'];
                $address = $_SESSION['reg_data']['address'];
                $password = $_SESSION['reg_data']['password'];
                $userType = $_SESSION['reg_data']['userType'];
                $referralCode = $_SESSION['reg_data']['referralCode'];
                $agreedTerms = $_SESSION['reg_data']['agreedTerms'];
                $referrerId = $_SESSION['reg_data']['referrerId'] ?? null;
                $userReferralCode = $_SESSION['reg_data']['userReferralCode'];
                
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user into database
                $sql = "INSERT INTO users (
                    first_name, 
                    last_name,
                    username,
                    email, 
                    phone, 
                    address, 
                    password_hash, 
                    user_type,
                    agreed_terms,
                    referral_code,
                    is_verified" . 
                    ($referrerId ? ", referred_by" : "") . "
                ) VALUES (
                    '$firstName',
                    '$lastName',
                    '$username',
                    '$email',
                    '$phone',
                    '$address',
                    '$passwordHash',
                    '$userType',
                    $agreedTerms,
                    '$userReferralCode',
                    1" .
                    ($referrerId ? ", $referrerId" : "") . "
                )";
                
                if (mysqli_query($conn, $sql)) {
                    $userId = mysqli_insert_id($conn);
                    
                    // If referred by someone, award points to both users
                    if ($referrerId) {
                        // Award 100 points to the referrer (person who owns the code)
                        $pointsToReferrer = 100;
                        mysqli_query($conn, "UPDATE users SET loyalty_points = loyalty_points + $pointsToReferrer WHERE id = $referrerId");
                        
                        // Award 50 points to the referred user (person who used the code)
                        $pointsToReferred = 50;
                        mysqli_query($conn, "UPDATE users SET loyalty_points = loyalty_points + $pointsToReferred WHERE id = $userId");
                        
                        // Log the referral transaction
                        mysqli_query($conn, "INSERT INTO referral_logs (referrer_id, referred_id, points_to_referrer, points_to_referred) 
                                           VALUES ($referrerId, $userId, $pointsToReferrer, $pointsToReferred)");
                    }
                    
                    $success = true;
                    // Clear session data
                    unset($_SESSION['verification_code']);
                    unset($_SESSION['verification_method']);
                    unset($_SESSION['reg_data']);
                    unset($_SESSION['verification_pending']);
                    
                    // Redirect to login after 3 seconds
                    header("Refresh: 3; url=Login.php");
                } else {
                    $errors[] = "Registration failed: " . mysqli_error($conn);
                }
            } else {
                $_SESSION['verification_error'] = "Invalid verification code. Please try again.";
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            }
        }
        // Check if this is a verification method selection
        elseif (isset($_POST['verification_method'])) {
            // Get all the registration data from session
            $regData = $_SESSION['reg_data'];
            $verificationMethod = $_POST['verification_method'];
            
            // Generate 6-digit verification code
            $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['verification_code'] = $verificationCode;
            $_SESSION['verification_method'] = $verificationMethod;
            $verificationSent = true;
            
            if ($verificationMethod === 'email') {
                // Send verification email
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'Stephenviray12@gmail.com';
                    $mail->Password   = 'bubr nckn tgqf lvus';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    
                    // Recipients
                    $mail->setFrom('noreply@junkvalue.com', 'JunkValue');
                    $mail->addAddress($regData['email'], $regData['firstName']);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your JunkValue Verification Code';
                    
                    // HTML email body
                    $mail->Body = '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #6A7F46; border: 1px solid #6A7F46; border-radius: 5px; padding: 20px;">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div style="display: inline-block; margin-bottom: 15px;">
                                <span style="font-size: 30px; font-weight: bold; color: #3C342C; vertical-align: middle; margin-left: 10px;">JunkValue</span>
                            </div>
                            <h2 style="color: #3C342C; margin-bottom: 5px;">One-Time Password (OTP) for Secure Login</h2>
                        </div>
                        
                        <div style="background-color: #6A7F46; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                            <h3>Hello '.$regData['firstName'].',</h3>
                            
                            <h3>Your one-time password (OTP) for secure login is</h3>
                            
                            <div style="text-align: center; margin: 25px 0;">
                                <span style="font-size: 28px; font-weight: bold; letter-spacing: 2px; color: #3C342C; background-color: #f0f0f0; padding: 10px 20px; border-radius: 5px; display: inline-block;">
                                    '.$verificationCode.'
                                </span>
                            </div>
                            
                            <h3>Please do not share it with anyone for security reasons.</h3>
                            
                            <h3>This code will expire in 10 minutes.</h3>
                        </div>
                        
                        <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                            <p style="margin-bottom: 15px;">
                                <strong>If you did not request this code or if you have any concerns,</strong> 
                                please contact our support team immediately.
                            </p>
                            
                            <p style="margin-bottom: 5px;">
                                <a href="https://www.facebook.com/s2.xwoo" style="color: #3C342C; text-decoration: none; font-weight: bold;">
                                    Contact our support team
                                </a> or call us at <strong>0998 431 9585</strong>
                            </p>
                        </div>
                        
                        <div style="margin-top: 30px; text-align: center; color: #3C342C; font-size: 14px;">
                            <p>Thank you,</p>
                            <h2><strong>JunkValue Team</strong></h2>
                            <p><a href="#" style="color: #3C342C; text-decoration: none;">junkvalue.com</a></p>
                        </div>
                    </div>
                    ';
                    
                    // Plain text version for non-HTML email clients
                    $mail->AltBody = "Hello ".$regData['firstName'].",\n\n"
                                   . "Your JunkValue verification code is: ".$verificationCode."\n\n"
                                   . "This code will expire in 10 minutes.\n\n"
                                   . "If you didn't request this, please ignore this email.\n\n"
                                   . "Best regards,\nThe JunkValue Team\njunkvalue.com";
                    
                    $mail->send();
                } catch (Exception $e) {
                    $errors[] = "Verification email could not be sent. Error: {$mail->ErrorInfo}";
                    $verificationSent = false;
                    $emailError = true;
                }
            }
        }
        // Check if user wants to go back to registration
        elseif (isset($_POST['back_to_register'])) {
            // Restore all registration data from session
            $regData = $_SESSION['reg_data'];
            $firstName = $regData['firstName'];
            $lastName = $regData['lastName'];
            $username = $regData['username'];
            $email = $regData['email'];
            $phone = $regData['phone'];
            $address = $regData['address'];
            $userType = $regData['userType'];
            $referralCode = $regData['referralCode'];
            
            // Clear verification session data
            unset($_SESSION['verification_pending']);
            unset($_SESSION['verification_code']);
            unset($_SESSION['verification_method']);
        }
        // Otherwise process the initial registration form
        else {
            // Validate reCAPTCHA
            if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
                $recaptchaResponse = $_POST['g-recaptcha-response'];
                $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
                $recaptchaData = [
                    'secret' => RECAPTCHA_SECRET_KEY,
                    'response' => $recaptchaResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ];
                
                $recaptchaOptions = [
                    'http' => [
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => 'POST',
                        'content' => http_build_query($recaptchaData)
                    ]
                ];
                
                $recaptchaContext = stream_context_create($recaptchaOptions);
                $recaptchaResult = file_get_contents($recaptchaUrl, false, $recaptchaContext);
                $recaptchaJson = json_decode($recaptchaResult);
                
                if (!$recaptchaJson->success) {
                    $errors[] = "reCAPTCHA verification failed. Please try again.";
                }
            } else {
                $_SESSION['captcha_error'] = true;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            
            if (empty($errors)) {
                // Sanitize and validate inputs
                $firstName = mysqli_real_escape_string($conn, trim($_POST['firstName']));
                $lastName = mysqli_real_escape_string($conn, trim($_POST['lastName']));
                $username = mysqli_real_escape_string($conn, trim($_POST['username']));
                $email = mysqli_real_escape_string($conn, trim($_POST['email']));
                $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
                $address = mysqli_real_escape_string($conn, trim($_POST['address']));
                $password = $_POST['password'];
                $confirmPassword = $_POST['confirmPassword'];
                $userType = mysqli_real_escape_string($conn, $_POST['userType']);
                $referralCode = isset($_POST['referralCode']) ? mysqli_real_escape_string($conn, trim($_POST['referralCode'])) : '';
                $agreedTerms = isset($_POST['agreeTerms']) ? 1 : 0;
                
                // Validate inputs
                if (empty($firstName)) $errors[] = "First name is required";
                if (empty($lastName)) $errors[] = "Last name is required";
                if (empty($username)) $errors[] = "Username is required";
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = "Username can only contain letters, numbers and underscores";
                if (strlen($username) < 4) $errors[] = "Username must be at least 4 characters";
                if (empty($email)) $errors[] = "Email is required";
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
                if (empty($phone)) $errors[] = "Phone number is required";
                if (!preg_match('/^09[0-9]{9}$/', $phone)) $errors[] = "Phone number must be 11 digits starting with 09";
                if (empty($address)) $errors[] = "Address is required";
                if (empty($password)) $errors[] = "Password is required";
                if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";
                if ($password !== $confirmPassword) $errors[] = "Passwords do not match";
                if (empty($userType)) $errors[] = "User type is required";
                if (!$agreedTerms) $errors[] = "You must agree to the terms and conditions";
                
                // Check if email already exists
                $emailCheck = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
                if (mysqli_num_rows($emailCheck) > 0) {
                    $errors[] = "Email already registered";
                }
                
                // Check if username already exists
                $usernameCheck = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
                if (mysqli_num_rows($usernameCheck) > 0) {
                    $errors[] = "Username already taken";
                }
                
                // Validate referral code if provided
                $referrerId = null;
                if (!empty($referralCode)) {
                    $referralCheck = mysqli_query($conn, "SELECT id FROM users WHERE referral_code = '$referralCode'");
                    if (mysqli_num_rows($referralCheck) == 0) {
                        $errors[] = "Invalid referral code";
                    } else {
                        $referrer = mysqli_fetch_assoc($referralCheck);
                        $referrerId = $referrer['id'];
                    }
                }
                
                // Generate unique referral code for new user
                $userReferralCode = generateRandomReferralCode(7);
                
                // Ensure the generated referral code is unique
                $codeCheck = true;
                $attempts = 0;
                while ($codeCheck && $attempts < 5) {
                    $codeCheckQuery = mysqli_query($conn, "SELECT id FROM users WHERE referral_code = '$userReferralCode'");
                    if (mysqli_num_rows($codeCheckQuery) > 0) {
                        $userReferralCode = generateRandomReferralCode(7);
                        $attempts++;
                    } else {
                        $codeCheck = false;
                    }
                }
                
                if ($codeCheck) {
                    $errors[] = "Failed to generate a unique referral code. Please try again.";
                }
                
                // If no errors, store data in session and proceed to verification
                if (empty($errors)) {
                    // Store all registration data in session
                    $_SESSION['reg_data'] = [
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'username' => $username,
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $address,
                        'password' => $password,
                        'userType' => $userType,
                        'referralCode' => $referralCode,
                        'agreedTerms' => $agreedTerms,
                        'referrerId' => $referrerId,
                        'userReferralCode' => $userReferralCode
                    ];
                    
                    $_SESSION['verification_pending'] = true;
                    $verificationSent = false; // User needs to choose verification method
                }
            }
        }
    }
}

// Function to generate random alphanumeric referral code
function generateRandomReferralCode($length = 7) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$pageTitle = 'JunkValue - Register';
if (isset($_GET['error'])) {
    $pageTitle = 'JunkValue - ' . htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
        
        /* Enhanced register container with glass morphism and organic shape */
        .register-container {
            position: fixed;
            right: 100px;
            top: 50%;
            transform: translateY(-50%);
            width: 500px;
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
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .register-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .register-container::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .register-container::-webkit-scrollbar-thumb {
            background: var(--accent-green);
            border-radius: 10px;
        }
        
        .register-container::before {
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
        
        .register-container > * {
            position: relative;
            z-index: 1;
        }
        
        /* Enhanced logo with bounce animation */
        .register-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .register-logo img {
            width: 100px;
            height: auto;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.3)) drop-shadow(0 0 20px rgba(255, 215, 0, 0.2));
            animation: bounce 3s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
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
        
        .register-header p {
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
        
        .form-group input, .form-group select {
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
        
        .form-group input:focus, .form-group select:focus {
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
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 22px;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .center-field {
            display: flex;
            justify-content: center;
            margin-bottom: 22px;
        }
        
        .center-field .form-group {
            width: 80%;
            text-align: center;
        }
        
        .recaptcha-container {
            margin-bottom: 22px;
            display: flex;
            justify-content: center;
            transform: scale(0.92);
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
        
        .terms {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 25px 0;
            padding: 15px;
            background-color: rgba(106, 127, 70, 0.08);
            border-radius: 10px;
            border-left: 4px solid var(--accent-green);
        }
        
        .terms input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            flex-shrink: 0;
            cursor: pointer;
        }
        
        .terms label {
            font-size: 14px;
            color: var(--text-secondary);
            text-align: left;
            line-height: 1.5;
            margin: 0;
            cursor: pointer;
            flex: 1;
        }
        
        .terms a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 600;
        }
        
        .terms a:hover {
            text-decoration: underline;
            color: var(--text-primary);
        }
        
        .referral-section {
            margin-top: 15px;
            padding: 15px;
            background-color: rgba(106, 127, 70, 0.08);
            border-radius: 10px;
            border-left: 4px solid var(--accent-green);
        }
        
        .referral-section p {
            margin-bottom: 10px;
            font-size: 14px;
            color: var(--text-secondary);
            text-align: center;
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: var(--text-secondary);
            font-size: 14px;
            transition: color 0.3s ease;
            font-weight: 500;
        }
        
        .login-link a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 800;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .login-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-green);
            transition: width 0.3s ease;
        }
        
        .login-link a:hover::after {
            width: 100%;
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
        
        /* Enhanced modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.75);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.4s ease;
        }
        
        .modal-content {
            background: var(--container-bg);
            margin: 8% auto;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 30px 80px var(--shadow-color), 0 0 0 1px rgba(255, 255, 255, 0.2);
            width: 90%;
            max-width: 500px;
            animation: modalSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        
        .modal-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 35px;
            animation: pulse 2.5s infinite;
            box-shadow: 0 10px 30px rgba(220, 53, 69, 0.4);
        }
        
        .modal-icon i {
            font-size: 60px;
            color: white;
        }
        
        .modal-content h2 {
            color: var(--text-primary);
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: 800;
        }
        
        .modal-content p {
            color: var(--text-secondary);
            margin-bottom: 35px;
            line-height: 1.8;
            font-size: 16px;
            font-weight: 500;
        }
        
        .modal-btn {
            background: linear-gradient(135deg, #2C2416 0%, #3C342C 50%, #6A7F46 100%);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 16px;
            box-shadow: 0 6px 20px rgba(60, 52, 44, 0.4);
        }
        
        .modal-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(60, 52, 44, 0.6);
        }
        
        /* Verification Modal Styles */
        .verification-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.75);
            backdrop-filter: blur(10px);
        }
        
        .verification-modal-content {
            background: var(--container-bg);
            margin: 8% auto;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 30px 80px var(--shadow-color), 0 0 0 1px rgba(255, 255, 255, 0.2);
            width: 90%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        
        .verification-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .verification-modal-header h2 {
            color: var(--text-primary);
            font-size: 26px;
            font-weight: 800;
        }
        
        .close {
            color: var(--text-secondary);
            font-size: 36px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.4s ease;
            line-height: 1;
        }
        
        .close:hover {
            color: var(--accent-green);
            transform: rotate(90deg) scale(1.1);
        }
        
        .verification-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 18px;
            padding-top: 30px;
            border-top: 2px solid var(--border-color);
            margin-top: 30px;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            padding: 14px 30px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 15px;
        }
        
        .btn-outline:hover {
            border-color: var(--accent-green);
            color: var(--accent-green);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(106, 127, 70, 0.2);
        }
        
        /* Verification method selection */
        .verification-method {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .verification-option {
            flex: 1;
            text-align: center;
            padding: 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--container-bg);
        }
        
        .verification-option:hover {
            border-color: var(--accent-green);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .verification-option.selected {
            border-color: var(--accent-green);
            background-color: rgba(106, 127, 70, 0.05);
            box-shadow: 0 5px 15px rgba(106, 127, 70, 0.2);
        }
        
        .verification-option i {
            font-size: 32px;
            margin-bottom: 12px;
            color: var(--accent-green);
        }
        
        .verification-option .option-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .verification-option .option-desc {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.4;
        }
        
        /* Verification code input */
        .verification-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .verification-input input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.3s;
            background: var(--container-bg);
            color: var(--text-primary);
        }
        
        .verification-input input:focus {
            border-color: var(--accent-green);
            outline: none;
            box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.2);
        }
        
        .resend-code {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .resend-code a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 600;
        }
        
        .resend-code a:hover {
            text-decoration: underline;
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
        
        /* Password strength indicator */
        .password-strength {
            height: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            background-color: #dc3545;
            transition: all 0.3s;
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
            
            .register-container {
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
            
            .register-container {
                right: 40px;
                width: 450px;
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
            
            .register-container {
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
            
            .form-row {
                flex-direction: column;
                gap: 22px;
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
    
    <a href="../../index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Home Page
    </a>
    
   
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
    
     
    <div class="register-container">
        <div class="register-logo">
            <img src="img/MainLogo.svg" alt="JunkValue">
        </div>
        
        <div class="register-header">
            <h2>Create Account</h2>
            <p>Join JunkValue and start turning your scrap into cash</p>
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
                <p><i class="fas fa-check-circle"></i> Account verified successfully! Redirecting to login page...</p>
            </div>
        <?php endif; ?>
        
        <?php if (!isset($_SESSION['verification_pending']) || !$_SESSION['verification_pending']): ?>
            <form method="POST" action="" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="firstName" name="firstName" placeholder="Juan" value="<?php echo htmlspecialchars($firstName); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="lastName" name="lastName" placeholder="Dela Cruz" value="<?php echo htmlspecialchars($lastName); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-at"></i>
                        <input type="text" id="username" name="username" placeholder="juan123" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <small style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 5px;">4-20 characters, letters, numbers and underscores only</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="juan.delacruz@example.com" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="input-wrapper">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" placeholder="09123456789" value="<?php echo htmlspecialchars($phone); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="center-field">
                    <div class="form-group">
                        <label for="address">Pickup Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="address" name="address" placeholder="123 Main St, Barangay San Jose" value="<?php echo htmlspecialchars($address); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Create a password" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="center-field">
                    <div class="form-group">
                        <label for="userType">I am a:</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-tag"></i>
                            <select id="userType" name="userType" required>
                                <option value="">Select account type</option>
                                <option value="individual" <?php echo ($userType === 'individual') ? 'selected' : ''; ?>>Individual Recycler</option>
                                <option value="business" <?php echo ($userType === 'business') ? 'selected' : ''; ?>>Business/Junk Shop</option>
                                <option value="collector" <?php echo ($userType === 'collector') ? 'selected' : ''; ?>>Scrap Collector</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="referral-section">
                    <p>Have a referral code? (Optional)</p>
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-gift"></i>
                            <input type="text" id="referralCode" name="referralCode" placeholder="Enter referral code" value="<?php echo htmlspecialchars($referralCode); ?>">
                        </div>
                        <small style="color: var(--text-secondary); font-size: 12px; display: block; margin-top: 5px;">Both you and the referrer will earn bonus points!</small>
                    </div>
                </div>
          
                
                <div class="terms">
                    <input type="checkbox" id="agreeTerms" name="agreeTerms" required <?php echo isset($_POST['agreeTerms']) ? 'checked' : ''; ?>>
                    <label for="agreeTerms">
                        I agree to the <a href="../../terms-of-service.html">Terms of Service</a> and <a href="../../privacy-policy.html">Privacy Policy</a>. 
                        I consent to receive communications about my account.
                    </label>
                </div>

<div class="recaptcha-container">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="Login.php">Sign in</a>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            © 2025 junkvalue
        </div>
    </div>
    

    <div id="captchaModal" class="modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-check-square"></i>
            </div>
            <h2>Verification Required</h2>
            <p><strong>Please complete the reCAPTCHA challenge before registering.</strong></p>
            <p style="font-size: 14px; margin-top: 15px;">
                <i class="fas fa-info-circle"></i> Instructions:<br>
                1. Check the "I'm not a robot" box<br>
                2. Complete any image challenges if prompted<br>
                3. Click the Create Account button again
            </p>
            <button class="modal-btn" onclick="closeCaptchaModal()">Got it!</button>
        </div>
    </div>
    
    <!-- Verification Modal -->
    <?php if (isset($_SESSION['verification_pending']) && $_SESSION['verification_pending']): ?>
        <div class="verification-modal" id="verificationModal" style="display: flex;">
            <div class="verification-modal-content">
                <div class="verification-modal-header">
                    <h2><i class="fas fa-shield-alt" style="margin-right: 10px;"></i> Verify Your Account</h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <?php if (isset($_SESSION['verification_error'])): ?>
                        <div class="error-message" style="display: block; margin-bottom: 20px;">
                            <p><?php echo htmlspecialchars($_SESSION['verification_error']); ?></p>
                            <?php unset($_SESSION['verification_error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$verificationSent): ?>
                        <div style="text-align: center; margin-bottom: 25px; padding: 15px; background: rgba(106, 127, 70, 0.08); border-radius: 10px; border-left: 4px solid var(--accent-green);">
                            <p style="margin: 0; color: var(--text-secondary); font-size: 14px; line-height: 1.5;">
                                To complete your registration, please verify your account.
                            </p>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="verification-method">
                                <label class="verification-option" onclick="selectMethod(this, 'email')">
                                    <input type="radio" name="verification_method" value="email" style="display: none;">
                                    <i class="fas fa-envelope"></i>
                                    <div class="option-title">Email Verification</div>
                                    <div class="option-desc">We'll send a 6-digit code to your email address</div>
                                </label>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                                <button type="submit" class="btn-primary" id="sendCodeBtn" disabled>Send Verification Code</button>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="back_to_register" value="1">
                                    <button type="submit" class="btn-outline">Back to Registration</button>
                                </form>
                            </div>
                        </form>
                    <?php else: ?>
                        <?php if ($emailError): ?>
                            <div class="error-message" style="display: block; margin-bottom: 20px;">
                                <p>Failed to send verification email. Please check your email address or try again.</p>
                            </div>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="verification-method">
                                    <label class="verification-option selected">
                                        <i class="fas fa-envelope"></i>
                                        <div class="option-title">Email Verification</div>
                                        <div class="option-desc">We'll send a 6-digit code to your email address</div>
                                    </label>
                                </div>
                                
                                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                                    <button type="submit" class="btn-primary">Resend Email Code</button>
                                    
                                    <form method="POST" action="">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="back_to_register" value="1">
                                        <button type="submit" class="btn-outline">Back to Registration</button>
                                    </form>
                                </div>
                            </form>
                        <?php else: ?>
                            <div style="text-align: center; margin-bottom: 25px; padding: 15px; background: rgba(106, 127, 70, 0.08); border-radius: 10px; border-left: 4px solid var(--accent-green);">
                                <p style="margin: 0; color: var(--text-secondary); font-size: 14px; line-height: 1.5;">
                                    We sent a 6-digit verification code to your email. Please enter it below to complete your registration:
                                </p>
                            </div>
                            
                            <form method="POST" action="" id="verificationForm">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="verification-input">
                                    <input type="text" name="verification_code[]" maxlength="1" pattern="[0-9]" oninput="moveToNext(this)" autocomplete="off">
                                    <input type="text" name="verification_code[]" maxlength="1" pattern="[0-9]" oninput="moveToNext(this)" autocomplete="off">
                                    <input type="text" name="verification_code[]" maxlength="1" pattern="[0-9]" oninput="moveToNext(this)" autocomplete="off">
                                    <input type="text" name="verification_code[]" maxlength="1" pattern="[0-9]" oninput="moveToNext(this)" autocomplete="off">
                                    <input type="text" name="verification_code[]" maxlength="1" pattern="[0-9]" oninput="moveToNext(this)" autocomplete="off">
                                    <input type="text" name="verification_code[]" maxlength="1" pattern="[0-9]" oninput="moveToNext(this)" autocomplete="off">
                                </div>
                                
                                <input type="hidden" name="verify_code" value="1">
                                
                                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                                    <button type="submit" class="btn-primary" id="verifyBtn">Verify Account</button>
                                    <div class="loading-spinner" id="verificationSpinner" style="display: none; margin: 0 auto;"></div>
                                    <div style="text-align: center; color: #28a745; font-size: 50px; display: none;" id="successCheck">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    
                                    <div class="resend-code">
                                        Didn't receive a code? <a href="#" onclick="resendCode()">Resend code</a>
                                    </div>
                                    
                                    <form method="POST" action="">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="back_to_register" value="1">
                                        <button type="submit" class="btn-outline">Back to Registration</button>
                                    </form>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    

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
        
        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        // Password strength indicator
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength bar
            let width = 0;
            let color = '#dc3545'; // Red
            
            if (strength > 3) {
                width = 100;
                color = '#28a745'; // Green
            } else if (strength > 1) {
                width = 66;
                color = '#fd7e14'; // Orange
            } else if (password.length > 0) {
                width = 33;
            }
            
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = color;
        });
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            // Remove all non-digit characters
            let value = this.value.replace(/\D/g, '');
            
            // Limit to 11 characters (09xxxxxxxxx)
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            this.value = value;
        });
        
        // Username validation
        document.getElementById('username').addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
        });
        
        // Verification modal functions
        function selectMethod(element, method) {
            // Remove selected class from all options
            document.querySelectorAll('.verification-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Enable send code button
            document.getElementById('sendCodeBtn').disabled = false;
        }
        
        function moveToNext(input) {
            // Only allow numbers
            input.value = input.value.replace(/[^0-9]/g, '');
            
            // Move to next input if a number was entered
            if (input.value.length === 1) {
                const next = input.nextElementSibling;
                if (next && next.tagName === 'INPUT') {
                    next.focus();
                }
            }
            
            // Move to previous input if backspace was pressed and current input is empty
            if (input.value.length === 0 && event.inputType === 'deleteContentBackward') {
                const prev = input.previousElementSibling;
                if (prev && prev.tagName === 'INPUT') {
                    prev.focus();
                }
            }
        }
        
        function resendCode() {
            // In a real implementation, you would resend the code here
            alert('A new verification code has been sent to your email.');
        }
        
        // Handle verification form submission
        document.getElementById('verificationForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const verifyBtn = document.getElementById('verifyBtn');
            const spinner = document.getElementById('verificationSpinner');
            const successCheck = document.getElementById('successCheck');
            
            // Show loading spinner
            verifyBtn.style.display = 'none';
            spinner.style.display = 'block';
            
            // Simulate verification delay
            setTimeout(() => {
                // Hide spinner and show success check
                spinner.style.display = 'none';
                successCheck.style.display = 'block';
                
                // Submit the form after showing success
                setTimeout(() => {
                    this.submit();
                }, 1000);
            }, 1500);
        });
        
        // Auto-focus first verification input when modal shows
        <?php if ($verificationSent): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const inputs = document.querySelectorAll('.verification-input input');
                if (inputs.length > 0) {
                    inputs[0].focus();
                }
            });
        <?php endif; ?>

        // Auto-focus first verification input when modal shows with error
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = document.querySelector('.verification-modal .error-message');
            if (errorMessage) {
                // If there's an error, shake the modal for emphasis
                const modalContent = document.querySelector('.verification-modal-content');
                modalContent.classList.add('animate__animated', 'animate__headShake');
                
                // Remove the animation class after it completes
                setTimeout(() => {
                    modalContent.classList.remove('animate__animated', 'animate__headShake');
                }, 1000);
            }
            
            // Focus first input if in verification mode
            const inputs = document.querySelectorAll('.verification-input input');
            if (inputs.length > 0) {
                inputs[0].focus();
            }
        });

        // Form validation
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const agreeTerms = document.getElementById('agreeTerms').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            if (!agreeTerms) {
                e.preventDefault();
                alert('You must agree to the terms and conditions');
                return false;
            }
            
            const recaptchaResponse = grecaptcha.getResponse();
            
            if (!recaptchaResponse) {
                e.preventDefault();
                document.getElementById('captchaModal').style.display = 'block';
                return false;
            }
            
            // Show loading screen when registration is successful
            document.getElementById('loadingScreen').style.display = 'flex';
            
            return true;
        });

        // Close modal when clicking the X
        document.querySelector('.close')?.addEventListener('click', function() {
            document.getElementById('verificationModal').style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('verificationModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
            if (event.target == document.getElementById('captchaModal')) {
                closeCaptchaModal();
            }
        });
        
        <?php if (isset($_SESSION['captcha_error']) && $_SESSION['captcha_error']): ?>
        document.getElementById('captchaModal').style.display = 'block';
        <?php unset($_SESSION['captcha_error']); ?>
        <?php endif; ?>
        
        function closeCaptchaModal() {
            document.getElementById('captchaModal').style.display = 'none';
        }
        
        // Enhanced input animations
        const inputs = document.querySelectorAll('.form-group input, .form-group select');
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