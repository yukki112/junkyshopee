<?php
// Admin Settings Handler
// Handles all admin tool operations

session_start();
require_once 'db_connection.php';

// Check if user is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = "SELECT is_admin FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['is_admin']) {
    session_destroy();
    header("Location: ../Customer-portal/Login/Login.php");
    exit();
}

// Handle different admin operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // System Configuration Update
        if (isset($_POST['action']) && $_POST['action'] === 'update_system_config') {
            $app_name = $_POST['app_name'] ?? 'JunkValue';
            $maintenance_mode = $_POST['maintenance_mode'] ?? '0';
            $debug_mode = $_POST['debug_mode'] ?? '0';
            $timezone = $_POST['timezone'] ?? 'Asia/Manila';
            
            // Update each setting
            $settings = [
                'app_name' => $app_name,
                'maintenance_mode' => $maintenance_mode,
                'debug_mode' => $debug_mode,
                'timezone' => $timezone
            ];
            
            foreach ($settings as $key => $value) {
                $update_query = "UPDATE system_settings SET setting_value = ?, updated_at = NOW(), updated_by = ? WHERE setting_key = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->execute([$value, $user_id, $key]);
            }
            
            $response['success'] = true;
            $response['message'] = 'System configuration updated successfully!';
        }
        
        // Database Backup
        elseif (isset($_POST['action']) && $_POST['action'] === 'backup_database') {
            // Get database credentials
            $db_host = 'localhost';
            $db_name = 'junkshop';
            $db_user = 'root';
            $db_pass = '';
            
            // Create backup filename
            $backup_file = 'backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Create backups directory if it doesn't exist
            if (!is_dir('backups')) {
                mkdir('backups', 0755, true);
            }
            
            // Execute mysqldump command
            $command = "mysqldump --host={$db_host} --user={$db_user} --password={$db_pass} {$db_name} > {$backup_file}";
            exec($command, $output, $return_var);
            
            if ($return_var === 0 && file_exists($backup_file)) {
                $response['success'] = true;
                $response['message'] = 'Database backup created successfully!';
                $response['file'] = $backup_file;
            } else {
                $response['message'] = 'Failed to create database backup.';
            }
        }
        
        // Database Optimization
        elseif (isset($_POST['action']) && $_POST['action'] === 'optimize_database') {
            // Get all tables
            $tables_query = "SHOW TABLES";
            $tables_stmt = $conn->query($tables_query);
            $tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Optimize each table
            foreach ($tables as $table) {
                $optimize_query = "OPTIMIZE TABLE `{$table}`";
                $conn->exec($optimize_query);
            }
            
            $response['success'] = true;
            $response['message'] = 'Database optimized successfully!';
        }
        
        // Update Notification Settings
        elseif (isset($_POST['action']) && $_POST['action'] === 'update_notifications') {
            $email_notifications = json_encode([
                'system_alerts' => isset($_POST['email_system_alerts']),
                'inventory_warnings' => isset($_POST['email_inventory_warnings']),
                'user_activities' => isset($_POST['email_user_activities']),
                'backup_reminders' => isset($_POST['email_backup_reminders'])
            ]);
            
            $app_notifications = json_encode([
                'new_transactions' => isset($_POST['app_new_transactions']),
                'system_updates' => isset($_POST['app_system_updates']),
                'scheduled_tasks' => isset($_POST['app_scheduled_tasks'])
            ]);
            
            // Check if notification settings exist
            $check_query = "SELECT id FROM system_settings WHERE setting_key = 'email_notifications'";
            $check_stmt = $conn->query($check_query);
            
            if ($check_stmt->rowCount() > 0) {
                // Update existing
                $update_query = "UPDATE system_settings SET setting_value = ?, updated_at = NOW(), updated_by = ? WHERE setting_key = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->execute([$email_notifications, $user_id, 'email_notifications']);
                $update_stmt->execute([$app_notifications, $user_id, 'app_notifications']);
            } else {
                // Insert new
                $insert_query = "INSERT INTO system_settings (setting_key, setting_value, setting_type, description, updated_by) VALUES (?, ?, 'json', ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->execute(['email_notifications', $email_notifications, 'Email notification preferences', $user_id]);
                $insert_stmt->execute(['app_notifications', $app_notifications, 'In-app notification preferences', $user_id]);
            }
            
            $response['success'] = true;
            $response['message'] = 'Notification settings updated successfully!';
        }
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    
    // Return JSON response for AJAX requests
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        // Redirect back with message
        $_SESSION['admin_message'] = $response['message'];
        $_SESSION['admin_message_type'] = $response['success'] ? 'success' : 'error';
        header("Location: profile.php");
        exit();
    }
}

// If GET request, redirect to profile
header("Location: profile.php");
exit();
?>
