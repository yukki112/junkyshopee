<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db_connection.php';

try {
    // Get parameters with defaults
    $limit = isset($_GET['limit']) ? min(max(1, intval($_GET['limit'])), 50) : 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;
    
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build the query
    $query = "SELECT 
                t.transaction_id,
                CONCAT(t.transaction_date, ' ', t.transaction_time) as datetime,
                t.amount,
                t.status,
                t.type as transaction_type,
                t.name as customer_name,
                u.username,
                t.created_at
              FROM transactions t 
              LEFT JOIN users u ON t.user_id = u.id 
              WHERE 1=1";
    
    $count_query = "SELECT COUNT(*) as total 
                   FROM transactions t 
                   LEFT JOIN users u ON t.user_id = u.id 
                   WHERE 1=1";
    
    $params = [];
    $types = "";
    $where_conditions = [];
    
    // Apply filters
    if (!empty($date_from)) {
        $where_conditions[] = " t.transaction_date >= ? ";
        $params[] = $date_from;
        $types .= "s";
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = " t.transaction_date <= ? ";
        $params[] = $date_to;
        $types .= "s";
    }
    
    if (!empty($status)) {
        $where_conditions[] = " t.status = ? ";
        $params[] = $status;
        $types .= "s";
    }
    
    if (!empty($search)) {
        $where_conditions[] = " (t.transaction_id LIKE ? OR t.name LIKE ? OR u.username LIKE ?) ";
        $search_param = "%$search%";
        array_push($params, $search_param, $search_param, $search_param);
        $types .= "sss";
    }
    
    // Add WHERE conditions
    if (!empty($where_conditions)) {
        $query .= " AND " . implode(" AND ", $where_conditions);
        $count_query .= " AND " . implode(" AND ", $where_conditions);
    }
    
    // Complete the queries
    $query .= " ORDER BY t.transaction_date DESC, t.transaction_time DESC 
                LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    // Get total count
    $count_stmt = $conn->prepare($count_query);
    if (!empty($where_conditions)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_count = $count_result->fetch_assoc()['total'];
    
    // Get transactions data
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = [
            'transaction_id' => $row['transaction_id'],
            'datetime' => $row['datetime'],
            'amount' => floatval($row['amount']),
            'status' => $row['status'],
            'transaction_type' => $row['transaction_type'],
            'customer_name' => $row['customer_name'],
            'username' => $row['username'],
            'formatted_datetime' => date('M j, Y h:i A', strtotime($row['datetime'])),
            'formatted_amount' => '₱' . number_format($row['amount'], 2)
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'transactions' => $transactions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => intval($total_count),
                'pages' => ceil($total_count / $limit)
            ],
            'filters' => [
                'date_from' => $date_from,
                'date_to' => $date_to,
                'status' => $status,
                'search' => $search
            ],
            'summary' => [
                'total_transactions' => intval($total_count),
                'total_amount' => array_sum(array_column($transactions, 'amount')),
                'completed_count' => count(array_filter($transactions, function($t) { return $t['status'] === 'Completed'; }))
            ]
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>