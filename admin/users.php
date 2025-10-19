<?php
session_start();
require_once 'db_connection.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, profile_image, is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !$user['is_admin']) {
        session_destroy();
        header("Location: ../Customer-portal/Login/Login.php");
        exit();
    }
} catch(PDOException $e) {
    error_log("User query failed: " . $e->getMessage());
    die("Error loading user data.");
}

$admin_name = $user['first_name'] . ' ' . $user['last_name'];
$admin_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

// Language handling
$language = 'en'; // Default language
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'tl'])) {
    $language = $_GET['lang'];
    $_SESSION['language'] = $language;
} elseif (isset($_SESSION['language'])) {
    $language = $_SESSION['language'];
}

// Language strings
$translations = [
    'en' => [
        'user_management' => 'User Management',
        'welcome_message' => 'Manage employee accounts, roles, permissions, attendance, and communication. Keep your team organized and productive.',
        'new_employee' => 'New Employee',
        'employees' => 'Employees',
        'roles_permissions' => 'Roles & Permissions',
        'attendance' => 'Attendance',
        'messages' => 'Messages',
        'employee_accounts' => 'Employee Accounts',
        'add_employee' => 'Add Employee',
        'search_employees' => 'Search employees...',
        'all_roles' => 'All Roles',
        'all_status' => 'All Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'name' => 'Name',
        'username' => 'Username',
        'role' => 'Role',
        'contact' => 'Contact',
        'email' => 'Email',
        'resume' => 'Resume',
        'status' => 'Status',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'no_employees_found' => 'No employees found',
        'date' => 'Date',
        'action' => 'Action',
        'user' => 'User',
        'login_time' => 'Login Time',
        'logout_time' => 'Logout Time',
        'method' => 'Method',
        'duration' => 'Duration',
        'no_attendance_logs' => 'No attendance logs found',
        'page' => 'Page',
        'of' => 'of',
        'previous_page' => 'Previous',
        'next_page' => 'Next',
        'add_new_employee' => 'Add New Employee',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'password' => 'Password',
        'contact_number' => 'Contact Number',
        'address' => 'Address',
        'select_role' => 'Select Role',
        'cancel' => 'Cancel',
        'edit_employee' => 'Edit Employee',
        'new_password' => 'New Password (leave blank to keep current)',
        'confirm_deletion' => 'Confirm Deletion',
        'delete_employee_confirm' => 'Are you sure you want to delete the employee',
        'warning' => 'Warning',
        'delete_warning' => 'This action cannot be undone.',
        'add_new_role' => 'Add New Role',
        'role_name' => 'Role Name',
        'description' => 'Description',
        'edit_role' => 'Edit Role',
        'new_message' => 'New Message',
        'to' => 'To',
        'select_employee' => 'Select employee...',
        'type_message' => 'Type your message here...',
        'send_message' => 'Send Message',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Users',
        'pricing_control' => 'Pricing Control',
        'reports_analytics' => 'Reports & Analytics',
        'transactions' => 'Transactions',
        'loyalty_program' => 'Loyalty Program',
        'profile' => 'Profile',

        'logout' => 'Logout',
        'administrator' => 'Administrator',
        'download' => 'Download',
        'no_resume' => 'No resume',
        'update_employee' => 'Update Employee',
        'no_roles_found' => 'No roles found',
        'search_conversations' => 'Search conversations...',
        'employee' => 'Employee',
        'view_profile' => 'View Profile',
        'type_message_placeholder' => 'Type a message...',
        'welcome_to_messages' => 'Welcome to Messages',
        'select_conversation' => 'Select a conversation from the sidebar to start chatting, or click the + button to start a new conversation.',
        'no_conversations' => 'No conversations yet. Start a new chat!',
        'contact_information' => 'Contact Information',
        'loading' => 'Loading...',
        'department' => 'Department',
        'update_role' => 'Update Role',
        'mark_as_read' => 'Mark as Read',
        'clear_all' => 'Clear All'
    ],
    'tl' => [
        'user_management' => 'Pamamahala ng Mga User',
        'welcome_message' => 'Pamahalaan ang mga account ng empleyado, mga tungkulin, mga pahintulot, attendance, at komunikasyon. Panatilihing organisado at produktibo ang iyong koponan.',
        'new_employee' => 'Bagong Empleyado',
        'employees' => 'Mga Empleyado',
        'roles_permissions' => 'Mga Tungkulin at Pahintulot',
        'attendance' => 'Attendance',
        'messages' => 'Mga Mensahe',
        'employee_accounts' => 'Mga Account ng Empleyado',
        'add_employee' => 'Magdagdag ng Empleyado',
        'search_employees' => 'Maghanap ng mga empleyado...',
        'all_roles' => 'Lahat ng Tungkulin',
        'all_status' => 'Lahat ng Katayuan',
        'active' => 'Aktibo',
        'inactive' => 'Hindi Aktibo',
        'name' => 'Pangalan',
        'username' => 'Username',
        'role' => 'Tungkulin',
        'contact' => 'Kontak',
        'email' => 'Email',
        'resume' => 'Resume',
        'status' => 'Katayuan',
        'actions' => 'Mga Aksyon',
        'edit' => 'I-edit',
        'delete' => 'Tanggalin',
        'no_employees_found' => 'Walang nakitang mga empleyado',
        'date' => 'Petsa',
        'action' => 'Aksyon',
        'user' => 'User',
        'login_time' => 'Oras ng Pag-login',
        'logout_time' => 'Oras ng Pag-logout',
        'method' => 'Pamamaraan',
        'duration' => 'Tagal',
        'no_attendance_logs' => 'Walang nakitang mga talaan ng attendance',
        'page' => 'Pahina',
        'of' => 'ng',
        'previous_page' => 'Nakaraan',
        'next_page' => 'Susunod',
        'add_new_employee' => 'Magdagdag ng Bagong Empleyado',
        'first_name' => 'Pangalan',
        'last_name' => 'Apelyido',
        'password' => 'Password',
        'contact_number' => 'Numero ng Kontak',
        'address' => 'Address',
        'select_role' => 'Pumili ng Tungkulin',
        'cancel' => 'Kanselahin',
        'edit_employee' => 'I-edit ang Empleyado',
        'new_password' => 'Bagong Password (iwanang blangko upang panatilihin ang kasalukuyan)',
        'confirm_deletion' => 'Kumpirmahin ang Pagtatanggal',
        'delete_employee_confirm' => 'Sigurado ka bang gusto mong tanggalin ang empleyado',
        'warning' => 'Babala',
        'delete_warning' => 'Hindi na mababawi ang aksyon na ito.',
        'add_new_role' => 'Magdagdag ng Bagong Tungkulin',
        'role_name' => 'Pangalan ng Tungkulin',
        'description' => 'Paglalarawan',
        'edit_role' => 'I-edit ang Tungkulin',
        'new_message' => 'Bagong Mensahe',
        'to' => 'Para Kay',
        'select_employee' => 'Pumili ng empleyado...',
        'type_message' => 'I-type ang iyong mensahe dito...',
        'send_message' => 'Ipadala ang Mensahe',
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'users' => 'Mga User',
        'pricing_control' => 'Kontrol sa Presyo',
        'reports_analytics' => 'Mga Ulat at Analytics',
        'transactions' => 'Mga Transaksyon',
        'loyalty_program' => 'Programa ng Loyalty',
        'profile' => 'Profile',
  
        'logout' => 'Logout',
        'administrator' => 'Administrator',
        'download' => 'I-download',
        'no_resume' => 'Walang resume',
        'update_employee' => 'I-update ang Empleyado',
        'no_roles_found' => 'Walang nakitang mga tungkulin',
        'search_conversations' => 'Maghanap ng mga pag-uusap...',
        'employee' => 'Empleyado',
        'view_profile' => 'Tingnan ang Profile',
        'type_message_placeholder' => 'Mag-type ng mensahe...',
        'welcome_to_messages' => 'Maligayang Pagdating sa Mga Mensahe',
        'select_conversation' => 'Pumili ng pag-uusap mula sa sidebar upang magsimulang mag-chat, o i-click ang + button upang magsimula ng bagong pag-uusap.',
        'no_conversations' => 'Wala pang mga pag-uusap. Magsimula ng bagong chat!',
        'contact_information' => 'Impormasyon sa Pakikipag-ugnayan',
        'loading' => 'Naglo-load...',
        'department' => 'Kagawaran',
        'update_role' => 'I-update ang Tungkulin',
        'mark_as_read' => 'Markahan bilang Nabasa',
        'clear_all' => 'Lahat ng Burahin'
    ]
];

$t = $translations[$language];

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'employees';

// Initialize variables
$employees = [];
$roles = [];
$attendance_logs = [];
$messages = [];
$users = [];

// Get all employee roles
try {
    $roles = $conn->query("SELECT * FROM employee_roles ORDER BY role_name")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Roles query failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new employee
    if (isset($_POST['add_employee'])) {
        try {
            $password_hash = password_hash(sanitizeInput($_POST['password']), PASSWORD_DEFAULT);
            
            // Handle file upload
            $resumePath = null;
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/resumes/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['resume']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetPath)) {
                    $resumePath = $targetPath;
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO employees 
                (first_name, last_name, username, password_hash, role_id, contact_number, email, address, resume, is_verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            
            $stmt->execute([
                sanitizeInput($_POST['first_name']),
                sanitizeInput($_POST['last_name']),
                sanitizeInput($_POST['username']),
                $password_hash,
                intval($_POST['role_id']),
                sanitizeInput($_POST['contact_number']),
                sanitizeInput($_POST['email']),
                sanitizeInput($_POST['address']),
                $resumePath
            ]);
            
            $_SESSION['success'] = "Employee added successfully!";
        } catch(PDOException $e) {
            error_log("Employee insert failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to add employee. Username may already exist.";
        }
        header("Location: users.php?tab=employees");
        exit();
    }
    
    // Update employee
    if (isset($_POST['update_employee'])) {
        try {
            // Get current resume path
            $currentResume = null;
            $stmt = $conn->prepare("SELECT resume FROM employees WHERE id = ?");
            $stmt->execute([intval($_POST['employee_id'])]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $currentResume = $result['resume'];
            }
            
            // Handle file upload
            $resumePath = $currentResume;
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                // Delete old resume if exists
                if ($currentResume && file_exists($currentResume)) {
                    unlink($currentResume);
                }
                
                $uploadDir = 'uploads/resumes/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['resume']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetPath)) {
                    $resumePath = $targetPath;
                }
            }
            
            $update_data = [
                sanitizeInput($_POST['first_name']),
                sanitizeInput($_POST['last_name']),
                intval($_POST['role_id']),
                sanitizeInput($_POST['contact_number']),
                sanitizeInput($_POST['email']),
                sanitizeInput($_POST['address']),
                $resumePath,
                intval($_POST['is_active']),
                intval($_POST['employee_id'])
            ];
            
            $query = "UPDATE employees SET 
                first_name = ?, last_name = ?, role_id = ?, 
                contact_number = ?, email = ?, address = ?, resume = ?, is_active = ?
                WHERE id = ?";
            
            // If password is being updated
            if (!empty($_POST['password'])) {
                $password_hash = password_hash(sanitizeInput($_POST['password']), PASSWORD_DEFAULT);
                $query = "UPDATE employees SET 
                    first_name = ?, last_name = ?, role_id = ?, 
                    contact_number = ?, email = ?, address = ?, resume = ?, is_active = ?,
                    password_hash = ?
                    WHERE id = ?";
                
                array_splice($update_data, 8, 0, $password_hash);
            }
            
            $stmt = $conn->prepare($query);
            $stmt->execute($update_data);
            
            $_SESSION['success'] = "Employee updated successfully!";
        } catch(PDOException $e) {
            error_log("Employee update failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update employee.";
        }
        header("Location: users.php?tab=employees");
        exit();
    }
    
    // Delete employee
    if (isset($_POST['delete_employee'])) {
        try {
            $employee_id = intval($_POST['employee_id']);
            
            // Get employee data first to delete resume file
            $stmt = $conn->prepare("SELECT resume FROM employees WHERE id = ?");
            $stmt->execute([$employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($employee && $employee['resume'] && file_exists($employee['resume'])) {
                unlink($employee['resume']);
            }
            
            // Delete the employee
            $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$employee_id]);
            
            $_SESSION['success'] = "Employee deleted successfully!";
        } catch(PDOException $e) {
            error_log("Employee delete failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to delete employee: " . $e->getMessage();
        }
        header("Location: users.php?tab=employees");
        exit();
    }
    
    // Add new role
    if (isset($_POST['add_role'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO employee_roles (role_name, description) VALUES (?, ?)");
            $stmt->execute([
                sanitizeInput($_POST['role_name']),
                sanitizeInput($_POST['description'])
            ]);
            
            $_SESSION['success'] = "Role added successfully!";
        } catch(PDOException $e) {
            error_log("Role insert failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to add role.";
        }
        header("Location: users.php?tab=roles");
        exit();
    }
    
    // Update role
    if (isset($_POST['update_role'])) {
        try {
            $stmt = $conn->prepare("UPDATE employee_roles 
                SET role_name = ?, description = ?, is_active = ?
                WHERE id = ?");
            $stmt->execute([
                sanitizeInput($_POST['role_name']),
                sanitizeInput($_POST['description']),
                intval($_POST['is_active']),
                intval($_POST['role_id'])
            ]);
            
            $_SESSION['success'] = "Role updated successfully!";
        } catch(PDOException $e) {
            error_log("Role update failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update role.";
        }
        header("Location: users.php?tab=roles");
        exit();
    }
    
    // Handle message sending
    if (isset($_POST['send_message'])) {
        try {
            $receiver_id = intval($_POST['receiver_id']);
            $message = sanitizeInput($_POST['message']);
            
            // Get admin employee ID for current user
            $admin_check = $conn->prepare("SELECT id FROM employees WHERE email = ? OR username LIKE ? LIMIT 1");
            $admin_check->execute([$user['email'], 'admin_%']);
            $admin_employee = $admin_check->fetch(PDO::FETCH_ASSOC);
            
            if (!$admin_employee) {
                // Create admin employee record if it doesn't exist
                $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, username, password_hash, role_id, email, is_verified, is_active) 
                    VALUES (?, ?, ?, ?, (SELECT id FROM employee_roles WHERE role_name = 'Administrator' LIMIT 1), ?, 1, 1)");
                $stmt->execute([
                    $user['first_name'],
                    $user['last_name'],
                    'admin_' . $user['id'],
                    password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    $user['email']
                ]);
                $admin_employee_id = $conn->lastInsertId();
            } else {
                $admin_employee_id = $admin_employee['id'];
            }
            
            // Insert message
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $admin_employee_id,
                $receiver_id,
                $message
            ]);
            
            $_SESSION['success'] = "Message sent successfully!";
            
        } catch (Exception $e) {
            error_log("Message sending failed: " . $e->getMessage());
            $_SESSION['error'] = "Failed to send message: " . $e->getMessage();
        }
        header("Location: users.php?tab=messages");
        exit();
    }

    // Handle AJAX requests for messages and profiles
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        
        // Get admin employee ID for current user
        $admin_employee_id = null;
        $admin_check = $conn->prepare("SELECT id FROM employees WHERE email = ? OR username LIKE ? LIMIT 1");
        $admin_check->execute([$user['email'], 'admin_%']);
        $admin_employee = $admin_check->fetch(PDO::FETCH_ASSOC);
        if ($admin_employee) {
            $admin_employee_id = $admin_employee['id'];
        }

        if ($_POST['action'] === 'get_conversation') {
            $other_user_id = intval($_POST['other_user_id']);
            $conversation = [];
            
            try {
                $stmt = $conn->prepare("SELECT 
                    m.*, 
                    CONCAT(sender.first_name, ' ', sender.last_name) as sender_name,
                    CONCAT(receiver.first_name, ' ', receiver.last_name) as receiver_name,
                    sender.username as sender_username,
                    receiver.username as receiver_username
                FROM messages m
                LEFT JOIN employees sender ON m.sender_id = sender.id
                LEFT JOIN employees receiver ON m.receiver_id = receiver.id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.created_at ASC");
                
                $stmt->execute([$admin_employee_id, $other_user_id, $other_user_id, $admin_employee_id]);
                $conversation = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'conversation' => $conversation]);
            } catch(PDOException $e) {
                error_log("Conversation fetch failed: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Failed to load conversation.']);
            }
            exit();
        }

        if ($_POST['action'] === 'get_user_profile') {
            $user_id_to_fetch = intval($_POST['user_id']);
            $profile = null;
            try {
                $stmt = $conn->prepare("SELECT 
                    e.id, e.first_name, e.last_name, e.email, e.profile_photo,
                    er.role_name
                FROM employees e
                LEFT JOIN employee_roles er ON e.role_id = er.id
                WHERE e.id = ?");
                $stmt->execute([$user_id_to_fetch]);
                $profile = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'profile' => $profile]);
            } catch(PDOException $e) {
                error_log("User profile fetch failed: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Failed to load user profile.']);
            }
            exit();
        }

        // Mark notification as read
        if ($_POST['action'] === 'mark_notification_read') {
            $notification_id = intval($_POST['notification_id']);
            // In a real implementation, you would update the database here
            echo json_encode(['success' => true]);
            exit();
        }
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Get employees with filtering
$employee_search = isset($_GET['employee_search']) ? sanitizeInput($_GET['employee_search']) : '';
$role_filter = isset($_GET['role_filter']) ? intval($_GET['role_filter']) : 0;
$status_filter = isset($_GET['status_filter']) ? sanitizeInput($_GET['status_filter']) : 'all';

$employee_query = "SELECT 
    e.*, 
    er.role_name,
    CONCAT(e.first_name, ' ', e.last_name) as full_name
FROM employees e
JOIN employee_roles er ON e.role_id = er.id
WHERE 1=1";

$params = [];

if (!empty($employee_search)) {
    $employee_query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.username LIKE ? OR e.email LIKE ?)";
    $params = array_merge($params, ["%$employee_search%", "%$employee_search%", "%$employee_search%", "%$employee_search%"]);
}

if ($role_filter > 0) {
    $employee_query .= " AND e.role_id = ?";
    $params[] = $role_filter;
}

if ($status_filter === 'active') {
    $employee_query .= " AND e.is_active = 1";
} elseif ($status_filter === 'inactive') {
    $employee_query .= " AND e.is_active = 0";
}

$employee_query .= " ORDER BY e.first_name, e.last_name";

try {
    $stmt = $conn->prepare($employee_query);
    $stmt->execute($params);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Employees query failed: " . $e->getMessage());
}

$attendance_page = isset($_GET['attendance_page']) ? max(1, intval($_GET['attendance_page'])) : 1;
$attendance_per_page = 10;
$attendance_offset = ($attendance_page - 1) * $attendance_per_page;

// Get total attendance count
$total_attendance = 0;
try {
    $count_result = $conn->query("SELECT COUNT(*) as total FROM attendance_logs");
    $total_attendance = $count_result->fetch(PDO::FETCH_ASSOC)['total'];
} catch(PDOException $e) {
    error_log("Attendance count query failed: " . $e->getMessage());
}

$total_attendance_pages = ceil($total_attendance / $attendance_per_page);

// Get attendance logs with pagination
try {
    $stmt = $conn->prepare("SELECT 
        al.*, 
        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
        e.username
    FROM attendance_logs al
    JOIN employees e ON al.employee_id = e.id
    ORDER BY al.login_time DESC
    LIMIT ? OFFSET ?");
    $stmt->execute([$attendance_per_page, $attendance_offset]);
    $attendance_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Attendance logs query failed: " . $e->getMessage());
}

// Get admin employee ID for current user
$admin_employee_id = null;
$admin_check = $conn->prepare("SELECT id FROM employees WHERE email = ? OR username LIKE ? LIMIT 1");
$admin_check->execute([$user['email'], 'admin_%']);
$admin_employee = $admin_check->fetch(PDO::FETCH_ASSOC);

if ($admin_employee) {
    $admin_employee_id = $admin_employee['id'];
}

// Fetch messages where the current admin user is sender or receiver
$messages_query = "SELECT 
    m.*, 
    CONCAT(sender.first_name, ' ', sender.last_name) as sender_name,
    CONCAT(receiver.first_name, ' ', receiver.last_name) as receiver_name,
    sender.username as sender_username,
    receiver.username as receiver_username
FROM messages m
LEFT JOIN employees sender ON m.sender_id = sender.id
LEFT JOIN employees receiver ON m.receiver_id = receiver.id
WHERE 1=1";

$message_params = [];

if ($admin_employee_id) {
    $messages_query .= " AND (m.sender_id = ? OR m.receiver_id = ?)";
    $message_params = [$admin_employee_id, $admin_employee_id];
} else {
    // If no admin employee record exists yet, show all messages to employees
    $messages_query .= " AND m.receiver_id IN (SELECT id FROM employees WHERE is_active = 1)";
}

$messages_query .= " ORDER BY m.created_at DESC LIMIT 50";

try {
    $stmt = $conn->prepare($messages_query);
    $stmt->execute($message_params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Messages query failed: " . $e->getMessage());
}

// Get all employees for messaging dropdown
try {
    // Fetch active employees to be selectable as recipients
    $users = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM employees WHERE is_active = 1 ORDER BY first_name")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Users query failed: " . $e->getMessage());
}

// Display success/error messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['user_management']; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    :root {
        --bg-beige: #E6D8C3;
        --sales-orange: #D97A41;
        --stock-green: #708B4C;
        --panel-cream: #F2EAD3;
        --topbar-brown: #3C342C;
        --text-dark: #2E2B29;
        --icon-green: #6A7F46;
        --icon-orange: #D97A41;
        --accent-blue: #4A89DC;
        --sidebar-width: 280px;
        
        /* Dark mode variables */
        --dark-bg-primary: #1a1a1a;
        --dark-bg-secondary: #2d2d2d;
        --dark-bg-tertiary: #3c3c3c;
        --dark-text-primary: #e0e0e0;
        --dark-text-secondary: #a0a0a0;
        --dark-border: #404040;
        --dark-shadow: rgba(0, 0, 0, 0.3);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    body {
        background-color: var(--bg-beige);
        color: var(--text-dark);
        line-height: 1.6;
        display: flex;
        min-height: 100vh;
        transition: all 0.3s ease;
    }

    body.dark-mode {
        background-color: var(--dark-bg-primary);
        color: var(--dark-text-primary);
    }

    /* Sidebar - Admin Version */
    .sidebar {
        width: var(--sidebar-width);
        background: linear-gradient(180deg, var(--topbar-brown) 0%, #2A2020 100%);
        color: white;
        padding: 30px 0;
        position: sticky;
        top: 0;
        height: 100vh;
        box-shadow: 5px 0 15px rgba(0,0,0,0.1);
        z-index: 10;
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    body.dark-mode .sidebar {
        background: linear-gradient(180deg, var(--dark-bg-secondary) 0%, var(--dark-bg-primary) 100%);
        box-shadow: 5px 0 15px var(--dark-shadow);
    }

    .sidebar-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 20px 30px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 30px;
    }

    .user-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background-color: var(--panel-cream);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: var(--topbar-brown);
        font-size: 24px;
        margin-bottom: 15px;
        border: 3px solid var(--sales-orange);
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }

    body.dark-mode .user-avatar {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
    }

    .user-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 15px rgba(0,0,0,0.3);
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-name {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 5px;
        font-family: 'Inter', sans-serif;
        text-align: center;
        letter-spacing: 0.5px;
    }

    .user-status {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--panel-cream);
        opacity: 0.8;
        font-weight: 500;
    }

    body.dark-mode .user-status {
        color: var(--dark-text-secondary);
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        background-color: #2ECC71;
        border-radius: 50%;
    }

    .nav-menu {
        list-style: none;
        padding: 0 15px;
    }

    .nav-menu li {
        margin-bottom: 5px;
    }

    .nav-menu a {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 20px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
        font-size: 15px;
        letter-spacing: 0.3px;
    }

    body.dark-mode .nav-menu a {
        color: rgba(255,255,255,0.8);
    }

    .nav-menu a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 3px;
        height: 100%;
        background-color: var(--sales-orange);
        transform: translateX(-10px);
        transition: all 0.3s ease;
        opacity: 0;
    }

    .nav-menu a:hover {
        background-color: rgba(255,255,255,0.1);
        color: white;
        transform: translateX(5px);
    }

    body.dark-mode .nav-menu a:hover {
        background-color: rgba(255,255,255,0.15);
    }

    .nav-menu a:hover::before {
        transform: translateX(0);
        opacity: 1;
    }

    .nav-menu a.active {
        background-color: rgba(255,255,255,0.15);
        color: white;
        font-weight: 600;
    }

    body.dark-mode .nav-menu a.active {
        background-color: rgba(255,255,255,0.2);
    }

    .nav-menu a.active::before {
        transform: translateX(0);
        opacity: 1;
    }

    .nav-menu i {
        width: 20px;
        text-align: center;
        font-size: 18px;
        color: var(--panel-cream);
    }

    .nav-menu a.active i {
        color: var(--sales-orange);
    }

    .sidebar-footer {
        padding: 20px;
        margin-top: 30px;
        border-top: 1px solid rgba(255,255,255,0.1);
        text-align: center;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 12px;
        background-color: rgba(255,255,255,0.1);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .logout-btn:hover {
        background-color: rgba(255,255,255,0.2);
        transform: translateY(-2px);
    }

    /* Language Switcher */
    .language-switcher {
        position: relative;
        margin: 15px 20px;
    }

    .language-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 12px 15px;
        background-color: rgba(255,255,255,0.1);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .language-btn:hover {
        background-color: rgba(255,255,255,0.15);
    }

    .language-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: var(--topbar-brown);
        border-radius: 8px;
        margin-top: 5px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        display: none;
        z-index: 1000;
    }

    body.dark-mode .language-options {
        background-color: var(--dark-bg-secondary);
    }

    .language-options.active {
        display: block;
    }

    .language-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }

    .language-option:hover {
        background-color: rgba(255,255,255,0.1);
    }

    .language-option.active {
        background-color: rgba(217, 122, 65, 0.3);
    }

    .language-flag {
        width: 20px;
        height: 15px;
        border-radius: 2px;
        object-fit: cover;
    }

    /* Main Content Area */
    .main-content {
        flex: 1;
        padding: 30px;
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    body.dark-mode .main-content {
        background-color: var(--dark-bg-primary);
    }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 50px;
        font-weight: 800;
        font-family: 'Inter', sans-serif;
        color: var(--topbar-brown);
        position: relative;
        display: inline-block;
        letter-spacing: 0.5px;
    }

    body.dark-mode .page-title {
        color: var(--dark-text-primary);
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 50px;
        height: 3px;
        background-color: var(--sales-orange);
        border-radius: 3px;
    }

    .header-controls {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    /* Fixed Dark Mode Toggle */
    .dark-mode-toggle-header {
        position: relative;
        width: 60px;
        height: 30px;
        background-color: var(--topbar-brown);
        border-radius: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        outline: none;
        overflow: hidden;
    }

    body.dark-mode .dark-mode-toggle-header {
        background-color: var(--dark-bg-tertiary);
    }

    .toggle-slider {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 24px;
        height: 24px;
        background-color: var(--panel-cream);
        border-radius: 50%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    body.dark-mode .toggle-slider {
        transform: translateX(30px);
        background-color: var(--dark-text-primary);
    }

    .toggle-icons {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 8px;
    }

    .toggle-icons i {
        font-size: 12px;
        transition: all 0.3s ease;
        z-index: 1;
        color: var(--sales-orange);
    }

    .toggle-icons .sun {
        color: var(--sales-orange);
        opacity: 0;
    }

    .toggle-icons .moon {
        color: var(--sales-orange);
        opacity: 1;
    }

    body.dark-mode .toggle-icons .sun {
        opacity: 1;
    }

    body.dark-mode .toggle-icons .moon {
        opacity: 0;
    }

    .notification-bell {
        position: relative;
        width: 40px;
        height: 40px;
        background-color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 100;
    }

    body.dark-mode .notification-bell {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 3px 10px var(--dark-shadow);
    }

    .notification-bell:hover {
        transform: scale(1.1) rotate(15deg);
    }

    .notification-badge {
        position: absolute;
        top: -3px;
        right: -3px;
        width: 18px;
        height: 18px;
        background-color: var(--sales-orange);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
    }

    /* Fixed Notification Dropdown */
    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        width: 350px;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        z-index: 1001;
        display: none;
        margin-top: 10px;
        max-height: 400px;
        overflow-y: auto;
    }

    body.dark-mode .notification-dropdown {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 10px 30px var(--dark-shadow);
    }

    .notification-dropdown.active {
        display: block;
    }

    .notification-header {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
        font-weight: 600;
        font-size: 16px;
        color: var(--text-dark);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    body.dark-mode .notification-header {
        color: var(--dark-text-primary);
        border-bottom-color: var(--dark-border);
    }

    .notification-clear {
        background: none;
        border: none;
        color: var(--icon-green);
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
    }

    .notification-list {
        padding: 0;
    }

    .notification-item {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    body.dark-mode .notification-item {
        border-bottom-color: var(--dark-border);
    }

    .notification-item:hover {
        background-color: rgba(106, 127, 70, 0.05);
    }

    body.dark-mode .notification-item:hover {
        background-color: rgba(106, 127, 70, 0.1);
    }

    .notification-item.unread {
        background-color: rgba(74, 137, 220, 0.05);
    }

    body.dark-mode .notification-item.unread {
        background-color: rgba(74, 137, 220, 0.1);
    }

    .notification-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        gap: 5px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .notification-item:hover .notification-actions {
        opacity: 1;
    }

    .mark-as-read-btn {
        background: none;
        border: none;
        color: var(--icon-green);
        cursor: pointer;
        font-size: 12px;
        padding: 2px 5px;
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    .mark-as-read-btn:hover {
        background-color: rgba(106, 127, 70, 0.1);
    }

    .notification-title {
        font-weight: 600;
        font-size: 14px;
        color: var(--text-dark);
        margin-bottom: 5px;
        padding-right: 60px;
    }

    body.dark-mode .notification-title {
        color: var(--dark-text-primary);
    }

    .notification-message {
        font-size: 13px;
        color: #666;
        line-height: 1.4;
        padding-right: 60px;
    }

    body.dark-mode .notification-message {
        color: var(--dark-text-secondary);
    }

    .notification-time {
        font-size: 11px;
        color: #999;
        margin-top: 5px;
    }

    body.dark-mode .notification-time {
        color: var(--dark-text-secondary);
    }

    .notification-empty {
        padding: 30px 20px;
        text-align: center;
        color: #999;
    }

    body.dark-mode .notification-empty {
        color: var(--dark-text-secondary);
    }

    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, var(--panel-cream) 0%, #E8DFC8 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(217, 122, 65, 0.3);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .welcome-banner {
        background: linear-gradient(135deg, var(--dark-bg-secondary) 0%, var(--dark-bg-tertiary) 100%);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .welcome-content h2 {
        font-size: 22px;
        font-weight: 700;
        color: var(--topbar-brown);
        margin-bottom: 10px;
        letter-spacing: 0.3px;
    }

    body.dark-mode .welcome-content h2 {
        color: var(--dark-text-primary);
    }

    .welcome-content p {
        color: var(--text-dark);
        max-width: 600px;
        margin-bottom: 15px;
        font-size: 15px;
        line-height: 1.5;
    }

    body.dark-mode .welcome-content p {
        color: var(--dark-text-secondary);
    }

    .welcome-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 100px;
        color: rgba(217, 122, 65, 0.1);
        z-index: 1;
    }

    body.dark-mode .welcome-icon {
        color: rgba(217, 122, 65, 0.2);
    }

    /* User Tabs */
    .user-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .user-tab {
        padding: 12px 20px;
        border-radius: 8px;
        background-color: white;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s;
        border: 1px solid rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    body.dark-mode .user-tab {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .user-tab:hover {
        background-color: rgba(106, 127, 70, 0.1);
        transform: translateY(-2px);
    }

    body.dark-mode .user-tab:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .user-tab.active {
        background-color: var(--icon-green);
        color: white;
        border-color: var(--icon-green);
    }

    /* Dashboard Cards */
    .dashboard-card {
        background-color: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    body.dark-mode .dashboard-card {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 5px 15px var(--dark-shadow);
        border-color: var(--dark-border);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    body.dark-mode .card-header {
        border-bottom-color: var(--dark-border);
    }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: 0.3px;
    }

    body.dark-mode .card-title {
        color: var(--dark-text-primary);
    }

    .card-title i {
        color: var(--icon-green);
    }

    .view-all {
        color: var(--icon-green);
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
    }

    .view-all:hover {
        color: var(--sales-orange);
        transform: translateX(3px);
    }

    /* Search and Filter */
    .search-filter {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .search-box {
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-dark);
        opacity: 0.5;
    }

    body.dark-mode .search-box i {
        color: var(--dark-text-secondary);
    }

    .search-box input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        background-color: white;
        color: var(--text-dark);
    }

    body.dark-mode .search-box input {
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    body.dark-mode .search-box input:focus {
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.3);
    }

    .filter-dropdown {
        padding: 12px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        background-color: white;
        width: 100%;
        transition: all 0.3s;
        color: var(--text-dark);
    }

    body.dark-mode .filter-dropdown {
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .filter-dropdown:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    body.dark-mode .filter-dropdown:focus {
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.3);
    }

    /* Tables */
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    table thead {
        position: sticky;
        top: 0;
    }

    th {
        background-color: rgba(106, 127, 70, 0.08);
        font-weight: 600;
        color: var(--icon-green);
        padding: 12px 15px;
        text-align: left;
        border-bottom: 2px solid rgba(106, 127, 70, 0.2);
        font-size: 14px;
        letter-spacing: 0.3px;
    }

    body.dark-mode th {
        background-color: rgba(106, 127, 70, 0.2);
        color: var(--icon-green);
        border-bottom-color: rgba(106, 127, 70, 0.4);
    }

    td {
        padding: 14px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-size: 14px;
        background-color: white;
        color: var(--text-dark);
    }

    body.dark-mode td {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-bottom-color: var(--dark-border);
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover td {
        background-color: rgba(106, 127, 70, 0.03);
    }

    body.dark-mode tr:hover td {
        background-color: rgba(106, 127, 70, 0.1);
    }

    /* Badges */
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }

    .badge-success {
        background-color: rgba(112, 139, 76, 0.1);
        color: var(--stock-green);
    }

    body.dark-mode .badge-success {
        background-color: rgba(112, 139, 76, 0.3);
        color: #a8d08d;
    }

    .badge-warning {
        background-color: rgba(217, 122, 65, 0.1);
        color: var(--sales-orange);
    }

    body.dark-mode .badge-warning {
        background-color: rgba(217, 122, 65, 0.3);
        color: #f8b88b;
    }

    .badge-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    body.dark-mode .badge-danger {
        background-color: rgba(220, 53, 69, 0.3);
        color: #f8a0a0;
    }

    /* Status Indicators */
    .status-active {
        color: var(--stock-green);
    }

    .status-inactive {
        color: #dc3545;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        text-decoration: none;
        border: none;
        position: relative;
        overflow: hidden;
        letter-spacing: 0.3px;
    }

    .btn::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
        z-index: 1;
    }

    .btn-primary {
        background: linear-gradient(90deg, var(--icon-green) 0%, var(--stock-green) 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(106, 127, 70, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(106, 127, 70, 0.4);
    }

    .btn-secondary {
        background-color: white;
        color: var(--text-dark);
        border: 1px solid rgba(0,0,0,0.1);
    }

    body.dark-mode .btn-secondary {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border);
    }

    .btn-secondary:hover {
        background-color: #f5f5f5;
        transform: translateY(-2px);
    }

    body.dark-mode .btn-secondary:hover {
        background-color: var(--dark-bg-secondary);
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #bb2d3b;
        transform: translateY(-2px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-dark);
        opacity: 0.7;
    }

    body.dark-mode .empty-state {
        color: var(--dark-text-secondary);
    }

    .empty-state i {
        font-size: 50px;
        color: var(--icon-green);
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 15px;
    }

    /* Forms */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-dark);
    }

    body.dark-mode .form-group label {
        color: var(--dark-text-primary);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        background-color: white;
        color: var(--text-dark);
    }

    body.dark-mode .form-control {
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    body.dark-mode .form-control:focus {
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.3);
    }

    .form-row {
        display: flex;
        gap: 15px;
    }

    .form-col {
        flex: 1;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        animation: fadeIn 0.3s;
        overflow-y: auto;
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 25px;
        border-radius: 12px;
        width: 90%;
        max-width: 700px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        animation: slideDown 0.3s;
        max-height: 90vh;
        overflow-y: auto;
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 10px 30px var(--dark-shadow);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    body.dark-mode .modal-header {
        border-bottom-color: var(--dark-border);
    }

    .modal-header h2 {
        font-size: 20px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    body.dark-mode .modal-header h2 {
        color: var(--dark-text-primary);
    }

    .close-modal {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 24px;
        font-weight: bold;
        color: var(--text-dark);
        opacity: 0.5;
        cursor: pointer;
        transition: all 0.3s;
    }

    body.dark-mode .close-modal {
        color: var(--dark-text-primary);
    }

    .close-modal:hover {
        opacity: 1;
        color: var(--icon-green);
        transform: rotate(90deg);
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    body.dark-mode .modal-footer {
        border-top-color: var(--dark-border);
    }

    /* Message Styles */
    .message-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .message {
        padding: 15px;
        border-radius: 8px;
        background-color: #f5f5f5;
        position: relative;
    }

    body.dark-mode .message {
        background-color: var(--dark-bg-tertiary);
    }

    .message.sent {
        background-color: rgba(106, 127, 70, 0.1);
        align-self: flex-end;
        border-right: 3px solid var(--icon-green);
    }

    body.dark-mode .message.sent {
        background-color: rgba(106, 127, 70, 0.3);
    }

    .message.received {
        background-color: rgba(217, 122, 65, 0.1);
        align-self: flex-start;
        border-left: 3px solid var(--sales-orange);
    }

    body.dark-mode .message.received {
        background-color: rgba(217, 122, 65, 0.3);
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .message-time {
        font-size: 12px;
        color: var(--text-dark);
        opacity: 0.7;
    }

    body.dark-mode .message-time {
        color: var(--dark-text-secondary);
    }

    /* Mobile Menu Toggle */
    .mobile-menu-toggle {
        display: none;
        width: 40px;
        height: 40px;
        background-color: var(--sales-orange);
        color: white;
        border-radius: 8px;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 100;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    /* Resume Download */
    .resume-download {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        background-color: #4a89dc;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s;
    }
    
    .resume-download:hover {
        background-color: #3a70c2;
        transform: translateY(-1px);
    }
    
    .no-resume {
        color: #999;
        font-style: italic;
    }

    body.dark-mode .no-resume {
        color: var(--dark-text-secondary);
    }

    /* Responsive styles */
    @media (max-width: 1200px) {
        .sidebar {
            width: 240px;
        }
    }

    @media (max-width: 992px) {
        .sidebar {
            position: fixed;
            left: -100%;
            transition: all 0.3s ease;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
        
        .mobile-menu-toggle {
            display: flex;
        }

        .search-filter {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 36px;
        }

        .user-tabs {
            flex-wrap: wrap;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }

    @media (max-width: 576px) {
        .header {
            flex-direction: column;
            gap: 15px;
        }

        .page-title {
            font-size: 30px;
        }

        .modal-content {
            padding: 20px;
        }

        .modal-footer {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideDown {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    </style>
</head>
<body>

    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                <?php else: ?>
                    <?php echo $admin_initials; ?>
                <?php endif; ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
            <div class="user-status">
                <span class="status-indicator"></span>
                <span><?php echo $t['administrator']; ?></span>
            </div>
        </div>
        
        <!-- Language Switcher -->
        <div class="language-switcher">
            <button class="language-btn" id="languageBtn">
                <span><?php echo $language === 'en' ? 'English' : 'Filipino'; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="language-options" id="languageOptions">
                <button class="language-option <?php echo $language === 'en' ? 'active' : ''; ?>" data-lang="en">
                    <img src="img/us.png" class="language-flag">
                    English
                </button>
                <button class="language-option <?php echo $language === 'tl' ? 'active' : ''; ?>" data-lang="tl">
                    <img src="img/ph.png" class="language-flag">
                    Filipino
                </button>
            </div>
        </div>
        
        <ul class="nav-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="inventory.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory']; ?></a></li>
            <li><a href="users.php" class="active"><i class="fas fa-users"></i> <?php echo $t['users']; ?></a></li>
            <li><a href="pricing.php"><i class="fas fa-tags"></i> <?php echo $t['pricing_control']; ?></a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> <?php echo $t['reports_analytics']; ?></a></li>
            <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i> <?php echo $t['transactions']; ?></a></li>
             <li><a href="loyalty.php" ><i class="fas fa-award"></i> <?php echo $t['loyalty_program']; ?></a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i></i> <?php echo $t['profile']; ?></a></li>
         
        </ul>
        
        <div class="sidebar-footer">
            <a href="../customer-portal/Login/login.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
            </a>
        </div>
    </div>
    
   
    <div class="main-content">
        <?php if ($success): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h1 class="page-title"><?php echo $t['user_management']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle-header" id="darkModeToggleHeader">
                    <div class="toggle-slider"></div>
                    <div class="toggle-icons">
                        <i class="fas fa-sun"></i>
                        <i class="fas fa-moon"></i>
                    </div>
                </button>
                <div class="notification-bell" id="notificationBell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge"><?php echo count($messages); ?></span>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <span>Notifications</span>
                            <button class="notification-clear"><?php echo $t['clear_all']; ?></button>
                        </div>
                        <div class="notification-list">
                            <?php if (!empty($messages)): ?>
                                <?php foreach (array_slice($messages, 0, 5) as $index => $message): ?>
                                    <div class="notification-item unread" data-notification-id="<?php echo $index; ?>">
                                        <div class="notification-actions">
                                            <button class="mark-as-read-btn" title="<?php echo $t['mark_as_read']; ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                        <div class="notification-title">
                                            <?php echo htmlspecialchars($message['sender_name']); ?>
                                        </div>
                                        <div class="notification-message">
                                            <?php echo htmlspecialchars(substr($message['message'], 0, 50)); ?>
                                            <?php if (strlen($message['message']) > 50): ?>...<?php endif; ?>
                                        </div>
                                        <div class="notification-time">
                                            <?php echo date('M j, g:i A', strtotime($message['created_at'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="notification-empty">
                                    <i class="fas fa-bell-slash"></i>
                                    <p>No new notifications</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2><?php echo $t['employee_management_system'] ?? 'Employee Management System'; ?></h2>
                <p><?php echo $t['welcome_message']; ?></p>
                <button class="btn btn-primary" onclick="openAddEmployeeModal()" style="display: inline-flex; align-items: center; gap: 10px;">
                    <i class="fas fa-user-plus"></i> <?php echo $t['new_employee']; ?>
                </button>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
        
       
        <div class="user-tabs">
            <a href="users.php?tab=employees&lang=<?php echo $language; ?>" class="user-tab <?php echo $current_tab === 'employees' ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> <?php echo $t['employees']; ?>
            </a>
            <a href="users.php?tab=roles&lang=<?php echo $language; ?>" class="user-tab <?php echo $current_tab === 'roles' ? 'active' : ''; ?>">
                <i class="fas fa-user-shield"></i> <?php echo $t['roles_permissions']; ?>
            </a>
            <a href="users.php?tab=attendance&lang=<?php echo $language; ?>" class="user-tab <?php echo $current_tab === 'attendance' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i> <?php echo $t['attendance']; ?>
            </a>
            <a href="users.php?tab=messages&lang=<?php echo $language; ?>" class="user-tab <?php echo $current_tab === 'messages' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> <?php echo $t['messages']; ?>
                <?php if (count($messages) > 0): ?>
                    <span style="background-color: var(--sales-orange); color: white; border-radius: 50%; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;">
                        <?php echo count($messages); ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
        
        <?php if ($current_tab === 'employees'): ?>
           
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-tie"></i> <?php echo $t['employee_accounts']; ?></h3>
                    <div class="card-actions">
                        <button class="btn btn-primary" onclick="openAddEmployeeModal()">
                            <i class="fas fa-user-plus"></i> <?php echo $t['add_employee']; ?>
                        </button>
                    </div>
                </div>
                
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="employeeSearch" placeholder="<?php echo $t['search_employees']; ?>" 
                               value="<?php echo htmlspecialchars($employee_search); ?>"
                               onkeyup="if(event.keyCode === 13) applyEmployeeFilters()">
                    </div>
                    <select class="filter-dropdown" id="roleFilter" onchange="applyEmployeeFilters()">
                        <option value="0"><?php echo $t['all_roles']; ?></option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" 
                                <?php echo $role_filter == $role['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select class="filter-dropdown" id="statusFilter" onchange="applyEmployeeFilters()">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>><?php echo $t['all_status']; ?></option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>><?php echo $t['active']; ?></option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>><?php echo $t['inactive']; ?></option>
                    </select>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['name']; ?></th>
                                <th><?php echo $t['username']; ?></th>
                                <th><?php echo $t['role']; ?></th>
                                <th><?php echo $t['contact']; ?></th>
                                <th><?php echo $t['email']; ?></th>
                                <th><?php echo $t['resume']; ?></th>
                                <th><?php echo $t['status']; ?></th>
                                <th><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($employees)): ?>
                                <?php foreach ($employees as $employee): ?>
                                    <tr data-employee-id="<?php echo $employee['id']; ?>">
                                        <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['username']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['role_name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['contact_number']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td>
                                            <?php if ($employee['resume']): ?>
                                                <a href="<?php echo htmlspecialchars($employee['resume']); ?>" class="resume-download" download>
                                                    <i class="fas fa-file-download"></i> <?php echo $t['download']; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="no-resume"><?php echo $t['no_resume']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $employee['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $employee['is_active'] ? $t['active'] : $t['inactive']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary edit-employee-btn">
                                                <i class="fas fa-edit"></i> <?php echo $t['edit']; ?>
                                            </button>
                                            <button class="btn btn-danger" onclick="confirmDeleteEmployee(<?php echo $employee['id']; ?>, '<?php echo htmlspecialchars($employee['full_name']); ?>')">
                                                <i class="fas fa-trash"></i> <?php echo $t['delete']; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_employees_found']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($current_tab === 'roles'): ?>
           
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-shield"></i> <?php echo $t['roles_permissions']; ?></h3>
                    <div class="card-actions">
                        <button class="btn btn-primary" onclick="openAddRoleModal()">
                            <i class="fas fa-plus"></i> <?php echo $t['add_new_role']; ?>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['role_name']; ?></th>
                                <th><?php echo $t['description']; ?></th>
                                <th><?php echo $t['status']; ?></th>
                                <th><?php echo $t['actions']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <tr data-role-id="<?php echo $role['id']; ?>" data-role-is-active="<?php echo $role['is_active']; ?>">
                                        <td><?php echo htmlspecialchars($role['role_name']); ?></td>
                                        <td><?php echo htmlspecialchars($role['description']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $role['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $role['is_active'] ? $t['active'] : $t['inactive']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary edit-role-btn">
                                                <i class="fas fa-edit"></i> <?php echo $t['edit']; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_roles_found']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($current_tab === 'attendance'): ?>
           
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-check"></i> <?php echo $t['attendance']; ?></h3>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo $t['user']; ?></th>
                                <th><?php echo $t['username']; ?></th>
                                <th><?php echo $t['login_time']; ?></th>
                                <th><?php echo $t['logout_time']; ?></th>
                                <th><?php echo $t['method']; ?></th>
                                <th><?php echo $t['duration']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($attendance_logs)): ?>
                                <?php foreach ($attendance_logs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['employee_name']); ?></td>
                                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($log['login_time'])); ?></td>
                                        <td>
                                            <?php if ($log['logout_time']): ?>
                                                <?php echo date('M j, Y g:i A', strtotime($log['logout_time'])); ?>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><?php echo $t['active']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['method']); ?></td>
                                        <td>
                                            <?php if ($log['logout_time']): ?>
                                                <?php 
                                                    $start = new DateTime($log['login_time']);
                                                    $end = new DateTime($log['logout_time']);
                                                    $diff = $start->diff($end);
                                                    echo $diff->format('%h hours %i minutes');
                                                ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-info-circle"></i>
                                        <p><?php echo $t['no_attendance_logs']; ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
               
                <?php if ($total_attendance_pages > 1): ?>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; padding: 20px;">
                        <?php if ($attendance_page > 1): ?>
                            <a href="users.php?tab=attendance&attendance_page=<?php echo $attendance_page - 1; ?>&lang=<?php echo $language; ?>" 
                               class="btn btn-secondary" style="text-decoration: none;">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous_page']; ?>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed;">
                                <i class="fas fa-chevron-left"></i> <?php echo $t['previous_page']; ?>
                            </button>
                        <?php endif; ?>
                        
                        <span style="font-size: 14px; color: var(--text-dark); font-weight: 500;">
                            <?php echo $t['page']; ?> <?php echo $attendance_page; ?> <?php echo $t['of']; ?> <?php echo $total_attendance_pages; ?>
                        </span>
                        
                        <?php if ($attendance_page < $total_attendance_pages): ?>
                            <a href="users.php?tab=attendance&attendance_page=<?php echo $attendance_page + 1; ?>&lang=<?php echo $language; ?>" 
                               class="btn btn-secondary" style="text-decoration: none;">
                                <?php echo $t['next_page']; ?> <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed;">
                                <?php echo $t['next_page']; ?> <i class="fas fa-chevron-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($current_tab === 'messages'): ?>
           
            <div style="display: flex; background-color: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: 600px; overflow: hidden;">
              
                <div style="width: 350px; background-color: var(--panel-cream); border-right: 1px solid rgba(0,0,0,0.1); display: flex; flex-direction: column;">
                    <div style="padding: 20px; background: linear-gradient(135deg, var(--topbar-brown) 0%, #2A2520 100%); color: white; display: flex; align-items: center; justify-content: space-between;">
                        <div style="font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-comments"></i>
                            <?php echo $t['messages']; ?>
                            <?php if (count($messages) > 0): ?>
                                <span style="background-color: var(--sales-orange); color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;">
                                    <?php echo count($messages); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <button onclick="openNewMessageModal()" style="width: 35px; height: 35px; background-color: var(--sales-orange); border: none; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    
                    <div style="padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.1);">
                        <input type="text" placeholder="<?php echo $t['search_conversations']; ?>" style="width: 100%; padding: 12px 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 25px; font-size: 14px; background-color: white; transition: all 0.3s ease;" id="searchConversations" onkeyup="filterConversations()">
                    </div>
                    
                    <div style="flex: 1; overflow-y: auto; padding: 10px 0;" id="conversationsList">
                        <?php 
                        // Get unique conversations grouped by employee
                        $conversations = [];
                        $admin_employee_check = $conn->prepare("SELECT id FROM employees WHERE email = ? OR username LIKE ? LIMIT 1");
                        $admin_employee_check->execute([$user['email'], 'admin_%']);
                        $admin_emp = $admin_employee_check->fetch(PDO::FETCH_ASSOC);
                        $admin_employee_id = $admin_emp ? $admin_emp['id'] : null;
                        
                        foreach ($messages as $message) {
                            $other_user_id = ($admin_employee_id && $message['sender_id'] == $admin_employee_id) ? $message['receiver_id'] : $message['sender_id'];
                            $other_user_name = ($admin_employee_id && $message['sender_id'] == $admin_employee_id) ? $message['receiver_name'] : $message['sender_name'];
                            
                            if (!isset($conversations[$other_user_id])) {
                                $conversations[$other_user_id] = [
                                    'user_id' => $other_user_id,
                                    'user_name' => $other_user_name,
                                    'last_message' => $message['message'],
                                    'last_time' => $message['created_at'],
                                    'unread_count' => 0
                                ];
                            }
                        }
                        ?>
                        
                        <?php if (!empty($conversations)): ?>
                            <?php foreach ($conversations as $conv): ?>
                                <div class="conversation-item" data-user-id="<?php echo $conv['user_id']; ?>" onclick="openConversation(<?php echo $conv['user_id']; ?>, '<?php echo htmlspecialchars($conv['user_name']); ?>')" style="display: flex; align-items: center; padding: 15px 20px; cursor: pointer; transition: all 0.3s ease; border-bottom: 1px solid rgba(0,0,0,0.05); position: relative;">
                                    <div style="width: 50px; height: 50px; border-radius: 50%; background-color: var(--icon-green); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 15px; position: relative;">
                                        <?php 
                                        $names = explode(' ', $conv['user_name']);
                                        echo strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
                                        ?>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 600; font-size: 15px; color: var(--text-dark); margin-bottom: 3px;">
                                            <?php echo htmlspecialchars($conv['user_name']); ?>
                                        </div>
                                        <div style="font-size: 13px; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)) . (strlen($conv['last_message']) > 50 ? '...' : ''); ?>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px;">
                                        <div style="font-size: 11px; color: #999;">
                                            <?php 
                                            date_default_timezone_set('Asia/Manila');
                                            $time = strtotime($conv['last_time']);
                                            if (date('Y-m-d') == date('Y-m-d', $time)) {
                                                echo date('h:i A', $time);
                                            } else {
                                                echo date('M j', $time);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding: 40px 20px; text-align: center; color: #666;">
                                <i class="fas fa-comments" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
                                <p><?php echo $t['no_conversations']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                
                <div style="flex: 1; display: flex; flex-direction: column; background-color: #f8f9fa;" id="chatMain">
                    <div id="emptyChat" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #666; text-align: center; padding: 40px;">
                        <i class="fas fa-comments" style="font-size: 60px; color: var(--icon-green); margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3 style="font-size: 18px; margin-bottom: 10px; color: var(--text-dark);"><?php echo $t['welcome_to_messages']; ?></h3>
                        <p style="font-size: 14px; max-width: 300px;"><?php echo $t['select_conversation']; ?></p>
                    </div>
                    
                    <div id="conversationHeader" style="display: none; padding: 20px; background-color: white; border-bottom: 1px solid rgba(0,0,0,0.1); align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div id="conversationAvatar" style="width: 45px; height: 45px; border-radius: 50%; background-color: var(--icon-green); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;"></div>
                            <div>
                                <h3 id="conversationUserName" style="font-size: 16px; font-weight: 600; color: var(--text-dark); margin-bottom: 2px;"></h3>
                                <p id="conversationUserRole" style="font-size: 12px; color: #666;"><?php echo $t['employee']; ?></p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button onclick="toggleProfileSidebar()" style="width: 35px; height: 35px; border: none; border-radius: 50%; background-color: var(--panel-cream); color: var(--text-dark); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" title="<?php echo $t['view_profile']; ?>">
                                <i class="fas fa-user"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="messagesContainer" style="display: none; flex: 1; overflow-y: auto; padding: 20px; gap: 15px; flex-direction: column;"></div>
                    
                    <div id="messageInputContainer" style="display: none; padding: 20px; background-color: white; border-top: 1px solid rgba(0,0,0,0.1);">
                        <form id="messageForm" style="display: flex; align-items: center; gap: 15px; background-color: #f8f9fa; border-radius: 25px; padding: 10px 20px;" onsubmit="sendMessage(event)">
                            <textarea id="messageInput" placeholder="<?php echo $t['type_message_placeholder']; ?>" style="flex: 1; border: none; background: transparent; font-size: 14px; padding: 8px 0; outline: none; resize: none; max-height: 100px;" rows="1"></textarea>
                            <button type="submit" style="width: 40px; height: 40px; border: none; border-radius: 50%; background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                  
                <div id="profileSidebar" style="width: 300px; background-color: white; border-left: 1px solid rgba(0,0,0,0.1); display: none; flex-direction: column;">
                    <div style="padding: 30px 20px; text-align: center; background: linear-gradient(135deg, var(--panel-cream) 0%, #E8DFC8 100%); border-bottom: 1px solid rgba(0,0,0,0.1);">
                        <div id="profileAvatar" style="width: 80px; height: 80px; border-radius: 50%; background-color: var(--icon-green); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; margin: 0 auto 15px; border: 3px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);"></div>
                        <div id="profileName" style="font-size: 18px; font-weight: 600; color: var(--text-dark); margin-bottom: 5px;"></div>
                        <div id="profileRole" style="font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;"></div>
                    </div>
                    <div style="padding: 20px; flex: 1;">
                        <div style="margin-bottom: 25px;">
                            <h4 style="font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo $t['contact_information']; ?></h4>
                            <div id="profileContact" style="background-color: #f8f9fa; padding: 15px; border-radius: 10px; font-size: 14px; color: #666;"><?php echo $t['loading']; ?></div>
                        </div>
                        <div style="margin-bottom: 25px;">
                            <h4 style="font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo $t['department']; ?></h4>
                            <div id="profileDepartment" style="background-color: #f8f9fa; padding: 15px; border-radius: 10px; font-size: 14px; color: #666;"><?php echo $t['loading']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>


    <div class="modal" id="addEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> <?php echo $t['add_new_employee']; ?></h2>
                <button class="close-modal" onclick="closeModal('addEmployeeModal')">&times;</button>
            </div>
            <form action="users.php?tab=employees&lang=<?php echo $language; ?>" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="first_name"><?php echo $t['first_name']; ?></label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="last_name"><?php echo $t['last_name']; ?></label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="username"><?php echo $t['username']; ?></label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="password"><?php echo $t['password']; ?></label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role_id"><?php echo $t['role']; ?></label>
                    <select id="role_id" name="role_id" class="form-control" required>
                        <?php foreach ($roles as $role): ?>
                            <?php if ($role['is_active'] == 1): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="contact_number"><?php echo $t['contact_number']; ?></label>
                            <input type="tel" id="contact_number" name="contact_number" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="email"><?php echo $t['email']; ?></label>
                            <input type="email" id="email" name="email" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address"><?php echo $t['address']; ?></label>
                    <textarea id="address" name="address" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="resume"><?php echo $t['resume']; ?> (PDF, DOC, DOCX)</label>
                    <input type="file" id="resume" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addEmployeeModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="add_employee" class="btn btn-primary"><?php echo $t['add_employee']; ?></button>
                </div>
            </form>
        </div>
    </div>

     
    <div class="modal" id="editEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-edit"></i> <?php echo $t['edit_employee']; ?></h2>
                <button class="close-modal" onclick="closeModal('editEmployeeModal')">&times;</button>
            </div>
            <form action="users.php?tab=employees&lang=<?php echo $language; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_employee_id" name="employee_id">
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit_first_name"><?php echo $t['first_name']; ?></label>
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit_last_name"><?php echo $t['last_name']; ?></label>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_role_id"><?php echo $t['role']; ?></label>
                    <select id="edit_role_id" name="role_id" class="form-control" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>">
                                <?php echo htmlspecialchars($role['role_name']); ?>
                                <?php if ($role['is_active'] == 0): ?>
                                    (<?php echo $t['inactive']; ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit_contact_number"><?php echo $t['contact_number']; ?></label>
                            <input type="tel" id="edit_contact_number" name="contact_number" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit_email"><?php echo $t['email']; ?></label>
                            <input type="email" id="edit_email" name="email" class="form-control">
                        </div>
                        </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_address"><?php echo $t['address']; ?></label>
                    <textarea id="edit_address" name="address" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_resume"><?php echo $t['resume']; ?> (PDF, DOC, DOCX)</label>
                    <input type="file" id="edit_resume" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                    <small id="currentResumeText" class="text-muted"></small>
                </div>
                
                <div class="form-group">
                    <label for="edit_password"><?php echo $t['new_password']; ?></label>
                    <input type="password" id="edit_password" name="password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="edit_is_active"><?php echo $t['status']; ?></label>
                    <select id="edit_is_active" name="is_active" class="form-control">
                        <option value="1"><?php echo $t['active']; ?></option>
                        <option value="0"><?php echo $t['inactive']; ?></option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editEmployeeModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="update_employee" class="btn btn-primary"><?php echo $t['update_employee']; ?></button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal" id="deleteEmployeeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> <?php echo $t['confirm_deletion']; ?></h2>
                <button class="close-modal" onclick="closeModal('deleteEmployeeModal')">&times;</button>
            </div>
            <div class="confirmation-dialog">
                <p><?php echo $t['delete_employee_confirm']; ?> "<span id="employeeToDeleteName"></span>"?</p>
                <p class="text-danger"><strong><?php echo $t['warning']; ?>:</strong> <?php echo $t['delete_warning']; ?></p>
                <form action="users.php?tab=employees&lang=<?php echo $language; ?>" method="POST" style="display: inline;">
                    <input type="hidden" id="delete_employee_id" name="employee_id">
                    <div class="buttons">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('deleteEmployeeModal')"><?php echo $t['cancel']; ?></button>
                        <button type="submit" name="delete_employee" class="btn btn-danger"><?php echo $t['delete']; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   
    <div class="modal" id="addRoleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-shield"></i> <?php echo $t['add_new_role']; ?></h2>
                <button class="close-modal" onclick="closeModal('addRoleModal')">&times;</button>
            </div>
            <form action="users.php?tab=roles&lang=<?php echo $language; ?>" method="POST">
                <div class="form-group">
                    <label for="role_name"><?php echo $t['role_name']; ?></label>
                    <input type="text" id="role_name" name="role_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description"><?php echo $t['description']; ?></label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addRoleModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="add_role" class="btn btn-primary"><?php echo $t['add_new_role']; ?></button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal" id="editRoleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-shield"></i> <?php echo $t['edit_role']; ?></h2>
                <button class="close-modal" onclick="closeModal('editRoleModal')">&times;</button>
            </div>
            <form action="users.php?tab=roles&lang=<?php echo $language; ?>" method="POST">
                <input type="hidden" id="edit_role_id" name="role_id">
                
                <div class="form-group">
                    <label for="edit_role_name"><?php echo $t['role_name']; ?></label>
                    <input type="text" id="edit_role_name" name="role_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_description"><?php echo $t['description']; ?></label>
                    <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_role_is_active"><?php echo $t['status']; ?></label>
                    <select id="edit_role_is_active" name="is_active" class="form-control">
                        <option value="1"><?php echo $t['active']; ?></option>
                        <option value="0"><?php echo $t['inactive']; ?></option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editRoleModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="update_role" class="btn btn-primary"><?php echo $t['update_role']; ?></button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal" id="newMessageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-envelope"></i> <?php echo $t['new_message']; ?></h2>
                <button class="close-modal" onclick="closeModal('newMessageModal')">&times;</button>
            </div>
            <form action="users.php?tab=messages&lang=<?php echo $language; ?>" method="POST" onsubmit="return validateMessageForm()">
                <div class="form-group">
                    <label for="receiver_id"><?php echo $t['to']; ?></label>
                    <select id="receiver_id" name="receiver_id" class="form-control" required>
                        <option value=""><?php echo $t['select_employee']; ?></option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message"><?php echo $t['type_message'] ?? 'Message'; ?></label>
                    <textarea id="message" name="message" class="form-control" rows="5" required placeholder="<?php echo $t['type_message_placeholder'] ?? 'Type a message...'; ?>"></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('newMessageModal')"><?php echo $t['cancel']; ?></button>
                    <button type="submit" name="send_message" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> <?php echo $t['send_message']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Language switcher
        const languageBtn = document.getElementById('languageBtn');
        const languageOptions = document.getElementById('languageOptions');
        
        languageBtn.addEventListener('click', function() {
            languageOptions.classList.toggle('active');
        });
        
        document.querySelectorAll('.language-option').forEach(option => {
            option.addEventListener('click', function() {
                const lang = this.getAttribute('data-lang');
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('lang', lang);
                window.location.href = currentUrl.toString();
            });
        });
        
        // Close language dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!languageBtn.contains(event.target) && !languageOptions.contains(event.target)) {
                languageOptions.classList.remove('active');
            }
        });

        // Dark mode toggle with improved animation
        const darkModeToggleHeader = document.getElementById('darkModeToggleHeader');
        const body = document.body;
        
        // Check for saved dark mode preference
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        if (isDarkMode) {
            body.classList.add('dark-mode');
            updateDarkModeIcon();
        }
        
        darkModeToggleHeader.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
            updateDarkModeIcon();
        });

        function updateDarkModeIcon() {
            const toggleSlider = document.querySelector('.toggle-slider');
            const sunIcon = document.querySelector('.toggle-icons .fa-sun');
            const moonIcon = document.querySelector('.toggle-icons .fa-moon');
            
            if (body.classList.contains('dark-mode')) {
                toggleSlider.style.transform = 'translateX(30px)';
                sunIcon.style.opacity = '1';
                moonIcon.style.opacity = '0';
                darkModeToggleHeader.title = 'Toggle Light Mode';
            } else {
                toggleSlider.style.transform = 'translateX(3px)';
                sunIcon.style.opacity = '0';
                moonIcon.style.opacity = '1';
                darkModeToggleHeader.title = 'Toggle Dark Mode';
            }
        }

        // Fixed Notification dropdown
        const notificationBell = document.getElementById('notificationBell');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        notificationBell.addEventListener('click', function(event) {
            event.stopPropagation();
            notificationDropdown.classList.toggle('active');
        });
        
        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!notificationBell.contains(event.target) && !notificationDropdown.contains(event.target)) {
                notificationDropdown.classList.remove('active');
            }
        });
        
        // Clear notifications
        document.querySelector('.notification-clear').addEventListener('click', function() {
            document.querySelectorAll('.notification-item').forEach(item => {
                item.remove();
            });
            document.querySelector('.notification-badge').textContent = '0';
            document.querySelector('.notification-list').innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>No new notifications</p>
                </div>
            `;
        });

        // Mark as read functionality
        document.addEventListener('click', function(event) {
            if (event.target.closest('.mark-as-read-btn')) {
                const notificationItem = event.target.closest('.notification-item');
                const notificationId = notificationItem.dataset.notificationId;
                
                // Remove unread styling
                notificationItem.classList.remove('unread');
                
                // Update badge count
                const badge = document.querySelector('.notification-badge');
                const currentCount = parseInt(badge.textContent);
                if (currentCount > 0) {
                    badge.textContent = currentCount - 1;
                }
                
                // In a real implementation, you would send an AJAX request to mark the notification as read
                fetch('users.php?lang=<?php echo $language; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=mark_notification_read&notification_id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Notification marked as read');
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            }
        });

        // Modal functions
        function openAddEmployeeModal() {
            document.getElementById('addEmployeeModal').style.display = 'flex';
        }

        function openEditEmployeeModal(id, firstName, lastName, roleId, contact, email, address, isActive, resume) {
            document.getElementById('edit_employee_id').value = id;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_role_id').value = roleId;
            document.getElementById('edit_contact_number').value = contact;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_is_active').value = isActive;
            
            // Display current resume info
            const currentResumeText = document.getElementById('currentResumeText');
            if (resume) {
                const fileName = resume.split('/').pop();
                currentResumeText.textContent = `Current file: ${fileName}`;
            } else {
                currentResumeText.textContent = 'No resume uploaded';
            }
            
            document.getElementById('editEmployeeModal').style.display = 'flex';
        }

        function confirmDeleteEmployee(id, name) {
            document.getElementById('delete_employee_id').value = id;
            document.getElementById('employeeToDeleteName').textContent = name;
            document.getElementById('deleteEmployeeModal').style.display = 'flex';
        }

        function openAddRoleModal() {
            document.getElementById('addRoleModal').style.display = 'flex';
        }

        function openEditRoleModal(id, name, description, isActive) {
            document.getElementById('edit_role_id').value = id;
            document.getElementById('edit_role_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_role_is_active').value = isActive;
            
            document.getElementById('editRoleModal').style.display = 'flex';
        }

        function openNewMessageModal() {
            document.getElementById('newMessageModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function validateMessageForm() {
            const receiverId = document.getElementById('receiver_id').value;
            const message = document.getElementById('message').value.trim();
            
            if (!receiverId || receiverId === '') {
                alert('Please select a recipient.');
                return false;
            }
            
            if (!message || message === '') {
                alert('Please enter a message.');
                return false;
            }
            
            return true;
        }

        // Filter functions
        function applyEmployeeFilters() {
            const search = document.getElementById('employeeSearch').value;
            const role = document.getElementById('roleFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            let url = 'users.php?tab=employees&lang=<?php echo $language; ?>';
            
            if (search) url += '&employee_search=' + encodeURIComponent(search);
            if (role > 0) url += '&role_filter=' + role;
            if (status !== 'all') url += '&status_filter=' + status;
            
            window.location.href = url;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        // Auto-close alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);

        // Add event listeners for edit buttons in tables
        document.addEventListener('DOMContentLoaded', function() {
            // Handle edit employee buttons
            document.querySelectorAll('.edit-employee-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const id = row.dataset.employeeId;
                    const firstName = row.querySelector('td:nth-child(1)').textContent.split(' ')[0];
                    const lastName = row.querySelector('td:nth-child(1)').textContent.split(' ').slice(1).join(' ');
                    const roleName = row.querySelector('td:nth-child(3)').textContent;
                    const contact = row.querySelector('td:nth-child(4)').textContent;
                    const email = row.querySelector('td:nth-child(5)').textContent;
                    const resume = row.querySelector('td:nth-child(6) a') ? row.querySelector('td:nth-child(6) a').getAttribute('href') : null;
                    const isActive = row.querySelector('td:nth-child(7) span').textContent === '<?php echo $t['active']; ?>' ? '1' : '0';
                    
                    // Get role ID from role name
                    let roleId = 0;
                    <?php foreach ($roles as $role): ?>
                        if ("<?php echo $role['role_name']; ?>" === roleName) {
                            roleId = <?php echo $role['id']; ?>;
                        }
                    <?php endforeach; ?>
                    
                    openEditEmployeeModal(id, firstName, lastName, roleId, contact, email, '', isActive, resume);
                });
            });

            // Handle edit role buttons
            document.querySelectorAll('.edit-role-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const id = row.dataset.roleId;
                    const name = row.querySelector('td:nth-child(1)').textContent.trim();
                    const description = row.querySelector('td:nth-child(2)').textContent.trim();
                    const isActive = row.dataset.roleIsActive;
                    
                    openEditRoleModal(id, name, description, isActive);
                });
            });

            // Add event listener for search input to trigger on Enter key
            const searchInput = document.getElementById('employeeSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(event) {
                    if (event.key === 'Enter') {
                        applyEmployeeFilters();
                    }
                });
            }
        });

        let currentConversationUserId = null;
        let messageRefreshInterval = null;
        let conversationData = {};

        function openConversation(userId, userName) {
            // Update active conversation
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.style.backgroundColor = '';
                item.style.borderLeft = '';
            });
            const conversationItem = document.querySelector(`[data-user-id="${userId}"]`);
            if (conversationItem) {
                conversationItem.style.backgroundColor = 'rgba(106, 127, 70, 0.15)';
                conversationItem.style.borderLeft = '4px solid var(--icon-green)';
            }
            
            currentConversationUserId = userId;
            
            // Show chat interface
            document.getElementById('emptyChat').style.display = 'none';
            document.getElementById('conversationHeader').style.display = 'flex';
            document.getElementById('messagesContainer').style.display = 'flex';
            document.getElementById('messageInputContainer').style.display = 'block';
            
            // Update header
            document.getElementById('conversationUserName').textContent = userName;
            const avatar = document.getElementById('conversationAvatar');
            const names = userName.split(' ');
            const initials = names[0].charAt(0) + (names[1] ? names[1].charAt(0) : '');
            avatar.textContent = initials.toUpperCase();
            
            loadUserProfile(userId).then(() => {
                // Profile will be updated in the sidebar, but we also need to update the header avatar
                fetch('users.php?lang=<?php echo $language; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_user_profile&user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.profile) {
                        if (data.profile.profile_photo && data.profile.profile_photo.trim() !== '') {
                            avatar.innerHTML = `<img src="${data.profile.profile_photo}" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
                        } else {
                            const names = userName.split(' ');
                            const initials = names[0].charAt(0) + (names[1] ? names[1].charAt(0) : '');
                            avatar.textContent = initials.toUpperCase();
                        }
                    }
                });
            });
            
            // Load conversation
            loadConversation(userId);
        }

        function loadConversation(userId) {
            fetch('users.php?lang=<?php echo $language; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_conversation&other_user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessages(data.conversation);
                    scrollMessagesToBottom();
                } else {
                    console.error('Error loading conversation:', data.error);
                }
            })
            .catch(error => {
                console.error('Error loading conversation:', error);
            });
        }

        function loadUserProfile(userId) {
            return fetch('users.php?lang=<?php echo $language; ?>', { // Return the promise
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_user_profile&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateProfileSidebar(data.profile);
                } else {
                    console.error('Error loading user profile:', data.error);
                }
                return data; // Return data for chaining if needed
            })
            .catch(error => {
                console.error('Error loading user profile:', error);
                return { success: false, error: error }; // Return error object
            });
        }

        function updateProfileSidebar(profile) {
            const avatar = document.getElementById('profileAvatar');
            const name = document.getElementById('profileName');
            const role = document.getElementById('profileRole');
            const contact = document.getElementById('profileContact');
            const department = document.getElementById('profileDepartment');
            
            if (profile.profile_photo && profile.profile_photo.trim() !== '') {
                avatar.innerHTML = `<img src="${profile.profile_photo}" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
            } else {
                const initials = profile.first_name.charAt(0) + (profile.last_name ? profile.last_name.charAt(0) : '');
                avatar.textContent = initials.toUpperCase();
                avatar.innerHTML = initials.toUpperCase();
            }
            
            name.textContent = `${profile.first_name} ${profile.last_name}`;
            role.textContent = profile.role_name;
            contact.textContent = profile.email || 'No email provided';
            department.textContent = profile.role_name;
        }

        function displayMessages(messages) {
            const container = document.getElementById('messagesContainer');
            const currentUserId = <?php echo $admin_employee_id ?: 'null'; ?>;
            
            container.innerHTML = '';
            
            messages.forEach(message => {
                const messageDiv = document.createElement('div');
                messageDiv.style.display = 'flex';
                messageDiv.style.alignItems = 'flex-end';
                messageDiv.style.gap = '10px';
                messageDiv.style.maxWidth = '70%';
                messageDiv.style.marginBottom = '15px';
                
                const isSent = message.sender_id == currentUserId;
                if (isSent) {
                    messageDiv.style.alignSelf = 'flex-end';
                    messageDiv.style.flexDirection = 'row-reverse';
                }
                
                const avatar = document.createElement('div');
                avatar.style.width = '30px';
                avatar.style.height = '30px';
                avatar.style.borderRadius = '50%';
                avatar.style.backgroundColor = 'var(--icon-green)';
                avatar.style.display = 'flex';
                avatar.style.alignItems = 'center';
                avatar.style.justifyContent = 'center';
                avatar.style.color = 'white';
                avatar.style.fontSize = '12px';
                avatar.style.fontWeight = 'bold';
                avatar.style.flexShrink = '0';
                avatar.style.overflow = 'hidden';
                
                if (isSent) {
                    // For sent messages, use current user's profile
                    <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                        avatar.innerHTML = `<img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
                    <?php else: ?>
                        avatar.textContent = '<?php echo $admin_initials; ?>';
                    <?php endif; ?>
                } else {
                    // For received messages, we need to fetch the sender's profile picture
                    // We'll use a placeholder for now and load it via a separate function
                    loadSenderAvatar(avatar, message.sender_id);
                }
                
                const content = document.createElement('div');
                content.style.backgroundColor = isSent ? 'var(--icon-green)' : 'white';
                content.style.color = isSent ? 'white' : 'var(--text-dark)';
                content.style.padding = '12px 16px';
                content.style.borderRadius = '18px';
                content.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                content.style.position = 'relative';
                
                const text = document.createElement('div');
                text.style.fontSize = '14px';
                text.style.lineHeight = '1.4';
                text.style.marginBottom = '5px';
                text.textContent = message.message;
                
                const time = document.createElement('div');
                time.style.fontSize = '11px';
                time.style.opacity = '0.7';
                const messageTime = new Date(message.created_at);
                time.textContent = messageTime.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                
                content.appendChild(text);
                content.appendChild(time);
                messageDiv.appendChild(avatar);
                messageDiv.appendChild(content);
                
                container.appendChild(messageDiv);
            });
        }

        function loadSenderAvatar(avatarElement, senderId) {
            fetch('users.php?lang=<?php echo $language; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_user_profile&user_id=${senderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.profile) {
                    if (data.profile.profile_photo && data.profile.profile_photo.trim() !== '') {
                        avatarElement.innerHTML = `<img src="${data.profile.profile_photo}" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
                    } else {
                        const initials = data.profile.first_name.charAt(0) + (data.profile.last_name ? data.profile.last_name.charAt(0) : '');
                        avatarElement.textContent = initials.toUpperCase();
                    }
                } else {
                    // Fallback to initials if profile loading fails
                    avatarElement.textContent = '??';
                }
            })
            .catch(error => {
                console.error('Error loading sender avatar:', error);
                avatarElement.textContent = '??';
            });
        }

        function sendMessage(event) {
            event.preventDefault();
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message || !currentConversationUserId) {
                alert('Please enter a message.');
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('send_message', '1');
            formData.append('receiver_id', currentConversationUserId);
            formData.append('message', message);
            
            fetch('users.php?tab=messages&lang=<?php echo $language; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    // Reload the conversation to show the new message
                    loadConversation(currentConversationUserId);
                } else {
                    alert('Failed to send message. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Error sending message. Please try again.');
            });
        }

        function toggleProfileSidebar() {
            const profileSidebar = document.getElementById('profileSidebar');
            profileSidebar.style.display = profileSidebar.style.display === 'flex' ? 'none' : 'flex';
        }

        function filterConversations() {
            const searchTerm = document.getElementById('searchConversations').value.toLowerCase();
            const conversations = document.querySelectorAll('.conversation-item');
            
            conversations.forEach(conv => {
                const name = conv.querySelector('div:nth-child(2) div:first-child').textContent.toLowerCase();
                const message = conv.querySelector('div:nth-child(2) div:last-child').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || message.includes(searchTerm)) {
                    conv.style.display = 'flex';
                } else {
                    conv.style.display = 'none';
                }
            });
        }

        function scrollMessagesToBottom() {
            const container = document.getElementById('messagesContainer');
            container.scrollTop = container.scrollHeight;
        }

        // Auto-resize message input
        document.addEventListener('DOMContentLoaded', function() {
            const messageInput = document.getElementById('messageInput');
            if (messageInput) {
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                });
                
                messageInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        document.getElementById('messageForm').dispatchEvent(new Event('submit'));
                    }
                });
            }
        });
    </script>
</body>
</html>