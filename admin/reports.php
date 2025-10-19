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
        'reports_analytics' => 'Reports & Analytics',
        'business_intelligence_dashboard' => 'Business Intelligence Dashboard',
        'welcome_message' => 'Gain insights into your business performance with comprehensive reports and analytics. Track sales, inventory, employee performance, and customer activity.',
        'quick_report' => 'Quick Report',
        'sales_reports' => 'Sales Reports',
        'inventory_movement' => 'Inventory Movement',
        'employee_performance' => 'Employee Performance',
        'customer_activity' => 'Customer Activity',
        'profit_loss' => 'Profit & Loss',
        'export_report' => 'Export Report',
        'filter_by_customer' => 'Filter by Customer',
        'all_customers' => 'All Customers',
        'filter_by_employee' => 'Filter by Employee',
        'all_employees' => 'All Employees',
        'filter_by_item' => 'Filter by Item',
        'all_items' => 'All Items',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'apply_filters' => 'Apply Filters',
        'transaction_id' => 'Transaction ID',
        'date' => 'Date',
        'customer' => 'Customer',
        'amount' => 'Amount',
        'items' => 'Items',
        'points_earned' => 'Points Earned',
        'no_customer_activity' => 'No customer activity found',
        'employee' => 'Employee',
        'role' => 'Role',
        'logins' => 'Logins',
        'hours_worked' => 'Hours Worked',
        'inventory_actions' => 'Inventory Actions',
        'no_employee_activity' => 'No employee activity found',
        'month' => 'Month',
        'revenue' => 'Revenue',
        'cost' => 'Cost',
        'profit' => 'Profit',
        'margin' => 'Margin',
        'no_profit_loss_data' => 'No profit & loss data found',
        'action' => 'Action',
        'quantity_change' => 'Quantity Change',
        'previous_stock' => 'Previous Stock',
        'new_stock' => 'New Stock',
        'reason' => 'Reason',
        'no_inventory_movement' => 'No inventory movement found',
        'total_sales' => 'Total Sales',
        'change' => 'Change',
        'no_sales_data' => 'No sales data found',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Users',
        'pricing_control' => 'Pricing Control',
        'transactions' => 'Transactions',
        'loyalty_program' => 'Loyalty Program',
        'profile' => 'Profile',
     
        'logout' => 'Logout',
        'administrator' => 'Administrator'
    ],
    'tl' => [
        'reports_analytics' => 'Mga Ulat at Analytics',
        'business_intelligence_dashboard' => 'Business Intelligence Dashboard',
        'welcome_message' => 'Kumuha ng mga insight sa iyong business performance na may komprehensibong mga ulat at analytics. Subaybayan ang mga benta, imbentaryo, performance ng empleyado, at aktibidad ng customer.',
        'quick_report' => 'Mabilis na Ulat',
        'sales_reports' => 'Mga Ulat ng Benta',
        'inventory_movement' => 'Paggalaw ng Imbentaryo',
        'employee_performance' => 'Performance ng Empleyado',
        'customer_activity' => 'Aktibidad ng Customer',
        'profit_loss' => 'Kita at Pagkawala',
        'export_report' => 'I-export ang Ulat',
        'filter_by_customer' => 'I-filter ayon sa Customer',
        'all_customers' => 'Lahat ng Customer',
        'filter_by_employee' => 'I-filter ayon sa Empleyado',
        'all_employees' => 'Lahat ng Empleyado',
        'filter_by_item' => 'I-filter ayon sa Item',
        'all_items' => 'Lahat ng Item',
        'start_date' => 'Petsa ng Simula',
        'end_date' => 'Petsa ng Katapusan',
        'apply_filters' => 'Ilapat ang Mga Filter',
        'transaction_id' => 'ID ng Transaksyon',
        'date' => 'Petsa',
        'customer' => 'Customer',
        'amount' => 'Halaga',
        'items' => 'Mga Item',
        'points_earned' => 'Mga Puntos na Nakuha',
        'no_customer_activity' => 'Walang nakitang aktibidad ng customer',
        'employee' => 'Empleyado',
        'role' => 'Tungkulin',
        'logins' => 'Mga Login',
        'hours_worked' => 'Mga Oras na Nagtrabaho',
        'inventory_actions' => 'Mga Aksyon sa Imbentaryo',
        'no_employee_activity' => 'Walang nakitang aktibidad ng empleyado',
        'month' => 'Buwan',
        'revenue' => 'Kita',
        'cost' => 'Gastos',
        'profit' => 'Tubo',
        'margin' => 'Margin',
        'no_profit_loss_data' => 'Walang nakitang kita at pagkawala ng data',
        'action' => 'Aksyon',
        'quantity_change' => 'Pagbabago ng Dami',
        'previous_stock' => 'Nakaraang Stock',
        'new_stock' => 'Bagong Stock',
        'reason' => 'Dahilan',
        'no_inventory_movement' => 'Walang nakitang paggalaw ng imbentaryo',
        'total_sales' => 'Kabuuang Benta',
        'change' => 'Pagbabago',
        'no_sales_data' => 'Walang nakitang data ng benta',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Mga User',
        'pricing_control' => 'Kontrol sa Presyo',
        'transactions' => 'Mga Transaksyon',
        'loyalty_program' => 'Programa ng Loyalty',
        'profile' => 'Profile',
        
        'logout' => 'Logout',
        'administrator' => 'Administrator'
    ]
];

$t = $translations[$language];

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'sales';

if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $export_type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';
    
    switch($export_type) {
        case 'sales':
            generateSalesPDF($conn);
            break;
        case 'profit':
            $start_date = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : null;
            $end_date = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : null;
            generateProfitLossPDF($conn, $start_date, $end_date);
            break;
        case 'inventory':
            $item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : null;
            generateInventoryPDF($conn, $item_id);
            break;
        case 'employee':
            $employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : null;
            generateEmployeePDF($conn, $employee_id);
            break;
        case 'customer':
            $customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;
            generateCustomerPDF($conn, $customer_id);
            break;
        case 'quick':
            generateQuickReportPDF($conn);
            break;
    }
    exit();
}

function generateSalesPDF($conn) {
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
    $pdf->Cell(0, 15, 'SALES REPORT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    // Get sales data
    try {
        $stmt = $conn->query("SELECT 
                             DATE_FORMAT(transaction_date, '%Y-%m') as month, 
                             SUM(amount) as total_sales,
                             COUNT(*) as transaction_count
                             FROM transactions
                             WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                             GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
                             ORDER BY transaction_date DESC");
        $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Total statistics
        $total_sales = array_sum(array_column($sales_data, 'total_sales'));
        $total_transactions = array_sum(array_column($sales_data, 'transaction_count'));
        $avg_sale = $total_transactions > 0 ? $total_sales / $total_transactions : 0;
    } catch(PDOException $e) {
        $sales_data = [];
        $total_sales = $total_transactions = $avg_sale = 0;
    }
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Sales:', 0, 0);
    $pdf->Cell(40, 7, 'P' . number_format($total_sales, 2), 0, 1);
    $pdf->Cell(50, 7, 'Total Transactions:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_transactions), 0, 1);
    $pdf->Cell(50, 7, 'Average Sale:', 0, 0);
    $pdf->Cell(40, 7, 'P' . number_format($avg_sale, 2), 0, 1);
    
    // Sales table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(60, 7, 'Month', 1, 0, 'C');
    $pdf->Cell(50, 7, 'Total Sales', 1, 0, 'C');
    $pdf->Cell(50, 7, 'Transactions', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    foreach ($sales_data as $sale) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(60, 7, 'Month', 1, 0, 'C');
            $pdf->Cell(50, 7, 'Total Sales', 1, 0, 'C');
            $pdf->Cell(50, 7, 'Transactions', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 10);
        }
        
        $pdf->Cell(60, 6, date('F Y', strtotime($sale['month'] . '-01')), 1, 0);
        $pdf->Cell(50, 6, 'P' . number_format($sale['total_sales'], 2), 1, 0, 'R');
        $pdf->Cell(50, 6, number_format($sale['transaction_count']), 1, 1, 'C');
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'sales_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateProfitLossPDF($conn, $start_date = null, $end_date = null) {
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
    $pdf->Cell(0, 15, 'PROFIT & LOSS REPORT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    if ($start_date && $end_date) {
        $pdf->Cell(40, 7, 'Period:', 0, 0);
        $pdf->Cell(0, 7, date('M j, Y', strtotime($start_date)) . ' - ' . date('M j, Y', strtotime($end_date)), 0, 1);
    }
    
    // Get profit/loss data
    $query = "SELECT 
                DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
                SUM(t.amount) as revenue,
                SUM(t.amount * 0.7) as cost,
                SUM(t.amount * 0.3) as profit
              FROM transactions t";
    
    if ($start_date && $end_date) {
        $query .= " WHERE t.transaction_date BETWEEN :start_date AND :end_date";
    }
    
    $query .= " GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
                ORDER BY month DESC
                LIMIT 6";
    
    try {
        $stmt = $conn->prepare($query);
        if ($start_date && $end_date) {
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
        }
        $stmt->execute();
        $profit_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_revenue = array_sum(array_column($profit_data, 'revenue'));
        $total_cost = array_sum(array_column($profit_data, 'cost'));
        $total_profit = array_sum(array_column($profit_data, 'profit'));
        $profit_margin = $total_revenue > 0 ? ($total_profit / $total_revenue) * 100 : 0;
    } catch(PDOException $e) {
        $profit_data = [];
        $total_revenue = $total_cost = $total_profit = $profit_margin = 0;
    }
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Revenue:', 0, 0);
    $pdf->Cell(40, 7, 'P' . number_format($total_revenue, 2), 0, 1);
    $pdf->Cell(50, 7, 'Total Cost:', 0, 0);
    $pdf->Cell(40, 7, 'P' . number_format($total_cost, 2), 0, 1);
    $pdf->Cell(50, 7, 'Total Profit:', 0, 0);
    $pdf->Cell(40, 7, 'P' . number_format($total_profit, 2), 0, 1);
    $pdf->Cell(50, 7, 'Profit Margin:', 0, 0);
    $pdf->Cell(40, 7, number_format($profit_margin, 2) . '%', 0, 1);
    
    // Profit/Loss table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Month', 1, 0, 'C');
    $pdf->Cell(35, 7, 'Revenue', 1, 0, 'C');
    $pdf->Cell(35, 7, 'Cost', 1, 0, 'C');
    $pdf->Cell(35, 7, 'Profit', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Margin', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    foreach ($profit_data as $report) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(40, 7, 'Month', 1, 0, 'C');
            $pdf->Cell(35, 7, 'Revenue', 1, 0, 'C');
            $pdf->Cell(35, 7, 'Cost', 1, 0, 'C');
            $pdf->Cell(35, 7, 'Profit', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Margin', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 9);
        }
        
        $margin = $report['revenue'] > 0 ? round(($report['profit'] / $report['revenue']) * 100, 2) : 0;
        
        $pdf->Cell(40, 6, date('F Y', strtotime($report['month'] . '-01')), 1, 0);
        $pdf->Cell(35, 6, 'P' . number_format($report['revenue'], 2), 1, 0, 'R');
        $pdf->Cell(35, 6, 'P' . number_format($report['cost'], 2), 1, 0, 'R');
        $pdf->Cell(35, 6, 'P' . number_format($report['profit'], 2), 1, 0, 'R');
        $pdf->Cell(25, 6, $margin . '%', 1, 1, 'C');
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'profit_loss_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateInventoryPDF($conn, $item_id = null) {
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
    $pdf->Cell(0, 15, 'INVENTORY MOVEMENT REPORT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    // Get inventory data
    $query = "SELECT 
                il.id,
                ii.item_name,
                il.action_type,
                il.quantity_change,
                il.previous_stock,
                il.new_stock,
                il.reason,
                il.created_at,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
              FROM inventory_logs il
              JOIN inventory_items ii ON il.inventory_item_id = ii.id
              LEFT JOIN employees e ON il.user_id = e.id";
    
    if ($item_id) {
        $query .= " WHERE il.inventory_item_id = :item_id";
    }
    
    $query .= " ORDER BY il.created_at DESC LIMIT 50";
    
    try {
        $stmt = $conn->prepare($query);
        if ($item_id) {
            $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        $inventory_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Statistics
        $total_additions = 0;
        $total_deductions = 0;
        foreach ($inventory_data as $item) {
            if ($item['action_type'] === 'addition') {
                $total_additions += $item['quantity_change'];
            } else {
                $total_deductions += $item['quantity_change'];
            }
        }
    } catch(PDOException $e) {
        $inventory_data = [];
        $total_additions = $total_deductions = 0;
    }
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Additions:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_additions, 2) . ' kg', 0, 1);
    $pdf->Cell(50, 7, 'Total Deductions:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_deductions, 2) . ' kg', 0, 1);
    $pdf->Cell(50, 7, 'Net Change:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_additions - $total_deductions, 2) . ' kg', 0, 1);
    
    // Inventory table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(35, 7, 'Item', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Action', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Change', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Prev Stock', 1, 0, 'C');
    $pdf->Cell(25, 7, 'New Stock', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Date', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    foreach ($inventory_data as $movement) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(35, 7, 'Item', 1, 0, 'C');
            $pdf->Cell(20, 7, 'Action', 1, 0, 'C');
            $pdf->Cell(20, 7, 'Change', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Prev Stock', 1, 0, 'C');
            $pdf->Cell(25, 7, 'New Stock', 1, 0, 'C');
            $pdf->Cell(30, 7, 'Date', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 8);
        }
        
        $pdf->Cell(35, 6, substr($movement['item_name'], 0, 20), 1, 0);
        $pdf->Cell(20, 6, ucfirst($movement['action_type']), 1, 0);
        $pdf->Cell(20, 6, number_format($movement['quantity_change'], 2), 1, 0, 'R');
        $pdf->Cell(25, 6, number_format($movement['previous_stock'], 2), 1, 0, 'R');
        $pdf->Cell(25, 6, number_format($movement['new_stock'], 2), 1, 0, 'R');
        $pdf->Cell(30, 6, date('m/d/Y h:i A', strtotime($movement['created_at'])), 1, 1);
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'inventory_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateEmployeePDF($conn, $employee_id = null) {
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
    $pdf->Cell(0, 15, 'EMPLOYEE PERFORMANCE REPORT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    // Get employee data
    $query = "SELECT 
                e.id,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                er.role_name,
                COUNT(al.id) as logins,
                SUM(TIMESTAMPDIFF(HOUR, al.login_time, al.logout_time)) as hours_worked,
                COUNT(il.id) as inventory_actions
              FROM employees e
              JOIN employee_roles er ON e.role_id = er.id
              LEFT JOIN attendance_logs al ON e.id = al.employee_id
              LEFT JOIN inventory_logs il ON e.id = il.user_id";
    
    if ($employee_id) {
        $query .= " WHERE e.id = :employee_id";
    }
    
    $query .= " GROUP BY e.id ORDER BY e.last_name ASC";
    
    try {
        $stmt = $conn->prepare($query);
        if ($employee_id) {
            $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        $employee_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_employees = count($employee_data);
        $total_hours = array_sum(array_column($employee_data, 'hours_worked'));
        $total_actions = array_sum(array_column($employee_data, 'inventory_actions'));
    } catch(PDOException $e) {
        $employee_data = [];
        $total_employees = $total_hours = $total_actions = 0;
    }
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Employees:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_employees), 0, 1);
    $pdf->Cell(50, 7, 'Total Hours Worked:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_hours), 0, 1);
    $pdf->Cell(50, 7, 'Total Actions:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_actions), 0, 1);
    
    // Employee table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(50, 7, 'Employee', 1, 0, 'C');
    $pdf->Cell(35, 7, 'Role', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Logins', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Hours', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Actions', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    foreach ($employee_data as $employee) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(50, 7, 'Employee', 1, 0, 'C');
            $pdf->Cell(35, 7, 'Role', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Logins', 1, 0, 'C');
            $pdf->Cell(30, 7, 'Hours', 1, 0, 'C');
            $pdf->Cell(30, 7, 'Actions', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 9);
        }
        
        $pdf->Cell(50, 6, substr($employee['employee_name'], 0, 25), 1, 0);
        $pdf->Cell(35, 6, substr($employee['role_name'], 0, 18), 1, 0);
        $pdf->Cell(25, 6, number_format($employee['logins']), 1, 0, 'C');
        $pdf->Cell(30, 6, number_format($employee['hours_worked']), 1, 0, 'C');
        $pdf->Cell(30, 6, number_format($employee['inventory_actions']), 1, 1, 'C');
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'employee_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateCustomerPDF($conn, $customer_id = null) {
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
    $pdf->Cell(0, 15, 'CUSTOMER ACTIVITY REPORT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    // Get customer data
    $query = "SELECT 
                t.transaction_id, 
                t.transaction_date, 
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                t.amount,
                GROUP_CONCAT(CONCAT(pm.quantity_kg, 'kg ', m.material_option) SEPARATOR ', ') as items,
                t.points_earned
              FROM transactions t
              JOIN users u ON t.user_id = u.id
              JOIN pickup_materials pm ON t.id = pm.pickup_id
              JOIN materials m ON pm.material_id = m.id";
    
    if ($customer_id) {
        $query .= " WHERE t.user_id = :customer_id";
    }
    
    $query .= " GROUP BY t.id ORDER BY t.transaction_date DESC LIMIT 50";
    
    try {
        $stmt = $conn->prepare($query);
        if ($customer_id) {
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        $customer_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_transactions = count($customer_data);
        $total_amount = array_sum(array_column($customer_data, 'amount'));
        $total_points = array_sum(array_column($customer_data, 'points_earned'));
    } catch(PDOException $e) {
        $customer_data = [];
        $total_transactions = $total_amount = $total_points = 0;
    }
    
    // Statistics section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'SUMMARY STATISTICS', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 7, 'Total Transactions:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_transactions), 0, 1);
    $pdf->Cell(50, 7, 'Total Amount:', 0, 0);
    $pdf->Cell(40, 7, 'P' . number_format($total_amount, 2), 0, 1);
    $pdf->Cell(50, 7, 'Total Points:', 0, 0);
    $pdf->Cell(40, 7, number_format($total_points), 0, 1);
    
    // Customer table
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(30, 7, 'Trans ID', 1, 0, 'C');
    $pdf->Cell(25, 7, 'Date', 1, 0, 'C');
    $pdf->Cell(40, 7, 'Customer', 1, 0, 'C');
    $pdf->Cell(30, 7, 'Amount', 1, 0, 'C');
    $pdf->Cell(20, 7, 'Points', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 8);
    foreach ($customer_data as $activity) {
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(30, 7, 'Trans ID', 1, 0, 'C');
            $pdf->Cell(25, 7, 'Date', 1, 0, 'C');
            $pdf->Cell(40, 7, 'Customer', 1, 0, 'C');
            $pdf->Cell(30, 7, 'Amount', 1, 0, 'C');
            $pdf->Cell(20, 7, 'Points', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 8);
        }
        
        $pdf->Cell(30, 6, $activity['transaction_id'], 1, 0);
        $pdf->Cell(25, 6, date('m/d/Y', strtotime($activity['transaction_date'])), 1, 0);
        $pdf->Cell(40, 6, substr($activity['customer_name'], 0, 20), 1, 0);
        $pdf->Cell(30, 6, 'P' . number_format($activity['amount'], 2), 1, 0, 'R');
        $pdf->Cell(20, 6, number_format($activity['points_earned']), 1, 1, 'C');
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'customer_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}

function generateQuickReportPDF($conn) {
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
    $pdf->Cell(0, 15, 'QUICK BUSINESS REPORT', 0, 1, 'C');
    
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated on:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    
    try {
        // Sales Overview
        $stmt = $conn->query("SELECT 
                             COUNT(*) as total_transactions,
                             SUM(amount) as total_sales,
                             AVG(amount) as avg_sale
                             FROM transactions
                             WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $sales = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Inventory Status
        $stmt = $conn->query("SELECT 
                             COUNT(*) as total_items,
                             SUM(stock_quantity) as total_stock
                             FROM inventory_items");
        $inventory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Customer Stats
        $stmt = $conn->query("SELECT 
                             COUNT(DISTINCT user_id) as active_customers
                             FROM transactions
                             WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $customers = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Employee Stats
        $stmt = $conn->query("SELECT COUNT(*) as total_employees FROM employees");
        $employees = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        $sales = ['total_transactions' => 0, 'total_sales' => 0, 'avg_sale' => 0];
        $inventory = ['total_items' => 0, 'total_stock' => 0];
        $customers = ['active_customers' => 0];
        $employees = ['total_employees' => 0];
    }
    
    // Sales Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(112, 139, 76);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'SALES OVERVIEW (Last 30 Days)', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(70, 7, 'Total Transactions:', 0, 0);
    $pdf->Cell(0, 7, number_format($sales['total_transactions']), 0, 1);
    $pdf->Cell(70, 7, 'Total Sales:', 0, 0);
    $pdf->Cell(0, 7, 'P' . number_format($sales['total_sales'], 2), 0, 1);
    $pdf->Cell(70, 7, 'Average Sale:', 0, 0);
    $pdf->Cell(0, 7, 'P' . number_format($sales['avg_sale'], 2), 0, 1);
    
    // Inventory Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(217, 122, 65);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'INVENTORY STATUS', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(70, 7, 'Total Items:', 0, 0);
    $pdf->Cell(0, 7, number_format($inventory['total_items']), 0, 1);
    $pdf->Cell(70, 7, 'Total Stock:', 0, 0);
    $pdf->Cell(0, 7, number_format($inventory['total_stock'], 2) . ' kg', 0, 1);
    
    // Customer Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(74, 137, 220);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'CUSTOMER ACTIVITY', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(70, 7, 'Active Customers (30 days):', 0, 0);
    $pdf->Cell(0, 7, number_format($customers['active_customers']), 0, 1);
    
    // Employee Section
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(106, 127, 70);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'EMPLOYEE OVERVIEW', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(70, 7, 'Total Employees:', 0, 0);
    $pdf->Cell(0, 7, number_format($employees['total_employees']), 0, 1);
    
    // Recent Transactions
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(60, 52, 44);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'RECENT TRANSACTIONS', 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    
    try {
        $stmt = $conn->query("SELECT 
                             t.transaction_id,
                             t.transaction_date,
                             CONCAT(u.first_name, ' ', u.last_name) as customer,
                             t.amount
                             FROM transactions t
                             JOIN users u ON t.user_id = u.id
                             ORDER BY t.transaction_date DESC
                             LIMIT 10");
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 7, 'Transaction ID', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Date', 1, 0, 'C');
        $pdf->Cell(60, 7, 'Customer', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Amount', 1, 1, 'C');
        
        $pdf->SetFont('Arial', '', 9);
        foreach ($recent as $trans) {
            $pdf->Cell(40, 6, $trans['transaction_id'], 1, 0);
            $pdf->Cell(30, 6, date('m/d/Y', strtotime($trans['transaction_date'])), 1, 0);
            $pdf->Cell(60, 6, substr($trans['customer'], 0, 30), 1, 0);
            $pdf->Cell(30, 6, 'P' . number_format($trans['amount'], 2), 1, 1, 'R');
        }
    } catch(PDOException $e) {
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 7, 'No recent transactions found', 0, 1);
    }
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 7, 'Generated by JunkValue Management System', 0, 1, 'C');
    
    $filename = 'quick_report_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($filename, 'D');
}

// Function to get customer activity data
function getCustomerActivity($conn, $customer_id = null) {
    $query = "SELECT 
                t.transaction_id, 
                t.transaction_date, 
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                t.amount,
                GROUP_CONCAT(CONCAT(pm.quantity_kg, 'kg ', m.material_option) SEPARATOR ', ') as items,
                t.points_earned
              FROM transactions t
              JOIN users u ON t.user_id = u.id
              JOIN pickup_materials pm ON t.id = pm.pickup_id
              JOIN materials m ON pm.material_id = m.id";
    
    if ($customer_id) {
        $query .= " WHERE t.user_id = :customer_id";
    }
    
    $query .= " GROUP BY t.id
                ORDER BY t.transaction_date DESC
                LIMIT 10";
    
    $stmt = $conn->prepare($query);
    
    if ($customer_id) {
        $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get employee activity data
function getEmployeeActivity($conn, $employee_id = null) {
    $query = "SELECT 
                e.id,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                er.role_name,
                COUNT(al.id) as logins,
                SUM(TIMESTAMPDIFF(HOUR, al.login_time, al.logout_time)) as hours_worked,
                COUNT(il.id) as inventory_actions
              FROM employees e
              JOIN employee_roles er ON e.role_id = er.id
              LEFT JOIN attendance_logs al ON e.id = al.employee_id
              LEFT JOIN inventory_logs il ON e.id = il.user_id";
    
    if ($employee_id) {
        $query .= " WHERE e.id = :employee_id";
    }
    
    $query .= " GROUP BY e.id
                ORDER BY e.last_name ASC";
    
    $stmt = $conn->prepare($query);
    
    if ($employee_id) {
        $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get profit/loss data
function getProfitLossData($conn, $start_date = null, $end_date = null) {
    $query = "SELECT 
                DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
                SUM(t.amount) as revenue,
                SUM(t.amount * 0.7) as cost, -- Assuming 30% margin
                SUM(t.amount * 0.3) as profit
              FROM transactions t";
    
    if ($start_date && $end_date) {
        $query .= " WHERE t.transaction_date BETWEEN :start_date AND :end_date";
    }
    
    $query .= " GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
                ORDER BY month DESC
                LIMIT 6";
    
    $stmt = $conn->prepare($query);
    
    if ($start_date && $end_date) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get inventory movement
function getInventoryMovement($conn, $item_id = null) {
    $query = "SELECT 
                il.id,
                ii.item_name,
                il.action_type,
                il.quantity_change,
                il.previous_stock,
                il.new_stock,
                il.reason,
                il.created_at,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
              FROM inventory_logs il
              JOIN inventory_items ii ON il.inventory_item_id = ii.id
              LEFT JOIN employees e ON il.user_id = e.id";
    
    if ($item_id) {
        $query .= " WHERE il.inventory_item_id = :item_id";
    }
    
    $query .= " ORDER BY il.created_at DESC
                LIMIT 10";
    
    $stmt = $conn->prepare($query);
    
    if ($item_id) {
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get data based on current tab
$customer_activity = [];
$employee_activity = [];
$profit_loss = [];
$inventory_movement = [];

try {
    if ($current_tab === 'customer') {
        $customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        $customer_activity = getCustomerActivity($conn, $customer_id);
    } elseif ($current_tab === 'employee') {
        $employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : null;
        $employee_activity = getEmployeeActivity($conn, $employee_id);
    } elseif ($current_tab === 'profit') {
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
        $profit_loss = getProfitLossData($conn, $start_date, $end_date);
    } elseif ($current_tab === 'inventory') {
        $item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : null;
        $inventory_movement = getInventoryMovement($conn, $item_id);
    }
} catch(PDOException $e) {
    error_log("Data query failed: " . $e->getMessage());
    die("Error loading report data.");
}

// Get data for charts
$sales_chart_data = [];
$inventory_chart_data = [];
$employee_chart_data = [];
$customer_chart_data = [];

try {
    // Last 6 months sales data
    $stmt = $conn->query("SELECT 
                         DATE_FORMAT(transaction_date, '%Y-%m') as month, 
                         SUM(amount) as total_sales
                         FROM transactions
                         WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                         GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
                         ORDER BY transaction_date");
    $sales_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Inventory movement data
    $stmt = $conn->query("SELECT 
                         DATE_FORMAT(created_at, '%Y-%m') as month,
                         SUM(CASE WHEN action_type = 'addition' THEN quantity_change ELSE 0 END) as incoming,
                         SUM(CASE WHEN action_type = 'deduction' THEN quantity_change ELSE 0 END) as outgoing
                         FROM inventory_logs
                         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                         GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                         ORDER BY created_at");
    $inventory_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top 5 customers by spending
    $stmt = $conn->query("SELECT 
                         CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                         SUM(t.amount) as total_spent
                         FROM transactions t
                         JOIN users u ON t.user_id = u.id
                         WHERE t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                         GROUP BY t.user_id
                         ORDER BY total_spent DESC
                         LIMIT 5");
    $customer_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Employee activity
    $stmt = $conn->query("SELECT 
                         CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                         COUNT(al.id) as logins
                         FROM employees e
                         LEFT JOIN attendance_logs al ON e.id = al.employee_id
                         WHERE al.login_time >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                         GROUP BY e.id
                         ORDER BY logins DESC
                         LIMIT 5");
    $employee_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Chart data query failed: " . $e->getMessage());
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
    <title>JunkValue - <?php echo $t['reports_analytics']; ?></title>
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

    /* Additional styles for reports page */
    .filter-controls {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-group label {
        font-weight: 500;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .filter-group label {
        color: var(--dark-text-primary);
    }

    .filter-group select, 
    .filter-group input {
        padding: 8px 12px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 6px;
        background-color: white;
        color: var(--text-dark);
        transition: all 0.3s ease;
    }

    body.dark-mode .filter-group select,
    body.dark-mode .filter-group input {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .filter-group button {
        padding: 8px 15px;
        background-color: var(--icon-green);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-group button:hover {
        background-color: #5A6F3D;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
        background-color: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .chart-container {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .chart-container.doughnut {
        height: 300px;
        width: 100%;
        max-width: 400px;
        margin: 0 auto 20px;
    }

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

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideDown {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
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
        
        .filter-controls {
            flex-direction: column;
        }
        
        .filter-group {
            width: 100%;
        }
        
        .filter-group select, 
        .filter-group input {
            flex: 1;
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
            <li><a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> <?php echo $t['reports_analytics']; ?></a></li>
            <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> <?php echo $t['transactions']; ?></a></li>
             <li><a href="loyalty.php" ><i class="fas fa-award"></i> <?php echo $t['loyalty_program']; ?></a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> <?php echo $t['profile']; ?></a></li>

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
            <h1 class="page-title"><?php echo $t['reports_analytics']; ?></h1>
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
                <h2><?php echo $t['business_intelligence_dashboard']; ?></h2>
                <p><?php echo $t['welcome_message']; ?></p>
                <!-- Updated Quick Report button to trigger PDF export -->
                <button class="btn btn-primary" onclick="window.location.href='reports.php?export=pdf&type=quick'" style="display: inline-flex; align-items: center; gap: 10px;">
                    <i class="fas fa-file-alt"></i> <?php echo $t['quick_report']; ?>
                </button>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
        </div>
        
        <!-- Reports Tabs -->
        <div class="inventory-tabs">
            <a href="reports.php?tab=sales" class="inventory-tab <?php echo $current_tab === 'sales' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> <?php echo $t['sales_reports']; ?>
            </a>
            <a href="reports.php?tab=inventory" class="inventory-tab <?php echo $current_tab === 'inventory' ? 'active' : ''; ?>">
                <i class="fas fa-boxes"></i> <?php echo $t['inventory_movement']; ?>
            </a>
            <a href="reports.php?tab=employee" class="inventory-tab <?php echo $current_tab === 'employee' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> <?php echo $t['employee_performance']; ?>
            </a>
            <a href="reports.php?tab=customer" class="inventory-tab <?php echo $current_tab === 'customer' ? 'active' : ''; ?>">
                <i class="fas fa-user-tag"></i> <?php echo $t['customer_activity']; ?>
            </a>
            <a href="reports.php?tab=profit" class="inventory-tab <?php echo $current_tab === 'profit' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i> <?php echo $t['profit_loss']; ?>
            </a>
        </div>
        
        <?php if ($current_tab === 'customer'): ?>
            <!-- Customer Activity Tab -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-tag"></i> <?php echo $t['customer_activity']; ?></h3>
                    <div class="card-actions">
                        <!-- Updated export button to trigger PDF export -->
                        <button class="btn btn-primary" onclick="exportCustomerReport()">
                            <i class="fas fa-download"></i> <?php echo $t['export_report']; ?>
                        </button>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="customer-select"><?php echo $t['filter_by_customer']; ?>:</label>
                        <select id="customer-select" class="form-control" onchange="filterCustomerActivity()">
                            <option value=""><?php echo $t['all_customers']; ?></option>
                            <?php 
                            try {
                                $customers = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE user_type != 'employee' ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($customers as $customer) {
                                    $selected = isset($_GET['customer_id']) && $_GET['customer_id'] == $customer['id'] ? 'selected' : '';
                                    echo '<option value="' . $customer['id'] . '" ' . $selected . '>' . htmlspecialchars($customer['name']) . '</option>';
                                }
                            } catch(PDOException $e) {
                                error_log("Customers query failed: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="chart-container doughnut">
                    <canvas id="customerChart"></canvas>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $t['transaction_id']; ?></th>
                                <th><?php echo $t['date']; ?></th>
                                <th><?php echo $t['customer']; ?></th>
                                <th><?php echo $t['amount']; ?></th>
                                <th><?php echo $t['items']; ?></th>
                                <th><?php echo $t['points_earned']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($customer_activity)): ?>
                                <?php foreach ($customer_activity as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['transaction_id']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($activity['transaction_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($activity['customer_name']); ?></td>
                                        <td>₱<?php echo number_format($activity['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($activity['items']); ?></td>
                                        <td><?php echo number_format($activity['points_earned']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_customer_activity']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php elseif ($current_tab === 'employee'): ?>
            <!-- Employee Performance Tab -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> <?php echo $t['employee_performance']; ?></h3>
                    <div class="card-actions">
                        <!-- Updated export button to trigger PDF export -->
                        <button class="btn btn-primary" onclick="exportEmployeeReport()">
                            <i class="fas fa-download"></i> <?php echo $t['export_report']; ?>
                        </button>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="employee-select"><?php echo $t['filter_by_employee']; ?>:</label>
                        <select id="employee-select" class="form-control" onchange="filterEmployeeActivity()">
                            <option value=""><?php echo $t['all_employees']; ?></option>
                            <?php 
                            try {
                                $employees = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM employees ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($employees as $employee) {
                                    $selected = isset($_GET['employee_id']) && $_GET['employee_id'] == $employee['id'] ? 'selected' : '';
                                    echo '<option value="' . $employee['id'] . '" ' . $selected . '>' . htmlspecialchars($employee['name']) . '</option>';
                                }
                            } catch(PDOException $e) {
                                error_log("Employees query failed: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="employeeChart"></canvas>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $t['employee']; ?></th>
                                <th><?php echo $t['role']; ?></th>
                                <th><?php echo $t['logins']; ?></th>
                                <th><?php echo $t['hours_worked']; ?></th>
                                <th><?php echo $t['inventory_actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($employee_activity)): ?>
                                <?php foreach ($employee_activity as $employee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($employee['employee_name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['role_name']); ?></td>
                                        <td><?php echo number_format($employee['logins']); ?></td>
                                        <td><?php echo number_format($employee['hours_worked']); ?></td>
                                        <td><?php echo number_format($employee['inventory_actions']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_employee_activity']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php elseif ($current_tab === 'profit'): ?>
            <!-- Profit & Loss Tab -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> <?php echo $t['profit_loss']; ?></h3>
                    <div class="card-actions">
                        <!-- Updated export button to trigger PDF export -->
                        <button class="btn btn-primary" onclick="exportProfitLossReport()">
                            <i class="fas fa-download"></i> <?php echo $t['export_report']; ?>
                        </button>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="start-date"><?php echo $t['start_date']; ?>:</label>
                        <input type="date" id="start-date" class="form-control" 
                               value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                    </div>
                    <div class="filter-group">
                        <label for="end-date"><?php echo $t['end_date']; ?>:</label>
                        <input type="date" id="end-date" class="form-control" 
                               value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                    </div>
                    <div class="filter-group">
                        <button class="btn btn-primary" onclick="filterProfitLoss()">
                            <i class="fas fa-filter"></i> <?php echo $t['apply_filters']; ?>
                        </button>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="profitLossChart"></canvas>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $t['month']; ?></th>
                                <th><?php echo $t['revenue']; ?></th>
                                <th><?php echo $t['cost']; ?></th>
                                <th><?php echo $t['profit']; ?></th>
                                <th><?php echo $t['margin']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($profit_loss)): ?>
                                <?php foreach ($profit_loss as $report): ?>
                                    <tr>
                                        <td><?php echo date('F Y', strtotime($report['month'] . '-01')); ?></td>
                                        <td>₱<?php echo number_format($report['revenue'], 2); ?></td>
                                        <td>₱<?php echo number_format($report['cost'], 2); ?></td>
                                        <td>₱<?php echo number_format($report['profit'], 2); ?></td>
                                        <td><?php echo $report['revenue'] > 0 ? round(($report['profit'] / $report['revenue']) * 100, 2) . '%' : '0%'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_profit_loss_data']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php elseif ($current_tab === 'inventory'): ?>
            <!-- Inventory Movement Tab -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-boxes"></i> <?php echo $t['inventory_movement']; ?></h3>
                    <div class="card-actions">
                        <!-- Updated export button to trigger PDF export -->
                        <button class="btn btn-primary" onclick="exportInventoryReport()">
                            <i class="fas fa-download"></i> <?php echo $t['export_report']; ?>
                        </button>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="item-select"><?php echo $t['filter_by_item']; ?>:</label>
                        <select id="item-select" class="form-control" onchange="filterInventoryMovement()">
                            <option value=""><?php echo $t['all_items']; ?></option>
                            <?php 
                            try {
                                $items = $conn->query("SELECT id, item_name FROM inventory_items ORDER BY item_name")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($items as $item) {
                                    $selected = isset($_GET['item_id']) && $_GET['item_id'] == $item['id'] ? 'selected' : '';
                                    echo '<option value="' . $item['id'] . '" ' . $selected . '>' . htmlspecialchars($item['item_name']) . '</option>';
                                }
                            } catch(PDOException $e) {
                                error_log("Items query failed: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="inventoryChart"></canvas>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $t['items']; ?></th>
                                <th><?php echo $t['action']; ?></th>
                                <th><?php echo $t['quantity_change']; ?></th>
                                <th><?php echo $t['previous_stock']; ?></th>
                                <th><?php echo $t['new_stock']; ?></th>
                                <th><?php echo $t['reason']; ?></th>
                                <th><?php echo $t['date']; ?></th>
                                <th><?php echo $t['employee']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($inventory_movement)): ?>
                                <?php foreach ($inventory_movement as $movement): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($movement['item_name']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $movement['action_type'] === 'addition' ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo ucfirst($movement['action_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($movement['quantity_change'], 2); ?></td>
                                        <td><?php echo number_format($movement['previous_stock'], 2); ?></td>
                                        <td><?php echo number_format($movement['new_stock'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($movement['reason']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($movement['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($movement['employee_name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_inventory_movement']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php elseif ($current_tab === 'sales'): ?>
            <!-- Sales Reports Tab -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shopping-cart"></i> <?php echo $t['sales_reports']; ?></h3>
                    <div class="card-actions">
                        <!-- Updated export button to trigger PDF export -->
                        <button class="btn btn-primary" onclick="exportSalesReport()">
                            <i class="fas fa-download"></i> <?php echo $t['export_report']; ?>
                        </button>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?php echo $t['month']; ?></th>
                                <th><?php echo $t['total_sales']; ?></th>
                                <th><?php echo $t['change']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sales_chart_data)): ?>
                                <?php 
                                $prev_sales = null;
                                foreach ($sales_chart_data as $index => $sales): 
                                    $change = null;
                                    if ($prev_sales !== null) {
                                        $change = (($sales['total_sales'] - $prev_sales) / $prev_sales) * 100;
                                    }
                                    $prev_sales = $sales['total_sales'];
                                ?>
                                    <tr>
                                        <td><?php echo date('F Y', strtotime($sales['month'] . '-01')); ?></td>
                                        <td>₱<?php echo number_format($sales['total_sales'], 2); ?></td>
                                        <td>
                                            <?php if ($change !== null): ?>
                                                <span class="<?php echo $change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $change >= 0 ? '+' : ''; ?>
                                                    <?php echo number_format($change, 2); ?>%
                                                    <i class="fas fa-arrow-<?php echo $change >= 0 ? 'up' : 'down'; ?>"></i>
                                                </span>
                                            <?php else: ?>
                                                <span>N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_sales_data']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

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

        // Chart variables
        let salesChart, inventoryChart, employeeChart, customerChart, profitLossChart;
        let chartUpdateInterval = 30000; // 30 seconds

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            startChartUpdates();
        });

        function initializeCharts() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart')?.getContext('2d');
            if (salesCtx) {
                salesChart = new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_map(function($item) { 
                            return date('M Y', strtotime($item['month'] . '-01')); 
                        }, $sales_chart_data)); ?>,
                        datasets: [{
                            label: 'Total Sales',
                            data: <?php echo json_encode(array_column($sales_chart_data, 'total_sales')); ?>,
                            borderColor: 'rgba(217, 122, 65, 1)',
                            backgroundColor: 'rgba(217, 122, 65, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Sales Trend (Last 6 Months)',
                                font: {
                                    size: 14
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Sales: ₱' + context.raw.toLocaleString();
                                    }
                                }
                            },
                            legend: {
                                labels: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    },
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Inventory Chart
            const inventoryCtx = document.getElementById('inventoryChart')?.getContext('2d');
            if (inventoryCtx) {
                inventoryChart = new Chart(inventoryCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_map(function($item) { 
                            return date('M Y', strtotime($item['month'] . '-01')); 
                        }, $inventory_chart_data)); ?>,
                        datasets: [
                            {
                                label: 'Incoming',
                                data: <?php echo json_encode(array_column($inventory_chart_data, 'incoming')); ?>,
                                backgroundColor: 'rgba(112, 139, 76, 0.7)'
                            },
                            {
                                label: 'Outgoing',
                                data: <?php echo json_encode(array_column($inventory_chart_data, 'outgoing')); ?>,
                                backgroundColor: 'rgba(217, 122, 65, 0.7)'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Inventory Movement (Last 6 Months)',
                                font: {
                                    size: 14
                                }
                            },
                            legend: {
                                labels: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Employee Chart
            const employeeCtx = document.getElementById('employeeChart')?.getContext('2d');
            if (employeeCtx) {
                employeeChart = new Chart(employeeCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($employee_chart_data, 'employee_name')); ?>,
                        datasets: [{
                            label: 'Logins',
                            data: <?php echo json_encode(array_column($employee_chart_data, 'logins')); ?>,
                            backgroundColor: [
                                'rgba(217, 122, 65, 0.7)',
                                'rgba(112, 139, 76, 0.7)',
                                'rgba(74, 137, 220, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(255, 159, 64, 0.7)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Top Employees by Logins (Last 3 Months)',
                                font: {
                                    size: 14
                                }
                            },
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Customer Chart
            const customerCtx = document.getElementById('customerChart')?.getContext('2d');
            if (customerCtx) {
                customerChart = new Chart(customerCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($customer_chart_data, 'customer_name')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($customer_chart_data, 'total_spent')); ?>,
                            backgroundColor: [
                                'rgba(112, 139, 76, 0.7)',
                                'rgba(74, 137, 220, 0.7)',
                                'rgba(217, 122, 65, 0.7)',
                                'rgba(220, 53, 69, 0.7)',
                                'rgba(255, 159, 64, 0.7)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Top Customers by Spending (Last 3 Months)',
                                font: {
                                    size: 14
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '₱' + context.raw.toLocaleString();
                                    }
                                }
                            },
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Profit & Loss Chart
            const profitLossCtx = document.getElementById('profitLossChart')?.getContext('2d');
            if (profitLossCtx) {
                profitLossChart = new Chart(profitLossCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php 
                            $profit_months = [];
                            foreach ($profit_loss as $report) {
                                $profit_months[] = date('M Y', strtotime($report['month'] . '-01'));
                            }
                            echo json_encode($profit_months);
                        ?>,
                        datasets: [
                            {
                                label: 'Revenue',
                                data: <?php echo json_encode(array_column($profit_loss, 'revenue')); ?>,
                                backgroundColor: 'rgba(112, 139, 76, 0.7)'
                            },
                            {
                                label: 'Profit',
                                data: <?php echo json_encode(array_column($profit_loss, 'profit')); ?>,
                                backgroundColor: 'rgba(74, 137, 220, 0.7)'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Revenue & Profit Trend',
                                font: {
                                    size: 14
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ₱' + context.raw.toLocaleString();
                                    }
                                }
                            },
                            legend: {
                                labels: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    },
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Start periodic chart updates
        function startChartUpdates() {
            setInterval(updateCharts, chartUpdateInterval);
        }

        // Update all charts with fresh data
        function updateCharts() {
            fetch('get_chart_data.php?tab=<?php echo $current_tab; ?>')
                .then(response => response.json())
                .then(data => {
                    if (salesChart && data.sales) {
                        salesChart.data.labels = data.sales.labels;
                        salesChart.data.datasets[0].data = data.sales.data;
                        salesChart.update();
                    }
                    
                    if (inventoryChart && data.inventory) {
                        inventoryChart.data.labels = data.inventory.labels;
                        inventoryChart.data.datasets[0].data = data.inventory.incoming;
                        inventoryChart.data.datasets[1].data = data.inventory.outgoing;
                        inventoryChart.update();
                    }
                    
                    if (employeeChart && data.employee) {
                        employeeChart.data.labels = data.employee.labels;
                        employeeChart.data.datasets[0].data = data.employee.data;
                        employeeChart.update();
                    }
                    
                    if (customerChart && data.customer) {
                        customerChart.data.labels = data.customer.labels;
                        customerChart.data.datasets[0].data = data.customer.data;
                        customerChart.update();
                    }
                    
                    if (profitLossChart && data.profitLoss) {
                        profitLossChart.data.labels = data.profitLoss.labels;
                        profitLossChart.data.datasets[0].data = data.profitLoss.revenue;
                        profitLossChart.data.datasets[1].data = data.profitLoss.profit;
                        profitLossChart.update();
                    }
                })
                .catch(error => console.error('Error updating charts:', error));
        }

        // Filter functions
        function filterCustomerActivity() {
            const customerId = document.getElementById('customer-select').value;
            let url = 'reports.php?tab=customer';
            
            if (customerId) {
                url += '&customer_id=' + customerId;
            }
            
            window.location.href = url;
        }
        
        function filterEmployeeActivity() {
            const employeeId = document.getElementById('employee-select').value;
            let url = 'reports.php?tab=employee';
            
            if (employeeId) {
                url += '&employee_id=' + employeeId;
            }
            
            window.location.href = url;
        }
        
        function filterInventoryMovement() {
            const itemId = document.getElementById('item-select').value;
            let url = 'reports.php?tab=inventory';
            
            if (itemId) {
                url += '&item_id=' + itemId;
            }
            
            window.location.href = url;
        }
        
        function filterProfitLoss() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            let url = 'reports.php?tab=profit';
            
            if (startDate && endDate) {
                url += '&start_date=' + startDate + '&end_date=' + endDate;
            }
            
            window.location.href = url;
        }
        
        function exportSalesReport() {
            window.location.href = 'reports.php?export=pdf&type=sales';
        }
        
        function exportProfitLossReport() {
            const startDate = document.getElementById('start-date')?.value || '';
            const endDate = document.getElementById('end-date')?.value || '';
            let url = 'reports.php?export=pdf&type=profit';
            
            if (startDate && endDate) {
                url += '&start_date=' + startDate + '&end_date=' + endDate;
            }
            
            window.location.href = url;
        }
        
        function exportInventoryReport() {
            const itemId = document.getElementById('item-select')?.value || '';
            let url = 'reports.php?export=pdf&type=inventory';
            
            if (itemId) {
                url += '&item_id=' + itemId;
            }
            
            window.location.href = url;
        }
        
        function exportEmployeeReport() {
            const employeeId = document.getElementById('employee-select')?.value || '';
            let url = 'reports.php?export=pdf&type=employee';
            
            if (employeeId) {
                url += '&employee_id=' + employeeId;
            }
            
            window.location.href = url;
        }
        
        function exportCustomerReport() {
            const customerId = document.getElementById('customer-select')?.value || '';
            let url = 'reports.php?export=pdf&type=customer';
            
            if (customerId) {
                url += '&customer_id=' + customerId;
            }
            
            window.location.href = url;
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