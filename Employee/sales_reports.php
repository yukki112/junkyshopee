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
        'welcome' => 'Welcome',
        'sales_performance_dashboard' => 'Sales Performance Dashboard',
        'track_your_sales' => 'Track your personal sales performance, analyze trends, and generate detailed reports for your transactions.',
        'total_sales' => 'Total Sales',
        'total_purchases' => 'Total Purchases',
        'total_transactions' => 'Total Transactions',
        'points_earned' => 'Points Earned',
        'sales_vs_purchases' => 'Sales vs Purchases Overview',
        'monthly_performance' => 'Monthly Performance',
        'sales_overview' => 'Sales Overview',
        'generate_report' => 'Generate Report',
        'transaction_details' => 'Transaction Details',
        'saved_reports' => 'Saved Reports',
        'period_summary' => 'Period Summary',
        'date_range' => 'Date Range',
        'average_transaction' => 'Average Transaction',
        'generate_new_report' => 'Generate New Report',
        'report_name' => 'Report Name',
        'enter_report_name' => 'Enter report name',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'clear_form' => 'Clear Form',
        'generate_report_btn' => 'Generate Report',
        'transaction_id' => 'Transaction ID',
        'datetime' => 'Date/Time',
        'type' => 'Type',
        'customer' => 'Customer',
        'amount' => 'Amount',
        'points' => 'Points',
        'status' => 'Status',
        'no_transactions_found' => 'No transactions found for the selected period',
        'no_saved_reports' => 'No saved reports found',
        'period' => 'Period',
        'total_profit' => 'Total Profit',
        'items_sold' => 'Items Sold',
        'created' => 'Created',
        'actions' => 'Actions',
        'view_pdf' => 'View PDF',
        'apply_filters' => 'Apply Filters',
        'all_types' => 'All Types',
        'purchase' => 'Purchase',
        'sale' => 'Sale',
        'previous' => 'Previous',
        'next' => 'Next'
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
        'welcome' => 'Maligayang Pagdating',
        'sales_performance_dashboard' => 'Dashboard ng Pagganap sa Pagbebenta',
        'track_your_sales' => 'Subaybayan ang iyong personal na pagganap sa pagbebenta, pag-aralan ang mga trend, at gumawa ng mga detalyadong ulat para sa iyong mga transaksyon.',
        'total_sales' => 'Kabuuang Benta',
        'total_purchases' => 'Kabuuang Pagbili',
        'total_transactions' => 'Kabuuang Transaksyon',
        'points_earned' => 'Mga Nakuha na Points',
        'sales_vs_purchases' => 'Pangkalahatang-ideya ng Benta vs Pagbili',
        'monthly_performance' => 'Buwanang Pagganap',
        'sales_overview' => 'Pangkalahatang-ideya ng Benta',
        'generate_report' => 'Gumawa ng Ulat',
        'transaction_details' => 'Mga Detalye ng Transaksyon',
        'saved_reports' => 'Mga Nai-save na Ulat',
        'period_summary' => 'Buod ng Panahon',
        'date_range' => 'Saklaw ng Petsa',
        'average_transaction' => 'Average na Transaksyon',
        'generate_new_report' => 'Gumawa ng Bagong Ulat',
        'report_name' => 'Pangalan ng Ulat',
        'enter_report_name' => 'Ilagay ang pangalan ng ulat',
        'start_date' => 'Petsa ng Simula',
        'end_date' => 'Petsa ng Pagtatapos',
        'clear_form' => 'I-clear ang Form',
        'generate_report_btn' => 'Gumawa ng Ulat',
        'transaction_id' => 'ID ng Transaksyon',
        'datetime' => 'Petsa/Oras',
        'type' => 'Uri',
        'customer' => 'Customer',
        'amount' => 'Halaga',
        'points' => 'Mga Points',
        'status' => 'Katayuan',
        'no_transactions_found' => 'Walang nakitang mga transaksyon para sa napiling panahon',
        'no_saved_reports' => 'Walang nakitang mga nai-save na ulat',
        'period' => 'Panahon',
        'total_profit' => 'Kabuuang Kita',
        'items_sold' => 'Mga Nabentang Item',
        'created' => 'Ginawa',
        'actions' => 'Mga Aksyon',
        'view_pdf' => 'Tingnan ang PDF',
        'apply_filters' => 'Ilapat ang Mga Filter',
        'all_types' => 'Lahat ng Uri',
        'purchase' => 'Pagbili',
        'sale' => 'Benta',
        'previous' => 'Nakaraan',
        'next' => 'Susunod'
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

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_report'])) {
        $report_name = sanitizeInput($_POST['report_name']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        $sales_query = "SELECT 
                        COUNT(*) as total_transactions,
                        SUM(amount) as total_sales,
                        SUM(CASE WHEN type = 'Purchase' THEN amount ELSE 0 END) as total_purchases,
                        SUM(CASE WHEN type = 'Sale' THEN amount ELSE 0 END) as total_sales_amount,
                        AVG(amount) as avg_transaction
                        FROM transactions 
                        WHERE created_by = ? 
                        AND transaction_date BETWEEN ? AND ? 
                        AND status = 'Completed'";
        
        $stmt = $conn->prepare($sales_query);
        $stmt->bind_param("iss", $employee_id, $start_date, $end_date);
        $stmt->execute();
        $sales_data = $stmt->get_result()->fetch_assoc();
        
        // Calculate estimated profit (assuming 20% markup on purchases)
        $estimated_profit = $sales_data['total_sales_amount'] - ($sales_data['total_purchases'] * 0.8);
        
        // Save report to database
        $insert_report = "INSERT INTO sales_reports 
                         (report_name, start_date, end_date, total_sales, total_profit, items_sold, created_by) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_report);
        $stmt->bind_param("sssddii", $report_name, $start_date, $end_date, 
                         $sales_data['total_sales'], $estimated_profit, 
                         $sales_data['total_transactions'], $employee_id);
        $stmt->execute();
        
        $report_id = $conn->insert_id;
        $_SESSION['success'] = "Sales report generated successfully!";
        header("Location: generate_report_pdf.php?report_id=" . $report_id);
        exit();
    }
}

$date_from = $_GET['date_from'] ?? date('Y-m-01'); // Default to current month start
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Default to today
$transaction_type = $_GET['transaction_type'] ?? null;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 5;
$offset = ($page - 1) * $records_per_page;

$summary_query = "SELECT 
                  COUNT(*) as total_transactions,
                  SUM(amount) as total_amount,
                  SUM(CASE WHEN type = 'Purchase' THEN amount ELSE 0 END) as total_purchases,
                  SUM(CASE WHEN type = 'Sale' THEN amount ELSE 0 END) as total_sales,
                  AVG(amount) as avg_transaction,
                  SUM(points_earned) as total_points_earned
                  FROM transactions 
                  WHERE created_by = ? 
                  AND transaction_date BETWEEN ? AND ? 
                  AND status = 'Completed'";

$params = [$employee_id, $date_from, $date_to];
$types = "iss";

$stmt = $conn->prepare($summary_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

$count_query = "SELECT COUNT(*) as total 
                FROM transactions t 
                WHERE t.created_by = ? 
                AND t.transaction_date BETWEEN ? AND ? 
                AND t.status = 'Completed'";

$count_params = [$employee_id, $date_from, $date_to];
$count_types = "iss";

if ($transaction_type) {
    $count_query .= " AND t.type = ?";
    $count_params[] = $transaction_type;
    $count_types .= "s";
}

$stmt = $conn->prepare($count_query);
$stmt->bind_param($count_types, ...$count_params);
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

$detail_query = "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name, u.username
                 FROM transactions t 
                 LEFT JOIN users u ON t.user_id = u.id 
                 WHERE t.created_by = ? 
                 AND t.transaction_date BETWEEN ? AND ? 
                 AND t.status = 'Completed'";

if ($transaction_type) {
    $detail_query .= " AND t.type = ?";
    $params[] = $transaction_type;
    $types .= "s";
}

$detail_query .= " ORDER BY t.transaction_date DESC, t.transaction_time DESC LIMIT ? OFFSET ?";
$params[] = $records_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($detail_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get saved reports for this employee
$saved_reports_query = "SELECT * FROM sales_reports WHERE created_by = ? ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($saved_reports_query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$saved_reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$monthly_data_query = "SELECT 
    DATE_FORMAT(transaction_date, '%Y-%m') as month,
    SUM(CASE WHEN type = 'Sale' THEN amount ELSE 0 END) as sales,
    SUM(CASE WHEN type = 'Purchase' THEN amount ELSE 0 END) as purchases,
    COUNT(*) as transactions
    FROM transactions 
    WHERE created_by = ? 
    AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    AND status = 'Completed'
    GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6";

$stmt = $conn->prepare($monthly_data_query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$monthly_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Display success/error messages
$success = $_SESSION['success'] ?? $success ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['sales_reports']; ?></title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    /* Updated sidebar styling to match attendance.php design */
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

    /* Main Content */
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

    /* Updated stats grid styling to match attendance.php */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    body.dark-mode .stat-card {
        background: var(--dark-bg-secondary);
        box-shadow: 0 4px 15px var(--dark-shadow);
        border-color: var(--dark-border);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    body.dark-mode .stat-card:hover {
        box-shadow: 0 8px 25px var(--dark-shadow);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
        color: white;
    }

    .stat-icon.sales { background: linear-gradient(135deg, var(--sales-orange), #E8956A); }
    .stat-icon.purchases { background: linear-gradient(135deg, var(--stock-green), #8FA663); }
    .stat-icon.transactions { background: linear-gradient(135deg, var(--accent-blue), #6BA3E8); }
    .stat-icon.points { background: linear-gradient(135deg, #f39c12, #e67e22); }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }

    body.dark-mode .stat-value {
        color: var(--dark-text-primary);
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .stat-label {
        color: var(--dark-text-secondary);
    }

    /* Fixed chart container styling */
    .chart-container {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .chart-container {
        background: var(--dark-bg-secondary);
        box-shadow: 0 4px 15px var(--dark-shadow);
        border-color: var(--dark-border);
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: 0.3px;
        transition: color 0.3s ease;
    }

    body.dark-mode .chart-title {
        color: var(--dark-text-primary);
    }

    .chart-title i {
        color: var(--icon-green);
    }

    #salesChart, #monthlyChart {
        max-width: 100% !important;
        max-height: 300px !important;
        width: 100% !important;
        height: 300px !important;
    }

    /* Sales report specific tabs */
    .sales-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .sales-tab {
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

    body.dark-mode .sales-tab {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .sales-tab:hover {
        background-color: rgba(106, 127, 70, 0.1);
        transform: translateY(-2px);
    }

    body.dark-mode .sales-tab:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .sales-tab.active {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
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
        box-shadow: 0 5px 15px var(--dark-shadow);
        border-color: var(--dark-border);
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
        color: var(--icon-green);
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
        background-color: rgba(106, 127, 70, 0.08);
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
    }

    .badge-warning {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    body.dark-mode .badge-warning {
        background-color: rgba(217, 122, 65, 0.2);
    }

    .badge-info {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
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
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .btn-secondary:hover {
        background-color: #f5f5f5;
        transform: translateY(-2px);
    }

    body.dark-mode .btn-secondary:hover {
        background-color: var(--dark-bg-primary);
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

    /* Added pagination styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        padding: 20px 0;
    }

    .pagination-btn {
        padding: 8px 16px;
        border: 1px solid rgba(0,0,0,0.1);
        background-color: white;
        color: var(--text-dark);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    body.dark-mode .pagination-btn {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .pagination-btn:hover:not(:disabled) {
        background-color: var(--icon-green);
        color: white;
        transform: translateY(-2px);
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-info {
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .pagination-info {
        color: var(--dark-text-primary);
    }

    .page-number {
        padding: 8px 12px;
        border: 1px solid rgba(0,0,0,0.1);
        background-color: white;
        border-radius: 6px;
        font-weight: 600;
        color: var(--icon-green);
        transition: all 0.3s ease;
    }

    body.dark-mode .page-number {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
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
        box-shadow: 0 5px 15px var(--dark-shadow);
        border-color: var(--dark-border);
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

        .sales-tabs {
            flex-wrap: wrap;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .stats-grid {
            grid-template-columns: 1fr;
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
            <li><a href="sales_reports.php" class="active"><i class="fas fa-chart-pie"></i> <?php echo $t['sales_reports']; ?></a></li>
            <li><a href="customer_management.php"><i class="fas fa-users"></i> <?php echo $t['customer_management']; ?></a></li>
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
            <h1 class="page-title"><?php echo $t['sales_reports']; ?></h1>
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
                <h2><?php echo $t['sales_performance_dashboard']; ?></h2>
                <p><?php echo $t['track_your_sales']; ?></p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon sales">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($summary['total_sales'] ?? 0, 2); ?></div>
                <div class="stat-label"><?php echo $t['total_sales']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purchases">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($summary['total_purchases'] ?? 0, 2); ?></div>
                <div class="stat-label"><?php echo $t['total_purchases']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon transactions">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($summary['total_transactions'] ?? 0); ?></div>
                <div class="stat-label"><?php echo $t['total_transactions']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon points">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-value"><?php echo number_format($summary['total_points_earned'] ?? 0); ?></div>
                <div class="stat-label"><?php echo $t['points_earned']; ?></div>
            </div>
        </div>

         
        <div class="form-row">
            <div class="form-col">
                <div class="chart-container">
                    <h3 class="chart-title"><i class="fas fa-chart-pie"></i> <?php echo $t['sales_vs_purchases']; ?></h3>
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="form-col">
                <div class="chart-container">
                    <h3 class="chart-title"><i class="fas fa-chart-line"></i> <?php echo $t['monthly_performance']; ?></h3>
                    <canvas id="monthlyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        
        <div class="sales-tabs">
            <div class="sales-tab active" data-target="sales-overview">
                <i class="fas fa-chart-bar"></i> <?php echo $t['sales_overview']; ?>
            </div>
            <div class="sales-tab" data-target="generate-report">
                <i class="fas fa-file-alt"></i> <?php echo $t['generate_report']; ?>
            </div>
            <div class="sales-tab" data-target="transaction-details">
                <i class="fas fa-list"></i> <?php echo $t['transaction_details']; ?>
            </div>
            <div class="sales-tab" data-target="saved-reports">
                <i class="fas fa-archive"></i> <?php echo $t['saved_reports']; ?>
            </div>
        </div>
        
    
        <div id="sales-overview" class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-chart-bar"></i> <?php echo $t['sales_overview']; ?></h2>
            </div>
            
            <div class="filters">
                <form method="GET" id="filterForm">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['start_date']; ?></label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['end_date']; ?></label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['type']; ?></label>
                            <select name="transaction_type" class="form-control">
                                <option value=""><?php echo $t['all_types']; ?></option>
                                <option value="Purchase" <?php echo $transaction_type === 'Purchase' ? 'selected' : ''; ?>><?php echo $t['purchase']; ?></option>
                                <option value="Sale" <?php echo $transaction_type === 'Sale' ? 'selected' : ''; ?>><?php echo $t['sale']; ?></option>
                            </select>
                        </div>
                        <div class="form-col">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> <?php echo $t['apply_filters']; ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="form-group">
                <div class="form-row">
                    <div class="form-col">
                        <label><?php echo $t['period_summary']; ?></label>
                        <p><strong><?php echo $t['date_range']; ?>:</strong> <?php echo date('M j, Y', strtotime($date_from)); ?> - <?php echo date('M j, Y', strtotime($date_to)); ?></p>
                        <p><strong><?php echo $t['average_transaction']; ?>:</strong> ₱<?php echo number_format($summary['avg_transaction'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

         
        <div id="generate-report" class="dashboard-card" style="display: none;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-file-alt"></i> <?php echo $t['generate_new_report']; ?></h2>
            </div>
            
            <form method="POST" id="reportForm">
                <div class="form-group">
                    <label><?php echo $t['report_name']; ?></label>
                    <input type="text" name="report_name" placeholder="<?php echo $t['enter_report_name']; ?>" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['start_date']; ?></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['end_date']; ?></label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="reset" class="btn btn-secondary"><?php echo $t['clear_form']; ?></button>
                    <button type="submit" name="generate_report" class="btn btn-orange">
                        <i class="fas fa-chart-line"></i> <?php echo $t['generate_report_btn']; ?>
                    </button>
                </div>
            </form>
        </div>

     
        <div id="transaction-details" class="dashboard-card" style="display: none;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-list"></i> <?php echo $t['transaction_details']; ?></h2>
            </div>
            
            <?php if (!empty($transactions)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['transaction_id']; ?></th>
                                <th><?php echo $t['datetime']; ?></th>
                                <th><?php echo $t['type']; ?></th>
                                <th><?php echo $t['customer']; ?></th>
                                <th><?php echo $t['amount']; ?></th>
                                <th><?php echo $t['points']; ?></th>
                                <th><?php echo $t['status']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                    <td><?php 
                                        date_default_timezone_set('Asia/Manila');
                                        echo date('M j, Y h:i A', strtotime($transaction['transaction_date'] . ' ' . $transaction['transaction_time'])); 
                                    ?></td>
                                    <td>
                                        <span class="badge <?php echo $transaction['type'] === 'Sale' ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo htmlspecialchars($transaction['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($transaction['customer_name'] ?? $transaction['name']); ?>
                                        <?php if ($transaction['username']): ?>
                                            <br><small>@<?php echo htmlspecialchars($transaction['username']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>₱<?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td><?php echo number_format($transaction['points_earned']); ?></td>
                                    <td>
                                        <span class="badge badge-success"><?php echo htmlspecialchars($transaction['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Added pagination controls -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?><?php echo $transaction_type ? '&transaction_type=' . $transaction_type : ''; ?>&page=<?php echo $page - 1; ?>#transaction-details" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                            </a>
                        <?php else: ?>
                            <button class="pagination-btn" disabled>
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                            </button>
                        <?php endif; ?>
                        
                        <div class="pagination-info">
                            <span class="page-number"><?php echo $page; ?></span> of <?php echo $total_pages; ?>
                        </div>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?><?php echo $transaction_type ? '&transaction_type=' . $transaction_type : ''; ?>&page=<?php echo $page + 1; ?>#transaction-details" class="pagination-btn">
                                <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <button class="pagination-btn" disabled>
                                <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-chart-line"></i>
                    <p><?php echo $t['no_transactions_found']; ?></p>
                </div>
            <?php endif; ?>
        </div>

      
        <div id="saved-reports" class="dashboard-card" style="display: none;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-archive"></i> <?php echo $t['saved_reports']; ?></h2>
            </div>
            
            <?php if (!empty($saved_reports)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['report_name']; ?></th>
                                <th><?php echo $t['period']; ?></th>
                                <th><?php echo $t['total_sales']; ?></th>
                                <th><?php echo $t['total_profit']; ?></th>
                                <th><?php echo $t['items_sold']; ?></th>
                                <th><?php echo $t['created']; ?></th>
                                <th><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($saved_reports as $report): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($report['report_name']); ?></td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($report['start_date'])); ?> - 
                                        <?php echo date('M j, Y', strtotime($report['end_date'])); ?>
                                    </td>
                                    <td>₱<?php echo number_format($report['total_sales'], 2); ?></td>
                                    <td>₱<?php echo number_format($report['total_profit'], 2); ?></td>
                                    <td><?php echo number_format($report['items_sold']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <!-- Updated to view PDF instead of generic view -->
                                        <a href="view_report_pdf.php?report_id=<?php echo $report['id']; ?>" target="_blank" class="btn btn-secondary">
                                            <i class="fas fa-file-pdf"></i> <?php echo $t['view_pdf']; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-archive"></i>
                    <p><?php echo $t['no_saved_reports']; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal for success message -->
    <div id="successModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 40px; border-radius: 15px; text-align: center; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: modalSlideIn 0.3s ease;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #708B4C, #8FA663); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-check" style="font-size: 40px; color: white;"></i>
            </div>
            <h2 style="color: var(--topbar-brown); margin-bottom: 10px; font-size: 24px; font-weight: 700;">Generation Successful!</h2>
            <p style="color: #666; margin-bottom: 25px; font-size: 15px;">Your sales report has been generated successfully. The PDF will download shortly.</p>
            <div style="display: flex; align-items: center; justify-content: center; gap: 10px; color: var(--icon-green); font-weight: 500;">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Preparing download...</span>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        #successModal {
            display: none;
        }
        
        #successModal.show {
            display: flex !important;
        }
    </style>
    
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

        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Tab switching
        document.querySelectorAll('.sales-tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active tab
                document.querySelectorAll('.sales-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding section
                const target = this.getAttribute('data-target');
                document.querySelectorAll('.dashboard-card').forEach(card => {
                    card.style.display = 'none';
                });
                document.getElementById(target).style.display = 'block';
            });
        });

        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Submit form via AJAX
            fetch('sales_reports.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Check if generation was successful by looking for redirect or success
                // Since PHP redirects on success, we need to handle this differently
                // We'll show the modal and trigger download
                showSuccessModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while generating the report. Please try again.');
            });
        });

        function showSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.add('show');
            
            // Get form data to construct download URL
            const reportName = document.querySelector('input[name="report_name"]').value;
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            
            // Wait 2 seconds then trigger download
            setTimeout(function() {
                // Create a form to submit for PDF generation
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'generate_report_pdf_ajax.php';
                form.style.display = 'none';
                
                const nameInput = document.createElement('input');
                nameInput.name = 'report_name';
                nameInput.value = reportName;
                form.appendChild(nameInput);
                
                const startInput = document.createElement('input');
                startInput.name = 'start_date';
                startInput.value = startDate;
                form.appendChild(startInput);
                
                const endInput = document.createElement('input');
                endInput.name = 'end_date';
                endInput.value = endDate;
                form.appendChild(endInput);
                
                const generateInput = document.createElement('input');
                generateInput.name = 'generate_report';
                generateInput.value = '1';
                form.appendChild(generateInput);
                
                document.body.appendChild(form);
                form.submit();
                
                // Close modal after 3 seconds
                setTimeout(function() {
                    modal.classList.remove('show');
                    // Reset form
                    document.getElementById('reportForm').reset();
                }, 1000);
            }, 2000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sales', 'Purchases'],
                    datasets: [{
                        data: [
                            <?php echo $summary['total_sales'] ?? 0; ?>,
                            <?php echo $summary['total_purchases'] ?? 0; ?>
                        ],
                        backgroundColor: [
                            '#D97A41',
                            '#708B4C'
                        ],
                        borderWidth: 0,
                        hoverBorderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateScale: false,
                        animateRotate: true
                    },
                    hover: {
                        animationDuration: 0
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    },
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10,
                            left: 10,
                            right: 10
                        }
                    }
                }
            });

            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            const monthlyChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: [
                        <?php 
                        $months = array_reverse($monthly_data);
                        foreach ($months as $data) {
                            echo "'" . date('M Y', strtotime($data['month'] . '-01')) . "',";
                        }
                        ?>
                    ],
                    datasets: [{
                        label: 'Sales',
                        data: [
                            <?php 
                            foreach ($months as $data) {
                                echo $data['sales'] . ',';
                            }
                            ?>
                        ],
                        borderColor: '#D97A41',
                        backgroundColor: 'rgba(217, 122, 65, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Purchases',
                        data: [
                            <?php 
                            foreach ($months as $data) {
                                echo $data['purchases'] . ',';
                            }
                            ?>
                        ],
                        borderColor: '#708B4C',
                        backgroundColor: 'rgba(112, 139, 76, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Check if there's a hash in the URL to show a specific tab
            if (window.location.hash) {
                const tab = document.querySelector(`.sales-tab[data-target="${window.location.hash.substring(1)}"]`);
                if (tab) {
                    tab.click();
                }
            }
        });

        function viewReport(reportId) {
            // This would typically open a detailed view or download the report
            alert('Report viewing functionality would be implemented here for report ID: ' + reportId);
        }
    </script>
</body>
</html>