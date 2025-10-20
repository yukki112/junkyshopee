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
        m.id, 
        m.material_option, 
        m.unit_price, 
        m.weight_unit,
        m.trend_direction,
        m.trend_change,
        m.updated_at,
        ic.category_name,
        ic.color_tag,
        ic.icon
    FROM materials m
    LEFT JOIN item_categories ic ON m.category_id = ic.id
    WHERE m.status = 'active'
    ORDER BY ic.category_name, m.material_option";

    $result = $conn->query($query);
    $materials = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $materials[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Materials retrieved successfully',
        'data' => $materials
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
