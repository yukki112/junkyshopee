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
        'inventory_management' => 'Inventory Management',
        'welcome_message' => 'Track and manage all your inventory items and stock movements in one place. Keep your materials organized and stocked.',
        'new_item' => 'New Item',
        'items' => 'Items',
        'history_logs' => 'History Logs',
        'inventory_items' => 'Inventory Items',
        'add_item' => 'Add Item',
        'search_items' => 'Search items...',
        'all_materials' => 'All Materials',
        'all_stock' => 'All Stock',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'item_name' => 'Item Name',
        'material' => 'Material',
        'stock' => 'Stock',
        'unit_price' => 'Unit Price',
        'condition' => 'Condition',
        'supplier' => 'Supplier',
        'actions' => 'Actions',
        'normal' => 'Normal',
        'update' => 'Update',
        'no_inventory_items' => 'No inventory items found',
        'date' => 'Date',
        'action' => 'Action',
        'user' => 'User',
        'quantity' => 'Quantity',
        'previous' => 'Previous',
        'new' => 'New',
        'reason' => 'Reason',
        'addition' => 'Addition',
        'deduction' => 'Deduction',
        'no_inventory_logs' => 'No inventory logs found',
        'page' => 'Page',
        'of' => 'of',
        'previous_page' => 'Previous',
        'next_page' => 'Next',
        'add_new_item' => 'Add New Inventory Item',
        'item_name_label' => 'Item Name',
        'material_label' => 'Material',
        'condition_label' => 'Condition',
        'select_condition' => 'Select Condition',
        'supplier_label' => 'Supplier',
        'select_supplier' => 'Select Supplier',
        'barcode' => 'Barcode',
        'unit' => 'Unit',
        'initial_stock' => 'Initial Stock',
        'min_stock_level' => 'Min Stock Level',
        'description' => 'Description',
        'notes' => 'Notes',
        'cancel' => 'Cancel',
        'update_stock' => 'Update Stock',
        'action_type' => 'Action Type',
        'quantity_label' => 'Quantity',
        'reason_label' => 'Reason',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Users',
        'pricing_control' => 'Pricing Control',
        'reports_analytics' => 'Reports & Analytics',
        'transactions' => 'Transactions',
        'loyalty_program' => 'Loyalty Program',
        'profile' => 'Profile',
        'export_report' => 'Export Report',
        'logout' => 'Logout',
        'administrator' => 'Administrator'
    ],
    'tl' => [
        'inventory_management' => 'Pamamahala ng Inventory',
        'welcome_message' => 'Subaybayan at pamahalaan ang lahat ng iyong mga item sa inventory at paggalaw ng stock sa isang lugar. Panatilihing maayos at naka-stock ang iyong mga materyales.',
        'new_item' => 'Bagong Item',
        'items' => 'Mga Item',
        'history_logs' => 'Mga Talaan ng Kasaysayan',
        'inventory_items' => 'Mga Item sa Inventory',
        'add_item' => 'Magdagdag ng Item',
        'search_items' => 'Maghanap ng mga item...',
        'all_materials' => 'Lahat ng Materyales',
        'all_stock' => 'Lahat ng Stock',
        'low_stock' => 'Mababang Stock',
        'out_of_stock' => 'Walang Stock',
        'item_name' => 'Pangalan ng Item',
        'material' => 'Materyal',
        'stock' => 'Stock',
        'unit_price' => 'Presyo ng Yunit',
        'condition' => 'Kondisyon',
        'supplier' => 'Tagapagtustos',
        'actions' => 'Mga Aksyon',
        'normal' => 'Normal',
        'update' => 'I-update',
        'no_inventory_items' => 'Walang nakitang mga item sa inventory',
        'date' => 'Petsa',
        'action' => 'Aksyon',
        'user' => 'User',
        'quantity' => 'Dami',
        'previous' => 'Nakaraan',
        'new' => 'Bago',
        'reason' => 'Dahilan',
        'addition' => 'Pagdaragdag',
        'deduction' => 'Pagbabawas',
        'no_inventory_logs' => 'Walang nakitang mga talaan ng inventory',
        'page' => 'Pahina',
        'of' => 'ng',
        'previous_page' => 'Nakaraan',
        'next_page' => 'Susunod',
        'add_new_item' => 'Magdagdag ng Bagong Item sa Inventory',
        'item_name_label' => 'Pangalan ng Item',
        'material_label' => 'Materyal',
        'condition_label' => 'Kondisyon',
        'select_condition' => 'Pumili ng Kondisyon',
        'supplier_label' => 'Tagapagtustos',
        'select_supplier' => 'Pumili ng Tagapagtustos',
        'barcode' => 'Barcode',
        'unit' => 'Yunit',
        'initial_stock' => 'Paunang Stock',
        'min_stock_level' => 'Minimum na Antas ng Stock',
        'description' => 'Paglalarawan',
        'notes' => 'Mga Tala',
        'cancel' => 'Kanselahin',
        'update_stock' => 'I-update ang Stock',
        'action_type' => 'Uri ng Aksyon',
        'quantity_label' => 'Dami',
        'reason_label' => 'Dahilan',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Mga User',
        'pricing_control' => 'Kontrol sa Presyo',
        'reports_analytics' => 'Mga Ulat at Analytics',
        'transactions' => 'Mga Transaksyon',
        'loyalty_program' => 'Programa ng Loyalty',
        'profile' => 'Profile',
        'export_report' => 'I-export ang Ulat',
        'logout' => 'Logout',
        'administrator' => 'Administrator'
    ]
];

$t = $translations[$language];

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'items';

// Handle PDF Export
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $export_type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
    
    switch($export_type) {
        case 'inventory_items':
            generateInventoryItemsPDF($conn);
            break;
        case 'inventory_logs':
            generateInventoryLogsPDF($conn);
            break;
    }
    exit();
}

// Initialize variables
$items = [];
$logs = [];
$materials = [];
$conditions = [];
$suppliers = [];

// Get all materials
try {
    $materials = $conn->query("SELECT * FROM materials WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Materials query failed: " . $e->getMessage());
}

// Get all item conditions
try {
    $conditions = $conn->query("SELECT * FROM item_conditions WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Conditions query failed: " . $e->getMessage());
}

// Get all suppliers
try {
    $suppliers = $conn->query("SELECT * FROM inventory_suppliers WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Suppliers query failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new item
    if (isset($_POST['add_item'])) {
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("INSERT INTO inventory_items 
                (material_id, condition_id, item_name, barcode, description, 
                 initial_stock, current_stock, unit, notes, min_stock_level, 
                 low_stock_threshold, user_id, supplier_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $initial_stock = floatval($_POST['initial_stock']);
            $min_stock_level = floatval($_POST['min_stock_level']);
            
            $stmt->execute([
                intval($_POST['material_id']),
                !empty($_POST['condition_id']) ? intval($_POST['condition_id']) : null,
                sanitizeInput($_POST['item_name']),
                sanitizeInput($_POST['barcode']),
                sanitizeInput($_POST['description']),
                $initial_stock,
                $initial_stock,
                sanitizeInput($_POST['unit']),
                sanitizeInput($_POST['notes']),
                $min_stock_level,
                $min_stock_level,
                $user_id,
                !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null
            ]);
            
            $item_id = $conn->lastInsertId();
            
            // Log the addition
            $stmt = $conn->prepare("INSERT INTO inventory_logs 
                (inventory_item_id, user_id, action_type, quantity_change, 
                 previous_stock, new_stock, reason) 
                VALUES (?, ?, 'addition', ?, 0, ?, 'Initial stock addition')");
            $stmt->execute([$item_id, $user_id, $initial_stock, $initial_stock]);
            
            $conn->commit();
            $_SESSION['success'] = "Item added successfully!";
        } catch(PDOException $e) {
            $conn->rollBack();
            error_log("Item insert failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to add item.";
        }
        header("Location: inventory.php?tab=items");
        exit();
    }
    
    // Update item stock
    if (isset($_POST['update_stock'])) {
        try {
            $conn->beginTransaction();
            
            $item_id = intval($_POST['item_id']);
            $action_type = sanitizeInput($_POST['action_type']);
            $quantity = floatval($_POST['quantity']);
            $reason = sanitizeInput($_POST['reason']);
            
            // Get current stock
            $stmt = $conn->prepare("SELECT current_stock FROM inventory_items WHERE id = ?");
            $stmt->execute([$item_id]);
            $stock_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stock_data) {
                throw new Exception("Item not found");
            }
            
            $current_stock = $stock_data['current_stock'];
            $new_stock = ($action_type === 'addition') ? 
                $current_stock + $quantity : $current_stock - $quantity;
            
            // Update inventory
            $stmt = $conn->prepare("UPDATE inventory_items SET current_stock = ? WHERE id = ?");
            $stmt->execute([$new_stock, $item_id]);
            
            // Log the change
            $stmt = $conn->prepare("INSERT INTO inventory_logs 
                (inventory_item_id, user_id, action_type, quantity_change, 
                 previous_stock, new_stock, reason) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $item_id, 
                $user_id, 
                $action_type, 
                $quantity,
                $current_stock, 
                $new_stock, 
                $reason
            ]);
            
            $conn->commit();
            $_SESSION['success'] = "Stock updated successfully!";
        } catch(Exception $e) {
            $conn->rollBack();
            error_log("Stock update failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update stock.";
        }
        header("Location: inventory.php?tab=items");
        exit();
    }
}

// Get items with filtering
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$material_filter = isset($_GET['material']) ? intval($_GET['material']) : 0;
$stock_filter = isset($_GET['stock']) ? sanitizeInput($_GET['stock']) : 'all';

$item_query = "SELECT 
    ii.*, 
    m.material_option, 
    m.unit_price,
    ico.condition_label,
    s.name as supplier_name
FROM inventory_items ii
LEFT JOIN materials m ON ii.material_id = m.id
LEFT JOIN item_conditions ico ON ii.condition_id = ico.id
LEFT JOIN inventory_suppliers s ON ii.supplier_id = s.id
WHERE ii.is_active = 1";

$params = [];
$types = '';

if (!empty($search)) {
    $item_query .= " AND (ii.item_name LIKE ? OR ii.barcode LIKE ? OR ii.description LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
    $types .= 'sss';
}

if ($material_filter > 0) {
    $item_query .= " AND ii.material_id = ?";
    $params[] = $material_filter;
    $types .= 'i';
}

if ($stock_filter === 'low') {
    $item_query .= " AND ii.current_stock <= ii.min_stock_level";
} elseif ($stock_filter === 'out') {
    $item_query .= " AND ii.current_stock <= 0";
}

$item_query .= " ORDER BY ii.item_name ASC";

try {
    $stmt = $conn->prepare($item_query);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Items query failed: " . $e->getMessage());
}

// Get inventory logs with pagination
$logs_page = isset($_GET['logs_page']) ? max(1, intval($_GET['logs_page'])) : 1;
$logs_per_page = 5; // Changed to 5 per page as requested
$logs_offset = ($logs_page - 1) * $logs_per_page;

try {
    // Get total count for pagination
    $count_stmt = $conn->query("SELECT COUNT(*) as total FROM inventory_logs");
    $total_logs = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_logs / $logs_per_page);
    
    // Get logs for current page
    $logs = $conn->prepare("SELECT 
        il.*, 
        ii.item_name,
        CONCAT(u.first_name, ' ', u.last_name) as user_name
    FROM inventory_logs il
    JOIN inventory_items ii ON il.inventory_item_id = ii.id
    JOIN users u ON il.user_id = u.id
    ORDER BY il.created_at DESC
    LIMIT ? OFFSET ?");
    $logs->execute([$logs_per_page, $logs_offset]);
    $logs = $logs->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Logs query failed: " . $e->getMessage());
    $total_pages = 1;
}

// Display success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// PDF Generation Functions
function generateInventoryItemsPDF($conn) {
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
    $pdf->Cell(0, 15, 'INVENTORY ITEMS REPORT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    // Get inventory items data
    try {
        $stmt = $conn->query("SELECT 
            ii.item_name,
            m.material_option,
            ii.current_stock,
            ii.unit,
            m.unit_price,
            ico.condition_label,
            s.name as supplier_name
        FROM inventory_items ii
        LEFT JOIN materials m ON ii.material_id = m.id
        LEFT JOIN item_conditions ico ON ii.condition_id = ico.id
        LEFT JOIN inventory_suppliers s ON ii.supplier_id = s.id
        WHERE ii.is_active = 1
        ORDER BY ii.item_name ASC");
        $inventory_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Statistics
        $total_items = count($inventory_data);
        $total_stock = array_sum(array_column($inventory_data, 'current_stock'));
        $total_value = 0;
        foreach ($inventory_data as $item) {
            $total_value += $item['current_stock'] * $item['unit_price'];
        }
    } catch(PDOException $e) {
        $inventory_data = [];
        $total_items = $total_stock = $total_value = 0;
    }
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Items:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_items), 0, 1);
    $pdf->Cell(50, 7, 'Total Stock:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_stock, 2) . ' kg', 0, 1);
    $pdf->Cell(50, 7, 'Total Value:', 0, 0);
    $pdf->Cell(40, 7, 'P' . number_format($total_value, 2), 0, 1);
    
    // Real-time Prices Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(112, 139, 76);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'REAL-TIME PRICES (Based on NSWMC)', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    // Real-time prices data (you can update these based on the actual NSWMC prices)
    $realtime_prices = [
        ['Material' => 'White Paper', 'Price' => 'P 8.00/kg'],
        ['Material' => 'Newspaper', 'Price' => 'P 7.00/kg'],
        ['Material' => 'Corrugated Carton', 'Price' => 'P 6.00/kg'],
        ['Material' => 'PET Bottles', 'Price' => 'P 12.00/kg'],
        ['Material' => 'HDPE Plastic', 'Price' => 'P 15.00/kg'],
        ['Material' => 'LDPE Plastic', 'Price' => 'P 10.00/kg'],
        ['Material' => 'PP Plastic', 'Price' => 'P 8.00/kg'],
        ['Material' => 'Aluminum Cans', 'Price' => 'P 45.00/kg'],
        ['Material' => 'Tin Cans', 'Price' => 'P 8.00/kg'],
        ['Material' => 'Glass Bottles', 'Price' => 'P 3.00/kg'],
        ['Material' => 'Scrap Iron', 'Price' => 'P 12.00/kg'],
        ['Material' => 'Copper', 'Price' => 'P 300.00/kg'],
        ['Material' => 'Brass', 'Price' => 'P 180.00/kg'],
    ];
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(100, 7, 'Material', 1, 0, 'C');
    $pdf->Cell(50, 7, 'Price per kg', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    foreach ($realtime_prices as $price) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(100, 7, 'Material', 1, 0, 'C');
            $pdf->Cell(50, 7, 'Price per kg', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 9);
        }
        
        $pdf->Cell(100, 6, $price['Material'], 1, 0);
        $pdf->Cell(50, 6, $price['Price'], 1, 1, 'C');
    }
    
    // Inventory Items Table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(217, 122, 65);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'INVENTORY ITEMS', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(50, 7, 'Item Name', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Material', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Stock', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Unit Price', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Total Value', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    foreach ($inventory_data as $item) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(50, 7, 'Item Name', 1, 0, 'C');
            $pdf->Cell(40, 7, 'Material', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Stock', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Unit Price', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Total Value', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 8);
        }
        
        $total_value = $item['current_stock'] * $item['unit_price'];
        
        $pdf->Cell(50, 6, substr($item['item_name'], 0, 30), 1, 0);
        $pdf->Cell(40, 6, substr($item['material_option'], 0, 20), 1, 0);
        $pdf->Cell(25, 6, number_format($item['current_stock'], 2) . ' ' . $item['unit'], 1, 0, 'R');
        $pdf->Cell(25, 6, 'P' . number_format($item['unit_price'], 2), 1, 0, 'R');
        $pdf->Cell(25, 6, 'P' . number_format($total_value, 2), 1, 1, 'R');
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'inventory_items_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateInventoryLogsPDF($conn) {
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
    $pdf->Cell(0, 15, 'INVENTORY MOVEMENT LOGS', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    // Get inventory logs data
    try {
        $stmt = $conn->query("SELECT 
            il.created_at,
            il.action_type,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            ii.item_name,
            il.quantity_change,
            il.previous_stock,
            il.new_stock,
            il.reason
        FROM inventory_logs il
        JOIN inventory_items ii ON il.inventory_item_id = ii.id
        JOIN users u ON il.user_id = u.id
        ORDER BY il.created_at DESC");
        $logs_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Statistics
        $total_logs = count($logs_data);
        $total_additions = 0;
        $total_deductions = 0;
        foreach ($logs_data as $log) {
            if ($log['action_type'] === 'addition') {
                $total_additions += $log['quantity_change'];
            } else {
                $total_deductions += $log['quantity_change'];
            }
        }
    } catch(PDOException $e) {
        $logs_data = [];
        $total_logs = $total_additions = $total_deductions = 0;
    }
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Logs:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_logs), 0, 1);
    $pdf->Cell(50, 7, 'Total Additions:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_additions, 2) . ' kg', 0, 1);
    $pdf->Cell(50, 7, 'Total Deductions:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_deductions, 2) . ' kg', 0, 1);
    $pdf->Cell(50, 7, 'Net Change:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_additions - $total_deductions, 2) . ' kg', 0, 1);
    
    // Logs Table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(30, 7, 'Date', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Action', 1, 0, 'C');
    $pdf->Cell(35, 7, 'User', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Item', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Change', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Previous', 1, 0, 'C');
    $pdf->Cell(20, 7, 'New', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 7);
    foreach ($logs_data as $log) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(30, 7, 'Date', 1, 0, 'C');
            $pdf->Cell(20, 7, 'Action', 1, 0, 'C');
            $pdf->Cell(35, 7, 'User', 1, 0, 'C');
            $pdf->Cell(40, 7, 'Item', 1, 0, 'C');
            $pdf->Cell(20, 7, 'Change', 1, 0, 'C');
            $pdf->Cell(20, 7, 'Previous', 1, 0, 'C');
            $pdf->Cell(20, 7, 'New', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 7);
        }
        
        $pdf->Cell(30, 6, date('m/d/Y H:i', strtotime($log['created_at'])), 1, 0);
        $pdf->Cell(20, 6, ucfirst($log['action_type']), 1, 0);
        $pdf->Cell(35, 6, substr($log['user_name'], 0, 20), 1, 0);
        $pdf->Cell(40, 6, substr($log['item_name'], 0, 25), 1, 0);
        $pdf->Cell(20, 6, number_format($log['quantity_change'], 2), 1, 0, 'R');
        $pdf->Cell(20, 6, number_format($log['previous_stock'], 2), 1, 0, 'R');
        $pdf->Cell(20, 6, number_format($log['new_stock'], 2), 1, 1, 'R');
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'inventory_logs_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['inventory_management']; ?></title>
     <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    /* All the existing CSS styles remain the same, just adding the export button styles */
    
    .export-btn {
        background-color: var(--sales-orange);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
        margin-left: 10px;
    }
    
    .export-btn:hover {
        background-color: #c56938;
        transform: translateY(-2px);
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .pagination-dots {
        padding: 8px 12px;
        color: var(--text-dark);
    }
    
    body.dark-mode .pagination-dots {
        color: var(--dark-text-primary);
    }
    
    /* Rest of the existing CSS remains exactly the same */
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

    .view-all {
        color: var(--icon-green);
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .view-all:hover {
        color: var(--icon-orange);
        transform: translateX(3px);
    }

    /* Buttons */
    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-primary {
        background-color: var(--icon-green);
        color: white;
    }

    .btn-primary:hover {
        background-color: #5A6F3D;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(106, 127, 70, 0.3);
    }

    .btn-secondary {
        background-color: var(--panel-cream);
        color: var(--text-dark);
    }

    body.dark-mode .btn-secondary {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
    }

    .btn-secondary:hover {
        background-color: #E3D5B8;
        transform: translateY(-2px);
    }

    body.dark-mode .btn-secondary:hover {
        background-color: var(--dark-border);
    }

    .btn-outline {
        background-color: transparent;
        border: 1px solid var(--icon-green);
        color: var(--icon-green);
    }

    .btn-outline:hover {
        background-color: var(--icon-green);
        color: white;
        transform: translateY(-2px);
    }

    /* Search and Filter */
    .search-filter-container {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
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

    .filter-select {
        padding: 12px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        background-color: white;
        color: var(--text-dark);
        min-width: 150px;
        transition: all 0.3s ease;
    }

    body.dark-mode .filter-select {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    /* Tables */
    .table-responsive {
        overflow-x: auto;
        border-radius: 8px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    body.dark-mode .data-table {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 2px 10px var(--dark-shadow);
    }

    .data-table th {
        background-color: var(--panel-cream);
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 14px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .data-table th {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
        border-bottom-color: var(--dark-border);
    }

    .data-table td {
        padding: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        color: var(--text-dark);
        font-size: 14px;
        transition: all 0.3s ease;
    }

    body.dark-mode .data-table td {
        color: var(--dark-text-primary);
        border-bottom-color: var(--dark-border);
    }

    .data-table tr:hover td {
        background-color: rgba(106, 127, 70, 0.05);
    }

    body.dark-mode .data-table tr:hover td {
        background-color: rgba(106, 127, 70, 0.1);
    }

    .stock-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .stock-normal {
        background-color: rgba(112, 139, 76, 0.1);
        color: var(--stock-green);
    }

    .stock-low {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    .stock-out {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 4px;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .pagination-btn {
        padding: 8px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 6px;
        background-color: white;
        color: var(--text-dark);
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    body.dark-mode .pagination-btn {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .pagination-btn:hover {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
    }

    .pagination-btn.active {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
    }

    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-btn.disabled:hover {
        background-color: white;
        color: var(--text-dark);
        border-color: rgba(0,0,0,0.1);
    }

    body.dark-mode .pagination-btn.disabled:hover {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    /* Modals */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: white;
        border-radius: 15px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        transform: scale(0.9);
        opacity: 0;
        transition: all 0.3s ease;
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary);
    }

    .modal.active .modal-content {
        transform: scale(1);
        opacity: 1;
    }

    .modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex;
        justify-content: between;
        align-items: center;
    }

    body.dark-mode .modal-header {
        border-bottom-color: var(--dark-border);
    }

    .modal-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .modal-title {
        color: var(--dark-text-primary);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: var(--text-dark);
        cursor: pointer;
        transition: color 0.3s ease;
    }

    body.dark-mode .modal-close {
        color: var(--dark-text-primary);
    }

    .modal-close:hover {
        color: var(--sales-orange);
    }

    .modal-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .form-label {
        color: var(--dark-text-primary);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
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

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 25px;
    }

    /* Alerts */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .alert-success {
        background-color: rgba(46, 204, 113, 0.1);
        color: #27ae60;
        border: 1px solid rgba(46, 204, 113, 0.2);
    }

    .alert-error {
        background-color: rgba(231, 76, 60, 0.1);
        color: #c0392b;
        border: 1px solid rgba(231, 76, 60, 0.2);
    }

    /* Responsive */
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
        
        .header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .header-controls {
            width: 100%;
            justify-content: space-between;
        }
        
        .search-filter-container {
            flex-direction: column;
        }
        
        .search-box {
            min-width: 100%;
        }
    }

    /* Loading Animation */
    .loading {
        opacity: 0.7;
        pointer-events: none;
        position: relative;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid rgba(106, 127, 70, 0.3);
        border-top: 2px solid var(--icon-green);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
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
                <?php echo $t['administrator']; ?>
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
            <li><a href="inventory.php"class="active"><i class="fas fa-boxes"></i> <?php echo $t['inventory']; ?></a></li>
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
        <!-- Header -->
        <div class="header">
            <h1 class="page-title"><?php echo $t['inventory_management']; ?></h1>
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
                <h2><?php echo $t['inventory_management']; ?></h2>
                <p><?php echo $t['welcome_message']; ?></p>
                <button class="btn btn-primary" onclick="openAddItemModal()">
                    <i class="fas fa-plus"></i>
                    <?php echo $t['new_item']; ?>
                </button>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>

        <!-- Inventory Tabs -->
        <div class="inventory-tabs">
            <a href="?tab=items" class="inventory-tab <?php echo $current_tab === 'items' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <?php echo $t['items']; ?>
            </a>
            <a href="?tab=logs" class="inventory-tab <?php echo $current_tab === 'logs' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <?php echo $t['history_logs']; ?>
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Items Tab Content -->
        <?php if ($current_tab === 'items'): ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-boxes"></i>
                        <?php echo $t['inventory_items']; ?>
                    </h3>
                    <div>
                        <button class="btn btn-primary" onclick="openAddItemModal()">
                            <i class="fas fa-plus"></i>
                            <?php echo $t['add_item']; ?>
                        </button>
                        <button class="export-btn" onclick="exportInventoryItems()">
                            <i class="fas fa-file-pdf"></i>
                            <?php echo $t['export_report']; ?>
                        </button>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="search-filter-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="<?php echo $t['search_items']; ?>" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <select class="filter-select" id="materialFilter">
                        <option value="0"><?php echo $t['all_materials']; ?></option>
                        <?php foreach ($materials as $material): ?>
                            <option value="<?php echo $material['id']; ?>" 
                                <?php echo $material_filter == $material['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($material['material_option']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="filter-select" id="stockFilter">
                        <option value="all" <?php echo $stock_filter === 'all' ? 'selected' : ''; ?>>
                            <?php echo $t['all_stock']; ?>
                        </option>
                        <option value="low" <?php echo $stock_filter === 'low' ? 'selected' : ''; ?>>
                            <?php echo $t['low_stock']; ?>
                        </option>
                        <option value="out" <?php echo $stock_filter === 'out' ? 'selected' : ''; ?>>
                            <?php echo $t['out_of_stock']; ?>
                        </option>
                    </select>
                </div>

                <!-- Items Table -->
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $t['item_name']; ?></th>
                                <th><?php echo $t['material']; ?></th>
                                <th><?php echo $t['stock']; ?></th>
                                <th><?php echo $t['unit_price']; ?></th>
                                <th><?php echo $t['condition']; ?></th>
                                <th><?php echo $t['supplier']; ?></th>
                                <th><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">
                                        <?php echo $t['no_inventory_items']; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['material_option'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $stock_class = 'stock-normal';
                                            if ($item['current_stock'] <= 0) {
                                                $stock_class = 'stock-out';
                                            } elseif ($item['current_stock'] <= $item['min_stock_level']) {
                                                $stock_class = 'stock-low';
                                            }
                                            ?>
                                            <span class="stock-badge <?php echo $stock_class; ?>">
                                                <?php echo number_format($item['current_stock'], 2); ?> <?php echo htmlspecialchars($item['unit']); ?>
                                            </span>
                                        </td>
                                        <td>₱<?php echo number_format($item['unit_price'] ?? 0, 2); ?></td>
                                        <td><?php echo htmlspecialchars($item['condition_label'] ?? $t['normal']); ?></td>
                                        <td><?php echo htmlspecialchars($item['supplier_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" 
                                                        onclick="openUpdateStockModal(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-edit"></i> <?php echo $t['update']; ?>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Logs Tab Content -->
        <?php if ($current_tab === 'logs'): ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        <?php echo $t['history_logs']; ?>
                    </h3>
                    <button class="export-btn" onclick="exportInventoryLogs()">
                        <i class="fas fa-file-pdf"></i>
                        <?php echo $t['export_report']; ?>
                    </button>
                </div>

                <!-- Logs Table -->
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $t['date']; ?></th>
                                <th><?php echo $t['action']; ?></th>
                                <th><?php echo $t['user']; ?></th>
                                <th><?php echo $t['item_name']; ?></th>
                                <th><?php echo $t['quantity']; ?></th>
                                <th><?php echo $t['previous']; ?></th>
                                <th><?php echo $t['new']; ?></th>
                                <th><?php echo $t['reason']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">
                                        <?php echo $t['no_inventory_logs']; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                                        <td>
                                            <span class="stock-badge <?php echo $log['action_type'] === 'addition' ? 'stock-normal' : 'stock-low'; ?>">
                                                <?php echo $log['action_type'] === 'addition' ? $t['addition'] : $t['deduction']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($log['item_name']); ?></td>
                                        <td><?php echo number_format($log['quantity_change'], 2); ?></td>
                                        <td><?php echo number_format($log['previous_stock'], 2); ?></td>
                                        <td><?php echo number_format($log['new_stock'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($log['reason']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <a href="?tab=logs&logs_page=<?php echo max(1, $logs_page - 1); ?>" 
                           class="pagination-btn <?php echo $logs_page <= 1 ? 'disabled' : ''; ?>">
                            <?php echo $t['previous_page']; ?>
                        </a>
                        
                        <?php 
                        // Show first page
                        if ($logs_page > 3): ?>
                            <a href="?tab=logs&logs_page=1" class="pagination-btn">1</a>
                            <?php if ($logs_page > 4): ?>
                                <span class="pagination-dots">...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php 
                        // Show pages around current page
                        for ($i = max(1, $logs_page - 2); $i <= min($total_pages, $logs_page + 2); $i++): ?>
                            <a href="?tab=logs&logs_page=<?php echo $i; ?>" 
                               class="pagination-btn <?php echo $i == $logs_page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php 
                        // Show last page
                        if ($logs_page < $total_pages - 2): ?>
                            <?php if ($logs_page < $total_pages - 3): ?>
                                <span class="pagination-dots">...</span>
                            <?php endif; ?>
                            <a href="?tab=logs&logs_page=<?php echo $total_pages; ?>" class="pagination-btn"><?php echo $total_pages; ?></a>
                        <?php endif; ?>
                        
                        <a href="?tab=logs&logs_page=<?php echo min($total_pages, $logs_page + 1); ?>" 
                           class="pagination-btn <?php echo $logs_page >= $total_pages ? 'disabled' : ''; ?>">
                            <?php echo $t['next_page']; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Item Modal -->
    <div class="modal" id="addItemModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $t['add_new_item']; ?></h3>
                <button class="modal-close" onclick="closeAddItemModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="addItemForm">
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['item_name_label']; ?> *</label>
                        <input type="text" class="form-control" name="item_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['material_label']; ?> *</label>
                        <select class="form-control" name="material_id" required>
                            <option value=""><?php echo $t['select_material']; ?></option>
                            <?php foreach ($materials as $material): ?>
                                <option value="<?php echo $material['id']; ?>">
                                    <?php echo htmlspecialchars($material['material_option']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['condition_label']; ?></label>
                        <select class="form-control" name="condition_id">
                            <option value=""><?php echo $t['select_condition']; ?></option>
                            <?php foreach ($conditions as $condition): ?>
                                <option value="<?php echo $condition['id']; ?>">
                                    <?php echo htmlspecialchars($condition['condition_label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['supplier_label']; ?></label>
                        <select class="form-control" name="supplier_id">
                            <option value=""><?php echo $t['select_supplier']; ?></option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['id']; ?>">
                                    <?php echo htmlspecialchars($supplier['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['barcode']; ?></label>
                        <input type="text" class="form-control" name="barcode">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['unit']; ?></label>
                        <input type="text" class="form-control" name="unit" placeholder="kg, pcs, etc.">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['initial_stock']; ?> *</label>
                        <input type="number" class="form-control" name="initial_stock" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['min_stock_level']; ?></label>
                        <input type="number" class="form-control" name="min_stock_level" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['description']; ?></label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['notes']; ?></label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeAddItemModal()">
                            <?php echo $t['cancel']; ?>
                        </button>
                        <button type="submit" class="btn btn-primary" name="add_item">
                            <?php echo $t['add_item']; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Stock Modal -->
    <div class="modal" id="updateStockModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $t['update_stock']; ?></h3>
                <button class="modal-close" onclick="closeUpdateStockModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="updateStockForm">
                    <input type="hidden" name="item_id" id="updateItemId">
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['action_type']; ?> *</label>
                        <select class="form-control" name="action_type" required>
                            <option value="addition"><?php echo $t['addition']; ?></option>
                            <option value="deduction"><?php echo $t['deduction']; ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['quantity_label']; ?> *</label>
                        <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $t['reason_label']; ?></label>
                        <input type="text" class="form-control" name="reason" placeholder="Reason for stock change">
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeUpdateStockModal()">
                            <?php echo $t['cancel']; ?>
                        </button>
                        <button type="submit" class="btn btn-primary" name="update_stock">
                            <?php echo $t['update_stock']; ?>
                        </button>
                    </div>
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

    // Search and Filter Functionality
    const searchInput = document.getElementById('searchInput');
    const materialFilter = document.getElementById('materialFilter');
    const stockFilter = document.getElementById('stockFilter');

    function applyFilters() {
        const search = searchInput.value;
        const material = materialFilter.value;
        const stock = stockFilter.value;
        
        const params = new URLSearchParams();
        params.set('tab', 'items');
        
        if (search) params.set('search', search);
        if (material > 0) params.set('material', material);
        if (stock !== 'all') params.set('stock', stock);
        
        window.location.href = 'inventory.php?' + params.toString();
    }

    // Debounce search input
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });

    materialFilter.addEventListener('change', applyFilters);
    stockFilter.addEventListener('change', applyFilters);

    // Modal Functions
    function openAddItemModal() {
        document.getElementById('addItemModal').classList.add('active');
    }

    function closeAddItemModal() {
        document.getElementById('addItemModal').classList.remove('active');
    }

    function openUpdateStockModal(itemId) {
        document.getElementById('updateItemId').value = itemId;
        document.getElementById('updateStockModal').classList.add('active');
    }

    function closeUpdateStockModal() {
        document.getElementById('updateStockModal').classList.remove('active');
    }

    // Export Functions
    function exportInventoryItems() {
        window.location.href = 'inventory.php?export=pdf&type=inventory_items';
    }

    function exportInventoryLogs() {
        window.location.href = 'inventory.php?export=pdf&type=inventory_logs';
    }

    // Close modals when clicking outside
    document.addEventListener('click', (e) => {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Logout function
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '../Customer-portal/Login/Login.php?logout=true';
        }
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
    </script>
</body>
</html>