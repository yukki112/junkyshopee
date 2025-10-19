<?php
// Start session and include database connection
session_start();
require_once 'db_connection.php';

// Include PHPMailer for email verification
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// reCAPTCHA secret key
define('RECAPTCHA_SECRET_KEY', '6LeYjuorAAAAAGS0RH6BiwKoS-muwQyzdzFS121K');

// SMTP Configuration
define('SMTP_USERNAME', 'Stephenviray12@gmail.com');
define('SMTP_PASSWORD', 'bubr nckn tgqf lvus');

// Language handling
$language = 'en'; // Default language
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'tl'])) {
    $language = $_GET['lang'];
    $_SESSION['language'] = $language;
} elseif (isset($_SESSION['language'])) {
    $language = $_SESSION['language'];
}

// Language strings
$translations = [
    'en' => [
        'dashboard' => 'Dashboard',
        'transaction_history' => 'Transaction History',
        'schedule_pickup' => 'Schedule Pickup',
        'current_prices' => 'Current Prices',
        'loyalty_rewards' => 'Loyalty Rewards',
        'account_settings' => 'Account Settings',
        'logout' => 'Logout',
        'profile' => 'Profile',
        'security' => 'Security',
        'member_since' => 'Member since',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'account_type' => 'Account Type',
        'phone_number' => 'Phone Number',
        'username' => 'Username',
        'email' => 'Email',
        'loyalty_tier' => 'Loyalty Tier',
        'loyalty_points' => 'Loyalty Points',
        'default_pickup_address' => 'Default Pickup Address',
        'cancel' => 'Cancel',
        'save_changes' => 'Save Changes',
        'security_settings' => 'Security Settings',
        'password' => 'Password',
        'last_changed' => 'Last changed',
        'months_ago' => 'months ago',
        'change' => 'Change',
        'danger_zone' => 'Danger Zone',
        'delete_account_warning' => 'Once you delete your account, there is no going back. Please be certain.',
        'delete_account' => 'Delete Account',
        'change_password' => 'Change Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_new_password' => 'Confirm New Password',
        'change_phone_number' => 'Change Phone Number',
        'new_phone_number' => 'New Phone Number',
        'delete_account_confirm' => 'This action cannot be undone. This will permanently delete your account and all associated data.',
        'delete_confirm_instruction' => 'To confirm, please type DELETE in the box below:',
        'crop_profile_picture' => 'Crop Profile Picture',
        'crop_save' => 'Crop & Save',
        'change_username' => 'Change Username',
        'new_username' => 'New Username',
        'change_email' => 'Change Email',
        'new_email' => 'New Email Address',
        'verified' => 'Verified',
        'verify_email' => 'Verify Email',
        'enter_verification_code' => 'Enter Verification Code',
        'verification_code' => 'Verification Code',
        'verify' => 'Verify',
        'resend_code' => 'Resend Code',
        'success' => 'Success',
        'username_changed_success' => 'Username successfully changed to',
        'email_verification_sent' => 'Verification code sent to your email',
        'email_verified_success' => 'Email successfully verified and updated',
        'invalid_verification_code' => 'Invalid verification code',
        'captcha_verification_failed' => 'CAPTCHA verification failed. Please try again.',
        'captcha_required' => 'Please complete the CAPTCHA verification'
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'transaction_history' => 'Kasaysayan ng Transaksyon',
        'schedule_pickup' => 'I-skedyul ang Pickup',
        'current_prices' => 'Kasalukuyang Mga Presyo',
        'loyalty_rewards' => 'Mga Gantimpala ng Loyalty',
        'account_settings' => 'Mga Setting ng Account',
        'logout' => 'Logout',
        'profile' => 'Profile',
        'security' => 'Seguridad',
        'member_since' => 'Miyembro mula noong',
        'first_name' => 'Pangalan',
        'last_name' => 'Apelyido',
        'username' => 'Username',
        'email' => 'Email',
        'loyalty_tier' => 'Antas ng Loyalty',
        'loyalty_points' => 'Mga Puntos ng Loyalty',
        'account_type' => 'Uri ng Account',
        'phone_number' => 'Numero ng Telepono',
        'default_pickup_address' => 'Default na Pickup Address',
        'cancel' => 'Kanselahin',
        'save_changes' => 'I-save ang Mga Pagbabago',
        'security_settings' => 'Mga Setting ng Seguridad',
        'password' => 'Password',
        'last_changed' => 'Huling binago',
        'months_ago' => 'buwan na ang nakalipas',
        'change' => 'Palitan',
        'danger_zone' => 'Danger Zone',
        'delete_account_warning' => 'Kapag tinanggal mo ang iyong account, walang paraan para bumalik. Mangyaring maging sigurado.',
        'delete_account' => 'Tanggalin ang Account',
        'change_password' => 'Palitan ang Password',
        'current_password' => 'Kasalukuyang Password',
        'new_password' => 'Bagong Password',
        'confirm_new_password' => 'Kumpirmahin ang Bagong Password',
        'change_phone_number' => 'Palitan ang Numero ng Telepono',
        'new_phone_number' => 'Bagong Numero ng Telepono',
        'delete_account_confirm' => 'Hindi na mababawi ang aksyon na ito. Permanente nitong tatanggalin ang iyong account at lahat ng nauugnay na data.',
        'delete_confirm_instruction' => 'Upang kumpirmahin, mangyaring i-type ang DELETE sa kahon sa ibaba:',
        'crop_profile_picture' => 'I-crop ang Profile Picture',
        'crop_save' => 'I-crop at I-save',
        'change_username' => 'Palitan ang Username',
        'new_username' => 'Bagong Username',
        'change_email' => 'Palitan ang Email',
        'new_email' => 'Bagong Email Address',
        'verified' => 'Na-verify',
        'verify_email' => 'I-verify ang Email',
        'enter_verification_code' => 'Ilagay ang Verification Code',
        'verification_code' => 'Verification Code',
        'verify' => 'I-verify',
        'resend_code' => 'Ipadala muli ang Code',
        'success' => 'Tagumpay',
        'username_changed_success' => 'Matagumpay na napalitan ang username sa',
        'email_verification_sent' => 'Verification code ay ipinadala sa iyong email',
        'email_verified_success' => 'Matagumpay na na-verify at na-update ang email',
        'invalid_verification_code' => 'Hindi wastong verification code',
        'captcha_verification_failed' => 'Nabigo ang pag-verify ng CAPTCHA. Pakisubukan muli.',
        'captcha_required' => 'Mangyaring kumpletuhin ang pag-verify ng CAPTCHA'
    ]
];

$t = $translations[$language];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login/Login.php");
    exit();
}

// Get current user ID and info - FIXED: Use is_verified instead of is_email_verified
$user_id = $_SESSION['user_id'];
$user_query = "SELECT id, first_name, last_name, username, email, phone, address, profile_image, user_type, loyalty_points, loyalty_tier, created_at, is_verified FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// Initialize CAPTCHA verification status
if (!isset($_SESSION['captcha_verified'])) {
    $_SESSION['captcha_verified'] = [
        'username' => false,
        'email' => false
    ];
}

// Function to verify CAPTCHA
function verifyCaptcha($captcha_response) {
    if (empty($captcha_response)) {
        return false;
    }
    
    $captcha_verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".RECAPTCHA_SECRET_KEY."&response=".$captcha_response);
    $captcha_response_data = json_decode($captcha_verify);
    
    return $captcha_response_data->success;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        $update_query = "UPDATE users SET address = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "si", $address, $user_id);
        mysqli_stmt_execute($update_stmt);
        
        // Refresh user data
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user = mysqli_fetch_assoc($user_result);
        
        $success_message = "Profile updated successfully!";
    }
    elseif (isset($_POST['upload_avatar'])) {
        if (isset($_POST['avatar_data']) && !empty($_POST['avatar_data'])) {
            $avatar_data = $_POST['avatar_data'];
            $image_parts = explode(";base64,", $avatar_data);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            
            // Generate unique filename
            $filename = 'avatar_' . $user_id . '_' . time() . '.png';
            $filepath = 'uploads/' . $filename;
            
            // Create uploads directory if it doesn't exist
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            // Save the file
            if (file_put_contents($filepath, $image_base64)) {
                // Delete old image if exists
                if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                    @unlink($user['profile_image']);
                }
                
                // Update database
                $update_img_query = "UPDATE users SET profile_image = ? WHERE id = ?";
                $update_img_stmt = mysqli_prepare($conn, $update_img_query);
                mysqli_stmt_bind_param($update_img_stmt, "si", $filepath, $user_id);
                mysqli_stmt_execute($update_img_stmt);
                
                // Refresh user data
                $user['profile_image'] = $filepath;
                $success_message = "Profile picture updated successfully!";
            } else {
                $error_message = "Failed to save profile picture";
            }
        }
    }
    elseif (isset($_POST['delete_account'])) {
        if ($_POST['confirmation'] === "DELETE") {
            // Delete user account
            $delete_query = "DELETE FROM users WHERE id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
            mysqli_stmt_execute($delete_stmt);
            
            // Delete profile image if exists
            if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                @unlink($user['profile_image']);
            }
            
            // Logout and redirect
            session_destroy();
            header("Location: Login/Login.php");
            exit();
        } else {
            $error_message = "Please type DELETE to confirm account deletion";
        }
    }
    elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $check_query = "SELECT password_hash FROM users WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $check_data = mysqli_fetch_assoc($check_result);
        
        if (password_verify($current_password, $check_data['password_hash'])) {
            if ($new_password === $confirm_password) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pw_query = "UPDATE users SET password_hash = ? WHERE id = ?";
                $update_pw_stmt = mysqli_prepare($conn, $update_pw_query);
                mysqli_stmt_bind_param($update_pw_stmt, "si", $hashed_password, $user_id);
                mysqli_stmt_execute($update_pw_stmt);
                
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "New passwords don't match";
            }
        } else {
            $error_message = "Current password is incorrect";
        }
    }
    elseif (isset($_POST['change_phone'])) {
        $new_phone = mysqli_real_escape_string($conn, $_POST['new_phone']);
        
        $update_phone_query = "UPDATE users SET phone = ? WHERE id = ?";
        $update_phone_stmt = mysqli_prepare($conn, $update_phone_query);
        mysqli_stmt_bind_param($update_phone_stmt, "si", $new_phone, $user_id);
        mysqli_stmt_execute($update_phone_stmt);
        
        // Refresh user data
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user = mysqli_fetch_assoc($user_result);
        
        $success_message = "Phone number updated successfully!";
    }
    elseif (isset($_POST['change_username'])) {
        $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
        $captcha_response = $_POST['g-recaptcha-response'] ?? '';
        
        // Check if CAPTCHA is already verified for username
        if (!$_SESSION['captcha_verified']['username']) {
            // Verify CAPTCHA if not already verified
            if (!verifyCaptcha($captcha_response)) {
                $error_message = $t['captcha_verification_failed'];
            } else {
                // Mark CAPTCHA as verified for username
                $_SESSION['captcha_verified']['username'] = true;
            }
        }
        
        // If CAPTCHA is verified (either previously or now), proceed with username change
        if ($_SESSION['captcha_verified']['username']) {
            // Check if username already exists
            $username_check_query = "SELECT id FROM users WHERE username = ? AND id != ?";
            $username_check_stmt = mysqli_prepare($conn, $username_check_query);
            mysqli_stmt_bind_param($username_check_stmt, "si", $new_username, $user_id);
            mysqli_stmt_execute($username_check_stmt);
            $username_check_result = mysqli_stmt_get_result($username_check_stmt);
            
            if (mysqli_num_rows($username_check_result) > 0) {
                $error_message = "Username already taken";
            } else {
                // Update username
                $update_username_query = "UPDATE users SET username = ? WHERE id = ?";
                $update_username_stmt = mysqli_prepare($conn, $update_username_query);
                mysqli_stmt_bind_param($update_username_stmt, "si", $new_username, $user_id);
                
                if (mysqli_stmt_execute($update_username_stmt)) {
                    // Refresh user data
                    mysqli_stmt_execute($user_stmt);
                    $user_result = mysqli_stmt_get_result($user_stmt);
                    $user = mysqli_fetch_assoc($user_result);
                    
                    $success_message = $t['username_changed_success'] . " " . $new_username;
                    
                    // Reset CAPTCHA verification for username after successful change
                    $_SESSION['captcha_verified']['username'] = false;
                } else {
                    $error_message = "Failed to update username. Please try again.";
                }
            }
        } else {
            $error_message = $t['captcha_required'];
        }
    }
    elseif (isset($_POST['change_email_request'])) {
        $new_email = mysqli_real_escape_string($conn, $_POST['new_email']);
        
        // Check if email already exists
        $email_check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_check_stmt = mysqli_prepare($conn, $email_check_query);
        mysqli_stmt_bind_param($email_check_stmt, "si", $new_email, $user_id);
        mysqli_stmt_execute($email_check_stmt);
        $email_check_result = mysqli_stmt_get_result($email_check_stmt);
        
        if (mysqli_num_rows($email_check_result) > 0) {
            $error_message = "Email already registered";
        } else {
            // Generate verification code
            $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['email_verification_code'] = $verification_code;
            $_SESSION['pending_email'] = $new_email;
            $_SESSION['email_verification_time'] = time();
            
            // Send verification email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USERNAME;
                $mail->Password   = SMTP_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                
                // Recipients
                $mail->setFrom('noreply@junkvalue.com', 'JunkValue');
                $mail->addAddress($new_email, $user['first_name']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your JunkValue Email Verification Code';
                
                // HTML email body
                $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #6A7F46; border: 1px solid #6A7F46; border-radius: 5px; padding: 20px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="display: inline-block; margin-bottom: 15px;">
                            <span style="font-size: 30px; font-weight: bold; color: #3C342C; vertical-align: middle; margin-left: 10px;">JunkValue</span>
                        </div>
                        <h2 style="color: #3C342C; margin-bottom: 5px;">Email Verification Code</h2>
                    </div>
                    
                    <div style="background-color: #6A7F46; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                        <h3>Hello '.$user['first_name'].',</h3>
                        
                        <h3>Your email verification code is:</h3>
                        
                        <div style="text-align: center; margin: 25px 0;">
                            <span style="font-size: 28px; font-weight: bold; letter-spacing: 2px; color: #3C342C; background-color: #f0f0f0; padding: 10px 20px; border-radius: 5px; display: inline-block;">
                                '.$verification_code.'
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
                    
                    <div style="margin-top: 30px; text-align: center; color: #3C342C;; font-size: 14px;">
                        <p>Thank you,</p>
                        <h2><strong>JunkValue Team</strong></h2>
                        <p><a href="#" style="color: #3C342C; text-decoration: none;">junkvalue.com</a></p>
                    </div>
                </div>
                ';
                
                // Plain text version for non-HTML email clients
                $mail->AltBody = "Hello ".$user['first_name'].",\n\n"
                               . "Your JunkValue email verification code is: ".$verification_code."\n\n"
                               . "This code will expire in 10 minutes.\n\n"
                               . "If you didn't request this, please ignore this email.\n\n"
                               . "Best regards,\nThe JunkValue Team\njunkvalue.com";
                
                $mail->send();
                $success_message = $t['email_verification_sent'];
            } catch (Exception $e) {
                $error_message = "Verification email could not be sent. Error: {$mail->ErrorInfo}";
            }
        }
    }
    elseif (isset($_POST['verify_email_code'])) {
        $submitted_code = $_POST['verification_code'];
        $stored_code = $_SESSION['email_verification_code'];
        $pending_email = $_SESSION['pending_email'];
        $verification_time = $_SESSION['email_verification_time'];
        
        // Check if code is expired (10 minutes)
        if (time() - $verification_time > 600) {
            $error_message = "Verification code has expired. Please request a new one.";
        } elseif ($submitted_code === $stored_code) {
            // Update email - FIXED: Use is_verified instead of is_email_verified
            $update_email_query = "UPDATE users SET email = ?, is_verified = 1 WHERE id = ?";
            $update_email_stmt = mysqli_prepare($conn, $update_email_query);
            mysqli_stmt_bind_param($update_email_stmt, "si", $pending_email, $user_id);
            mysqli_stmt_execute($update_email_stmt);
            
            // Clear session data
            unset($_SESSION['email_verification_code']);
            unset($_SESSION['pending_email']);
            unset($_SESSION['email_verification_time']);
            
            // Refresh user data
            mysqli_stmt_execute($user_stmt);
            $user_result = mysqli_stmt_get_result($user_stmt);
            $user = mysqli_fetch_assoc($user_result);
            
            $success_message = $t['email_verified_success'];
        } else {
            $error_message = $t['invalid_verification_code'];
        }
    }
}

// Format user data for display
$user_name = $user['first_name'] . ' ' . $user['last_name'];
$user_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$member_since = date('F Y', strtotime($user['created_at']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['account_settings']; ?></title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- reCAPTCHA API -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        /* All existing CSS styles remain the same */
        :root {
            --bg-beige: #E6D8C3;
            --sales-orange: #D97A41;
            --stock-green: #708B4C;
            --panel-cream: #F2EAD3;
            --topbar-brown: #3C342C;
            --text-dark: #2E2B29;
            --icon-green: #6A7F46;
            --icon-orange: #D97A41;
            --accent-blue: #4A89DC;
            --sidebar-width: 280px;
            
            /* Dark mode variables */
            --dark-bg-primary: #1a1a1a;
            --dark-bg-secondary: #2d2d2d;
            --dark-bg-tertiary: #3c3c3c;
            --dark-text-primary: #ffffff;
            --dark-text-secondary: #a0a0a0;
            --dark-border: #404040;
            --dark-shadow: rgba(0, 0, 0, 0.3);
        }

        /* All existing CSS styles remain the same until the new additions below */

        /* New styles for verification modal and loading indicators */
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
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .verification-input input:focus {
            border-color: var(--icon-green);
            outline: none;
            box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.2);
            background: white;
        }

        body.dark-mode .verification-input input {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .resend-code {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
        }

        body.dark-mode .resend-code {
            color: var(--dark-text-secondary);
        }

        .resend-code a {
            color: var(--icon-green);
            text-decoration: none;
            font-weight: 600;
        }

        .resend-code a:hover {
            text-decoration: underline;
        }

        .loading-spinner {
            display: none;
            width: 40px;
            height: 40px;
            margin: 0 auto;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid var(--icon-green);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .success-check {
            display: none;
            text-align: center;
            color: #28a745;
            font-size: 50px;
            margin: 20px 0;
        }

        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease infinite;
        }

        .g-recaptcha {
            margin: 15px 0;
            display: flex;
            justify-content: center;
        }

        /* All other existing CSS styles remain the same */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-beige);
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        body.dark-mode {
            background-color: var(--dark-bg-primary);
            color: var(--dark-text-primary);
        }

        /* Sidebar - New Vibrant Design */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--topbar-brown) 0%, #2A2520 100%);
            color: white;
            padding: 30px 0;
            position: sticky;
            top: 0;
            height: 100vh;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
            z-index: 10;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        body.dark-mode .sidebar {
            background: linear-gradient(180deg, var(--dark-bg-secondary) 0%, var(--dark-bg-primary) 100%);
            box-shadow: 5px 0 15px var(--dark-shadow);
        }

        .sidebar-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }

        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--panel-cream);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--topbar-brown);
            font-size: 24px;
            margin-bottom: 15px;
            border: 3px solid var(--sales-orange);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        body.dark-mode .user-avatar {
            background-color: var(--dark-bg-tertiary);
            color: var(--dark-text-primary);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        }

        body.dark-mode .user-avatar:hover {
            box-shadow: 0 6px 15px var(--dark-shadow);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .user-name {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 5px;
            text-align: center;
            color: white;
        }

        body.dark-mode .user-name {
            color: var(--dark-text-primary);
        }

        .user-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--panel-cream);
            opacity: 0.8;
        }

        body.dark-mode .user-status {
            color: var(--dark-text-secondary);
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            background-color: #2ECC71;
            border-radius: 50%;
        }

        .nav-menu {
            list-style: none;
            padding: 0 15px;
        }

        .nav-menu li {
            margin-bottom: 5px;
        }

        .nav-menu a {
            display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 20px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
        font-size: 15px;
        letter-spacing: 0.3px;
        }

        body.dark-mode .nav-menu a {
            color: rgba(255,255,255,0.8);
        }

        .nav-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 3px;
            height: 100%;
            background-color: var(--sales-orange);
            transform: translateX(-10px);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .nav-menu a:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        body.dark-mode .nav-menu a:hover {
            background-color: rgba(255,255,255,0.15);
        }

        .nav-menu a:hover::before {
            transform: translateX(0);
            opacity: 1;
        }

        .nav-menu a.active {
            background-color: rgba(255,255,255,0.15);
            color: white;
            font-weight: 600;
        }

        body.dark-mode .nav-menu a.active {
            background-color: rgba(255,255,255,0.2);
        }

        .nav-menu a.active::before {
            transform: translateX(0);
            opacity: 1;
        }

        .nav-menu i {
            width: 20px;
            text-align: center;
            font-size: 18px;
            color: var(--panel-cream);
        }

        .nav-menu a.active i {
            color: var(--sales-orange);
        }

        .sidebar-footer {
            padding: 20px;
            margin-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            background-color: rgba(255,255,255,0.1);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        /* Language Switcher */
        .language-switcher {
            position: relative;
            margin: 15px 20px;
        }

        .language-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(255,255,255,0.1);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .language-btn:hover {
            background-color: rgba(255,255,255,0.2);
        }

        .language-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: var(--topbar-brown);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 100;
            display: none;
            overflow: hidden;
        }

        body.dark-mode .language-dropdown {
            background-color: var(--dark-bg-secondary);
        }

        .language-dropdown.active {
            display: block;
        }

        .language-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .language-option:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .language-option.active {
            background-color: rgba(217, 122, 65, 0.3);
        }

        .flag-icon {
            width: 20px;
            height: 15px;
            border-radius: 2px;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 50px;
            font-weight: 700;
            color: var(--topbar-brown);
            position: relative;
            display: inline-block;
            transition: color 0.3s ease;
        }

        body.dark-mode .page-title {
            color: var(--dark-text-primary);
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--sales-orange);
            border-radius: 3px;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .dark-mode-toggle {
            position: relative;
            width: 60px;
            height: 30px;
            background-color: var(--topbar-brown);
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            outline: none;
        }

        body.dark-mode .dark-mode-toggle {
            background-color: var(--dark-bg-tertiary);
        }

        .dark-mode-toggle::before {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            background-color: var(--panel-cream);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        body.dark-mode .dark-mode-toggle::before {
            transform: translateX(30px);
            background-color: var(--dark-text-primary);
        }

        .dark-mode-toggle i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            color: var(--sales-orange);
        }

        .dark-mode-toggle .sun {
            left: 8px;
            opacity: 0;
        }

        .dark-mode-toggle .moon {
            right: 8px;
            opacity: 1;
        }

        body.dark-mode .dark-mode-toggle .sun {
            opacity: 1;
        }

        body.dark-mode .dark-mode-toggle .moon {
            opacity: 0;
        }

        .notification-bell {
            position: relative;
            width: 40px;
            height: 40px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        body.dark-mode .notification-bell {
            background-color: var(--dark-bg-secondary);
            box-shadow: 0 3px 10px var(--dark-shadow);
            color: var(--dark-text-primary);
        }

        .notification-bell:hover {
            transform: scale(1.1) rotate(15deg);
        }

        .notification-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            width: 18px;
            height: 18px;
            background-color: var(--sales-orange);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        /* Dashboard Cards */
        .dashboard-card {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        body.dark-mode .dashboard-card {
            background-color: var(--dark-bg-secondary);
            border-color: var(--dark-border);
            box-shadow: 0 5px 15px var(--dark-shadow);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: border-color 0.3s ease;
        }

        body.dark-mode .card-header {
            border-bottom-color: var(--dark-border);
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: color 0.3s ease;
        }

        body.dark-mode .card-title {
            color: var(--dark-text-primary);
        }

        .card-title i {
            color: var(--icon-green);
        }

        .view-all {
            color: var(--icon-green);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .view-all:hover {
            color: var(--sales-orange);
            transform: translateX(3px);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
            z-index: 1;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--icon-green) 0%, var(--stock-green) 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(106, 127, 70, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(106, 127, 70, 0.4);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid rgba(0,0,0,0.1);
            color: var(--text-dark);
        }

        body.dark-mode .btn-outline {
            color: var(--dark-text-primary);
            border-color: var(--dark-border);
        }

        .btn-outline:hover {
            background-color: rgba(0,0,0,0.05);
        }

        body.dark-mode .btn-outline:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .btn-danger {
            background-color: #d32f2f;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c62828;
        }

        /* Account Settings Specific Styles */
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 25px;
            transition: border-color 0.3s ease;
        }

        body.dark-mode .settings-tabs {
            border-bottom-color: var(--dark-border);
        }

        .settings-tab {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            color: var(--text-dark);
            opacity: 0.7;
            transition: all 0.3s;
        }

        body.dark-mode .settings-tab {
            color: var(--dark-text-primary);
        }

        .settings-tab:hover {
            opacity: 1;
        }

        .settings-tab.active {
            border-bottom-color: var(--icon-green);
            color: var(--icon-green);
            opacity: 1;
        }

        .settings-section {
            display: none;
        }

        .settings-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: rgba(106, 127, 70, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            color: var(--icon-green);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
            border: 3px solid rgba(106, 127, 70, 0.2);
        }

        body.dark-mode .profile-avatar {
            background-color: rgba(106, 127, 70, 0.2);
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        body.dark-mode .profile-avatar:hover {
            box-shadow: 0 5px 15px var(--dark-shadow);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Loyalty Tier Styles */
        .loyalty-tier-display {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            border-radius: 8px;
            font-weight: 600;
            border: 2px solid;
            background-color: rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }

        body.dark-mode .loyalty-tier-display {
            background-color: rgba(255,255,255,0.05);
        }

        .loyalty-icon {
            font-size: 18px;
        }

        .tier-text {
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Bronze */
        .loyalty-tier-display.bronze {
            border-color: #cd7f32;
            color: #cd7f32;
            background: linear-gradient(135deg, rgba(205, 127, 50, 0.1) 0%, rgba(205, 127, 50, 0.05) 100%);
        }

        .loyalty-tier-display.bronze .loyalty-icon {
            color: #cd7f32;
        }

        /* Silver */
        .loyalty-tier-display.silver {
            border-color: #c0c0c0;
            color: #c0c0c0;
            background: linear-gradient(135deg, rgba(192, 192, 192, 0.1) 0%, rgba(192, 192, 192, 0.05) 100%);
        }

        .loyalty-tier-display.silver .loyalty-icon {
            color: #c0c0c0;
        }

        /* Gold */
        .loyalty-tier-display.gold {
            border-color: #ffd700;
            color: #ffd700;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 215, 0, 0.05) 100%);
        }

        .loyalty-tier-display.gold .loyalty-icon {
            color: #ffd700;
        }

        /* Platinum */
        .loyalty-tier-display.platinum {
            border-color: #e5e4e2;
            color: #e5e4e2;
            background: linear-gradient(135deg, rgba(229, 228, 226, 0.1) 0%, rgba(229, 228, 226, 0.05) 100%);
        }

        .loyalty-tier-display.platinum .loyalty-icon {
            color: #e5e4e2;
        }

        /* Diamond */
        .loyalty-tier-display.diamond {
            border-color: #b9f2ff;
            color: #b9f2ff;
            background: linear-gradient(135deg, rgba(185, 242, 255, 0.1) 0%, rgba(185, 242, 255, 0.05) 100%);
        }

        .loyalty-tier-display.diamond .loyalty-icon {
            color: #b9f2ff;
        }

        /* Ethereal */
        .loyalty-tier-display.ethereal {
            border-color: #9b30ff;
            color: #9b30ff;
            background: linear-gradient(135deg, rgba(155, 48, 255, 0.1) 0%, rgba(155, 48, 255, 0.05) 100%);
            box-shadow: 0 0 15px rgba(155, 48, 255, 0.3);
        }

        .loyalty-tier-display.ethereal .loyalty-icon {
            color: #9b30ff;
            text-shadow: 0 0 10px rgba(155, 48, 255, 0.5);
        }

        .loyalty-tier-display:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        body.dark-mode .loyalty-tier-display:hover {
            box-shadow: 0 5px 15px var(--dark-shadow);
        }

        .avatar-upload {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0,0,0,0.5);
            color: white;
            text-align: center;
            padding: 8px;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .profile-avatar:hover .avatar-upload {
            opacity: 1;
        }

        #avatarInput {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .profile-info h3 {
            margin-bottom: 8px;
            color: var(--text-dark);
            font-size: 24px;
            transition: color 0.3s ease;
        }

        body.dark-mode .profile-info h3 {
            color: var(--dark-text-primary);
        }

        .profile-info p {
            color: var(--text-dark);
            opacity: 0.7;
            transition: color 0.3s ease;
        }

        body.dark-mode .profile-info p {
            color: var(--dark-text-secondary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .form-group label {
            color: var(--dark-text-primary);
        }

        .form-group input, 
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: white;
            color: var(--text-dark);
        }

        body.dark-mode .form-group input,
        body.dark-mode .form-group select,
        body.dark-mode .form-group textarea {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input:focus, 
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--icon-green);
            box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
        }

        input[readonly], input[disabled] {
            background-color: rgba(0,0,0,0.03);
            cursor: not-allowed;
        }

        body.dark-mode input[readonly],
        body.dark-mode input[disabled] {
            background-color: rgba(255,255,255,0.05);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding-top: 20px;
            transition: border-color 0.3s ease;
        }

        body.dark-mode .form-actions {
            border-top-color: var(--dark-border);
        }

        .security-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.05);
        }

        body.dark-mode .security-item {
            background-color: var(--dark-bg-secondary);
            box-shadow: 0 3px 10px var(--dark-shadow);
            border-color: var(--dark-border);
        }

        .security-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-color: rgba(106, 127, 70, 0.3);
        }

        body.dark-mode .security-item:hover {
            box-shadow: 0 8px 20px var(--dark-shadow);
        }

        .security-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .security-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background-color: rgba(106, 127, 70, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--icon-green);
            font-size: 20px;
        }

        body.dark-mode .security-icon {
            background-color: rgba(106, 127, 70, 0.2);
        }

        .security-info h4 {
            margin: 0;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .security-info h4 {
            color: var(--dark-text-primary);
        }

        .security-info p {
            margin: 0;
            color: var(--text-dark);
            opacity: 0.7;
            transition: color 0.3s ease;
        }

        body.dark-mode .security-info p {
            color: var(--dark-text-secondary);
        }

        .verification-badge {
            display: inline-block;
            padding: 3px 10px;
            background-color: var(--icon-green);
            color: white;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 10px;
        }

        .delete-account {
            background-color: rgba(255, 0, 0, 0.05);
            border: 1px solid rgba(255, 0, 0, 0.1);
            padding: 25px;
            border-radius: 12px;
            margin-top: 40px;
        }

        body.dark-mode .delete-account {
            background-color: rgba(255, 0, 0, 0.1);
            border-color: rgba(255, 0, 0, 0.2);
        }

        .delete-account p {
            color: var(--text-dark);
            opacity: 0.8;
            transition: color 0.3s ease;
        }

        body.dark-mode .delete-account p {
            color: var(--dark-text-primary);
        }

        /* Security Section Heading */
        .security-section-heading {
            margin-bottom: 20px;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .security-section-heading {
            color: var(--dark-text-primary);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-color: rgba(40, 167, 69, 0.2);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-color: rgba(220, 53, 69, 0.2);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            position: relative;
            animation: slideDown 0.3s;
            transition: all 0.3s ease;
        }

        body.dark-mode .modal-content {
            background-color: var(--dark-bg-secondary);
            box-shadow: 0 10px 30px var(--dark-shadow);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            font-weight: bold;
            color: var(--text-dark);
            opacity: 0.5;
            cursor: pointer;
            transition: all 0.3s;
        }

        body.dark-mode .close-modal {
            color: var(--dark-text-primary);
        }
        
        .close-modal:hover {
            opacity: 1;
            color: var(--icon-green);
            transform: rotate(90deg);
        }
        
        .modal h3 {
            color: var(--icon-green);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 22px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
            transition: border-color 0.3s ease;
        }

        body.dark-mode .modal-footer {
            border-top-color: var(--dark-border);
        }

        /* Cropper Modal Styles */
        #avatarModal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        
        #avatarModal .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
        }

        body.dark-mode #avatarModal .modal-content {
            background-color: var(--dark-bg-secondary);
        }
        
        #avatarModal .modal-body {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        #imagePreview {
            max-width: 100%;
            max-height: 60vh;
        }
        
        .cropper-container {
            margin-bottom: 20px;
        }
        
        .cropper-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            width: 40px;
            height: 40px;
            background-color: var(--sales-orange);
            color: white;
            border-radius: 8px;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }

        /* Responsive styles */
        @media (max-width: 1200px) {
            .sidebar {
                width: 240px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                left: -100%;
                transition: all 0.3s ease;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .security-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 576px) {
            .dashboard-card {
                padding: 20px;
            }
            
            .form-actions, .modal-footer {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                <?php else: ?>
                    <?php echo $user_initials; ?>
                <?php endif; ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="user-status">
                <span class="status-indicator"></span>
                <span>Active</span>
            </div>
        </div>
        
        <!-- Language Switcher -->
        <div class="language-switcher">
            <button class="language-btn">
                <span><?php echo $language === 'en' ? 'English' : 'Filipino'; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="language-dropdown">
                <a href="?lang=en" class="language-option <?php echo $language === 'en' ? 'active' : ''; ?>">
                    <img src="img/us.png" alt="English" class="flag-icon">
                    <span>English</span>
                </a>
                <a href="?lang=tl" class="language-option <?php echo $language === 'tl' ? 'active' : ''; ?>">
                    <img src="img/ph.png" alt="Filipino" class="flag-icon">
                    <span>Filipino</span>
                </a>
            </div>
        </div>
        
        <ul class="nav-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="transaction.php"><i class="fas fa-history"></i> <?php echo $t['transaction_history']; ?></a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> <?php echo $t['schedule_pickup']; ?></a></li>
            <li><a href="prices.php"><i class="fas fa-coins"></i> <?php echo $t['current_prices']; ?></a></li>
            <li><a href="rewards.php"><i class="fas fa-award"></i> <?php echo $t['loyalty_rewards']; ?></a></li>
            <li><a href="#" class="active"><i class="fas fa-user-cog"></i> <?php echo $t['account_settings']; ?></a></li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 class="page-title"><?php echo $t['account_settings']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-card">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Settings Tabs -->
            <div class="settings-tabs">
                <div class="settings-tab active" onclick="showSection('profile')"><?php echo $t['profile']; ?></div>
                <div class="settings-tab" onclick="showSection('security')"><?php echo $t['security']; ?></div>
            </div>
            
            <!-- Profile Section -->
            <div class="settings-section active" id="profileSection">
                <div class="profile-header">
                    <div class="profile-avatar" id="avatarContainer">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                        <?php else: ?>
                            <span><?php echo $user_initials; ?></span>
                        <?php endif; ?>
                        <div class="avatar-upload">
                            <i class="fas fa-camera"></i> <?php echo $t['change']; ?>
                        </div>
                        <input type="file" id="avatarInput" accept="image/*">
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($user_name); ?></h3>
                        <p><?php echo $t['member_since']; ?> <?php echo $member_since; ?></p>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label><?php echo $t['first_name']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label><?php echo $t['last_name']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                       <div class="form-group">
                            <label><?php echo $t['username']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username'] ?? 'Not set'); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label><?php echo $t['email']; ?></label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label><?php echo $t['phone_number']; ?></label>
                            <input type="tel" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label><?php echo $t['loyalty_tier']; ?></label>
                            <div class="loyalty-tier-display <?php echo htmlspecialchars($user['loyalty_tier']); ?>">
                                <i class="loyalty-icon <?php echo $user['loyalty_tier'] === 'ethereal' ? 'fas fa-crown' : 'fas fa-medal'; ?>"></i>
                                <span class="tier-text"><?php echo htmlspecialchars(ucfirst($user['loyalty_tier'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label><?php echo $t['loyalty_points']; ?></label>
                            <input type="text" value="<?php echo number_format($user['loyalty_points']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label><?php echo $t['account_type']; ?></label>
                            <input type="text" value="<?php echo htmlspecialchars(ucfirst($user['user_type'])); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo $t['default_pickup_address']; ?></label>
                        <textarea name="address" style="min-height: 100px;"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline"><?php echo $t['cancel']; ?></button>
                        <button type="submit" name="update_profile" class="btn btn-primary"><?php echo $t['save_changes']; ?></button>
                    </div>
                </form>
            </div>
            
            <!-- Security Section -->
            <div class="settings-section" id="securitySection">
                <h3 class="security-section-heading"><?php echo $t['security_settings']; ?></h3>
                
                <div class="security-item">
                    <div class="security-info">
                        <div class="security-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h4><?php echo $t['username']; ?></h4>
                            <p><?php echo htmlspecialchars($user['username'] ?? 'Not set'); ?></p>
                        </div>
                    </div>
                    <button class="btn btn-outline" style="padding: 8px 15px;" onclick="openModal('usernameModal')"><?php echo $t['change']; ?></button>
                </div>
                
                <div class="security-item">
                    <div class="security-info">
                        <div class="security-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4><?php echo $t['email']; ?></h4>
                            <p><?php echo htmlspecialchars($user['email']); ?> 
                                <?php if ($user['is_verified']): ?>
                                    <span class="verification-badge"><?php echo $t['verified']; ?></span>
                                <?php else: ?>
                                    <span class="verification-badge" style="background-color: #dc3545;"><?php echo $t['verify_email']; ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <button class="btn btn-outline" style="padding: 8px 15px;" onclick="openModal('emailModal')"><?php echo $t['change']; ?></button>
                </div>
                
                <div class="security-item">
                    <div class="security-info">
                        <div class="security-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <h4><?php echo $t['password']; ?></h4>
                            <p><?php echo $t['last_changed']; ?> 3 <?php echo $t['months_ago']; ?> / You can't change password if you're using facebook as account</p>
                        </div>
                    </div>
                    <button class="btn btn-outline" style="padding: 8px 15px;" onclick="openModal('passwordModal')"><?php echo $t['change']; ?></button>
                </div>
                
                <div class="security-item">
                    <div class="security-info">
                        <div class="security-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div>
                            <h4><?php echo $t['phone_number']; ?></h4>
                            
                        </div>
                    </div>
                    <button class="btn btn-outline" style="padding: 8px 15px;" onclick="openModal('phoneModal')"><?php echo $t['change']; ?></button>
                </div>
                
                <div class="delete-account">
                    <h4 style="margin-top: 0; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> <?php echo $t['danger_zone']; ?></h4>
                    <p><?php echo $t['delete_account_warning']; ?></p>
                    <button type="button" class="btn btn-outline" style="border-color: #dc3545; color: #dc3545; margin-top: 10px;" onclick="openModal('deleteModal')">
                        <?php echo $t['delete_account']; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Username Modal -->
    <div id="usernameModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('usernameModal')">&times;</span>
            <h3><i class="fas fa-user"></i> <?php echo $t['change_username']; ?></h3>
            <form method="POST" action="" id="usernameForm">
                <div class="form-group">
                    <label><?php echo $t['new_username']; ?></label>
                    <input type="text" name="new_username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>
                <?php if (!$_SESSION['captcha_verified']['username']): ?>
                    <div class="g-recaptcha" data-sitekey="6LeYjuorAAAAAPbR8cTtzeaLz05h_yRz2sEfsqfO"></div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> CAPTCHA verified
                    </div>
                <?php endif; ?>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('usernameModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="change_username" class="btn btn-primary" id="usernameSubmitBtn"><?php echo $t['save_changes']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Email Modal -->
    <div id="emailModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('emailModal')">&times;</span>
            <h3><i class="fas fa-envelope"></i> <?php echo $t['change_email']; ?></h3>
            <form method="POST" action="" id="emailForm">
                <div class="form-group">
                    <label><?php echo $t['new_email']; ?></label>
                    <input type="email" name="new_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('emailModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="change_email_request" class="btn btn-primary"><?php echo $t['save_changes']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Verification Modal -->
    <div id="emailVerifyModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('emailVerifyModal')">&times;</span>
            <h3><i class="fas fa-envelope"></i> <?php echo $t['verify_email']; ?></h3>
            <p style="margin-bottom: 20px;"><?php echo $t['enter_verification_code']; ?></p>
            <form method="POST" action="" id="emailVerifyForm">
                <div class="form-group">
                    <label><?php echo $t['verification_code']; ?></label>
                    <input type="text" name="verification_code" maxlength="6" required style="text-align: center; font-size: 18px; letter-spacing: 3px; font-weight: bold;">
                </div>
                <div class="resend-code">
                    <?php echo $t['resend_code']; ?> <a href="#" onclick="resendVerificationCode()"><?php echo $t['resend_code']; ?></a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('emailVerifyModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="verify_email_code" class="btn btn-primary"><?php echo $t['verify']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('passwordModal')">&times;</span>
            <h3><i class="fas fa-lock"></i> <?php echo $t['change_password']; ?></h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label><?php echo $t['current_password']; ?></label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['new_password']; ?></label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['confirm_new_password']; ?></label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('passwordModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="change_password" class="btn btn-primary"><?php echo $t['save_changes']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Phone Modal -->
    <div id="phoneModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('phoneModal')">&times;</span>
            <h3><i class="fas fa-mobile-alt"></i> <?php echo $t['change_phone_number']; ?></h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label><?php echo $t['new_phone_number']; ?></label>
                    <input type="tel" name="new_phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('phoneModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="change_phone" class="btn btn-primary"><?php echo $t['save_changes']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('deleteModal')">&times;</span>
            <h3 style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> <?php echo $t['delete_account']; ?></h3>
            <p style="color: var(--text-dark); opacity: 0.8; margin-bottom: 15px; transition: color 0.3s ease;"><?php echo $t['delete_account_confirm']; ?></p>
            <p style="color: var(--text-dark); opacity: 0.8; margin-bottom: 15px; transition: color 0.3s ease;"><?php echo $t['delete_confirm_instruction']; ?></p>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="confirmation" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('deleteModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="delete_account" class="btn btn-danger"><?php echo $t['delete_account']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Avatar Cropping Modal -->
    <div id="avatarModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo $t['crop_profile_picture']; ?></h3>
                <span class="close" onclick="closeModal('avatarModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div id="imagePreview"></div>
                <div class="cropper-buttons">
                    <button type="button" class="btn btn-outline" onclick="closeModal('avatarModal')"><?php echo $t['cancel']; ?></button>
                    <button type="button" class="btn btn-primary" id="cropButton"><?php echo $t['crop_save']; ?></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include CropperJS library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    
    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.querySelector('.dark-mode-toggle');
        const body = document.body;

        // Check for saved dark mode preference
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        if (isDarkMode) {
            body.classList.add('dark-mode');
        }

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
        });

        // Language Dropdown
        const languageBtn = document.querySelector('.language-btn');
        const languageDropdown = document.querySelector('.language-dropdown');

        languageBtn.addEventListener('click', () => {
            languageDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!languageBtn.contains(e.target) && !languageDropdown.contains(e.target)) {
                languageDropdown.classList.remove('active');
            }
        });

        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Tab switching functionality
        function showSection(section) {
            document.querySelectorAll('.settings-section').forEach(sec => {
                sec.classList.remove('active');
            });
            
            document.querySelectorAll('.settings-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.getElementById(section + 'Section').classList.add('active');
            event.currentTarget.classList.add('active');
        }
        
        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Avatar upload and cropping functionality
        const avatarInput = document.getElementById('avatarInput');
        const avatarModal = document.getElementById('avatarModal');
        const imagePreview = document.getElementById('imagePreview');
        let cropper;

        avatarInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Destroy previous cropper instance if exists
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    // Create new image element
                    imagePreview.innerHTML = '';
                    const img = document.createElement('img');
                    img.id = 'imageToCrop';
                    img.src = e.target.result;
                    imagePreview.appendChild(img);
                    
                    // Initialize cropper
                    cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 0.8,
                        responsive: true,
                        guides: false
                    });
                    
                    // Show modal
                    openModal('avatarModal');
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Handle crop button click
        document.getElementById('cropButton').addEventListener('click', function() {
            if (cropper) {
                // Get cropped canvas
                const canvas = cropper.getCroppedCanvas({
                    width: 300,
                    height: 300,
                    minWidth: 256,
                    minHeight: 256,
                    maxWidth: 1024,
                    maxHeight: 1024,
                    fillColor: '#fff',
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });
                
                if (canvas) {
                    // Convert canvas to data URL
                    const croppedImageData = canvas.toDataURL('image/png');
                    
                    // Create hidden form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'avatar_data';
                    input.value = croppedImageData;
                    form.appendChild(input);
                    
                    const input2 = document.createElement('input');
                    input2.type = 'hidden';
                    input2.name = 'upload_avatar';
                    input2.value = '1';
                    form.appendChild(input2);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        });

        // Make sure the avatar container is clickable
        document.getElementById('avatarContainer').addEventListener('click', function(e) {
            // Only trigger if clicking on the avatar container itself, not its children
            if (e.target === this) {
                document.getElementById('avatarInput').click();
            }
        });

        // Make sure the "Change" text is clickable
        document.querySelector('.avatar-upload').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('avatarInput').click();
        });

        // Username change form validation
        document.getElementById('usernameForm').addEventListener('submit', function(e) {
            // Check if CAPTCHA is required and not verified
            const captchaRequired = <?php echo $_SESSION['captcha_verified']['username'] ? 'false' : 'true'; ?>;
            
            if (captchaRequired) {
                const captchaResponse = grecaptcha.getResponse();
                if (!captchaResponse) {
                    alert('<?php echo $t['captcha_required']; ?>');
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Function to resend verification code
        function resendVerificationCode() {
            // This would typically make an AJAX request to resend the code
            alert('Verification code has been resent to your email.');
        }

        // Auto-open email verification modal if there's a pending email
        <?php if (isset($_SESSION['pending_email'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                openModal('emailVerifyModal');
            });
        <?php endif; ?>
    </script>
</body>
</html>
<?php
// Close prepared statements
if (isset($user_stmt)) mysqli_stmt_close($user_stmt);
if (isset($update_stmt)) mysqli_stmt_close($update_stmt);
if (isset($delete_stmt)) mysqli_stmt_close($delete_stmt);
if (isset($update_pw_stmt)) mysqli_stmt_close($update_pw_stmt);
if (isset($update_phone_stmt)) mysqli_stmt_close($update_phone_stmt);
if (isset($update_img_stmt)) mysqli_stmt_close($update_img_stmt);
// Close connection
mysqli_close($conn);
?>