<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ob_start();

// Set JSON header first
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit;
}

define('DB_SERVER', 'sql301.infinityfree.com');
define('DB_USERNAME', 'if0_39632973');
define('DB_PASSWORD', '12Spykekyle12');
define('DB_NAME', 'if0_39632973_if0_39632973_');

$response = array(
    'success' => false,
    'message' => 'Invalid request'
);

try {
    // Attempt to connect to MySQL database
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if($conn === false){
        http_response_code(500);
        $response['message'] = 'Database connection failed';
        ob_end_clean();
        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
        $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $userType = isset($_POST['userType']) ? trim($_POST['userType']) : 'individual';
        $referralCode = isset($_POST['referralCode']) ? trim($_POST['referralCode']) : null;

        // Validation
        if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || 
            empty($phone) || empty($address) || empty($password)) {
            http_response_code(400);
            $response['message'] = 'All fields are required';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            $response['message'] = 'Invalid email format';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            $response['message'] = 'Password must be at least 6 characters';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Check if email or username already exists
        $checkQuery = "SELECT id FROM users WHERE email = ? OR username = ?";
        $checkStmt = $conn->prepare($checkQuery);
        
        if (!$checkStmt) {
            http_response_code(500);
            $response['message'] = 'Database error';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $checkStmt->bind_param("ss", $email, $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            http_response_code(400);
            $response['message'] = 'Email or username already exists';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Generate referral code if needed
        $newReferralCode = strtoupper(substr(md5(uniqid()), 0, 8));

        // Insert user
        $insertQuery = "INSERT INTO users (first_name, last_name, username, email, phone, address, password_hash, user_type, referral_code, agreed_terms, is_verified) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)";
        $insertStmt = $conn->prepare($insertQuery);
        
        if (!$insertStmt) {
            http_response_code(500);
            $response['message'] = 'Database error';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $insertStmt->bind_param("sssssssss", $firstName, $lastName, $username, $email, $phone, $address, $passwordHash, $userType, $newReferralCode);
        
        if ($insertStmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Registration successful. Please login.';
            http_response_code(201);
        } else {
            http_response_code(400);
            $response['message'] = 'Registration failed';
        }

        $insertStmt->close();
        $checkStmt->close();
    } else {
        http_response_code(405);
        $response['message'] = 'Invalid request method';
    }

    mysqli_close($conn);
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Server error';
}

ob_end_clean();
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
