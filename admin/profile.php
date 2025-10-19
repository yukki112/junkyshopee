<?php
session_start();
require_once 'db_connection.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

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
        'admin_profile' => 'Admin Profile',
        'profile' => 'Profile',
        'security' => 'Security',
        'admin_tools' => 'Admin Tools',
        'administrator' => 'Administrator',
        'change_password' => 'Change Password',
        'email_address' => 'Email Address',
        'api_access' => 'API Access',
        'two_factor_auth' => 'Two-Factor Authentication',
        'delete_account' => 'Delete Account',
        'danger_zone' => 'Danger Zone',
        'database_management' => 'Database Management',
        'user_permissions' => 'User Permissions',
        'system_configuration' => 'System Configuration',
        'developer_tools' => 'Developer Tools',
        'notification_settings' => 'Notification Settings',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Users',
        'pricing_control' => 'Pricing Control',
        'reports_analytics' => 'Reports & Analytics',
        'transactions' => 'Transactions',
        'loyalty_program' => 'Loyalty Program',
      
        'logout' => 'Logout'
    ],
    'tl' => [
        'admin_profile' => 'Admin Profile',
        'profile' => 'Profile',
        'security' => 'Seguridad',
        'admin_tools' => 'Mga Tool ng Admin',
        'administrator' => 'Administrator',
        'change_password' => 'Palitan ang Password',
        'email_address' => 'Email Address',
        'api_access' => 'API Access',
        'two_factor_auth' => 'Two-Factor Authentication',
        'delete_account' => 'Delete Account',
        'danger_zone' => 'Danger Zone',
        'database_management' => 'Pamamahala ng Database',
        'user_permissions' => 'Mga Pahintulot ng User',
        'system_configuration' => 'Pagsasaayos ng System',
        'developer_tools' => 'Mga Tool ng Developer',
        'notification_settings' => 'Mga Setting ng Notification',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Mga User',
        'pricing_control' => 'Kontrol sa Presyo',
        'reports_analytics' => 'Mga Ulat at Analytics',
        'transactions' => 'Mga Transaksyon',
        'loyalty_program' => 'Programa ng Loyalty',
     
        'logout' => 'Logout'
    ]
];

$t = $translations[$language];

// Get current user ID and info
$user_id = $_SESSION['user_id'];
$user_query = "SELECT id, first_name, last_name, email, phone, address, profile_image, user_type, created_at, is_admin, last_login FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['is_admin']) {
    session_destroy();
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

// Get security data (API key, 2FA status)
$security_query = "SELECT api_key, two_factor_auth, two_factor_secret FROM users WHERE id = ?";
$security_stmt = $conn->prepare($security_query);
$security_stmt->execute([$user_id]);
$security_data = $security_stmt->fetch(PDO::FETCH_ASSOC);

if (!$security_data) {
    $security_data = ['api_key' => '', 'two_factor_auth' => 0, 'two_factor_secret' => ''];
}

// Get system settings for admin tools
$settings_query = "SELECT setting_key, setting_value FROM system_settings";
$settings_stmt = $conn->query($settings_query);
$system_settings = [];
while ($row = $settings_stmt->fetch(PDO::FETCH_ASSOC)) {
    $system_settings[$row['setting_key']] = $row['setting_value'];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        
        $update_query = "UPDATE users SET address = ?, phone = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([$address, $phone, $user_id]);
        
        // Refresh user data
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
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
                $update_img_stmt = $conn->prepare($update_img_query);
                $update_img_stmt->execute([$filepath, $user_id]);
                
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
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->execute([$user_id]);
            
            // Delete profile image if exists
            if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                @unlink($user['profile_image']);
            }
            
            // Logout and redirect
            session_destroy();
            header("Location: ../Customer-portal/Login/Login.php");
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
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([$user_id]);
        $check_data = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($current_password, $check_data['password_hash'])) {
            if ($new_password === $confirm_password) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pw_query = "UPDATE users SET password_hash = ? WHERE id = ?";
                $update_pw_stmt = $conn->prepare($update_pw_query);
                $update_pw_stmt->execute([$hashed_password, $user_id]);
                
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "New passwords don't match";
            }
        } else {
            $error_message = "Current password is incorrect";
        }
    }
    elseif (isset($_POST['update_email'])) {
        $new_email = $_POST['new_email'];
        
        // Check if email already exists
        $email_check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_check_stmt = $conn->prepare($email_check_query);
        $email_check_stmt->execute([$new_email, $user_id]);
        
        if ($email_check_stmt->rowCount()) {
            $error_message = "Email address is already in use by another account";
        } else {
            $update_email_query = "UPDATE users SET email = ? WHERE id = ?";
            $update_email_stmt = $conn->prepare($update_email_query);
            $update_email_stmt->execute([$new_email, $user_id]);
            
            // Refresh user data
            $user['email'] = $new_email;
            $success_message = "Email address updated successfully!";
        }
    }
    elseif (isset($_POST['generate_api_key'])) {
        $api_key = bin2hex(random_bytes(32));
        
        $update_api_query = "UPDATE users SET api_key = ? WHERE id = ?";
        $update_api_stmt = $conn->prepare($update_api_query);
        $update_api_stmt->execute([$api_key, $user_id]);
        
        // Refresh security data
        $security_stmt->execute([$user_id]);
        $security_data = $security_stmt->fetch(PDO::FETCH_ASSOC);
        
        $success_message = "New API key generated successfully!";
    }
    elseif (isset($_POST['revoke_api_key'])) {
        $update_api_query = "UPDATE users SET api_key = NULL WHERE id = ?";
        $update_api_stmt = $conn->prepare($update_api_query);
        $update_api_stmt->execute([$user_id]);
        
        // Refresh security data
        $security_stmt->execute([$user_id]);
        $security_data = $security_stmt->fetch(PDO::FETCH_ASSOC);
        
        $success_message = "API key revoked successfully!";
    }
    elseif (isset($_POST['enable_2fa'])) {
        // Generate a random 2FA secret (16 characters)
        $two_factor_secret = strtoupper(bin2hex(random_bytes(8)));
        
        $update_2fa_query = "UPDATE users SET two_factor_auth = 1, two_factor_secret = ? WHERE id = ?";
        $update_2fa_stmt = $conn->prepare($update_2fa_query);
        $update_2fa_stmt->execute([$two_factor_secret, $user_id]);
        
        // Send confirmation email
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
            $mail->setFrom('Stephenviray12@gmail.com', 'JunkValue Security');
            $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Two-Factor Authentication Enabled';
            $mail->Body    = "Hi {$user['first_name']},<br><br>"
                            . "Two-factor authentication has been successfully enabled on your JunkValue account.<br><br>"
                            . "From now on, you'll receive a verification code via email when you log in.<br><br>"
                            . "If you didn't enable this feature, please contact us immediately.<br><br>"
                            . "Thanks,<br>The JunkValue Security Team";
            
            $mail->send();
        } catch (Exception $e) {
            // Continue even if email fails
        }
        
        // Refresh security data
        $security_stmt->execute([$user_id]);
        $security_data = $security_stmt->fetch(PDO::FETCH_ASSOC);
        
        $success_message = "Two-factor authentication enabled successfully! You'll receive a code via email when logging in.";
    }
    elseif (isset($_POST['disable_2fa'])) {
        $update_2fa_query = "UPDATE users SET two_factor_auth = 0, two_factor_secret = NULL WHERE id = ?";
        $update_2fa_stmt = $conn->prepare($update_2fa_query);
        $update_2fa_stmt->execute([$user_id]);
        
        // Refresh security data
        $security_stmt->execute([$user_id]);
        $security_data = $security_stmt->fetch(PDO::FETCH_ASSOC);
        
        $success_message = "Two-factor authentication disabled successfully!";
    }
}

// Check for admin messages from handler
if (isset($_SESSION['admin_message'])) {
    if ($_SESSION['admin_message_type'] === 'success') {
        $success_message = $_SESSION['admin_message'];
    } else {
        $error_message = $_SESSION['admin_message'];
    }
    unset($_SESSION['admin_message']);
    unset($_SESSION['admin_message_type']);
}

// Format user data for display
$admin_name = $user['first_name'] . ' ' . $user['last_name'];
$admin_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$member_since = date('F Y', strtotime($user['created_at']));

// Handle last login display
if (!empty($user['last_login'])) {
    $last_login = date('F j, Y \a\t g:i A', strtotime($user['last_login']));
} else {
    $last_login = "Never logged in";
}

// Update last login
$update_login = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$update_login->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - JunkValue</title>
     <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
            --primary-green: #6A7F46;
            --bg-light: #f8f9fa;
            
            /* Dark mode variables */
            --dark-bg-primary: #1a1a1a;
            --dark-bg-secondary: #2d2d2d;
            --dark-bg-tertiary: #3c3c3c;
            --dark-text-primary: #ffffff;
            --dark-text-secondary: #e0e0e0;
            --dark-border: #404040;
            --dark-shadow: rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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

        /* Sidebar - Admin Version */
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

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .user-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
            font-family: 'Inter', sans-serif;
            text-align: center;
            letter-spacing: 0.5px;
            color: white;
        }

        .user-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--panel-cream);
            opacity: 0.8;
            font-weight: 500;
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
            font-size: 14px;
            text-decoration: none;
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
            font-weight: 800;
            font-family: 'Inter', sans-serif;
            color: var(--topbar-brown);
            position: relative;
            display: inline-block;
            letter-spacing: 0.5px;
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
            color: var(--dark-text-primary);
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

        body.dark-mode .view-all {
            color: var(--dark-text-secondary);
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
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .btn-outline:hover {
            background-color: rgba(0,0,0,0.05);
        }

        body.dark-mode .btn-outline:hover {
            background-color: var(--dark-border);
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

        body.dark-mode .settings-tab.active {
            color: var(--dark-text-primary);
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

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            background-color: var(--dark-bg-primary);
            color: var(--dark-text-secondary);
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
            border-color: var(--dark-border);
            box-shadow: 0 3px 10px var(--dark-shadow);
            color: var(--dark-text-primary);
        }

        .security-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-color: rgba(106, 127, 70, 0.3);
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

        /* API Key Styles */
        .api-key-container {
            background: linear-gradient(135deg, rgba(106, 127, 70, 0.05), rgba(106, 127, 70, 0.1));
            border: 2px dashed rgba(106, 127, 70, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            position: relative;
        }

        body.dark-mode .api-key-container {
            background: linear-gradient(135deg, rgba(106, 127, 70, 0.1), rgba(106, 127, 70, 0.15));
            border-color: rgba(106, 127, 70, 0.4);
        }

        .api-key {
            font-family: 'Courier New', monospace;
            word-break: break-all;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.1);
            font-size: 14px;
            color: var(--text-dark);
            font-weight: 600;
            letter-spacing: 1px;
        }

        body.dark-mode .api-key {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .copy-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: var(--primary-green);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .copy-btn:hover {
            background-color: #5a6f36;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(106, 127, 70, 0.3);
        }

        /* 2FA Styles */
        .two-factor-setup {
            background: linear-gradient(135deg, rgba(106, 127, 70, 0.03), rgba(106, 127, 70, 0.08));
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid rgba(106, 127, 70, 0.2);
        }

        body.dark-mode .two-factor-setup {
            background: linear-gradient(135deg, rgba(106, 127, 70, 0.1), rgba(106, 127, 70, 0.15));
            border-color: rgba(106, 127, 70, 0.3);
        }

        .two-factor-setup h4 {
            color: var(--primary-green);
            margin: 20px 0 10px 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        body.dark-mode .two-factor-setup h4 {
            color: var(--dark-text-primary);
        }

        .two-factor-setup h4:first-child {
            margin-top: 0;
        }

        .two-factor-setup h4::before {
            content: '';
            width: 30px;
            height: 30px;
            background-color: var(--primary-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }

        .two-factor-setup h4:nth-of-type(1)::before {
            content: '1';
        }

        .two-factor-setup h4:nth-of-type(2)::before {
            content: '2';
        }

        .two-factor-setup p {
            color: #555;
            line-height: 1.6;
            margin: 10px 0;
        }

        body.dark-mode .two-factor-setup p {
            color: var(--dark-text-secondary);
        }

        .info-box {
            background-color: rgba(106, 127, 70, 0.1);
            border-left: 4px solid var(--primary-green);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        body.dark-mode .info-box {
            background-color: rgba(106, 127, 70, 0.15);
        }

        .info-box i {
            color: var(--primary-green);
            margin-right: 10px;
        }

        .info-box p {
            margin: 0;
            color: var(--text-dark);
            font-size: 14px;
        }

        body.dark-mode .info-box p {
            color: var(--dark-text-primary);
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
            background-color: rgba(0,0,0,0.6);
            animation: fadeIn 0.3s;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            animation: slideDown 0.4s ease-out;
            overflow: hidden;
        }

        body.dark-mode .modal-content {
            background-color: var(--dark-bg-secondary);
            color: var(--dark-text-primary);
        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-green), #5a8a3a);
        }

        .modal-header {
            padding: 30px 30px 20px;
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }

        body.dark-mode .modal-header {
            border-bottom-color: var(--dark-border);
        }

        .modal-body {
            padding: 30px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 20px 30px;
            border-top: 2px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            background-color: rgba(0,0,0,0.02);
        }

        body.dark-mode .modal-footer {
            border-top-color: var(--dark-border);
            background-color: var(--dark-bg-primary);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from { transform: translateY(-100px) scale(0.9); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }
        
        .close-modal {
            position: absolute;
            top: 25px;
            right: 25px;
            font-size: 28px;
            font-weight: bold;
            color: var(--text-dark);
            opacity: 0.4;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        body.dark-mode .close-modal {
            color: var(--dark-text-primary);
        }
        
        .close-modal:hover {
            opacity: 1;
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            transform: rotate(90deg);
        }
        
        .modal h3 {
            color: var(--icon-green);
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 600;
        }

        body.dark-mode .modal h3 {
            color: var(--dark-text-primary);
        }

        .modal h3 i {
            font-size: 28px;
        }

        .modal-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        body.dark-mode .modal-description {
            color: var(--dark-text-secondary);
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

        /* Admin Specific Styles */
        .admin-features {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
            transition: border-color 0.3s ease;
        }

        body.dark-mode .admin-features {
            border-top-color: var(--dark-border);
        }

        .admin-feature {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        body.dark-mode .admin-feature {
            background-color: var(--dark-bg-secondary);
            border-color: var(--dark-border);
            box-shadow: 0 3px 10px var(--dark-shadow);
            color: var(--dark-text-primary);
        }

        .admin-feature h4 {
            margin-bottom: 10px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: color 0.3s ease;
        }

        body.dark-mode .admin-feature h4 {
            color: var(--dark-text-primary);
        }

        .admin-feature p {
            color: var(--text-dark);
            opacity: 0.7;
            margin-bottom: 15px;
            transition: color 0.3s ease;
        }

        body.dark-mode .admin-feature p {
            color: var(--dark-text-secondary);
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

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }

            .modal-header, .modal-body, .modal-footer {
                padding: 20px;
            }

            .modal h3 {
                font-size: 20px;
            }

            .copy-btn {
                position: static;
                width: 100%;
                margin-top: 10px;
                justify-content: center;
            }

            .api-key-container {
                padding-bottom: 60px;
            }
        }

        @media (max-width: 576px) {
            .card {
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
                    <?php echo $admin_initials; ?>
                <?php endif; ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
            <div class="user-status">
                <span class="status-indicator"></span>
                <span><?php echo $t['administrator']; ?></span>
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
            <li><a href="inventory.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory']; ?></a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> <?php echo $t['users']; ?></a></li>
            <li><a href="pricing.php"><i class="fas fa-tags"></i> <?php echo $t['pricing_control']; ?></a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> <?php echo $t['reports_analytics']; ?></a></li>
            <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> <?php echo $t['transactions']; ?></a></li>
             <li><a href="loyalty.php" ><i class="fas fa-award"></i> <?php echo $t['loyalty_program']; ?></a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-user"></i> <?php echo $t['profile']; ?></a></li>
           
        </ul>
        
        <div class="sidebar-footer">
            <a href="../Customer-portal/Login/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 class="page-title"><?php echo $t['admin_profile']; ?></h1>
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
                <div class="settings-tab" onclick="showSection('admin')"><?php echo $t['admin_tools']; ?></div>
            </div>
            
            <!-- Profile Section -->
            <div class="settings-section active" id="profileSection">
                <div class="profile-header">
                    <div class="profile-avatar" id="avatarContainer">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                        <?php else: ?>
                            <span><?php echo $admin_initials; ?></span>
                        <?php endif; ?>
                        <div class="avatar-upload">
                            <i class="fas fa-camera"></i> Change
                        </div>
                        <input type="file" id="avatarInput" accept="image/*">
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($admin_name); ?></h3>
                        <p>Administrator since <?php echo $member_since; ?></p>
                        <p>Last login: <?php echo $last_login; ?></p>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" style="min-height: 100px;"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline">Cancel</button>
                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
            
            <!-- Security Section -->
            <div class="settings-section" id="securitySection">
                <h3 style="margin-bottom: 20px;"><?php echo $t['security']; ?></h3>
                
                <div class="security-item">
                    <div class="security-info">
                        <div class="security-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;"><?php echo $t['change_password']; ?></h4>
                            <p style="margin: 0; opacity: 0.7;">Last changed 3 months ago</p>
                        </div>
                    </div>
                    <button class="btn btn-outline" style="padding: 8px 15px;" onclick="openModal('passwordModal')">Change</button>
                </div>
                
                <div class="security-item">
                    <div class="security-info">
                        <div class="security-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;"><?php echo $t['email_address']; ?></h4>
                            <p style="margin: 0; opacity: 0.7;"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    <button class="btn btn-outline" style="padding: 8px 15px;" onclick="openModal('emailModal')">Change</button>
                </div>
                
                <!-- Updated API Access section with working functionality -->
                <div class="security-item">
                    <div class="security-info">
                        <div class="security-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;"><?php echo $t['api_access']; ?></h4>
                            <p style="margin: 0; opacity: 0.7;">
                                <?php echo !empty($security_data['api_key']) ? 'API key active' : 'No API key generated'; ?>
                            </p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <?php if (!empty($security_data['api_key'])): ?>
                            <button class="btn btn-outline" style="padding: 8px 15px;" onclick="openModal('apiKeyModal')">
                                <i class="fas fa-eye"></i> View Key
                            </button>
                            <form method="POST" style="display: inline;">
                                <button type="submit" class="btn btn-danger" style="padding: 8px 15px;" name="revoke_api_key" 
                                        onclick="return confirm('Are you sure you want to revoke this API key? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i> Revoke
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <button type="submit" class="btn btn-primary" style="padding: 8px 15px;" name="generate_api_key">
                                    <i class="fas fa-plus"></i> Generate Key
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Updated Two-Factor Authentication section -->
                <div class="security-item">
                    <div class="security-info">
                        <div class="security-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;"><?php echo $t['two_factor_auth']; ?></h4>
                            <p style="margin: 0; opacity: 0.7;">
                                <?php if (!empty($security_data['two_factor_auth'])): ?>
                                    <span style="color: #28a745; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i> Enabled
                                    </span>
                                <?php else: ?>
                                    <span style="color: #dc3545;">
                                        <i class="fas fa-times-circle"></i> Disabled
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <?php if (!empty($security_data['two_factor_auth'])): ?>
                            <form method="POST" style="display: inline;">
                                <button type="submit" class="btn btn-danger" style="padding: 8px 15px;" name="disable_2fa"
                                        onclick="return confirm('Are you sure you want to disable two-factor authentication?')">
                                    <i class="fas fa-times"></i> Disable
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-primary" style="padding: 8px 15px;" onclick="openModal('enable2faModal')">
                                <i class="fas fa-shield-alt"></i> Enable
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="delete-account">
                    <h4 style="margin-top: 0; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> <?php echo $t['danger_zone']; ?></h4>
                    <p style="opacity: 0.8;">Once you delete your account, there is no going back. Please be certain.</p>
                    <button type="button" class="btn btn-outline" style="border-color: #dc3545; color: #dc3545; margin-top: 10px;" onclick="openModal('deleteModal')">
                        <?php echo $t['delete_account']; ?>
                    </button>
                </div>
            </div>
            
            <!-- Admin Tools Section -->
            <div class="settings-section" id="adminSection">
                <h3 style="margin-bottom: 20px;"><?php echo $t['admin_tools']; ?></h3>
                
                <div class="admin-features">
                    <div class="admin-feature">
                        <h4><i class="fas fa-database"></i> <?php echo $t['database_management']; ?></h4>
                        <p>Backup, restore, or optimize your application database. Handle with care as these operations can affect all users.</p>
                        <button class="btn btn-outline" onclick="openModal('databaseModal')">
                            <i class="fas fa-cog"></i> Manage Database
                        </button>
                    </div>
                    
                    <div class="admin-feature">
                        <h4><i class="fas fa-users-cog"></i> <?php echo $t['user_permissions']; ?></h4>
                        <p>Configure role-based access control and permissions for different user types in the system.</p>
                        <button class="btn btn-outline" onclick="openModal('permissionsModal')">
                            <i class="fas fa-user-shield"></i> Manage Permissions
                        </button>
                    </div>
                    
                    <div class="admin-feature">
                        <h4><i class="fas fa-server"></i> <?php echo $t['system_configuration']; ?></h4>
                        <p>Adjust system-wide settings, application parameters, and server configurations.</p>
                        <button class="btn btn-outline" onclick="openModal('systemConfigModal')">
                            <i class="fas fa-sliders-h"></i> System Settings
                        </button>
                    </div>
                    
                    <div class="admin-feature">
                        <h4><i class="fas fa-code"></i> <?php echo $t['developer_tools']; ?></h4>
                        <p>Access developer tools, API documentation, and system logs for debugging purposes.</p>
                        <button class="btn btn-outline" onclick="openModal('devToolsModal')">
                            <i class="fas fa-terminal"></i> Open Developer Tools
                        </button>
                    </div>
                    
                    <div class="admin-feature">
                        <h4><i class="fas fa-bell"></i> <?php echo $t['notification_settings']; ?></h4>
                        <p>Configure email and system notifications for administrators and users.</p>
                        <button class="btn btn-outline" onclick="openModal('notificationsModal')">
                            <i class="fas fa-envelope"></i> Notification Preferences
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('passwordModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-lock"></i> <?php echo $t['change_password']; ?></h3>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('passwordModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Email Modal -->
    <div id="emailModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('emailModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-envelope"></i> <?php echo $t['email_address']; ?></h3>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label>New Email Address</label>
                        <input type="email" name="new_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('emailModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" name="update_email" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- API Key Modal -->
    <!-- Enhanced API Key Modal -->
    <div id="apiKeyModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('apiKeyModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-key"></i> <?php echo $t['api_access']; ?></h3>
                <p class="modal-description">
                    This key provides full access to your account through the API. Keep it secure and don't share it with anyone.
                </p>
            </div>
            <div class="modal-body">
                <div class="info-box">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p><strong>Security Warning:</strong> Treat this key like a password. Anyone with this key can access your account data.</p>
                </div>
                <div class="api-key-container">
                    <div class="api-key"><?php echo htmlspecialchars($security_data['api_key'] ?? ''); ?></div>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($security_data['api_key'] ?? ''); ?>')">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('apiKeyModal')">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Enable 2FA Modal -->
    <!-- Enhanced Enable 2FA Modal with better design -->
    <div id="enable2faModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('enable2faModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-shield-alt"></i> <?php echo $t['two_factor_auth']; ?></h3>
                <p class="modal-description">
                    Add an extra layer of security to your account. You'll receive a verification code via email each time you log in.
                </p>
            </div>
            <div class="modal-body">
                <div class="two-factor-setup">
                    <h4>How It Works</h4>
                    <p>When you log in to your account, we'll send a unique 6-digit verification code to your registered email address. You'll need to enter this code to complete the login process.</p>
                    
                    <div class="info-box" style="margin-top: 20px;">
                        <i class="fas fa-envelope"></i>
                        <p><strong>Your Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <h4>Benefits</h4>
                    <ul style="color: #555; line-height: 2; margin-left: 20px;">
                        <li><i class="fas fa-check" style="color: var(--primary-green); margin-right: 8px;"></i> Protects your account from unauthorized access</li>
                        <li><i class="fas fa-check" style="color: var(--primary-green); margin-right: 8px;"></i> Instant email notifications for login attempts</li>
                        <li><i class="fas fa-check" style="color: var(--primary-green); margin-right: 8px;"></i> Easy to use - no additional apps required</li>
                        <li><i class="fas fa-check" style="color: var(--primary-green); margin-right: 8px;"></i> Can be disabled anytime from your profile</li>
                    </ul>

                    <div class="info-box" style="margin-top: 20px; background-color: rgba(255, 193, 7, 0.1); border-left-color: #ffc107;">
                        <i class="fas fa-info-circle" style="color: #ffc107;"></i>
                        <p><strong>Note:</strong> Make sure you have access to your email account before enabling this feature.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('enable2faModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="enable_2fa" class="btn btn-primary">
                        <i class="fas fa-shield-alt"></i> Enable 2FA
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('deleteModal')">&times;</span>
            <div class="modal-header">
                <h3 style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> <?php echo $t['delete_account']; ?></h3>
            </div>
            <div class="modal-body">
                <p style="opacity: 0.8; margin-bottom: 15px;">This action cannot be undone. This will permanently delete your account and all associated data.</p>
                <p style="opacity: 0.8; margin-bottom: 15px;">To confirm, please type <strong>DELETE</strong> in the box below:</p>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="text" name="confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('deleteModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" name="delete_account" class="btn btn-danger">
                        <i class="fas fa-trash"></i> <?php echo $t['delete_account']; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Database Management Modal -->
    <div id="databaseModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('databaseModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-database"></i> <?php echo $t['database_management']; ?></h3>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Create Database Backup</label>
                    <p style="opacity: 0.7; margin-top: 5px;">Generate a complete backup of the system database.</p>
                    <button class="btn btn-primary" style="width: 100%;" onclick="performDatabaseAction('backup')">
                        <i class="fas fa-download"></i> Download Backup
                    </button>
                </div>
                
                <!-- Enhanced restore form with proper file upload handling -->
                <div class="form-group">
                    <label>Restore Database</label>
                    <p style="opacity: 0.7; margin-top: 5px;">Upload a database backup file to restore. <strong>Warning:</strong> This will replace all current data!</p>
                    <form id="restoreForm" method="POST" action="admin_settings_handler.php" enctype="multipart/form-data">
                        <input type="file" class="form-control" id="restoreFile" name="restore_file" accept=".sql" required>
                        <button type="submit" class="btn btn-outline" style="width: 100%; margin-top: 10px;" onclick="return confirmRestore()">
                            <i class="fas fa-upload"></i> Upload & Restore
                        </button>
                    </form>
                </div>
                
                <div class="form-group">
                    <label>Optimize Database</label>
                    <p style="opacity: 0.7; margin-top: 5px;">Run optimization routines to improve database performance.</p>
                    <button class="btn btn-outline" style="width: 100%;" onclick="performDatabaseAction('optimize')">
                        <i class="fas fa-magic"></i> Optimize Now
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('databaseModal')">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Permissions Modal -->
    <div id="permissionsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('permissionsModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-users-cog"></i> <?php echo $t['user_permissions']; ?></h3>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>User Roles</label>
                    <select class="form-control">
                        <option>Administrator</option>
                        <option>Manager</option>
                        <option>Staff</option>
                        <option>Customer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Permissions</label>
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; padding: 10px;">
                        <label style="display: block; padding: 8px;">
                            <input type="checkbox"> Access Dashboard
                        </label>
                        <label style="display: block; padding: 8px;">
                            <input type="checkbox"> Manage Inventory
                        </label>
                        <label style="display: block; padding: 8px;">
                            <input type="checkbox"> Manage Users
                        </label>
                        <label style="display: block; padding: 8px;">
                            <input type="checkbox"> View Reports
                        </label>
                        <label style="display: block; padding: 8px;">
                            <input type="checkbox"> System Configuration
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('permissionsModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Permissions
                </button>
            </div>
        </div>
    </div>

    <!-- System Config Modal -->
    <div id="systemConfigModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('systemConfigModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-server"></i> <?php echo $t['system_configuration']; ?></h3>
            </div>
            <div class="modal-body">
                <form id="systemConfigForm">
                    <div class="form-group">
                        <label>Application Name</label>
                        <input type="text" name="app_name" class="form-control" value="<?php echo htmlspecialchars($system_settings['app_name'] ?? 'JunkValue'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Maintenance Mode</label>
                        <select name="maintenance_mode" class="form-control">
                            <option value="0" <?php echo ($system_settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : ''; ?>>Disabled</option>
                            <option value="1" <?php echo ($system_settings['maintenance_mode'] ?? '0') == '1' ? 'selected' : ''; ?>>Enabled</option>
                        </select>
                        <p style="opacity: 0.7; margin-top: 5px; font-size: 13px;">
                            When enabled, only administrators can access the system. All other users will see a maintenance page.
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label>Debug Mode</label>
                        <select name="debug_mode" class="form-control">
                            <option value="0" <?php echo ($system_settings['debug_mode'] ?? '0') == '0' ? 'selected' : ''; ?>>Disabled</option>
                            <option value="1" <?php echo ($system_settings['debug_mode'] ?? '0') == '1' ? 'selected' : ''; ?>>Enabled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Default Timezone</label>
                        <select name="timezone" class="form-control">
                            <option value="Asia/Manila" <?php echo ($system_settings['timezone'] ?? 'Asia/Manila') == 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila</option>
                            <option value="UTC" <?php echo ($system_settings['timezone'] ?? 'Asia/Manila') == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            <option value="America/New_York" <?php echo ($system_settings['timezone'] ?? 'Asia/Manila') == 'America/New_York' ? 'selected' : ''; ?>>America/New_York</option>
                            <option value="Europe/London" <?php echo ($system_settings['timezone'] ?? 'Asia/Manila') == 'Europe/London' ? 'selected' : ''; ?>>Europe/London</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('systemConfigModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveSystemConfig()">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </div>
        </div>
    </div>

    <!-- Developer Tools Modal -->
    <div id="devToolsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('devToolsModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-code"></i> <?php echo $t['developer_tools']; ?></h3>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>System Logs</label>
                    <select class="form-control">
                        <option>Application Logs</option>
                        <option>Error Logs</option>
                        <option>Access Logs</option>
                        <option>Database Logs</option>
                    </select>
                    <textarea class="form-control" rows="10" style="margin-top: 10px; font-family: monospace;" readonly>
[2023-01-01 12:00:00] INFO: System started
[2023-01-01 12:05:23] INFO: User admin logged in
[2023-01-01 12:30:45] WARNING: Inventory low on item ABC123
                    </textarea>
                </div>
                
                <div class="form-group">
                    <label>API Documentation</label>
                    <button class="btn btn-outline" style="width: 100%;">
                        <i class="fas fa-book"></i> View API Documentation
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('devToolsModal')">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications Modal -->
    <div id="notificationsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('notificationsModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-bell"></i> <?php echo $t['notification_settings']; ?></h3>
            </div>
            <div class="modal-body">
                <form id="notificationsForm">
                    <div class="form-group">
                        <label>Email Notifications</label>
                        <div style="border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; padding: 10px;">
                            <label style="display: block; padding: 8px;">
                                <input type="checkbox" name="email_system_alerts" checked> System alerts
                            </label>
                            <label style="display: block; padding: 8px;">
                                <input type="checkbox" name="email_inventory_warnings" checked> Inventory warnings
                            </label>
                            <label style="display: block; padding: 8px;">
                                <input type="checkbox" name="email_user_activities" checked> User activities
                            </label>
                            <label style="display: block; padding: 8px;">
                                <input type="checkbox" name="email_backup_reminders" checked> Backup reminders
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>In-App Notifications</label>
                        <div style="border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; padding: 10px;">
                            <label style="display: block; padding: 8px;">
                                <input type="checkbox" name="app_new_transactions" checked> New transactions
                            </label>
                            <label style="display: block; padding: 8px;">
                                <input type="checkbox" name="app_system_updates" checked> System updates
                            </label>
                            <label style="display: block; padding: 8px;">
                                <input type="checkbox" name="app_scheduled_tasks" checked> Scheduled tasks
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('notificationsModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveNotifications()">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Avatar Cropping Modal -->
    <div id="avatarModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Crop Profile Picture</h3>
                <span class="close-modal" onclick="closeModal('avatarModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div id="imagePreview"></div>
                <div class="cropper-buttons">
                    <button type="button" class="btn btn-outline" onclick="closeModal('avatarModal')">Cancel</button>
                    <button type="button" class="btn btn-primary" id="cropButton">Crop & Save</button>
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
        
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const btn = event.target.closest('.copy-btn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.style.backgroundColor = '#28a745';
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.style.backgroundColor = '';
                }, 2000);
            }).catch(function(err) {
                alert('Failed to copy: ' + err);
            });
        }
        
        function saveSystemConfig() {
            const form = document.getElementById('systemConfigForm');
            const formData = new FormData(form);
            formData.append('action', 'update_system_config');
            formData.append('ajax', '1');
            
            fetch('admin_settings_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeModal('systemConfigModal');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error saving configuration: ' + error);
            });
        }
        
        function performDatabaseAction(action) {
    let confirmMessage = '';
    
    if (action === 'backup') {
        confirmMessage = 'Create a backup of the database?';
    } else if (action === 'optimize') {
        confirmMessage = 'Optimize the database? This may take a few moments.';
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Show loading indicator
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loadingIndicator';
    loadingDiv.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.8); color: white; padding: 20px; border-radius: 10px; z-index: 10000;';
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    document.body.appendChild(loadingDiv);
    
    const formData = new FormData();
    formData.append('action', action + '_database');
    formData.append('ajax', '1');
    
    fetch('admin_settings_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading indicator
        const loading = document.getElementById('loadingIndicator');
        if (loading) loading.remove();
        
        if (data.success) {
            alert(data.message);
            
            // <CHANGE> Fixed to use download_url instead of file
            if (action === 'backup' && data.download_url) {
                const link = document.createElement('a');
                link.href = data.download_url;
                link.download = data.file;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        // Remove loading indicator
        const loading = document.getElementById('loadingIndicator');
        if (loading) loading.remove();
        
        alert('Error performing database action: ' + error);
    });
}
        
        function confirmRestore() {
            const fileInput = document.getElementById('restoreFile');
            
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Please select a SQL file to restore');
                return false;
            }
            
            const fileName = fileInput.files[0].name;
            const fileExt = fileName.split('.').pop().toLowerCase();
            
            if (fileExt !== 'sql') {
                alert('Please select a valid SQL file');
                return false;
            }
            
            return confirm('WARNING: This will replace ALL current database data with the backup file.\n\nFile: ' + fileName + '\n\nAre you absolutely sure you want to continue?');
        }
        
        function saveNotifications() {
            const form = document.getElementById('notificationsForm');
            const formData = new FormData(form);
            formData.append('action', 'update_notifications');
            formData.append('ajax', '1');
            
            fetch('admin_settings_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeModal('notificationsModal');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error saving notifications: ' + error);
            });
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

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);
    </script>
</body>
</html>
<?php
// Close prepared statements (not necessary in PDO, but if you want to explicitly null them)
if (isset($user_stmt)) $user_stmt = null;
if (isset($update_stmt)) $update_stmt = null;
if (isset($delete_stmt)) $delete_stmt = null;
if (isset($update_pw_stmt)) $update_pw_stmt = null;
if (isset($update_email_stmt)) $update_email_stmt = null;
if (isset($update_img_stmt)) $update_img_stmt = null;
if (isset($update_api_stmt)) $update_api_stmt = null;
if (isset($update_2fa_stmt)) $update_2fa_stmt = null;
// Close connection
$conn = null;
?>