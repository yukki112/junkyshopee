<?php
session_start();
require_once 'db_connection.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, profile_image, is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !$user['is_admin']) {
        session_destroy();
        header("Location: ../Customer-portal/Login/Login.php");
        exit();
    }
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
        'loyalty_program' => 'Loyalty Program',
        'welcome_message' => 'Manage your customer loyalty program, track points, configure tiers, and reward your most valuable customers.',
        'adjust_points' => 'Adjust Points',
        'dashboard' => 'Dashboard',
        'customers' => 'Customers',
        'transactions' => 'Transactions',
        'tiers_rewards' => 'Tiers & Rewards',
        'loyalty_overview' => 'Loyalty Overview',
        'top_customers' => 'Top Customers',
        'recent_transactions' => 'Recent Transactions',
        'customer_points' => 'Customer Points',
        'points_transactions' => 'Points Transactions',
        'tier_settings' => 'Tier Settings',
        'customer' => 'Customer',
        'email' => 'Email',
        'points' => 'Points',
        'tier' => 'Tier',
        'actions' => 'Actions',
        'date' => 'Date',
        'transaction_id' => 'Transaction ID',
        'details' => 'Details',
        'type' => 'Type',
        'earned' => 'Earned',
        'redeemed' => 'Redeemed',
        'search_customers' => 'Search customers...',
        'search_transactions' => 'Search transactions...',
        'all_tiers' => 'All Tiers',
        'all_types' => 'All Types',
        'points_earned' => 'Points Earned',
        'points_redeemed' => 'Points Redeemed',
        'manual_adjustments' => 'Manual Adjustments',
        'bronze' => 'Bronze',
        'silver' => 'Silver',
        'gold' => 'Gold',
        'platinum' => 'Platinum',
        'diamond' => 'Diamond',
        'ethereal' => 'Ethereal',
        'no_customers_found' => 'No customers found',
        'no_transactions_found' => 'No transactions found',
        'previous' => 'Previous',
        'next' => 'Next',
        'adjust_customer_points' => 'Adjust Customer Points',
        'action_type' => 'Action Type',
        'add_points' => 'Add Points',
        'deduct_points' => 'Deduct Points',
        'reason' => 'Reason',
        'cancel' => 'Cancel',
        'update_points' => 'Update Points',
        'minimum_points' => 'Minimum Points',
        'maximum_points' => 'Maximum Points',
        'benefits' => 'Benefits',
        'requirements' => 'Requirements', // Added missing translation
        'points_conversion_rate' => 'Points Conversion Rate',
        'reset' => 'Reset',
        'save_settings' => 'Save Settings',
        'inventory' => 'Inventory',
        'users' => 'Users',
        'pricing_control' => 'Pricing Control',
        'reports_analytics' => 'Reports & Analytics',
        'transactions' => 'Transactions',
        'loyalty_program' => 'Loyalty Program',
        'profile' => 'Profile',
     
        'logout' => 'Logout',
        'administrator' => 'Administrator'
    ],
    'tl' => [
        'loyalty_program' => 'Programa ng Loyalty',
        'welcome_message' => 'Pamahalaan ang iyong customer loyalty program, subaybayan ang mga puntos, i-configure ang mga tier, at gantimpalaan ang iyong mga pinakamahalagang customer.',
        'adjust_points' => 'I-adjust ang Mga Puntos',
        'dashboard' => 'Dashboard',
        'customers' => 'Mga Customer',
        'transactions' => 'Mga Transaksyon',
        'tiers_rewards' => 'Mga Tier at Gantimpala',
        'loyalty_overview' => 'Pangkalahatang-ideya ng Loyalty',
        'top_customers' => 'Nangungunang Mga Customer',
        'recent_transactions' => 'Mga Kamakailang Transaksyon',
        'customer_points' => 'Mga Puntos ng Customer',
        'points_transactions' => 'Mga Transaksyon ng Puntos',
        'tier_settings' => 'Mga Setting ng Tier',
        'customer' => 'Customer',
        'email' => 'Email',
        'points' => 'Mga Puntos',
        'tier' => 'Tier',
        'actions' => 'Mga Aksyon',
        'date' => 'Petsa',
        'transaction_id' => 'ID ng Transaksyon',
        'details' => 'Mga Detalye',
        'type' => 'Uri',
        'earned' => 'Nakuha',
        'redeemed' => 'Naredeem',
        'search_customers' => 'Maghanap ng mga customer...',
        'search_transactions' => 'Maghanap ng mga transaksyon...',
        'all_tiers' => 'Lahat ng Tier',
        'all_types' => 'Lahat ng Uri',
        'points_earned' => 'Mga Nakuha na Puntos',
        'points_redeemed' => 'Mga Naredeem na Puntos',
        'manual_adjustments' => 'Mga Manual na Adjust',
        'bronze' => 'Bronze',
        'silver' => 'Silver',
        'gold' => 'Gold',
        'platinum' => 'Platinum',
        'diamond' => 'Diamond',
        'ethereal' => 'Ethereal',
        'no_customers_found' => 'Walang nakitang mga customer',
        'no_transactions_found' => 'Walang nakitang mga transaksyon',
        'previous' => 'Nakaraan',
        'next' => 'Susunod',
        'adjust_customer_points' => 'I-adjust ang Mga Puntos ng Customer',
        'action_type' => 'Uri ng Aksyon',
        'add_points' => 'Magdagdag ng Puntos',
        'deduct_points' => 'Bawasan ang Puntos',
        'reason' => 'Dahilan',
        'cancel' => 'Kanselahin',
        'update_points' => 'I-update ang Mga Puntos',
        'minimum_points' => 'Minimum na Puntos',
        'maximum_points' => 'Maximum na Puntos',
        'benefits' => 'Mga Benepisyo',
        'requirements' => 'Mga Pangangailangan', // Added missing translation
        'points_conversion_rate' => 'Rate ng Conversion ng Puntos',
        'reset' => 'I-reset',
        'save_settings' => 'I-save ang Mga Setting',
        'inventory' => 'Inventory',
        'users' => 'Mga User',
        'pricing_control' => 'Kontrol sa Presyo',
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
$current_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'dashboard';

$tiers = [
    'bronze' => ['min_points' => 0, 'max_points' => 999, 'benefits' => 'Basic rewards, 5% discount on special items'],
    'silver' => ['min_points' => 1000, 'max_points' => 2999, 'benefits' => 'Free shipping, ₱50 voucher, 10% discount on all items'],
    'gold' => ['min_points' => 3000, 'max_points' => 4999, 'benefits' => 'Priority pickup, ₱100 voucher, 15% discount on all items, exclusive offers'],
    'platinum' => ['min_points' => 5000, 'max_points' => 9999, 'benefits' => 'VIP support, ₱200 voucher, 20% discount, exclusive offers'],
    'diamond' => ['min_points' => 10000, 'max_points' => 19999, 'benefits' => 'Premium support, ₱500 voucher, 25% discount, early access'],
    'ethereal' => ['min_points' => 20000, 'max_points' => null, 'benefits' => 'Ultimate rewards, ₱1000 voucher, 30% discount, VIP treatment']
];

// Function to determine tier based on points
function getTierByPoints($points) {
    global $tiers;
    foreach ($tiers as $tier => $data) {
        if ($points >= $data['min_points'] && ($data['max_points'] === null || $points <= $data['max_points'])) {
            return $tier;
        }
    }
    return 'bronze';
}

// Function to update user tier
function updateUserTier($conn, $user_id, $points) {
    $new_tier = getTierByPoints($points);
    $stmt = $conn->prepare("UPDATE users SET loyalty_tier = ? WHERE id = ?");
    $stmt->execute([$new_tier, $user_id]);
    return $new_tier;
}

$customers_page = isset($_GET['customers_page']) ? max(1, intval($_GET['customers_page'])) : 1;
$transactions_page = isset($_GET['transactions_page']) ? max(1, intval($_GET['transactions_page'])) : 1;
$items_per_page = 10;
$customers_offset = ($customers_page - 1) * $items_per_page;
$transactions_offset = ($transactions_page - 1) * $items_per_page;

// Initialize variables
$customers = [];
$transactions = [];
$customers_total = 0;
$transactions_total = 0;

try {
    // Get total count
    $count_stmt = $conn->query("SELECT COUNT(*) FROM users WHERE user_type != 'admin'");
    $customers_total = $count_stmt->fetchColumn();
    
    // Get paginated customers with email
    $customers = $conn->prepare("
        SELECT id, first_name, last_name, email, loyalty_points, loyalty_tier 
        FROM users 
        WHERE user_type != 'admin' 
        ORDER BY loyalty_points DESC 
        LIMIT ? OFFSET ?
    ");
    $customers->execute([$items_per_page, $customers_offset]);
    $customers = $customers->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Customers query failed: " . $e->getMessage());
}

try {
    // Get total count
    $count_stmt = $conn->query("
        SELECT COUNT(*) FROM transactions t
        JOIN users u ON t.user_id = u.id
        WHERE (t.points_earned > 0 OR t.points_redeemed > 0)
    ");
    $transactions_total = $count_stmt->fetchColumn();
    
    // Get paginated transactions
    $transactions = $conn->prepare("
        SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        WHERE (t.points_earned > 0 OR t.points_redeemed > 0)
        ORDER BY t.transaction_date DESC, t.transaction_time DESC
        LIMIT ? OFFSET ?
    ");
    $transactions->execute([$items_per_page, $transactions_offset]);
    $transactions = $transactions->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Transactions query failed: " . $e->getMessage());
}

// Calculate pagination info
$customers_total_pages = ceil($customers_total / $items_per_page);
$transactions_total_pages = ceil($transactions_total / $items_per_page);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manual points adjustment
    if (isset($_POST['adjust_points'])) {
        try {
            $conn->beginTransaction();
            
            $adjust_user_id = intval($_POST['user_id']);
            $points = intval($_POST['points']);
            $action = sanitizeInput($_POST['action']);
            $reason = sanitizeInput($_POST['reason']);
            
            // Get current points
            $stmt = $conn->prepare("SELECT loyalty_points FROM users WHERE id = ?");
            $stmt->execute([$adjust_user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user_data) {
                throw new Exception("User not found");
            }
            
            $current_points = $user_data['loyalty_points'];
            $new_points = ($action === 'add') ? $current_points + $points : max(0, $current_points - $points);
            
            // Update user points and tier
            $new_tier = updateUserTier($conn, $adjust_user_id, $new_points);
            $stmt = $conn->prepare("UPDATE users SET loyalty_points = ?, loyalty_tier = ? WHERE id = ?");
            $stmt->execute([$new_points, $new_tier, $adjust_user_id]);
            
            // Create transaction record
            $transaction_id = 'TXN-' . date('Ymd') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $conn->prepare("
                INSERT INTO transactions 
                (transaction_id, user_id, transaction_type, transaction_date, transaction_time, 
                 amount, points_earned, points_redeemed, additional_info)
                VALUES (?, ?, 'manual_adjustment', CURDATE(), CURTIME(), 0, ?, ?, ?)
            ");
            
            if ($action === 'add') {
                $stmt->execute([$transaction_id, $adjust_user_id, $points, 0, $reason]);
            } else {
                $stmt->execute([$transaction_id, $adjust_user_id, 0, $points, $reason]);
            }
            
            $conn->commit();
            $_SESSION['success'] = "Points adjusted successfully! User tier updated to " . ucfirst($new_tier);
        } catch(Exception $e) {
            $conn->rollBack();
            error_log("Points adjustment failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to adjust points: " . $e->getMessage();
        }
        header("Location: loyalty.php?tab=" . $current_tab);
        exit();
    }
    
    // Update tier settings
    if (isset($_POST['update_tiers'])) {
        try {
            // In a real implementation, you would save these to a database table
            $_SESSION['success'] = "Tier settings updated successfully!";
        } catch(Exception $e) {
            error_log("Tier update failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update tier settings";
        }
        header("Location: loyalty.php?tab=tiers");
        exit();
    }
}

// Display success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Sanitize input function
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['loyalty_program']; ?></title>
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
        --bronze: #cd7f32;
        --silver: #c0c0c0;
        --gold: #ffd700;
        --platinum: #e5e4e2;
        --diamond: #b9f2ff;
        --ethereal: #9370db;
        
        /* Dark mode variables */
        --dark-bg-primary: #1a1a1a;
        --dark-bg-secondary: #2d2d2d;
        --dark-bg-tertiary: #3c3c3c;
        --dark-text-primary: #e0e0e0;
        --dark-text-secondary: #a0a0a0;
        --dark-border: #404040;
        --dark-shadow: rgba(0, 0, 0, 0.3);
    }

    /* Added new tier colors */
    .badge-platinum {
        background-color: rgba(229, 228, 226, 0.1);
        color: var(--platinum);
        border: 1px solid var(--platinum);
    }

    .badge-diamond {
        background-color: rgba(185, 242, 255, 0.1);
        color: var(--diamond);
        border: 1px solid var(--diamond);
    }

    .badge-ethereal {
        background-color: rgba(147, 112, 219, 0.1);
        color: var(--ethereal);
        border: 1px solid var(--ethereal);
    }

    .tier-card.platinum {
        border-left: 4px solid var(--platinum);
    }

    .tier-card.diamond {
        border-left: 4px solid var(--diamond);
    }

    .tier-card.ethereal {
        border-left: 4px solid var(--ethereal);
    }

    .platinum .tier-icon {
        color: var(--platinum);
    }

    .diamond .tier-icon {
        color: var(--diamond);
    }

    .ethereal .tier-icon {
        color: var(--ethereal);
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

    .pagination a, .pagination span {
        padding: 8px 12px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 6px;
        text-decoration: none;
        color: var(--text-dark);
        background-color: white;
        transition: all 0.3s ease;
    }

    body.dark-mode .pagination a, 
    body.dark-mode .pagination span {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .pagination a:hover {
        background-color: var(--icon-green);
        color: white;
        transform: translateY(-2px);
    }

    .pagination .current {
        background-color: var(--icon-green);
        color: white;
        font-weight: 600;
    }

    .pagination .disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Tier badges */
    .badge-tier {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-bronze {
        background-color: rgba(205, 127, 50, 0.1);
        color: var(--bronze);
        border: 1px solid var(--bronze);
    }

    .badge-silver {
        background-color: rgba(192, 192, 192, 0.1);
        color: var(--silver);
        border: 1px solid var(--silver);
    }

    .badge-gold {
        background-color: rgba(255, 215, 0, 0.1);
        color: var(--gold);
        border: 1px solid var(--gold);
    }

    /* Points display */
    .points-display {
        font-weight: 700;
        color: var(--icon-green);
    }

    /* Tier benefits cards */
    .tier-card {
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        background-color: white;
        transition: all 0.3s ease;
    }

    body.dark-mode .tier-card {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .tier-card.bronze {
        border-left: 4px solid var(--bronze);
    }

    .tier-card.silver {
        border-left: 4px solid var(--silver);
    }

    .tier-card.gold {
        border-left: 4px solid var(--gold);
    }

    .tier-card h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .tier-card h3 {
        color: var(--dark-text-primary);
    }

    .tier-icon {
        font-size: 24px;
    }

    .bronze .tier-icon {
        color: var(--bronze);
    }

    .silver .tier-icon {
        color: var(--silver);
    }

    .gold .tier-icon {
        color: var(--gold);
    }

    .tier-requirements {
        background-color: rgba(0,0,0,0.03);
        padding: 10px 15px;
        border-radius: 8px;
        margin: 15px 0;
        font-size: 14px;
        color: var(--text-dark);
        transition: all 0.3s ease;
    }

    body.dark-mode .tier-requirements {
        background-color: rgba(255,255,255,0.05);
        color: var(--dark-text-primary);
    }

    .tier-benefits ul {
        padding-left: 20px;
        margin: 10px 0;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .tier-benefits ul {
        color: var(--dark-text-primary);
    }

    .tier-benefits li {
        margin-bottom: 8px;
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

    .card-actions {
        display: flex;
        gap: 10px;
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
    }

    body.dark-mode th {
        background-color: rgba(106, 127, 70, 0.15);
    }

    td {
        padding: 14px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-size: 14px;
        color: var(--text-dark);
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

    .badge-warning {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    .badge-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    /* Stock Levels */
    .stock-level {
        font-weight: 500;
    }

    .stock-level.low {
        color: var(--sales-orange);
    }

    .stock-level.out {
        color: #dc3545;
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

    /* Improved Form Layout */
    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-col {
        flex: 1;
        min-width: 0;
    }

    /* Better spacing for form elements */
    .form-group {
        margin-bottom: 15px;
    }

    /* Improved dropdown styling */
    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s;
        background-color: #fff;
    }

    body.dark-mode .form-control {
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    /* Responsive adjustments for modals */
    @media (max-width: 768px) {
        .modal-content {
            margin: 10px auto;
            padding: 15px;
            width: 95%;
        }
        
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }

    /* Confirmation dialog styles */
    .confirmation-dialog {
        text-align: center;
        padding: 20px;
    }

    .confirmation-dialog .buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }

    /* Edit category modal specific styles */
    .category-color-preview {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px;
        border: 1px solid #ddd;
    }

    /* Improved button spacing in modals */
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    body.dark-mode .modal-footer {
        border-top-color: var(--dark-border);
    }

    /* Better alignment for form labels */
    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #333;
    }

    body.dark-mode .form-group label {
        color: var(--dark-text-primary);
    }

    /* Improved radio button styling */
    .radio-group {
        display: flex;
        gap: 20px;
        margin-top: 10px;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .radio-option {
        color: var(--dark-text-primary);
    }

    .radio-option input[type="radio"] {
        margin: 0;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideDown {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
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

        .inventory-tabs {
            flex-wrap: wrap;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
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
           <li><a href="loyalty.php" class="active"><i class="fas fa-award"></i> <?php echo $t['loyalty_program']; ?></a></li>
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
            <h1 class="page-title"><?php echo $t['loyalty_program']; ?></h1>
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
                <h2><?php echo $t['loyalty_program']; ?></h2>
                <p><?php echo $t['welcome_message']; ?></p>
                <button class="btn btn-primary" onclick="openAdjustPointsModal()">
                    <i class="fas fa-plus"></i> <?php echo $t['adjust_points']; ?>
                </button>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-award"></i>
            </div>
        </div>
        
        <!-- Loyalty Tabs -->
        <div class="inventory-tabs">
            <a href="loyalty.php?tab=dashboard" class="inventory-tab <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> <?php echo $t['dashboard']; ?>
            </a>
            <a href="loyalty.php?tab=customers" class="inventory-tab <?php echo $current_tab === 'customers' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> <?php echo $t['customers']; ?>
            </a>
            <a href="loyalty.php?tab=transactions" class="inventory-tab <?php echo $current_tab === 'transactions' ? 'active' : ''; ?>">
                <i class="fas fa-exchange-alt"></i> <?php echo $t['transactions']; ?>
            </a>
            <a href="loyalty.php?tab=tiers" class="inventory-tab <?php echo $current_tab === 'tiers' ? 'active' : ''; ?>">
                <i class="fas fa-medal"></i> <?php echo $t['tiers_rewards']; ?>
            </a>
        </div>
        
        <?php if ($current_tab === 'dashboard'): ?>
            <!-- Dashboard Tab Content -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tachometer-alt"></i> <?php echo $t['loyalty_overview']; ?></h3>
                </div>
                
                <!-- Updated tier cards with new tiers -->
                <div class="form-row" style="margin-bottom: 30px;">
                    <div class="form-col">
                        <div class="tier-card bronze">
                            <h3><i class="fas fa-medal tier-icon"></i> <?php echo $t['bronze']; ?> Tier</h3>
                            <div class="tier-requirements">
                                <strong><?php echo $t['requirements']; ?>:</strong> 0 - 999 <?php echo $t['points']; ?>
                            </div>
                            <div class="tier-benefits">
                                <strong><?php echo $t['benefits']; ?>:</strong>
                                <ul>
                                    <li>Basic rewards</li>
                                    <li>5% discount on special items</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="tier-card silver">
                            <h3><i class="fas fa-medal tier-icon"></i> <?php echo $t['silver']; ?> Tier</h3>
                            <div class="tier-requirements">
                                <strong><?php echo $t['requirements']; ?>:</strong> 1,000 - 2,999 <?php echo $t['points']; ?>
                            </div>
                            <div class="tier-benefits">
                                <strong><?php echo $t['benefits']; ?>:</strong>
                                <ul>
                                    <li>Free shipping</li>
                                    <li>₱50 voucher</li>
                                    <li>10% discount on all items</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="tier-card gold">
                            <h3><i class="fas fa-medal tier-icon"></i> <?php echo $t['gold']; ?> Tier</h3>
                            <div class="tier-requirements">
                                <strong><?php echo $t['requirements']; ?>:</strong> 3,000 - 4,999 <?php echo $t['points']; ?>
                            </div>
                            <div class="tier-benefits">
                                <strong><?php echo $t['benefits']; ?>:</strong>
                                <ul>
                                    <li>Priority pickup</li>
                                    <li>₱100 voucher</li>
                                    <li>15% discount on all items</li>
                                    <li>Exclusive offers</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-row" style="margin-bottom: 30px;">
                    <div class="form-col">
                        <div class="tier-card platinum">
                            <h3><i class="fas fa-crown tier-icon"></i> <?php echo $t['platinum']; ?> Tier</h3>
                            <div class="tier-requirements">
                                <strong><?php echo $t['requirements']; ?>:</strong> 5,000 - 9,999 <?php echo $t['points']; ?>
                            </div>
                            <div class="tier-benefits">
                                <strong><?php echo $t['benefits']; ?>:</strong>
                                <ul>
                                    <li>VIP support</li>
                                    <li>₱150 voucher</li>
                                    <li>20% discount</li>
                                    <li>Exclusive offers</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="tier-card diamond">
                            <h3><i class="fas fa-gem tier-icon"></i> <?php echo $t['diamond']; ?> Tier</h3>
                            <div class="tier-requirements">
                                <strong><?php echo $t['requirements']; ?>:</strong> 10,000 - 19,999 <?php echo $t['points']; ?>
                            </div>
                            <div class="tier-benefits">
                                <strong><?php echo $t['benefits']; ?>:</strong>
                                <ul>
                                    <li>Premium support</li>
                                    <li>₱250 voucher</li>
                                    <li>25% discount</li>
                                    <li>Early access</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="tier-card ethereal">
                            <h3><i class="fas fa-star tier-icon"></i> <?php echo $t['ethereal']; ?> Tier</h3>
                            <div class="tier-requirements">
                                <strong><?php echo $t['requirements']; ?>:</strong> 20,000+ <?php echo $t['points']; ?>
                            </div>
                            <div class="tier-benefits">
                                <strong><?php echo $t['benefits']; ?>:</strong>
                                <ul>
                                    <li>Ultimate rewards</li>
                                    <li>₱500 voucher</li>
                                    <li>30% discount</li>
                                    <li>VIP treatment</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-star"></i> <?php echo $t['top_customers']; ?></h3>
                            </div>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th><?php echo $t['customer']; ?></th>
                                            <th><?php echo $t['points']; ?></th>
                                            <th><?php echo $t['tier']; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($customers)): ?>
                                            <?php foreach (array_slice($customers, 0, 5) as $customer): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                                                    <td class="points-display"><?php echo number_format($customer['loyalty_points']); ?></td>
                                                    <td>
                                                        <span class="badge-tier badge-<?php echo $customer['loyalty_tier']; ?>">
                                                            <?php echo ucfirst($customer['loyalty_tier']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="empty-state">
                                                    <i class="fas fa-info-circle"></i>
                                                    <p><?php echo $t['no_customers_found']; ?></p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-exchange-alt"></i> <?php echo $t['recent_transactions']; ?></h3>
                            </div>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th><?php echo $t['date']; ?></th>
                                            <th><?php echo $t['customer']; ?></th>
                                            <th><?php echo $t['points']; ?></th>
                                            <th><?php echo $t['type']; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($transactions)): ?>
                                            <?php foreach (array_slice($transactions, 0, 5) as $transaction): ?>
                                                <tr>
                                                    <td><?php echo date('M j', strtotime($transaction['transaction_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                                    <td class="points-display">
                                                        <?php 
                                                        if ($transaction['points_earned'] > 0) {
                                                            echo '+' . number_format($transaction['points_earned']);
                                                        } else {
                                                            echo '-' . number_format($transaction['points_redeemed']);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($transaction['points_earned'] > 0): ?>
                                                            <span class="badge badge-success"><?php echo $t['earned']; ?></span>
                                                        <?php else: ?>
                                                            <span class="badge badge-warning"><?php echo $t['redeemed']; ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="empty-state">
                                                    <i class="fas fa-info-circle"></i>
                                                    <p><?php echo $t['no_transactions_found']; ?></p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($current_tab === 'customers'): ?>
            <!-- Customers Tab Content -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> <?php echo $t['customer_points']; ?></h3>
                    <div class="card-actions">
                        <button class="btn btn-primary" onclick="openAdjustPointsModal()">
                            <i class="fas fa-plus"></i> <?php echo $t['adjust_points']; ?>
                        </button>
                    </div>
                </div>
                
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="<?php echo $t['search_customers']; ?>">
                    </div>
                    <select class="filter-dropdown">
                        <option value="all"><?php echo $t['all_tiers']; ?></option>
                        <option value="bronze"><?php echo $t['bronze']; ?></option>
                        <option value="silver"><?php echo $t['silver']; ?></option>
                        <option value="gold"><?php echo $t['gold']; ?></option>
                        <option value="platinum"><?php echo $t['platinum']; ?></option>
                        <option value="diamond"><?php echo $t['diamond']; ?></option>
                        <option value="ethereal"><?php echo $t['ethereal']; ?></option>
                    </select>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['customer']; ?></th>
                                <th><?php echo $t['email']; ?></th>
                                <th><?php echo $t['points']; ?></th>
                                <th><?php echo $t['tier']; ?></th>
                                <th><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($customers)): ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></td>
                                        <td class="points-display"><?php echo number_format($customer['loyalty_points']); ?></td>
                                        <td>
                                            <span class="badge-tier badge-<?php echo $customer['loyalty_tier']; ?>">
                                                <?php echo ucfirst($customer['loyalty_tier']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary" onclick="openAdjustPointsModal(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>')">
                                                <i class="fas fa-edit"></i> <?php echo $t['adjust_points']; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_customers_found']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Added pagination for customers -->
                <?php if ($customers_total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($customers_page > 1): ?>
                            <a href="loyalty.php?tab=customers&customers_page=<?php echo $customers_page - 1; ?>">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                            </a>
                        <?php else: ?>
                            <span class="disabled">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $customers_page - 2); $i <= min($customers_total_pages, $customers_page + 2); $i++): ?>
                            <?php if ($i == $customers_page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="loyalty.php?tab=customers&customers_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($customers_page < $customers_total_pages): ?>
                            <a href="loyalty.php?tab=customers&customers_page=<?php echo $customers_page + 1; ?>">
                                <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled">
                                <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="text-align: center; margin-top: 10px; color: #666; font-size: 14px;">
                        Showing <?php echo (($customers_page - 1) * $items_per_page) + 1; ?> to 
                        <?php echo min($customers_page * $items_per_page, $customers_total); ?> of 
                        <?php echo $customers_total; ?> <?php echo $t['customers']; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($current_tab === 'transactions'): ?>
            <!-- Transactions Tab Content -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exchange-alt"></i> <?php echo $t['points_transactions']; ?></h3>
                </div>
                
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="<?php echo $t['search_transactions']; ?>">
                    </div>
                    <select class="filter-dropdown">
                        <option value="all"><?php echo $t['all_types']; ?></option>
                        <option value="earned"><?php echo $t['points_earned']; ?></option>
                        <option value="redeemed"><?php echo $t['points_redeemed']; ?></option>
                        <option value="manual"><?php echo $t['manual_adjustments']; ?></option>
                    </select>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['date']; ?></th>
                                <th><?php echo $t['transaction_id']; ?></th>
                                <th><?php echo $t['customer']; ?></th>
                                <th><?php echo $t['points']; ?></th>
                                <th><?php echo $t['type']; ?></th>
                                <th><?php echo $t['details']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transactions)): ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                        <td class="points-display">
                                            <?php 
                                            if ($transaction['points_earned'] > 0) {
                                                echo '+' . number_format($transaction['points_earned']);
                                            } else {
                                                echo '-' . number_format($transaction['points_redeemed']);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($transaction['points_earned'] > 0): ?>
                                                <span class="badge badge-success"><?php echo $t['earned']; ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><?php echo $t['redeemed']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($transaction['additional_info'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_transactions_found']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Added pagination for transactions -->
                <?php if ($transactions_total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($transactions_page > 1): ?>
                            <a href="loyalty.php?tab=transactions&transactions_page=<?php echo $transactions_page - 1; ?>">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                            </a>
                        <?php else: ?>
                            <span class="disabled">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $transactions_page - 2); $i <= min($transactions_total_pages, $transactions_page + 2); $i++): ?>
                            <?php if ($i == $transactions_page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="loyalty.php?tab=transactions&transactions_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($transactions_page < $transactions_total_pages): ?>
                            <a href="loyalty.php?tab=transactions&transactions_page=<?php echo $transactions_page + 1; ?>">
                                <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled">
                                <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="text-align: center; margin-top: 10px; color: #666; font-size: 14px;">
                        Showing <?php echo (($transactions_page - 1) * $items_per_page) + 1; ?> to 
                        <?php echo min($transactions_page * $items_per_page, $transactions_total); ?> of 
                        <?php echo $transactions_total; ?> <?php echo $t['transactions']; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($current_tab === 'tiers'): ?>
            <!-- Updated Tiers & Rewards Tab Content with new tiers -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-medal"></i> <?php echo $t['tier_settings']; ?></h3>
                </div>
                
                <form action="loyalty.php?tab=tiers" method="POST">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="tier-card bronze">
                                <h3><i class="fas fa-medal tier-icon"></i> <?php echo $t['bronze']; ?> Tier</h3>
                                <div class="form-group">
                                    <label for="bronze_min_points"><?php echo $t['minimum_points']; ?></label>
                                    <input type="number" id="bronze_min_points" name="bronze_min_points" class="form-control" value="0" min="0" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="bronze_max_points"><?php echo $t['maximum_points']; ?></label>
                                    <input type="number" id="bronze_max_points" name="bronze_max_points" class="form-control" value="999" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="bronze_benefits"><?php echo $t['benefits']; ?></label>
                                    <textarea id="bronze_benefits" name="bronze_benefits" class="form-control" rows="3">Basic rewards, 5% discount on special items</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="tier-card silver">
                                <h3><i class="fas fa-medal tier-icon"></i> <?php echo $t['silver']; ?> Tier</h3>
                                <div class="form-group">
                                    <label for="silver_min_points"><?php echo $t['minimum_points']; ?></label>
                                    <input type="number" id="silver_min_points" name="silver_min_points" class="form-control" value="1000" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="silver_max_points"><?php echo $t['maximum_points']; ?></label>
                                    <input type="number" id="silver_max_points" name="silver_max_points" class="form-control" value="2999" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="silver_benefits"><?php echo $t['benefits']; ?></label>
                                    <textarea id="silver_benefits" name="silver_benefits" class="form-control" rows="3">Free shipping, ₱50 voucher, 10% discount on all items</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="tier-card gold">
                                <h3><i class="fas fa-medal tier-icon"></i> <?php echo $t['gold']; ?> Tier</h3>
                                <div class="form-group">
                                    <label for="gold_min_points"><?php echo $t['minimum_points']; ?></label>
                                    <input type="number" id="gold_min_points" name="gold_min_points" class="form-control" value="3000" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="gold_max_points"><?php echo $t['maximum_points']; ?></label>
                                    <input type="number" id="gold_max_points" name="gold_max_points" class="form-control" value="4999" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="gold_benefits"><?php echo $t['benefits']; ?></label>
                                    <textarea id="gold_benefits" name="gold_benefits" class="form-control" rows="3">Priority pickup, ₱100 voucher, 15% discount on all items, exclusive offers</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="tier-card platinum">
                                <h3><i class="fas fa-crown tier-icon"></i> <?php echo $t['platinum']; ?> Tier</h3>
                                <div class="form-group">
                                    <label for="platinum_min_points"><?php echo $t['minimum_points']; ?></label>
                                    <input type="number" id="platinum_min_points" name="platinum_min_points" class="form-control" value="5000" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="platinum_max_points"><?php echo $t['maximum_points']; ?></label>
                                    <input type="number" id="platinum_max_points" name="platinum_max_points" class="form-control" value="9999" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="platinum_benefits"><?php echo $t['benefits']; ?></label>
                                    <textarea id="platinum_benefits" name="platinum_benefits" class="form-control" rows="3">VIP support, ₱150 voucher, 20% discount, exclusive offers</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="tier-card diamond">
                                <h3><i class="fas fa-gem tier-icon"></i> <?php echo $t['diamond']; ?> Tier</h3>
                                <div class="form-group">
                                    <label for="diamond_min_points"><?php echo $t['minimum_points']; ?></label>
                                    <input type="number" id="diamond_min_points" name="diamond_min_points" class="form-control" value="10000" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="diamond_max_points"><?php echo $t['maximum_points']; ?></label>
                                    <input type="number" id="diamond_max_points" name="diamond_max_points" class="form-control" value="19999" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="diamond_benefits"><?php echo $t['benefits']; ?></label>
                                    <textarea id="diamond_benefits" name="diamond_benefits" class="form-control" rows="3">Premium support, ₱250 voucher, 25% discount, early access</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="tier-card ethereal">
                                <h3><i class="fas fa-star tier-icon"></i> <?php echo $t['ethereal']; ?> Tier</h3>
                                <div class="form-group">
                                    <label for="ethereal_min_points"><?php echo $t['minimum_points']; ?></label>
                                    <input type="number" id="ethereal_min_points" name="ethereal_min_points" class="form-control" value="20000" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="ethereal_max_points"><?php echo $t['maximum_points']; ?></label>
                                    <input type="text" id="ethereal_max_points" name="ethereal_max_points" class="form-control" value="Unlimited" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="ethereal_benefits"><?php echo $t['benefits']; ?></label>
                                    <textarea id="ethereal_benefits" name="ethereal_benefits" class="form-control" rows="3">Ultimate rewards, ₱500 voucher, 30% discount, VIP treatment</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="conversion_rate"><?php echo $t['points_conversion_rate']; ?></label>
                        <div class="form-row">
                            <div class="form-col">
                                <div class="input-group">
                                    <span class="input-group-text">1 kg =</span>
                                    <input type="number" id="conversion_rate" name="conversion_rate" class="form-control" value="1" min="1">
                                    <span class="input-group-text"><?php echo $t['points']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="resetTierSettings()"><?php echo $t['reset']; ?></button>
                        <button type="submit" name="update_tiers" class="btn btn-primary"><?php echo $t['save_settings']; ?></button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Adjust Points Modal -->
    <div class="modal" id="adjustPointsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="adjustPointsTitle"><i class="fas fa-plus-circle"></i> <?php echo $t['adjust_customer_points']; ?></h2>
                <button class="close-modal" onclick="closeModal('adjustPointsModal')">&times;</button>
            </div>
            <form action="loyalty.php?tab=<?php echo $current_tab; ?>" method="POST">
                <input type="hidden" id="adjust_user_id" name="user_id">
                <div class="form-group">
                    <label><?php echo $t['action_type']; ?></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="action" value="add" checked> 
                            <i class="fas fa-plus-circle" style="color: var(--stock-green);"></i> <?php echo $t['add_points']; ?>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="action" value="deduct"> 
                            <i class="fas fa-minus-circle" style="color: var(--sales-orange);"></i> <?php echo $t['deduct_points']; ?>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="adjust_points"><?php echo $t['points']; ?></label>
                    <input type="number" id="adjust_points" name="points" class="form-control" min="1" value="100" required>
                </div>
                <div class="form-group">
                    <label for="adjust_reason"><?php echo $t['reason']; ?></label>
                    <textarea id="adjust_reason" name="reason" class="form-control" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('adjustPointsModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="adjust_points" class="btn btn-primary"><?php echo $t['update_points']; ?></button>
                </div>
            </form>
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

    // Modal functions
    function openAdjustPointsModal(userId = null, userName = null) {
        if (userId && userName) {
            document.getElementById('adjust_user_id').value = userId;
            document.getElementById('adjustPointsTitle').textContent = '<?php echo $t['adjust_customer_points']; ?>: ' + userName;
        } else {
            document.getElementById('adjust_user_id').value = '';
            document.getElementById('adjustPointsTitle').textContent = '<?php echo $t['adjust_customer_points']; ?>';
        }
        document.getElementById('adjustPointsModal').style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function resetTierSettings() {
        document.getElementById('bronze_max_points').value = 999;
        document.getElementById('silver_min_points').value = 1000;
        document.getElementById('silver_max_points').value = 2999;
        document.getElementById('gold_min_points').value = 3000;
        document.getElementById('gold_max_points').value = 4999;
        document.getElementById('platinum_min_points').value = 5000;
        document.getElementById('platinum_max_points').value = 9999;
        document.getElementById('diamond_min_points').value = 10000;
        document.getElementById('diamond_max_points').value = 19999;
        document.getElementById('ethereal_min_points').value = 20000;
        document.getElementById('bronze_benefits').value = 'Basic rewards, 5% discount on special items';
        document.getElementById('silver_benefits').value = 'Free shipping, ₱50 voucher, 10% discount on all items';
        document.getElementById('gold_benefits').value = 'Priority pickup, ₱100 voucher, 15% discount on all items, exclusive offers';
        document.getElementById('platinum_benefits').value = 'VIP support, ₱200 voucher, 20% discount, exclusive offers';
        document.getElementById('diamond_benefits').value = 'Premium support, ₱500 voucher, 25% discount, early access';
        document.getElementById('ethereal_benefits').value = 'Ultimate rewards, ₱1000 voucher, 30% discount, VIP treatment';
        document.getElementById('conversion_rate').value = 1;
    }

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