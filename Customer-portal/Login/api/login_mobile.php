<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

require_once '../db_connection.php';


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


$input = file_get_contents('php://input');
$data = json_decode($input, true);

$email = '';
$password = '';

if (is_array($data) && isset($data['email']) && isset($data['password'])) {
    $email = trim($data['email']);
    $password = trim($data['password']);
} elseif (isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
}

// Validate required fields
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

// ✅ Match the actual structure of your `users` table
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, is_verified, user_type, is_admin FROM users WHERE email = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

$user = $result->fetch_assoc();


if (!password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}


if (isset($user['is_verified']) && !$user['is_verified']) {
    echo json_encode(['success' => false, 'message' => 'Account not verified']);
    exit;
}


echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'id' => (int)$user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $user['email'],
        'user_type' => $user['user_type'],
        'is_admin' => (int)$user['is_admin']
    ]
]);
exit;
?>
