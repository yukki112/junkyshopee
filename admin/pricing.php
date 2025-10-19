<?php
session_start();
require_once 'db_connection.php';

// Fixed authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

// Then get user info and check admin status separately
$user_id = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, profile_image, is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header("Location: ../Customer-portal/Login/Login.php");
        exit();
    }
    
    // You can use $user['is_admin'] later if you need admin-specific functionality
} catch(PDOException $e) {
    error_log("User query failed: " . $e->getMessage());
    die("Error loading user data.");
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
        'pricing_control' => 'Pricing Control',
        'welcome_message' => 'Manage your material prices, view price history, and schedule price updates for your business operations.',
        'update_prices' => 'Update Prices',
        'current_prices' => 'Current Prices',
        'price_history' => 'Price History',
        'scheduled_updates' => 'Scheduled Updates',
        'pricing_management' => 'Pricing Management',
        'current_material_prices' => 'Current Material Prices',
        'material' => 'Material',
        'buying_price' => 'Buying Price',
        'selling_price' => 'Selling Price',
        'last_updated' => 'Last Updated',
        'price_trend' => 'Price Trend',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'price_change_history' => 'Price Change History',
        'search_price_history' => 'Search price history...',
        'all_materials' => 'All Materials',
        'date' => 'Date',
        'changed_by' => 'Changed By',
        'old_price' => 'Old Price',
        'new_price' => 'New Price',
        'change' => 'Change',
        'reason' => 'Reason',
        'scheduled_price_updates' => 'Scheduled Price Updates',
        'schedule_update' => 'Schedule Update',
        'new_buying_price' => 'New Buying Price',
        'new_selling_price' => 'New Selling Price',
        'scheduled_date' => 'Scheduled Date',
        'created_by' => 'Created By',
        'status' => 'Status',
        'cancel' => 'Cancel',
        'update_material_prices' => 'Update Material Prices',
        'reason_for_change' => 'Reason for Change',
        'update_price' => 'Update Price',
        'schedule_price_update' => 'Schedule Price Update',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Users',
        'reports_analytics' => 'Reports & Analytics',
        'transactions' => 'Transactions',
        'loyalty_program' => 'Loyalty Program',
        'profile' => 'Profile',
       
        'logout' => 'Logout',
        'administrator' => 'Administrator'
    ],
    'tl' => [
        'pricing_control' => 'Kontrol sa Presyo',
        'welcome_message' => 'Pamahalaan ang iyong mga presyo ng materyales, tingnan ang kasaysayan ng presyo, at iskedyul ang mga pag-update ng presyo para sa iyong mga operasyon sa negosyo.',
        'update_prices' => 'I-update ang mga Presyo',
        'current_prices' => 'Kasalukuyang mga Presyo',
        'price_history' => 'Kasaysayan ng Presyo',
        'scheduled_updates' => 'Mga Nakatakdang Update',
        'pricing_management' => 'Pamamahala ng Presyo',
        'current_material_prices' => 'Kasalukuyang mga Presyo ng Materyales',
        'material' => 'Materyal',
        'buying_price' => 'Presyo ng Pagbili',
        'selling_price' => 'Presyo ng Pagbebenta',
        'last_updated' => 'Huling Na-update',
        'price_trend' => 'Trend ng Presyo',
        'actions' => 'Mga Aksyon',
        'edit' => 'I-edit',
        'price_change_history' => 'Kasaysayan ng Pagbabago ng Presyo',
        'search_price_history' => 'Maghanap ng kasaysayan ng presyo...',
        'all_materials' => 'Lahat ng Materyales',
        'date' => 'Petsa',
        'changed_by' => 'Binago Ni',
        'old_price' => 'Lumang Presyo',
        'new_price' => 'Bagong Presyo',
        'change' => 'Pagbabago',
        'reason' => 'Dahilan',
        'scheduled_price_updates' => 'Mga Nakatakdang Pag-update ng Presyo',
        'schedule_update' => 'Iskedyul ng Update',
        'new_buying_price' => 'Bagong Presyo ng Pagbili',
        'new_selling_price' => 'Bagong Presyo ng Pagbebenta',
        'scheduled_date' => 'Nakatakdang Petsa',
        'created_by' => 'Ginawa Ni',
        'status' => 'Katayuan',
        'cancel' => 'Kanselahin',
        'update_material_prices' => 'I-update ang mga Presyo ng Materyales',
        'reason_for_change' => 'Dahilan para sa Pagbabago',
        'update_price' => 'I-update ang Presyo',
        'schedule_price_update' => 'Iskedyul ang Pag-update ng Presyo',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Mga User',
        'reports_analytics' => 'Mga Ulat at Analytics',
        'transactions' => 'Mga Transaksyon',
        'loyalty_program' => 'Programa ng Loyalty',
        'profile' => 'Profile',
     
        'logout' => 'Logout',
        'administrator' => 'Administrator'
    ]
];

$t = $translations[$language];

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'prices';

// Get all materials
try {
    $materials = $conn->query("SELECT * FROM materials WHERE status = 'active' ORDER BY material_option")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Materials query failed: " . $e->getMessage());
    $materials = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update prices
    if (isset($_POST['update_prices'])) {
        try {
            $conn->beginTransaction();
            
            $material_id = intval($_POST['material_id']);
            $new_buying_price = floatval($_POST['buying_price']);
            $new_selling_price = floatval($_POST['selling_price']);
            $reason = sanitizeInput($_POST['reason']);
            
            // Get current price
            $stmt = $conn->prepare("SELECT unit_price FROM materials WHERE id = ?");
            $stmt->execute([$material_id]);
            $material = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$material) {
                throw new Exception("Material not found");
            }
            
            $old_price = $material['unit_price'];
            
            // Update material price
            $stmt = $conn->prepare("UPDATE materials SET unit_price = ? WHERE id = ?");
            $stmt->execute([$new_buying_price, $material_id]);
            
            // Log the price change
            $stmt = $conn->prepare("INSERT INTO price_history 
                (material_id, old_price, new_price, changed_by, reason) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $material_id, 
                $old_price, 
                $new_buying_price, 
                $user_id, 
                $reason
            ]);
            
            $conn->commit();
            $_SESSION['success'] = "Prices updated successfully!";
        } catch(Exception $e) {
            $conn->rollBack();
            error_log("Price update failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update prices: " . $e->getMessage();
        }
        header("Location: pricing.php?tab=prices");
        exit();
    }
    
    // Schedule price update
    if (isset($_POST['schedule_update'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO scheduled_price_updates 
                (material_id, new_buying_price, new_selling_price, scheduled_date, created_by) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                intval($_POST['material_id']),
                floatval($_POST['new_buying_price']),
                floatval($_POST['new_selling_price']),
                sanitizeInput($_POST['scheduled_date']),
                $user_id
            ]);
            $_SESSION['success'] = "Price update scheduled successfully!";
        } catch(PDOException $e) {
            error_log("Schedule insert failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to schedule price update.";
        }
        header("Location: pricing.php?tab=scheduled");
        exit();
    }
}

// Get price history
try {
    $price_history = $conn->query("SELECT 
        ph.*, 
        m.material_option,
        CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
        FROM price_history ph
        JOIN materials m ON ph.material_id = m.id
        JOIN users u ON ph.changed_by = u.id
        ORDER BY ph.change_date DESC
        LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Price history query failed: " . $e->getMessage());
    $price_history = [];
}

// Get scheduled updates
try {
    $scheduled_updates = $conn->query("SELECT 
        sp.*, 
        m.material_option,
        CONCAT(u.first_name, ' ', u.last_name) as created_by_name
        FROM scheduled_price_updates sp
        JOIN materials m ON sp.material_id = m.id
        JOIN users u ON sp.created_by = u.id
        WHERE sp.status = 'pending'
        ORDER BY sp.scheduled_date ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Scheduled updates query failed: " . $e->getMessage());
    $scheduled_updates = [];
}

// Display success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['pricing_control']; ?></title>
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

    /* Pricing Tabs */
    .pricing-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .pricing-tab {
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

    body.dark-mode .pricing-tab {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .pricing-tab:hover {
        background-color: rgba(106, 127, 70, 0.1);
        transform: translateY(-2px);
    }

    body.dark-mode .pricing-tab:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .pricing-tab.active {
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

    /* Search and Filter */
    .search-filter {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .search-box {
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-dark);
        opacity: 0.5;
    }

    body.dark-mode .search-box i {
        color: var(--dark-text-secondary);
    }

    .search-box input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        background-color: white;
        color: var(--text-dark);
    }

    body.dark-mode .search-box input {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    .filter-dropdown {
        padding: 12px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        background-color: white;
        width: 100%;
        transition: all 0.3s;
        color: var(--text-dark);
    }

    body.dark-mode .filter-dropdown {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .filter-dropdown:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
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
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
        border-bottom-color: var(--dark-border);
    }

    td {
        padding: 14px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-size: 14px;
        transition: all 0.3s ease;
    }

    body.dark-mode td {
        color: var(--dark-text-primary);
        border-bottom-color: var(--dark-border);
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

    .badge-warning {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    .badge-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    /* Price Change Indicators */
    .price-change {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .price-up {
        color: #28a745;
    }

    .price-down {
        color: #dc3545;
    }

    .price-neutral {
        color: #6c757d;
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
        text-align: center;
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

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #bb2d3b;
        transform: translateY(-2px);
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
        background-color: var(--dark-bg-secondary);
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

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        animation: fadeIn 0.3s;
        overflow-y: auto;
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 25px;
        border-radius: 12px;
        width: 90%;
        max-width: 700px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        animation: slideDown 0.3s;
        max-height: 90vh;
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 10px 30px var(--dark-shadow);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .modal-header {
        border-bottom-color: var(--dark-border);
    }

    .modal-header h2 {
        font-size: 20px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }

    body.dark-mode .modal-header h2 {
        color: var(--dark-text-primary);
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

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(0,0,0,0.05);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .modal-footer {
        border-top-color: var(--dark-border);
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

        .search-filter {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 36px;
        }

        .pricing-tabs {
            flex-wrap: wrap;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .header-controls {
            gap: 10px;
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

        .modal-content {
            padding: 20px;
        }

        .modal-footer {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }

        .header-controls {
            width: 100%;
            justify-content: space-between;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideDown {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
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
            <li><a href="index.php"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="inventory.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory']; ?></a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> <?php echo $t['users']; ?></a></li>
            <li><a href="pricing.php" class="active"><i class="fas fa-tags"></i> <?php echo $t['pricing_control']; ?></a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> <?php echo $t['reports_analytics']; ?></a></li>
            <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> <?php echo $t['transactions']; ?></a></li>
             <li><a href="loyalty.php" ><i class="fas fa-award"></i> <?php echo $t['loyalty_program']; ?></a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> <?php echo $t['profile']; ?></a></li>
          
        
        <div class="sidebar-footer">
           <a href="../Customer-portal/Login/logout.php" class="logout-btn">
    <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <?php if ($success): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h1 class="page-title"><?php echo $t['pricing_control']; ?></h1>
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
                <h2><?php echo $t['pricing_management']; ?></h2>
                <p><?php echo $t['welcome_message']; ?></p>
                <button class="btn btn-primary" onclick="openUpdatePricesModal()">
                    <i class="fas fa-edit"></i> <?php echo $t['update_prices']; ?>
                </button>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-tags"></i>
            </div>
        </div>
        
        <!-- Pricing Tabs -->
        <div class="pricing-tabs">
            <a href="pricing.php?tab=prices" class="pricing-tab <?php echo $current_tab === 'prices' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i> <?php echo $t['current_prices']; ?>
            </a>
            <a href="pricing.php?tab=history" class="pricing-tab <?php echo $current_tab === 'history' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> <?php echo $t['price_history']; ?>
            </a>
            <a href="pricing.php?tab=scheduled" class="pricing-tab <?php echo $current_tab === 'scheduled' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> <?php echo $t['scheduled_updates']; ?>
            </a>
        </div>
        
        <?php if ($current_tab === 'prices'): ?>
            <!-- Current Prices Tab Content -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> <?php echo $t['current_material_prices']; ?></h3>
                    <div class="card-actions">
                        <button class="btn btn-primary" onclick="openUpdatePricesModal()">
                            <i class="fas fa-edit"></i> <?php echo $t['update_prices']; ?>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['material']; ?></th>
                                <th><?php echo $t['buying_price']; ?> (₱/kg)</th>
                                <th><?php echo $t['selling_price']; ?> (₱/kg)</th>
                                <th><?php echo $t['last_updated']; ?></th>
                                <th><?php echo $t['price_trend']; ?></th>
                                <th><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($materials)): ?>
                                <?php foreach ($materials as $material): 
                                    // Get price history for this material
                                    $stmt = $conn->prepare("SELECT * FROM price_history WHERE material_id = ? ORDER BY change_date DESC LIMIT 1");
                                    $stmt->execute([$material['id']]);
                                    $last_change = $stmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    // Calculate price change
                                    $trend = 'neutral';
                                    $trend_icon = 'fa-equals';
                                    $trend_class = 'price-neutral';
                                    
                                    if ($last_change) {
                                        if ($last_change['new_price'] > $last_change['old_price']) {
                                            $trend = 'up';
                                            $trend_icon = 'fa-arrow-up';
                                            $trend_class = 'price-up';
                                        } elseif ($last_change['new_price'] < $last_change['old_price']) {
                                            $trend = 'down';
                                            $trend_icon = 'fa-arrow-down';
                                            $trend_class = 'price-down';
                                        }
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($material['material_option']); ?></td>
                                        <td>₱<?php echo number_format($material['unit_price'], 2); ?></td>
                                        <td>₱<?php echo number_format($material['unit_price'] * 1.2, 2); ?></td>
                                        <td>
                                            <?php if ($last_change): ?>
                                                <?php echo date('M j, Y', strtotime($last_change['change_date'])); ?>
                                            <?php else: ?>
                                                Never
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="price-change <?php echo $trend_class; ?>">
                                                <i class="fas <?php echo $trend_icon; ?>"></i>
                                                <?php echo ucfirst($trend); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary" onclick="openUpdateSinglePriceModal(<?php echo $material['id']; ?>, '<?php echo htmlspecialchars($material['material_option']); ?>', <?php echo $material['unit_price']; ?>)">
                                                <i class="fas fa-edit"></i> <?php echo $t['edit']; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p>No materials found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($current_tab === 'history'): ?>
            <!-- Price History Tab Content -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> <?php echo $t['price_change_history']; ?></h3>
                </div>
                
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="<?php echo $t['search_price_history']; ?>">
                    </div>
                    <select class="filter-dropdown">
                        <option><?php echo $t['all_materials']; ?></option>
                        <?php foreach ($materials as $material): ?>
                            <option><?php echo htmlspecialchars($material['material_option']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['date']; ?></th>
                                <th><?php echo $t['material']; ?></th>
                                <th><?php echo $t['changed_by']; ?></th>
                                <th><?php echo $t['old_price']; ?></th>
                                <th><?php echo $t['new_price']; ?></th>
                                <th><?php echo $t['change']; ?></th>
                                <th><?php echo $t['reason']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($price_history)): ?>
                                <?php foreach ($price_history as $history): 
                                    $change = $history['new_price'] - $history['old_price'];
                                    $change_class = $change > 0 ? 'price-up' : ($change < 0 ? 'price-down' : 'price-neutral');
                                    $change_icon = $change > 0 ? 'fa-arrow-up' : ($change < 0 ? 'fa-arrow-down' : 'fa-equals');
                                ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($history['change_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($history['material_option']); ?></td>
                                        <td><?php echo htmlspecialchars($history['changed_by_name']); ?></td>
                                        <td>₱<?php echo number_format($history['old_price'], 2); ?></td>
                                        <td>₱<?php echo number_format($history['new_price'], 2); ?></td>
                                        <td>
                                            <span class="price-change <?php echo $change_class; ?>">
                                                <i class="fas <?php echo $change_icon; ?>"></i>
                                                ₱<?php echo number_format(abs($change), 2); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($history['reason']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p>No price history found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($current_tab === 'scheduled'): ?>
            <!-- Scheduled Updates Tab Content -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> <?php echo $t['scheduled_price_updates']; ?></h3>
                    <div class="card-actions">
                        <button class="btn btn-primary" onclick="openScheduleUpdateModal()">
                            <i class="fas fa-plus"></i> <?php echo $t['schedule_update']; ?>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['material']; ?></th>
                                <th><?php echo $t['new_buying_price']; ?></th>
                                <th><?php echo $t['new_selling_price']; ?></th>
                                <th><?php echo $t['scheduled_date']; ?></th>
                                <th><?php echo $t['created_by']; ?></th>
                                <th><?php echo $t['status']; ?></th>
                                <th><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($scheduled_updates)): ?>
                                <?php foreach ($scheduled_updates as $update): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($update['material_option']); ?></td>
                                        <td>₱<?php echo number_format($update['new_buying_price'], 2); ?></td>
                                        <td>₱<?php echo number_format($update['new_selling_price'], 2); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($update['scheduled_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($update['created_by_name']); ?></td>
                                        <td>
                                            <span class="badge badge-warning">Pending</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary">
                                                <i class="fas fa-edit"></i> <?php echo $t['edit']; ?>
                                            </button>
                                            <button class="btn btn-danger">
                                                <i class="fas fa-trash"></i> <?php echo $t['cancel']; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p>No scheduled updates found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Update Prices Modal -->
    <div class="modal" id="updatePricesModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> <?php echo $t['update_material_prices']; ?></h2>
                <button class="close-modal" onclick="closeModal('updatePricesModal')">&times;</button>
            </div>
            <form action="pricing.php?tab=prices" method="POST">
                <div class="form-group">
                    <label for="material_id"><?php echo $t['material']; ?></label>
                    <select id="material_id" name="material_id" class="form-control" required>
                        <?php foreach ($materials as $material): ?>
                            <option value="<?php echo $material['id']; ?>">
                                <?php echo htmlspecialchars($material['material_option']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="buying_price"><?php echo $t['buying_price']; ?> (₱/kg)</label>
                            <input type="number" id="buying_price" name="buying_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="selling_price"><?php echo $t['selling_price']; ?> (₱/kg)</label>
                            <input type="number" id="selling_price" name="selling_price" class="form-control" step="0.01" min="0" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reason"><?php echo $t['reason_for_change']; ?></label>
                    <textarea id="reason" name="reason" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('updatePricesModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="update_prices" class="btn btn-primary"><?php echo $t['update_prices']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Single Price Modal -->
    <div class="modal" id="updateSinglePriceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="singlePriceTitle"><i class="fas fa-edit"></i> <?php echo $t['update_price']; ?></h2>
                <button class="close-modal" onclick="closeModal('updateSinglePriceModal')">&times;</button>
            </div>
            <form action="pricing.php?tab=prices" method="POST">
                <input type="hidden" id="single_material_id" name="material_id">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="single_buying_price"><?php echo $t['buying_price']; ?> (₱/kg)</label>
                            <input type="number" id="single_buying_price" name="buying_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="single_selling_price"><?php echo $t['selling_price']; ?> (₱/kg)</label>
                            <input type="number" id="single_selling_price" name="selling_price" class="form-control" step="0.01" min="0" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="single_reason"><?php echo $t['reason_for_change']; ?></label>
                    <textarea id="single_reason" name="reason" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('updateSinglePriceModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="update_prices" class="btn btn-primary"><?php echo $t['update_prices']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedule Price Update Modal -->
    <div class="modal" id="scheduleUpdateModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-plus"></i> <?php echo $t['schedule_price_update']; ?></h2>
                <button class="close-modal" onclick="closeModal('scheduleUpdateModal')">&times;</button>
            </div>
            <form action="pricing.php?tab=scheduled" method="POST">
                <div class="form-group">
                    <label for="schedule_material_id"><?php echo $t['material']; ?></label>
                    <select id="schedule_material_id" name="material_id" class="form-control" required>
                        <?php foreach ($materials as $material): ?>
                            <option value="<?php echo $material['id']; ?>">
                                <?php echo htmlspecialchars($material['material_option']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="schedule_buying_price"><?php echo $t['new_buying_price']; ?> (₱/kg)</label>
                            <input type="number" id="schedule_buying_price" name="new_buying_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="schedule_selling_price"><?php echo $t['new_selling_price']; ?> (₱/kg)</label>
                            <input type="number" id="schedule_selling_price" name="new_selling_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="scheduled_date"><?php echo $t['scheduled_date']; ?></label>
                    <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" required>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('scheduleUpdateModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="schedule_update" class="btn btn-primary"><?php echo $t['schedule_update']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Mobile menu toggle
    document.getElementById('mobileMenuToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // Language switcher
    document.querySelector('.language-btn').addEventListener('click', function() {
        document.getElementById('languageDropdown').classList.toggle('active');
    });

    // Close language dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const languageSwitcher = document.querySelector('.language-switcher');
        if (!languageSwitcher.contains(event.target)) {
            document.getElementById('languageDropdown').classList.remove('active');
        }
    });

    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;

    // Check for saved dark mode preference
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        body.classList.add('dark-mode');
    }

    darkModeToggle.addEventListener('click', function() {
        body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
    });

    // Modal functions
    function openUpdatePricesModal() {
        document.getElementById('updatePricesModal').style.display = 'flex';
    }

    function openUpdateSinglePriceModal(materialId, materialName, currentPrice) {
        document.getElementById('single_material_id').value = materialId;
        document.getElementById('singlePriceTitle').textContent = '<?php echo $t['update_price']; ?>: ' + materialName;
        document.getElementById('single_buying_price').value = currentPrice;
        document.getElementById('single_selling_price').value = (currentPrice * 1.2).toFixed(2);
        document.getElementById('updateSinglePriceModal').style.display = 'flex';
    }

    function openScheduleUpdateModal() {
        document.getElementById('scheduleUpdateModal').style.display = 'flex';
        // Set default date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById('scheduled_date').valueAsDate = tomorrow;
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Calculate selling price when buying price changes
    document.getElementById('buying_price').addEventListener('input', function() {
        const buyingPrice = parseFloat(this.value) || 0;
        document.getElementById('selling_price').value = (buyingPrice * 1.2).toFixed(2);
    });

    document.getElementById('single_buying_price').addEventListener('input', function() {
        const buyingPrice = parseFloat(this.value) || 0;
        document.getElementById('single_selling_price').value = (buyingPrice * 1.2).toFixed(2);
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }
    
    // Auto-close alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
    </script>
</body>
</html>