<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connection.php';

try {
    // Get parameters with defaults
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(max(1, intval($_GET['limit'])), 100) : 50; // Max 100 records per request
    $offset = ($page - 1) * $limit;
    
    $search = $_GET['search'] ?? '';
    $user_type = $_GET['user_type'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $sort_by = $_GET['sort_by'] ?? 'u.created_at';
    $sort_order = strtoupper($_GET['sort_order'] ?? 'DESC');
    
    // Validate sort order
    $sort_order = in_array($sort_order, ['ASC', 'DESC']) ? $sort_order : 'DESC';
    
    // Build the query
    $query = "SELECT 
                u.id,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                u.username,
                u.email,
                u.phone,
                u.user_type,
                COUNT(DISTINCT t.id) as total_transactions,
                COALESCE(SUM(t.amount), 0) as total_spent,
                MAX(t.created_at) as last_transaction,
                u.created_at,
                u.updated_at
              FROM users u 
              LEFT JOIN transactions t ON u.id = t.user_id 
              WHERE 1=1";
    
    $count_query = "SELECT COUNT(DISTINCT u.id) as total 
                   FROM users u 
                   LEFT JOIN transactions t ON u.id = t.user_id 
                   WHERE 1=1";
    
    $params = [];
    $types = "";
    $where_conditions = [];
    
    // Apply filters
    if (!empty($search)) {
        $where_conditions[] = " (u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ?) ";
        $search_param = "%$search%";
        array_push($params, $search_param, $search_param, $search_param, $search_param, $search_param);
        $types .= "sssss";
    }
    
    if (!empty($user_type)) {
        $where_conditions[] = " u.user_type = ? ";
        $params[] = $user_type;
        $types .= "s";
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = " u.created_at >= ? ";
        $params[] = $date_from . " 00:00:00";
        $types .= "s";
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = " u.created_at <= ? ";
        $params[] = $date_to . " 23:59:59";
        $types .= "s";
    }
    
    // Add WHERE conditions
    if (!empty($where_conditions)) {
        $query .= " AND " . implode(" AND ", $where_conditions);
        $count_query .= " AND " . implode(" AND ", $where_conditions);
    }
    
    // Complete the queries
    $query .= " GROUP BY u.id 
                ORDER BY $sort_by $sort_order 
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
    
    // Get customers data
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'id' => intval($row['id']),
            'customer_name' => $row['customer_name'],
            'username' => $row['username'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'user_type' => $row['user_type'],
            'total_transactions' => intval($row['total_transactions']),
            'total_spent' => floatval($row['total_spent']),
            'last_transaction' => $row['last_transaction'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'customers' => $customers,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => intval($total_count),
                'pages' => ceil($total_count / $limit)
            ],
            'filters' => [
                'search' => $search,
                'user_type' => $user_type,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'sort_by' => $sort_by,
                'sort_order' => $sort_order
            ]
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
?>