<?php
// Start session and include database connection
session_start();
require_once 'db_connection.php';
require_once 'fpdf/fpdf.php'; // Include FPDF library for export

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
        'transaction_history' => 'Transaction History',
        'schedule_pickup' => 'Schedule Pickup',
        'current_prices' => 'Current Prices',
        'loyalty_rewards' => 'Loyalty Rewards',
        'account_settings' => 'Account Settings',
        'logout' => 'Logout',
        'your_transactions' => 'Your Transactions',
        'export_pdf' => 'Export PDF',
        'transaction_type' => 'Transaction Type',
        'all_types' => 'All Types',
        'pickups' => 'Pickups',
        'walk_in_sales' => 'Walk-in Sales',
        'loyalty_rewards' => 'Loyalty Rewards',
        'status' => 'Status',
        'all_statuses' => 'All Statuses',
        'completed' => 'Completed',
        'pending' => 'Pending',
        'cancelled' => 'Cancelled',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
        'search_transactions' => 'Search transactions...',
        'apply_filters' => 'Apply Filters',
        'clear_filters' => 'Clear Filters',
        'id' => 'ID',
        'date_time' => 'Date & Time',
        'type' => 'Type',
        'details' => 'Details',
        'amount' => 'Amount',
        'actions' => 'Actions',
        'pickup' => 'Pickup',
        'shipping' => 'Shipping',
        'cancellation_reason' => 'Cancellation Reason',
        'cancel' => 'Cancel',
        'no_transactions_found' => 'No transactions found',
        'try_adjusting_filters' => 'Try adjusting your filters or',
        'clear_all_filters' => 'clear all filters',
        'cancel_transaction' => 'Cancel Transaction',
        'reason_cancellation' => 'Reason for Cancellation',
        'confirm_cancellation' => 'Confirm Cancellation',
        'first_page' => 'First Page',
        'previous_page' => 'Previous Page',
        'next_page' => 'Next Page',
        'last_page' => 'Last Page'
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'transaction_history' => 'Kasaysayan ng Transaksyon',
        'schedule_pickup' => 'I-skedyul ang Pickup',
        'current_prices' => 'Kasalukuyang Mga Presyo',
        'loyalty_rewards' => 'Mga Gantimpala ng Loyalty',
        'account_settings' => 'Mga Setting ng Account',
        'logout' => 'Logout',
        'your_transactions' => 'Iyong Mga Transaksyon',
        'export_pdf' => 'I-export ang PDF',
        'transaction_type' => 'Uri ng Transaksyon',
        'all_types' => 'Lahat ng Uri',
        'pickups' => 'Mga Pickup',
        'walk_in_sales' => 'Walk-in na Benta',
        'loyalty_rewards' => 'Mga Gantimpala ng Loyalty',
        'status' => 'Katayuan',
        'all_statuses' => 'Lahat ng Katayuan',
        'completed' => 'Natapos',
        'pending' => 'Nakabinbin',
        'cancelled' => 'Nakansela',
        'date_from' => 'Petsa Mula',
        'date_to' => 'Petsa Hanggang',
        'search_transactions' => 'Maghanap ng mga transaksyon...',
        'apply_filters' => 'Ilapat ang Mga Filter',
        'clear_filters' => 'I-clear ang Mga Filter',
        'id' => 'ID',
        'date_time' => 'Petsa at Oras',
        'type' => 'Uri',
        'details' => 'Mga Detalye',
        'amount' => 'Halaga',
        'actions' => 'Mga Aksyon',
        'pickup' => 'Pickup',
        'shipping' => 'Paghahatid',
        'cancellation_reason' => 'Dahilan ng Pagkansela',
        'cancel' => 'Kanselahin',
        'no_transactions_found' => 'Walang nakitang mga transaksyon',
        'try_adjusting_filters' => 'Subukang iayos ang iyong mga filter o',
        'clear_all_filters' => 'i-clear ang lahat ng filter',
        'cancel_transaction' => 'Kanselahin ang Transaksyon',
        'reason_cancellation' => 'Dahilan para sa Pagkansela',
        'confirm_cancellation' => 'Kumpirmahin ang Pagkansela',
        'first_page' => 'Unang Pahina',
        'previous_page' => 'Nakaraang Pahina',
        'next_page' => 'Susunod na Pahina',
        'last_page' => 'Huling Pahina'
    ]
];

$t = $translations[$language];

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user ID
$user_id = $_SESSION['user_id'];

// Handle PDF export
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    generateTransactionPDF($conn, $user_id);
    exit();
}

// Handle cancellation request
if (isset($_POST['cancel_transaction'])) {
    $transaction_id = mysqli_real_escape_string($conn, $_POST['transaction_id']);
    $reason = mysqli_real_escape_string($conn, $_POST['cancel_reason']);
    
    // Update transaction status to Cancelled with reason and timestamp
    $update_sql = "UPDATE transactions SET status = 'Cancelled', cancel_reason = ?, cancelled_at = NOW() WHERE transaction_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "ssi", $reason, $transaction_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_msg'] = "Transaction #$transaction_id has been cancelled successfully.";
    } else {
        $_SESSION['error_msg'] = "Failed to cancel transaction. Please try again.";
    }
    
    mysqli_stmt_close($stmt);
    header("Location: transaction.php");
    exit();
}

// Check if clear filters was clicked
if (isset($_GET['clear_filters'])) {
    header("Location: transaction.php");
    exit();
}

// Initialize filter variables with proper sanitization
$type_filter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Pagination variables
$transactions_per_page = 5; // Changed from 10 to 5
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $transactions_per_page;

// Build base SQL query with prepared statement approach
$sql = "SELECT t.*, p.shipping_method as shipping_method_id, p.shipping_fee, sm.name as shipping_method_name
        FROM transactions t
        LEFT JOIN pickups p ON t.pickup_date = p.pickup_date AND t.time_slot = p.time_slot AND t.user_id = p.user_id
        LEFT JOIN shipping_methods sm ON p.shipping_method = sm.id
        WHERE t.user_id = ?";

// Initialize parameters array
$params = array($user_id);
$types = "i"; // i for integer

// Add filters to query
if (!empty($type_filter)) {
    $sql .= " AND t.transaction_type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $sql .= " AND t.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_from) && !empty($date_to)) {
    $sql .= " AND t.transaction_date BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= "ss";
}

if (!empty($search_query)) {
    $sql .= " AND (t.transaction_id LIKE CONCAT('%', ?, '%') 
              OR t.item_details LIKE CONCAT('%', ?, '%'))";
    $params[] = $search_query;
    $params[] = $search_query;
    $types .= "ss";
}

// Order by most recent first
$sql .= " ORDER BY t.transaction_date DESC, t.transaction_time DESC";

// Get total count for pagination
$count_sql = $sql;
$count_stmt = mysqli_prepare($conn, $count_sql);
if ($count_stmt === false) {
    die("Error preparing count statement: " . mysqli_error($conn));
}

// Bind parameters for count query
mysqli_stmt_bind_param($count_stmt, $types, ...$params);

// Execute count query
if (!mysqli_stmt_execute($count_stmt)) {
    die("Error executing count statement: " . mysqli_stmt_error($count_stmt));
}

$count_result = mysqli_stmt_get_result($count_stmt);
$total_transactions = mysqli_num_rows($count_result);
$total_pages = ceil($total_transactions / $transactions_per_page);

// Add LIMIT to main query for pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $transactions_per_page;
$params[] = $offset;
$types .= "ii";

// Prepare and execute main query with proper parameter binding
$stmt = mysqli_prepare($conn, $sql);
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}

// Bind parameters for main query
mysqli_stmt_bind_param($stmt, $types, ...$params);

// Execute query
if (!mysqli_stmt_execute($stmt)) {
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}

// Get result
$result = mysqli_stmt_get_result($stmt);

// Get user info for header
$user_query = "SELECT first_name, last_name, profile_image FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);
$user_name = $user['first_name'] . ' ' . $user['last_name'];
$user_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

// Function to generate PDF export
function generateTransactionPDF($conn, $user_id) {
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
    $pdf->Cell(0, 15, 'TRANSACTION HISTORY', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    // Get user info for PDF
    $user_query = "SELECT first_name, last_name FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user = mysqli_fetch_assoc($user_result);
    
    $pdf->Cell(40, 7, 'Customer:', 0, 0);
    $pdf->Cell(0, 7, $user['first_name'] . ' ' . $user['last_name'], 0, 1);
    
    // Get transaction data for PDF
    $transaction_sql = "SELECT t.*, p.shipping_method as shipping_method_id, p.shipping_fee, sm.name as shipping_method_name
                        FROM transactions t
                        LEFT JOIN pickups p ON t.pickup_date = p.pickup_date AND t.time_slot = p.time_slot AND t.user_id = p.user_id
                        LEFT JOIN shipping_methods sm ON p.shipping_method = sm.id
                        WHERE t.user_id = ?
                        ORDER BY t.transaction_date DESC, t.transaction_time DESC";
    $transaction_stmt = mysqli_prepare($conn, $transaction_sql);
    mysqli_stmt_bind_param($transaction_stmt, "i", $user_id);
    mysqli_stmt_execute($transaction_stmt);
    $transaction_result = mysqli_stmt_get_result($transaction_stmt);
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    
    // Calculate statistics
    $total_amount = 0;
    $completed_count = 0;
    $pending_count = 0;
    $cancelled_count = 0;
    
    while ($transaction = mysqli_fetch_assoc($transaction_result)) {
        $total_amount += $transaction['amount'];
        if ($transaction['status'] === 'Completed') $completed_count++;
        if ($transaction['status'] === 'Pending') $pending_count++;
        if ($transaction['status'] === 'Cancelled') $cancelled_count++;
    }
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Transactions:', 0, 0);
    $pdf->Cell(40, 7, number_format(mysqli_num_rows($transaction_result)), 0, 1);
    $pdf->Cell(50, 7, 'Total Amount:', 0, 0);
    $pdf->Cell(40, 7, 'P' . number_format($total_amount, 2), 0, 1);
    $pdf->Cell(50, 7, 'Completed:', 0, 0);
    $pdf->Cell(40, 7, number_format($completed_count), 0, 1);
    $pdf->Cell(50, 7, 'Pending:', 0, 0);
    $pdf->Cell(40, 7, number_format($pending_count), 0, 1);
    $pdf->Cell(50, 7, 'Cancelled:', 0, 0);
    $pdf->Cell(40, 7, number_format($cancelled_count), 0, 1);
    
    // Reset result pointer
    mysqli_data_seek($transaction_result, 0);
    
    // Transaction table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(35, 7, 'Trans ID', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Date', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Type', 1, 0, 'C');
    $pdf->Cell(50, 7, 'Details', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Status', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Amount', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    while ($transaction = mysqli_fetch_assoc($transaction_result)) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(35, 7, 'Trans ID', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Date', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Type', 1, 0, 'C');
            $pdf->Cell(50, 7, 'Details', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Status', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Amount', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 9);
        }
        
        $pdf->Cell(25, 6, $transaction['transaction_id'], 1, 0);
        $pdf->Cell(25, 6, date('m/d/Y', strtotime($transaction['transaction_date'])), 1, 0);
        $pdf->Cell(25, 6, $transaction['transaction_type'], 1, 0);
        
        // Truncate details if too long
        $details = substr($transaction['item_details'], 0, 30);
        if (strlen($transaction['item_details']) > 30) {
            $details .= '...';
        }
        $pdf->Cell(50, 6, $details, 1, 0);
        
        $pdf->Cell(25, 6, $transaction['status'], 1, 0);
        $pdf->Cell(25, 6, 'P' . number_format($transaction['amount'], 2), 1, 1, 'R');
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'transaction_history_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
    
    // Close statement
    mysqli_stmt_close($transaction_stmt);
}

// Function to format transaction details
function formatTransactionDetails($transaction) {
    $details = '';
    
    if ($transaction['transaction_type'] === 'Pickup') {
        // For pickup transactions, use the existing format
        $details = $transaction['item_details'];
    } else {
        // For walk-in transactions, parse the JSON and format it
        $item_details = $transaction['item_details'];
        
        // Try to parse as JSON first
        $items = json_decode($item_details, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($items)) {
            // Format as JSON array
            foreach ($items as $item) {
                if (isset($item['material']) && isset($item['quantity'])) {
                    $details .= "{$item['material']} ({$item['quantity']}kg)";
                    if (isset($item['price_per_kg'])) {
                        $details .= " @ ₱{$item['price_per_kg']}/kg";
                    }
                    if (isset($item['total'])) {
                        $details .= " - ₱{$item['total']}";
                    }
                    $details .= "\n";
                }
            }
        } else {
            // If not JSON, use the raw details
            $details = $item_details;
        }
    }
    
    return $details;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Transaction History</title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Carter+One&family=Gugi&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Newsreader:ital,opsz,wght@0,6..72,200;1,6..72,200&family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Winky+Sans:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --bg-beige: #E6D8C3;
            --sales-orange: #6A7F46; /* Changed to olive green */
            --stock-green: #708B4C;
            --panel-cream: #F2EAD3;
            --topbar-brown: #3C342C;
            --text-dark: #2E2B29;
            --icon-green: #6A7F46;
            --icon-orange: #6A7F46; /* Changed to olive green */
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
            overflow-x: hidden;
        }

        body.dark-mode {
            background-color: var(--dark-bg-primary);
            color: var(--dark-text-primary);
        }

        /* Enhanced animated background with mesh gradient effect */
        .bg-decoration {
            position: fixed;
            border-radius: 50%;
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
            filter: blur(80px);
        }
        
        .bg-decoration-1 {
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, var(--accent-blue) 0%, transparent 70%);
            top: -250px;
            left: -250px;
            animation: float 25s ease-in-out infinite;
        }
        
        .bg-decoration-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--icon-green) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            animation: float 20s ease-in-out infinite reverse;
        }
        
        .bg-decoration-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--sales-orange) 0%, transparent 70%);
            top: 40%;
            left: 20%;
            animation: float 30s ease-in-out infinite;
        }
        
        .bg-decoration-4 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, transparent 70%);
            top: 60%;
            right: 25%;
            animation: float 22s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(60px, -60px) scale(1.15) rotate(120deg); }
            66% { transform: translate(-40px, 40px) scale(0.85) rotate(240deg); }
        }

        /* Enhanced watermark with glow effect */
        .watermark-logo {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 700px;
            height: 700px;
            opacity: 0.12;
            z-index: 0;
            pointer-events: none;
            transition: opacity 0.6s ease;
            animation: floatWatermark 25s ease-in-out infinite;
            filter: drop-shadow(0 0 60px rgba(106, 127, 70, 0.3));
        }
        
        @keyframes floatWatermark {
            0%, 100% { transform: translate(-50%, -50%) scale(1) rotate(0deg); }
            50% { transform: translate(-50%, -52%) scale(1.08) rotate(5deg); }
        }

        /* Sidebar - New Vibrant Design */
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
            opacity: 0;
            transform: translateX(-20px);
            animation: slideInLeft 0.8s ease forwards 0.5s;
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
            opacity: 0;
            transform: scale(0.8);
            animation: fadeInScale 0.8s ease forwards 0.7s;
            cursor: pointer;
            overflow: hidden;
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
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 5px;
            text-align: center;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.8s ease forwards 0.9s;
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
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.8s ease forwards 1.1s;
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
            opacity: 0;
            transform: translateX(-10px);
        }

        .nav-menu li:nth-child(1) { animation: slideInLeft 0.6s ease forwards 1.3s; }
        .nav-menu li:nth-child(2) { animation: slideInLeft 0.6s ease forwards 1.4s; }
        .nav-menu li:nth-child(3) { animation: slideInLeft 0.6s ease forwards 1.5s; }
        .nav-menu li:nth-child(4) { animation: slideInLeft 0.6s ease forwards 1.6s; }
        .nav-menu li:nth-child(5) { animation: slideInLeft 0.6s ease forwards 1.7s; }
        .nav-menu li:nth-child(6) { animation: slideInLeft 0.6s ease forwards 1.8s; }

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
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.8s ease forwards 1.9s;
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
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.8s ease forwards 1.2s;
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
            background-color: rgba(106, 127, 70, 0.3);
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
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 0.8s;
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
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 1s;
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
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 1.1s;
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

        /* Dashboard Cards */
        .dashboard-card {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 1.7s;
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

        /* Transaction Filters */
        .transaction-filters {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .filter-group label {
            color: var(--dark-text-primary);
        }
        
        select, .date-input, .search-bar input {
            padding: 10px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            background-color: white;
            font-size: 14px;
            transition: all 0.3s;
            width: 100%;
            color: var(--text-dark);
        }

        body.dark-mode select,
        body.dark-mode .date-input,
        body.dark-mode .search-bar input {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        select:focus, .date-input:focus, .search-bar input:focus {
            outline: none;
            border-color: var(--icon-green);
            box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
        }
        
        .search-bar {
            position: relative;
            grid-column: 1 / -1;
        }
        
        .search-bar input {
            padding-left: 40px;
        }
        
        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dark);
            opacity: 0.7;
            transition: color 0.3s ease;
        }

        body.dark-mode .search-bar i {
            color: var(--dark-text-primary);
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            grid-column: 1 / -1;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, var(--icon-green) 0%, var(--stock-green) 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(106, 127, 70, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 127, 70, 0.4);
        }
        
        .btn-secondary {
            background-color: white;
            color: var(--text-dark);
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        body.dark-mode .btn-secondary {
            background-color: var(--dark-bg-tertiary);
            color: var(--dark-text-primary);
            border-color: var(--dark-border);
        }
        
        .btn-secondary:hover {
            background-color: #f8f9fa;
            border-color: rgba(0,0,0,0.2);
        }

        body.dark-mode .btn-secondary:hover {
            background-color: var(--dark-bg-secondary);
            border-color: var(--dark-border);
        }

        /* Transaction Table */
        .transaction-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .transaction-table thead {
            position: sticky;
            top: 0;
        }

        .transaction-table th {
            background-color: rgba(106, 127, 70, 0.08);
            font-weight: 600;
            color: var(--icon-green);
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid rgba(106, 127, 70, 0.2);
            transition: all 0.3s ease;
        }

        body.dark-mode .transaction-table th {
            background-color: rgba(106, 127, 70, 0.15);
            color: var(--dark-text-primary);
            border-bottom-color: var(--dark-border);
        }

        .transaction-table td {
            padding: 14px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        body.dark-mode .transaction-table td {
            border-bottom-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .transaction-table tr:last-child td {
            border-bottom: none;
        }

        .transaction-table tr:hover td {
            background-color: rgba(106, 127, 70, 0.03);
        }

        body.dark-mode .transaction-table tr:hover td {
            background-color: rgba(106, 127, 70, 0.1);
        }

        .transaction-id {
            color: var(--icon-green);
            font-weight: 500;
            font-family: 'Courier New', monospace;
        }
        
        .transaction-items {
            font-size: 13px;
            color: var(--text-dark);
            opacity: 0.7;
            margin-top: 5px;
            transition: color 0.3s ease;
        }

        body.dark-mode .transaction-items {
            color: var(--dark-text-secondary);
        }
        
        .transaction-status {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            gap: 5px;
        }
        
        .transaction-status i {
            font-size: 10px;
        }
        
        .status-completed {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #d39e00;
        }
        
        .status-cancelled {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .transaction-amount {
            font-weight: 600;
            white-space: nowrap;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 25px;
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

        .btn-danger {
            background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .export-btn {
            background: linear-gradient(90deg, var(--sales-orange) 0%, #6A7F46 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(106, 127, 70, 0.3);
            text-decoration: none;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 127, 70, 0.4);
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
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            position: relative;
            animation: slideDown 0.3s;
            transition: all 0.3s ease;
        }

        body.dark-mode .modal-content {
            background-color: var(--dark-bg-secondary);
            box-shadow: 0 10px 30px var(--dark-shadow);
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
            border-bottom: 1px solid rgba(0,0,0,0.1);
            transition: border-color 0.3s ease;
        }

        body.dark-mode .modal-header {
            border-bottom-color: var(--dark-border);
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .modal-title {
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

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
            transition: color 0.3s ease;
        }

        body.dark-mode .form-group label {
            color: var(--dark-text-primary);
        }

        .form-group textarea {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
            background-color: white;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        body.dark-mode .form-group textarea {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .form-group textarea:focus {
            outline: none;
            border-color: var(--icon-green);
            box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .alert i {
            font-size: 20px;
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

        .empty-state a {
            color: var(--icon-green);
            text-decoration: none;
            font-weight: 500;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 8px;
            align-items: center;
        }
        
        .page-btn {
            min-width: 40px;
            height: 40px;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            color: var(--text-dark);
            transition: all 0.2s ease;
            text-decoration: none;
            padding: 0 12px;
        }

        body.dark-mode .page-btn {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        .page-btn:hover:not(.active, .disabled) {
            background-color: rgba(106, 127, 70, 0.1);
            border-color: var(--icon-green);
            color: var(--icon-green);
        }
        
        .page-btn.active {
            background-color: var(--icon-green);
            color: white;
            border-color: var(--icon-green);
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(106, 127, 70, 0.2);
        }
        
        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .page-btn i {
            font-size: 14px;
        }
        
        .page-dots {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            opacity: 0.7;
            transition: color 0.3s ease;
        }

        body.dark-mode .page-dots {
            color: var(--dark-text-primary);
        }

        /* Profile Picture Modal Styles */
        .profile-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            animation: fadeIn 0.3s;
        }

        .profile-modal-content {
            position: relative;
            margin: 5% auto;
            width: 90%;
            max-width: 500px;
            text-align: center;
            animation: slideDown 0.3s;
        }

        .profile-modal-image {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.5);
        }

        .profile-modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .profile-modal-close:hover {
            color: var(--icon-green);
            transform: scale(1.1);
        }

        .profile-modal-name {
            color: white;
            margin-top: 20px;
            font-size: 24px;
            font-weight: 600;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
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
        }

        @media (max-width: 768px) {
            .transaction-filters {
                grid-template-columns: 1fr 1fr;
            }
            
            .transaction-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .page-title {
                font-size: 36px;
            }
        }
        
        @media (max-width: 576px) {
            .transaction-filters {
                grid-template-columns: 1fr;
            }
            
            .filter-buttons {
                flex-direction: column;
            }
            
            .page-btn {
                min-width: 36px;
                height: 36px;
                font-size: 14px;
            }
            
            .page-dots {
                width: 36px;
                height: 36px;
            }
            
            .dashboard-card {
                padding: 20px 15px;
            }
            
            .transaction-table td, 
            .transaction-table th {
                padding: 12px 10px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-controls {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced animated background with mesh gradient effect -->
    <div class="bg-decoration bg-decoration-1"></div>
    <div class="bg-decoration bg-decoration-2"></div>
    <div class="bg-decoration bg-decoration-3"></div>
    <div class="bg-decoration bg-decoration-4"></div>
    
    <!-- Enhanced watermark with glow effect -->
    <img src="img/MainLogo.svg" alt="JunkValue Watermark" class="watermark-logo">
    
    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar" id="profilePicture">
                <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                <?php else: ?>
                    <?php echo $user_initials; ?>
                <?php endif; ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="user-status">
                <span class="status-indicator"></span>
                <span>Active</span>
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
            <li><a href="#" class="active"><i class="fas fa-history"></i> <?php echo $t['transaction_history']; ?></a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> <?php echo $t['schedule_pickup']; ?></a></li>
            <li><a href="prices.php"><i class="fas fa-coins"></i> <?php echo $t['current_prices']; ?></a></li>
            <li><a href="rewards.php"><i class="fas fa-award"></i> <?php echo $t['loyalty_rewards']; ?></a></li>
            <li><a href="settings.php"><i class="fas fa-user-cog"></i> <?php echo $t['account_settings']; ?></a></li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
            </a>
        </div>
    </div>
    
     <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 class="page-title"><?php echo $t['transaction_history']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> <?php echo $t['your_transactions']; ?></h3>
                <a href="transaction.php?export=pdf" class="export-btn">
                    <i class="fas fa-download"></i> <?php echo $t['export_pdf']; ?>
                </a>
            </div>
            
            <!-- Display success/error messages -->
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <form method="GET" action="">
                <input type="hidden" name="page" value="1"> <!-- Reset to page 1 when filters change -->
                <div class="transaction-filters">
                    <div class="filter-group">
                        <label><?php echo $t['transaction_type']; ?></label>
                        <select name="type">
                            <option value=""><?php echo $t['all_types']; ?></option>
                            <option value="Pickup" <?php echo ($type_filter == 'Pickup') ? 'selected' : ''; ?>><?php echo $t['pickups']; ?></option>
                            <option value="Walk-in" <?php echo ($type_filter == 'Walk-in') ? 'selected' : ''; ?>><?php echo $t['walk_in_sales']; ?></option>
                            <option value="Loyalty" <?php echo ($type_filter == 'Loyalty') ? 'selected' : ''; ?>><?php echo $t['loyalty_rewards']; ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><?php echo $t['status']; ?></label>
                        <select name="status">
                            <option value=""><?php echo $t['all_statuses']; ?></option>
                            <option value="Completed" <?php echo ($status_filter == 'Completed') ? 'selected' : ''; ?>><?php echo $t['completed']; ?></option>
                            <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>><?php echo $t['pending']; ?></option>
                            <option value="Cancelled" <?php echo ($status_filter == 'Cancelled') ? 'selected' : ''; ?>><?php echo $t['cancelled']; ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><?php echo $t['date_from']; ?></label>
                        <input type="date" class="date-input" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><?php echo $t['date_to']; ?></label>
                        <input type="date" class="date-input" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="<?php echo $t['search_transactions']; ?>" value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?php echo $t['apply_filters']; ?>
                        </button>
                        
                        <a href="transaction.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <?php echo $t['clear_filters']; ?>
                        </a>
                    </div>
                </div>
            </form>
            
            <!-- Transaction Table -->
            <div style="overflow-x: auto;">
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th><?php echo $t['id']; ?></th>
                            <th><?php echo $t['date_time']; ?></th>
                            <th><?php echo $t['type']; ?></th>
                            <th><?php echo $t['details']; ?></th>
                            <th><?php echo $t['status']; ?></th>
                            <th><?php echo $t['amount']; ?></th>
                            <th><?php echo $t['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($transaction = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="transaction-id">#<?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?>
                                        <div class="transaction-items">
                                            <?php echo date('g:i A', strtotime($transaction['transaction_time'])); ?>
                                        </div>
                                        <?php if ($transaction['transaction_type'] == 'Pickup' && isset($transaction['pickup_date'])): ?>
                                            <div class="transaction-items">
                                                <strong><?php echo $t['pickup']; ?>:</strong> 
                                                <?php echo date('M j, Y', strtotime($transaction['pickup_date'])); ?>
                                                <?php if (isset($transaction['pickup_time'])): ?>
                                                    at <?php echo date('g:i A', strtotime($transaction['pickup_time'])); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $type_icon = '';
                                        switch($transaction['transaction_type']) {
                                            case 'Pickup': $type_icon = 'fa-truck'; break;
                                            case 'Walk-in': $type_icon = 'fa-walking'; break;
                                            case 'Loyalty': $type_icon = 'fa-award'; break;
                                            default: $type_icon = 'fa-exchange-alt';
                                        }
                                        ?>
                                        <i class="fas <?php echo $type_icon; ?>" style="margin-right: 8px; color: var(--icon-green);"></i>
                                        <?php echo htmlspecialchars($transaction['transaction_type']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        // Use the formatTransactionDetails function to display details
                                        $formatted_details = formatTransactionDetails($transaction);
                                        echo nl2br(htmlspecialchars($formatted_details));
                                        ?>
                                        
                                        <?php if (!empty($transaction['additional_info'])): ?>
                                            <div class="transaction-items">
                                                <?php echo htmlspecialchars($transaction['additional_info']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($transaction['shipping_method_name'])): ?>
                                            <div class="transaction-items">
                                                <strong><?php echo $t['shipping']; ?>:</strong> <?php echo htmlspecialchars($transaction['shipping_method_name']); ?>
                                                <?php if (!empty($transaction['shipping_fee'])): ?>
                                                    (₱<?php echo number_format($transaction['shipping_fee'], 2); ?>)
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($transaction['cancel_reason'])): ?>
                                            <div class="transaction-items" style="color: #dc3545;">
                                                <strong><?php echo $t['cancellation_reason']; ?>:</strong> <?php echo htmlspecialchars($transaction['cancel_reason']); ?>
                                                <?php if (!empty($transaction['cancelled_at'])): ?>
                                                    <br><small>on <?php echo date('M j, Y g:i A', strtotime($transaction['cancelled_at'])); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Add referral information check here -->
                                        <?php if ($transaction['status'] === 'Completed' && $transaction['transaction_type'] === 'Pickup'): ?>
                                            <?php
                                            // Check if this transaction earned referral points for someone
                                            $referral_stmt = $conn->prepare("SELECT * FROM referral_logs WHERE referred_id = ?");
                                            $referral_stmt->bind_param("i", $user_id);
                                            $referral_stmt->execute();
                                            $referral_result = $referral_stmt->get_result();
                                            $referral = $referral_result->fetch_assoc();
                                            if ($referral): ?>
                                                <div class="referral-notice">
                                                    <i class="fas fa-gift"></i>
                                                    This transaction earned <?php echo $referral['points_awarded']; ?> points for your referrer!
                                                </div>
                                            <?php 
                                            $referral_stmt = null; // Close the statement
                                            endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_class = 'status-' . strtolower($transaction['status']);
                                        $status_icon = '';
                                        switch($transaction['status']) {
                                            case 'Completed': $status_icon = 'fa-check-circle'; break;
                                            case 'Pending': $status_icon = 'fa-clock'; break;
                                            case 'Cancelled': $status_icon = 'fa-times-circle'; break;
                                        }
                                        echo '<span class="transaction-status ' . $status_class . '">' . 
                                             '<i class="fas ' . $status_icon . '"></i>' . 
                                             htmlspecialchars($transaction['status']) . '</span>';
                                        ?>
                                    </td>
                                    <td class="transaction-amount" style="color: <?php 
                                        echo ($transaction['transaction_type'] == 'Loyalty') ? 'var(--sales-orange)' : 'var(--icon-green)';
                                    ?>;">
                                        +₱<?php echo number_format($transaction['amount'], 2); ?>
                                    </td>
                                    <td>
                                        <?php if ($transaction['status'] == 'Pending' && $transaction['transaction_type'] == 'Pickup'): ?>
                                            <button class="btn btn-danger btn-sm cancel-btn" 
                                                    data-transaction-id="<?php echo $transaction['transaction_id']; ?>">
                                                <i class="fas fa-times"></i> <?php echo $t['cancel']; ?>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-info-circle"></i> N/A
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-info-circle"></i>
                                    <p><?php echo $t['no_transactions_found']; ?></p>
                                    <?php if (!empty($type_filter) || !empty($status_filter) || !empty($date_from) || !empty($search_query)): ?>
                                        <p><?php echo $t['try_adjusting_filters']; ?> <a href="transaction.php"><?php echo $t['clear_all_filters']; ?></a></p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="page-btn" title="<?php echo $t['first_page']; ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>" class="page-btn" title="<?php echo $t['previous_page']; ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    // Show page numbers
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="page-btn">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="page-dots">...</span>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active = ($i == $current_page) ? 'active' : '';
                        echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '" class="page-btn ' . $active . '">' . $i . '</a>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="page-dots">...</span>';
                        }
                        echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '" class="page-btn">' . $total_pages . '</a>';
                    }
                    ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>" class="page-btn" title="<?php echo $t['next_page']; ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" class="page-btn" title="<?php echo $t['last_page']; ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cancel Transaction Modal -->
    <div class="modal" id="cancelModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $t['cancel_transaction']; ?></h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="cancelForm" method="POST" action="">
                <input type="hidden" name="transaction_id" id="modalTransactionId">
                <div class="form-group">
                    <label for="cancel_reason"><?php echo $t['reason_cancellation']; ?></label>
                    <textarea name="cancel_reason" id="cancel_reason" required 
                              placeholder="<?php echo $t['reason_cancellation']; ?>..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="cancel_transaction" class="btn btn-danger"><?php echo $t['confirm_cancellation']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Profile Picture Modal -->
    <div id="profilePictureModal" class="profile-modal">
        <div class="profile-modal-content">
            <span class="profile-modal-close" onclick="document.getElementById('profilePictureModal').style.display='none'">&times;</span>
            <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture" class="profile-modal-image">
            <?php else: ?>
                <div style="width: 300px; height: 300px; background-color: var(--panel-cream); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 80px; font-weight: bold; color: var(--topbar-brown); margin: 0 auto;">
                    <?php echo $user_initials; ?>
                </div>
            <?php endif; ?>
            <div class="profile-modal-name"><?php echo htmlspecialchars($user_name); ?></div>
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

        // Profile picture modal functionality
        document.getElementById('profilePicture').addEventListener('click', function() {
            document.getElementById('profilePictureModal').style.display = 'block';
        });

        // Modal functionality
        const cancelButtons = document.querySelectorAll('.cancel-btn');
        const cancelModal = document.getElementById('cancelModal');
        const modalTransactionId = document.getElementById('modalTransactionId');
        const closeModalButtons = document.querySelectorAll('.close-modal');
        
        // Open modal when cancel button is clicked
        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                const transactionId = this.getAttribute('data-transaction-id');
                modalTransactionId.value = transactionId;
                cancelModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        });
        
        // Close modal when X or cancel button is clicked
        closeModalButtons.forEach(button => {
            button.addEventListener('click', function() {
                cancelModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        });
        
        // Close modal when clicking outside the modal content
        cancelModal.addEventListener('click', function(e) {
            if (e.target === cancelModal) {
                cancelModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        
        // Prevent form submission from closing the modal
        document.getElementById('cancelForm').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
            if (event.target.classList.contains('profile-modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php
// Close prepared statements
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
if (isset($count_stmt)) {
    mysqli_stmt_close($count_stmt);
}
if (isset($user_stmt)) {
    mysqli_stmt_close($user_stmt);
}
// Close connection
mysqli_close($conn);
?>