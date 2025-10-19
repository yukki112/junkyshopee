<?php
session_start();
require_once 'db_connection.php';
require_once 'fpdf/fpdf.php'; // Include FPDF library for receipt generation

// Authentication check
if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

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
        'transaction_management' => 'Transaction Management',
        'record_purchase' => 'Record Purchase',
        'record_sale' => 'Record Sale',
        'recent_transactions' => 'Recent Transactions',
        'seller_information' => 'Seller Information',
        'username_if_registered' => 'Username (if registered)',
        'seller_name' => 'Seller Name',
        'contact_number' => 'Contact Number',
        'materials_purchased' => 'Materials Purchased',
        'select_material' => 'Select Material',
        'quantity_kg' => 'Quantity (kg)',
        'total' => 'Total',
        'add_another_material' => 'Add Another Material',
        'total_amount' => 'Total Amount',
        'total_weight' => 'Total Weight (kg)',
        'points_earned' => 'Points Earned',
        'clear_form' => 'Clear Form',
        'record_purchase_button' => 'Record Purchase',
        'buyer_information' => 'Buyer Information',
        'buyer_name' => 'Buyer Name',
        'materials_sold' => 'Materials Sold',
        'record_sale_button' => 'Record Sale',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
        'search' => 'Search',
        'filter' => 'Filter',
        'transaction_id' => 'Transaction ID',
        'datetime' => 'Date/Time',
        'amount' => 'Amount',
        'receipt' => 'Receipt',
        'status' => 'Status',
        'no_recent_transactions' => 'No recent transactions found',
        'transaction_successful' => 'Transaction Successful!',
        'walkin_success' => 'Walk-in transaction has been recorded successfully!',
        'purchase_success' => 'Purchase from seller has been recorded successfully!',
        'sale_success' => 'Sale to buyer has been recorded successfully!',
        'print_receipt' => 'Print Receipt',
        'close' => 'Close',
        'previous' => 'Previous',
        'next' => 'Next',
        'page' => 'Page',
        'of' => 'of',
        'view' => 'View',
        'not_available' => 'Not Available'
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
        'transaction_management' => 'Pamamahala ng Transaksyon',
        'record_purchase' => 'Itala ang Pagbili',
        'record_sale' => 'Itala ang Pagbenta',
        'recent_transactions' => 'Mga Kamakailang Transaksyon',
        'seller_information' => 'Impormasyon ng Nagbebenta',
        'username_if_registered' => 'Username (kung rehistrado)',
        'seller_name' => 'Pangalan ng Nagbebenta',
        'contact_number' => 'Numero ng Kontak',
        'materials_purchased' => 'Mga Materyal na Binili',
        'select_material' => 'Pumili ng Materyal',
        'quantity_kg' => 'Dami (kg)',
        'total' => 'Kabuuan',
        'add_another_material' => 'Magdagdag ng Iba Pang Materyal',
        'total_amount' => 'Kabuuang Halaga',
        'total_weight' => 'Kabuuang Timbang (kg)',
        'points_earned' => 'Mga Nakuha na Puntos',
        'clear_form' => 'I-clear ang Form',
        'record_purchase_button' => 'Itala ang Pagbili',
        'buyer_information' => 'Impormasyon ng Bumibili',
        'buyer_name' => 'Pangalan ng Bumibili',
        'materials_sold' => 'Mga Materyal na Naibenta',
        'record_sale_button' => 'Itala ang Pagbenta',
        'date_from' => 'Petsa Mula',
        'date_to' => 'Petsa Hanggang',
        'search' => 'Maghanap',
        'filter' => 'Filter',
        'transaction_id' => 'ID ng Transaksyon',
        'datetime' => 'Petsa/Oras',
        'amount' => 'Halaga',
        'receipt' => 'Resibo',
        'status' => 'Katayuan',
        'no_recent_transactions' => 'Walang nakitang mga kamakailang transaksyon',
        'transaction_successful' => 'Matagumpay ang Transaksyon!',
        'walkin_success' => 'Matagumpay na naitala ang walk-in na transaksyon!',
        'purchase_success' => 'Matagumpay na naitala ang pagbili mula sa nagbebenta!',
        'sale_success' => 'Matagumpay na naitala ang pagbenta sa bumibili!',
        'print_receipt' => 'I-print ang Resibo',
        'close' => 'Isara',
        'previous' => 'Nakaraan',
        'next' => 'Susunod',
        'page' => 'Pahina',
        'of' => 'ng',
        'view' => 'Tingnan',
        'not_available' => 'Hindi Available'
    ]
];

$t = $translations[$language];

if (isset($_GET['action']) && $_GET['action'] === 'get_user_data' && isset($_GET['username'])) {
    header('Content-Type: application/json');
    
    $username = sanitizeInput($_GET['username']);
    
    $query = "SELECT id, first_name, last_name, phone, email FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $user['id'],
                'name' => trim($user['first_name'] . ' ' . $user['last_name']),
                'contact' => $user['phone'] ?: $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'phone' => $user['phone'],
                'email' => $user['email']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Username not found'
        ]);
    }
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

// Helper function to format item details as readable string (UPDATED)
function formatItemDetails($items) {
    $formatted = [];
    foreach ($items as $item) {
        $formatted[] = sprintf(
            "%s (%s) (%.2fkg) = %.2f",
            $item['material'],
            $item['material'], // You might want to add a description field if available
            $item['quantity'],
            $item['total']
        );
    }
    return implode(' | ', $formatted);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Record purchase from seller
    if (isset($_POST['record_purchase'])) {
        $conn->begin_transaction();
        try {
            // Validate and sanitize inputs
            $seller_username = isset($_POST['seller_username']) ? sanitizeInput($_POST['seller_username']) : null;
            $seller_name = sanitizeInput($_POST['seller_name']);
            $seller_contact = sanitizeInput($_POST['seller_contact']);
            
            // Check if username exists and get user ID
            $seller_id = null;
            if ($seller_username) {
                $check_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $check_user->bind_param("s", $seller_username);
                $check_user->execute();
                $check_user->store_result();
                
                if ($check_user->num_rows > 0) {
                    $check_user->bind_result($seller_id);
                    $check_user->fetch();
                } else {
                    throw new Exception("Username not found");
                }
                $check_user->close();
            }
                
            // Create transaction ID
            $transaction_id = 'TXN-' . date('Ymd') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $transaction_date = date('Y-m-d');
            $transaction_time = date('H:i:s');
            
            // Process materials
            $total_amount = 0;
            $total_weight = 0; // Added to track total weight for points
            $item_details = [];
            $materials_processed = [];
            
            foreach ($_POST['material_id'] as $index => $material_id) {
                $material_id = intval($material_id);
                $quantity = floatval($_POST['quantity'][$index]);
                
                if ($quantity <= 0) {
                    throw new Exception("Quantity must be greater than 0");
                }
                
                // Get material details
                $material_query = "SELECT * FROM materials WHERE id = ?";
                $material_stmt = $conn->prepare($material_query);
                $material_stmt->bind_param("i", $material_id);
                $material_stmt->execute();
                $material_result = $material_stmt->get_result();
                $material = $material_result->fetch_assoc();
                $material_stmt->close();
                
                if (!$material) {
                    throw new Exception("Invalid material selected");
                }
                
                // Check if inventory item exists, if not create it
                $inv_query = "SELECT id FROM inventory_items WHERE material_id = ? LIMIT 1";
                $inv_stmt = $conn->prepare($inv_query);
                $inv_stmt->bind_param("i", $material_id);
                $inv_stmt->execute();
                $inv_result = $inv_stmt->get_result();
                $inventory_item = $inv_result->fetch_assoc();
                $inv_stmt->close();
                
                if (!$inventory_item) {
                    // Create new inventory item if it doesn't exist
                    $create_query = "INSERT INTO inventory_items 
                                   (material_id, category_id, item_name, current_stock, unit, created_at, updated_at) 
                                   VALUES (?, 1, ?, 0, 'kg', NOW(), NOW())";
                    $create_stmt = $conn->prepare($create_query);
                    $item_name = $material['material_option'] . " Scrap";
                    $create_stmt->bind_param("is", $material_id, $item_name);
                    $create_stmt->execute();
                    $create_stmt->close();
                    $inventory_id = $conn->insert_id;
                } else {
                    $inventory_id = $inventory_item['id'];
                }
                
                $item_value = $quantity * $material['unit_price'];
                $total_amount += $item_value;
                $total_weight += $quantity; // Add to total weight for points
                
                $item_details[] = [
                    'material' => $material['material_option'],
                    'quantity' => $quantity,
                    'unit' => 'kg',
                    'price_per_kg' => $material['unit_price'],
                    'total' => $item_value
                ];
                
                // Track for inventory update
                $materials_processed[] = [
                    'id' => $material_id,
                    'quantity' => $quantity,
                    'name' => $material['material_option']
                ];
                
                // Update inventory - FIXED: Use employee_id instead of user_id
                updateInventory($conn, $material_id, $quantity, 'addition', $employee_id, "Purchase from $seller_name");
            }
            
            // Format item details as readable string instead of JSON (UPDATED FORMAT)
            $item_details_string = formatItemDetails($item_details);
            
            // Insert transaction
            $stmt = $conn->prepare("INSERT INTO transactions 
                                  (transaction_id, user_id, created_by, name, transaction_type, type,
                                  transaction_date, transaction_time, item_details, amount, status, points_earned) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $transaction_type = 'Walk-in';
            $type = 'Purchase'; // This is the transaction type for database
            $status = 'Completed';
            $points_earned = $seller_id ? floor($total_weight) : 0; // 1 point per kg
            
            $stmt->bind_param(
                "siissssssdsi", 
                $transaction_id,
                $seller_id,
                $employee_id,
                $seller_name,
                $transaction_type,
                $type,
                $transaction_date,
                $transaction_time,
                $item_details_string,
                $total_amount,
                $status,
                $points_earned
            );
            $stmt->execute();
            $stmt->close();
            
            // Update seller's loyalty points if registered
            if ($seller_id) {
                $stmt = $conn->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?");
                $stmt->bind_param("ii", $points_earned, $seller_id);
                $stmt->execute();
                $stmt->close();
            }
            
            // Generate receipt
            $receipt_path = generateReceipt(
                $transaction_id,
                $transaction_date,
                $transaction_time,
                $seller_name,
                $seller_contact,
                $employee_name,
                $item_details,
                $total_amount,
                'Purchase' // This is for the receipt title, not the transaction type in DB
            );
            
            $conn->commit();
            
            // Set session variables for receipt download and modal
            $_SESSION['receipt_path'] = $receipt_path;
            $_SESSION['transaction_id'] = $transaction_id;
            $_SESSION['show_success_modal'] = true;
            $_SESSION['transaction_type'] = 'Purchase';
            
            // Redirect to prevent form resubmission
            header("Location: transaction_logging.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Purchase recording failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to record purchase: " . $e->getMessage();
        }
    }
    
    // Record sale to buyer
    if (isset($_POST['record_sale'])) {
        $conn->begin_transaction();
        try {
            // Validate and sanitize inputs
            $buyer_username = isset($_POST['buyer_username']) ? sanitizeInput($_POST['buyer_username']) : null;
            $buyer_name = sanitizeInput($_POST['buyer_name']);
            $buyer_contact = sanitizeInput($_POST['buyer_contact']);
            
            // Check if username exists and get user ID
            $buyer_id = null;
            if ($buyer_username) {
                $check_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $check_user->bind_param("s", $buyer_username);
                $check_user->execute();
                $check_user->store_result();
                
                if ($check_user->num_rows > 0) {
                    $check_user->bind_result($buyer_id);
                    $check_user->fetch();
                } else {
                    throw new Exception("Username not found");
                }
                $check_user->close();
            }
            
            // Validate materials
            if (!isset($_POST['material_id']) || !is_array($_POST['material_id'])) {
                throw new Exception("Please add at least one material");
            }
            
            // Create transaction ID
            $transaction_id = 'TXN-' . date('Ymd') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $transaction_date = date('Y-m-d');
            $transaction_time = date('H:i:s');
            
            // Process materials
            $total_amount = 0;
            $total_weight = 0;
            $item_details = [];
            $materials_processed = [];
            
            foreach ($_POST['material_id'] as $index => $material_id) {
                $material_id = intval($material_id);
                $quantity = floatval($_POST['quantity'][$index]);
                
                if ($quantity <= 0) {
                    throw new Exception("Quantity must be greater than 0");
                }
                
                // Get material details
                $material_query = "SELECT * FROM materials WHERE id = ?";
                $material_stmt = $conn->prepare($material_query);
                $material_stmt->bind_param("i", $material_id);
                $material_stmt->execute();
                $material_result = $material_stmt->get_result();
                $material = $material_result->fetch_assoc();
                $material_stmt->close();
                
                if (!$material) {
                    throw new Exception("Invalid material selected");
                }
                
                // Check if inventory item exists, if not create it
                $inv_query = "SELECT id, current_stock FROM inventory_items WHERE material_id = ? LIMIT 1";
                $inv_stmt = $conn->prepare($inv_query);
                $inv_stmt->bind_param("i", $material_id);
                $inv_stmt->execute();
                $inv_result = $inv_stmt->get_result();
                $inventory_item = $inv_result->fetch_assoc();
                $inv_stmt->close();
                
                if (!$inventory_item) {
                    throw new Exception("Inventory item not found for material ID: $material_id");
                }
                
                // Check if sufficient stock
                if ($inventory_item['current_stock'] < $quantity) {
                    throw new Exception("Insufficient stock for " . $material['material_option']);
                }
                
                $item_value = $quantity * $material['unit_price'];
                $total_amount += $item_value;
                $total_weight += $quantity;
                
                $item_details[] = [
                    'material' => $material['material_option'],
                    'quantity' => $quantity,
                    'unit' => 'kg',
                    'price_per_kg' => $material['unit_price'],
                    'total' => $item_value
                ];
                
                // Track for inventory update
                $materials_processed[] = [
                    'id' => $material_id,
                    'quantity' => $quantity,
                    'name' => $material['material_option']
                ];
                
                // Update inventory - FIXED: Use employee_id instead of user_id
                updateInventory($conn, $material_id, $quantity, 'deduction', $employee_id, "Sale to $buyer_name");
            }
            
            // Calculate points (1 point per kg)
            $points_earned = $buyer_id ? floor($total_weight) : 0;
            
            // Format item details as readable string instead of JSON (UPDATED FORMAT)
            $item_details_string = formatItemDetails($item_details);
            
            // Insert transaction - modified to handle NULL user_id
            $stmt = $conn->prepare("INSERT INTO transactions 
                                  (transaction_id, user_id, created_by, name, transaction_type, type,
                                  transaction_date, transaction_time, item_details, amount, 
                                  status, points_earned) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $transaction_type = 'Walk-in';
            $type = 'Sale'; // This is the transaction type for database
            $status = 'Completed';
            
            $stmt->bind_param(
                "siissssssdsi", 
                $transaction_id,
                $buyer_id,  // This can be NULL for non-registered customers
                $employee_id,
                $buyer_name,
                $transaction_type,
                $type,
                $transaction_date,
                $transaction_time,
                $item_details_string,
                $total_amount,
                $status,
                $points_earned
            );
            $stmt->execute();
            $stmt->close();
            
            // Update buyer's loyalty points if registered
            if ($buyer_id) {
                $stmt = $conn->prepare("UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?");
                $stmt->bind_param("ii", $points_earned, $buyer_id);
                $stmt->execute();
                $stmt->close();
            }
            
            // Generate receipt
            $receipt_path = generateReceipt(
                $transaction_id,
                $transaction_date,
                $transaction_time,
                $buyer_name,
                $buyer_contact,
                $employee_name,
                $item_details,
                $total_amount,
                'Sale' // This is for the receipt title, not the transaction type in DB
            );
            
            $conn->commit();
            
            // Set session variables for receipt download and modal
            $_SESSION['receipt_path'] = $receipt_path;
            $_SESSION['transaction_id'] = $transaction_id;
            $_SESSION['show_success_modal'] = true;
            $_SESSION['transaction_type'] = 'Sale';
            
            // Redirect to prevent form resubmission
            header("Location: transaction_logging.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Sale recording failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to record sale: " . $e->getMessage();
        }
    }
    
    header("Location: transaction_logging.php");
    exit();
}

// Helper functions
function sanitizeInput($data) {
    global $conn;
    return $conn->real_escape_string(strip_tags(trim($data)));
}

function updateInventory($conn, $material_id, $quantity, $action, $employee_id, $reason) {
    // Get inventory item
    $query = "SELECT * FROM inventory_items WHERE material_id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
    
    if (!$item) {
        throw new Exception("Inventory item not found for material ID: $material_id");
    }
    
    // Calculate new stock
    $previous_stock = $item['current_stock'];
    $new_stock = $action === 'addition' ? $previous_stock + $quantity : $previous_stock - $quantity;
    
    if ($new_stock < 0) {
        throw new Exception("Insufficient stock for material ID: $material_id");
    }
    
    // Update inventory
    $query = "UPDATE inventory_items SET current_stock = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("di", $new_stock, $item['id']);
    $stmt->execute();
    $stmt->close();
    
    // Log inventory change - FIXED: Use employee_id instead of user_id
    // First, we need to find a valid user_id to use for the foreign key constraint
    // Let's use the admin user (id=43) or find another valid user
    $user_query = "SELECT id FROM users WHERE is_admin = 1 LIMIT 1";
    $user_result = $conn->query($user_query);
    $default_user_id = 43; // Fallback to admin user
    
    if ($user_result && $user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $default_user_id = $user_row['id'];
    }
    
    $query = "INSERT INTO inventory_logs 
             (inventory_item_id, user_id, action_type, quantity_change, 
             previous_stock, new_stock, reason, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "iisddds", 
        $item['id'],
        $default_user_id, // Use a valid user_id instead of employee_id
        $action,
        $quantity,
        $previous_stock,
        $new_stock,
        $reason
    );
    $stmt->execute();
    $stmt->close();
}

function generateReceipt($transaction_id, $date, $time, $customer_name, $contact, $employee_name, $items, $total_amount, $type) {
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
    $pdf->Cell(0, 15, strtoupper($type) . ' RECEIPT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A'); 
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Transaction ID:', 0, 0);
    $pdf->Cell(0, 7, $transaction_id, 0, 1);
    $pdf->Cell(40, 7, 'Date/Time:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
     
    // Add username if available
    if (!empty($_POST['seller_username'] ?? $_POST['buyer_username'] ?? '')) {
        $pdf->Cell(40, 7, 'Username:', 0, 0);
        $pdf->Cell(0, 7, $_POST['seller_username'] ?? $_POST['buyer_username'] ?? '', 0, 1);
    }
    
    $pdf->Cell(40, 7, $type === 'Purchase' ? 'Seller:' : 'Buyer:', 0, 0);
    $pdf->Cell(0, 7, $customer_name, 0, 1);
    $pdf->Cell(40, 7, 'Contact:', 0, 0);
    $pdf->Cell(0, 7, $contact, 0, 1);
    $pdf->Cell(40, 7, 'Processed by:', 0, 0);
    $pdf->Cell(0, 7, $employee_name, 0, 1);

    // Items table header
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(90, 7, 'Material', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Quantity', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Unit Price', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Total', 1, 1, 'C');
    
    // Items
    $pdf->SetFont('Arial', '', 11);
    foreach ($items as $item) {
        $pdf->Cell(90, 7, $item['material'], 1, 0);
        $pdf->Cell(30, 7, number_format($item['quantity'], 2) . ' kg', 1, 0, 'R');
        $pdf->Cell(30, 7, '' . number_format($item['price_per_kg'], 2), 1, 0, 'R');
        $pdf->Cell(40, 7, '' . number_format($item['total'], 2), 1, 1, 'R');
    }
    
    // Total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(150, 7, 'TOTAL AMOUNT:', 1, 0, 'R');
    $pdf->Cell(40, 7, '' . number_format($total_amount, 2), 1, 1, 'R');
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Thank you for choosing JunkValue!', 0, 1, 'C');
    $pdf->Cell(0, 7, 'Bring this receipt for any inquiries or returns', 0, 1, 'C');
    
    // Save the PDF to a temporary file
    $receipt_dir = 'receipts/';
    if (!file_exists($receipt_dir)) {
        mkdir($receipt_dir, 0755, true); 
    }
    
    $filename = $receipt_dir . $transaction_id . '.pdf';
    $pdf->Output($filename, 'F');
    
    return $filename;
}

// Get materials for dropdowns
$materials = [];
$query = "SELECT * FROM materials WHERE status = 'active' ORDER BY material_option";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
}

// Get recent transactions with filters and pagination
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$type = $_GET['type'] ?? null;
$search = $_GET['search'] ?? null;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as user_name, u.username as user_username,
          CONCAT('receipts/', t.transaction_id, '.pdf') as receipt_path
         FROM transactions t 
         LEFT JOIN users u ON t.user_id = u.id 
         WHERE t.created_by = ?";
         
$params = [$employee_id];
$types = "i";

// Add filters to query
if ($date_from) {
    $query .= " AND t.transaction_date >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $query .= " AND t.transaction_date <= ?";
    $params[] = $date_to;
    $types .= "s";
}

if ($type) {
    $query .= " AND t.transaction_type = ?";
    $params[] = $type;
    $types .= "s";
}
if ($search) {
    $query .= " AND (t.transaction_id LIKE ? OR t.name LIKE ? OR u.username LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$query .= " ORDER BY t.transaction_date DESC, t.transaction_time DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$recent_transactions = [];
while ($row = $result->fetch_assoc()) {
    $recent_transactions[] = $row;
}
$stmt->close();

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.created_by = ?";
$count_params = [$employee_id];
$count_types = "i";

if ($date_from) {
    $count_query .= " AND t.transaction_date >= ?";
    $count_params[] = $date_from;
    $count_types .= "s";
}

if ($date_to) {
    $count_query .= " AND t.transaction_date <= ?";
    $count_params[] = $date_to;
    $count_types .= "s";
}

if ($type) {
    $count_query .= " AND t.transaction_type = ?";
    $count_params[] = $type;
    $count_types .= "s";
}
if ($search) {
    $count_query .= " AND (t.transaction_id LIKE ? OR t.name LIKE ? OR u.username LIKE ?)";
    $search_term = "%$search%";
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_types .= "sss";
}

$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param($count_types, ...$count_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_count = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_count / $limit);

// Display success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Check if we should show the modal (only if this is a fresh redirect after transaction)
$show_modal = $_SESSION['show_success_modal'] ?? false;
$transaction_type = $_SESSION['transaction_type'] ?? '';
$receipt_path = $_SESSION['receipt_path'] ?? '';

// Clear the modal flags immediately after checking
unset($_SESSION['show_success_modal'], $_SESSION['transaction_type'], $_SESSION['receipt_path']);

// Determine active tab - check for tab parameter or default to recent-transactions if pagination/filter is active
$active_tab = $_GET['tab'] ?? 'record-purchase';
if (isset($_GET['page']) || isset($_GET['date_from']) || isset($_GET['date_to']) || isset($_GET['search'])) {
    $active_tab = 'recent-transactions';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Employee Transactions</title>
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

    /* Added styles for username status indicators */
    .username-status {
        font-size: 12px;
        margin-top: 5px;
        padding: 3px 8px;
        border-radius: 4px;
        display: none;
    }

    .username-status.found {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        display: block;
    }

    body.dark-mode .username-status.found {
        background-color: rgba(212, 237, 218, 0.2);
        color: #d4edda;
        border-color: #155724;
    }

    .username-status.not-found {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        display: block;
    }

    body.dark-mode .username-status.not-found {
        background-color: rgba(248, 215, 218, 0.2);
        color: #f8d7da;
        border-color: #721c24;
    }

    .username-status.loading {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
        display: block;
    }

    body.dark-mode .username-status.loading {
        background-color: rgba(255, 243, 205, 0.2);
        color: #ffeaa7;
        border-color: #856404;
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
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        border-radius: 15px;
        padding: 30px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease-out;
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-content button#closeModal {
        position: absolute;
        top: 15px;
        right: 20px;
        background: none;
        border: none;
        font-size: 24px;
        color: #999;
        cursor: pointer;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    body.dark-mode .modal-content button#closeModal {
        color: var(--dark-text-secondary);
    }

    .modal-content button#closeModal:hover {
        background-color: #f5f5f5;
        color: #333;
    }

    body.dark-mode .modal-content button#closeModal:hover {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
    }

    #receiptPreview {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        background-color: #f9f9f9;
    }

    body.dark-mode #receiptPreview {
        border-color: var(--dark-border);
        background-color: var(--dark-bg-tertiary);
    }

    #receiptPreview iframe {
        width: 100%;
        height: 400px;
        border: none;
    }

    .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .modal-buttons .btn {
        min-width: 120px;
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

    /* Transaction Tabs */
    .transaction-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .transaction-tab {
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

    body.dark-mode .transaction-tab {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .transaction-tab:hover {
        background-color: rgba(106, 127, 70, 0.1);
        transform: translateY(-2px);
    }

    body.dark-mode .transaction-tab:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .transaction-tab.active {
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

    /* Material Items */
    .material-item {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
        padding: 10px;
        background-color: rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    body.dark-mode .material-item {
        background-color: rgba(255,255,255,0.05);
    }

    .remove-item {
        color: #dc3545;
        cursor: pointer;
        font-size: 18px;
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
        background-color: rgba(106, 127, 70, 0.2);
        color: var(--icon-green);
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
        background-color: rgba(112, 139, 76, 0.3);
        color: #a8d5a2;
    }

    .badge-warning {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    body.dark-mode .badge-warning {
        background-color: rgba(217, 122, 65, 0.3);
        color: #f0b37a;
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

    body.dark-mode .alert-success {
        background-color: rgba(212, 237, 218, 0.2);
        color: #d4edda;
        border-color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    body.dark-mode .alert-danger {
        background-color: rgba(248, 215, 218, 0.2);
        color: #f8d7da;
        border-color: #721c24;
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
        background-color: var(--dark-bg-primary);
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        padding: 15px 0;
    }

    .pagination-btn {
        padding: 8px 16px;
        background-color: var(--icon-green);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .pagination-btn:hover:not(:disabled) {
        background-color: var(--stock-green);
        transform: translateY(-2px);
    }

    .pagination-btn:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
        transform: none;
    }

    body.dark-mode .pagination-btn:disabled {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-secondary);
    }

    .page-info {
        font-size: 14px;
        color: var(--text-dark);
        font-weight: 500;
    }

    body.dark-mode .page-info {
        color: var(--dark-text-primary);
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

        .transaction-tabs {
            flex-wrap: wrap;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .material-item {
            grid-template-columns: 1fr 1fr auto;
        }

        .material-item input[type="text"] {
            grid-column: span 2;
        }

        .modal-content {
            width: 95%;
            padding: 20px;
        }

        .modal-buttons {
            flex-direction: column;
        }

        .modal-buttons .btn {
            width: 100%;
        }

        .header-controls {
            flex-direction: column;
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

        .pagination {
            flex-wrap: wrap;
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
            <li><a href="transaction_logging.php"class="active"><i class="fas fa-cash-register"></i> <?php echo $t['transaction_logging']; ?></a></li>
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <?php echo $t['attendance']; ?></a></li>
            <li><a href="inventory_view.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory_view']; ?></a></li>
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
            <h1 class="page-title"><?php echo $t['transaction_logging']; ?></h1>
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
                <h2><?php echo $t['transaction_management']; ?></h2>
                <p>Record purchases from sellers and sales to buyers. Keep track of all transactions and manage inventory levels.</p>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
        </div>
        
       
        <div class="transaction-tabs">
            <a href="#record-purchase" class="transaction-tab <?php echo $active_tab === 'record-purchase' ? 'active' : ''; ?>" data-tab="record-purchase">
                <i class="fas fa-user-tag"></i> <?php echo $t['record_purchase']; ?>
            </a>
            <a href="#record-sale" class="transaction-tab <?php echo $active_tab === 'record-sale' ? 'active' : ''; ?>" data-tab="record-sale">
                <i class="fas fa-user-tie"></i> <?php echo $t['record_sale']; ?>
            </a>
            <a href="#recent-transactions" class="transaction-tab <?php echo $active_tab === 'recent-transactions' ? 'active' : ''; ?>" data-tab="recent-transactions">
                <i class="fas fa-history"></i> <?php echo $t['recent_transactions']; ?>
            </a>
        </div>
        
        
<div id="record-purchase" class="dashboard-card" style="<?php echo $active_tab === 'record-purchase' ? '' : 'display: none;'; ?>">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-user-tag"></i> <?php echo $t['record_purchase']; ?></h2>
    </div>
    
    <form method="POST" id="purchaseForm">
       <div class="form-group">
    <label><?php echo $t['seller_information']; ?></label>
    <div class="form-row">
        <div class="form-col">
            <input type="text" name="seller_username" id="seller_username" placeholder="<?php echo $t['username_if_registered']; ?>" class="form-control">
            <div id="seller_username_status" class="username-status"></div>
        </div>
        <div class="form-col">
            <input type="text" name="seller_name" id="seller_name" placeholder="<?php echo $t['seller_name']; ?>" class="form-control" required>
        </div>
        <div class="form-col">
            <input type="text" name="seller_contact" id="seller_contact" placeholder="<?php echo $t['contact_number']; ?>" class="form-control" required>
        </div>
    </div>
</div>
        <div class="form-group">
            <label><?php echo $t['materials_purchased']; ?></label>
            <div id="purchaseItems">
                <div class="material-item">
                    <select name="material_id[]" class="form-control" required>
                        <option value=""><?php echo $t['select_material']; ?></option>
                        <?php foreach ($materials as $material): ?>
                            <option value="<?php echo $material['id']; ?>" 
                                data-price="<?php echo $material['unit_price']; ?>">
                                <?php echo htmlspecialchars($material['material_option']); ?> 
                                (₱<?php echo number_format($material['unit_price'], 2); ?>/kg)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantity[]" placeholder="<?php echo $t['quantity_kg']; ?>" class="form-control" step="0.01" min="0.01" required>
                    <input type="text" name="item_total[]" placeholder="<?php echo $t['total']; ?>" class="form-control" readonly>
                    <button type="button" class="remove-item" onclick="removePurchaseItem(this)"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" onclick="addPurchaseItem()" style="margin-top: 10px;">
                <i class="fas fa-plus"></i> <?php echo $t['add_another_material']; ?>
            </button>
        </div>
        
        <div class="form-group">
            <div class="form-row">
                <div class="form-col">
                    <label><?php echo $t['total_amount']; ?></label>
                    <input type="text" id="purchaseTotal" class="form-control" readonly>
                </div>
                <div class="form-col">
                    <label><?php echo $t['total_weight']; ?></label>
                    <input type="text" id="purchaseWeight" class="form-control" readonly>
                </div>
                <div class="form-col">
                    <label><?php echo $t['points_earned']; ?></label>
                    <input type="text" id="purchasePoints" class="form-control" readonly>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="reset" class="btn btn-secondary"><?php echo $t['clear_form']; ?></button>
            <button type="submit" name="record_purchase" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $t['record_purchase_button']; ?>
            </button>
        </div>
    </form>
</div>
        
        <div id="record-sale" class="dashboard-card" style="<?php echo $active_tab === 'record-sale' ? '' : 'display: none;'; ?>">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-tie"></i> <?php echo $t['record_sale']; ?></h2>
            </div>
            
            <form method="POST" id="saleForm">
              <div class="form-group">
    <label><?php echo $t['buyer_information']; ?></label>
    <div class="form-row">
        <div class="form-col">
            <input type="text" name="buyer_username" id="buyer_username" placeholder="<?php echo $t['username_if_registered']; ?>" class="form-control">
            <div id="buyer_username_status" class="username-status"></div>
        </div>
        <div class="form-col">
            <input type="text" name="buyer_name" id="buyer_name" placeholder="<?php echo $t['buyer_name']; ?>" class="form-control" required>
        </div>
        <div class="form-col">
            <input type="text" name="buyer_contact" id="buyer_contact" placeholder="<?php echo $t['contact_number']; ?>" class="form-control" required>
        </div>
    </div>
</div>
                
                <div class="form-group">
                    <label><?php echo $t['materials_sold']; ?></label>
                    <div id="saleItems">
                        <div class="material-item">
                            <select name="material_id[]" class="form-control" required>
                                <option value=""><?php echo $t['select_material']; ?></option>
                                <?php foreach ($materials as $material): ?>
                                    <option value="<?php echo $material['id']; ?>" 
                                        data-price="<?php echo $material['unit_price']; ?>">
                                        <?php echo htmlspecialchars($material['material_option']); ?> 
                                        (₱<?php echo number_format($material['unit_price'], 2); ?>/kg)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="quantity[]" placeholder="<?php echo $t['quantity_kg']; ?>" class="form-control" step="0.01" min="0.01" required>
                            <input type="text" name="item_total[]" placeholder="<?php echo $t['total']; ?>" class="form-control" readonly>
                            <button type="button" class="remove-item" onclick="removeSaleItem(this)"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addSaleItem()" style="margin-top: 10px;">
                        <i class="fas fa-plus"></i> <?php echo $t['add_another_material']; ?>
                    </button>
                </div>
                
                <div class="form-group">
                    <div class="form-row">
                        <div class="form-col">
                            <label><?php echo $t['total_amount']; ?></label>
                            <input type="text" id="saleTotal" class="form-control" readonly>
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['total_weight']; ?></label>
                            <input type="text" id="saleWeight" class="form-control" readonly>
                        </div>
                        <div class="form-col">
                            <label><?php echo $t['points_earned']; ?></label>
                            <input type="text" id="salePoints" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary"><?php echo $t['clear_form']; ?></button>
                    <button type="submit" name="record_sale" class="btn btn-orange">
                        <i class="fas fa-save"></i> <?php echo $t['record_sale_button']; ?>
                    </button>
                </div>
            </form>
        </div>
        
       
    <div id="recent-transactions" class="dashboard-card" style="<?php echo $active_tab === 'recent-transactions' ? '' : 'display: none;'; ?>">
    <div class="filters" style="margin-bottom: 20px;">
        <form method="GET" id="filterForm">
            <input type="hidden" name="tab" id="activeTab" value="<?php echo $active_tab; ?>">
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
                <h2 class="card-title"><i class="fas fa-history"></i> <?php echo $t['recent_transactions']; ?></h2>
            </div>
            
            <?php if (!empty($recent_transactions)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['transaction_id']; ?></th>
                                <th><?php echo $t['datetime']; ?></th>
                                <th><?php echo $t['amount']; ?></th>
                                <th><?php echo $t['receipt']; ?></th>
                                <th><?php echo $t['status']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                 <td><?php 
    date_default_timezone_set('Asia/Manila');
    echo date('M j, Y h:i A', strtotime($transaction['transaction_date'] . ' ' . $transaction['transaction_time'])); 
?></td>
                                    <td>₱<?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td>
                                        <?php if (file_exists($transaction['receipt_path'])): ?>
                                            <a href="<?php echo $transaction['receipt_path']; ?>" target="_blank" class="btn btn-secondary">
                                                <i class="fas fa-receipt"></i> <?php echo $t['view']; ?>
                                            </a>
                                        <?php else: ?>
                                            <span><?php echo $t['not_available']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-success"><?php echo htmlspecialchars($transaction['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <button class="pagination-btn" onclick="changePage(<?php echo $page - 1; ?>)" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                        <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                    </button>
                    
                    <span class="page-info">
                        <?php echo $t['page']; ?> <?php echo $page; ?> <?php echo $t['of']; ?> <?php echo $total_pages; ?>
                    </span>
                    
                    <button class="pagination-btn" onclick="changePage(<?php echo $page + 1; ?>)" <?php echo $page >= $total_pages ? 'disabled' : ''; ?>>
                        <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <p><?php echo $t['no_recent_transactions']; ?></p>
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
                <?php 
                if ($transaction_type === 'Walk-in') {
                    echo $t['walkin_success'];
                } elseif ($transaction_type === 'Purchase') {
                    echo $t['purchase_success'];
                } elseif ($transaction_type === 'Sale') {
                    echo $t['sale_success'];
                }
                ?>
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

        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Pagination function
        function changePage(newPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', newPage);
            url.searchParams.set('tab', 'recent-transactions'); // Always set tab to recent-transactions for pagination
            window.location.href = url.toString();
        }

        function setupUsernameAutopopulation(usernameFieldId, nameFieldId, contactFieldId, statusFieldId) {
            const usernameField = document.getElementById(usernameFieldId);
            const nameField = document.getElementById(nameFieldId);
            const contactField = document.getElementById(contactFieldId);
            const statusField = document.getElementById(statusFieldId);
            
            let debounceTimer;
            
            usernameField.addEventListener('input', function() {
                const username = this.value.trim();
                
                // Clear previous timer
                clearTimeout(debounceTimer);
                
                if (username === '') {
                    statusField.style.display = 'none';
                    nameField.value = '';
                    contactField.value = '';
                    nameField.removeAttribute('readonly');
                    contactField.removeAttribute('readonly');
                    return;
                }
                
                // Show loading status
                statusField.className = 'username-status loading';
                statusField.textContent = 'Searching...';
                statusField.style.display = 'block';
                
                // Debounce the API call
                debounceTimer = setTimeout(() => {
                    fetch(`transaction_logging.php?action=get_user_data&username=${encodeURIComponent(username)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // User found - populate fields
                                statusField.className = 'username-status found';
                                statusField.textContent = `✓ User found: ${data.data.name}`;
                                
                                nameField.value = data.data.name;
                                contactField.value = data.data.contact;
                                
                                // Make fields readonly to prevent editing
                                nameField.setAttribute('readonly', true);
                                contactField.setAttribute('readonly', true);
                            } else {
                                // User not found
                                statusField.className = 'username-status not-found';
                                statusField.textContent = '✗ Username not found';
                                
                                // Clear fields and make them editable
                                nameField.value = '';
                                contactField.value = '';
                                nameField.removeAttribute('readonly');
                                contactField.removeAttribute('readonly');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching user data:', error);
                            statusField.className = 'username-status not-found';
                            statusField.textContent = '✗ Error checking username';
                            
                            // Make fields editable
                            nameField.removeAttribute('readonly');
                            contactField.removeAttribute('readonly');
                        });
                }, 500); // 500ms debounce
            });
            
            // Clear readonly when username is cleared
            usernameField.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    nameField.removeAttribute('readonly');
                    contactField.removeAttribute('readonly');
                }
            });
        }

      // Calculate totals for purchase items
function calculatePurchaseTotals() {
    let total = 0;
    let totalWeight = 0;
    
    document.querySelectorAll('#purchaseItems .material-item').forEach(item => {
        const select = item.querySelector('select');
        const quantityInput = item.querySelector('input[name="quantity[]"]');
        const totalInput = item.querySelector('input[name="item_total[]"]');
        
        if (select && quantityInput && totalInput) {
            const price = parseFloat(select.options[select.selectedIndex]?.dataset.price || 0);
            const quantity = parseFloat(quantityInput.value) || 0;
            const itemTotal = price * quantity;
            
            totalInput.value = itemTotal.toFixed(2);
            total += itemTotal;
            totalWeight += quantity;
        }
    });
    
    document.getElementById('purchaseTotal').value = total.toFixed(2);
    document.getElementById('purchaseWeight').value = totalWeight.toFixed(2);
    document.getElementById('purchasePoints').value = Math.floor(totalWeight);
}

        // Calculate totals for sale items
        function calculateSaleTotals() {
            let total = 0;
            let totalWeight = 0;
            
            document.querySelectorAll('#saleItems .material-item').forEach(item => {
                const select = item.querySelector('select');
                const quantityInput = item.querySelector('input[name="quantity[]"]');
                const totalInput = item.querySelector('input[name="item_total[]"]');
                
                if (select && quantityInput && totalInput) {
                    const price = parseFloat(select.options[select.selectedIndex]?.dataset.price || 0);
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const itemTotal = price * quantity;
                    
                    totalInput.value = itemTotal.toFixed(2);
                    total += itemTotal;
                    totalWeight += quantity;
                }
            });
            
            document.getElementById('saleTotal').value = total.toFixed(2);
            document.getElementById('saleWeight').value = totalWeight.toFixed(2);
            document.getElementById('salePoints').value = Math.floor(totalWeight);
        }

        // Add purchase item row
        function addPurchaseItem() {
            const container = document.getElementById('purchaseItems');
            const newItem = document.createElement('div');
            newItem.className = 'material-item';
            newItem.innerHTML = `
                <select name="material_id[]" class="form-control" required>
                    <option value=""><?php echo $t['select_material']; ?></option>
                    <?php foreach ($materials as $material): ?>
                        <option value="<?php echo $material['id']; ?>" 
                            data-price="<?php echo $material['unit_price']; ?>">
                            <?php echo htmlspecialchars($material['material_option']); ?> 
                            (₱<?php echo number_format($material['unit_price'], 2); ?>/kg)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantity[]" placeholder="<?php echo $t['quantity_kg']; ?>" class="form-control" step="0.01" min="0.01" required>
                <input type="text" name="item_total[]" placeholder="<?php echo $t['total']; ?>" class="form-control" readonly>
                <button type="button" class="remove-item" onclick="removePurchaseItem(this)"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(newItem);
            
            // Add event listeners to new inputs
            newItem.querySelector('select').addEventListener('change', calculatePurchaseTotals);
            newItem.querySelector('input[name="quantity[]"]').addEventListener('input', calculatePurchaseTotals);
        }

        // Add sale item row
        function addSaleItem() {
            const container = document.getElementById('saleItems');
            const newItem = document.createElement('div');
            newItem.className = 'material-item';
            newItem.innerHTML = `
                <select name="material_id[]" class="form-control" required>
                    <option value=""><?php echo $t['select_material']; ?></option>
                    <?php foreach ($materials as $material): ?>
                        <option value="<?php echo $material['id']; ?>" 
                            data-price="<?php echo $material['unit_price']; ?>">
                            <?php echo htmlspecialchars($material['material_option']); ?> 
                            (₱<?php echo number_format($material['unit_price'], 2); ?>/kg)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantity[]" placeholder="<?php echo $t['quantity_kg']; ?>" class="form-control" step="0.01" min="0.01" required>
                <input type="text" name="item_total[]" placeholder="<?php echo $t['total']; ?>" class="form-control" readonly>
                <button type="button" class="remove-item" onclick="removeSaleItem(this)"><i class="fas fa-times"></i></button>
            `;
            container.appendChild(newItem);
            
            // Add event listeners to new inputs
            newItem.querySelector('select').addEventListener('change', calculateSaleTotals);
            newItem.querySelector('input[name="quantity[]"]').addEventListener('input', calculateSaleTotals);
        }

        // Remove purchase item row
        function removePurchaseItem(button) {
            if (document.querySelectorAll('#purchaseItems .material-item').length > 1) {
                button.closest('.material-item').remove();
                calculatePurchaseTotals();
            } else {
                alert('You need at least one material item');
            }
        }

        // Remove sale item row
        function removeSaleItem(button) {
            if (document.querySelectorAll('#saleItems .material-item').length > 1) {
                button.closest('.material-item').remove();
                calculateSaleTotals();
            } else {
                alert('You need at least one material item');
            }
        }

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', function() {
            setupUsernameAutopopulation('seller_username', 'seller_name', 'seller_contact', 'seller_username_status');
            setupUsernameAutopopulation('buyer_username', 'buyer_name', 'buyer_contact', 'buyer_username_status');
            
            // Purchase form
            document.querySelectorAll('#purchaseItems select').forEach(select => {
                select.addEventListener('change', calculatePurchaseTotals);
            });
            document.querySelectorAll('#purchaseItems input[name="quantity[]"]').forEach(input => {
                input.addEventListener('input', calculatePurchaseTotals);
            });
            
            // Sale form
            document.querySelectorAll('#saleItems select').forEach(select => {
                select.addEventListener('change', calculateSaleTotals);
            });
            document.querySelectorAll('#saleItems input[name="quantity[]"]').forEach(input => {
                input.addEventListener('input', calculateSaleTotals);
            });
            
            // Modal close handlers
            document.getElementById('closeModal')?.addEventListener('click', function() {
                document.getElementById('successModal').style.display = 'none';
            });
            
            document.getElementById('closeModalBtn')?.addEventListener('click', function() {
                document.getElementById('successModal').style.display = 'none';
            });

            // Print receipt handler
            document.getElementById('printReceipt')?.addEventListener('click', function() {
                const iframe = document.querySelector('#receiptPreview iframe');
                iframe.contentWindow.print();
            });

            // Close modal when clicking outside
            document.getElementById('successModal')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });

            // Update active tab hidden field when switching tabs
            document.querySelectorAll('.transaction-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.getElementById('activeTab').value = this.getAttribute('data-tab');
                });
            });
        });

        // Tab switching
        document.querySelectorAll('.transaction-tab').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active tab
                document.querySelectorAll('.transaction-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding section
                const target = this.getAttribute('href').substring(1);
                document.querySelectorAll('.dashboard-card').forEach(card => {
                    card.style.display = 'none';
                });
                document.getElementById(target).style.display = 'block';
                
                // Update the hidden tab field
                document.getElementById('activeTab').value = target;
            });
        });
    </script>
</body>
</html>