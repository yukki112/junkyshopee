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
        'attendance_management' => 'Attendance Management',
        'track_your_work_hours' => 'Track your work hours, clock in and out, and view your attendance history. Stay on top of your schedule and work performance.',
        'currently_clocked_in' => 'Currently Clocked In',
        'currently_clocked_out' => 'Currently Clocked Out',
        'todays_hours' => 'Today\'s Hours',
        'clock_in' => 'Clock In',
        'clock_out' => 'Clock Out',
        'attendance_history' => 'Attendance History',
        'summary' => 'Summary',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
        'search' => 'Search',
        'filter' => 'Filter',
        'date' => 'Date',
        'clock_in_time' => 'Clock In',
        'clock_out_time' => 'Clock Out',
        'duration' => 'Duration',
        'method' => 'Method',
        'status' => 'Status',
        'no_attendance_records' => 'No attendance records found for the selected period',
        'this_month' => 'This Month',
        'days_present' => 'Days Present',
        'average_daily' => 'Average Daily',
        'active' => 'Active',
        'completed' => 'Completed'
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
        'attendance_management' => 'Pamamahala ng Pagdalo',
        'track_your_work_hours' => 'Subaybayan ang iyong mga oras ng trabaho, mag-clock in at out, at tingnan ang iyong kasaysayan ng pagdalo. Panatilihing updated ang iyong iskedyul at performance sa trabaho.',
        'currently_clocked_in' => 'Kasalukuyang Naka-clock In',
        'currently_clocked_out' => 'Kasalukuyang Naka-clock Out',
        'todays_hours' => 'Mga Oras Ngayong Araw',
        'clock_in' => 'Mag-clock In',
        'clock_out' => 'Mag-clock Out',
        'attendance_history' => 'Kasaysayan ng Pagdalo',
        'summary' => 'Buod',
        'date_from' => 'Petsa Mula',
        'date_to' => 'Petsa Hanggang',
        'search' => 'Maghanap',
        'filter' => 'Filter',
        'date' => 'Petsa',
        'clock_in_time' => 'Oras ng Clock In',
        'clock_out_time' => 'Oras ng Clock Out',
        'duration' => 'Tagal',
        'method' => 'Paraan',
        'status' => 'Katayuan',
        'no_attendance_records' => 'Walang nakitang mga talaan ng pagdalo para sa napiling panahon',
        'this_month' => 'Ngayong Buwan',
        'days_present' => 'Mga Araw na Present',
        'average_daily' => 'Average Araw-araw',
        'active' => 'Aktibo',
        'completed' => 'Natapos'
    ]
];

$t = $translations[$language];

// Authentication check
if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

// Get employee info
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
$employee_initials = strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1));

// Initialize variables
$success = '';
$error = '';

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Check if employee is currently clocked in
function isCurrentlyClockedIn($conn, $employee_id) {
    $query = "SELECT id FROM attendance_logs WHERE employee_id = ? AND logout_time IS NULL ORDER BY login_time DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get current attendance status
$current_session = isCurrentlyClockedIn($conn, $employee_id);
$is_clocked_in = !empty($current_session);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clock_in'])) {
        if ($is_clocked_in) {
            $error = "You are already clocked in!";
        } else {
            $method = sanitizeInput($_POST['method'] ?? 'Manual');
            $login_time = date('Y-m-d H:i:s');
            
            $query = "INSERT INTO attendance_logs (employee_id, login_time, method) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $employee_id, $login_time, $method);
            
            if ($stmt->execute()) {
                $success = "Successfully clocked in at " . date('h:i A');
                $is_clocked_in = true;
                $current_session = ['id' => $conn->insert_id];
            } else {
                $error = "Failed to clock in. Please try again.";
            }
        }
    }
    
    if (isset($_POST['clock_out'])) {
        if (!$is_clocked_in) {
            $error = "You are not currently clocked in!";
        } else {
            $logout_time = date('Y-m-d H:i:s');
            
            $query = "UPDATE attendance_logs SET logout_time = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $logout_time, $current_session['id']);
            
            if ($stmt->execute()) {
                $success = "Successfully clocked out at " . date('h:i A');
                $is_clocked_in = false;
                $current_session = null;
            } else {
                $error = "Failed to clock out. Please try again.";
            }
        }
    }
}

$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today
$search = $_GET['search'] ?? '';

$attendance_query = "SELECT 
    DATE(login_time) as date,
    TIME(login_time) as clock_in_time,
    TIME(logout_time) as clock_out_time,
    method,
    CASE 
        WHEN logout_time IS NULL THEN 'Active'
        ELSE 'Completed'
    END as status,
    CASE 
        WHEN logout_time IS NOT NULL THEN 
            TIMESTAMPDIFF(MINUTE, login_time, logout_time)
        ELSE 
            TIMESTAMPDIFF(MINUTE, login_time, NOW())
    END as duration_minutes
    FROM attendance_logs 
    WHERE employee_id = ? 
    AND DATE(login_time) BETWEEN ? AND ?";

$params = [$employee_id, $date_from, $date_to];
$types = "iss";

if (!empty($search)) {
    $attendance_query .= " AND (method LIKE ? OR DATE(login_time) LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$attendance_query .= " ORDER BY login_time DESC LIMIT 20";

$stmt = $conn->prepare($attendance_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$attendance_history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$today_query = "SELECT 
    SUM(CASE 
        WHEN logout_time IS NOT NULL THEN 
            TIMESTAMPDIFF(MINUTE, login_time, logout_time)
        ELSE 
            TIMESTAMPDIFF(MINUTE, login_time, NOW())
    END) as total_minutes
    FROM attendance_logs 
    WHERE employee_id = ? AND DATE(login_time) = CURDATE()";

$stmt = $conn->prepare($today_query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$today_result = $stmt->get_result()->fetch_assoc();
$today_minutes = $today_result['total_minutes'] ?? 0;
$today_hours = floor($today_minutes / 60);
$today_mins = $today_minutes % 60;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Employee Attendance</title>
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

    /* Welcome Banner */
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

    /* Attendance-specific styles */
    .attendance-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .attendance-tab {
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
    }

    body.dark-mode .attendance-tab {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .attendance-tab:hover {
        background-color: rgba(106, 127, 70, 0.1);
        transform: translateY(-2px);
    }

    body.dark-mode .attendance-tab:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .attendance-tab.active {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
    }

    /* Clock Status Card */
    .clock-status-card {
        background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(106, 127, 70, 0.3);
    }

    .clock-status-card.clocked-out {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        box-shadow: 0 10px 30px rgba(108, 117, 125, 0.3);
    }

    .clock-time {
        font-size: 48px;
        font-weight: 800;
        margin-bottom: 10px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .clock-status {
        font-size: 18px;
        margin-bottom: 20px;
        opacity: 0.9;
    }

    .today-hours {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .today-hours h3 {
        font-size: 16px;
        margin-bottom: 5px;
        opacity: 0.8;
    }

    .today-hours .hours {
        font-size: 24px;
        font-weight: 700;
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

    /* Forms */
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

    .form-row {
        display: flex;
        gap: 15px;
    }

    .form-col {
        flex: 1;
    }

    /* Tables */
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

    /* Badges */
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

    body.dark-mode .badge-success {
        background-color: rgba(112, 139, 76, 0.2);
        color: #a8d08d;
    }

    .badge-warning {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    body.dark-mode .badge-warning {
        background-color: rgba(217, 122, 65, 0.2);
        color: #f8b88e;
    }

    .badge-info {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .badge-active {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    body.dark-mode .badge-active {
        background-color: rgba(40, 167, 69, 0.2);
        color: #6fcf97;
    }

    /* Buttons */
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
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .btn-secondary:hover {
        background-color: #f5f5f5;
        transform: translateY(-2px);
    }

    body.dark-mode .btn-secondary:hover {
        background-color: var(--dark-bg-tertiary);
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

    .btn-large {
        padding: 20px 40px;
        font-size: 18px;
        font-weight: 700;
    }

    /* Empty State */
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

    /* Alerts */
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

    /* Filter Section */
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
        .page-title {
            font-size: 36px;
        }

        .attendance-tabs {
            flex-wrap: wrap;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .clock-time {
            font-size: 36px;
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

        .clock-time {
            font-size: 28px;
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
            <li><a href="attendance.php" class="active"><i class="fas fa-user-check"></i> <?php echo $t['attendance']; ?></a></li>
            <li><a href="inventory_view.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory_view']; ?></a></li>
            <li><a href="sales_reports.php"><i class="fas fa-chart-pie"></i> <?php echo $t['sales_reports']; ?></a></li>
            <li><a href="customer_management.php"><i class="fas fa-users"></i> <?php echo $t['customer_management']; ?></a></li>
            <li><a href="loyalty_points.php"><i class="fas fa-award"></i> <?php echo $t['loyalty_points']; ?></a></li>
             <li><a href="messages.php"><i class="fas fa-envelope"></i> <?php echo $t['messages']; ?></a></li>
            <li><a href="employee_profile.php"><i class="fas fa-user-cog"></i> <?php echo $t['profile']; ?></a></li>
        </ul>
        
        <div class="sidebar-footer">
            <form action="employee_logout.php" method="post">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Alerts -->
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

        <!-- Header -->
        <div class="header">
            <h1 class="page-title"><?php echo $t['attendance']; ?></h1>
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
        
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2><?php echo $t['attendance_management']; ?></h2>
                <p><?php echo $t['track_your_work_hours']; ?></p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-user-clock"></i>
            </div>
        </div>

        <!-- Clock Status Card -->
        <div class="clock-status-card <?php echo $is_clocked_in ? '' : 'clocked-out'; ?>">
            <div class="clock-time" id="currentTime"><?php echo date('h:i A'); ?></div>
            <div class="clock-status">
                <?php if ($is_clocked_in): ?>
                    <i class="fas fa-clock"></i> <?php echo $t['currently_clocked_in']; ?>
                <?php else: ?>
                    <i class="fas fa-clock"></i> <?php echo $t['currently_clocked_out']; ?>
                <?php endif; ?>
            </div>
            
            <div class="today-hours">
                <h3><?php echo $t['todays_hours']; ?></h3>
                <div class="hours"><?php echo $today_hours; ?>h <?php echo $today_mins; ?>m</div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center;">
                <?php if (!$is_clocked_in): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="method" value="Manual">
                        <button type="submit" name="clock_in" class="btn btn-primary btn-large">
                            <i class="fas fa-play"></i> <?php echo $t['clock_in']; ?>
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="clock_out" class="btn btn-orange btn-large">
                            <i class="fas fa-stop"></i> <?php echo $t['clock_out']; ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Attendance Tabs -->
        <div class="attendance-tabs">
            <div class="attendance-tab active" data-target="attendance-history">
                <i class="fas fa-history"></i> <?php echo $t['attendance_history']; ?>
            </div>
            <div class="attendance-tab" data-target="attendance-summary">
                <i class="fas fa-chart-bar"></i> <?php echo $t['summary']; ?>
            </div>
        </div>
        
        <!-- Attendance History -->
        <div id="attendance-history" class="dashboard-card">
            <div class="filters" style="margin-bottom: 20px;">
                <form method="GET" id="filterForm">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['date_from']; ?></label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['date_to']; ?></label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['search']; ?></label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="<?php echo $t['search']; ?>..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary"><?php echo $t['filter']; ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> <?php echo $t['attendance_history']; ?></h2>
            </div>
            
            <?php if (!empty($attendance_history)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['date']; ?></th>
                                <th><?php echo $t['clock_in_time']; ?></th>
                                <th><?php echo $t['clock_out_time']; ?></th>
                                <th><?php echo $t['duration']; ?></th>
                                <th><?php echo $t['method']; ?></th>
                                <th><?php echo $t['status']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_history as $record): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                                    <td><?php echo $record['clock_in_time'] ? date('h:i A', strtotime($record['clock_in_time'])) : '-'; ?></td>
                                    <td><?php echo $record['clock_out_time'] ? date('h:i A', strtotime($record['clock_out_time'])) : '-'; ?></td>
                                    <td>
                                        <?php 
                                        $hours = floor($record['duration_minutes'] / 60);
                                        $mins = $record['duration_minutes'] % 60;
                                        echo $hours . 'h ' . $mins . 'm';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['method']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $record['status'] === 'Active' ? 'badge-active' : 'badge-success'; ?>">
                                            <?php echo $record['status'] === 'Active' ? $t['active'] : $t['completed']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-clock"></i>
                    <p><?php echo $t['no_attendance_records']; ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Attendance Summary -->
        <div id="attendance-summary" class="dashboard-card" style="display: none;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-chart-bar"></i> <?php echo $t['summary']; ?></h2>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div style="background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                        <h3 style="margin-bottom: 10px; opacity: 0.9;"><?php echo $t['this_month']; ?></h3>
                        <div style="font-size: 24px; font-weight: 700;">
                            <?php
                            // Calculate this month's total hours
                            $month_query = "SELECT 
                                SUM(CASE 
                                    WHEN logout_time IS NOT NULL THEN 
                                        TIMESTAMPDIFF(MINUTE, login_time, logout_time)
                                    ELSE 0
                                END) as total_minutes
                                FROM attendance_logs 
                                WHERE employee_id = ? AND MONTH(login_time) = MONTH(CURDATE()) AND YEAR(login_time) = YEAR(CURDATE())";
                            
                            $stmt = $conn->prepare($month_query);
                            $stmt->bind_param("i", $employee_id);
                            $stmt->execute();
                            $month_result = $stmt->get_result()->fetch_assoc();
                            $month_minutes = $month_result['total_minutes'] ?? 0;
                            $month_hours = floor($month_minutes / 60);
                            $month_mins = $month_minutes % 60;
                            echo $month_hours . 'h ' . $month_mins . 'm';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div style="background: linear-gradient(135deg, var(--sales-orange) 0%, #c46a38 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                        <h3 style="margin-bottom: 10px; opacity: 0.9;"><?php echo $t['days_present']; ?></h3>
                        <div style="font-size: 24px; font-weight: 700;">
                            <?php
                            // Calculate days present this month
                            $days_query = "SELECT COUNT(DISTINCT DATE(login_time)) as days_present
                                FROM attendance_logs 
                                WHERE employee_id = ? AND MONTH(login_time) = MONTH(CURDATE()) AND YEAR(login_time) = YEAR(CURDATE())";
                            
                            $stmt = $conn->prepare($days_query);
                            $stmt->bind_param("i", $employee_id);
                            $stmt->execute();
                            $days_result = $stmt->get_result()->fetch_assoc();
                            echo $days_result['days_present'] ?? 0;
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div style="background: linear-gradient(135deg, var(--accent-blue) 0%, #357abd 100%); color: white; padding: 20px; border-radius: 10px; text-align: center;">
                        <h3 style="margin-bottom: 10px; opacity: 0.9;"><?php echo $t['average_daily']; ?></h3>
                        <div style="font-size: 24px; font-weight: 700;">
                            <?php
                            $days_present = $days_result['days_present'] ?? 0;
                            if ($days_present > 0) {
                                $avg_minutes = $month_minutes / $days_present;
                                $avg_hours = floor($avg_minutes / 60);
                                $avg_mins = $avg_minutes % 60;
                                echo $avg_hours . 'h ' . round($avg_mins) . 'm';
                            } else {
                                echo '0h 0m';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
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

        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            document.getElementById('currentTime').textContent = timeString;
        }

        // Update time immediately and then every second
        updateTime();
        setInterval(updateTime, 1000);

        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        document.querySelectorAll('.attendance-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.attendance-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding section
                const target = this.getAttribute('data-target');
                document.querySelectorAll('.dashboard-card').forEach(card => {
                    card.style.display = 'none';
                });
                document.getElementById(target).style.display = 'block';
            });
        });

        // Auto-refresh page every 5 minutes to keep attendance status updated
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>