<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db_connection.php';

try {
    $query = "SELECT 
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                u.username,
                u.email,
                u.phone,
                COALESCE(SUM(t.amount), 0) as total_spent,
                MAX(t.created_at) as last_transaction
              FROM users u 
              LEFT JOIN transactions t ON u.id = t.user_id 
              GROUP BY u.id 
              ORDER BY u.created_at DESC 
              LIMIT 100";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'customer_name' => $row['customer_name'],
            'username' => $row['username'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'total_spent' => floatval($row['total_spent']),
            'last_transaction' => $row['last_transaction']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $customers,
        'count' => count($customers)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>