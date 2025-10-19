<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ob_start();

// Set JSON header first
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
    'message' => 'Unauthorized',
    'user' => null
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
        $token = isset($_SERVER['HTTP_AUTHORIZATION']) ? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : '';

        if (empty($token)) {
            http_response_code(401);
            $response['message'] = 'Token is required';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Get user ID from POST data (mobile sends it)
        $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;

        if ($userId === 0) {
            http_response_code(400);
            $response['message'] = 'User ID is required';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $query = "SELECT id, first_name, last_name, username, email, phone, address, user_type, profile_image, loyalty_points, loyalty_tier FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            http_response_code(500);
            $response['message'] = 'Database error';
            ob_end_clean();
            echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $userData = array(
                'id' => (int)$user['id'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'userType' => $user['user_type'],
                'profileImage' => $user['profile_image'],
                'loyaltyPoints' => (int)$user['loyalty_points'],
                'loyaltyTier' => $user['loyalty_tier']
            );

            $response['success'] = true;
            $response['message'] = 'User data retrieved';
            $response['user'] = $userData;
            http_response_code(200);
        } else {
            http_response_code(404);
            $response['message'] = 'User not found';
        }

        $stmt->close();
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
