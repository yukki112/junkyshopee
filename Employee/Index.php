<?php
session_start();
require_once 'db_connection.php';
require_once 'maintenance_check.php';

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
        'welcome' => 'Welcome',
        'material_price_calculator' => 'Material Price Calculator',
        'recent_price_changes' => 'Recent Price Changes',
        'your_recent_transactions' => 'Your Recent Transactions',
        'your_sales_this_week' => 'Your Sales This Week',
        'items_processed' => 'Items Processed',
        'efficiency_rating' => 'Efficiency Rating',
        'customer_interactions' => 'Customer Interactions',
        'calculate' => 'Calculate',
        'no_recent_price_changes' => 'No recent price changes found',
        'no_recent_transactions' => 'No recent transactions found',
        'uncategorized' => 'Uncategorized'
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
        'material_price_calculator' => 'Calculator ng Presyo ng Materyal',
        'recent_price_changes' => 'Mga Kamakailang Pagbabago sa Presyo',
        'your_recent_transactions' => 'Iyong Mga Kamakailang Transaksyon',
        'your_sales_this_week' => 'Iyong Mga Benta Ngayong Linggo',
        'items_processed' => 'Mga Na-prosesong Item',
        'efficiency_rating' => 'Marka ng Kahusayan',
        'customer_interactions' => 'Pakikipag-ugnayan sa Customer',
        'calculate' => 'Kalkulahin',
        'no_recent_price_changes' => 'Walang nakitang mga kamakailang pagbabago sa presyo',
        'no_recent_transactions' => 'Walang nakitang mga kamakailang transaksyon',
        'uncategorized' => 'Hindi Nai-uri'
    ]
];

$t = $translations[$language];

// Get material prices with categories
$materials_query = "SELECT 
    m.id, 
    m.material_option, 
    m.unit_price, 
    m.weight_unit,
    m.trend_direction,
    m.trend_change,
    m.updated_at,
    ic.category_name,
    ic.color_tag,
    ic.icon
FROM materials m
LEFT JOIN item_categories ic ON m.category_id = ic.id
WHERE m.status = 'active'
ORDER BY ic.category_name, m.material_option";

$materials_result = $conn->query($materials_query);
$materials = [];
if ($materials_result) {
    while ($row = $materials_result->fetch_assoc()) {
        $materials[] = $row;
    }
}

// Group materials by category
$categories = [];
$uncategorized_materials = [];

foreach ($materials as $material) {
    $category = $material['category_name'] ?? null;
    if ($category) {
        if (!isset($categories[$category])) {
            $categories[$category] = [
                'name' => $category,
                'color' => $material['color_tag'] ?? '#708B4C',
                'icon' => $material['icon'] ?? 'fa-box',
                'materials' => []
            ];
        }
        $categories[$category]['materials'][] = $material;
    } else {
        $uncategorized_materials[] = $material;
    }
}

// Add uncategorized materials as a separate category
if (!empty($uncategorized_materials)) {
    $categories['Uncategorized'] = [
        'name' => 'Uncategorized',
        'color' => '#6b7785',
        'icon' => 'fa-question-circle',
        'materials' => $uncategorized_materials
    ];
}

// Get recent price changes
$price_changes_query = "SELECT 
    ph.material_id,
    m.material_option,
    ph.old_price,
    ph.new_price,
    ph.change_date,
    ph.reason,
    e.first_name,
    e.last_name
FROM price_history ph
JOIN materials m ON ph.material_id = m.id
JOIN employees e ON ph.changed_by = e.id
ORDER BY ph.change_date DESC
LIMIT 5";

$price_changes_result = $conn->query($price_changes_query);
$price_changes = [];
if ($price_changes_result) {
    while ($row = $price_changes_result->fetch_assoc()) {
        $price_changes[] = $row;
    }
}

// Get recent transactions (removed the created_by filter)
$transactions_query = "SELECT 
    t.transaction_id,
    t.transaction_type,
    t.transaction_date,
    t.transaction_time,
    t.item_details,
    t.status,
    t.amount,
    u.first_name,
    u.last_name
FROM transactions t
JOIN users u ON t.user_id = u.id
ORDER BY t.transaction_date DESC, t.transaction_time DESC
LIMIT 5";

$transactions_result = $conn->query($transactions_query);
$transactions = [];
if ($transactions_result) {
    while ($row = $transactions_result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

// Get employee performance data
$performance_query = "SELECT 
    sales_amount,
    items_processed,
    customer_interactions,
    efficiency_rating,
    period_start,
    period_end
FROM employee_performance
WHERE employee_id = ?
ORDER BY period_end DESC
LIMIT 1";

$performance_stmt = $conn->prepare($performance_query);
$performance_stmt->bind_param("i", $employee_id);
$performance_stmt->execute();
$performance_result = $performance_stmt->get_result();
$performance = $performance_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Employee Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
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
        font-size: 36px;
        font-weight: 800;
        font-family: 'Inter', sans-serif;
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

    /* Quick Stats */
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

    .stat-card.efficiency::before {
        background: linear-gradient(90deg, var(--topbar-brown), #34495e);
    }

    .stat-card.customers::before {
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
        color: var(--dark-text-primary);
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

    /* Material Price Calculator */
    .price-calculator {
        background-color: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .price-calculator {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .calculator-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .calculator-header {
        border-bottom-color: var(--dark-border);
    }

    .calculator-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }

    body.dark-mode .calculator-title {
        color: var(--dark-text-primary);
    }

    .calculator-title i {
        color: var(--icon-green);
    }

    .calculator-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 8px 15px;
        background-color: rgba(106, 127, 70, 0.1);
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        color: var(--text-dark);
    }

    body.dark-mode .tab-btn {
        background-color: rgba(106, 127, 70, 0.2);
        color: var(--dark-text-primary);
    }

    .tab-btn.active {
        background-color: var(--icon-green);
        color: white;
    }

    .tab-btn:hover:not(.active) {
        background-color: rgba(106, 127, 70, 0.2);
    }

    body.dark-mode .tab-btn:hover:not(.active) {
        background-color: rgba(106, 127, 70, 0.3);
    }

    .category-container {
        display: none;
    }

    .category-container.active {
        display: block;
    }

    .material-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .material-card {
        background-color: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .material-card {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 3px 10px var(--dark-shadow);
    }

    .material-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        border-color: rgba(106, 127, 70, 0.3);
    }

    body.dark-mode .material-card:hover {
        box-shadow: 0 8px 20px var(--dark-shadow);
    }

    .material-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .material-name {
        font-weight: 600;
        font-size: 16px;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .material-name {
        color: var(--dark-text-primary);
    }

    .material-price {
        font-weight: 700;
        font-size: 16px;
        color: var(--icon-green);
    }

    .material-details {
        font-size: 13px;
        color: var(--text-dark);
        opacity: 0.7;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        transition: color 0.3s ease;
    }

    body.dark-mode .material-details {
        color: var(--dark-text-secondary);
    }

    .price-trend {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
    }

    .price-trend.up {
        color: #27ae60;
    }

    .price-trend.down {
        color: #e74c3c;
    }

    .price-trend.equal {
        color: var(--text-dark);
        opacity: 0.7;
    }

    body.dark-mode .price-trend.equal {
        color: var(--dark-text-secondary);
    }

    .price-form {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .weight-input {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        background-color: white;
        color: var(--text-dark);
        transition: all 0.3s ease;
    }

    body.dark-mode .weight-input {
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .weight-input:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    .calculate-btn {
        padding: 8px 15px;
        background-color: var(--icon-green);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .calculate-btn:hover {
        background-color: var(--stock-green);
    }

    .result-display {
        margin-top: 10px;
        padding: 10px;
        background-color: rgba(106, 127, 70, 0.1);
        border-radius: 8px;
        font-size: 14px;
        text-align: center;
        display: none;
        transition: all 0.3s ease;
    }

    body.dark-mode .result-display {
        background-color: rgba(106, 127, 70, 0.2);
        color: var(--dark-text-primary);
    }

    /* Recent Price Changes */
    .price-changes {
        background-color: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .price-changes {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .price-change-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        background-color: rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    body.dark-mode .price-change-item {
        background-color: rgba(255,255,255,0.05);
    }

    .price-change-info {
        flex: 1;
    }

    .price-change-material {
        font-weight: 600;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }

    body.dark-mode .price-change-material {
        color: var(--dark-text-primary);
    }

    .price-change-details {
        font-size: 13px;
        color: var(--text-dark);
        opacity: 0.7;
        transition: color 0.3s ease;
    }

    body.dark-mode .price-change-details {
        color: var(--dark-text-secondary);
    }

    .price-change-amount {
        font-weight: 700;
        text-align: right;
    }

    .price-change-old {
        text-decoration: line-through;
        color: #e74c3c;
        font-size: 14px;
    }

    .price-change-new {
        color: #27ae60;
        font-size: 16px;
    }

    .price-change-reason {
        font-size: 12px;
        color: var(--text-dark);
        opacity: 0.7;
        margin-top: 5px;
        transition: color 0.3s ease;
    }

    body.dark-mode .price-change-reason {
        color: var(--dark-text-secondary);
    }

    /* Recent Transactions */
    .transaction-list {
        display: grid;
        gap: 15px;
    }

    .transaction-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        transition: all 0.3s ease;
        background-color: white;
        box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
    }

    body.dark-mode .transaction-item {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 3px 10px var(--dark-shadow);
        border-color: var(--dark-border);
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
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background-color: rgba(106, 127, 70, 0.1);
        color: var(--icon-green);
        flex-shrink: 0;
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
        font-weight: 700;
        font-size: 16px;
        color: var(--icon-green);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 30px 20px;
        color: var(--text-dark);
        opacity: 0.7;
        transition: color 0.3s ease;
    }

    body.dark-mode .empty-state {
        color: var(--dark-text-secondary);
    }

    .empty-state i {
        font-size: 40px;
        color: var(--icon-green);
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 14px;
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
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .material-list {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .material-list {
            grid-template-columns: 1fr;
        }
        
        .price-calculator,
        .price-changes {
            padding: 20px;
        }
        
        .calculator-tabs {
            flex-direction: column;
        }
        
        .tab-btn {
            text-align: left;
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
            <li><a href="Index.php" class="active"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="transaction_logging.php"><i class="fas fa-cash-register"></i> <?php echo $t['transaction_logging']; ?></a></li>
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <?php echo $t['attendance']; ?></a></li>
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
        <div class="header">
            <h1 class="page-title"><?php echo $t['dashboard']; ?></h1>
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
        
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2><?php echo $t['welcome']; ?>, <?php echo htmlspecialchars($employee['first_name']); ?>!</h2>
                <p>Access material prices, log transactions, and assist customers with our easy-to-use tools. Check recent price changes and your performance metrics below.</p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-recycle"></i>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card sales">
                <div class="stat-label"><?php echo $t['your_sales_this_week']; ?></div>
                <div class="stat-value">₱<?php echo number_format($performance['sales_amount'] ?? 0, 2); ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i> 15% from last week
                </div>
            </div>
            
            <div class="stat-card transactions">
                <div class="stat-label"><?php echo $t['items_processed']; ?></div>
                <div class="stat-value"><?php echo $performance['items_processed'] ?? 0; ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i> 8% from last week
                </div>
            </div>
            
            <div class="stat-card efficiency">
                <div class="stat-label"><?php echo $t['efficiency_rating']; ?></div>
                <div class="stat-value"><?php echo ($performance['efficiency_rating'] ?? 0) * 100; ?>%</div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i> 5% improvement
                </div>
            </div>
            
            <div class="stat-card customers">
                <div class="stat-label"><?php echo $t['customer_interactions']; ?></div>
                <div class="stat-value"><?php echo $performance['customer_interactions'] ?? 0; ?></div>
                <div class="stat-change up">
                    <i class="fas fa-arrow-up"></i> 12% from last week
                </div>
            </div>
        </div>
        
        <!-- Material Price Calculator -->
        <div class="price-calculator">
            <div class="calculator-header">
                <h3 class="calculator-title"><i class="fas fa-calculator"></i> <?php echo $t['material_price_calculator']; ?></h3>
            </div>
            
            <div class="calculator-tabs">
                <?php if (!empty($categories)): ?>
                    <?php $first = true; ?>
                    <?php foreach ($categories as $category_name => $category): ?>
                        <button class="tab-btn <?php echo $first ? 'active' : ''; ?>" 
                                data-target="category-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $category_name))); ?>">
                            <i class="fas <?php echo htmlspecialchars($category['icon']); ?>"></i> <?php echo htmlspecialchars($category_name); ?>
                        </button>
                        <?php $first = false; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($categories)): ?>
                <?php $first = true; ?>
                <?php foreach ($categories as $category_name => $category): ?>
                    <div class="category-container <?php echo $first ? 'active' : ''; ?>" 
                         id="category-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $category_name))); ?>">
                        <div class="material-list">
                            <?php foreach ($category['materials'] as $material): ?>
                                <div class="material-card">
                                    <div class="material-header">
                                        <div class="material-name"><?php echo htmlspecialchars($material['material_option']); ?></div>
                                        <div class="material-price">₱<?php echo number_format($material['unit_price'], 2); ?>/<?php echo htmlspecialchars($material['weight_unit']); ?></div>
                                    </div>
                                    <div class="material-details">
                                        <div>
                                            Last updated: <?php echo date('M j, Y', strtotime($material['updated_at'])); ?>
                                        </div>
                                        <div class="price-trend <?php echo htmlspecialchars($material['trend_direction']); ?>">
                                            <?php if ($material['trend_direction'] === 'up'): ?>
                                                <i class="fas fa-arrow-up"></i> +₱<?php echo number_format($material['trend_change'], 2); ?>
                                            <?php elseif ($material['trend_direction'] === 'down'): ?>
                                                <i class="fas fa-arrow-down"></i> -₱<?php echo number_format($material['trend_change'], 2); ?>
                                            <?php else: ?>
                                                <i class="fas fa-equals"></i> No change
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <form class="price-form" data-material-id="<?php echo $material['id']; ?>" data-material-name="<?php echo htmlspecialchars($material['material_option']); ?>" data-unit-price="<?php echo $material['unit_price']; ?>">
                                        <input type="number" class="weight-input" placeholder="Weight in <?php echo htmlspecialchars($material['weight_unit']); ?>" step="0.01" min="0" required>
                                        <button type="submit" class="calculate-btn"><?php echo $t['calculate']; ?></button>
                                    </form>
                                    <div class="result-display" id="result-<?php echo $material['id']; ?>"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $first = false; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Recent Price Changes -->
        <div class="price-changes">
            <div class="calculator-header">
                <h3 class="calculator-title"><i class="fas fa-chart-line"></i> <?php echo $t['recent_price_changes']; ?></h3>
            </div>
            
            <?php if (!empty($price_changes)): ?>
                <?php foreach ($price_changes as $change): ?>
                    <div class="price-change-item">
                        <div class="price-change-info">
                            <div class="price-change-material"><?php echo htmlspecialchars($change['material_option']); ?></div>
                            <div class="price-change-details">
                                Updated by <?php echo htmlspecialchars($change['first_name'] . ' ' . $change['last_name']); ?> on <?php echo date('M j, Y g:i A', strtotime($change['change_date'])); ?>
                            </div>
                            <?php if (!empty($change['reason'])): ?>
                                <div class="price-change-reason">Reason: <?php echo htmlspecialchars($change['reason']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="price-change-amount">
                            <div class="price-change-old">₱<?php echo number_format($change['old_price'], 2); ?></div>
                            <div class="price-change-new">₱<?php echo number_format($change['new_price'], 2); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-info-circle"></i>
                    <p><?php echo $t['no_recent_price_changes']; ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Your Recent Transactions -->
        <div class="price-changes">
            <div class="calculator-header">
                <h3 class="calculator-title"><i class="fas fa-history"></i> <?php echo $t['your_recent_transactions']; ?></h3>
            </div>
            
            <?php if (!empty($transactions)): ?>
                <div class="transaction-list">
                    <?php foreach ($transactions as $transaction): ?>
                        <div class="transaction-item">
                            <div class="transaction-info">
                                <div class="transaction-icon">
                                    <?php 
                                    $icon = '';
                                    $color = 'var(--icon-green)';
                                    switch($transaction['transaction_type']) {
                                        case 'Pickup':
                                            $icon = 'fa-truck-loading';
                                            break;
                                        case 'Walk-in':
                                            $icon = 'fa-coins';
                                            break;
                                        case 'Loyalty':
                                            $icon = 'fa-award';
                                            $color = 'var(--sales-orange)';
                                            break;
                                        default:
                                            $icon = 'fa-exchange-alt';
                                    }
                                    ?>
                                    <i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>"></i>
                                </div>
                                <div class="transaction-details">
                                    <h4><?php echo htmlspecialchars($transaction['transaction_type']); ?> - ₱<?php echo number_format($transaction['amount'], 2); ?></h4>
                                    <p>
                                        <?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?> • 
                                        <?php echo date('M j, Y g:i A', strtotime($transaction['transaction_date'] . ' ' . $transaction['transaction_time'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="transaction-amount" style="color: <?php echo $color; ?>">
                                ₱<?php echo number_format($transaction['amount'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-info-circle"></i>
                    <p><?php echo $t['no_recent_transactions']; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

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

        // Tab switching for material categories
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all tabs and containers
                document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.category-container').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding container
                this.classList.add('active');
                const targetId = this.getAttribute('data-target');
                document.getElementById(targetId).classList.add('active');
            });
        });

        // Price calculation forms
        document.querySelectorAll('.price-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const weightInput = this.querySelector('.weight-input');
                const weight = parseFloat(weightInput.value);
                const materialId = this.getAttribute('data-material-id');
                const materialName = this.getAttribute('data-material-name');
                const unitPrice = parseFloat(this.getAttribute('data-unit-price'));
                
                if (isNaN(weight) || weight <= 0) {
                    alert('Please enter a valid weight');
                    return;
                }
                
                const totalPrice = weight * unitPrice;
                const resultDisplay = document.getElementById(`result-${materialId}`);
                
                resultDisplay.innerHTML = `
                    <strong>${materialName}:</strong> ${weight} kg × ₱${unitPrice.toFixed(2)} = <strong>₱${totalPrice.toFixed(2)}</strong>
                `;
                resultDisplay.style.display = 'block';
            });
        });

        // Highlight outdated prices (updated more than 7 days ago)
        document.querySelectorAll('.material-details').forEach(detail => {
            const updateText = detail.querySelector('div').textContent;
            const updateDate = new Date(updateText.replace('Last updated: ', ''));
            const today = new Date();
            const diffDays = Math.floor((today - updateDate) / (1000 * 60 * 60 * 24));
            
            if (diffDays > 7) {
                const warning = document.createElement('span');
                warning.innerHTML = ' <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Price may be outdated';
                detail.querySelector('div').appendChild(warning);
            }
        });

        // Real-time price updates (simulated - in real implementation, this would use WebSockets or AJAX)
        function checkForPriceUpdates() {
            // This would typically make an AJAX request to check for new price changes
            console.log('Checking for price updates...');
            // In a real implementation, you would update the DOM with new data
        }

        // Check for updates every 30 seconds
        setInterval(checkForPriceUpdates, 30000);
    </script>
</body>
</html>