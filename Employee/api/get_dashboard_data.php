<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

// Check authorization
$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Verify token and get employee ID (implement your token verification logic)
// For now, we'll assume the token is valid
$employee_id = 1; // This should come from token verification

try {
    // Get employee performance data
    $performance_query = "SELECT 
        sales_amount,
        items_processed,
        customer_interactions,
        efficiency_rating,
        period_start,
        period_end
    FROM employee_performance
    WHERE employee_id = ?
    ORDER BY period_end DESC
    LIMIT 1";

    $performance_stmt = $conn->prepare($performance_query);
    $performance_stmt->bind_param("i", $employee_id);
    $performance_stmt->execute();
    $performance_result = $performance_stmt->get_result();
    $performance = $performance_result->fetch_assoc();

    // Get materials
    $materials_query = "SELECT 
        m.id, 
        m.material_option, 
        m.unit_price, 
        m.weight_unit,
        m.trend_direction,
        m.trend_change,
        m.updated_at,
        ic.category_name
    FROM materials m
    LEFT JOIN item_categories ic ON m.category_id = ic.id
    WHERE m.status = 'active'
    ORDER BY ic.category_name, m.material_option
    LIMIT 10";

    $materials_result = $conn->query($materials_query);
    $materials = [];
    if ($materials_result) {
        while ($row = $materials_result->fetch_assoc()) {
            $materials[] = $row;
        }
    }

    // Get recent transactions
    $transactions_query = "SELECT 
        t.transaction_id,
        t.transaction_type,
        t.transaction_date,
        t.transaction_time,
        t.item_details,
        t.status,
        t.amount,
        u.first_name,
        u.last_name
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.transaction_date DESC, t.transaction_time DESC
    LIMIT 5";

    $transactions_result = $conn->query($transactions_query);
    $transactions = [];
    if ($transactions_result) {
        while ($row = $transactions_result->fetch_assoc()) {
            $transactions[] = $row;
        }
    }

    // Get recent price changes
    $price_changes_query = "SELECT 
        ph.material_id,
        m.material_option,
        ph.old_price,
        ph.new_price,
        ph.change_date,
        ph.reason,
        e.first_name,
        e.last_name
    FROM price_history ph
    JOIN materials m ON ph.material_id = m.id
    JOIN employees e ON ph.changed_by = e.id
    ORDER BY ph.change_date DESC
    LIMIT 5";

    $price_changes_result = $conn->query($price_changes_query);
    $price_changes = [];
    if ($price_changes_result) {
        while ($row = $price_changes_result->fetch_assoc()) {
            $price_changes[] = $row;
        }
    }

    $data = [
        'sales_this_week' => $performance['sales_amount'] ?? 0,
        'items_processed' => $performance['items_processed'] ?? 0,
        'efficiency_rating' => $performance['efficiency_rating'] ?? 0,
        'customer_interactions' => $performance['customer_interactions'] ?? 0,
        'materials' => $materials,
        'recent_transactions' => $transactions,
        'price_changes' => $price_changes
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Dashboard data retrieved successfully',
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
