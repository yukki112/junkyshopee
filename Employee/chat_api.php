<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$employee_id = $_SESSION['employee_id'];
$action = $_POST['action'] ?? '';

function sanitizeInput($data) {
    global $conn;
    return $conn->real_escape_string(strip_tags(trim($data)));
}

switch ($action) {
    case 'get_messages':
        $user_id = intval($_POST['user_id']);
        $last_message_id = intval($_POST['last_message_id'] ?? 0);
        
        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
            exit();
        }
        
        $query = "SELECT m.*, 
                  CONCAT(e.first_name, ' ', e.last_name) as sender_name
                  FROM messages m
                  LEFT JOIN employees e ON m.sender_id = e.id
                  WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                  AND m.id > ?
                  ORDER BY m.created_at ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiii", $employee_id, $user_id, $user_id, $employee_id, $last_message_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        break;
        
    case 'send_message':
        $receiver_id = intval($_POST['receiver_id']);
        $message = sanitizeInput($_POST['message']);
        
        if ($receiver_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid receiver ID']);
            exit();
        }
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            exit();
        }
        
        if ($receiver_id == $employee_id) {
            echo json_encode(['success' => false, 'error' => 'Cannot send message to yourself']);
            exit();
        }
        
        // Check if receiver exists
        $check_receiver = $conn->prepare("SELECT id FROM employees WHERE id = ? AND is_active = 1");
        $check_receiver->bind_param("i", $receiver_id);
        $check_receiver->execute();
        $check_receiver->store_result();
        
        if ($check_receiver->num_rows == 0) {
            echo json_encode(['success' => false, 'error' => 'Recipient not found or inactive']);
            exit();
        }
        
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $employee_id, $receiver_id, $message);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
        break;
        
    case 'mark_read':
        $user_id = intval($_POST['user_id']);
        
        if ($user_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
            exit();
        }
        
        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
        $stmt->bind_param("ii", $user_id, $employee_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to mark messages as read']);
        }
        break;
        
    case 'get_conversations':
        $query = "SELECT 
            CASE 
                WHEN m.sender_id = ? THEN m.receiver_id 
                ELSE m.sender_id 
            END as other_user_id,
            CONCAT(e.first_name, ' ', e.last_name) as other_user_name,
            r.role_name as other_user_role,
            m.message as last_message,
            m.created_at as last_message_time,
            m.sender_id as last_sender_id,
            COUNT(CASE WHEN m2.receiver_id = ? AND m2.is_read = 0 THEN 1 END) as unread_count
        FROM messages m
        LEFT JOIN employees e ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = e.id
        LEFT JOIN employee_roles r ON e.role_id = r.id
        LEFT JOIN messages m2 ON (
            (m2.sender_id = e.id AND m2.receiver_id = ?) OR 
            (m2.sender_id = ? AND m2.receiver_id = e.id)
        )
        WHERE (m.sender_id = ? OR m.receiver_id = ?)
        AND m.created_at = (
            SELECT MAX(created_at) 
            FROM messages 
            WHERE (sender_id = ? AND receiver_id = e.id) OR (sender_id = e.id AND receiver_id = ?)
        )
        GROUP BY other_user_id, other_user_name, other_user_role, last_message, last_message_time, last_sender_id
        ORDER BY last_message_time DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiiiiiii", $employee_id, $employee_id, $employee_id, $employee_id, $employee_id, $employee_id, $employee_id, $employee_id, $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        
        echo json_encode(['success' => true, 'conversations' => $conversations]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
?>
