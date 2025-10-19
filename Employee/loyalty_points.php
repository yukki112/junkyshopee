<?php
session_start();
require_once 'db_connection.php';
require_once 'fpdf/fpdf.php';

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
        'enhanced_loyalty_points_management' => 'Enhanced Loyalty Points Management',
        'add_points' => 'Add Points',
        'deduct_points' => 'Deduct Points',
        'manual_adjustment' => 'Manual Adjustment',
        'recent_activities' => 'Recent Activities',
        'customer_information' => 'Customer Information',
        'customer_username' => 'Customer Username',
        'customer_name' => 'Customer Name',
        'points_to_add' => 'Points to Add',
        'points_to_deduct' => 'Points to Deduct',
        'adjustment_points' => 'Adjustment Points (+ or -)',
        'reason' => 'Reason',
        'reason_for_adjustment' => 'Reason for Adjustment',
        'generate_pdf_receipt' => 'Generate PDF Receipt',
        'clear_form' => 'Clear Form',
        'apply_adjustment' => 'Apply Adjustment',
        'customer_details' => 'Customer Details',
        'name' => 'Name',
        'current_points' => 'Current Points',
        'loyalty_tier' => 'Loyalty Tier',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
        'search' => 'Search',
        'filter' => 'Filter',
        'date_time' => 'Date/Time',
        'customer' => 'Customer',
        'points_change' => 'Points Change',
        'previous_points' => 'Previous Points',
        'new_points' => 'New Points',
        'tier' => 'Tier',
        'receipt' => 'Receipt',
        'processed_by' => 'Processed By',
        'no_recent_point_activities' => 'No recent point activities found',
        'view' => 'View',
        'not_generated' => 'Not generated',
        'transaction_successful' => 'Transaction Successful!',
        'print_receipt' => 'Print Receipt',
        'close' => 'Close',
        'use_positive_negative' => 'Use positive numbers to add points, negative numbers to deduct points'
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
        'enhanced_loyalty_points_management' => 'Pinahusay na Pamamahala ng Loyalty Points',
        'add_points' => 'Magdagdag ng Points',
        'deduct_points' => 'Bawasan ng Points',
        'manual_adjustment' => 'Manual na Pag-aayos',
        'recent_activities' => 'Mga Kamakailang Aktibidad',
        'customer_information' => 'Impormasyon ng Customer',
        'customer_username' => 'Username ng Customer',
        'customer_name' => 'Pangalan ng Customer',
        'points_to_add' => 'Points na Idadagdag',
        'points_to_deduct' => 'Points na Babawasan',
        'adjustment_points' => 'Points sa Pag-aayos (+ o -)',
        'reason' => 'Dahilan',
        'reason_for_adjustment' => 'Dahilan para sa Pag-aayos',
        'generate_pdf_receipt' => 'Gumawa ng PDF Receipt',
        'clear_form' => 'I-clear ang Form',
        'apply_adjustment' => 'Ilapat ang Pag-aayos',
        'customer_details' => 'Mga Detalye ng Customer',
        'name' => 'Pangalan',
        'current_points' => 'Kasalukuyang Points',
        'loyalty_tier' => 'Antas ng Loyalty',
        'date_from' => 'Petsa Mula',
        'date_to' => 'Petsa Hanggang',
        'search' => 'Maghanap',
        'filter' => 'Filter',
        'date_time' => 'Petsa/Oras',
        'customer' => 'Customer',
        'points_change' => 'Pagbabago ng Points',
        'previous_points' => 'Nakaraang Points',
        'new_points' => 'Bagong Points',
        'tier' => 'Antas',
        'receipt' => 'Resibo',
        'processed_by' => 'Prosesado ni',
        'no_recent_point_activities' => 'Walang nakitang mga kamakailang aktibidad ng points',
        'view' => 'Tingnan',
        'not_generated' => 'Hindi nagawa',
        'transaction_successful' => 'Matagumpay ang Transaksyon!',
        'print_receipt' => 'I-print ang Resibo',
        'close' => 'Isara',
        'use_positive_negative' => 'Gumamit ng positibong numero para magdagdag ng points, negatibong numero para magbawas ng points'
    ]
];

$t = $translations[$language];

function calculateLoyaltyTier($points) {
    if ($points >= 20000) return 'ethereal';
    if ($points >= 10000) return 'diamond';
    if ($points >= 5000) return 'platinum';
    if ($points >= 3000) return 'gold';
    if ($points >= 1000) return 'silver';
    return 'bronze';
}

function generatePointsReceipt($customer_data, $transaction_data, $employee_name) {
    date_default_timezone_set('Asia/Manila');
    
    $pdf = new FPDF();
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 15, 'JunkValue', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 7, '10 Sto. Nino St. Barangay Commonwealth Quezon city', 0, 1, 'C');
    $pdf->Cell(0, 12, 'Contact: 0947 884 4412', 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 15, 'LOYALTY POINTS RECEIPT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $transaction_id = 'LP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Transaction ID:', 0, 0);
    $pdf->Cell(0, 7, $transaction_id, 0, 1);
    $pdf->Cell(40, 7, 'Date/Time:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    $pdf->Cell(40, 7, 'Customer:', 0, 0);
    $pdf->Cell(0, 7, $customer_data['name'], 0, 1);
    $pdf->Cell(40, 7, 'Username:', 0, 0);
    $pdf->Cell(0, 7, '@' . $customer_data['username'], 0, 1);
    $pdf->Cell(40, 7, 'Processed by:', 0, 0);
    $pdf->Cell(0, 7, $employee_name, 0, 1);
    
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'POINTS TRANSACTION DETAILS', 0, 1, 'C');
    $pdf->Ln(5);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Transaction Type:', 0, 0);
    $pdf->Cell(0, 7, $transaction_data['type'], 0, 1);
    $pdf->Cell(50, 7, 'Points Change:', 0, 0);
    $pdf->Cell(0, 7, $transaction_data['points'] . ' pts', 0, 1);
    $pdf->Cell(50, 7, 'Reason:', 0, 0);
    $pdf->Cell(0, 7, $transaction_data['reason'], 0, 1);
    $pdf->Cell(50, 7, 'Previous Balance:', 0, 0);
    $pdf->Cell(0, 7, $transaction_data['previous_points'] . ' pts', 0, 1);
    $pdf->Cell(50, 7, 'New Balance:', 0, 0);
    $pdf->Cell(0, 7, $transaction_data['new_points'] . ' pts', 0, 1);
    $pdf->Cell(50, 7, 'Loyalty Tier:', 0, 0);
    $pdf->Cell(0, 7, strtoupper($transaction_data['tier']), 0, 1);
    
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Thank you for choosing JunkValue!', 0, 1, 'C');
    $pdf->Cell(0, 7, 'Keep collecting points for rewards!', 0, 1, 'C');
    
    $receipt_dir = 'receipts/loyalty/';
    if (!file_exists($receipt_dir)) {
        mkdir($receipt_dir, 0755, true);
    }
    
    $filename = $receipt_dir . $transaction_id . '.pdf';
    $pdf->Output($filename, 'F');
    
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_points'])) {
        $conn->begin_transaction();
        try {
            $customer_username = sanitizeInput($_POST['customer_username']);
            $customer_name = sanitizeInput($_POST['customer_name']);
            $points_to_add = intval($_POST['points_to_add']);
            $reason = sanitizeInput($_POST['reason']);
            $generate_receipt = isset($_POST['generate_receipt']);
            
            if ($points_to_add <= 0) {
                throw new Exception("Points must be greater than 0");
            }
            
            $customer_id = null;
            if ($customer_username) {
                $check_user = $conn->prepare("SELECT id, first_name, last_name, loyalty_points FROM users WHERE username = ?");
                $check_user->bind_param("s", $customer_username);
                $check_user->execute();
                $check_user->store_result();
                
                if ($check_user->num_rows > 0) {
                    $check_user->bind_result($customer_id, $first_name, $last_name, $current_points);
                    $check_user->fetch();
                    $customer_name = $first_name . ' ' . $last_name;
                } else {
                    throw new Exception("Username not found");
                }
            } else {
                throw new Exception("Customer username is required");
            }
            
            $new_points = $current_points + $points_to_add;
            $new_tier = calculateLoyaltyTier($new_points);
            
            $stmt = $conn->prepare("UPDATE users SET loyalty_points = ?, loyalty_tier = ? WHERE id = ?");
            $stmt->bind_param("isi", $new_points, $new_tier, $customer_id);
            $stmt->execute();
            
            $receipt_path = null;
            if ($generate_receipt) {
                $customer_data = [
                    'name' => $customer_name,
                    'username' => $customer_username
                ];
                $transaction_data = [
                    'type' => 'POINTS ADDED',
                    'points' => '+' . $points_to_add,
                    'reason' => $reason,
                    'previous_points' => $current_points,
                    'new_points' => $new_points,
                    'tier' => $new_tier
                ];
                $receipt_path = generatePointsReceipt($customer_data, $transaction_data, $employee_name);
            }
            
            $log_query = "INSERT INTO loyalty_point_logs (customer_id, employee_id, points_change, reason, previous_points, new_points, receipt_path, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("iiisiis", $customer_id, $employee_id, $points_to_add, $reason, $current_points, $new_points, $receipt_path);
            $log_stmt->execute();
            
            $conn->commit();
            
            if ($generate_receipt) {
                $_SESSION['receipt_path'] = $receipt_path;
                $_SESSION['show_success_modal'] = true;
                $_SESSION['transaction_type'] = 'Points Addition';
            }
            
            $_SESSION['success'] = "Successfully added $points_to_add points to $customer_name. New balance: $new_points points. Tier: " . strtoupper($new_tier);
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Points addition failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to add points: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['deduct_points'])) {
        $conn->begin_transaction();
        try {
            $customer_username = sanitizeInput($_POST['customer_username']);
            $customer_name = sanitizeInput($_POST['customer_name']);
            $points_to_deduct = intval($_POST['points_to_deduct']);
            $reason = sanitizeInput($_POST['reason']);
            $generate_receipt = isset($_POST['generate_receipt']);
            
            if ($points_to_deduct <= 0) {
                throw new Exception("Points must be greater than 0");
            }
            
            $customer_id = null;
            if ($customer_username) {
                $check_user = $conn->prepare("SELECT id, first_name, last_name, loyalty_points FROM users WHERE username = ?");
                $check_user->bind_param("s", $customer_username);
                $check_user->execute();
                $check_user->store_result();
                
                if ($check_user->num_rows > 0) {
                    $check_user->bind_result($customer_id, $first_name, $last_name, $current_points);
                    $check_user->fetch();
                    $customer_name = $first_name . ' ' . $last_name;
                } else {
                    throw new Exception("Username not found");
                }
            } else {
                throw new Exception("Customer username is required");
            }
            
            if ($current_points < $points_to_deduct) {
                throw new Exception("Insufficient points. Customer has only $current_points points.");
            }
            
            $new_points = $current_points - $points_to_deduct;
            $new_tier = calculateLoyaltyTier($new_points);
            
            $stmt = $conn->prepare("UPDATE users SET loyalty_points = ?, loyalty_tier = ? WHERE id = ?");
            $stmt->bind_param("isi", $new_points, $new_tier, $customer_id);
            $stmt->execute();
            
            $receipt_path = null;
            if ($generate_receipt) {
                $customer_data = [
                    'name' => $customer_name,
                    'username' => $customer_username
                ];
                $transaction_data = [
                    'type' => 'POINTS DEDUCTED',
                    'points' => '-' . $points_to_deduct,
                    'reason' => $reason,
                    'previous_points' => $current_points,
                    'new_points' => $new_points,
                    'tier' => $new_tier
                ];
                $receipt_path = generatePointsReceipt($customer_data, $transaction_data, $employee_name);
            }
            
            $points_change = -$points_to_deduct;
            $log_query = "INSERT INTO loyalty_point_logs (customer_id, employee_id, points_change, reason, previous_points, new_points, receipt_path, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("iiisiis", $customer_id, $employee_id, $points_change, $reason, $current_points, $new_points, $receipt_path);
            $log_stmt->execute();
            
            $conn->commit();
            
            if ($generate_receipt) {
                $_SESSION['receipt_path'] = $receipt_path;
                $_SESSION['show_success_modal'] = true;
                $_SESSION['transaction_type'] = 'Points Deduction';
            }
            
            $_SESSION['success'] = "Successfully deducted $points_to_deduct points from $customer_name. New balance: $new_points points. Tier: " . strtoupper($new_tier);
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Points deduction failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to deduct points: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['manual_adjust'])) {
        $conn->begin_transaction();
        try {
            $customer_username = sanitizeInput($_POST['customer_username']);
            $adjustment_points = intval($_POST['adjustment_points']);
            $reason = sanitizeInput($_POST['reason']);
            $generate_receipt = isset($_POST['generate_receipt']);
            
            if ($adjustment_points == 0) {
                throw new Exception("Adjustment points cannot be zero");
            }
            
            $customer_id = null;
            if ($customer_username) {
                $check_user = $conn->prepare("SELECT id, first_name, last_name, loyalty_points FROM users WHERE username = ?");
                $check_user->bind_param("s", $customer_username);
                $check_user->execute();
                $check_user->store_result();
                
                if ($check_user->num_rows > 0) {
                    $check_user->bind_result($customer_id, $first_name, $last_name, $current_points);
                    $check_user->fetch();
                    $customer_name = $first_name . ' ' . $last_name;
                } else {
                    throw new Exception("Username not found");
                }
            } else {
                throw new Exception("Customer username is required");
            }
            
            if ($adjustment_points < 0 && ($current_points + $adjustment_points) < 0) {
                throw new Exception("Adjustment would result in negative points. Customer has only $current_points points.");
            }
            
            $new_points = $current_points + $adjustment_points;
            $new_tier = calculateLoyaltyTier($new_points);
            
            $stmt = $conn->prepare("UPDATE users SET loyalty_points = ?, loyalty_tier = ? WHERE id = ?");
            $stmt->bind_param("isi", $new_points, $new_tier, $customer_id);
            $stmt->execute();
            
            $receipt_path = null;
            if ($generate_receipt) {
                $customer_data = [
                    'name' => $customer_name,
                    'username' => $customer_username
                ];
                $transaction_data = [
                    'type' => 'MANUAL ADJUSTMENT',
                    'points' => ($adjustment_points > 0 ? '+' : '') . $adjustment_points,
                    'reason' => $reason,
                    'previous_points' => $current_points,
                    'new_points' => $new_points,
                    'tier' => $new_tier
                ];
                $receipt_path = generatePointsReceipt($customer_data, $transaction_data, $employee_name);
            }
            
            $log_query = "INSERT INTO loyalty_point_logs (customer_id, employee_id, points_change, reason, previous_points, new_points, receipt_path, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("iiisiis", $customer_id, $employee_id, $adjustment_points, $reason, $current_points, $new_points, $receipt_path);
            $log_stmt->execute();
            
            $conn->commit();
            
            if ($generate_receipt) {
                $_SESSION['receipt_path'] = $receipt_path;
                $_SESSION['show_success_modal'] = true;
                $_SESSION['transaction_type'] = 'Manual Adjustment';
            }
            
            $adjustment_type = $adjustment_points > 0 ? 'added' : 'deducted';
            $abs_points = abs($adjustment_points);
            $_SESSION['success'] = "Successfully $adjustment_type $abs_points points for $customer_name. New balance: $new_points points. Tier: " . strtoupper($new_tier);
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Manual adjustment failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to adjust points: " . $e->getMessage();
        }
    }
    
    header("Location: loyalty_points.php");
    exit();
}

function sanitizeInput($data) {
    global $conn;
    return $conn->real_escape_string(strip_tags(trim($data)));
}

$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$search = $_GET['search'] ?? null;

$query = "SELECT lpl.*, 
          CONCAT(u.first_name, ' ', u.last_name) as customer_name, 
          u.username as customer_username,
          u.loyalty_tier,
          CONCAT(e.first_name, ' ', e.last_name) as employee_name
         FROM loyalty_point_logs lpl 
         LEFT JOIN users u ON lpl.customer_id = u.id 
         LEFT JOIN employees e ON lpl.employee_id = e.id 
         WHERE lpl.employee_id = ?";
         
$params = [$employee_id];
$types = "i";

if ($date_from) {
    $query .= " AND DATE(lpl.created_at) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $query .= " AND DATE(lpl.created_at) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

if ($search) {
    $query .= " AND (u.username LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR lpl.reason LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$query .= " ORDER BY lpl.created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$recent_activities = [];
while ($row = $result->fetch_assoc()) {
    $recent_activities[] = $row;
}

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

$show_modal = $_SESSION['show_success_modal'] ?? false;
$transaction_type = $_SESSION['transaction_type'] ?? '';
$receipt_path = $_SESSION['receipt_path'] ?? '';

unset($_SESSION['show_success_modal'], $_SESSION['transaction_type'], $_SESSION['receipt_path']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Loyalty Point Input</title>
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
        --gold-tier: #FFD700;
        --silver-tier: #C0C0C0;
        --bronze-tier: #CD7F32;
        /* Added new tier colors */
        --platinum-tier: #E5E4E2;
        --diamond-tier: #B9F2FF;
        --ethereal-tier: #8A2BE2;
        
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
        border-radius: 20px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideIn 0.5s ease-out;
        margin: auto;
        position: relative;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
    }

    @keyframes slideIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal iframe {
        width: 100%;
        height: 400px;
        border: 1px solid #ddd;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    body.dark-mode .modal iframe {
        border-color: var(--dark-border);
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
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    body.dark-mode #closeModal {
        color: var(--dark-text-primary);
    }

    #closeModal:hover {
        background-color: #f5f5f5;
        transform: scale(1.1);
    }

    body.dark-mode #closeModal:hover {
        background-color: var(--dark-bg-tertiary);
    }

    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
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
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(217, 122, 65, 0.3);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    body.dark-mode .welcome-banner {
        background: linear-gradient(135deg, var(--dark-bg-secondary) 0%, var(--dark-bg-tertiary) 100%);
        border-color: var(--dark-border);
        box-shadow: 0 8px 25px var(--dark-shadow);
    }

    .welcome-content h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--topbar-brown);
        margin-bottom: 12px;
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
        font-size: 16px;
        line-height: 1.6;
        transition: color 0.3s ease;
    }

    body.dark-mode .welcome-content p {
        color: var(--dark-text-secondary);
    }

    .welcome-icon {
        position: absolute;
        right: 30px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 120px;
        color: rgba(217, 122, 65, 0.08);
        z-index: 1;
    }

    body.dark-mode .welcome-icon {
        color: rgba(217, 122, 65, 0.05);
    }

    .transaction-tabs {
        display: flex;
        gap: 12px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .transaction-tab {
        padding: 14px 24px;
        border-radius: 12px;
        background-color: white;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }

    body.dark-mode .transaction-tab {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .transaction-tab::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.05) 0%, rgba(106, 127, 70, 0.02) 100%);
        opacity: 0;
        transition: all 0.3s ease;
    }

    .transaction-tab:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        border-color: rgba(106, 127, 70, 0.2);
    }

    body.dark-mode .transaction-tab:hover {
        box-shadow: 0 8px 20px var(--dark-shadow);
    }

    .transaction-tab:hover::before {
        opacity: 1;
    }

    .transaction-tab.active {
        background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
        color: white;
        border-color: var(--icon-green);
        box-shadow: 0 6px 20px rgba(106, 127, 70, 0.3);
    }

    .transaction-tab.active::before {
        opacity: 0;
    }

    .dashboard-card {
        background-color: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.03);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    body.dark-mode .dashboard-card {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 8px 25px var(--dark-shadow);
    }

    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--icon-green) 0%, var(--sales-orange) 100%);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 2px solid rgba(0,0,0,0.03);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .card-header {
        border-bottom-color: var(--dark-border);
    }

    .card-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: 0.3px;
        transition: color 0.3s ease;
    }

    body.dark-mode .card-title {
        color: var(--dark-text-primary);
    }

    .card-title i {
        color: var(--icon-green);
        font-size: 22px;
    }

    .view-all {
        color: var(--icon-green);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
        padding: 8px 12px;
        border-radius: 8px;
    }

    .view-all:hover {
        color: var(--sales-orange);
        background-color: rgba(217, 122, 65, 0.05);
        transform: translateX(3px);
    }

    body.dark-mode .view-all:hover {
        background-color: rgba(217, 122, 65, 0.1);
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 15px;
        transition: color 0.3s ease;
    }

    body.dark-mode .form-group label {
        color: var(--dark-text-primary);
    }

    .form-control {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s ease;
        background-color: #fafafa;
        font-weight: 500;
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
        box-shadow: 0 0 0 4px rgba(106, 127, 70, 0.1);
        background-color: white;
        transform: translateY(-1px);
    }

    body.dark-mode .form-control:focus {
        background-color: var(--dark-bg-secondary);
    }

    .form-control:hover {
        border-color: rgba(106, 127, 70, 0.3);
        background-color: white;
    }

    body.dark-mode .form-control:hover {
        background-color: var(--dark-bg-tertiary);
    }

    .form-row {
        display: flex;
        gap: 20px;
    }

    .form-col {
        flex: 1;
    }

    .tier-badge {
        padding: 6px 16px;
        border-radius: 25px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: inline-block;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .tier-bronze {
        background: linear-gradient(135deg, rgba(205, 127, 50, 0.15) 0%, rgba(205, 127, 50, 0.05) 100%);
        color: var(--bronze-tier);
        border: 2px solid rgba(205, 127, 50, 0.3);
    }

    .tier-silver {
        background: linear-gradient(135deg, rgba(192, 192, 192, 0.15) 0%, rgba(192, 192, 192, 0.05) 100%);
        color: var(--silver-tier);
        border: 2px solid rgba(192, 192, 192, 0.3);
    }

    .tier-gold {
        background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 215, 0, 0.05) 100%);
        color: var(--gold-tier);
        border: 2px solid rgba(255, 215, 0, 0.3);
    }

    /* Added styling for new tiers */
    .tier-platinum {
        background: linear-gradient(135deg, rgba(229, 228, 226, 0.15) 0%, rgba(229, 228, 226, 0.05) 100%);
        color: var(--platinum-tier);
        border: 2px solid rgba(229, 228, 226, 0.3);
        position: relative;
    }

    .tier-platinum::before {
        content: '👑';
        margin-right: 5px;
    }

    .tier-diamond {
        background: linear-gradient(135deg, rgba(185, 242, 255, 0.15) 0%, rgba(185, 242, 255, 0.05) 100%);
        color: var(--diamond-tier);
        border: 2px solid rgba(185, 242, 255, 0.3);
        position: relative;
    }

    .tier-diamond::before {
        content: '💎';
        margin-right: 5px;
    }

    .tier-ethereal {
        background: linear-gradient(135deg, rgba(138, 43, 226, 0.15) 0%, rgba(138, 43, 226, 0.05) 100%);
        color: var(--ethereal-tier);
        border: 2px solid rgba(138, 43, 226, 0.3);
        position: relative;
        animation: etherealGlow 2s ease-in-out infinite alternate;
    }

    .tier-ethereal::before {
        content: '⭐';
        margin-right: 5px;
    }

    @keyframes etherealGlow {
        from {
            box-shadow: 0 0 5px rgba(138, 43, 226, 0.3);
        }
        to {
            box-shadow: 0 0 20px rgba(138, 43, 226, 0.6);
        }
    }

    .customer-info {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.08) 0%, rgba(106, 127, 70, 0.03) 100%);
        border: 2px solid rgba(106, 127, 70, 0.15);
        border-radius: 15px;
        padding: 20px;
        margin-top: 15px;
        display: none;
        animation: slideDown 0.4s ease-out;
        box-shadow: 0 4px 15px rgba(106, 127, 70, 0.1);
    }

    body.dark-mode .customer-info {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.12) 0%, rgba(106, 127, 70, 0.06) 100%);
        border-color: var(--dark-border);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .customer-info.show {
        display: block;
    }

    .customer-info h4 {
        color: var(--icon-green);
        margin-bottom: 15px;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
    }

    .customer-info p {
        margin: 8px 0;
        font-size: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 500;
        color: var(--text-dark);
    }

    body.dark-mode .customer-info p {
        color: var(--dark-text-primary);
    }

    .points-display {
        font-weight: 700;
        color: var(--icon-green);
        font-size: 18px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 20px;
        padding: 16px 20px;
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.08) 0%, rgba(106, 127, 70, 0.03) 100%);
        border-radius: 12px;
        border: 2px solid rgba(106, 127, 70, 0.1);
        transition: all 0.3s ease;
    }

    body.dark-mode .checkbox-group {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.12) 0%, rgba(106, 127, 70, 0.06) 100%);
        border-color: var(--dark-border);
    }

    .checkbox-group:hover {
        border-color: rgba(106, 127, 70, 0.2);
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.12) 0%, rgba(106, 127, 70, 0.05) 100%);
    }

    body.dark-mode .checkbox-group:hover {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.15) 0%, rgba(106, 127, 70, 0.08) 100%);
    }

    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: var(--icon-green);
        cursor: pointer;
    }

    .checkbox-group label {
        margin: 0;
        font-size: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--text-dark);
    }

    body.dark-mode .checkbox-group label {
        color: var(--dark-text-primary);
    }

    .table-responsive {
        overflow-x: auto;
        margin-bottom: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    body.dark-mode .table-responsive {
        box-shadow: 0 4px 15px var(--dark-shadow);
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background-color: white;
        border-radius: 12px;
        overflow: hidden;
    }

    body.dark-mode table {
        background-color: var(--dark-bg-secondary);
    }

    table thead {
        position: sticky;
        top: 0;
    }

    th {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.12) 0%, rgba(106, 127, 70, 0.08) 100%);
        font-weight: 700;
        color: var(--icon-green);
        padding: 16px 20px;
        text-align: left;
        border-bottom: 2px solid rgba(106, 127, 70, 0.2);
        font-size: 14px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    body.dark-mode th {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.15) 0%, rgba(106, 127, 70, 0.1) 100%);
    }

    td {
        padding: 18px 20px;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.03);
        font-size: 14px;
        font-weight: 500;
        color: var(--text-dark);
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

    .badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background-color: rgba(112, 139, 76, 0.15);
        color: var(--stock-green);
        border: 1px solid rgba(112, 139, 76, 0.3);
    }

    .badge-warning {
        background-color: rgba(217, 122, 65, 0.15);
        color: var(--sales-orange);
        border: 1px solid rgba(217, 122, 65, 0.3);
    }

    .badge-info {
        background-color: rgba(13, 110, 253, 0.15);
        color: #0d6efd;
        border: 1px solid rgba(13, 110, 253, 0.3);
    }

    .badge-danger {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 16px 28px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        position: relative;
        overflow: hidden;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
        background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(106, 127, 70, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(106, 127, 70, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: var(--text-dark);
        border: 2px solid rgba(0,0,0,0.1);
    }

    body.dark-mode .btn-secondary {
        background: linear-gradient(135deg, var(--dark-bg-tertiary) 0%, var(--dark-bg-secondary) 100%);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    body.dark-mode .btn-secondary:hover {
        background: linear-gradient(135deg, var(--dark-bg-secondary) 0%, var(--dark-bg-tertiary) 100%);
    }

    .btn-orange {
        background: linear-gradient(135deg, var(--sales-orange) 0%, #c46a38 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(217, 122, 65, 0.3);
    }

    .btn-orange:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(217, 122, 65, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(220, 53, 69, 0.4);
    }

    .btn-purple {
        background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(111, 66, 193, 0.3);
    }

    .btn-purple:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(111, 66, 193, 0.4);
    }

    .form-actions {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 2px solid rgba(0,0,0,0.03);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .form-actions {
        border-top-color: var(--dark-border);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-dark);
        opacity: 0.7;
        transition: color 0.3s ease;
    }

    body.dark-mode .empty-state {
        color: var(--dark-text-secondary);
    }

    .empty-state i {
        font-size: 60px;
        color: var(--icon-green);
        margin-bottom: 25px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 16px;
        font-weight: 500;
    }

    .alert {
        padding: 18px 24px;
        margin-bottom: 25px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 2px solid #c3e6cb;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: 2px solid #f5c6cb;
    }

    body.dark-mode .alert-success {
        background: linear-gradient(135deg, rgba(212, 237, 218, 0.2) 0%, rgba(195, 230, 203, 0.15) 100%);
        color: #d4edda;
        border-color: rgba(195, 230, 203, 0.3);
    }

    body.dark-mode .alert-danger {
        background: linear-gradient(135deg, rgba(248, 215, 218, 0.2) 0%, rgba(245, 198, 203, 0.15) 100%);
        color: #f8d7da;
        border-color: rgba(245, 198, 203, 0.3);
    }

    .mobile-menu-toggle {
        display: none;
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--sales-orange) 0%, #c46a38 100%);
        color: white;
        border-radius: 12px;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 100;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(217, 122, 65, 0.3);
        transition: all 0.3s ease;
    }

    .mobile-menu-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(217, 122, 65, 0.4);
    }

    .filters {
        background-color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }

    body.dark-mode .filters {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 6px 20px var(--dark-shadow);
    }

    .input-group {
        display: flex;
        gap: 12px;
    }

    .input-group .form-control {
        flex: 1;
    }

    .input-group .btn {
        flex: 0 0 auto;
        padding: 16px 20px;
    }

    .reset-btn {
        background-color: #f8f9fa;
        color: #495057;
        border: 1px solid #ced4da;
    }

    .reset-btn:hover {
        background-color: #e9ecef;
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

        .transaction-tabs {
            flex-wrap: wrap;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .form-actions {
            flex-direction: column;
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
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <?php echo $t['attendance']; ?></a></li>
            <li><a href="inventory_view.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory_view']; ?></a></li>
            <li><a href="sales_reports.php"><i class="fas fa-chart-pie"></i> <?php echo $t['sales_reports']; ?></a></li>
            <li><a href="customer_management.php"><i class="fas fa-users"></i> <?php echo $t['customer_management']; ?></a></li>
            <li><a href="loyalty_points.php" class="active"><i class="fas fa-award"></i> <?php echo $t['loyalty_points']; ?></a></li>
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
    
    <div class="main-content">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <h1 class="page-title"><?php echo $t['loyalty_points']; ?></h1>
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
                <h2><?php echo $t['welcome']; ?>, <?php echo htmlspecialchars($employee['first_name']); ?>!</h2>
                <p><?php echo $t['enhanced_loyalty_points_management']; ?></p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-award"></i>
            </div>
        </div>
        
        <div class="transaction-tabs">
            <a href="#add-points" class="transaction-tab active">
                <i class="fas fa-plus-circle"></i> <?php echo $t['add_points']; ?>
            </a>
            <a href="#deduct-points" class="transaction-tab">
                <i class="fas fa-minus-circle"></i> <?php echo $t['deduct_points']; ?>
            </a>
            <a href="#manual-adjust" class="transaction-tab">
                <i class="fas fa-edit"></i> <?php echo $t['manual_adjustment']; ?>
            </a>
            <a href="#recent-activities" class="transaction-tab">
                <i class="fas fa-history"></i> <?php echo $t['recent_activities']; ?>
            </a>
        </div>
        
        <div id="add-points" class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-plus-circle"></i> <?php echo $t['add_points']; ?></h2>
            </div>
            
            <form method="POST" id="addPointsForm">
                <div class="form-group">
                    <label><?php echo $t['customer_information']; ?></label>
                    <div class="form-row">
                        <div class="form-col">
                            <input type="text" name="customer_username" id="add_customer_username" placeholder="<?php echo $t['customer_username']; ?>" class="form-control" required>
                        </div>
                        <div class="form-col">
                            <input type="text" name="customer_name" id="add_customer_name" placeholder="<?php echo $t['customer_name']; ?>" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="customer-info" id="add_customer_info">
                        <h4><i class="fas fa-user"></i> <?php echo $t['customer_details']; ?></h4>
                        <p><strong><?php echo $t['name']; ?>:</strong> <span id="add_display_name"></span></p>
                        <p><strong><?php echo $t['current_points']; ?>:</strong> <span id="add_current_points" class="points-display"></span></p>
                        <p><strong><?php echo $t['loyalty_tier']; ?>:</strong> <span id="add_loyalty_tier" class="tier-badge"></span></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['points_to_add']; ?></label>
                            <input type="number" name="points_to_add" placeholder="<?php echo $t['points_to_add']; ?>" class="form-control" min="1" required>
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['reason']; ?></label>
                            <input type="text" name="reason" placeholder="<?php echo $t['reason']; ?>" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" name="generate_receipt" id="add_generate_receipt">
                    <label for="add_generate_receipt">
                        <i class="fas fa-receipt"></i> <?php echo $t['generate_pdf_receipt']; ?>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary"><?php echo $t['clear_form']; ?></button>
                    <button type="submit" name="add_points" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?php echo $t['add_points']; ?>
                    </button>
                </div>
            </form>
        </div>
        
        <div id="deduct-points" class="dashboard-card" style="display: none;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-minus-circle"></i> <?php echo $t['deduct_points']; ?></h2>
            </div>
            
            <form method="POST" id="deductPointsForm">
                <div class="form-group">
                    <label><?php echo $t['customer_information']; ?></label>
                    <div class="form-row">
                        <div class="form-col">
                            <input type="text" name="customer_username" id="deduct_customer_username" placeholder="<?php echo $t['customer_username']; ?>" class="form-control" required>
                        </div>
                        <div class="form-col">
                            <input type="text" name="customer_name" id="deduct_customer_name" placeholder="<?php echo $t['customer_name']; ?>" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="customer-info" id="deduct_customer_info">
                        <h4><i class="fas fa-user"></i> <?php echo $t['customer_details']; ?></h4>
                        <p><strong><?php echo $t['name']; ?>:</strong> <span id="deduct_display_name"></span></p>
                        <p><strong><?php echo $t['current_points']; ?>:</strong> <span id="deduct_current_points" class="points-display"></span></p>
                        <p><strong><?php echo $t['loyalty_tier']; ?>:</strong> <span id="deduct_loyalty_tier" class="tier-badge"></span></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['points_to_deduct']; ?></label>
                            <input type="number" name="points_to_deduct" placeholder="<?php echo $t['points_to_deduct']; ?>" class="form-control" min="1" required>
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['reason']; ?></label>
                            <input type="text" name="reason" placeholder="<?php echo $t['reason']; ?>" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" name="generate_receipt" id="deduct_generate_receipt">
                    <label for="deduct_generate_receipt">
                        <i class="fas fa-receipt"></i> <?php echo $t['generate_pdf_receipt']; ?>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary"><?php echo $t['clear_form']; ?></button>
                    <button type="submit" name="deduct_points" class="btn btn-danger">
                        <i class="fas fa-minus"></i> <?php echo $t['deduct_points']; ?>
                    </button>
                </div>
            </form>
        </div>
        
        <div id="manual-adjust" class="dashboard-card" style="display: none;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-edit"></i> <?php echo $t['manual_adjustment']; ?></h2>
            </div>
            
            <form method="POST" id="manualAdjustForm">
                <div class="form-group">
                    <label><?php echo $t['customer_information']; ?></label>
                    <div class="form-row">
                        <div class="form-col">
                            <input type="text" name="customer_username" id="manual_customer_username" placeholder="<?php echo $t['customer_username']; ?>" class="form-control" required>
                        </div>
                        <div class="form-col">
                            <input type="text" name="customer_name" id="manual_customer_name" placeholder="<?php echo $t['customer_name']; ?>" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="customer-info" id="manual_customer_info">
                        <h4><i class="fas fa-user"></i> <?php echo $t['customer_details']; ?></h4>
                        <p><strong><?php echo $t['name']; ?>:</strong> <span id="manual_display_name"></span></p>
                        <p><strong><?php echo $t['current_points']; ?>:</strong> <span id="manual_current_points" class="points-display"></span></p>
                        <p><strong><?php echo $t['loyalty_tier']; ?>:</strong> <span id="manual_loyalty_tier" class="tier-badge"></span></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['adjustment_points']; ?></label>
                            <input type="number" name="adjustment_points" placeholder="<?php echo $t['adjustment_points']; ?>" class="form-control" required>
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                <?php echo $t['use_positive_negative']; ?>
                            </small>
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['reason_for_adjustment']; ?></label>
                            <input type="text" name="reason" placeholder="<?php echo $t['reason_for_adjustment']; ?>" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" name="generate_receipt" id="manual_generate_receipt">
                    <label for="manual_generate_receipt">
                        <i class="fas fa-receipt"></i> <?php echo $t['generate_pdf_receipt']; ?>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary"><?php echo $t['clear_form']; ?></button>
                    <button type="submit" name="manual_adjust" class="btn btn-purple">
                        <i class="fas fa-edit"></i> <?php echo $t['apply_adjustment']; ?>
                    </button>
                </div>
            </form>
        </div>
        
        <div id="recent-activities" class="dashboard-card" style="display: none;">
            <div class="filters" style="margin-bottom: 20px;">
                <form method="GET" id="filterForm">
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
                            <label><?php echo $t['search']; ?></label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="<?php echo $t['search']; ?>..." value="<?php echo $_GET['search'] ?? ''; ?>">
                                <button type="submit" class="btn btn-primary"><?php echo $t['filter']; ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> <?php echo $t['recent_activities']; ?></h2>
            </div>
            
            <?php if (!empty($recent_activities)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['date_time']; ?></th>
                                <th><?php echo $t['customer']; ?></th>
                                <th><?php echo $t['points_change']; ?></th>
                                <th><?php echo $t['previous_points']; ?></th>
                                <th><?php echo $t['new_points']; ?></th>
                                <th><?php echo $t['tier']; ?></th>
                                <th><?php echo $t['reason']; ?></th>
                                <th><?php echo $t['receipt']; ?></th>
                                <th><?php echo $t['processed_by']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td><?php 
                                        date_default_timezone_set('Asia/Manila');
                                        echo date('M j, Y h:i A', strtotime($activity['created_at'])); 
                                    ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($activity['customer_name']); ?></strong><br>
                                        <small>@<?php echo htmlspecialchars($activity['customer_username']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($activity['points_change'] > 0): ?>
                                            <span class="badge badge-success">+<?php echo $activity['points_change']; ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><?php echo $activity['points_change']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo number_format($activity['previous_points']); ?></td>
                                    <td><?php echo number_format($activity['new_points']); ?></td>
                                    <td>
                                        <span class="tier-badge tier-<?php echo $activity['loyalty_tier']; ?>">
                                            <?php echo strtoupper($activity['loyalty_tier']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['reason']); ?></td>
                                    <td>
                                        <?php if (!empty($activity['receipt_path']) && file_exists($activity['receipt_path'])): ?>
                                            <a href="<?php echo $activity['receipt_path']; ?>" target="_blank" class="btn btn-secondary">
                                                <i class="fas fa-receipt"></i> <?php echo $t['view']; ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 12px;"><?php echo $t['not_generated']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['employee_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-award"></i>
                    <p><?php echo $t['no_recent_point_activities']; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($show_modal): ?>
    <div id="successModal" class="modal" style="display: flex;">
        <div class="modal-content">
            <button id="closeModal">&times;</button>
            <div style="text-align: center; margin-bottom: 20px;">
                <i class="fas fa-check-circle" style="font-size: 60px; color: #4CAF50;"></i>
                <h2 style="margin-top: 15px; color: #4CAF50;"><?php echo $t['transaction_successful']; ?></h2>
                <p id="transactionMessage" style="margin-bottom: 20px;">
                    <?php echo htmlspecialchars($transaction_type); ?> <?php echo $t['transaction_successful']; ?>
                </p>
            </div>
            <div id="receiptPreview" style="margin-bottom: 20px;">
                <iframe src="<?php echo $receipt_path; ?>"></iframe>
            </div>
            <div class="modal-buttons">
                <button id="printReceipt" class="btn btn-primary">
                    <i class="fas fa-print"></i> <?php echo $t['print_receipt']; ?>
                </button>
                <button id="closeModalBtn" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?php echo $t['close']; ?>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
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

        document.querySelectorAll('.transaction-tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                document.querySelectorAll('.transaction-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const target = this.getAttribute('href').substring(1);
                document.querySelectorAll('.dashboard-card').forEach(card => {
                    card.style.display = 'none';
                });
                document.getElementById(target).style.display = 'block';
            });
        });

        function lookupCustomer(username, prefix) {
            if (username.length < 2) {
                document.getElementById(prefix + '_customer_info').classList.remove('show');
                document.getElementById(prefix + '_customer_name').value = '';
                return;
            }

            $.ajax({
                url: 'customer_lookup.php',
                method: 'POST',
                data: { username: username },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        document.getElementById(prefix + '_customer_name').value = response.data.full_name;
                        document.getElementById(prefix + '_display_name').textContent = response.data.full_name;
                        document.getElementById(prefix + '_current_points').textContent = response.data.loyalty_points + ' pts';
                        
                        const tierBadge = document.getElementById(prefix + '_loyalty_tier');
                        tierBadge.textContent = response.data.loyalty_tier.toUpperCase();
                        tierBadge.className = 'tier-badge tier-' + response.data.loyalty_tier;
                        
                        document.getElementById(prefix + '_customer_info').classList.add('show');
                    } else {
                        document.getElementById(prefix + '_customer_info').classList.remove('show');
                        document.getElementById(prefix + '_customer_name').value = '';
                    }
                },
                error: function() {
                    document.getElementById(prefix + '_customer_info').classList.remove('show');
                    document.getElementById(prefix + '_customer_name').value = '';
                }
            });
        }

        document.getElementById('add_customer_username').addEventListener('input', function() {
            lookupCustomer(this.value, 'add');
        });

        document.getElementById('deduct_customer_username').addEventListener('input', function() {
            lookupCustomer(this.value, 'deduct');
        });

        document.getElementById('manual_customer_username').addEventListener('input', function() {
            lookupCustomer(this.value, 'manual');
        });

        document.getElementById('addPointsForm').addEventListener('reset', function() {
            document.getElementById('add_customer_info').classList.remove('show');
        });

        document.getElementById('deductPointsForm').addEventListener('reset', function() {
            document.getElementById('deduct_customer_info').classList.remove('show');
        });

        document.getElementById('manualAdjustForm').addEventListener('reset', function() {
            document.getElementById('manual_customer_info').classList.remove('show');
        });

        document.getElementById('closeModal')?.addEventListener('click', function() {
            document.getElementById('successModal').style.display = 'none';
        });
        
        document.getElementById('closeModalBtn')?.addEventListener('click', function() {
            document.getElementById('successModal').style.display = 'none';
        });

        document.getElementById('printReceipt')?.addEventListener('click', function() {
            const iframe = document.querySelector('#receiptPreview iframe');
            iframe.contentWindow.print();
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const tab = document.querySelector(`.transaction-tab[href="${window.location.hash}"]`);
                if (tab) {
                    tab.click();
                }
            }
        });
    </script>
</body>
</html>