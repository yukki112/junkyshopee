<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

require_once '../db_connection.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Read input (JSON or form encoded)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$loginInput = '';
$password = '';

// Debug logging
error_log("Login attempt received");
error_log("Raw input: " . $input);
error_log("POST data: " . print_r($_POST, true));

// Accept both raw JSON and form-data
if (is_array($data)) {
    // Handle JSON input
    if (isset($data['loginInput']) && isset($data['password'])) {
        $loginInput = trim($data['loginInput']);
        $password = trim($data['password']);
        error_log("JSON login - loginInput: $loginInput, password length: " . strlen($password));
    }
} elseif (isset($_POST['loginInput']) && isset($_POST['password'])) {
    // Handle form-data input
    $loginInput = trim($_POST['loginInput']);
    $password = trim($_POST['password']);
    error_log("Form login - loginInput: $loginInput, password length: " . strlen($password));
}

// Validate required fields
if (empty($loginInput) || empty($password)) {
    http_response_code(400);
    error_log("Missing fields - loginInput: " . (empty($loginInput) ? 'empty' : 'has value') . ", password: " . (empty($password) ? 'empty' : 'has value'));
    echo json_encode(['success' => false, 'message' => 'Email/Username and password required']);
    exit;
}

// Query: allow login with email OR username
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, username, password_hash, is_verified, user_type, is_admin 
                        FROM users 
                        WHERE email = ? OR username = ?");
if (!$stmt) {
    http_response_code(500);
    error_log("Database prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ss", $loginInput, $loginInput);
$stmt->execute();
$result = $stmt->get_result();

// User not found
if (!$result || $result->num_rows === 0) {
    http_response_code(401);
    error_log("User not found for login input: $loginInput");
    echo json_encode(['success' => false, 'message' => 'Invalid email/username or password']);
    exit;
}

$user = $result->fetch_assoc();
error_log("User found: " . $user['email'] . " / " . $user['username']);

// Validate password
if (!password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    error_log("Password verification failed for user: " . $user['email']);
    echo json_encode(['success' => false, 'message' => 'Invalid email/username or password']);
    exit;
}


if (isset($user['is_verified']) && !$user['is_verified']) {
    http_response_code(403);
    error_log("Account not verified: " . $user['email']);
    echo json_encode(['success' => false, 'message' => 'Account not verified']);
    exit;
}


$token = "token_" . $user['id'] . "_" . time();


$response = [
    'success' => true,
    'message' => 'Login successful',
    'token' => $token,
    'user' => [
        'id' => (int)$user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $user['email'],
        'username' => $user['username'],
        'user_type' => $user['user_type'],
        'is_admin' => (int)$user['is_admin']
    ]
];

error_log("Login successful for user: " . $user['email']);
echo json_encode($response);

$stmt->close();
$conn->close();
exit;