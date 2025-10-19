<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    if (isset($_POST['ajax'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    } else {
        header('Location: login.php');
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user is admin
$admin_check = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$admin_check->execute([$user_id]);
$user = $admin_check->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['is_admin']) {
    if (isset($_POST['ajax'])) {
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
    } else {
        header('Location: index.php');
    }
    exit();
}

if (isset($_GET['download_backup']) && !empty($_GET['file'])) {
    $backup_file = $_GET['file'];
    
    // Security: Validate file path to prevent directory traversal
    $backup_file = basename($backup_file);
    $full_path = 'backups/' . $backup_file;
    
    if (file_exists($full_path) && strpos($backup_file, 'backup_') === 0 && pathinfo($backup_file, PATHINFO_EXTENSION) === 'sql') {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup_file . '"');
        header('Content-Length: ' . filesize($full_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        
        readfile($full_path);
        exit();
    } else {
        $_SESSION['error_message'] = 'Backup file not found or invalid';
        header('Location: profile.php');
        exit();
    }
}

// Handle AJAX requests
if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'update_system_config':
                $app_name = $_POST['app_name'] ?? 'JunkValue';
                $maintenance_mode = $_POST['maintenance_mode'] ?? '0';
                $debug_mode = $_POST['debug_mode'] ?? '0';
                $timezone = $_POST['timezone'] ?? 'Asia/Manila';
                
                // Update or insert settings
                $settings = [
                    'app_name' => $app_name,
                    'maintenance_mode' => $maintenance_mode,
                    'debug_mode' => $debug_mode,
                    'timezone' => $timezone
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt = $conn->prepare("
                        INSERT INTO system_settings (setting_key, setting_value, updated_by, updated_at) 
                        VALUES (?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value),
                        updated_by = VALUES(updated_by),
                        updated_at = NOW()
                    ");
                    $stmt->execute([$key, $value, $user_id]);
                }
                
                echo json_encode(['success' => true, 'message' => 'System configuration updated successfully']);
                break;
                
            case 'backup_database':
                $backup_filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                $backup_file = 'backups/' . $backup_filename;
                
                // Create backups directory if it doesn't exist
                if (!file_exists('backups')) {
                    if (!mkdir('backups', 0755, true)) {
                        throw new Exception('Failed to create backups directory');
                    }
                }
                
                // Check if directory is writable
                if (!is_writable('backups')) {
                    throw new Exception('Backups directory is not writable');
                }
                
                // Get database credentials from config
                $db_host = DB_HOST;
                $db_name = DB_NAME;
                $db_user = DB_USER;
                $db_pass = DB_PASS;
                
                // PHP-based backup (more reliable than mysqldump)
                $tables = [];
                $result = $conn->query("SHOW TABLES");
                
                if (!$result) {
                    throw new Exception('Failed to retrieve database tables');
                }
                
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $tables[] = $row[0];
                }
                
                if (empty($tables)) {
                    throw new Exception('No tables found in database');
                }
                
                $sql_dump = "-- Database Backup\n";
                $sql_dump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
                $sql_dump .= "-- Database: {$db_name}\n\n";
                $sql_dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
                $sql_dump .= "START TRANSACTION;\n";
                $sql_dump .= "SET time_zone = \"+00:00\";\n\n";
                
                foreach ($tables as $table) {
                    $sql_dump .= "-- --------------------------------------------------------\n";
                    $sql_dump .= "-- Table structure for table `{$table}`\n";
                    $sql_dump .= "-- --------------------------------------------------------\n\n";
                    $sql_dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    
                    // Get CREATE TABLE statement
                    $create_table = $conn->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
                    if (!$create_table) {
                        throw new Exception("Failed to get CREATE TABLE statement for {$table}");
                    }
                    $sql_dump .= $create_table[1] . ";\n\n";
                    
                    // Get table data
                    $rows = $conn->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($rows)) {
                        $sql_dump .= "-- Dumping data for table `{$table}`\n\n";
                        
                        foreach ($rows as $row) {
                            $values = array_map(function($value) use ($conn) {
                                return $value === null ? 'NULL' : $conn->quote($value);
                            }, array_values($row));
                            
                            $columns = '`' . implode('`, `', array_keys($row)) . '`';
                            $sql_dump .= "INSERT INTO `{$table}` ({$columns}) VALUES (" . implode(', ', $values) . ");\n";
                        }
                        $sql_dump .= "\n";
                    }
                }
                
                $sql_dump .= "COMMIT;\n";
                
                // Write backup file
                $bytes_written = file_put_contents($backup_file, $sql_dump);
                
                if ($bytes_written === false) {
                    throw new Exception('Failed to write backup file');
                }
                
                if (!file_exists($backup_file)) {
                    throw new Exception('Backup file was not created');
                }
                
                // Generate download URL
                $download_url = 'admin_settings_handler.php?download_backup=1&file=' . urlencode($backup_filename);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Database backup created successfully',
                    'file' => $backup_filename,
                    'download_url' => $download_url,
                    'size' => round(filesize($backup_file) / 1024, 2) . ' KB',
                    'tables_count' => count($tables)
                ]);
                break;
                
            case 'optimize_database':
                $tables = [];
                $result = $conn->query("SHOW TABLES");
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $tables[] = $row[0];
                }
                
                $optimized_count = 0;
                foreach ($tables as $table) {
                    $conn->exec("OPTIMIZE TABLE `{$table}`");
                    $optimized_count++;
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => "Database optimized successfully. {$optimized_count} tables optimized."
                ]);
                break;
                
            case 'update_notifications':
                // Save notification preferences
                $notifications = [
                    'email_system_alerts' => isset($_POST['email_system_alerts']) ? 1 : 0,
                    'email_inventory_warnings' => isset($_POST['email_inventory_warnings']) ? 1 : 0,
                    'email_user_activities' => isset($_POST['email_user_activities']) ? 1 : 0,
                    'email_backup_reminders' => isset($_POST['email_backup_reminders']) ? 1 : 0,
                    'app_new_transactions' => isset($_POST['app_new_transactions']) ? 1 : 0,
                    'app_system_updates' => isset($_POST['app_system_updates']) ? 1 : 0,
                    'app_scheduled_tasks' => isset($_POST['app_scheduled_tasks']) ? 1 : 0
                ];
                
                foreach ($notifications as $key => $value) {
                    $stmt = $conn->prepare("
                        INSERT INTO system_settings (setting_key, setting_value, setting_type, updated_by, updated_at) 
                        VALUES (?, ?, 'boolean', ?, NOW())
                        ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value),
                        updated_by = VALUES(updated_by),
                        updated_at = NOW()
                    ");
                    $stmt->execute([$key, $value, $user_id]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Notification settings updated successfully']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['restore_file'])) {
    try {
        $file = $_FILES['restore_file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = 'File upload error';
            header('Location: profile.php');
            exit();
        }
        
        // Check file extension
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'sql') {
            $_SESSION['error_message'] = 'Only SQL files are allowed';
            header('Location: profile.php');
            exit();
        }
        
        // Read SQL file
        $sql_content = file_get_contents($file['tmp_name']);
        
        if (empty($sql_content)) {
            $_SESSION['error_message'] = 'SQL file is empty';
            header('Location: profile.php');
            exit();
        }
        
        // Disable foreign key checks
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Execute SQL statements
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));
        $executed = 0;
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $conn->exec($statement);
                $executed++;
            }
        }
        
        // Re-enable foreign key checks
        $conn->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        $_SESSION['success_message'] = "Database restored successfully. {$executed} statements executed.";
        header('Location: profile.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error restoring database: ' . $e->getMessage();
        header('Location: profile.php');
        exit();
    }
}

// Handle non-AJAX POST requests (form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Generate API Key
        if (isset($_POST['generate_api_key'])) {
            $api_key = bin2hex(random_bytes(32));
            $stmt = $conn->prepare("UPDATE users SET api_key = ? WHERE id = ?");
            $stmt->execute([$api_key, $user_id]);
            $_SESSION['success_message'] = 'API key generated successfully';
            header('Location: profile.php');
            exit();
        }
        
        // Revoke API Key
        if (isset($_POST['revoke_api_key'])) {
            $stmt = $conn->prepare("UPDATE users SET api_key = NULL WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success_message'] = 'API key revoked successfully';
            header('Location: profile.php');
            exit();
        }
        
        // Enable 2FA
        if (isset($_POST['enable_2fa'])) {
            $verification_code = $_POST['verification_code'] ?? '';
            
            // In production, verify the code against the secret
            // For now, just enable it
            $two_factor_secret = bin2hex(random_bytes(16));
            $stmt = $conn->prepare("UPDATE users SET two_factor_auth = 1, two_factor_secret = ? WHERE id = ?");
            $stmt->execute([$two_factor_secret, $user_id]);
            $_SESSION['success_message'] = 'Two-factor authentication enabled successfully';
            header('Location: profile.php');
            exit();
        }
        
        // Disable 2FA
        if (isset($_POST['disable_2fa'])) {
            $stmt = $conn->prepare("UPDATE users SET two_factor_auth = 0, two_factor_secret = NULL WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success_message'] = 'Two-factor authentication disabled successfully';
            header('Location: profile.php');
            exit();
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        header('Location: profile.php');
        exit();
    }
}

// If no valid action, redirect to profile
header('Location: profile.php');
exit();
?>