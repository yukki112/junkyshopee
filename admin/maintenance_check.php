<?php
// Maintenance Mode Checker
// Include this file at the top of every page that needs maintenance mode protection

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connection.php';

// Get maintenance mode settings from database
function getMaintenanceSettings($conn) {
    try {
        $query = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('maintenance_mode', 'maintenance_message', 'allow_admin_access')";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    } catch (PDOException $e) {
        // If there's an error, assume maintenance mode is off
        return [
            'maintenance_mode' => '0',
            'maintenance_message' => 'System is currently unavailable.',
            'allow_admin_access' => '1'
        ];
    }
}

// Check if user is admin
function isAdmin($conn) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    try {
        $query = "SELECT is_admin FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user && $user['is_admin'] == 1;
    } catch (PDOException $e) {
        return false;
    }
}

// Get maintenance settings
$maintenance_settings = getMaintenanceSettings($conn);
$is_maintenance_mode = $maintenance_settings['maintenance_mode'] == '1';
$allow_admin_access = $maintenance_settings['allow_admin_access'] == '1';
$maintenance_message = $maintenance_settings['maintenance_message'];

// Check if current page is login page
$current_page = basename($_SERVER['PHP_SELF']);
$is_login_page = (strpos($current_page, 'login') !== false || strpos($current_page, 'Login') !== false);

// If maintenance mode is enabled
if ($is_maintenance_mode) {
    $user_is_admin = isAdmin($conn);
    
    // If not on login page and not admin (or admin access not allowed)
    if (!$is_login_page) {
        if (!$user_is_admin || !$allow_admin_access) {
            // Show maintenance page
            showMaintenancePage($maintenance_message, $allow_admin_access);
            exit();
        }
    }
}

function showMaintenancePage($message, $allow_admin_login) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance Mode - JunkValue</title>
        <link rel="icon" type="image/png" href="img/MainLogo.svg">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            }
            
            body {
                background: linear-gradient(135deg, #E6D8C3 0%, #F2EAD3 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .maintenance-container {
                background: white;
                border-radius: 20px;
                padding: 60px 40px;
                max-width: 600px;
                width: 100%;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            }
            
            .maintenance-icon {
                width: 120px;
                height: 120px;
                background: linear-gradient(135deg, #708B4C 0%, #6A7F46 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 30px;
                animation: pulse 2s infinite;
            }
            
            .maintenance-icon i {
                font-size: 50px;
                color: white;
            }
            
            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(112, 139, 76, 0.4);
                }
                50% {
                    transform: scale(1.05);
                    box-shadow: 0 0 0 20px rgba(112, 139, 76, 0);
                }
            }
            
            h1 {
                font-size: 32px;
                color: #3C342C;
                margin-bottom: 20px;
                font-weight: 700;
            }
            
            .maintenance-message {
                font-size: 18px;
                color: #666;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            
            .admin-login-btn {
                display: inline-block;
                padding: 15px 30px;
                background: linear-gradient(90deg, #6A7F46 0%, #708B4C 100%);
                color: white;
                text-decoration: none;
                border-radius: 10px;
                font-weight: 600;
                transition: all 0.3s ease;
                margin-top: 20px;
            }
            
            .admin-login-btn:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(106, 127, 70, 0.3);
            }
            
            .status-indicator {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 20px;
                background: rgba(255, 152, 0, 0.1);
                border-radius: 50px;
                color: #ff9800;
                font-weight: 500;
                margin-top: 30px;
            }
            
            .status-dot {
                width: 10px;
                height: 10px;
                background: #ff9800;
                border-radius: 50%;
                animation: blink 1.5s infinite;
            }
            
            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.3; }
            }
        </style>
    </head>
    <body>
        <div class="maintenance-container">
            <div class="maintenance-icon">
                <i class="fas fa-tools"></i>
            </div>
            <h1>System Maintenance</h1>
            <p class="maintenance-message"><?php echo htmlspecialchars($message); ?></p>
            
            <div class="status-indicator">
                <span class="status-dot"></span>
                <span>Maintenance in Progress</span>
            </div>
            
            <?php if ($allow_admin_login): ?>
                <a href="../Customer-portal/Login/Login.php" class="admin-login-btn">
                    <i class="fas fa-user-shield"></i> Admin Login
                </a>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}
?>
