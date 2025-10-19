<?php
session_start();
require_once 'db_connection.php';
require_once 'fpdf/fpdf.php'; // Include FPDF library for export

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
        'transactions' => 'Transactions',
        'transaction_management' => 'Transaction Management',
        'welcome_message' => 'View and manage all customer transactions, including pickups, walk-ins, and loyalty redemptions. Update statuses and track transaction history.',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Users',
        'pricing_control' => 'Pricing Control',
        'reports_analytics' => 'Reports & Analytics',
        'transactions' => 'Transactions',
        'loyalty_program' => 'Loyalty Program',
        'profile' => 'Profile',
    
        'logout' => 'Logout',
        'administrator' => 'Administrator',
        'filter_transactions' => 'Filter Transactions',
        'transaction_history' => 'Transaction History',
        'export_to_pdf' => 'Export to PDF',
        'apply_filters' => 'Apply Filters',
        'reset' => 'Reset',
        'all_statuses' => 'All Statuses',
        'pending' => 'Pending',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'all_users' => 'All Users',
        'all_types' => 'All Types',
        'pickup' => 'Pickup',
        'walk_in' => 'Walk-in',
        'loyalty' => 'Loyalty',
        'from_date' => 'From Date',
        'to_date' => 'To Date',
        'transaction_id' => 'Transaction ID',
        'date_time' => 'Date & Time',
        'pickup_info' => 'Pickup Info',
        'user' => 'User',
        'type' => 'Type',
        'amount' => 'Amount',
        'status' => 'Status',
        'actions' => 'Actions',
        'view' => 'View',
        'complete' => 'Complete',
        'cancel' => 'Cancel',
        'no_transactions_found' => 'No transactions found matching your filters',
        'transaction_details' => 'Transaction Details',
        'close' => 'Close',
        'print_receipt' => 'Print Receipt',
        'confirm_status_change' => 'Confirm Status Change',
        'confirm_cancellation' => 'Confirm Cancellation',
        'confirm_completion' => 'Confirm Completion',
        'set_to_pending' => 'Set to Pending'
    ],
    'tl' => [
        'transactions' => 'Mga Transaksyon',
        'transaction_management' => 'Pamamahala ng Transaksyon',
        'welcome_message' => 'Tingnan at pamahalaan ang lahat ng mga transaksyon ng customer, kabilang ang mga pickup, walk-in, at loyalty redemptions. I-update ang mga status at subaybayan ang kasaysayan ng transaksyon.',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Mga User',
        'pricing_control' => 'Kontrol sa Presyo',
        'reports_analytics' => 'Mga Ulat at Analytics',
        'transactions' => 'Mga Transaksyon',
        'loyalty_program' => 'Programa ng Loyalty',
        'profile' => 'Profile',
      
        'logout' => 'Logout',
        'administrator' => 'Administrator',
        'filter_transactions' => 'I-filter ang mga Transaksyon',
        'transaction_history' => 'Kasaysayan ng Transaksyon',
        'export_to_pdf' => 'I-export sa PDF',
        'apply_filters' => 'Ilapat ang mga Filter',
        'reset' => 'I-reset',
        'all_statuses' => 'Lahat ng Status',
        'pending' => 'Nakabinbin',
        'completed' => 'Nakumpleto',
        'cancelled' => 'Nakansela',
        'all_users' => 'Lahat ng User',
        'all_types' => 'Lahat ng Uri',
        'pickup' => 'Pickup',
        'walk_in' => 'Walk-in',
        'loyalty' => 'Loyalty',
        'from_date' => 'Mula sa Petsa',
        'to_date' => 'Hanggang Petsa',
        'transaction_id' => 'ID ng Transaksyon',
        'date_time' => 'Petsa at Oras',
        'pickup_info' => 'Impormasyon ng Pickup',
        'user' => 'User',
        'type' => 'Uri',
        'amount' => 'Halaga',
        'status' => 'Status',
        'actions' => 'Mga Aksyon',
        'view' => 'Tingnan',
        'complete' => 'Kumpleto',
        'cancel' => 'Kanselahin',
        'no_transactions_found' => 'Walang nakitang mga transaksyon na tumutugma sa iyong mga filter',
        'transaction_details' => 'Mga Detalye ng Transaksyon',
        'close' => 'Isara',
        'print_receipt' => 'I-print ang Resibo',
        'confirm_status_change' => 'Kumpirmahin ang Pagbabago ng Status',
        'confirm_cancellation' => 'Kumpirmahin ang Pagkansela',
        'confirm_completion' => 'Kumpirmahin ang Pagkumpleto',
        'set_to_pending' => 'I-set sa Nakabinbin'
    ]
];

$t = $translations[$language];

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'transactions';

// Initialize variables
$transactions = [];
$users = [];
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'all';
$user_filter = isset($_GET['user']) ? intval($_GET['user']) : 0;
$type_filter = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'all';
$date_from = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get all users for filter dropdown
try {
    $users = $conn->query("SELECT id, first_name, last_name FROM users ORDER BY first_name")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Users query failed: " . $e->getMessage());
}

if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    generateTransactionsPDF($conn, $status_filter, $user_filter, $type_filter, $date_from, $date_to);
    exit();
}

// In the admin transactions.php file, within the status update section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $transaction_id = intval($_POST['transaction_id']);
        $new_status = sanitizeInput($_POST['new_status']);
        
        // Validate status
        $valid_statuses = ['Pending', 'Completed', 'Cancelled'];
        if (!in_array($new_status, $valid_statuses)) {
            throw new Exception("Invalid status value");
        }
        
        // Get transaction details before updating
        $stmt = $conn->prepare("SELECT user_id, transaction_type, status FROM transactions WHERE id = ?");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update transaction status
        $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $transaction_id]);
        
        // If status changed to Completed and it's a pickup transaction, check for referral
        if ($new_status === 'Completed' && $transaction['status'] !== 'Completed' && $transaction['transaction_type'] === 'Pickup') {
            // Check if this user was referred by someone
            $stmt = $conn->prepare("SELECT referred_by FROM users WHERE id = ? AND referred_by IS NOT NULL");
            $stmt->execute([$transaction['user_id']]);
            $referrer_id = $stmt->fetchColumn();
            
            if ($referrer_id) {
                // Award referral points (100 points as per your example)
                $points_to_award = 100;
                
                // Update referrer's points
                $stmt = $conn->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?");
                $stmt->execute([$points_to_award, $referrer_id]);
                
                // Log the referral award
                $stmt = $conn->prepare("INSERT INTO referral_logs (referrer_id, referred_id, points_awarded) VALUES (?, ?, ?)");
                $stmt->execute([$referrer_id, $transaction['user_id'], $points_to_award]);
                
                // You might want to add a notification system here
            }
        }
        
        // Update pickup status if this is a pickup transaction
        if ($transaction['transaction_type'] === 'Pickup') {
            $stmt = $conn->prepare("UPDATE pickups SET status = ? WHERE id IN (SELECT pickup_id FROM pickup_materials WHERE transaction_id = ?)");
            $stmt->execute([$new_status, $transaction_id]);
        }
        
        $_SESSION['success'] = "Transaction status updated successfully!";
    } catch(PDOException $e) {
        error_log("Status update failed: " . $e->getMessage());
        $_SESSION['error'] = "Failed to update transaction status: " . $e->getMessage();
    } catch(Exception $e) {
        error_log("Invalid status update attempt: " . $e->getMessage());
        $_SESSION['error'] = "Invalid status update attempt.";
    }
    header("Location: transactions.php");
    exit();
}

$transaction_query = "SELECT 
    t.*, 
    CONCAT(u.first_name, ' ', u.last_name) as user_name,
    u.user_type,
    p.pickup_date,
    p.time_slot
FROM transactions t
JOIN users u ON t.user_id = u.id
LEFT JOIN pickups p ON t.transaction_id = p.id
WHERE 1=1";

$count_query = "SELECT COUNT(*) as total FROM transactions t
JOIN users u ON t.user_id = u.id
LEFT JOIN pickups p ON t.transaction_id = p.id
WHERE 1=1";

$params = [];
$types = '';

if ($status_filter !== 'all') {
    $transaction_query .= " AND t.status = ?";
    $count_query .= " AND t.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($user_filter > 0) {
    $transaction_query .= " AND t.user_id = ?";
    $count_query .= " AND t.user_id = ?";
    $params[] = $user_filter;
    $types .= 'i';
}

if ($type_filter !== 'all') {
    $transaction_query .= " AND t.transaction_type = ?";
    $count_query .= " AND t.transaction_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

if (!empty($date_from)) {
    $transaction_query .= " AND t.transaction_date >= ?";
    $count_query .= " AND t.transaction_date >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $transaction_query .= " AND t.transaction_date <= ?";
    $count_query .= " AND t.transaction_date <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$transaction_query .= " ORDER BY t.transaction_date DESC, t.transaction_time DESC LIMIT ? OFFSET ?";

try {
    // Get total count
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get paginated transactions
    $stmt = $conn->prepare($transaction_query);
    $stmt->execute(array_merge($params, [$records_per_page, $offset]));
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Transactions query failed: " . $e->getMessage());
    $total_records = 0;
    $total_pages = 0;
}

// Display success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateTransactionsPDF($conn, $status_filter, $user_filter, $type_filter, $date_from, $date_to) {
    date_default_timezone_set('Asia/Manila');
    
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Header
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 15, 'JunkValue', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 7, '10 Sto. Nino St. Barangay Commonwealth Quezon city', 0, 1, 'C');
    $pdf->Cell(0, 12, 'Contact: 0947 884 4412', 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 15, 'TRANSACTIONS REPORT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    // Get statistics
    try {
        // Total transactions
        $stmt = $conn->query("SELECT COUNT(*) as total FROM transactions");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Completed transactions
        $stmt = $conn->query("SELECT COUNT(*) as completed FROM transactions WHERE status = 'Completed'");
        $completed = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];
        
        // Pending transactions
        $stmt = $conn->query("SELECT COUNT(*) as pending FROM transactions WHERE status = 'Pending'");
        $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
        
        // Total revenue
        $stmt = $conn->query("SELECT SUM(amount) as revenue FROM transactions WHERE status = 'Completed'");
        $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
    } catch(PDOException $e) {
        $total = $completed = $pending = $revenue = 0;
    }
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Transactions:', 0, 0);
    $pdf->Cell(40, 7, number_format($total), 0, 1);
    $pdf->Cell(50, 7, 'Completed:', 0, 0);
    $pdf->Cell(40, 7, number_format($completed), 0, 1);
    $pdf->Cell(50, 7, 'Pending:', 0, 0);
    $pdf->Cell(40, 7, number_format($pending), 0, 1);
    $pdf->Cell(50, 7, 'Total Revenue:', 0, 0);
    $pdf->Cell(40, 7, '' . number_format($revenue, 2), 0, 1);
    
    // Get filtered transactions for the report
    $query = "SELECT 
        t.transaction_id, t.transaction_date, t.transaction_time, t.transaction_type,
        t.amount, t.status, CONCAT(u.first_name, ' ', u.last_name) as user_name,
        p.pickup_date, p.time_slot
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN pickups p ON t.transaction_id = p.id
    WHERE 1=1";
    
    $params = [];
    
    if ($status_filter !== 'all') {
        $query .= " AND t.status = ?";
        $params[] = $status_filter;
    }
    
    if ($user_filter > 0) {
        $query .= " AND t.user_id = ?";
        $params[] = $user_filter;
    }
    
    if ($type_filter !== 'all') {
        $query .= " AND t.transaction_type = ?";
        $params[] = $type_filter;
    }
    
    if (!empty($date_from)) {
        $query .= " AND t.transaction_date >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $query .= " AND t.transaction_date <= ?";
        $params[] = $date_to;
    }
    
    $query .= " ORDER BY t.transaction_date DESC, t.transaction_time DESC";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $transactions = [];
    }
    
    // Transactions table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Trans ID', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Date', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Time', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Customer', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Type', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Amount', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Status', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    foreach ($transactions as $transaction) {
        // Check if we need a new page
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            // Repeat header
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(40, 7, 'Trans ID', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Date', 1, 0, 'C');
            $pdf->Cell(20, 7, 'Time', 1, 0, 'C');
            $pdf->Cell(30, 7, 'Customer', 1, 0, 'C');
            $pdf->Cell(20, 7, 'Type', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Amount', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Status', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 9);
        }
        
        $pdf->Cell(40, 6, $transaction['transaction_id'], 1, 0);
        $pdf->Cell(25, 6, date('m/d/Y', strtotime($transaction['transaction_date'])), 1, 0);
        $pdf->Cell(20, 6, date('h:i A', strtotime($transaction['transaction_time'])), 1, 0);
        $pdf->Cell(30, 6, substr($transaction['user_name'], 0, 18), 1, 0);
        $pdf->Cell(20, 6, $transaction['transaction_type'], 1, 0);
        $pdf->Cell(25, 6, '' . number_format($transaction['amount'], 2), 1, 0, 'R');
        $pdf->Cell(25, 6, $transaction['status'], 1, 1);
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Transaction Management System', 0, 1, 'C');
    
    // Output the PDF
    $filename = 'transactions_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['transactions']; ?></title>
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

    /* Stats Cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-card {
        background-color: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        text-align: center;
        transition: all 0.3s ease;
    }

    body.dark-mode .stat-card {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 3px 10px var(--dark-shadow);
    }

    .stat-card h3 {
        font-size: 14px;
        color: var(--text-dark);
        margin-bottom: 10px;
        transition: color 0.3s ease;
    }

    body.dark-mode .stat-card h3 {
        color: var(--dark-text-primary);
    }

    .stat-card .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--icon-green);
    }

    .stat-card .stat-change {
        font-size: 12px;
        margin-top: 5px;
    }

    .stat-card .stat-change.positive {
        color: var(--stock-green);
    }

    .stat-card .stat-change.negative {
        color: var(--sales-orange);
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
        transition: color 0.3s ease;
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
        background-color: var(--dark-bg-tertiary);
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
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .filter-dropdown:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
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
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .btn-secondary:hover {
        background-color: #f5f5f5;
        transform: translateY(-2px);
    }

    body.dark-mode .btn-secondary:hover {
        background-color: var(--dark-border);
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #bb2d3b;
        transform: translateY(-2px);
    }

    .btn-sm {
        padding: 8px 12px;
        font-size: 13px;
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
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode table {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 2px 10px var(--dark-shadow);
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
        border-bottom-color: var(--dark-border);
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

    .badge-info {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
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
        color: var(--dark-text-primary);
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

    /* Modal */
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
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.1);
        background-color: white;
        color: var(--text-dark);
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
        border-color: var(--icon-green);
    }

    .pagination .disabled {
        background-color: #f5f5f5;
        color: #999;
        cursor: not-allowed;
    }

    body.dark-mode .pagination .disabled {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-secondary);
    }

    .pagination-info {
        text-align: center;
        margin-bottom: 10px;
        color: var(--text-dark);
        font-size: 14px;
        transition: color 0.3s ease;
    }

    body.dark-mode .pagination-info {
        color: var(--dark-text-primary);
    }

    /* Transaction Details */
    .transaction-details {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
        border: 1px solid #eee;
        transition: all 0.3s ease;
    }

    body.dark-mode .transaction-details {
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
    }

    .transaction-details h4 {
        margin-bottom: 10px;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .transaction-details h4 {
        color: var(--dark-text-primary);
    }

    .transaction-details ul {
        list-style: none;
        padding-left: 0;
    }

    .transaction-details li {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
        transition: border-color 0.3s ease;
    }

    body.dark-mode .transaction-details li {
        border-bottom-color: var(--dark-border);
    }

    .transaction-details li:last-child {
        border-bottom: none;
    }

    /* Date Picker */
    .date-picker {
        position: relative;
    }

    .date-picker input {
        padding-right: 30px;
    }

    .date-picker i {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-dark);
        opacity: 0.5;
        transition: color 0.3s ease;
    }

    body.dark-mode .date-picker i {
        color: var(--dark-text-secondary);
    }

    /* Status Colors */
    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .status-completed {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .status-cancelled {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    /* Toggle Details Button */
    .toggle-details {
        background: none;
        border: none;
        color: var(--icon-green);
        cursor: pointer;
        font-size: 13px;
        padding: 0;
        text-decoration: underline;
    }

    /* Status Action Buttons */
    .status-actions {
        display: flex;
        gap: 5px;
    }

    .status-btn {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .status-btn-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .status-btn-pending:hover {
        background-color: rgba(255, 193, 7, 0.2);
    }

    .status-btn-completed {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .status-btn-completed:hover {
        background-color: rgba(40, 167, 69, 0.2);
    }

    .status-btn-cancelled {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .status-btn-cancelled:hover {
        background-color: rgba(220, 53, 69, 0.2);
    }

    /* Confirmation Modal */
    .confirmation-modal {
        display: none;
        position: fixed;
        z-index: 1060;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .confirmation-content {
        background-color: white;
        margin: 15% auto;
        padding: 25px;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        transition: all 0.3s ease;
    }

    body.dark-mode .confirmation-content {
        background-color: var(--dark-bg-secondary);
    }

    .confirmation-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .confirmation-header {
        border-bottom-color: var(--dark-border);
    }

    .confirmation-header h2 {
        font-size: 20px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }

    body.dark-mode .confirmation-header h2 {
        color: var(--dark-text-primary);
    }

    .confirmation-body {
        margin-bottom: 25px;
    }

    .confirmation-footer {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }

    /* Warning Icon */
    .warning-icon {
        color: #ffc107;
        font-size: 24px;
        margin-right: 10px;
    }

    /* Success Icon */
    .success-icon {
        color: #28a745;
        font-size: 24px;
        margin-right: 10px;
    }

    /* Add these new animation styles */
    @keyframes checkmark {
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }

    @keyframes clock {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes cross {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.3); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }

    .confirmation-animation {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        border-radius: 50%;
        font-size: 40px;
    }

    .check-animation {
        color: #28a745;
        background-color: rgba(40, 167, 69, 0.1);
        animation: checkmark 0.6s ease-out;
    }

    .pending-animation {
        color: #ffc107;
        background-color: rgba(255, 193, 7, 0.1);
        animation: clock 1s linear infinite;
    }

    .cancel-animation {
        color: #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
        animation: cross 0.6s ease-out;
    }

    /* Add this to make the form submission smoother */
    #statusChangeForm {
        display: inline-block;
    }

    /* Add this for the loading state */
    .btn-loading {
        position: relative;
        pointer-events: none;
    }

    .btn-loading::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        border: 3px solid transparent;
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: button-loading-spinner 1s ease infinite;
    }

    @keyframes button-loading-spinner {
        from { transform: rotate(0turn); }
        to { transform: rotate(1turn); }
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

        .confirmation-content {
            padding: 20px;
        }

        .modal-footer {
            flex-direction: column;
        }

        .confirmation-footer {
            flex-direction: column;
        }

        .btn {
            width: 100%;
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
            <li><a href="transactions.php" class="active"><i class="fas fa-exchange-alt"></i> <?php echo $t['transactions']; ?></a></li>
             <li><a href="loyalty.php" ><i class="fas fa-award"></i> <?php echo $t['loyalty_program']; ?></a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i></i> <?php echo $t['profile']; ?></a></li>

        </ul>
        
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
            <h1 class="page-title"><?php echo $t['transactions']; ?></h1>
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
                <h2><?php echo $t['transaction_management']; ?></h2>
                <p><?php echo $t['welcome_message']; ?></p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
        
        <!-- Transaction Stats -->
        <div class="stats-container">
            <?php
            // Get transaction stats
            try {
                // Total transactions
                $stmt = $conn->query("SELECT COUNT(*) as total FROM transactions");
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Completed transactions
                $stmt = $conn->query("SELECT COUNT(*) as completed FROM transactions WHERE status = 'Completed'");
                $completed = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];
                
                // Pending transactions
                $stmt = $conn->query("SELECT COUNT(*) as pending FROM transactions WHERE status = 'Pending'");
                $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
                
                // Total revenue
                $stmt = $conn->query("SELECT SUM(amount) as revenue FROM transactions WHERE status = 'Completed'");
                $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
            } catch(PDOException $e) {
                error_log("Stats query failed: " . $e->getMessage());
                $total = $completed = $pending = $revenue = 0;
            }
            ?>
            
            <div class="stat-card">
                <h3>Total Transactions</h3>
                <div class="stat-value"><?php echo number_format($total); ?></div>
                <div class="stat-change positive">+5% from last month</div>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $t['completed']; ?></h3>
                <div class="stat-value"><?php echo number_format($completed); ?></div>
                <div class="stat-change positive">+12% from last month</div>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $t['pending']; ?></h3>
                <div class="stat-value"><?php echo number_format($pending); ?></div>
                <div class="stat-change negative">-3% from last month</div>
            </div>
            
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="stat-value">₱<?php echo number_format($revenue, 2); ?></div>
                <div class="stat-change positive">+8% from last month</div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> <?php echo $t['filter_transactions']; ?></h3>
            </div>
            
            <form method="GET" action="transactions.php">
                <div class="search-filter">
                    <div>
                        <label><?php echo $t['status']; ?></label>
                        <select name="status" class="filter-dropdown">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>><?php echo $t['all_statuses']; ?></option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>><?php echo $t['pending']; ?></option>
                            <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>><?php echo $t['completed']; ?></option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>><?php echo $t['cancelled']; ?></option>
                        </select>
                    </div>
                    
                    <div>
                        <label><?php echo $t['user']; ?></label>
                        <select name="user" class="filter-dropdown">
                            <option value="0"><?php echo $t['all_users']; ?></option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label><?php echo $t['type']; ?></label>
                        <select name="type" class="filter-dropdown">
                            <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>><?php echo $t['all_types']; ?></option>
                            <option value="Pickup" <?php echo $type_filter === 'Pickup' ? 'selected' : ''; ?>><?php echo $t['pickup']; ?></option>
                            <option value="Walk-in" <?php echo $type_filter === 'Walk-in' ? 'selected' : ''; ?>><?php echo $t['walk_in']; ?></option>
                            <option value="Loyalty" <?php echo $type_filter === 'Loyalty' ? 'selected' : ''; ?>><?php echo $t['loyalty']; ?></option>
                        </select>
                    </div>
                    
                    <div class="date-picker">
                        <label><?php echo $t['from_date']; ?></label>
                        <input type="date" name="date_from" class="filter-dropdown" value="<?php echo $date_from; ?>">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    
                    <div class="date-picker">
                        <label><?php echo $t['to_date']; ?></label>
                        <input type="date" name="date_to" class="filter-dropdown" value="<?php echo $date_to; ?>">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    
                    <div style="align-self: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?php echo $t['apply_filters']; ?>
                        </button>
                        <a href="transactions.php" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> <?php echo $t['reset']; ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Transactions Table -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-list"></i> <?php echo $t['transaction_history']; ?></h3>
                <div class="card-actions">
                    <!-- Updated export button to use PDF -->
                    <a href="?export=pdf<?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> <?php echo $t['export_to_pdf']; ?>
                    </a>
                </div>
            </div>
            
            <!-- Added pagination info -->
            <?php if ($total_records > 0): ?>
                <div class="pagination-info">
                    Showing <?php echo (($page - 1) * $records_per_page) + 1; ?> to <?php echo min($page * $records_per_page, $total_records); ?> of <?php echo $total_records; ?> transactions
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th><?php echo $t['transaction_id']; ?></th>
                            <th><?php echo $t['date_time']; ?></th>
                            <th><?php echo $t['pickup_info']; ?></th>
                            <th><?php echo $t['user']; ?></th>
                            <th><?php echo $t['type']; ?></th>
                            <th><?php echo $t['amount']; ?></th>
                            <th><?php echo $t['status']; ?></th>
                            <th><?php echo $t['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $transaction): 
                                // Determine status badge class
                                $status_class = '';
                                if ($transaction['status'] === 'Completed') {
                                    $status_class = 'status-completed';
                                } elseif ($transaction['status'] === 'Cancelled') {
                                    $status_class = 'status-cancelled';
                                } else {
                                    $status_class = 'status-pending';
                                }
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?><br>
                                        <small><?php echo date('g:i A', strtotime($transaction['transaction_time'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($transaction['transaction_type'] === 'Pickup' && !empty($transaction['pickup_date'])): ?>
                                            <?php echo date('M j, Y', strtotime($transaction['pickup_date'])); ?><br>
                                            <small><?php echo htmlspecialchars($transaction['time_slot']); ?></small>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($transaction['user_name']); ?><br>
                                        <small class="badge badge-info"><?php echo htmlspecialchars($transaction['user_type']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                                    <td>₱<?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($transaction['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="status-actions">
                                            <button class="btn btn-secondary btn-sm" onclick="viewTransactionDetails(<?php echo $transaction['id']; ?>)">
                                                <i class="fas fa-eye"></i> <?php echo $t['view']; ?>
                                            </button>
                                            
                                            <?php if ($transaction['status'] !== 'Pending'): ?>
                                                <button class="status-btn status-btn-pending" 
                                                    onclick="confirmStatusChange(<?php echo $transaction['id']; ?>, 'Pending')">
                                                    <i class="fas fa-clock"></i> <?php echo $t['pending']; ?>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($transaction['status'] !== 'Completed'): ?>
                                                <button class="status-btn status-btn-completed" 
                                                    onclick="confirmStatusChange(<?php echo $transaction['id']; ?>, 'Completed')">
                                                    <i class="fas fa-check"></i> <?php echo $t['complete']; ?>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($transaction['status'] !== 'Cancelled'): ?>
                                                <button class="status-btn status-btn-cancelled" 
                                                    onclick="confirmStatusChange(<?php echo $transaction['id']; ?>, 'Cancelled')">
                                                    <i class="fas fa-times"></i> <?php echo $t['cancel']; ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="details-<?php echo $transaction['id']; ?>" style="display: none;">
                                    <td colspan="8">
                                        <div class="transaction-details">
                                            <h4><?php echo $t['transaction_details']; ?></h4>
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($transaction['name']); ?></p>
                                            <p><strong>Items:</strong></p>
                                            <div><?php echo nl2br(htmlspecialchars($transaction['item_details'])); ?></div>
                                            <?php if (!empty($transaction['additional_info'])): ?>
                                                <p><strong>Additional Info:</strong> <?php echo htmlspecialchars($transaction['additional_info']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($transaction['transaction_type'] === 'Pickup' && !empty($transaction['pickup_date'])): ?>
                                                <p><strong>Pickup Date:</strong> <?php echo date('M j, Y', strtotime($transaction['pickup_date'])); ?></p>
                                                <p><strong>Time Slot:</strong> <?php echo htmlspecialchars($transaction['time_slot']); ?></p>
                                            <?php endif; ?>
                                            <p><strong>Points Earned:</strong> <?php echo $transaction['points_earned']; ?></p>
                                            <p><strong>Points Redeemed:</strong> <?php echo $transaction['points_redeemed']; ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <i class="fas fa-info-circle"></i>
                                    <p><?php echo $t['no_transactions_found']; ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Added pagination controls -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    // Build query string for pagination links
                    $query_params = $_GET;
                    unset($query_params['page']);
                    $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $query_string; ?>">First</a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>">Previous</a>
                    <?php else: ?>
                        <span class="disabled">First</span>
                        <span class="disabled">Previous</span>
                    <?php endif; ?>
                    
                    <?php
                    // Show page numbers
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo $query_string; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>">Next</a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo $query_string; ?>">Last</a>
                    <?php else: ?>
                        <span class="disabled">Next</span>
                        <span class="disabled">Last</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div class="modal" id="transactionDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-receipt"></i> <?php echo $t['transaction_details']; ?></h2>
                <button class="close-modal" onclick="closeModal('transactionDetailsModal')">&times;</button>
            </div>
            <div id="transactionDetailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('transactionDetailsModal')"><?php echo $t['close']; ?></button>
                <button type="button" class="btn btn-primary" onclick="printTransaction()">
                    <i class="fas fa-print"></i> <?php echo $t['print_receipt']; ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Status Confirmation Modal -->
    <div class="confirmation-modal" id="statusConfirmationModal">
        <div class="confirmation-content">
            <div class="confirmation-header">
                <h2 id="confirmationTitle"><i class="fas fa-question-circle"></i> <?php echo $t['confirm_status_change']; ?></h2>
            </div>
            <div class="confirmation-body" id="confirmationMessage">
                <div id="confirmationAnimation" class="confirmation-animation"></div>
                <p id="confirmationText"></p>
            </div>
            <div class="confirmation-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('statusConfirmationModal')"><?php echo $t['close']; ?></button>
                <form method="POST" action="transactions.php" id="statusChangeForm">
                    <input type="hidden" name="transaction_id" id="transactionIdInput">
                    <input type="hidden" name="new_status" id="newStatusInput">
                    <button type="submit" name="update_status" class="btn btn-primary" id="confirmButton">
                        <?php echo $t['confirm_completion']; ?>
                    </button>
                </form>
            </div>
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

        // View transaction details
        function viewTransactionDetails(id) {
            const detailsRow = document.getElementById('details-' + id);
            if (detailsRow) {
                detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
            }
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Print transaction receipt
        function printTransaction() {
            const printContent = document.getElementById('transactionDetailsContent').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            
            window.location.reload();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal' || event.target.className === 'confirmation-modal') {
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

        // Confirm status change
        function confirmStatusChange(transactionId, newStatus) {
            const modal = document.getElementById('statusConfirmationModal');
            const title = document.getElementById('confirmationTitle');
            const animation = document.getElementById('confirmationAnimation');
            const message = document.getElementById('confirmationText');
            const transactionIdInput = document.getElementById('transactionIdInput');
            const newStatusInput = document.getElementById('newStatusInput');
            const confirmButton = document.getElementById('confirmButton');
            
            // Reset animation
            animation.className = 'confirmation-animation';
            animation.innerHTML = '';
            
            // Set the form values
            transactionIdInput.value = transactionId;
            newStatusInput.value = newStatus;
            
            // Customize the modal based on the action
            if (newStatus === 'Cancelled') {
                // Warning for cancellation
                title.innerHTML = '<i class="fas fa-exclamation-triangle warning-icon"></i> <?php echo $t['confirm_cancellation']; ?>';
                animation.className = 'confirmation-animation cancel-animation';
                animation.innerHTML = '<i class="fas fa-times"></i>';
                message.innerHTML = `
                    <p>Are you sure you want to cancel this transaction?</p>
                    <p><strong>This action cannot be undone.</strong> The customer will be notified if this transaction is cancelled.</p>
                `;
                confirmButton.className = 'btn btn-danger';
                confirmButton.innerHTML = '<i class="fas fa-times"></i> <?php echo $t['confirm_cancellation']; ?>';
            } else if (newStatus === 'Completed') {
                // Confirmation for completion
                title.innerHTML = '<i class="fas fa-check-circle success-icon"></i> <?php echo $t['confirm_completion']; ?>';
                animation.className = 'confirmation-animation check-animation';
                animation.innerHTML = '<i class="fas fa-check"></i>';
                message.innerHTML = `
                    <p>Are you sure you want to mark this transaction as completed?</p>
                    <p>This will finalize the transaction and update all related records.</p>
                `;
                confirmButton.className = 'btn btn-primary';
                confirmButton.innerHTML = '<i class="fas fa-check"></i> <?php echo $t['confirm_completion']; ?>';
            } else {
                // Default for pending
                title.innerHTML = '<i class="fas fa-clock"></i> <?php echo $t['confirm_status_change']; ?>';
                animation.className = 'confirmation-animation pending-animation';
                animation.innerHTML = '<i class="fas fa-clock"></i>';
                message.innerHTML = `
                    <p>Are you sure you want to set this transaction back to pending status?</p>
                    <p>This will reset the transaction workflow.</p>
                `;
                confirmButton.className = 'btn btn-primary';
                confirmButton.innerHTML = '<i class="fas fa-clock"></i> <?php echo $t['set_to_pending']; ?>';
            }
            
            // Show the modal
            modal.style.display = 'block';
        }

        // Add loading state to form submission
        document.getElementById('statusChangeForm').addEventListener('submit', function(e) {
            const confirmButton = document.getElementById('confirmButton');
            confirmButton.classList.add('btn-loading');
            confirmButton.innerHTML = '';
        });
    </script>
</body>
</html>