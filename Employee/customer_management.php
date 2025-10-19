<?php
session_start();
require_once 'db_connection.php';

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
        'customer_management_system' => 'Customer Management System',
        'manage_customer_info' => 'Manage customer information and maintain customer relationships. Add new customers with secure login credentials, update existing records, and monitor customer engagement.',
        'total_customers' => 'Total Customers',
        'individual' => 'Individual',
        'business' => 'Business',
        'collectors' => 'Collectors',
        'new_30_days' => 'New (30 days)',
        'customer_list' => 'Customer List',
        'add_customer' => 'Add Customer',
        'customer_name' => 'Customer Name',
        'username' => 'Username',
        'email' => 'Email',
        'phone' => 'Phone',
        'type' => 'Type',
        'transactions' => 'Transactions',
        'total_spent' => 'Total Spent',
        'loyalty_points' => 'Loyalty Point Input',
        'last_transaction' => 'Last Transaction',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'no_customers_found' => 'No customers found',
        'showing' => 'Showing',
        'to' => 'to',
        'of' => 'of',
        'customers' => 'customers',
        'page' => 'Page',
        'previous' => 'Previous',
        'next' => 'Next',
        'add_new_customer' => 'Add New Customer',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'customer_type' => 'Customer Type',
        'select_type' => 'Select Type',
        'password' => 'Password',
        'password_requirements' => 'Password must be at least 8 characters long, contain at least one capital letter and one number.',
        'address' => 'Address',
        'clear_form' => 'Clear Form',
        'add_customer_btn' => 'Add Customer',
        'edit_customer' => 'Edit Customer',
        'new_password_leave_blank' => 'New Password (leave blank to keep current password)',
        'cancel' => 'Cancel',
        'update_customer' => 'Update Customer',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
        'all_types' => 'All Types',
        'search' => 'Search',
        'search_customers' => 'Search customers...',
        'filter' => 'Filter'
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
        'customer_management_system' => 'Sistema ng Pamamahala ng Customer',
        'manage_customer_info' => 'Pamahalaan ang impormasyon ng customer at panatilihin ang relasyon sa customer. Magdagdag ng mga bagong customer na may secure na login credentials, i-update ang mga umiiral na rekord, at subaybayan ang pakikipag-ugnayan ng customer.',
        'total_customers' => 'Kabuuang Mga Customer',
        'individual' => 'Indibidwal',
        'business' => 'Negosyo',
        'collectors' => 'Mga Mangangalakal',
        'new_30_days' => 'Bago (30 araw)',
        'customer_list' => 'Listahan ng Customer',
        'add_customer' => 'Magdagdag ng Customer',
        'customer_name' => 'Pangalan ng Customer',
        'username' => 'Username',
        'email' => 'Email',
        'phone' => 'Telepono',
        'type' => 'Uri',
        'transactions' => 'Mga Transaksyon',
        'total_spent' => 'Kabuuang Nagastos',
        'loyalty_points' => 'Mga Loyalty Points',
        'last_transaction' => 'Huling Transaksyon',
        'actions' => 'Mga Aksyon',
        'edit' => 'I-edit',
        'no_customers_found' => 'Walang nakitang mga customer',
        'showing' => 'Ipinapakita',
        'to' => 'hanggang',
        'of' => 'ng',
        'customers' => 'mga customer',
        'page' => 'Pahina',
        'previous' => 'Nakaraan',
        'next' => 'Susunod',
        'add_new_customer' => 'Magdagdag ng Bagong Customer',
        'first_name' => 'Pangalan',
        'last_name' => 'Apelyido',
        'customer_type' => 'Uri ng Customer',
        'select_type' => 'Pumili ng Uri',
        'password' => 'Password',
        'password_requirements' => 'Ang password ay dapat hindi bababa sa 8 character ang haba, naglalaman ng hindi bababa sa isang malaking titik at isang numero.',
        'address' => 'Address',
        'clear_form' => 'I-clear ang Form',
        'add_customer_btn' => 'Magdagdag ng Customer',
        'edit_customer' => 'I-edit ang Customer',
        'new_password_leave_blank' => 'Bagong Password (iwanang blangko upang panatilihin ang kasalukuyang password)',
        'cancel' => 'Kanselahin',
        'update_customer' => 'I-update ang Customer',
        'date_from' => 'Petsa Mula',
        'date_to' => 'Petsa Hanggang',
        'all_types' => 'Lahat ng Uri',
        'search' => 'Maghanap',
        'search_customers' => 'Maghanap ng mga customer...',
        'filter' => 'Filter'
    ]
];

$t = $translations[$language];

if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$query = "SELECT e.*, r.role_name 
          FROM employees e 
          JOIN employee_roles r ON e.role_id = r.id 
          WHERE e.id = ?";
$stmt = $conn->prepare($query);
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
$employee_role = $employee['role_name'];

$success = '';
$error = '';
$show_modal = false;

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validatePassword($password) {
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one capital letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_customer'])) {
        try {
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $user_type = sanitizeInput($_POST['user_type']);
            $password = $_POST['password'];
            
            $password_validation = validatePassword($password);
            if ($password_validation !== true) {
                throw new Exception($password_validation);
            }
            
            $check_email_query = "SELECT id FROM users WHERE email = ?";
            $check_email_stmt = $conn->prepare($check_email_query);
            $check_email_stmt->bind_param("s", $email);
            $check_email_stmt->execute();
            $check_email_result = $check_email_stmt->get_result();
            
            if ($check_email_result->num_rows > 0) {
                throw new Exception("email already register");
            }
            
            $check_username_query = "SELECT id FROM users WHERE username = ?";
            $check_username_stmt = $conn->prepare($check_username_query);
            $check_username_stmt->bind_param("s", $username);
            $check_username_stmt->execute();
            $check_username_result = $check_username_stmt->get_result();
            
            if ($check_username_result->num_rows > 0) {
                throw new Exception("Username already exists");
            }
            
            $referral_code = strtoupper(substr($first_name, 0, 2) . substr($last_name, 0, 2) . rand(1000, 9999));
            
            $insert_query = "INSERT INTO users (first_name, last_name, username, email, phone, address, user_type, referral_code, password_hash, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_query);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt->bind_param("sssssssss", $first_name, $last_name, $username, $email, $phone, $address, $user_type, $referral_code, $password_hash);
            
            if ($insert_stmt->execute()) {
                $success = "Customer added successfully! They can now login with their credentials.";
            } else {
                throw new Exception("Failed to add customer");
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    if (isset($_POST['update_customer'])) {
        try {
            $customer_id = intval($_POST['customer_id']);
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $address = sanitizeInput($_POST['address']);
            $user_type = sanitizeInput($_POST['user_type']);
            
            $check_email_query = "SELECT id FROM users WHERE email = ? AND id != ?";
            $check_email_stmt = $conn->prepare($check_email_query);
            $check_email_stmt->bind_param("si", $email, $customer_id);
            $check_email_stmt->execute();
            $check_email_result = $check_email_stmt->get_result();
            
            if ($check_email_result->num_rows > 0) {
                throw new Exception("email already register");
            }
            
            $check_username_query = "SELECT id FROM users WHERE username = ? AND id != ?";
            $check_username_stmt = $conn->prepare($check_username_query);
            $check_username_stmt->bind_param("si", $username, $customer_id);
            $check_username_stmt->execute();
            $check_username_result = $check_username_stmt->get_result();
            
            if ($check_username_result->num_rows > 0) {
                throw new Exception("Username already exists");
            }
            
            if (!empty($_POST['new_password'])) {
                $new_password = $_POST['new_password'];
                $password_validation = validatePassword($new_password);
                if ($password_validation !== true) {
                    throw new Exception($password_validation);
                }
                
                $update_query = "UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, phone = ?, address = ?, user_type = ?, password_hash = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt->bind_param("ssssssssi", $first_name, $last_name, $username, $email, $phone, $address, $user_type, $password_hash, $customer_id);
            } else {
                $update_query = "UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, phone = ?, address = ?, user_type = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("sssssssi", $first_name, $last_name, $username, $email, $phone, $address, $user_type, $customer_id);
            }
            
            if ($update_stmt->execute()) {
                $success = "Customer updated successfully!";
            } else {
                throw new Exception("Failed to update customer");
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$customers_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $customers_per_page;

$search = $_GET['search'] ?? '';
$user_type_filter = $_GET['user_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$count_query = "SELECT COUNT(*) as total FROM users u WHERE 1=1";
$count_params = [];
$count_types = "";

if (!empty($search)) {
    $count_query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $search_param = "%$search%";
    $count_params = array_merge($count_params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    $count_types .= "sssss";
}

if (!empty($user_type_filter)) {
    $count_query .= " AND u.user_type = ?";
    $count_params[] = $user_type_filter;
    $count_types .= "s";
}

if (!empty($date_from)) {
    $count_query .= " AND u.created_at >= ?";
    $count_params[] = $date_from . " 00:00:00";
    $count_types .= "s";
}

if (!empty($date_to)) {
    $count_query .= " AND u.created_at <= ?";
    $count_params[] = $date_to . " 23:59:59";
    $count_types .= "s";
}

$count_stmt = $conn->prepare($count_query);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_customers = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_customers / $customers_per_page);

$customers_query = "SELECT u.*, 
                   COUNT(DISTINCT t.id) as total_transactions,
                   COALESCE(SUM(t.amount), 0) as total_spent,
                   MAX(t.created_at) as last_transaction
                   FROM users u 
                   LEFT JOIN transactions t ON u.id = t.user_id 
                   WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $customers_query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    $types .= "sssss";
}

if (!empty($user_type_filter)) {
    $customers_query .= " AND u.user_type = ?";
    $params[] = $user_type_filter;
    $types .= "s";
}

if (!empty($date_from)) {
    $customers_query .= " AND u.created_at >= ?";
    $params[] = $date_from . " 00:00:00";
    $types .= "s";
}

if (!empty($date_to)) {
    $customers_query .= " AND u.created_at <= ?";
    $params[] = $date_to . " 23:59:59";
    $types .= "s";
}

$customers_query .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $customers_per_page;
$params[] = $offset;
$types .= "ii";

$customers_stmt = $conn->prepare($customers_query);
if (!empty($params)) {
    $customers_stmt->bind_param($types, ...$params);
}
$customers_stmt->execute();
$customers_result = $customers_stmt->get_result();
$customers = $customers_result->fetch_all(MYSQLI_ASSOC);

$stats_query = "SELECT 
                COUNT(*) as total_customers,
                COUNT(CASE WHEN user_type = 'individual' THEN 1 END) as individual_customers,
                COUNT(CASE WHEN user_type = 'business' THEN 1 END) as business_customers,
                COUNT(CASE WHEN user_type = 'collector' THEN 1 END) as collector_customers,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_customers_30_days
                FROM users";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Customer Management</title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
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
        --dark-text-primary: #e0e0e0;
        --dark-text-secondary: #a0a0a0;
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
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 5px;
        font-family: 'Inter', sans-serif;
        text-align: center;
        letter-spacing: 0.5px;
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

    .main-content {
        flex: 1;
        padding: 30px;
        overflow-y: auto;
    }

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

    .welcome-banner {
        background: linear-gradient(135deg, var(--panel-cream) 0%, #E8DFC8 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(217, 122, 65, 0.3);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .welcome-banner {
        background: linear-gradient(135deg, var(--dark-bg-secondary) 0%, var(--dark-bg-tertiary) 100%);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .welcome-content h2 {
        font-size: 22px;
        font-weight: 700;
        color: var(--topbar-brown);
        margin-bottom: 10px;
        letter-spacing: 0.3px;
        transition: color 0.3s ease;
    }

    body.dark-mode .welcome-content h2 {
        color: var(--dark-text-primary);
    }

    .welcome-content p {
        color: var(--text-dark);
        max-width: 600px;
        margin-bottom: 15px;
        font-size: 15px;
        line-height: 1.5;
        transition: color 0.3s ease;
    }

    body.dark-mode .welcome-content p {
        color: var(--dark-text-secondary);
    }

    .welcome-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 100px;
        color: rgba(217, 122, 65, 0.1);
        z-index: 1;
    }

    body.dark-mode .welcome-icon {
        color: rgba(217, 122, 65, 0.05);
    }

    .customer-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .customer-tab {
        padding: 12px 20px;
        border-radius: 8px;
        background-color: white;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s;
        border: 1px solid rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    body.dark-mode .customer-tab {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .customer-tab:hover {
        background-color: rgba(106, 127, 70, 0.1);
        transform: translateY(-2px);
    }

    body.dark-mode .customer-tab:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .customer-tab.active {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
    }

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
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: 0.3px;
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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        text-align: center;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .stat-card {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 3px 10px var(--dark-shadow);
    }

    .stat-number {
        font-size: 32px;
        font-weight: 800;
        color: var(--icon-green);
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .stat-label {
        color: var(--dark-text-primary);
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

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        background-color: white;
        color: var(--text-dark);
    }

    body.dark-mode .form-control {
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    body.dark-mode .form-control:focus {
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.2);
    }

    .form-row {
        display: flex;
        gap: 15px;
    }

    .form-col {
        flex: 1;
    }

    .table-responsive {
        overflow-x: auto;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    table thead {
        position: sticky;
        top: 0;
    }

    th {
        background-color: rgba(106, 127, 70, 0.08);
        font-weight: 600;
        color: var(--icon-green);
        padding: 12px 15px;
        text-align: left;
        border-bottom: 2px solid rgba(106, 127, 70, 0.2);
        font-size: 14px;
        letter-spacing: 0.3px;
        transition: all 0.3s ease;
    }

    body.dark-mode th {
        background-color: rgba(106, 127, 70, 0.15);
        color: var(--dark-text-primary);
        border-bottom-color: rgba(106, 127, 70, 0.3);
    }

    td {
        padding: 14px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-size: 14px;
        transition: all 0.3s ease;
    }

    body.dark-mode td {
        border-bottom-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover td {
        background-color: rgba(106, 127, 70, 0.03);
    }

    body.dark-mode tr:hover td {
        background-color: rgba(106, 127, 70, 0.1);
    }

    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }

    .badge-success {
        background-color: rgba(112, 139, 76, 0.1);
        color: var(--stock-green);
    }

    .badge-warning {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    .badge-info {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .badge-individual {
        background-color: rgba(74, 137, 220, 0.1);
        color: var(--accent-blue);
    }

    .badge-business {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    .badge-collector {
        background-color: rgba(112, 139, 76, 0.1);
        color: var(--stock-green);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        position: relative;
        overflow: hidden;
        letter-spacing: 0.3px;
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

    .btn-secondary {
        background-color: white;
        color: var(--text-dark);
        border: 1px solid rgba(0,0,0,0.1);
    }

    body.dark-mode .btn-secondary {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .btn-secondary:hover {
        background-color: #f5f5f5;
        transform: translateY(-2px);
    }

    body.dark-mode .btn-secondary:hover {
        background-color: var(--dark-bg-secondary);
    }

    .btn-orange {
        background: linear-gradient(90deg, var(--sales-orange) 0%, #c46a38 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(217, 122, 65, 0.3);
    }

    .btn-orange:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(217, 122, 65, 0.4);
    }

    .btn-sm {
        padding: 8px 12px;
        font-size: 12px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        padding: 20px 0;
    }

    .pagination-info {
        color: var(--text-dark);
        font-size: 14px;
        margin-right: 20px;
        transition: color 0.3s ease;
    }

    body.dark-mode .pagination-info {
        color: var(--dark-text-primary);
    }

    .pagination-btn {
        padding: 8px 16px;
        border: 1px solid rgba(0,0,0,0.1);
        background-color: white;
        color: var(--text-dark);
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    body.dark-mode .pagination-btn {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .pagination-btn:hover {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
        transform: translateY(-2px);
    }

    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    .pagination-btn.current {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-dark);
        opacity: 0.7;
        transition: color 0.3s ease;
    }

    body.dark-mode .empty-state {
        color: var(--dark-text-secondary);
    }

    .empty-state i {
        font-size: 50px;
        color: var(--icon-green);
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 15px;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

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

    .filters {
        background-color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .filters {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .input-group {
        display: flex;
        gap: 10px;
    }

    .input-group .form-control {
        flex: 1;
    }

    .input-group .btn {
        flex: 0 0 auto;
        padding: 12px 15px;
    }

    .reset-btn {
        background-color: #f8f9fa;
        color: #495057;
        border: 1px solid #ced4da;
    }

    body.dark-mode .reset-btn {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .reset-btn:hover {
        background-color: #e9ecef;
    }

    body.dark-mode .reset-btn:hover {
        background-color: var(--dark-bg-secondary);
    }

    .password-requirements {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        transition: color 0.3s ease;
    }

    body.dark-mode .password-requirements {
        color: var(--dark-text-secondary);
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.7);
        z-index: 1000;
        overflow: auto;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: white;
        padding: 30px;
        border-radius: 15px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideIn 0.5s ease-out;
        margin: auto;
        position: relative;
        transition: all 0.3s ease;
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary);
    }

    @keyframes slideIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    #closeModal {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
        color: #333;
    }

    body.dark-mode #closeModal {
        color: var(--dark-text-primary);
    }

    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }

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
        .page-title {
            font-size: 36px;
        }

        .customer-tabs {
            flex-wrap: wrap;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .pagination {
            flex-wrap: wrap;
        }

        .pagination-info {
            margin-right: 0;
            margin-bottom: 10px;
        }
    }

    @media (max-width: 576px) {
        .header {
            flex-direction: column;
            gap: 15px;
        }

        .page-title {
            font-size: 30px;
        }

        .btn {
            width: 100%;
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
                    <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
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
            <li><a href="customer_management.php" class="active"><i class="fas fa-users"></i> <?php echo $t['customer_management']; ?></a></li>
            <li><a href="loyalty_points.php"><i class="fas fa-award"></i> <?php echo $t['loyalty_points']; ?></a></li>
             <li><a href="messages.php"><i class="fas fa-envelope"></i> <?php echo $t['messages']; ?></a></li>
            <li><a href="employee_profile.php"><i class="fas fa-user-cog"></i> <?php echo $t['profile']; ?></a></li>
        </ul>
        
        <div class="sidebar-footer">
            <button class="logout-btn" onclick="window.location.href='employee_logout.php'">
                <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
            </button>
        </div>
    </div>
    
    <div class="main-content">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <h1 class="page-title"><?php echo $t['customer_management']; ?></h1>
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
        
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2><?php echo $t['customer_management_system']; ?></h2>
                <p><?php echo $t['manage_customer_info']; ?></p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_customers']); ?></div>
                <div class="stat-label"><?php echo $t['total_customers']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['individual_customers']); ?></div>
                <div class="stat-label"><?php echo $t['individual']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['business_customers']); ?></div>
                <div class="stat-label"><?php echo $t['business']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['collector_customers']); ?></div>
                <div class="stat-label"><?php echo $t['collectors']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['new_customers_30_days']); ?></div>
                <div class="stat-label"><?php echo $t['new_30_days']; ?></div>
            </div>
        </div>
        
        <div class="customer-tabs">
            <div class="customer-tab active" data-tab="customer-list">
                <i class="fas fa-list"></i> <?php echo $t['customer_list']; ?>
            </div>
            <div class="customer-tab" data-tab="add-customer">
                <i class="fas fa-user-plus"></i> <?php echo $t['add_customer']; ?>
            </div>
        </div>
        
        <div id="customer-list" class="dashboard-card">
            <div class="filters" style="margin-bottom: 20px;">
                <form method="GET" id="filterForm">
                    <input type="hidden" name="page" value="1">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['date_from']; ?></label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['date_to']; ?></label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['customer_type']; ?></label>
                            <select name="user_type" class="form-control">
                                <option value=""><?php echo $t['all_types']; ?></option>
                                <option value="individual" <?php echo ($_GET['user_type'] ?? '') === 'individual' ? 'selected' : ''; ?>><?php echo $t['individual']; ?></option>
                                <option value="business" <?php echo ($_GET['user_type'] ?? '') === 'business' ? 'selected' : ''; ?>><?php echo $t['business']; ?></option>
                                <option value="collector" <?php echo ($_GET['user_type'] ?? '') === 'collector' ? 'selected' : ''; ?>><?php echo $t['collectors']; ?></option>
                            </select>
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['search']; ?></label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="<?php echo $t['search_customers']; ?>" value="<?php echo $_GET['search'] ?? ''; ?>">
                                <button type="submit" class="btn btn-primary"><?php echo $t['filter']; ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-users"></i> <?php echo $t['customer_list']; ?></h2>
            </div>
            
            <?php if (!empty($customers)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['customer_name']; ?></th>
                                <th><?php echo $t['username']; ?></th>
                                <th><?php echo $t['email']; ?></th>
                                <th><?php echo $t['phone']; ?></th>
                                <th><?php echo $t['type']; ?></th>
                                <th><?php echo $t['transactions']; ?></th>
                                <th><?php echo $t['total_spent']; ?></th>
                                <th><?php echo $t['loyalty_points']; ?></th>
                                <th><?php echo $t['last_transaction']; ?></th>
                                <th><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['username'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $customer['user_type']; ?>">
                                            <?php echo ucfirst($customer['user_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($customer['total_transactions']); ?></td>
                                    <td>₱<?php echo number_format($customer['total_spent'], 2); ?></td>
                                    <td><?php echo number_format($customer['loyalty_points']); ?></td>
                                    <td>
                                        <?php 
                                        if ($customer['last_transaction']) {
                                            echo date('M j, Y', strtotime($customer['last_transaction']));
                                        } else {
                                            echo 'No transactions';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="editCustomer(<?php echo $customer['id']; ?>)">
                                            <i class="fas fa-edit"></i> <?php echo $t['edit']; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <div class="pagination-info">
                            <?php echo $t['showing']; ?> <?php echo (($current_page - 1) * $customers_per_page) + 1; ?> <?php echo $t['to']; ?> 
                            <?php echo min($current_page * $customers_per_page, $total_customers); ?> <?php echo $t['of']; ?> 
                            <?php echo $total_customers; ?> <?php echo $t['customers']; ?>
                        </div>
                        
                        <?php if ($current_page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                            </a>
                        <?php else: ?>
                            <span class="pagination-btn disabled">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                            </span>
                        <?php endif; ?>

                        <span class="pagination-btn current">
                            <?php echo $t['page']; ?> <?php echo $current_page; ?> <?php echo $t['of']; ?> <?php echo $total_pages; ?>
                        </span>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>" class="pagination-btn">
                                <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="pagination-btn disabled">
                                <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p><?php echo $t['no_customers_found']; ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div id="add-customer" class="dashboard-card" style="display: none;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-plus"></i> <?php echo $t['add_new_customer']; ?></h2>
            </div>
            
            <form method="POST" id="addCustomerForm">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['first_name']; ?></label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['last_name']; ?></label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['username']; ?></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['email']; ?></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['phone']; ?></label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['customer_type']; ?></label>
                            <select name="user_type" class="form-control" required>
                                <option value=""><?php echo $t['select_type']; ?></option>
                                <option value="individual"><?php echo $t['individual']; ?></option>
                                <option value="business"><?php echo $t['business']; ?></option>
                                <option value="collector"><?php echo $t['collectors']; ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo $t['password']; ?></label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="password-requirements">
                        <?php echo $t['password_requirements']; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo $t['address']; ?></label>
                    <textarea name="address" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary"><?php echo $t['clear_form']; ?></button>
                    <button type="submit" name="add_customer" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $t['add_customer_btn']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="editCustomerModal" class="modal">
        <div class="modal-content">
            <button id="closeEditModal">&times;</button>
            <h2 style="margin-bottom: 20px;"><i class="fas fa-user-edit"></i> <?php echo $t['edit_customer']; ?></h2>
            
            <form method="POST" id="editCustomerForm">
                <input type="hidden" name="customer_id" id="edit_customer_id">
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['first_name']; ?></label>
                            <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['last_name']; ?></label>
                            <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['username']; ?></label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['email']; ?></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['phone']; ?></label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" required>
                        </div>
                        </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label><?php echo $t['customer_type']; ?></label>
                            <select name="user_type" id="edit_user_type" class="form-control" required>
                                <option value="individual"><?php echo $t['individual']; ?></option>
                                <option value="business"><?php echo $t['business']; ?></option>
                                <option value="collector"><?php echo $t['collectors']; ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo $t['new_password_leave_blank']; ?></label>
                    <input type="password" name="new_password" id="edit_new_password" class="form-control">
                    <div class="password-requirements">
                        <?php echo $t['password_requirements']; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label><?php echo $t['address']; ?></label>
                    <textarea name="address" id="edit_address" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" id="cancelEdit" class="btn btn-secondary"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="update_customer" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $t['update_customer']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        document.querySelectorAll('.customer-tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                document.querySelectorAll('.customer-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const target = this.getAttribute('data-tab');
                document.querySelectorAll('.dashboard-card').forEach(card => {
                    card.style.display = 'none';
                });
                document.getElementById(target).style.display = 'block';
            });
        });

        function editCustomer(customerId) {
            fetch('get_customer.php?id=' + customerId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const customer = data.customer;
                        document.getElementById('edit_customer_id').value = customer.id;
                        document.getElementById('edit_first_name').value = customer.first_name;
                        document.getElementById('edit_last_name').value = customer.last_name;
                        document.getElementById('edit_username').value = customer.username || '';
                        document.getElementById('edit_email').value = customer.email;
                        document.getElementById('edit_phone').value = customer.phone;
                        document.getElementById('edit_address').value = customer.address;
                        document.getElementById('edit_user_type').value = customer.user_type;
                        
                        document.getElementById('editCustomerModal').style.display = 'flex';
                    } else {
                        alert('Error loading customer data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading customer data');
                });
        }

        document.getElementById('closeEditModal')?.addEventListener('click', function() {
            document.getElementById('editCustomerModal').style.display = 'none';
        });
        
        document.getElementById('cancelEdit')?.addEventListener('click', function() {
            document.getElementById('editCustomerModal').style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('editCustomerModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>