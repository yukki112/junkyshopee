<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $query = "SELECT 
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
    LIMIT 10";

    $result = $conn->query($query);
    $transactions = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Transactions retrieved successfully',
        'data' => $transactions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
