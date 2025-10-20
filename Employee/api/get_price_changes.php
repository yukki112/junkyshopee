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
    LIMIT 10";

    $result = $conn->query($query);
    $price_changes = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $price_changes[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Price changes retrieved successfully',
        'data' => $price_changes
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
