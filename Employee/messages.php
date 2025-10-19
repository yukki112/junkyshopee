<?php
session_start();
require_once 'db_connection.php';

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
        'dashboard' => 'Dashboard',
        'transaction_logging' => 'Transaction Logging',
        'attendance' => 'Attendance',
        'inventory_view' => 'Inventory View',
        'sales_reports' => 'Personal Sales Reports',
        'customer_management' => 'Customer Management',
        'loyalty_points' => 'Loyalty Point Input',
        'messages' => 'Messages',
        'profile' => 'Profile',
        'logout' => 'Logout',
        'welcome_messages' => 'Welcome to Messages',
        'select_conversation' => 'Select a conversation from the sidebar to start chatting, or click the + button to start a new conversation.',
        'start_new_conversation' => 'Start New Conversation',
        'search_conversations' => 'Search conversations...',
        'no_conversations' => 'No conversations yet. Start a new chat!',
        'type_message' => 'Type a message...',
        'view_profile' => 'View Profile',
        'mark_all_read' => 'Mark All as Read',
        'contact_information' => 'Contact Information',
        'department' => 'Department',
        'loading' => 'Loading...',
        'no_email' => 'No email provided'
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'transaction_logging' => 'Pag-log ng Transaksyon',
        'attendance' => 'Pagdalo',
        'inventory_view' => 'Tingnan ang Inventory',
        'sales_reports' => 'Mga Personal na Ulat sa Pagbebenta',
        'customer_management' => 'Pamamahala ng Customer',
        'loyalty_points' => 'Input ng Loyalty Points',
        'messages' => 'Mga Mensahe',
        'profile' => 'Profile',
        'logout' => 'Logout',
        'welcome_messages' => 'Maligayang Pagdating sa Mga Mensahe',
        'select_conversation' => 'Pumili ng usapan mula sa sidebar upang magsimulang mag-chat, o i-click ang + button upang magsimula ng bagong usapan.',
        'start_new_conversation' => 'Magsimula ng Bagong Usapan',
        'search_conversations' => 'Maghanap ng mga usapan...',
        'no_conversations' => 'Walang mga usapan pa. Magsimula ng bagong chat!',
        'type_message' => 'Mag-type ng mensahe...',
        'view_profile' => 'Tingnan ang Profile',
        'mark_all_read' => 'Markahan ang Lahat bilang Nabasa',
        'contact_information' => 'Impormasyon ng Kontak',
        'department' => 'Kagawaran',
        'loading' => 'Naglo-load...',
        'no_email' => 'Walang email na ibinigay'
    ]
];

$t = $translations[$language];

if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$query = "SELECT e.*, r.role_name 
          FROM employees e 
          JOIN employee_roles r ON e.role_id = r.id 
          WHERE e.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    session_destroy();
    header("Location: employee_login.php");
    exit();
}

$employee_name = $employee['first_name'] . ' ' . $employee['last_name'];
$employee_role = $employee['role_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'send_message':
                $conn->begin_transaction();
                try {
                    $receiver_id = intval($_POST['receiver_id']);
                    $message = sanitizeInput($_POST['message']);
                    
                    if (empty($message)) {
                        throw new Exception("Message cannot be empty");
                    }
                    
                    if ($receiver_id <= 0) {
                        throw new Exception("Please select a recipient");
                    }
                    
                    if ($receiver_id == $employee_id) {
                        throw new Exception("You cannot send a message to yourself");
                    }
                    
                    // Check if receiver exists
                    $check_receiver = $conn->prepare("SELECT id FROM employees WHERE id = ? AND is_active = 1");
                    $check_receiver->bind_param("i", $receiver_id);
                    $check_receiver->execute();
                    $check_receiver->store_result();
                    
                    if ($check_receiver->num_rows == 0) {
                        throw new Exception("Recipient not found or inactive");
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->bind_param("iis", $employee_id, $receiver_id, $message);
                    $stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    error_log("Message sending failed: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                exit();
                
            case 'mark_read':
                try {
                    if (isset($_POST['message_id'])) {
                        // Mark single message as read
                        $message_id = intval($_POST['message_id']);
                        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
                        $stmt->bind_param("ii", $message_id, $employee_id);
                        $stmt->execute();
                    } elseif (isset($_POST['sender_id'])) {
                        // Mark all messages from a specific sender as read
                        $sender_id = intval($_POST['sender_id']);
                        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
                        $stmt->bind_param("ii", $sender_id, $employee_id);
                        $stmt->execute();
                    }
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                exit();
                
            case 'get_conversation':
                $other_user_id = intval($_POST['other_user_id']);
                
                $mark_read_stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
                $mark_read_stmt->bind_param("ii", $other_user_id, $employee_id);
                $mark_read_stmt->execute();
                
                $conversation_query = "SELECT m.*, 
                                     CONCAT(sender.first_name, ' ', sender.last_name) as sender_name,
                                     CONCAT(receiver.first_name, ' ', receiver.last_name) as receiver_name,
                                     sender.profile_photo as sender_photo,
                                     receiver.profile_photo as receiver_photo
                                     FROM messages m 
                                     LEFT JOIN employees sender ON m.sender_id = sender.id 
                                     LEFT JOIN employees receiver ON m.receiver_id = receiver.id
                                     WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                                        OR (m.sender_id = ? AND m.receiver_id = ?)
                                     ORDER BY m.created_at ASC";
                $conv_stmt = $conn->prepare($conversation_query);
                $conv_stmt->bind_param("iiii", $employee_id, $other_user_id, $other_user_id, $employee_id);
                $conv_stmt->execute();
                $conv_result = $conv_stmt->get_result();
                $conversation = [];
                while ($row = $conv_result->fetch_assoc()) {
                    $conversation[] = $row;
                }
                echo json_encode(['success' => true, 'conversation' => $conversation]);
                exit();
                
            case 'get_user_profile':
                $user_id = intval($_POST['user_id']);
                $profile_query = "SELECT e.*, r.role_name 
                                FROM employees e 
                                JOIN employee_roles r ON e.role_id = r.id 
                                WHERE e.id = ?";
                $profile_stmt = $conn->prepare($profile_query);
                $profile_stmt->bind_param("i", $user_id);
                $profile_stmt->execute();
                $profile_result = $profile_stmt->get_result();
                $profile = $profile_result->fetch_assoc();
                echo json_encode(['success' => true, 'profile' => $profile]);
                exit();
        }
    }
}

function sanitizeInput($data) {
    global $conn;
    return $conn->real_escape_string(strip_tags(trim($data)));
}

// Get all employees for chat list
$employees_query = "SELECT id, first_name, last_name, role_id, profile_photo FROM employees WHERE is_active = 1 AND id != ? ORDER BY first_name, last_name";
$employees_stmt = $conn->prepare($employees_query);
$employees_stmt->bind_param("i", $employee_id);
$employees_stmt->execute();
$employees_result = $employees_stmt->get_result();
$employees = [];
while ($row = $employees_result->fetch_assoc()) {
    $employees[] = $row;
}

$conversations_query = "SELECT DISTINCT 
                       CASE 
                           WHEN m.sender_id = ? THEN m.receiver_id 
                           ELSE m.sender_id 
                       END as other_user_id,
                       CASE 
                           WHEN m.sender_id = ? THEN CONCAT(receiver.first_name, ' ', receiver.last_name)
                           ELSE CONCAT(sender.first_name, ' ', sender.last_name)
                       END as other_user_name,
                       CASE 
                           WHEN m.sender_id = ? THEN receiver.profile_photo
                           ELSE sender.profile_photo
                       END as other_user_photo,
                       CASE 
                           WHEN m.sender_id = ? THEN receiver_role.role_name
                           ELSE sender_role.role_name
                       END as other_user_role,
                       (SELECT message FROM messages m2 
                        WHERE (m2.sender_id = ? AND m2.receiver_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END)
                           OR (m2.receiver_id = ? AND m2.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END)
                        ORDER BY m2.created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages m2 
                        WHERE (m2.sender_id = ? AND m2.receiver_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END)
                           OR (m2.receiver_id = ? AND m2.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END)
                        ORDER BY m2.created_at DESC LIMIT 1) as last_message_time,
                       (SELECT COUNT(*) FROM messages m3 
                        WHERE m3.sender_id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END 
                          AND m3.receiver_id = ? AND m3.is_read = 0) as unread_count
                       FROM messages m
                       LEFT JOIN employees sender ON m.sender_id = sender.id
                       LEFT JOIN employees receiver ON m.receiver_id = receiver.id
                       LEFT JOIN employee_roles sender_role ON sender.role_id = sender_role.id
                       LEFT JOIN employee_roles receiver_role ON receiver.role_id = receiver_role.id
                       WHERE m.sender_id = ? OR m.receiver_id = ?
                       ORDER BY last_message_time DESC";

$conv_stmt = $conn->prepare($conversations_query);
$conv_stmt->bind_param("iiiiiiiiiiiiiiii", 
    $employee_id, $employee_id, $employee_id, $employee_id, 
    $employee_id, $employee_id, $employee_id, $employee_id,
    $employee_id, $employee_id, $employee_id, $employee_id,
    $employee_id, $employee_id, $employee_id, $employee_id
);
$conv_stmt->execute();
$conv_result = $conv_stmt->get_result();
$conversations = [];
while ($row = $conv_result->fetch_assoc()) {
    $conversations[] = $row;
}

// Count total unread messages
$unread_query = "SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND is_read = 0";
$unread_stmt = $conn->prepare($unread_query);
$unread_stmt->bind_param("i", $employee_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_count = $unread_result->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Messages</title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
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

    .sidebar {
        width: var(--sidebar-width);
        background: linear-gradient(180deg, var(--topbar-brown) 0%, #2A2520 100%);
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
        width: 100px;
        height: 100px;
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
        font-size: 20px;
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
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .language-btn:hover {
        background-color: rgba(255,255,255,0.2);
    }

    .language-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: var(--topbar-brown);
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 100;
        display: none;
        overflow: hidden;
    }

    body.dark-mode .language-dropdown {
        background-color: var(--dark-bg-secondary);
    }

    .language-dropdown.active {
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

    .flag-icon {
        width: 20px;
        height: 15px;
        border-radius: 2px;
    }

    /* Dark Mode Toggle */
    .dark-mode-toggle {
        position: relative;
        width: 60px;
        height: 30px;
        background-color: var(--topbar-brown);
        border-radius: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        outline: none;
        margin: 15px 20px;
    }

    body.dark-mode .dark-mode-toggle {
        background-color: var(--dark-bg-tertiary);
    }

    .dark-mode-toggle::before {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 24px;
        height: 24px;
        background-color: var(--panel-cream);
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    body.dark-mode .dark-mode-toggle::before {
        transform: translateX(30px);
        background-color: var(--dark-text-primary);
    }

    .dark-mode-toggle i {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: var(--sales-orange);
    }

    .dark-mode-toggle .sun {
        left: 8px;
        opacity: 0;
    }

    .dark-mode-toggle .moon {
        right: 8px;
        opacity: 1;
    }

    body.dark-mode .dark-mode-toggle .sun {
        opacity: 1;
    }

    body.dark-mode .dark-mode-toggle .moon {
        opacity: 0;
    }

    .main-content {
        flex: 1;
        display: flex;
        background-color: white;
        min-height: 100vh;
        transition: all 0.3s ease;
    }

    body.dark-mode .main-content {
        background-color: var(--dark-bg-secondary);
    }

    /* New chat interface layout */
    .chat-sidebar {
        width: 350px;
        background-color: var(--panel-cream);
        border-right: 1px solid rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    body.dark-mode .chat-sidebar {
        background-color: var(--dark-bg-tertiary);
        border-right-color: var(--dark-border);
    }

    .chat-header {
        padding: 20px;
        background: linear-gradient(135deg, var(--topbar-brown) 0%, #2A2520 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    body.dark-mode .chat-header {
        background: linear-gradient(135deg, var(--dark-bg-secondary) 0%, var(--dark-bg-primary) 100%);
    }

    .chat-title {
        font-size: 18px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .new-chat-btn {
        width: 35px;
        height: 35px;
        background-color: var(--sales-orange);
        border: none;
        border-radius: 50%;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .new-chat-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(217, 122, 65, 0.3);
    }

    .chat-search {
        padding: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    body.dark-mode .chat-search {
        border-bottom-color: var(--dark-border);
    }

    .search-input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 25px;
        font-size: 14px;
        background-color: white;
        transition: all 0.3s ease;
        color: var(--text-dark);
    }

    body.dark-mode .search-input {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    .conversations-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px 0;
    }

    .conversation-item {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        position: relative;
    }

    body.dark-mode .conversation-item {
        border-bottom-color: var(--dark-border);
    }

    .conversation-item:hover {
        background-color: rgba(106, 127, 70, 0.1);
    }

    body.dark-mode .conversation-item:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .conversation-item.active {
        background-color: rgba(106, 127, 70, 0.15);
        border-left: 4px solid var(--icon-green);
    }

    body.dark-mode .conversation-item.active {
        background-color: rgba(106, 127, 70, 0.25);
    }

    .conversation-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--icon-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        margin-right: 15px;
        position: relative;
    }

    .conversation-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .conversation-info {
        flex: 1;
        min-width: 0;
    }

    .conversation-name {
        font-weight: 600;
        font-size: 15px;
        color: var(--text-dark);
        margin-bottom: 3px;
        transition: color 0.3s ease;
    }

    body.dark-mode .conversation-name {
        color: var(--dark-text-primary);
    }

    .conversation-last-message {
        font-size: 13px;
        color: #666;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color 0.3s ease;
    }

    body.dark-mode .conversation-last-message {
        color: var(--dark-text-secondary);
    }

    .conversation-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
    }

    .conversation-time {
        font-size: 11px;
        color: #999;
        transition: color 0.3s ease;
    }

    body.dark-mode .conversation-time {
        color: var(--dark-text-secondary);
    }

    .unread-badge {
        background-color: var(--sales-orange);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }

    body.dark-mode .chat-main {
        background-color: var(--dark-bg-secondary);
    }

    .chat-conversation-header {
        padding: 20px;
        background-color: white;
        border-bottom: 1px solid rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s ease;
    }

    body.dark-mode .chat-conversation-header {
        background-color: var(--dark-bg-tertiary);
        border-bottom-color: var(--dark-border);
    }

    .conversation-user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .conversation-user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: var(--icon-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }

    .conversation-user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .conversation-user-details h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 2px;
        transition: color 0.3s ease;
    }

    body.dark-mode .conversation-user-details h3 {
        color: var(--dark-text-primary);
    }

    .conversation-user-details p {
        font-size: 12px;
        color: #666;
        transition: color 0.3s ease;
    }

    body.dark-mode .conversation-user-details p {
        color: var(--dark-text-secondary);
    }

    .chat-actions {
        display: flex;
        gap: 10px;
    }

    .chat-action-btn {
        width: 35px;
        height: 35px;
        border: none;
        border-radius: 50%;
        background-color: var(--panel-cream);
        color: var(--text-dark);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    body.dark-mode .chat-action-btn {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
    }

    .chat-action-btn:hover {
        background-color: var(--icon-green);
        color: white;
        transform: scale(1.1);
    }

    /* Enhanced messages container for better scrolling */
    .messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        max-height: calc(100vh - 200px);
    }

    .messages-container::-webkit-scrollbar {
        width: 6px;
    }

    .messages-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    body.dark-mode .messages-container::-webkit-scrollbar-track {
        background: var(--dark-bg-tertiary);
    }

    .messages-container::-webkit-scrollbar-thumb {
        background: var(--icon-green);
        border-radius: 10px;
    }

    .messages-container::-webkit-scrollbar-thumb:hover {
        background: var(--stock-green);
    }

    .message {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        max-width: 70%;
    }

    .message.sent {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: var(--icon-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        font-weight: bold;
        flex-shrink: 0;
    }

    .message-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .message-content {
        background-color: white;
        padding: 12px 16px;
        border-radius: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: relative;
        word-wrap: break-word;
        max-width: 100%;
        transition: all 0.3s ease;
    }

    body.dark-mode .message-content {
        background-color: var(--dark-bg-tertiary);
        box-shadow: 0 2px 8px var(--dark-shadow);
    }

    .message.sent .message-content {
        background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
        color: white;
    }

    .message-text {
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 5px;
        white-space: pre-wrap;
    }

    .message-time {
        font-size: 11px;
        opacity: 0.7;
    }

    .message-input-container {
        padding: 20px;
        background-color: white;
        border-top: 1px solid rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    body.dark-mode .message-input-container {
        background-color: var(--dark-bg-tertiary);
        border-top-color: var(--dark-border);
    }

    .message-input-form {
        display: flex;
        align-items: center;
        gap: 15px;
        background-color: #f8f9fa;
        border-radius: 25px;
        padding: 10px 20px;
        transition: all 0.3s ease;
    }

    body.dark-mode .message-input-form {
        background-color: var(--dark-bg-secondary);
    }

    .message-input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 14px;
        padding: 8px 0;
        outline: none;
        resize: none;
        max-height: 100px;
        min-height: 20px;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .message-input {
        color: var(--dark-text-primary);
    }

    .send-btn {
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .send-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(106, 127, 70, 0.3);
    }

    .send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .empty-chat {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #666;
        text-align: center;
        padding: 40px;
        transition: color 0.3s ease;
    }

    body.dark-mode .empty-chat {
        color: var(--dark-text-secondary);
    }

    .empty-chat i {
        font-size: 60px;
        color: var(--icon-green);
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-chat h3 {
        font-size: 18px;
        margin-bottom: 10px;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .empty-chat h3 {
        color: var(--dark-text-primary);
    }

    .empty-chat p {
        font-size: 14px;
        max-width: 300px;
    }

    /* Profile sidebar */
    .profile-sidebar {
        width: 300px;
        background-color: white;
        border-left: 1px solid rgba(0,0,0,0.1);
        display: none;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    body.dark-mode .profile-sidebar {
        background-color: var(--dark-bg-tertiary);
        border-left-color: var(--dark-border);
    }

    .profile-sidebar.active {
        display: flex;
    }

    .profile-header {
        padding: 30px 20px;
        text-align: center;
        background: linear-gradient(135deg, var(--panel-cream) 0%, #E8DFC8 100%);
        border-bottom: 1px solid rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    body.dark-mode .profile-header {
        background: linear-gradient(135deg, var(--dark-bg-secondary) 0%, var(--dark-bg-primary) 100%);
        border-bottom-color: var(--dark-border);
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: var(--icon-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: bold;
        margin: 0 auto 15px;
        border: 3px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .profile-name {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }

    body.dark-mode .profile-name {
        color: var(--dark-text-primary);
    }

    .profile-role {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: color 0.3s ease;
    }

    body.dark-mode .profile-role {
        color: var(--dark-text-secondary);
    }

    .profile-details {
        padding: 20px;
        flex: 1;
    }

    .profile-section {
        margin-bottom: 25px;
    }

    .profile-section h4 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: color 0.3s ease;
    }

    body.dark-mode .profile-section h4 {
        color: var(--dark-text-primary);
    }

    .profile-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        font-size: 14px;
        color: #666;
        transition: all 0.3s ease;
    }

    body.dark-mode .profile-info {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-secondary);
    }

    .mobile-menu-toggle {
        display: none;
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--sales-orange) 0%, #c46a38 100%);
        color: white;
        border-radius: 12px;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 100;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(217, 122, 65, 0.3);
        transition: all 0.3s ease;
    }

    .mobile-menu-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(217, 122, 65, 0.4);
    }

    /* New chat modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        backdrop-filter: blur(5px);
    }

    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        border-radius: 15px;
        padding: 30px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-tertiary);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .modal-header {
        border-bottom-color: var(--dark-border);
    }

    .modal-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .modal-title {
        color: var(--dark-text-primary);
    }

    .close-btn {
        width: 30px;
        height: 30px;
        border: none;
        background: none;
        font-size: 18px;
        color: #666;
        cursor: pointer;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    body.dark-mode .close-btn {
        color: var(--dark-text-secondary);
    }

    .close-btn:hover {
        background-color: #f0f0f0;
        color: var(--text-dark);
    }

    body.dark-mode .close-btn:hover {
        background-color: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
    }

    .employee-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .employee-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    body.dark-mode .employee-item {
        border-color: var(--dark-border);
    }

    .employee-item:hover {
        background-color: rgba(106, 127, 70, 0.1);
        border-color: var(--icon-green);
    }

    body.dark-mode .employee-item:hover {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--icon-green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        margin-right: 15px;
    }

    .employee-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .employee-info h4 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 2px;
        transition: color 0.3s ease;
    }

    body.dark-mode .employee-info h4 {
        color: var(--dark-text-primary);
    }

    .employee-info p {
        font-size: 12px;
        color: #666;
        transition: color 0.3s ease;
    }

    body.dark-mode .employee-info p {
        color: var(--dark-text-secondary);
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
        }
        
        .mobile-menu-toggle {
            display: flex;
        }

        .chat-sidebar {
            width: 100%;
        }

        .profile-sidebar {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            z-index: 50;
        }
    }

    @media (max-width: 768px) {
        .chat-sidebar {
            width: 100%;
        }

        .chat-main {
            display: none;
        }

        .chat-main.active {
            display: flex;
        }

        .chat-sidebar.hidden {
            display: none;
        }
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
                <?php if (!empty($employee['profile_photo']) && file_exists($employee['profile_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($employee['profile_photo']); ?>" alt="Profile">
                <?php else: ?>
                    <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($employee_name); ?></div>
            <div class="user-status">
                <span class="status-indicator"></span>
                <span><?php echo htmlspecialchars($employee_role); ?></span>
            </div>
        </div>
        
        <!-- Language Switcher -->
        <div class="language-switcher">
            <button class="language-btn">
                <span><?php echo $language === 'en' ? 'English' : 'Filipino'; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="language-dropdown">
                <a href="?lang=en" class="language-option <?php echo $language === 'en' ? 'active' : ''; ?>">
                    <img src="img/us.png" alt="English" class="flag-icon">
                    <span>English</span>
                </a>
                <a href="?lang=tl" class="language-option <?php echo $language === 'tl' ? 'active' : ''; ?>">
                    <img src="img/ph.png" alt="Filipino" class="flag-icon">
                    <span>Filipino</span>
                </a>
            </div>
        </div>
        
      
        
        <ul class="nav-menu">
            <li><a href="Index.php"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="transaction_logging.php"><i class="fas fa-cash-register"></i> <?php echo $t['transaction_logging']; ?></a></li>
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <?php echo $t['attendance']; ?></a></li>
            <li><a href="inventory_view.php"><i class="fas fa-boxes"></i> <?php echo $t['inventory_view']; ?></a></li>
            <li><a href="sales_reports.php"><i class="fas fa-chart-pie"></i> <?php echo $t['sales_reports']; ?></a></li>
            <li><a href="customer_management.php"><i class="fas fa-users"></i> <?php echo $t['customer_management']; ?></a></li>
            <li><a href="loyalty_points.php"><i class="fas fa-award"></i> <?php echo $t['loyalty_points']; ?></a></li>
            <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> <?php echo $t['messages']; ?></a></li>
            <li><a href="employee_profile.php"><i class="fas fa-user-cog"></i> <?php echo $t['profile']; ?></a></li>
        </ul>
        
        <div class="sidebar-footer">
            <button class="logout-btn" onclick="window.location.href='employee_logout.php'">
                <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
            </button>
        </div>
    </div>
    
    <div class="main-content">
       
        <div class="chat-sidebar" id="chatSidebar">
            <div class="chat-header">
                <div class="chat-title">
                    <i class="fas fa-comments"></i>
                    <?php echo $t['messages']; ?>
                    <?php if ($unread_count > 0): ?>
                        <span class="unread-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </div>
           
                <button class="new-chat-btn" id="newChatBtn">
                    <i class="fas fa-plus"></i>
                </button>
                     
            </div>
              <!-- Dark Mode Toggle -->
        <button class="dark-mode-toggle">
            <i class="fas fa-sun sun"></i>
            <i class="fas fa-moon moon"></i>
        </button>
            <div class="chat-search">
                <input type="text" class="search-input" placeholder="<?php echo $t['search_conversations']; ?>" id="searchInput">
            </div>
            
            <div class="conversations-list" id="conversationsList">
                <?php if (!empty($conversations)): ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item" data-user-id="<?php echo $conv['other_user_id']; ?>">
                            <div class="conversation-avatar">
                                <?php if (!empty($conv['other_user_photo']) && file_exists($conv['other_user_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($conv['other_user_photo']); ?>" alt="Profile">
                                <?php else: ?>
                                    <?php 
                                    $names = explode(' ', $conv['other_user_name']);
                                    echo strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
                                    ?>
                                <?php endif; ?>
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-name"><?php echo htmlspecialchars($conv['other_user_name']); ?></div>
                                <div class="conversation-last-message"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)) . (strlen($conv['last_message']) > 50 ? '...' : ''); ?></div>
                            </div>
                            <div class="conversation-meta">
                                <div class="conversation-time">
                                    <?php 
                                    date_default_timezone_set('Asia/Manila');
                                    $time = strtotime($conv['last_message_time']);
                                    if (date('Y-m-d') == date('Y-m-d', $time)) {
                                        echo date('h:i A', $time);
                                    } else {
                                        echo date('M j', $time);
                                    }
                                    ?>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <div class="unread-badge"><?php echo $conv['unread_count']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="padding: 40px 20px;">
                        <i class="fas fa-comments"></i>
                        <p><?php echo $t['no_conversations']; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        
        <div class="chat-main" id="chatMain">
            <div class="empty-chat" id="emptyChat">
                <i class="fas fa-comments"></i>
                <h3><?php echo $t['welcome_messages']; ?></h3>
                <p><?php echo $t['select_conversation']; ?></p>
            </div>
            
            <div class="chat-conversation-header" id="conversationHeader" style="display: none;">
                <div class="conversation-user-info">
                    <div class="conversation-user-avatar" id="conversationAvatar"></div>
                    <div class="conversation-user-details">
                        <h3 id="conversationUserName"></h3>
                        <p id="conversationUserRole"></p>
                    </div>
                </div>
                <div class="chat-actions">
                    
                    <button class="chat-action-btn" id="profileBtn" title="<?php echo $t['view_profile']; ?>">
                        <i class="fas fa-user"></i>
                    </button>
                    <button class="chat-action-btn" id="markAllReadBtn" title="<?php echo $t['mark_all_read']; ?>">
                        <i class="fas fa-check-double"></i>
                    </button>
                </div>
            </div>
            
            <div class="messages-container" id="messagesContainer" style="display: none;"></div>
            
            <div class="message-input-container" id="messageInputContainer" style="display: none;">
                <form class="message-input-form" id="messageForm">
                    <textarea class="message-input" id="messageInput" placeholder="<?php echo $t['type_message']; ?>" rows="1"></textarea>
                    <button type="submit" class="send-btn" id="sendBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
        
         
        <div class="profile-sidebar" id="profileSidebar">
            <div class="profile-header">
                <div class="profile-avatar" id="profileAvatar"></div>
                <div class="profile-name" id="profileName"></div>
                <div class="profile-role" id="profileRole"></div>
            </div>
            <div class="profile-details">
                <div class="profile-section">
                    <h4><?php echo $t['contact_information']; ?></h4>
                    <div class="profile-info" id="profileContact">
                        <?php echo $t['loading']; ?>...
                    </div>
                </div>
                <div class="profile-section">
                    <h4><?php echo $t['department']; ?></h4>
                    <div class="profile-info" id="profileDepartment">
                        <?php echo $t['loading']; ?>...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
  
    <div class="modal" id="newChatModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $t['start_new_conversation']; ?></h3>
                <button class="close-btn" id="closeModalBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="employee-list" id="employeeList">
                <?php foreach ($employees as $emp): ?>
                    <div class="employee-item" data-user-id="<?php echo $emp['id']; ?>">
                        <div class="employee-avatar">
                            <?php if (!empty($emp['profile_photo']) && file_exists($emp['profile_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($emp['profile_photo']); ?>" alt="Profile">
                            <?php else: ?>
                                <?php echo strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="employee-info">
                            <h4><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></h4>
                            <p>Employee</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script>
        let currentConversationUserId = null;
        let messageRefreshInterval = null;
        let conversationData = {}; // Store conversation data to maintain state
        
        document.addEventListener('DOMContentLoaded', function() {
            initializeChat();
            setupEventListeners();
            initializeDarkMode();
            initializeLanguageSwitcher();
        });
        
        function initializeChat() {
            // Mobile menu toggle
            document.getElementById('mobileMenuToggle').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
            });
            
            // Auto-resize message input
            const messageInput = document.getElementById('messageInput');
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });
        }
        
        function initializeDarkMode() {
            const darkModeToggle = document.querySelector('.dark-mode-toggle');
            const body = document.body;

            // Check for saved dark mode preference
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            if (isDarkMode) {
                body.classList.add('dark-mode');
            }

            darkModeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
            });
        }
        
        function initializeLanguageSwitcher() {
            const languageBtn = document.querySelector('.language-btn');
            const languageDropdown = document.querySelector('.language-dropdown');

            languageBtn.addEventListener('click', () => {
                languageDropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!languageBtn.contains(e.target) && !languageDropdown.contains(e.target)) {
                    languageDropdown.classList.remove('active');
                }
            });
        }
        
        function setupEventListeners() {
            // Conversation item clicks
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    openConversation(userId);
                });
            });
            
            // New chat button
            document.getElementById('newChatBtn').addEventListener('click', function() {
                document.getElementById('newChatModal').classList.add('active');
            });
            
            // Close modal
            document.getElementById('closeModalBtn').addEventListener('click', function() {
                document.getElementById('newChatModal').classList.remove('active');
            });
            
            // Employee list items
            document.querySelectorAll('.employee-item').forEach(item => {
                item.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    document.getElementById('newChatModal').classList.remove('active');
                    openConversation(userId);
                });
            });
            
            // Message form submission
            document.getElementById('messageForm').addEventListener('submit', function(e) {
                e.preventDefault();
                sendMessage();
            });
            
            // Profile button
            document.getElementById('profileBtn').addEventListener('click', function() {
                toggleProfileSidebar();
            });
            
            document.getElementById('markAllReadBtn').addEventListener('click', function() {
                if (currentConversationUserId) {
                    markAllMessagesAsRead(currentConversationUserId);
                }
            });
            
            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function() {
                filterConversations(this.value);
            });
            
            // Enter key to send message
            document.getElementById('messageInput').addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }
        
        function openConversation(userId) {
            // Update active conversation
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            const conversationItem = document.querySelector(`[data-user-id="${userId}"]`);
            if (conversationItem) {
                conversationItem.classList.add('active');
                const unreadBadge = conversationItem.querySelector('.unread-badge');
                if (unreadBadge) {
                    unreadBadge.remove();
                }
            }
            
            currentConversationUserId = userId;
            
            // Show chat interface
            document.getElementById('emptyChat').style.display = 'none';
            document.getElementById('conversationHeader').style.display = 'flex';
            document.getElementById('messagesContainer').style.display = 'flex';
            document.getElementById('messageInputContainer').style.display = 'block';
            
            // Load conversation
            loadConversation(userId);
            loadUserProfile(userId);
            
            // Start auto-refresh
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
            messageRefreshInterval = setInterval(() => {
                loadConversation(userId, false);
            }, 3000);
            
            // Mobile: hide sidebar, show chat
            if (window.innerWidth <= 768) {
                document.getElementById('chatSidebar').classList.add('hidden');
                document.getElementById('chatMain').classList.add('active');
            }
        }
        
        function loadConversation(userId, scrollToBottom = true) {
            fetch('messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_conversation&other_user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    conversationData[userId] = data.conversation;
                    displayMessages(data.conversation);
                    if (scrollToBottom) {
                        setTimeout(() => {
                            scrollMessagesToBottom();
                        }, 100);
                    }
                }
            })
            .catch(error => {
                console.error('Error loading conversation:', error);
            });
        }
        
        function loadUserProfile(userId) {
            fetch('messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_user_profile&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateConversationHeader(data.profile);
                    updateProfileSidebar(data.profile);
                }
            })
            .catch(error => {
                console.error('Error loading user profile:', error);
            });
        }
        
        function markAllMessagesAsRead(userId) {
            fetch('messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_read&sender_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI to reflect read status
                    const conversationItem = document.querySelector(`[data-user-id="${userId}"]`);
                    if (conversationItem) {
                        const unreadBadge = conversationItem.querySelector('.unread-badge');
                        if (unreadBadge) {
                            unreadBadge.remove();
                        }
                    }
                    // Update total unread count
                    updateUnreadCount();
                }
            })
            .catch(error => {
                console.error('Error marking messages as read:', error);
            });
        }
        
        function updateUnreadCount() {
            // Recalculate total unread messages
            const unreadBadges = document.querySelectorAll('.conversation-item .unread-badge');
            let totalUnread = 0;
            unreadBadges.forEach(badge => {
                totalUnread += parseInt(badge.textContent) || 0;
            });
            
            // Update header unread count
            const headerUnreadBadge = document.querySelector('.chat-title .unread-badge');
            if (totalUnread > 0) {
                if (headerUnreadBadge) {
                    headerUnreadBadge.textContent = totalUnread;
                } else {
                    const chatTitle = document.querySelector('.chat-title');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'unread-badge';
                    newBadge.textContent = totalUnread;
                    chatTitle.appendChild(newBadge);
                }
            } else {
                if (headerUnreadBadge) {
                    headerUnreadBadge.remove();
                }
            }
        }
        
        function updateConversationHeader(profile) {
            const avatar = document.getElementById('conversationAvatar');
            const name = document.getElementById('conversationUserName');
            const role = document.getElementById('conversationUserRole');
            
            if (profile.profile_photo && profile.profile_photo !== '') {
                avatar.innerHTML = `<img src="${profile.profile_photo}" alt="${profile.first_name} ${profile.last_name} Profile">`;
            } else {
                const initials = profile.first_name.charAt(0) + profile.last_name.charAt(0);
                avatar.innerHTML = initials.toUpperCase();
            }
            
            name.textContent = `${profile.first_name} ${profile.last_name}`;
            role.textContent = profile.role_name;
        }
        
        function updateProfileSidebar(profile) {
            const avatar = document.getElementById('profileAvatar');
            const name = document.getElementById('profileName');
            const role = document.getElementById('profileRole');
            const contact = document.getElementById('profileContact');
            const department = document.getElementById('profileDepartment');
            
            if (profile.profile_photo && profile.profile_photo !== '') {
                avatar.innerHTML = `<img src="${profile.profile_photo}" alt="${profile.first_name} ${profile.last_name} Profile">`;
            } else {
                const initials = profile.first_name.charAt(0) + profile.last_name.charAt(0);
                avatar.innerHTML = initials.toUpperCase();
            }
            
            name.textContent = `${profile.first_name} ${profile.last_name}`;
            role.textContent = profile.role_name;
            contact.textContent = profile.email || '<?php echo $t['no_email']; ?>';
            department.textContent = profile.role_name;
        }
        
        function displayMessages(messages) {
            const container = document.getElementById('messagesContainer');
            const currentUserId = <?php echo $employee_id; ?>;
            
            container.innerHTML = '';
            
            messages.forEach((message, index) => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${message.sender_id == currentUserId ? 'sent' : 'received'}`;
                
                const avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                
                if (message.sender_id == currentUserId) {
                    // Current user's message - show current user's photo
                    const userPhoto = '<?php echo !empty($employee['profile_photo']) && file_exists($employee['profile_photo']) ? htmlspecialchars($employee['profile_photo']) : ''; ?>';
                    if (userPhoto) {
                        avatar.innerHTML = `<img src="${userPhoto}" alt="Your Profile">`;
                    } else {
                        avatar.innerHTML = '<?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>';
                    }
                } else {
                    // Other user's message - show their profile photo
                    if (message.sender_photo && message.sender_photo !== '' && message.sender_photo !== null) {
                        avatar.innerHTML = `<img src="${message.sender_photo}" alt="${message.sender_name} Profile">`;
                    } else {
                        // Fallback to initials if no photo
                        const names = message.sender_name.split(' ');
                        const initials = names[0].charAt(0) + (names[1] ? names[1].charAt(0) : '');
                        avatar.innerHTML = initials.toUpperCase();
                    }
                }
                
                const content = document.createElement('div');
                content.className = 'message-content';
                
                const text = document.createElement('div');
                text.className = 'message-text';
                text.textContent = message.message;
                
                const time = document.createElement('div');
                time.className = 'message-time';
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
        
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message || !currentConversationUserId) {
                return;
            }
            
            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;
            
            fetch('messages.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&receiver_id=${currentConversationUserId}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    // Reload the current conversation
                    loadConversation(currentConversationUserId);
                    updateConversationList();
                } else {
                    alert('Error sending message: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Error sending message. Please try again.');
            })
            .finally(() => {
                sendBtn.disabled = false;
            });
        }
        
        function updateConversationList() {
            // Update the last message in the conversation list
            const conversationItem = document.querySelector(`[data-user-id="${currentConversationUserId}"]`);
            if (conversationItem) {
                const lastMessageElement = conversationItem.querySelector('.conversation-last-message');
                const messageInput = document.getElementById('messageInput');
                if (lastMessageElement && messageInput.value.trim()) {
                    const message = messageInput.value.trim();
                    lastMessageElement.textContent = message.length > 50 ? message.substring(0, 50) + '...' : message;
                }
                
                // Update time
                const timeElement = conversationItem.querySelector('.conversation-time');
                if (timeElement) {
                    const now = new Date();
                    timeElement.textContent = now.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                }
                
                // Move conversation to top
                const conversationsList = document.getElementById('conversationsList');
                conversationsList.insertBefore(conversationItem, conversationsList.firstChild);
            }
        }
        
        function toggleProfileSidebar() {
            const profileSidebar = document.getElementById('profileSidebar');
            profileSidebar.classList.toggle('active');
        }
        
        function filterConversations(searchTerm) {
            const conversations = document.querySelectorAll('.conversation-item');
            
            conversations.forEach(conv => {
                const name = conv.querySelector('.conversation-name').textContent.toLowerCase();
                const message = conv.querySelector('.conversation-last-message').textContent.toLowerCase();
                
                if (name.includes(searchTerm.toLowerCase()) || message.includes(searchTerm.toLowerCase())) {
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
        
        // Close modal when clicking outside
        document.getElementById('newChatModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
        
        // Mobile back button functionality
        if (window.innerWidth <= 768) {
            const backBtn = document.createElement('button');
            backBtn.innerHTML = '<i class="fas fa-arrow-left"></i>';
            backBtn.className = 'chat-action-btn';
            backBtn.style.marginRight = '10px';
            backBtn.addEventListener('click', function() {
                document.getElementById('chatSidebar').classList.remove('hidden');
                document.getElementById('chatMain').classList.remove('active');
            });
            
            const chatActions = document.querySelector('.chat-actions');
            chatActions.insertBefore(backBtn, chatActions.firstChild);
        }
    </script>
</body>
</html>