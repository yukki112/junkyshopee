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

define('FB_APP_ID', '1448117463050692');
define('FB_APP_SECRET', '947a22c909084c5a3c57a9c66b5dc8c7');
define('FB_REDIRECT_URI', 'https://frsm.qcprotektado.com/Customer-portal/Login/Login.php');

define('RECAPTCHA_SITE_KEY', '6LeYjuorAAAAAPbR8cTtzeaLz05h_yRz2sEfsqfO');
define('RECAPTCHA_SECRET_KEY', '6LeYjuorAAAAAGS0RH6BiwKoS-muwQyzdzFS121K');

$errors = [];
$loginInput = '';
$forgotPasswordMessage = '';
$fbLoginUrl = '';
$pageTitle = 'JunkValue - Sign in';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['error'])) {
    $pageTitle = 'JunkValue - ' . htmlspecialchars($_GET['error']);
}

function generateFacebookLoginUrl() {
    if (isset($_SESSION['fb_state'])) {
        $stateTime = $_SESSION['fb_state_time'] ?? 0;
        if (time() - $stateTime < 120) {
            return 'https://www.facebook.com/v12.0/dialog/oauth?' . http_build_query([
                'client_id' => FB_APP_ID,
                'redirect_uri' => FB_REDIRECT_URI,
                'state' => $_SESSION['fb_state'],
                'scope' => 'email,public_profile',
                'response_type' => 'code'
            ]);
        }
    }
    
    $state = bin2hex(random_bytes(16));
    $_SESSION['fb_state'] = $state;
    $_SESSION['fb_state_time'] = time();
    
    return 'https://www.facebook.com/v12.0/dialog/oauth?' . http_build_query([
        'client_id' => FB_APP_ID,
        'redirect_uri' => FB_REDIRECT_URI,
        'state' => $state,
        'scope' => 'email,public_profile',
        'response_type' => 'code'
    ]);
}

$fbLoginUrl = generateFacebookLoginUrl();

error_log("Session FB State: " . ($_SESSION['fb_state'] ?? 'NOT SET'));

if (isset($_GET['code'])) {
    error_log("Facebook callback received with code");
    
    if (!isset($_SESSION['fb_state'])) {
        $errors[] = "Facebook session expired. Please try again.";
        error_log("Facebook state missing from session");
        $fbLoginUrl = generateFacebookLoginUrl();
    } elseif (!isset($_GET['state'])) {
        $errors[] = "Invalid Facebook response. Missing state parameter.";
        error_log("Facebook state missing from GET parameters");
        $fbLoginUrl = generateFacebookLoginUrl();
    } else {
        $sessionState = $_SESSION['fb_state'];
        $receivedState = $_GET['state'];
        
        error_log("Session state: $sessionState");
        error_log("Received state: $receivedState");
        
        if (!hash_equals($sessionState, $receivedState)) {
            $errors[] = "Security validation failed. Please try logging in again.";
            error_log("Facebook state mismatch: Session='$sessionState', GET='$receivedState'");
            unset($_SESSION['fb_state']);
            unset($_SESSION['fb_state_time']);
            $fbLoginUrl = generateFacebookLoginUrl();
        } else {
            $stateTime = $_SESSION['fb_state_time'] ?? 0;
            if (time() - $stateTime > 300) {
                $errors[] = "Login session expired. Please try again.";
                error_log("Facebook state expired");
                unset($_SESSION['fb_state']);
                unset($_SESSION['fb_state_time']);
                $fbLoginUrl = generateFacebookLoginUrl();
            } else {
                error_log("Facebook state validation successful");
                unset($_SESSION['fb_state']);
                unset($_SESSION['fb_state_time']);
                
                $tokenUrl = 'https://graph.facebook.com/v12.0/oauth/access_token';
                $tokenParams = [
                    'client_id' => FB_APP_ID,
                    'client_secret' => FB_APP_SECRET,
                    'redirect_uri' => FB_REDIRECT_URI,
                    'code' => $_GET['code']
                ];
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $tokenUrl . '?' . http_build_query($tokenParams),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_USERAGENT => 'JunkValue-App/1.0'
                ]);
                
                $tokenResponse = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($curlError) {
                    $errors[] = "Network error: " . $curlError;
                    error_log("CURL Error: " . $curlError);
                } elseif ($httpCode !== 200) {
                    $errors[] = "Facebook authentication failed (HTTP $httpCode). Please try again.";
                    error_log("Facebook token HTTP error $httpCode: " . $tokenResponse);
                } else {
                    $tokenData = json_decode($tokenResponse, true);
                    
                    if (isset($tokenData['access_token'])) {
                        error_log("Successfully obtained Facebook access token");
                        
                        $graphUrl = 'https://graph.facebook.com/v12.0/me?fields=id,name,email,first_name,last_name,picture&access_token=' . urlencode($tokenData['access_token']);
                        
                        $ch = curl_init();
                        curl_setopt_array($ch, [
                            CURLOPT_URL => $graphUrl,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_SSL_VERIFYPEER => true,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_USERAGENT => 'JunkValue-App/1.0'
                        ]);
                        
                        $userResponse = curl_exec($ch);
                        $userHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $userCurlError = curl_error($ch);
                        curl_close($ch);
                        
                        if ($userCurlError) {
                            $errors[] = "Failed to get user information: " . $userCurlError;
                            error_log("User info CURL Error: " . $userCurlError);
                        } elseif ($userHttpCode !== 200) {
                            $errors[] = "Failed to get user information from Facebook (HTTP $userHttpCode)";
                            error_log("Facebook user data HTTP error $userHttpCode: " . $userResponse);
                        } else {
                            $userData = json_decode($userResponse, true);
                            
                            if (isset($userData['error'])) {
                                $errors[] = "Facebook API error: " . $userData['error']['message'];
                                error_log("Facebook API error: " . $userResponse);
                            } elseif (isset($userData['id'])) {
                                error_log("Successfully obtained Facebook user data for ID: " . $userData['id']);
                                processFacebookUser($userData, $conn, $errors);
                            } else {
                                $errors[] = "Invalid user data received from Facebook";
                                error_log("Invalid Facebook user data: " . $userResponse);
                            }
                        }
                    } else {
                        $errors[] = "Failed to get access token from Facebook";
                        error_log("Facebook token error - no access_token: " . $tokenResponse);
                    }
                }
            }
        }
    }
}

function generateUniqueUsername($facebookId, $conn) {
    $baseUsername = 'fb_' . $facebookId;
    $username = $baseUsername;
    $counter = 1;
    
    while (true) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            $checkStmt->close();
            break;
        }
        
        $username = $baseUsername . '_' . $counter;
        $counter++;
        $checkStmt->close();
        
        if ($counter > 100) {
            $username = $baseUsername . '_' . uniqid();
            break;
        }
    }
    
    return $username;
}

function generateUniqueReferralCode($conn) {
    $referralCode = strtoupper(substr(md5(uniqid()), 0, 7));
    
    $refCheckStmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
    $refCheckStmt->bind_param("s", $referralCode);
    $refCheckStmt->execute();
    $refResult = $refCheckStmt->get_result();
    
    while ($refResult->num_rows > 0) {
        $referralCode = strtoupper(substr(md5(uniqid()), 0, 7));
        $refCheckStmt->bind_param("s", $referralCode);
        $refCheckStmt->execute();
        $refResult = $refCheckStmt->get_result();
    }
    $refCheckStmt->close();
    
    return $referralCode;
}

function processFacebookUser($userData, $conn, &$errors) {
    $facebookId = $userData['id'];
    $email = $userData['email'] ?? '';
    $firstName = $userData['first_name'] ?? 'Facebook';
    $lastName = $userData['last_name'] ?? 'User';
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, username, is_admin, is_verified, user_type, facebook_id 
                               FROM users WHERE facebook_id = ?");
        $stmt->bind_param("s", $facebookId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            error_log("Existing Facebook user found with ID: " . $facebookId);
            $conn->commit();
            $stmt->close();
            loginFacebookUser($user);
        } else {
            if (empty($email)) {
                $email = "fb_" . $facebookId . "@facebook.junkvalue.com";
                
                $emailCheckStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $emailCheckStmt->bind_param("s", $email);
                $emailCheckStmt->execute();
                $emailCheckResult = $emailCheckStmt->get_result();
                
                if ($emailCheckResult->num_rows > 0) {
                    $email = "fb_" . $facebookId . "_" . time() . "@facebook.junkvalue.com";
                }
                $emailCheckStmt->close();
                $_SESSION['facebook_no_email'] = true;
            }
            
            if (!empty($email) && $email !== "fb_" . $facebookId . "@facebook.junkvalue.com") {
                $emailStmt = $conn->prepare("SELECT id, facebook_id FROM users WHERE email = ?");
                $emailStmt->bind_param("s", $email);
                $emailStmt->execute();
                $emailResult = $emailStmt->get_result();
                
                if ($emailResult->num_rows === 1) {
                    $existingUser = $emailResult->fetch_assoc();
                    
                    if (empty($existingUser['facebook_id'])) {
                        $updateStmt = $conn->prepare("UPDATE users SET facebook_id = ? WHERE id = ?");
                        $updateStmt->bind_param("si", $facebookId, $existingUser['id']);
                        $updateStmt->execute();
                        $updateStmt->close();
                        
                        $updatedStmt = $conn->prepare("SELECT id, first_name, last_name, email, username, is_admin, is_verified, user_type 
                                                      FROM users WHERE id = ?");
                        $updatedStmt->bind_param("i", $existingUser['id']);
                        $updatedStmt->execute();
                        $updatedResult = $updatedStmt->get_result();
                        $user = $updatedResult->fetch_assoc();
                        $updatedStmt->close();
                        $conn->commit();
                        loginFacebookUser($user);
                        return;
                    } else {
                        throw new Exception("Account conflict detected. Please contact support.");
                    }
                }
                $emailStmt->close();
            }
            
            error_log("Creating new user for Facebook ID: " . $facebookId);
            $username = generateUniqueUsername($facebookId, $conn);
            $randomPassword = bin2hex(random_bytes(16));
            $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
            $referralCode = generateUniqueReferralCode($conn);
            
            $insertStmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash, user_type, agreed_terms, is_verified, facebook_id, referral_code, is_admin) 
                                         VALUES (?, ?, ?, ?, ?, 'individual', 1, 1, ?, ?, 0)");
            $insertStmt->bind_param("sssssss", $firstName, $lastName, $username, $email, $passwordHash, $facebookId, $referralCode);
            
            if ($insertStmt->execute()) {
                $newUserId = $conn->insert_id;
                error_log("New Facebook user created with ID: " . $newUserId);
                $conn->commit();
                $insertStmt->close();
                
                $newUserStmt = $conn->prepare("SELECT id, first_name, last_name, email, username, is_admin, is_verified, user_type 
                                              FROM users WHERE id = ?");
                $newUserStmt->bind_param("i", $newUserId);
                $newUserStmt->execute();
                $newUserResult = $newUserStmt->get_result();
                $newUser = $newUserResult->fetch_assoc();
                $newUserStmt->close();
                loginFacebookUser($newUser);
            } else {
                throw new Exception("Failed to create account: " . $insertStmt->error);
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = $e->getMessage();
        error_log("Facebook registration error: " . $e->getMessage());
    }
}

function loginFacebookUser($user) {
    session_regenerate_id(true);
    $_SESSION = [];
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['last_activity'] = time();
    $_SESSION['community_logged_in'] = true;
    $_SESSION['user_type'] = 'community';
    
    error_log("Facebook login successful - Redirecting to user dashboard for user ID: " . $user['id']);
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid form submission";
    } else {
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            $stmt = $conn->prepare("SELECT id, first_name, last_name, 'user' as type FROM users WHERE email = ? 
                                   UNION 
                                   SELECT id, first_name, last_name, 'employee' as type FROM employees WHERE email = ?");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", time() + 3600);
                
                if ($user['type'] === 'user') {
                    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                } else {
                    $stmt = $conn->prepare("UPDATE employees SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                }
                $stmt->bind_param("ssi", $token, $expires, $user['id']);
                $stmt->execute();
                
                $mail = new PHPMailer(true);
                
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'Stephenviray12@gmail.com';
                    $mail->Password   = 'bubr nckn tgqf lvus';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    
                    $mail->setFrom('Stephenviray12@gmail.com', 'JunkValue');
                    $mail->addAddress($email, $user['first_name'] . ' ' . $user['last_name']);
                    
                    $resetLink = "https://frsm.qcprotektado.com/Customer-portal/Login/reset_password.php?token=$token&type=" . $user['type'];
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body    = "Hi {$user['first_name']},<br><br>"
                                    . "We received a request to reset your password. Click the link below to reset it:<br><br>"
                                    . "<a href='$resetLink'>$resetLink</a><br><br>"
                                    . "If you didn't request this, please ignore this email.<br><br>"
                                    . "Thanks,<br>The JunkValue Team";
                    $mail->AltBody = "Hi {$user['first_name']},\n\n"
                                   . "We received a request to reset your password. Use this link to reset it:\n\n"
                                   . "$resetLink\n\n"
                                   . "If you didn't request this, please ignore this email.\n\n"
                                   . "Thanks,\nThe JunkValue Team";
                    
                    $mail->send();
                    $forgotPasswordMessage = "Password reset link has been sent to your email.";
                } catch (Exception $e) {
                    $errors[] = "Message could not be sent. Please try again later.";
                }
            } else {
                $forgotPasswordMessage = "If your email exists in our system, you'll receive a password reset link.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid form submission";
    } else {
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
            $loginInput = mysqli_real_escape_string($conn, trim($_POST['loginInput']));
            $password = $_POST['password'];
            
            if (empty($loginInput)) {
                $errors[] = "Username or email is required";
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            }
            
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }
            
            if ($_SESSION['login_attempts'] > 5) {
                $errors[] = "Too many login attempts. Please try again later.";
            }
            
            if (empty($errors)) {
                $stmt = $conn->prepare("SELECT id, first_name, last_name, email, username, password_hash, is_admin, is_verified, two_factor_auth 
                                       FROM users WHERE email = ? OR username = ?");
                $stmt->bind_param("ss", $loginInput, $loginInput);
                $stmt->execute();
                $userResult = $stmt->get_result();
                
                $stmt2 = $conn->prepare("SELECT id, first_name, last_name, email, username, password_hash, is_verified, two_factor_auth 
                                       FROM employees WHERE email = ? OR username = ?");
                $stmt2->bind_param("ss", $loginInput, $loginInput);
                $stmt2->execute();
                $employeeResult = $stmt2->get_result();
                
                $foundUser = false;
                
                if ($userResult->num_rows === 1) {
                    $user = $userResult->fetch_assoc();
                    $foundUser = true;
                    
                    if (password_verify($password, $user['password_hash'])) {
                        if (!$user['is_verified']) {
                            $errors[] = "Please verify your email first. Check your inbox.";
                            $_SESSION['verification_email'] = $user['email'];
                        } else {
                            if ($user['two_factor_auth'] == 1) {
                                $verification_code = sprintf("%06d", mt_rand(0, 999999));
                                $_SESSION['2fa_code'] = $verification_code;
                                $_SESSION['2fa_user_id'] = $user['id'];
                                $_SESSION['2fa_user_type'] = 'user';
                                $_SESSION['2fa_is_admin'] = $user['is_admin'];
                                $_SESSION['2fa_expires'] = time() + 300;
                                
                                $mail = new PHPMailer(true);
                                
                                try {
                                    $mail->isSMTP();
                                    $mail->Host       = 'smtp.gmail.com';
                                    $mail->SMTPAuth   = true;
                                    $mail->Username   = 'Stephenviray12@gmail.com';
                                    $mail->Password   = 'bubr nckn tgqf lvus';
                                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail->Port       = 587;
                                    
                                    $mail->setFrom('Stephenviray12@gmail.com', 'JunkValue Security');
                                    $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
                                    
                                    $mail->isHTML(true);
                                    $mail->Subject = 'Your JunkValue Verification Code';
                                    $mail->Body    = "Hi {$user['first_name']},<br><br>"
                                                    . "Your verification code is: <strong style='font-size: 24px; color: #6A7F46; letter-spacing: 3px;'>{$verification_code}</strong><br><br>"
                                                    . "This code will expire in 5 minutes.<br><br>"
                                                    . "If you didn't attempt to log in, please secure your account immediately.<br><br>"
                                                    . "Thanks,<br>The JunkValue Security Team";
                                    $mail->AltBody = "Hi {$user['first_name']},\n\n"
                                                   . "Your verification code is: {$verification_code}\n\n"
                                                   . "This code will expire in 5 minutes.\n\n"
                                                   . "If you didn't attempt to log in, please secure your account immediately.\n\n"
                                                   . "Thanks,\nThe JunkValue Security Team";
                                    
                                    $mail->send();
                                    header("Location: verify_2fa.php");
                                    exit();
                                } catch (Exception $e) {
                                    $errors[] = "Failed to send verification code. Please try again.";
                                }
                            } else {
                                session_regenerate_id(true);
                                $_SESSION = [];
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['user_email'] = $user['email'];
                                $_SESSION['user_username'] = $user['username'];
                                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                                $_SESSION['last_activity'] = time();
                                $_SESSION['login_attempts'] = 0;
                                
                                if ($user['is_admin']) {
                                    $_SESSION['admin_logged_in'] = true;
                                    $_SESSION['user_type'] = 'admin';
                                    header("Location: ../../admin/index.php?redirect=admin");
                                    exit();
                                } else {
                                    $_SESSION['community_logged_in'] = true;
                                    $_SESSION['user_type'] = 'community';
                                    header("Location: ../index.php");
                                    exit();
                                }
                            }
                        }
                    } else {
                        $_SESSION['login_attempts']++;
                        header("Location: Login.php?error=Incorrect+password");
                        exit();
                    }
                }
                
                if (!$foundUser && $employeeResult->num_rows === 1) {
                    $employee = $employeeResult->fetch_assoc();
                    
                    if (password_verify($password, $employee['password_hash'])) {
                        if (!$employee['is_verified']) {
                            $errors[] = "Please verify your email first. Check your inbox.";
                            $_SESSION['verification_email'] = $employee['email'];
                        } else {
                            if ($employee['two_factor_auth'] == 1) {
                                $verification_code = sprintf("%06d", mt_rand(0, 999999));
                                $_SESSION['2fa_code'] = $verification_code;
                                $_SESSION['2fa_user_id'] = $employee['id'];
                                $_SESSION['2fa_user_type'] = 'employee';
                                $_SESSION['2fa_expires'] = time() + 300;
                                
                                $mail = new PHPMailer(true);
                                
                                try {
                                    $mail->isSMTP();
                                    $mail->Host       = 'smtp.gmail.com';
                                    $mail->SMTPAuth   = true;
                                    $mail->Username   = 'Stephenviray12@gmail.com';
                                    $mail->Password   = 'bubr nckn tgqf lvus';
                                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail->Port       = 587;
                                    
                                    $mail->setFrom('Stephenviray12@gmail.com', 'JunkValue Security');
                                    $mail->addAddress($employee['email'], $employee['first_name'] . ' ' . $employee['last_name']);
                                    
                                    $mail->isHTML(true);
                                    $mail->Subject = 'Your JunkValue Verification Code';
                                    $mail->Body    = "Hi {$employee['first_name']},<br><br>"
                                                    . "Your verification code is: <strong style='font-size: 24px; color: #6A7F46; letter-spacing: 3px;'>{$verification_code}</strong><br><br>"
                                                    . "This code will expire in 5 minutes.<br><br>"
                                                    . "If you didn't attempt to log in, please secure your account immediately.<br><br>"
                                                    . "Thanks,<br>The JunkValue Security Team";
                                    
                                    $mail->send();
                                    header("Location: verify_2fa.php");
                                    exit();
                                } catch (Exception $e) {
                                    $errors[] = "Failed to send verification code. Please try again.";
                                }
                            } else {
                                session_regenerate_id(true);
                                $_SESSION = [];
                                $_SESSION['employee_id'] = $employee['id'];
                                $_SESSION['employee_email'] = $employee['email'];
                                $_SESSION['employee_username'] = $employee['username'];
                                $_SESSION['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
                                $_SESSION['last_activity'] = time();
                                $_SESSION['login_attempts'] = 0;
                                $_SESSION['employee_logged_in'] = true;
                                $_SESSION['user_type'] = 'employee';
                                header("Location: ../../Employee/Index.php?redirect=employee");
                                exit();
                            }
                        }
                    } else {
                        $_SESSION['login_attempts']++;
                        header("Location: Login.php?error=Incorrect+password");
                        exit();
                    }
                } else {
                    if (!$foundUser) {
                        $_SESSION['login_attempts']++;
                        header("Location: Login.php?error=User+not+found");
                        exit();
                    }
                }
                $stmt->close();
                if (isset($stmt2)) $stmt2->close();
            }
        }
    }
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
        
        /* Enhanced login container with glass morphism and organic shape */
        .login-container {
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
        
        .login-container::before {
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
        
        .login-container > * {
            position: relative;
            z-index: 1;
        }
        
        /* Enhanced logo with bounce animation */
        .login-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .login-logo img {
            width: 100px;
            height: auto;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.3)) drop-shadow(0 0 20px rgba(255, 215, 0, 0.2));
            animation: bounce 3s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
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
        
        .login-header p {
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
        
        .forgot-password {
            text-align: right;
            margin-bottom: 22px;
        }
        
        .forgot-password a {
            color: var(--accent-green);
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .forgot-password a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-green);
            transition: width 0.3s ease;
        }
        
        .forgot-password a:hover::after {
            width: 100%;
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
        
        .divider {
            display: flex;
            align-items: center;
            margin: 28px 0;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 700;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 2px solid var(--border-color);
            transition: border-color 0.3s ease;
        }
        
        .divider::before {
            margin-right: 15px;
        }
        
        .divider::after {
            margin-left: 15px;
        }
        
        /* Enhanced social button */
        .social-btn {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-weight: 700;
            cursor: pointer;
            border: 2px solid #4267B2;
            background: linear-gradient(135deg, #4267B2 0%, #365899 100%);
            color: white;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 22px;
            font-size: 15px;
            box-shadow: 0 6px 20px rgba(66, 103, 178, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .social-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .social-btn:hover::before {
            left: 100%;
        }
        
        .social-btn:hover {
            background: linear-gradient(135deg, #365899 0%, #2d4373 100%);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(66, 103, 178, 0.5);
        }
        
        .signup-link {
            text-align: center;
            margin-top: 25px;
            color: var(--text-secondary);
            font-size: 14px;
            transition: color 0.3s ease;
            font-weight: 500;
        }
        
        .signup-link a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 800;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .signup-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-green);
            transition: width 0.3s ease;
        }
        
        .signup-link a:hover::after {
            width: 100%;
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
        
        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
            font-weight: 600;
            letter-spacing: 0.5px;
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
        
        /* Enhanced forgot password modal */
        .forgot-modal {
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
        
        .forgot-modal-content {
            background: var(--container-bg);
            margin: 8% auto;
            padding: 50px;
            border-radius: 30px;
            box-shadow: 0 30px 80px var(--shadow-color), 0 0 0 1px rgba(255, 255, 255, 0.2);
            width: 90%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        
        .forgot-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .forgot-modal-header h2 {
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
        
        .forgot-modal-footer {
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
            
            .login-container {
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
            
            .login-container {
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
            
            .login-container {
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
    
     
    <div class="login-container">
        <div class="login-logo">
            <img src="img/MainLogo.svg" alt="JunkValue">
        </div>
        
        <div class="login-header">
            <h2>Sign In</h2>
            <p>Welcome back to JunkValue</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($forgotPasswordMessage)): ?>
            <div class="<?php echo strpos($forgotPasswordMessage, 'sent') !== false ? 'success-message' : 'error-message'; ?>">
                <p><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($forgotPasswordMessage); ?></p>
            </div>
        <?php endif; ?>
        
        <a href="<?php echo $fbLoginUrl; ?>" class="social-btn">
            <i class="fab fa-facebook-f"></i>
            Continue with Facebook
        </a>
        
        <div class="divider">or</div>
        
        <form method="POST" action="" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="loginInput">Username or Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" id="loginInput" name="loginInput" placeholder="Enter your username or email" 
                           value="<?php echo htmlspecialchars($loginInput); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="loginPassword">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="forgot-password">
                <a href="#" id="forgotPasswordLink">Forgot password?</a>
            </div>
            
            <div class="recaptcha-container">
                <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
            </div>
            
            <button type="submit" name="login" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        
        <div class="signup-link">
            Don't have an account? <a href="Register.php">Sign up</a>
        </div>
        
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
            <p><strong>Please complete the reCAPTCHA challenge before logging in.</strong></p>
            <p style="font-size: 14px; margin-top: 15px;">
                <i class="fas fa-info-circle"></i> Instructions:<br>
                1. Check the "I'm not a robot" box<br>
                2. Complete any image challenges if prompted<br>
                3. Click the Sign In button again
            </p>
            <button class="modal-btn" onclick="closeCaptchaModal()">Got it!</button>
        </div>
    </div>
    
    <div id="forgotPasswordModal" class="forgot-modal">
        <div class="forgot-modal-content">
            <div class="forgot-modal-header">
                <h2><i class="fas fa-key" style="margin-right: 10px;"></i> Forgot Password</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Enter your email address and we'll send you a link to reset your password.</p>
                <form id="forgotPasswordForm" method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="forgot-modal-footer">
                <button type="button" class="btn-outline" id="cancelForgotPassword">Cancel</button>
                <button type="submit" form="forgotPasswordForm" name="forgot_password" class="modal-btn">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </div>
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
        
        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('loginPassword');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        
        // Forgot password modal
        const forgotModal = document.getElementById("forgotPasswordModal");
        const forgotBtn = document.getElementById("forgotPasswordLink");
        const span = document.getElementsByClassName("close")[0];
        const cancelBtn = document.getElementById("cancelForgotPassword");
        
        forgotBtn.onclick = function(e) {
            e.preventDefault();
            forgotModal.style.display = "block";
        }
        
        span.onclick = function() {
            forgotModal.style.display = "none";
        }
        
        cancelBtn.onclick = function() {
            forgotModal.style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == forgotModal) {
                forgotModal.style.display = "none";
            }
            if (event.target == document.getElementById('captchaModal')) {
                closeCaptchaModal();
            }
        }
        
        <?php if (isset($_SESSION['captcha_error']) && $_SESSION['captcha_error']): ?>
        document.getElementById('captchaModal').style.display = 'block';
        <?php unset($_SESSION['captcha_error']); ?>
        <?php endif; ?>
        
        function closeCaptchaModal() {
            document.getElementById('captchaModal').style.display = 'none';
        }
        
        // Form submission with captcha validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const recaptchaResponse = grecaptcha.getResponse();
            
            if (!recaptchaResponse) {
                e.preventDefault();
                document.getElementById('captchaModal').style.display = 'block';
                return false;
            }
            
            // Show loading screen when login is successful
            document.getElementById('loadingScreen').style.display = 'flex';
        });
        
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