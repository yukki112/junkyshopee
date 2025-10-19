<?php
session_start();
require_once 'db_connection.php';

// Authentication check
if (!isset($_SESSION['employee_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit();
    }
    
    try {
        $query = "SELECT id, first_name, last_name, username, loyalty_points, loyalty_tier 
                  FROM users 
                  WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $user['id'],
                    'full_name' => $user['first_name'] . ' ' . $user['last_name'],
                    'username' => $user['username'],
                    'loyalty_points' => $user['loyalty_points'],
                    'loyalty_tier' => ucfirst($user['loyalty_tier'])
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
