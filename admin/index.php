<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, email, profile_image, is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['is_admin']) {
    session_destroy();
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

$admin_name = $user['first_name'] . ' ' . $user['last_name'];
$admin_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

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
        'welcome_back' => 'Welcome back, %s! 👋',
        'welcome_message' => "Here's what's happening with your business today. You've got %s transactions today with a total sales of ₱%s.",
        'view_full_report' => 'View Full Report',
        'manage_inventory' => 'Manage Inventory',
        'inventory_desc' => 'Add, edit, or remove items from your inventory',
        'user_management' => 'User Management',
        'users_desc' => 'Manage customer accounts and permissions',
        'view_transactions' => 'View Transactions',
        'transactions_desc' => 'Monitor all customer transactions and sales',
        'analytics' => 'Analytics',
        'analytics_desc' => 'Detailed reports and business insights',
        'todays_sales' => "Today's Sales",
        'todays_transactions' => "Today's Transactions",
        'estimated_profit' => 'Estimated Profit',
        'total_users' => 'Total Users',
        'from_yesterday' => 'from yesterday',
        'new_this_week' => 'new this week',
        'sales_overview' => 'Sales Overview',
        'view_detailed_reports' => 'View Detailed Reports',
        'recent_transactions' => 'Recent Transactions',
        'view_all' => 'View All',
        'no_transactions' => 'No recent transactions found',
        'administrator' => 'Administrator',
        'inventory' => 'Inventory',
        'users' => 'Users',
        'pricing_control' => 'Pricing Control',
        'reports_analytics' => 'Reports & Analytics',
        'transactions' => 'Transactions',
        'loyalty_program' => 'Loyalty Program',
        'profile' => 'Profile',
      
        'logout' => 'Logout'
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'welcome_back' => 'Maligayang pagbabalik, %s! 👋',
        'welcome_message' => 'Narito ang nangyayari sa iyong negosyo ngayon. Mayroon kang %s na transaksyon ngayon na may kabuuang benta na ₱%s.',
        'view_full_report' => 'Tingnan ang Buong Ulat',
        'manage_inventory' => 'Pamahalaan ang Inventory',
        'inventory_desc' => 'Magdagdag, mag-edit, o mag-alis ng mga item mula sa iyong inventory',
        'user_management' => 'Pamamahala ng User',
        'users_desc' => 'Pamahalaan ang mga customer account at pahintulot',
        'view_transactions' => 'Tingnan ang mga Transaksyon',
        'transactions_desc' => 'Subaybayan ang lahat ng transaksyon at benta ng customer',
        'analytics' => 'Analytics',
        'analytics_desc' => 'Detalyadong mga ulat at insight sa negosyo',
        'todays_sales' => 'Benta Ngayong Araw',
        'todays_transactions' => 'Mga Transaksyon Ngayong Araw',
        'estimated_profit' => 'Tinatantyang Kita',
        'total_users' => 'Kabuuang Mga User',
        'from_yesterday' => 'mula kahapon',
        'new_this_week' => 'bago ngayong linggo',
        'sales_overview' => 'Pangkalahatang Tanaw ng Benta',
        'view_detailed_reports' => 'Tingnan ang Detalyadong Mga Ulat',
        'recent_transactions' => 'Mga Kamakailang Transaksyon',
        'view_all' => 'Tingnan Lahat',
        'no_transactions' => 'Walang nakitang mga transaksyon',
        'administrator' => 'Administrator',
        'inventory' => 'Inventory',
        'users' => 'Mga User',
        'pricing_control' => 'Kontrol sa Presyo',
        'reports_analytics' => 'Mga Ulat at Analytics',
        'transactions' => 'Mga Transaksyon',
        'loyalty_program' => 'Programa ng Loyalty',
        'profile' => 'Profile',
    
        'logout' => 'Logout'
    ]
];

$t = $translations[$language];

// Get stats for dashboard
$stats = [];
$queries = [
    'today_sales' => "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE DATE(transaction_date) = CURDATE()",
    'today_transactions' => "SELECT COUNT(*) as total FROM transactions WHERE DATE(transaction_date) = CURDATE()",
    'today_profit' => "SELECT COALESCE(SUM(amount * 0.3), 0) as total FROM transactions WHERE DATE(transaction_date) = CURDATE()", // Assuming 30% profit
    'total_users' => "SELECT COUNT(*) as total FROM users" // Changed from active_users to total_users
];

foreach ($queries as $key => $query) {
    try {
        $stmt = $conn->query($query);
        $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        $stats[$key] = 0;
        error_log("Query failed: " . $e->getMessage());
    }
}

// Get recent transactions
$transaction_query = "SELECT 
                        t.transaction_id,
                        t.transaction_type,
                        t.transaction_date,
                        t.transaction_time,
                        t.Item_details,
                        t.status,
                        t.amount,
                        t.points_earned,
                        t.points_redeemed,
                        t.created_at,
                        u.first_name,
                        u.last_name
                     FROM transactions t
                     JOIN users u ON t.user_id = u.id
                     ORDER BY t.transaction_date DESC, t.transaction_time DESC 
                     LIMIT 5";
try {
    $transactions_result = $conn->query($transaction_query);
    $transactions = $transactions_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Transactions query failed: " . $e->getMessage());
    $transactions = [];
}

// Get sales data for charts
$sales_data = ['labels' => [], 'sales' => [], 'count' => []];
$sales_query = "SELECT 
    DATE_FORMAT(transaction_date, '%b') as month,
    SUM(amount) as total_sales,
    COUNT(*) as transaction_count
    FROM transactions
    WHERE transaction_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(transaction_date, '%Y-%m'), DATE_FORMAT(transaction_date, '%b')
    ORDER BY DATE_FORMAT(transaction_date, '%Y-%m')";

try {
    $sales_result = $conn->query($sales_query);
    while ($row = $sales_result->fetch(PDO::FETCH_ASSOC)) {
        $sales_data['labels'][] = $row['month'];
        $sales_data['sales'][] = $row['total_sales'];
        $sales_data['count'][] = $row['transaction_count'];
    }
} catch (PDOException $e) {
    error_log("Sales query failed: " . $e->getMessage());
}

// Get sales by category
$categories = [];
$category_sales = [];
$category_query = "SELECT 
    transaction_type as category,
    SUM(amount) as total_sales
    FROM transactions
    WHERE transaction_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
    GROUP BY transaction_type
    ORDER BY total_sales DESC";

try {
    $category_result = $conn->query($category_query);
    while ($row = $category_result->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row['category'];
        $category_sales[] = $row['total_sales'];
    }
} catch (PDOException $e) {
    error_log("Category query failed: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['dashboard']; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link rel="icon" type="image/png" href="img/MainLogo.svg">
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

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .action-card {
        background-color: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        color: var(--text-dark);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }

    body.dark-mode .action-card {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--sales-orange), var(--icon-green));
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    body.dark-mode .action-card:hover {
        box-shadow: 0 10px 25px var(--dark-shadow);
    }

    .action-icon {
        font-size: 30px;
        color: var(--icon-green);
        margin-bottom: 20px;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background-color: rgba(106, 127, 70, 0.1);
        transition: all 0.3s ease;
    }

    body.dark-mode .action-icon {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .action-card:hover .action-icon {
        transform: rotate(10deg) scale(1.1);
        background-color: rgba(106, 127, 70, 0.2);
    }

    body.dark-mode .action-card:hover .action-icon {
        background-color: rgba(106, 127, 70, 0.3);
    }

    .action-title {
        font-weight: 600;
        font-size: 17px;
        margin-bottom: 10px;
        letter-spacing: 0.3px;
    }

    .action-desc {
        font-size: 14px;
        color: var(--text-dark);
        opacity: 0.8;
        line-height: 1.4;
        transition: color 0.3s ease;
    }

    body.dark-mode .action-desc {
        color: var(--dark-text-secondary);
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

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .stat-card {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
    }

    .stat-card.sales::before {
        background: linear-gradient(90deg, var(--sales-orange), #e67e22);
    }

    .stat-card.transactions::before {
        background: linear-gradient(90deg, var(--icon-green), #27ae60);
    }

    .stat-card.profit::before {
        background: linear-gradient(90deg, var(--topbar-brown), #34495e);
    }

    .stat-card.users::before {
        background: linear-gradient(90deg, #9b59b6, #8e44ad);
    }

    .stat-label {
        font-size: 14px;
        color: var(--text-dark);
        opacity: 0.8;
        margin-bottom: 10px;
        transition: color 0.3s ease;
    }

    body.dark-mode .stat-label {
        color: var(--dark-text-secondary);
    }

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

    .stat-change {
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .stat-change.up {
        color: #27ae60;
    }

    .stat-change.down {
        color: #e74c3c;
    }

    /* Chart Container */
    .chart-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        height: 300px;
    }

    .main-chart, .secondary-chart {
        position: relative;
        height: 100%;
        background-color: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .main-chart,
    body.dark-mode .secondary-chart {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    /* Transactions */
    .transaction-list {
        display: grid;
        gap: 15px;
    }

    .transaction-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 20px;
        border-radius: 12px;
        transition: all 0.3s ease;
        background-color: white;
        box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
    }

    body.dark-mode .transaction-item {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 3px 10px var(--dark-shadow);
    }

    .transaction-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        border-color: rgba(106, 127, 70, 0.3);
    }

    body.dark-mode .transaction-item:hover {
        box-shadow: 0 8px 20px var(--dark-shadow);
    }

    .transaction-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .transaction-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background-color: rgba(106, 127, 70, 0.1);
        color: var(--icon-green);
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    body.dark-mode .transaction-icon {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .transaction-details h4 {
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 15px;
        transition: color 0.3s ease;
    }

    body.dark-mode .transaction-details h4 {
        color: var(--dark-text-primary);
    }

    .transaction-details p {
        font-size: 13px;
        color: var(--text-dark);
        opacity: 0.7;
        transition: color 0.3s ease;
    }

    body.dark-mode .transaction-details p {
        color: var(--dark-text-secondary);
    }

    .transaction-amount {
        text-align: right;
    }

    .amount {
        font-weight: 700;
        font-size: 16px;
        color: var(--icon-green);
        transition: color 0.3s ease;
    }

    body.dark-mode .amount {
        color: var(--icon-green);
    }

    .transaction-date {
        font-size: 12px;
        color: var(--text-dark);
        opacity: 0.6;
        transition: color 0.3s ease;
    }

    body.dark-mode .transaction-date {
        color: var(--dark-text-secondary);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .chart-container {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            position: fixed;
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .main-content {
            padding: 20px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions {
            grid-template-columns: 1fr;
        }
        
        .header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .header-controls {
            align-self: flex-end;
        }
    }

    /* Loading Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dashboard-card, .stat-card, .action-card {
        animation: fadeIn 0.5s ease forwards;
    }

    .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
    .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
    .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
    .action-card:nth-child(1) { animation-delay: 0.1s; }
    .action-card:nth-child(2) { animation-delay: 0.2s; }
    .action-card:nth-child(3) { animation-delay: 0.3s; }
    .action-card:nth-child(4) { animation-delay: 0.4s; }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 6px;
    }

    ::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.05);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--icon-green);
        border-radius: 3px;
    }

    body.dark-mode ::-webkit-scrollbar-track {
        background: var(--dark-bg-tertiary);
    }

    body.dark-mode ::-webkit-scrollbar-thumb {
        background: var(--dark-text-secondary);
    }

    /* Mobile Menu Toggle */
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1000;
        background-color: var(--topbar-brown);
        color: white;
        border: none;
        border-radius: 8px;
        width: 45px;
        height: 45px;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }

    body.dark-mode .mobile-menu-toggle {
        background-color: var(--dark-bg-secondary);
    }

    @media (max-width: 768px) {
        .mobile-menu-toggle {
            display: flex;
        }
    }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                <?php else: ?>
                    <?php echo $admin_initials; ?>
                <?php endif; ?>
            </div>
            <h3 class="user-name"><?php echo htmlspecialchars($admin_name); ?></h3>
            <div class="user-status">
                <span class="status-indicator"></span>
                <span><?php echo $t['administrator']; ?></span>
            </div>
        </div>

      <!-- Language Switcher -->
<div class="language-switcher">
    <button class="language-btn" id="languageBtn">
        <span><?php echo $language === 'en' ? 'English' : 'Filipino'; ?></span>
        <i class="fas fa-chevron-down"></i>
    </button>
    <div class="language-dropdown" id="languageDropdown">
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
            <li><a href="index.php" class="active"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="inventory.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory']; ?></a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> <?php echo $t['users']; ?></a></li>
            <li><a href="pricing.php"><i class="fas fa-tags"></i> <?php echo $t['pricing_control']; ?></a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> <?php echo $t['reports_analytics']; ?></a></li>
            <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> <?php echo $t['transactions']; ?></a></li>
            <li><a href="loyalty.php"><i class="fas fa-award"></i> <?php echo $t['loyalty_program']; ?></a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> <?php echo $t['profile']; ?></a></li>
          
        </ul>

        <div class="sidebar-footer">
            <button class="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <?php echo $t['logout']; ?>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 class="page-title"><?php echo $t['dashboard']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle" id="darkModeToggle">
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
                <h2><?php echo sprintf($t['welcome_back'], htmlspecialchars($user['first_name'])); ?></h2>
                <p><?php echo sprintf($t['welcome_message'], $stats['today_transactions'], number_format($stats['today_sales'], 2)); ?></p>
                <a href="reports.php" class="view-all">
                    <?php echo $t['view_full_report']; ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="inventory.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3 class="action-title"><?php echo $t['manage_inventory']; ?></h3>
                <p class="action-desc"><?php echo $t['inventory_desc']; ?></p>
            </a>
            <a href="users.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="action-title"><?php echo $t['user_management']; ?></h3>
                <p class="action-desc"><?php echo $t['users_desc']; ?></p>
            </a>
            <a href="transactions.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="action-title"><?php echo $t['view_transactions']; ?></h3>
                <p class="action-desc"><?php echo $t['transactions_desc']; ?></p>
            </a>
            <a href="reports.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="action-title"><?php echo $t['analytics']; ?></h3>
                <p class="action-desc"><?php echo $t['analytics_desc']; ?></p>
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card sales">
                <div class="stat-label"><?php echo $t['todays_sales']; ?></div>
                <div class="stat-value">₱<?php echo number_format($stats['today_sales'], 2); ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $t['from_yesterday']; ?></span>
                </div>
            </div>
            <div class="stat-card transactions">
                <div class="stat-label"><?php echo $t['todays_transactions']; ?></div>
                <div class="stat-value"><?php echo $stats['today_transactions']; ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $t['from_yesterday']; ?></span>
                </div>
            </div>
            <div class="stat-card profit">
                <div class="stat-label"><?php echo $t['estimated_profit']; ?></div>
                <div class="stat-value">₱<?php echo number_format($stats['today_profit'], 2); ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $t['from_yesterday']; ?></span>
                </div>
            </div>
            <div class="stat-card users">
                <div class="stat-label"><?php echo $t['total_users']; ?></div>
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i>
                    <span><?php echo $t['new_this_week']; ?></span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    <?php echo $t['sales_overview']; ?>
                </h2>
                <a href="reports.php" class="view-all">
                    <?php echo $t['view_detailed_reports']; ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="chart-container">
                <div class="main-chart">
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="secondary-chart">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-exchange-alt"></i>
                    <?php echo $t['recent_transactions']; ?>
                </h2>
                <a href="transactions.php" class="view-all">
                    <?php echo $t['view_all']; ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="transaction-list">
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <div class="transaction-item">
                            <div class="transaction-info">
                                <div class="transaction-icon">
                                    <i class="fas fa-<?php echo $transaction['transaction_type'] === 'purchase' ? 'shopping-cart' : 'exchange-alt'; ?>"></i>
                                </div>
                                <div class="transaction-details">
                                    <h4><?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($transaction['transaction_type']); ?> - <?php echo htmlspecialchars($transaction['Item_details']); ?></p>
                                </div>
                            </div>
                            <div class="transaction-amount">
                                <div class="amount">₱<?php echo number_format($transaction['amount'], 2); ?></div>
                                <div class="transaction-date">
                                    <?php echo date('M j, Y g:i A', strtotime($transaction['transaction_date'] . ' ' . $transaction['transaction_time'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-dark); opacity: 0.7;">
                        <i class="fas fa-exchange-alt" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p><?php echo $t['no_transactions']; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Dark Mode Toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;

    // Check for saved dark mode preference
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        body.classList.add('dark-mode');
    }

    darkModeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
        updateChartsForDarkMode();
    });

    // Language Dropdown
    const languageBtn = document.getElementById('languageBtn');
    const languageDropdown = document.getElementById('languageDropdown');

    languageBtn.addEventListener('click', () => {
        languageDropdown.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!languageBtn.contains(e.target) && !languageDropdown.contains(e.target)) {
            languageDropdown.classList.remove('active');
        }
    });

    // Mobile Menu Toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');

    mobileMenuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target) && window.innerWidth <= 768) {
            sidebar.classList.remove('active');
        }
    });

    // Logout function
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '../Customer-portal/Login/Login.php?logout=true';
        }
    }

    // Update notification badge
    function updateNotificationBadge() {
        // Simulate notification count
        const badge = document.querySelector('.notification-badge');
        // In a real app, you would fetch this from the server
        badge.textContent = '3';
    }

    // Chart initialization
    let salesChart, categoryChart;

    function initializeCharts() {
        const isDarkMode = body.classList.contains('dark-mode');
        const textColor = isDarkMode ? '#e0e0e0' : '#2E2B29';
        const gridColor = isDarkMode ? '#404040' : 'rgba(0, 0, 0, 0.1)';
        
        // Sales Chart (Line Chart)
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($sales_data['labels']); ?>,
                datasets: [
                    {
                        label: 'Sales (₱)',
                        data: <?php echo json_encode($sales_data['sales']); ?>,
                        borderColor: '#D97A41',
                        backgroundColor: 'rgba(217, 122, 65, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#D97A41',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Transactions',
                        data: <?php echo json_encode($sales_data['count']); ?>,
                        borderColor: '#6A7F46',
                        backgroundColor: 'rgba(106, 127, 70, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6A7F46',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#2d2d2d' : 'rgba(255, 255, 255, 0.9)',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: isDarkMode ? '#404040' : 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Category Chart (Doughnut Chart)
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    data: <?php echo json_encode($category_sales); ?>,
                    backgroundColor: [
                        '#D97A41',
                        '#6A7F46',
                        '#3C342C',
                        '#E6D8C3',
                        '#708B4C',
                        '#9B6B43'
                    ],
                    borderWidth: 2,
                    borderColor: isDarkMode ? '#2d2d2d' : '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: {
                                size: 11
                            },
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#2d2d2d' : 'rgba(255, 255, 255, 0.9)',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: isDarkMode ? '#404040' : 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ₱${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function updateChartsForDarkMode() {
        if (salesChart && categoryChart) {
            salesChart.destroy();
            categoryChart.destroy();
            initializeCharts();
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateNotificationBadge();
        initializeCharts();
        
        // Add loading animation to elements
        const elements = document.querySelectorAll('.dashboard-card, .stat-card, .action-card');
        elements.forEach((element, index) => {
            element.style.animationDelay = `${index * 0.1}s`;
        });
    });

    // Responsive sidebar handling
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
        }
    });
    </script>
</body>
</html>