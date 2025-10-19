<?php
session_start();
require_once 'db_connection.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$sql = "SELECT e.*, er.role_name 
        FROM employees e
        JOIN employee_roles er ON e.role_id = er.id
        WHERE e.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    session_destroy();
    header("Location: employee_login.php");
    exit();
}

$employee_name = $employee['first_name'] . ' ' . $employee['last_name'];
$employee_initials = strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1));
$employee_role = $employee['role_name'];

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
        'transaction_logging' => 'Transaction Logging',
        'attendance' => 'Attendance',
        'inventory_view' => 'Inventory View',
        'sales_reports' => 'Personal Sales Reports',
        'customer_management' => 'Customer Management',
        'loyalty_points' => 'Loyalty Point Input',
        'messages' => 'Messages',
        'profile' => 'Profile',
        'logout' => 'Logout',
        'employee_profile' => 'Employee Profile',
        'change_password' => 'Change Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm New Password',
        'save_changes' => 'Save Changes',
        'cancel' => 'Cancel',
        'security_settings' => 'Security Settings',
        'password' => 'Password',
        'last_changed' => 'Last changed 3 months ago',
        'two_factor_auth' => 'Two-Factor Authentication',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'enable' => 'Enable',
        'disable' => 'Disable',
        'how_it_works' => 'How It Works',
        'benefits' => 'Benefits',
        'important' => 'Important',
        'enable_2fa' => 'Enable 2FA',
        'crop_profile_picture' => 'Crop Profile Picture',
        'crop_save' => 'Crop & Save',
        'profile_updated' => 'Profile updated successfully!',
        'avatar_updated' => 'Profile picture updated successfully!',
        'avatar_failed' => 'Failed to save profile picture',
        'password_changed' => 'Password changed successfully!',
        'password_mismatch' => 'New passwords don\'t match',
        'wrong_password' => 'Current password is incorrect',
        '2fa_enabled' => 'Two-factor authentication enabled successfully! You\'ll receive a code via email when logging in.',
        '2fa_disabled' => 'Two-factor authentication disabled successfully!',
        'member_since' => 'Member since',
        'last_login' => 'Last login',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email Address',
        'phone' => 'Phone Number',
        'address' => 'Address'
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'transaction_logging' => 'Pag-log ng Transaksyon',
        'attendance' => 'Pagdalo',
        'inventory_view' => 'Tingnan ang Inventory',
        'sales_reports' => 'Mga Personal na Ulat sa Pagbebenta',
        'customer_management' => 'Pamamahala ng Customer',
        'loyalty_points' => 'Input ng Loyalty Points',
        'messages' => 'Mga Mensahe',
        'profile' => 'Profile',
        'logout' => 'Logout',
        'employee_profile' => 'Profile ng Empleyado',
        'change_password' => 'Palitan ang Password',
        'current_password' => 'Kasalukuyang Password',
        'new_password' => 'Bagong Password',
        'confirm_password' => 'Kumpirmahin ang Bagong Password',
        'save_changes' => 'I-save ang Mga Pagbabago',
        'cancel' => 'Kanselahin',
        'security_settings' => 'Mga Setting ng Seguridad',
        'password' => 'Password',
        'last_changed' => 'Huling binago 3 buwan na ang nakalipas',
        'two_factor_auth' => 'Two-Factor Authentication',
        'enabled' => 'Pinagana',
        'disabled' => 'Hindi Pinagana',
        'enable' => 'Paganahin',
        'disable' => 'Huwag Paganahin',
        'how_it_works' => 'Paano Ito Gumagana',
        'benefits' => 'Mga Benepisyo',
        'important' => 'Mahalaga',
        'enable_2fa' => 'Paganahin ang 2FA',
        'crop_profile_picture' => 'I-crop ang Larawan sa Profile',
        'crop_save' => 'I-crop at I-save',
        'profile_updated' => 'Matagumpay na na-update ang profile!',
        'avatar_updated' => 'Matagumpay na na-update ang larawan sa profile!',
        'avatar_failed' => 'Nabigong i-save ang larawan sa profile',
        'password_changed' => 'Matagumpay na napalitan ang password!',
        'password_mismatch' => 'Hindi magkatugma ang mga bagong password',
        'wrong_password' => 'Hindi tama ang kasalukuyang password',
        '2fa_enabled' => 'Matagumpay na pinagana ang two-factor authentication! Makakatanggap ka ng code sa email kapag nag-log in.',
        '2fa_disabled' => 'Matagumpay na hindi pinagana ang two-factor authentication!',
        'member_since' => 'Miyembro mula noong',
        'last_login' => 'Huling pag-log in',
        'first_name' => 'Pangalan',
        'last_name' => 'Apelyido',
        'email' => 'Email Address',
        'phone' => 'Numero ng Telepono',
        'address' => 'Address'
    ]
];

$t = $translations[$language];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $contact_number = $_POST['contact_number'];
        $address = $_POST['address'];
        
        $update_query = "UPDATE employees SET contact_number = ?, address = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $contact_number, $address, $employee_id);
        $update_stmt->execute();
        
        // Refresh employee data
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        
        $success_message = $t['profile_updated'];
    }
    elseif (isset($_POST['upload_avatar'])) {
        if (isset($_POST['avatar_data']) && !empty($_POST['avatar_data'])) {
            $avatar_data = $_POST['avatar_data'];
            $image_parts = explode(";base64,", $avatar_data);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            
            // Generate unique filename
            $filename = 'employee_avatar_' . $employee_id . '_' . time() . '.png';
            $filepath = 'uploads/employee_avatars/' . $filename;
            
            // Create uploads directory if it doesn't exist
            if (!is_dir('uploads/employee_avatars')) {
                mkdir('uploads/employee_avatars', 0755, true);
            }
            
            // Save the file
            if (file_put_contents($filepath, $image_base64)) {
                // Delete old image if exists
                if (!empty($employee['profile_photo']) && file_exists($employee['profile_photo'])) {
                    @unlink($employee['profile_photo']);
                }
                
                // Update database
                $update_img_query = "UPDATE employees SET profile_photo = ? WHERE id = ?";
                $update_img_stmt = $conn->prepare($update_img_query);
                $update_img_stmt->bind_param("si", $filepath, $employee_id);
                $update_img_stmt->execute();
                
                // Refresh employee data
                $employee['profile_photo'] = $filepath;
                $success_message = $t['avatar_updated'];
            } else {
                $error_message = $t['avatar_failed'];
            }
        }
    }
    elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current_password, $employee['password_hash'])) {
            if ($new_password === $confirm_password) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pw_query = "UPDATE employees SET password_hash = ? WHERE id = ?";
                $update_pw_stmt = $conn->prepare($update_pw_query);
                $update_pw_stmt->bind_param("si", $hashed_password, $employee_id);
                $update_pw_stmt->execute();
                
                $success_message = $t['password_changed'];
            } else {
                $error_message = $t['password_mismatch'];
            }
        } else {
            $error_message = $t['wrong_password'];
        }
    }
    elseif (isset($_POST['enable_2fa'])) {
        // Generate a random 2FA secret (16 characters)
        $two_factor_secret = strtoupper(bin2hex(random_bytes(8)));
        
        $update_2fa_query = "UPDATE employees SET two_factor_auth = 1, two_factor_secret = ? WHERE id = ?";
        $update_2fa_stmt = $conn->prepare($update_2fa_query);
        $update_2fa_stmt->bind_param("si", $two_factor_secret, $employee_id);
        $update_2fa_stmt->execute();
        
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
            $mail->addAddress($employee['email'], $employee['first_name'] . ' ' . $employee['last_name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Two-Factor Authentication Enabled';
            $mail->Body    = "Hi {$employee['first_name']},<br><br>"
                            . "Two-factor authentication has been successfully enabled on your JunkValue employee account.<br><br>"
                            . "From now on, you'll receive a verification code via email when you log in.<br><br>"
                            . "If you didn't enable this feature, please contact your administrator immediately.<br><br>"
                            . "Thanks,<br>The JunkValue Security Team";
            
            $mail->send();
        } catch (Exception $e) {
            // Continue even if email fails
        }
        
        // Refresh employee data
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        
        $success_message = $t['2fa_enabled'];
    }
    elseif (isset($_POST['disable_2fa'])) {
        $update_2fa_query = "UPDATE employees SET two_factor_auth = 0, two_factor_secret = NULL WHERE id = ?";
        $update_2fa_stmt = $conn->prepare($update_2fa_query);
        $update_2fa_stmt->bind_param("i", $employee_id);
        $update_2fa_stmt->execute();
        
        // Refresh employee data
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        
        $success_message = $t['2fa_disabled'];
    }
}

// Format employee data for display
$member_since = date('F Y', strtotime($employee['created_at']));
$last_login = !empty($employee['last_login']) ? date('F j, Y \a\t g:i A', strtotime($employee['last_login'])) : "Never logged in";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Employee Profile</title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
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

    /* Sidebar - Employee Version */
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
        width: 100px;
        height: 100px;
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

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-name {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 5px;
        font-family: 'Inter', sans-serif;
        text-align: center;
        letter-spacing: 0.5px;
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
        color: var(--dark-text-primary);
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
        color: var(--dark-text-primary);
    }

    .nav-menu a.active {
        background-color: rgba(255,255,255,0.15);
        color: white;
        font-weight: 600;
    }

    body.dark-mode .nav-menu a.active {
        background-color: rgba(255,255,255,0.2);
        color: var(--dark-text-primary);
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
    }

    body.dark-mode .logout-btn {
        color: var(--dark-text-primary);
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

    body.dark-mode .language-btn {
        color: var(--dark-text-primary);
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

    body.dark-mode .language-option {
        color: var(--dark-text-primary);
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
        font-size: 40px;
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

    /* Profile Card */
    .profile-card {
        background-color: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.03);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    

    body.dark-mode .profile-card {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 8px 25px var(--dark-shadow);
        color: var(--dark-text-primary);
    }

    

    .profile-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--icon-green) 0%, var(--sales-orange) 100%);
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
        color: var(--dark-text-primary);
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
        font-weight: 700;
        transition: color 0.3s ease;
    }

    body.dark-mode .profile-info h3 {
        color: var(--dark-text-primary);
    }

    .profile-info p {
        color: var(--text-dark);
        opacity: 0.7;
        font-size: 16px;
        font-weight: 500;
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
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 15px;
        transition: color 0.3s ease;
    }

    body.dark-mode .form-group label {
        color: var(--dark-text-primary);
    }

    .form-group input, 
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s;
        background-color: #fafafa;
        font-weight: 500;
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
        box-shadow: 0 0 0 4px rgba(106, 127, 70, 0.1);
        background-color: white;
        transform: translateY(-1px);
    }

    body.dark-mode .form-group input:focus,
    body.dark-mode .form-group select:focus,
    body.dark-mode .form-group textarea:focus {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
    }

    .form-group input:hover {
        border-color: rgba(106, 127, 70, 0.3);
        background-color: white;
    }

    body.dark-mode .form-group input:hover {
        background-color: var(--dark-bg-tertiary);
    }

    input[readonly], input[disabled] {
        background-color: rgba(0,0,0,0.03);
        cursor: not-allowed;
    }

    body.dark-mode input[readonly], 
    body.dark-mode input[disabled] {
        background-color: rgba(255,255,255,0.05);
        color: var(--dark-text-secondary);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        border-top: 2px solid rgba(0,0,0,0.03);
        padding-top: 20px;
        transition: border-color 0.3s ease;
    }

    body.dark-mode .form-actions {
        border-top-color: var(--dark-border);
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px 28px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        position: relative;
        overflow: hidden;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(106, 127, 70, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(106, 127, 70, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: var(--text-dark);
        border: 2px solid rgba(0,0,0,0.1);
    }

    body.dark-mode .btn-secondary {
        background: linear-gradient(135deg, var(--dark-bg-tertiary) 0%, var(--dark-bg-secondary) 100%);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    body.dark-mode .btn-secondary:hover {
        background: linear-gradient(135deg, var(--dark-bg-secondary) 0%, var(--dark-bg-tertiary) 100%);
    }

    .btn-orange {
        background: linear-gradient(135deg, var(--sales-orange) 0%, #c46a38 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(217, 122, 65, 0.3);
    }

    .btn-orange:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(217, 122, 65, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(220, 53, 69, 0.4);
    }

    .btn-purple {
        background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(111, 66, 193, 0.3);
    }

    .btn-purple:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(111, 66, 193, 0.4);
    }

    /* Security Items */
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

    .security-icon.enabled {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .security-icon.disabled {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .security-details h4 {
        margin: 0;
        color: var(--text-dark);
        font-size: 15px;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    body.dark-mode .security-details h4 {
        color: var(--dark-text-primary);
    }

    .security-details p {
        margin: 0;
        color: var(--text-dark);
        opacity: 0.7;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .security-details p {
        color: var(--dark-text-secondary);
    }

    /* Alerts */
    .alert {
        padding: 18px 24px;
        margin-bottom: 25px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 2px solid #c3e6cb;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: 2px solid #f5c6cb;
    }

    body.dark-mode .alert-success {
        background: linear-gradient(135deg, rgba(212, 237, 218, 0.2) 0%, rgba(195, 230, 203, 0.15) 100%);
        color: var(--dark-text-primary);
        border-color: rgba(195, 230, 203, 0.3);
    }

    body.dark-mode .alert-danger {
        background: linear-gradient(135deg, rgba(248, 215, 218, 0.2) 0%, rgba(245, 198, 203, 0.15) 100%);
        color: var(--dark-text-primary);
        border-color: rgba(245, 198, 203, 0.3);
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
        overflow-y: auto;
        justify-content: center;
        align-items: center;
    }
    
    .modal-content {
        background-color: white;
        margin: auto;
        padding: 0;
        border-radius: 20px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        position: relative;
        animation: slideIn 0.5s ease-out;
        max-height: 90vh;
        overflow-y: auto;
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 20px 60px var(--dark-shadow);
        color: var(--dark-text-primary);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .close-modal {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 28px;
        font-weight: bold;
        color: #333;
        opacity: 0.5;
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
        background-color: #f5f5f5;
        transform: scale(1.1);
    }

    body.dark-mode .close-modal:hover {
        background-color: var(--dark-bg-tertiary);
    }
    
    .modal-header {
        padding: 30px 30px 20px 30px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .modal-header {
        border-bottom-color: var(--dark-border);
    }

    .modal-header h3 {
        color: var(--icon-green);
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 24px;
        font-weight: 700;
        padding-right: 40px;
    }

    .modal-header h3 i {
        font-size: 26px;
    }

    .modal-description {
        color: #666;
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .modal-description {
        color: var(--dark-text-secondary);
    }

    .modal-body {
        padding: 25px 30px;
    }
    
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 30px;
        border-top: 2px solid rgba(0,0,0,0.03);
        background-color: rgba(0,0,0,0.02);
        border-radius: 0 0 20px 20px;
        transition: all 0.3s ease;
    }

    body.dark-mode .modal-footer {
        border-top-color: var(--dark-border);
        background-color: rgba(255,255,255,0.02);
    }

    /* 2FA Specific Styles */
    .two-factor-setup {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.03), rgba(106, 127, 70, 0.08));
        border-radius: 12px;
        padding: 25px;
        border: 1px solid rgba(106, 127, 70, 0.15);
        transition: all 0.3s ease;
    }

    body.dark-mode .two-factor-setup {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.08), rgba(106, 127, 70, 0.12));
        border-color: rgba(106, 127, 70, 0.3);
        color: var(--dark-text-primary);
    }

    .two-factor-setup h4 {
        color: var(--icon-green);
        margin: 0 0 15px 0;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .two-factor-setup h4:not(:first-child) {
        margin-top: 25px;
    }

    .two-factor-setup p {
        color: #555;
        line-height: 1.7;
        margin: 0 0 15px 0;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .two-factor-setup p {
        color: var(--dark-text-secondary);
    }

    .two-factor-setup ul {
        margin: 15px 0;
        padding-left: 0;
        list-style: none;
    }

    .two-factor-setup ul li {
        color: #555;
        line-height: 1.8;
        margin-bottom: 10px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        transition: color 0.3s ease;
    }

    body.dark-mode .two-factor-setup ul li {
        color: var(--dark-text-secondary);
    }

    .two-factor-setup ul li i {
        color: var(--icon-green);
        margin-top: 3px;
        font-size: 14px;
        flex-shrink: 0;
    }

    .info-box {
        background-color: rgba(106, 127, 70, 0.08);
        border-left: 4px solid var(--icon-green);
        padding: 15px;
        border-radius: 8px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin: 15px 0;
        transition: all 0.3s ease;
    }

    body.dark-mode .info-box {
        background-color: rgba(106, 127, 70, 0.12);
    }

    .info-box i {
        color: var(--icon-green);
        font-size: 18px;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .info-box p {
        margin: 0;
        flex: 1;
        font-size: 14px;
        line-height: 1.6;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .info-box p {
        color: var(--dark-text-secondary);
    }

    .info-box.warning {
        background-color: rgba(255, 193, 7, 0.08);
        border-left-color: #ffc107;
    }

    body.dark-mode .info-box.warning {
        background-color: rgba(255, 193, 7, 0.12);
    }

    .info-box.warning i {
        color: #ffc107;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .status-badge.enabled {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .status-badge.disabled {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
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
        background-color: rgba(0,0,0,0.7);
    }
    
    #avatarModal .modal-content {
        background-color: white;
        margin: 2% auto;
        padding: 20px;
        border-radius: 20px;
        width: 90%;
        max-width: 800px;
    }

    body.dark-mode #avatarModal .modal-content {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
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
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--sales-orange) 0%, #c46a38 100%);
        color: white;
        border-radius: 12px;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 100;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(217, 122, 65, 0.3);
        transition: all 0.3s ease;
    }

    .mobile-menu-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(217, 122, 65, 0.4);
    }

    /* Responsive styles */
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
        .page-title {
            font-size: 36px;
        }

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

        .modal-header,
        .modal-body,
        .modal-footer {
            padding: 20px;
        }
    }

    @media (max-width: 576px) {
        .profile-card {
            padding: 20px;
        }
        
        .form-actions, .modal-footer {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }

        .modal-header h3 {
            font-size: 20px;
        }

        .two-factor-setup {
            padding: 20px;
        }

        .page-title {
            font-size: 30px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            font-size: 20px;
        }

        .user-name {
            font-size: 18px;
        }
    }
    </style>
</head>
<body>

    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <?php if (!empty($employee['profile_photo']) && file_exists($employee['profile_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($employee['profile_photo']); ?>" alt="Profile">
                <?php else: ?>
                    <?php echo $employee_initials; ?>
                <?php endif; ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($employee_name); ?></div>
            <div class="user-status">
                <span class="status-indicator"></span>
                <span><?php echo htmlspecialchars($employee_role); ?></span>
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
            <li><a href="Index.php"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="transaction_logging.php"><i class="fas fa-cash-register"></i> <?php echo $t['transaction_logging']; ?></a></li>
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <?php echo $t['attendance']; ?></a></li>
            <li><a href="inventory_view.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory_view']; ?></a></li>
            <li><a href="sales_reports.php"><i class="fas fa-chart-pie"></i> <?php echo $t['sales_reports']; ?></a></li>
            <li><a href="customer_management.php"><i class="fas fa-users"></i> <?php echo $t['customer_management']; ?></a></li>
            <li><a href="loyalty_points.php"><i class="fas fa-award"></i> <?php echo $t['loyalty_points']; ?></a></li>
             <li><a href="messages.php"><i class="fas fa-envelope"></i> <?php echo $t['messages']; ?></a></li>
            <li><a href="employee_profile.php" class="active"><i class="fas fa-user-cog"></i> <?php echo $t['profile']; ?></a></li>
        </ul>
        
        <div class="sidebar-footer">
            <form action="employee_logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
                </button>
            </form>
        </div>
    </div>
    
   
    <div class="main-content">
        <div class="header">
            <h1 class="page-title"><?php echo $t['employee_profile']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">2</span>
                </div>
            </div>
        </div>
        
        <div class="profile-card">
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
            
            <div class="profile-header">
                <div class="profile-avatar" id="avatarContainer">
                    <?php if (!empty($employee['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($employee['profile_photo']); ?>" alt="Profile">
                    <?php else: ?>
                        <span><?php echo $employee_initials; ?></span>
                    <?php endif; ?>
                    <div class="avatar-upload">
                        <i class="fas fa-camera"></i> Change
                    </div>
                    <input type="file" id="avatarInput" accept="image/*">
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($employee_name); ?></h3>
                    <p><?php echo htmlspecialchars($employee_role); ?> <?php echo $t['member_since']; ?> <?php echo $member_since; ?></p>
                    <p><?php echo $t['last_login']; ?>: <?php echo $last_login; ?></p>
                </div>
            </div>
            
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label><?php echo $t['first_name']; ?></label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['first_name']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label><?php echo $t['last_name']; ?></label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['last_name']); ?>" readonly>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><?php echo $t['email']; ?></label>
                    <input type="email" value="<?php echo htmlspecialchars($employee['email']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label><?php echo $t['phone']; ?></label>
                    <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($employee['contact_number']); ?>">
                </div>
                
                <div class="form-group">
                    <label><?php echo $t['address']; ?></label>
                    <textarea name="address" style="min-height: 100px;"><?php echo htmlspecialchars($employee['address']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="update_profile" class="btn btn-primary"><?php echo $t['save_changes']; ?></button>
                </div>
            </form>
        </div>
        
       
        <div class="profile-card">
            <h3 style="margin-bottom: 20px; color: var(--text-dark); font-size: 20px; font-weight: 700; transition: color 0.3s ease;"><?php echo $t['security_settings']; ?></h3>
            
            <div class="security-item">
                <div class="security-info">
                    <div class="security-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="security-details">
                        <h4><?php echo $t['password']; ?></h4>
                        <p><?php echo $t['last_changed']; ?></p>
                    </div>
                </div>
                <button class="btn btn-secondary" style="padding: 12px 20px;" onclick="openModal('passwordModal')"><?php echo $t['change_password']; ?></button>
            </div>

              
            <div class="security-item">
                <div class="security-info">
                    <div class="security-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="security-details">
                        <h4><?php echo $t['two_factor_auth']; ?></h4>
                        <p>
                            <?php if (!empty($employee['two_factor_auth'])): ?>
                                <span style="color: #28a745; font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> <?php echo $t['enabled']; ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #dc3545;">
                                    <i class="fas fa-times-circle"></i> <?php echo $t['disabled']; ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div>
                    <?php if (!empty($employee['two_factor_auth'])): ?>
                        <form method="POST" style="display: inline;">
                            <button type="submit" class="btn btn-danger" style="padding: 12px 20px;" name="disable_2fa"
                                    onclick="return confirm('Are you sure you want to disable two-factor authentication?')">
                                <i class="fas fa-times"></i> <?php echo $t['disable']; ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-primary" style="padding: 12px 20px;" onclick="openModal('enable2faModal')">
                            <i class="fas fa-shield-alt"></i> <?php echo $t['enable']; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('passwordModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-lock"></i> <?php echo $t['change_password']; ?></h3>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label><?php echo $t['current_password']; ?></label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo $t['new_password']; ?></label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label><?php echo $t['confirm_password']; ?></label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('passwordModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="change_password" class="btn btn-primary"><?php echo $t['save_changes']; ?></button>
                </div>
            </form>
        </div>
    </div>

   
    <div id="enable2faModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('enable2faModal')">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-shield-alt"></i> <?php echo $t['enable']; ?> <?php echo $t['two_factor_auth']; ?></h3>
                <p class="modal-description">
                    Add an extra layer of security to your account. You'll receive a verification code via email each time you log in.
                </p>
            </div>
            <div class="modal-body">
                <div class="two-factor-setup">
                    <h4><i class="fas fa-info-circle"></i> <?php echo $t['how_it_works']; ?></h4>
                    <p>When you log in to your account, we'll send a unique 6-digit verification code to your registered email address. You'll need to enter this code to complete the login process.</p>
                    
                    <div class="info-box">
                        <i class="fas fa-envelope"></i>
                        <p><strong>Your Email:</strong> <?php echo htmlspecialchars($employee['email']); ?></p>
                    </div>

                    <h4><i class="fas fa-check-circle"></i> <?php echo $t['benefits']; ?></h4>
                    <ul>
                        <li><i class="fas fa-check"></i> <span>Protects your account from unauthorized access</span></li>
                        <li><i class="fas fa-check"></i> <span>Instant email notifications for login attempts</span></li>
                        <li><i class="fas fa-check"></i> <span>Easy to use - no additional apps required</span></li>
                        <li><i class="fas fa-check"></i> <span>Can be disabled anytime from your profile</span></li>
                    </ul>

                    <div class="info-box warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p><strong><?php echo $t['important']; ?>:</strong> Make sure you have access to your email account before enabling this feature.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('enable2faModal')">
                    <i class="fas fa-times"></i> <?php echo $t['cancel']; ?>
                </button>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="enable_2fa" class="btn btn-primary">
                        <i class="fas fa-shield-alt"></i> <?php echo $t['enable_2fa']; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    
    <div id="avatarModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo $t['crop_profile_picture']; ?></h3>
                <span class="close-modal" onclick="closeModal('avatarModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div id="imagePreview"></div>
                <div class="cropper-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('avatarModal')"><?php echo $t['cancel']; ?></button>
                    <button type="button" class="btn btn-primary" id="cropButton"><?php echo $t['crop_save']; ?></button>
                </div>
            </div>
        </div>
    </div>

 
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

        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
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
    </script>
</body>
</html>
<?php
// Close prepared statements
$stmt->close();
if (isset($update_stmt)) $update_stmt->close();
if (isset($update_img_stmt)) $update_img_stmt->close();
if (isset($update_pw_stmt)) $update_pw_stmt->close();
// Close connection
$conn->close();
?>