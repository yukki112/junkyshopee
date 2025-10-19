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
        'inventory_management' => 'Inventory Management',
        'monitor_stock' => 'Monitor stock levels, track inventory movements, and manage material availability. Keep your inventory organized and efficient.',
        'total_items' => 'Total Items',
        'low_stock_items' => 'Low Stock Items',
        'out_of_stock' => 'Out of Stock',
        'total_inventory_value' => 'Total Inventory Value',
        'current_inventory' => 'Current Inventory',
        'recent_movements' => 'Recent Movements',
        'search_items' => 'Search items, materials, or barcode...',
        'all_categories' => 'All Categories',
        'all_status' => 'All Status',
        'in_stock' => 'In Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'filter' => 'Filter',
        'item_name' => 'Item Name',
        'material' => 'Material',
        'barcode' => 'Barcode',
        'current_stock' => 'Current Stock',
        'stock_level' => 'Stock Level',
        'unit_price' => 'Unit Price',
        'total_value' => 'Total Value',
        'status' => 'Status',
        'location' => 'Location',
        'last_updated' => 'Last Updated',
        'no_inventory_items' => 'No inventory items found',
        'date_time' => 'Date/Time',
        'action' => 'Action',
        'quantity_change' => 'Quantity Change',
        'previous_stock' => 'Previous Stock',
        'new_stock' => 'New Stock',
        'employee' => 'Employee',
        'reason' => 'Reason',
        'no_recent_movements' => 'No recent inventory movements found',
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
        'inventory_management' => 'Pamamahala ng Inventory',
        'monitor_stock' => 'Subaybayan ang mga antas ng stock, subaybayan ang mga paggalaw ng imbentaryo, at pamahalaan ang availability ng materyal. Panatilihing maayos at mahusay ang iyong imbentaryo.',
        'total_items' => 'Kabuuang Mga Item',
        'low_stock_items' => 'Mga Item na Mababa ang Stock',
        'out_of_stock' => 'Walang Stock',
        'total_inventory_value' => 'Kabuuang Halaga ng Inventory',
        'current_inventory' => 'Kasalukuyang Inventory',
        'recent_movements' => 'Mga Kamakailang Paggalaw',
        'search_items' => 'Maghanap ng mga item, materyales, o barcode...',
        'all_categories' => 'Lahat ng Kategorya',
        'all_status' => 'Lahat ng Katayuan',
        'in_stock' => 'May Stock',
        'low_stock' => 'Mababang Stock',
        'out_of_stock' => 'Walang Stock',
        'filter' => 'Salain',
        'item_name' => 'Pangalan ng Item',
        'material' => 'Materyal',
        'barcode' => 'Barcode',
        'current_stock' => 'Kasalukuyang Stock',
        'stock_level' => 'Antas ng Stock',
        'unit_price' => 'Presyo ng Yunit',
        'total_value' => 'Kabuuang Halaga',
        'status' => 'Katayuan',
        'location' => 'Lokasyon',
        'last_updated' => 'Huling Na-update',
        'no_inventory_items' => 'Walang nakitang mga item sa imbentaryo',
        'date_time' => 'Petsa/Oras',
        'action' => 'Aksyon',
        'quantity_change' => 'Pagbabago sa Dami',
        'previous_stock' => 'Nakaraang Stock',
        'new_stock' => 'Bagong Stock',
        'employee' => 'Empleyado',
        'reason' => 'Dahilan',
        'no_recent_movements' => 'Walang nakitang mga kamakailang paggalaw ng imbentaryo',
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

function sanitizeInput($data) {
    global $conn;
    return $conn->real_escape_string(strip_tags(trim($data)));
}

// Get inventory items with material information and stock levels
$search = $_GET['search'] ?? null;
$category = $_GET['category'] ?? null;
$stock_status = $_GET['stock_status'] ?? null;

$query = "SELECT i.*, m.material_option, m.unit_price, m.category_id,
          CASE 
            WHEN i.current_stock <= i.low_stock_threshold THEN 'Low Stock'
            WHEN i.current_stock = 0 THEN 'Out of Stock'
            ELSE 'In Stock'
          END as stock_status,
          CASE 
            WHEN i.current_stock <= i.low_stock_threshold THEN 'danger'
            WHEN i.current_stock <= (i.low_stock_threshold * 1.5) THEN 'warning'
            ELSE 'success'
          END as status_class
         FROM inventory_items i 
         JOIN materials m ON i.material_id = m.id 
         WHERE i.is_active = 1";

$params = [];
$types = "";

// Add search filter
if ($search) {
    $query .= " AND (i.item_name LIKE ? OR m.material_option LIKE ? OR i.barcode LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

// Add category filter
if ($category) {
    $query .= " AND m.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Add stock status filter
if ($stock_status) {
    if ($stock_status === 'low') {
        $query .= " AND i.current_stock <= i.low_stock_threshold AND i.current_stock > 0";
    } elseif ($stock_status === 'out') {
        $query .= " AND i.current_stock = 0";
    } elseif ($stock_status === 'in') {
        $query .= " AND i.current_stock > i.low_stock_threshold";
    }
}

$query .= " ORDER BY i.item_name ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$inventory_items = [];
while ($row = $result->fetch_assoc()) {
    $inventory_items[] = $row;
}

// Get recent inventory movements with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$movements_query = "SELECT SQL_CALC_FOUND_ROWS il.*, i.item_name, m.material_option, 
                   CONCAT(e.first_name, ' ', e.last_name) as employee_name
                   FROM inventory_logs il
                   JOIN inventory_items i ON il.inventory_item_id = i.id
                   JOIN materials m ON i.material_id = m.id
                   JOIN employees e ON il.user_id = e.id
                   ORDER BY il.created_at DESC 
                   LIMIT ? OFFSET ?";
$movements_stmt = $conn->prepare($movements_query);
$movements_stmt->bind_param("ii", $limit, $offset);
$movements_stmt->execute();
$movements_result = $movements_stmt->get_result();
$recent_movements = [];
while ($row = $movements_result->fetch_assoc()) {
    $recent_movements[] = $row;
}

// Get total count for pagination
$total_result = $conn->query("SELECT FOUND_ROWS() as total");
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get inventory statistics
$stats_query = "SELECT 
                COUNT(*) as total_items,
                SUM(CASE WHEN current_stock <= low_stock_threshold AND current_stock > 0 THEN 1 ELSE 0 END) as low_stock_items,
                SUM(CASE WHEN current_stock = 0 THEN 1 ELSE 0 END) as out_of_stock_items,
                SUM(current_stock * (SELECT unit_price FROM materials WHERE id = material_id)) as total_value
                FROM inventory_items 
                WHERE is_active = 1";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get categories for filter dropdown
$categories_query = "SELECT DISTINCT m.category_id, ic.category_name 
                    FROM materials m 
                    LEFT JOIN item_categories ic ON m.category_id = ic.id 
                    WHERE m.status = 'active' AND ic.category_name IS NOT NULL
                    ORDER BY ic.category_name";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// Check if we're on the recent movements tab
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'recent_movements' ? 'recent_movements' : 'current_inventory';

// Display success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Inventory View</title>
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

    /* Stats Cards */
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
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
    }

    body.dark-mode .stat-card {
        background: var(--dark-bg-secondary);
        box-shadow: 0 4px 15px var(--dark-shadow);
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
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.total { background-color: var(--accent-blue); }
    .stat-icon.low { background-color: #ffc107; }
    .stat-icon.out { background-color: #dc3545; }
    .stat-icon.value { background-color: var(--stock-green); }

    .stat-info h3 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .stat-info h3 {
        color: var(--dark-text-primary);
    }

    .stat-info p {
        color: #666;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .stat-info p {
        color: var(--dark-text-secondary);
    }

    /* Inventory Tabs */
    .inventory-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .inventory-tab {
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
    }

    body.dark-mode .inventory-tab {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .inventory-tab:hover {
        background-color: rgba(106, 127, 70, 0.1);
        transform: translateY(-2px);
    }

    body.dark-mode .inventory-tab:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .inventory-tab.active {
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

    /* Filters */
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

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .form-col {
        display: flex;
        flex-direction: column;
    }

    .form-col label {
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .form-col label {
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
        background-color: var(--dark-bg-secondary);
    }

    /* Table Styles */
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
    }

    body.dark-mode th {
        background-color: rgba(106, 127, 70, 0.15);
        color: var(--dark-text-primary);
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

    /* Status Badges */
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

    .badge-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    /* Stock Level Indicators */
    .stock-level {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stock-bar {
        width: 100px;
        height: 8px;
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    body.dark-mode .stock-bar {
        background-color: var(--dark-bg-tertiary);
    }

    .stock-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .stock-fill.success { background-color: #28a745; }
    .stock-fill.warning { background-color: #ffc107; }
    .stock-fill.danger { background-color: #dc3545; }

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

    /* Pagination */
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
        background-color: white;
        color: var(--text-dark);
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 6px;
        text-decoration: none;
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

    .pagination-btn:hover:not(.disabled) {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
        transform: translateY(-2px);
    }

    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-info {
        color: var(--text-dark);
        font-size: 14px;
        transition: color 0.3s ease;
    }

    body.dark-mode .pagination-info {
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

    @media (max-width: 768px) {
        .mobile-menu-toggle {
            display: block;
        }

        .sidebar {
            position: fixed;
            left: -100%;
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar.active {
            left: 0;
        }

        .main-content {
            padding: 80px 20px 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .inventory-tabs {
            flex-direction: column;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .table-responsive {
            padding: 0 15px 15px;
        }

        table {
            font-size: 12px;
        }

        table th,
        table td {
            padding: 10px 8px;
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
                <a href="?lang=en&tab=<?php echo $active_tab; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['stock_status']) ? '&stock_status=' . urlencode($_GET['stock_status']) : ''; ?><?php echo isset($_GET['page']) ? '&page=' . urlencode($_GET['page']) : ''; ?>" class="language-option <?php echo $language === 'en' ? 'active' : ''; ?>">
                    <img src="img/us.png" alt="English" class="flag-icon">
                    <span>English</span>
                </a>
                <a href="?lang=tl&tab=<?php echo $active_tab; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['stock_status']) ? '&stock_status=' . urlencode($_GET['stock_status']) : ''; ?><?php echo isset($_GET['page']) ? '&page=' . urlencode($_GET['page']) : ''; ?>" class="language-option <?php echo $language === 'tl' ? 'active' : ''; ?>">
                    <img src="img/ph.png" alt="Filipino" class="flag-icon">
                    <span>Filipino</span>
                </a>
            </div>
        </div>
        
        <ul class="nav-menu">
            <li><a href="Index.php"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="transaction_logging.php"><i class="fas fa-cash-register"></i> <?php echo $t['transaction_logging']; ?></a></li>
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <?php echo $t['attendance']; ?></a></li>
            <li><a href="inventory_view.php" class="active"><i class="fas fa-boxes"></i> <?php echo $t['inventory_view']; ?></a></li>
            <li><a href="sales_reports.php"><i class="fas fa-chart-pie"></i> <?php echo $t['sales_reports']; ?></a></li>
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
            <h1 class="page-title"><?php echo $t['inventory_view']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge"><?php echo $stats['low_stock_items'] + $stats['out_of_stock_items']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2><?php echo $t['inventory_management']; ?></h2>
                <p><?php echo $t['monitor_stock']; ?></p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-warehouse"></i>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_items']); ?></h3>
                    <p><?php echo $t['total_items']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon low">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['low_stock_items']); ?></h3>
                    <p><?php echo $t['low_stock_items']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon out">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['out_of_stock_items']); ?></h3>
                    <p><?php echo $t['out_of_stock']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon value">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>₱<?php echo number_format($stats['total_value'], 2); ?></h3>
                    <p><?php echo $t['total_inventory_value']; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Inventory Tabs -->
        <div class="inventory-tabs">
            <a href="?tab=current_inventory<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['stock_status']) ? '&stock_status=' . urlencode($_GET['stock_status']) : ''; ?>" class="inventory-tab <?php echo $active_tab === 'current_inventory' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> <?php echo $t['current_inventory']; ?>
            </a>
            <a href="?tab=recent_movements<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['stock_status']) ? '&stock_status=' . urlencode($_GET['stock_status']) : ''; ?>" class="inventory-tab <?php echo $active_tab === 'recent_movements' ? 'active' : ''; ?>">
                <i class="fas fa-exchange-alt"></i> <?php echo $t['recent_movements']; ?>
            </a>
        </div>
        
        <!-- Current Inventory -->
        <div id="current-inventory" class="dashboard-card" style="<?php echo $active_tab === 'current_inventory' ? 'display: block;' : 'display: none;'; ?>">
            <div class="filters">
                <form method="GET" id="filterForm">
                    <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['search_items']; ?></label>
                            <input type="text" name="search" class="form-control" placeholder="<?php echo $t['search_items']; ?>" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['all_categories']; ?></label>
                            <select name="category" class="form-control">
                                <option value=""><?php echo $t['all_categories']; ?></option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($_GET['category'] ?? '') == $cat['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['all_status']; ?></label>
                            <select name="stock_status" class="form-control">
                                <option value=""><?php echo $t['all_status']; ?></option>
                                <option value="in" <?php echo ($_GET['stock_status'] ?? '') == 'in' ? 'selected' : ''; ?>><?php echo $t['in_stock']; ?></option>
                                <option value="low" <?php echo ($_GET['stock_status'] ?? '') == 'low' ? 'selected' : ''; ?>><?php echo $t['low_stock']; ?></option>
                                <option value="out" <?php echo ($_GET['stock_status'] ?? '') == 'out' ? 'selected' : ''; ?>><?php echo $t['out_of_stock']; ?></option>
                            </select>
                        </div>
                        <div class="form-col">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> <?php echo $t['filter']; ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-list"></i> <?php echo $t['current_inventory']; ?></h2>
            </div>
            
            <?php if (!empty($inventory_items)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['item_name']; ?></th>
                                <th><?php echo $t['material']; ?></th>
                                <th><?php echo $t['barcode']; ?></th>
                                <th><?php echo $t['current_stock']; ?></th>
                                <th><?php echo $t['stock_level']; ?></th>
                                <th><?php echo $t['unit_price']; ?></th>
                                <th><?php echo $t['total_value']; ?></th>
                                <th><?php echo $t['status']; ?></th>
                                <th><?php echo $t['location']; ?></th>
                                <th><?php echo $t['last_updated']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory_items as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['material_option']); ?></td>
                                    <td><?php echo htmlspecialchars($item['barcode'] ?? 'N/A'); ?></td>
                                    <td><?php echo number_format($item['current_stock'], 2); ?> <?php echo htmlspecialchars($item['unit']); ?></td>
                                    <td>
                                        <div class="stock-level">
                                            <div class="stock-bar">
                                                <?php 
                                                $percentage = $item['min_stock_level'] > 0 ? min(100, ($item['current_stock'] / $item['min_stock_level']) * 100) : 0;
                                                $fillClass = $item['status_class'];
                                                ?>
                                                <div class="stock-fill <?php echo $fillClass; ?>" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                            <span><?php echo number_format($percentage, 0); ?>%</span>
                                        </div>
                                    </td>
                                    <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td>₱<?php echo number_format($item['current_stock'] * $item['unit_price'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $item['status_class']; ?>">
                                            <?php echo htmlspecialchars($item['stock_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['location'] ?? 'Not specified'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($item['updated_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-boxes"></i>
                    <p><?php echo $t['no_inventory_items']; ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Movements -->
        <div id="recent-movements" class="dashboard-card" style="<?php echo $active_tab === 'recent_movements' ? 'display: block;' : 'display: none;'; ?>">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-exchange-alt"></i> <?php echo $t['recent_movements']; ?></h2>
            </div>
            
            <?php if (!empty($recent_movements)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['date_time']; ?></th>
                                <th><?php echo $t['item_name']; ?></th>
                                <th><?php echo $t['material']; ?></th>
                                <th><?php echo $t['action']; ?></th>
                                <th><?php echo $t['quantity_change']; ?></th>
                                <th><?php echo $t['previous_stock']; ?></th>
                                <th><?php echo $t['new_stock']; ?></th>
                                <th><?php echo $t['employee']; ?></th>
                                <th><?php echo $t['reason']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_movements as $movement): ?>
                                <tr>
                                    <td><?php echo date('M j, Y h:i A', strtotime($movement['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($movement['item_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($movement['material_option']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $movement['action_type'] === 'addition' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($movement['action_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $movement['action_type'] === 'addition' ? '+' : '-'; ?><?php echo number_format($movement['quantity_change'], 2); ?> kg
                                    </td>
                                    <td><?php echo number_format($movement['previous_stock'], 2); ?> kg</td>
                                    <td><?php echo number_format($movement['new_stock'], 2); ?> kg</td>
                                    <td><?php echo htmlspecialchars($movement['employee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($movement['reason'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?tab=recent_movements&page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['stock_status']) ? '&stock_status=' . urlencode($_GET['stock_status']) : ''; ?>" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn disabled">
                            <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                        </span>
                    <?php endif; ?>
                    
                    <span class="pagination-info">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?tab=recent_movements&page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['stock_status']) ? '&stock_status=' . urlencode($_GET['stock_status']) : ''; ?>" class="pagination-btn">
                            <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn disabled">
                            <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <p><?php echo $t['no_recent_movements']; ?></p>
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

        // Tab switching
        document.querySelectorAll('.inventory-tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                // The tab switching is now handled by PHP through URL parameters
                // This prevents default behavior only for JavaScript enhancement
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    
                    // Update active tab
                    document.querySelectorAll('.inventory-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding section
                    const target = this.getAttribute('href').substring(1);
                    document.querySelectorAll('.dashboard-card').forEach(card => {
                        card.style.display = 'none';
                    });
                    document.getElementById(target).style.display = 'block';
                }
            });
        });

        // Auto-refresh notification badge based on low stock alerts
        function updateNotificationBadge() {
            const lowStockCount = <?php echo $stats['low_stock_items'] + $stats['out_of_stock_items']; ?>;
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                badge.textContent = lowStockCount;
                if (lowStockCount > 0) {
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateNotificationBadge();
        });
    </script>
</body>
</html>