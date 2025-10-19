<?php
session_start();
require_once 'db_connection.php';
require_once 'fpdf/fpdf.php';

// Authentication check
if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $employee_id = $_SESSION['employee_id'];
    $report_name = htmlspecialchars(trim($_POST['report_name']), ENT_QUOTES, 'UTF-8');
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Get sales data
    $sales_query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(amount) as total_sales,
                    SUM(CASE WHEN type = 'Purchase' THEN amount ELSE 0 END) as total_purchases,
                    SUM(CASE WHEN type = 'Sale' THEN amount ELSE 0 END) as total_sales_amount,
                    AVG(amount) as avg_transaction
                    FROM transactions 
                    WHERE created_by = ? 
                    AND transaction_date BETWEEN ? AND ? 
                    AND status = 'Completed'";
    
    $stmt = $conn->prepare($sales_query);
    $stmt->bind_param("iss", $employee_id, $start_date, $end_date);
    $stmt->execute();
    $sales_data = $stmt->get_result()->fetch_assoc();
    
    // Calculate estimated profit
    $estimated_profit = $sales_data['total_sales_amount'] - ($sales_data['total_purchases'] * 0.8);
    
    // Save report to database
    $insert_report = "INSERT INTO sales_reports 
                     (report_name, start_date, end_date, total_sales, total_profit, items_sold, created_by) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_report);
    $stmt->bind_param("sssddii", $report_name, $start_date, $end_date, 
                     $sales_data['total_sales'], $estimated_profit, 
                     $sales_data['total_transactions'], $employee_id);
    $stmt->execute();
    
    // <CHANGE> Fixed: insert_id is a property, not a method - removed parentheses
    $report_id = $conn->insert_id;
    
    // Get employee info
    $emp_query = "SELECT CONCAT(first_name, ' ', last_name) as employee_name FROM employees WHERE id = ?";
    $stmt = $conn->prepare($emp_query);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $emp_result = $stmt->get_result()->fetch_assoc();
    
    // Get transaction details
    $transactions_query = "SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name
                           FROM transactions t
                           LEFT JOIN users u ON t.user_id = u.id
                           WHERE t.created_by = ?
                           AND t.transaction_date BETWEEN ? AND ?
                           AND t.status = 'Completed'
                           ORDER BY t.transaction_date DESC, t.transaction_time DESC";
    $stmt = $conn->prepare($transactions_query);
    $stmt->bind_param("iss", $employee_id, $start_date, $end_date);
    $stmt->execute();
    $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Create PDF
    class PDF extends FPDF
    {
        function Header()
        {
            $this->SetFont('Arial', 'B', 20);
            $this->SetTextColor(60, 52, 44);
            $this->Cell(0, 10, 'JunkValue Sales Report', 0, 1, 'C');
            $this->Ln(5);
        }
        
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(128, 128, 128);
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
        
        function ReportTitle($title)
        {
            $this->SetFont('Arial', 'B', 16);
            $this->SetFillColor(230, 216, 195);
            $this->SetTextColor(60, 52, 44);
            $this->Cell(0, 10, $title, 0, 1, 'L', true);
            $this->Ln(5);
        }
        
        function InfoRow($label, $value)
        {
            $this->SetFont('Arial', 'B', 11);
            $this->SetTextColor(60, 52, 44);
            $this->Cell(50, 8, $label . ':', 0, 0);
            $this->SetFont('Arial', '', 11);
            $this->Cell(0, 8, $value, 0, 1);
        }
        
        function SummaryBox($label, $value, $color)
        {
            $this->SetFillColor($color[0], $color[1], $color[2]);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, $label, 1, 1, 'C', true);
            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 12, $value, 1, 1, 'C', true);
            $this->Ln(3);
        }
        
        function TransactionTable($header, $data)
        {
            $this->SetFillColor(106, 127, 70);
            $this->SetTextColor(255, 255, 255);
            $this->SetDrawColor(106, 127, 70);
            $this->SetLineWidth(0.3);
            $this->SetFont('Arial', 'B', 9);
            
            $w = array(35, 30, 25, 50, 30, 20);
            for($i = 0; $i < count($header); $i++) {
                $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            $this->SetFillColor(242, 234, 211);
            $this->SetTextColor(46, 43, 41);
            $this->SetFont('Arial', '', 8);
            
            $fill = false;
            foreach($data as $row) {
                $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
                $this->Cell($w[1], 6, $row[1], 'LR', 0, 'C', $fill);
                $this->Cell($w[2], 6, $row[2], 'LR', 0, 'C', $fill);
                $this->Cell($w[3], 6, substr($row[3], 0, 25), 'LR', 0, 'L', $fill);
                $this->Cell($w[4], 6, $row[4], 'LR', 0, 'R', $fill);
                $this->Cell($w[5], 6, $row[5], 'LR', 0, 'C', $fill);
                $this->Ln();
                $fill = !$fill;
            }
            $this->Cell(array_sum($w), 0, '', 'T');
        }
    }
    
    // Create PDF instance
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 20);
    
    // Report Information
    $pdf->ReportTitle($report_name);
    $pdf->InfoRow('Generated By', $emp_result['employee_name']);
    $pdf->InfoRow('Report Period', date('M j, Y', strtotime($start_date)) . ' - ' . date('M j, Y', strtotime($end_date)));
    $pdf->InfoRow('Generated On', date('M j, Y h:i A'));
    $pdf->Ln(5);
    
    // Summary Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(60, 52, 44);
    $pdf->Cell(0, 10, 'Summary', 0, 1);
    $pdf->Ln(2);
    
    $pdf->SummaryBox('Total Sales', 'PHP ' . number_format($sales_data['total_sales'], 2), array(217, 122, 65));
    $pdf->SummaryBox('Total Profit', 'PHP ' . number_format($estimated_profit, 2), array(112, 139, 76));
    $pdf->SummaryBox('Items Sold', number_format($sales_data['total_transactions']), array(74, 137, 220));
    
    $pdf->Ln(5);
    
    // Transaction Details
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(60, 52, 44);
    $pdf->Cell(0, 10, 'Transaction Details', 0, 1);
    $pdf->Ln(2);
    
    if (!empty($transactions)) {
        $header = array('Transaction ID', 'Date', 'Type', 'Customer', 'Amount', 'Points');
        $data = array();
        
        foreach ($transactions as $transaction) {
            $data[] = array(
                $transaction['transaction_id'],
                date('M j, Y', strtotime($transaction['transaction_date'])),
                $transaction['type'],
                $transaction['customer_name'] ?? 'N/A',
                'PHP ' . number_format($transaction['amount'], 2),
                $transaction['points_earned']
            );
        }
        
        $pdf->TransactionTable($header, $data);
    } else {
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 10, 'No transactions found for this period', 0, 1, 'C');
    }
    
    // Output PDF for download
    $pdf->Output('D', 'Sales_Report_' . $report_name . '_' . date('Y-m-d') . '.pdf');
    exit();
}
?>