<?php
// Start session and include database connection
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
        'transaction_history' => 'Transaction History',
        'schedule_pickup' => 'Schedule Pickup',
        'current_prices' => 'Current Prices',
        'loyalty_rewards' => 'Loyalty Rewards',
        'account_settings' => 'Account Settings',
        'logout' => 'Logout',
        'todays_scrap_prices' => 'Today\'s Scrap Prices',
        'updated' => 'Updated',
        'material' => 'Material',
        'price_per_kg' => 'Price (per kg)',
        'trend' => 'Trend',
        'view_all' => 'View All',
        'no_price_data' => 'No price data available',
        'live_prices' => 'Live Prices',
        'real_time_trends' => 'Real-Time Price Trends',
        'last_30_days' => 'Last 30 Days',
        'metals_prices' => 'Metals Prices',
        'electronics_ewaste' => 'Electronics & E-Waste',
        'plastics_glass' => 'Plastics & Glass',
        'other_materials' => 'Other Materials',
        'detailed_price_list' => 'Detailed Price List',
        'download_pdf' => 'Download as PDF',
        'weekly_high' => 'Weekly High',
        'monthly_avg' => 'Monthly Avg',
        'real_time_trend' => 'Real-Time Trend',
        'last_updated' => 'Last Updated'
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'transaction_history' => 'Kasaysayan ng Transaksyon',
        'schedule_pickup' => 'I-skedyul ang Pickup',
        'current_prices' => 'Kasalukuyang Mga Presyo',
        'loyalty_rewards' => 'Mga Gantimpala ng Loyalty',
        'account_settings' => 'Mga Setting ng Account',
        'logout' => 'Logout',
        'todays_scrap_prices' => 'Mga Presyo ng Scrap Ngayon',
        'updated' => 'Na-update',
        'material' => 'Materyal',
        'price_per_kg' => 'Presyo (bawat kg)',
        'trend' => 'Trend',
        'view_all' => 'Tingnan Lahat',
        'no_price_data' => 'Walang available na data ng presyo',
        'live_prices' => 'Live na Mga Presyo',
        'real_time_trends' => 'Real-Time na Mga Trend ng Presyo',
        'last_30_days' => 'Huling 30 Araw',
        'metals_prices' => 'Mga Presyo ng Metal',
        'electronics_ewaste' => 'Electronics at E-Waste',
        'plastics_glass' => 'Plastics at Glass',
        'other_materials' => 'Iba Pang Materyales',
        'detailed_price_list' => 'Detalyadong Listahan ng Presyo',
        'download_pdf' => 'I-download bilang PDF',
        'weekly_high' => 'Pinakamataas sa Linggo',
        'monthly_avg' => 'Average sa Buwan',
        'real_time_trend' => 'Real-Time na Trend',
        'last_updated' => 'Huling Na-update'
    ]
];

$t = $translations[$language];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user ID
$user_id = $_SESSION['user_id'];

// Get user info for header
$user_query = "SELECT first_name, last_name, profile_image FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);
$user_name = $user['first_name'] . ' ' . $user['last_name'];
$user_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

$prices = [];
$price_query = "SELECT m.*, 
                COALESCE(mp.buying_price, m.unit_price) as current_price,
                m.trend_direction,
                m.trend_change,
                DATE(m.updated_at) as last_updated
                FROM materials m 
                LEFT JOIN market_prices mp ON m.id = mp.material_id 
                WHERE m.status = 'active' 
                ORDER BY m.material_option";
$price_result = mysqli_query($conn, $price_query);
if ($price_result) {
    while ($row = mysqli_fetch_assoc($price_result)) {
        $prices[] = $row;
    }
}

$price_history = [];
$material_colors = [
    'Copper Wire' => '#6A7F46',
    'Aluminum Cans' => '#708B4C', 
    'Iron Scrap' => '#3C342C',
    'E-Waste' => '#6A7F46',
    'Stainless Steel' => '#2E2B29',
    'PET Bottles' => '#4A89DC',
    'Cardboard' => '#8B4513',
    'Steel' => '#696969',
    'Glass Bottles' => '#20B2AA',
    'Computer Parts' => '#9932CC',
    'Yero (Corrugated Sheets)' => '#CD853F',
    'Batteries' => '#FF6347'
];

foreach ($prices as $material) {
    $history_query = "SELECT buying_price, collected_on 
                     FROM market_prices 
                     WHERE material_id = ? 
                     ORDER BY collected_on DESC 
                     LIMIT 30";
    $history_stmt = mysqli_prepare($conn, $history_query);
    mysqli_stmt_bind_param($history_stmt, "i", $material['id']);
    mysqli_stmt_execute($history_stmt);
    $history_result = mysqli_stmt_get_result($history_stmt);
    
    $history_data = [];
    $prices_array = [];
    
    // If no historical data, generate recent data based on current price
    if (mysqli_num_rows($history_result) == 0) {
        $current_price = $material['unit_price'];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $fluctuation = (rand(-10, 10) / 100); // ±10% fluctuation
            $price = max(1, $current_price * (1 + $fluctuation));
            $history_data[$date] = $price;
            $prices_array[] = $price;
        }
    } else {
        while ($row = mysqli_fetch_assoc($history_result)) {
            $history_data[$row['collected_on']] = $row['buying_price'];
            $prices_array[] = $row['buying_price'];
        }
        // Fill missing days with interpolated data
        $dates = array_keys($history_data);
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            if (!isset($history_data[$date])) {
                $history_data[$date] = end($prices_array) * (1 + (rand(-5, 5) / 100));
            }
        }
        ksort($history_data);
    }
    
    $current_price = $material['current_price'] ?? $material['unit_price'];
    $previous_price = count($prices_array) > 1 ? $prices_array[count($prices_array)-2] : $current_price;
    $change_amount = abs($current_price - $previous_price);
    $change_percent = $previous_price > 0 ? (($current_price - $previous_price) / $previous_price) * 100 : 0;
    
    $trend = 'equal';
    if ($current_price > $previous_price) {
        $trend = 'up';
    } elseif ($current_price < $previous_price) {
        $trend = 'down';
    }
    
    $price_history[$material['id']] = [
        'name' => $material['material_option'],
        'color' => $material_colors[$material['material_option']] ?? '#6A7F46',
        'data' => $history_data,
        'current' => $current_price,
        'weekly_high' => max($prices_array),
        'monthly_avg' => array_sum($prices_array) / count($prices_array),
        'trend' => $trend,
        'change_amount' => $change_amount,
        'change_percent' => abs($change_percent)
    ];
}

$metals_data = [];
$electronics_data = [];
$plastics_data = [];
$others_data = [];

foreach ($prices as $material) {
    $chart_item = [
        'label' => $material['material_option'],
        'data' => array_values($price_history[$material['id']]['data']),
        'borderColor' => $price_history[$material['id']]['color'],
        'backgroundColor' => str_replace(')', ', 0.1)', $price_history[$material['id']]['color']),
        'borderWidth' => 3,
        'tension' => 0.3,
        'fill' => true
    ];
    
    // Categorize materials for different charts
    if (in_array($material['material_option'], ['Copper Wire', 'Aluminum Cans', 'Iron Scrap', 'Stainless Steel', 'Steel'])) {
        $metals_data[] = $chart_item;
    } elseif (in_array($material['material_option'], ['E-Waste', 'Computer Parts', 'Batteries'])) {
        $electronics_data[] = $chart_item;
    } elseif (in_array($material['material_option'], ['PET Bottles', 'Glass Bottles'])) {
        $plastics_data[] = $chart_item;
    } else {
        $others_data[] = $chart_item;
    }
}

$chart_labels = array_keys($price_history[$prices[0]['id']]['data']);
$formatted_labels = array_map(function($label) {
    return date('M j', strtotime($label));
}, $chart_labels);

$metals_chart_data = json_encode(['labels' => $formatted_labels, 'datasets' => $metals_data]);
$electronics_chart_data = json_encode(['labels' => $formatted_labels, 'datasets' => $electronics_data]);
$plastics_chart_data = json_encode(['labels' => $formatted_labels, 'datasets' => $plastics_data]);
$others_chart_data = json_encode(['labels' => $formatted_labels, 'datasets' => $others_data]);

// Close database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Current Prices</title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-beige: #E6D8C3;
            --sales-orange: #6A7F46; /* Changed to olive green */
            --stock-green: #708B4C;
            --panel-cream: #F2EAD3;
            --topbar-brown: #3C342C;
            --text-dark: #2E2B29;
            --icon-green: #6A7F46;
            --icon-orange: #6A7F46; /* Changed to olive green */
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
            overflow-x: hidden;
        }

        body.dark-mode {
            background-color: var(--dark-bg-primary);
            color: var(--dark-text-primary);
        }

        /* Enhanced animated background with mesh gradient effect */
        .bg-decoration {
            position: fixed;
            border-radius: 50%;
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
            filter: blur(80px);
        }
        
        .bg-decoration-1 {
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, var(--accent-blue) 0%, transparent 70%);
            top: -250px;
            left: -250px;
            animation: float 25s ease-in-out infinite;
        }
        
        .bg-decoration-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--icon-green) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            animation: float 20s ease-in-out infinite reverse;
        }
        
        .bg-decoration-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--sales-orange) 0%, transparent 70%);
            top: 40%;
            left: 20%;
            animation: float 30s ease-in-out infinite;
        }
        
        .bg-decoration-4 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, transparent 70%);
            top: 60%;
            right: 25%;
            animation: float 22s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(60px, -60px) scale(1.15) rotate(120deg); }
            66% { transform: translate(-40px, 40px) scale(0.85) rotate(240deg); }
        }

        /* Enhanced watermark with glow effect */
        .watermark-logo {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 700px;
            height: 700px;
            opacity: 0.12;
            z-index: 0;
            pointer-events: none;
            transition: opacity 0.6s ease;
            animation: floatWatermark 25s ease-in-out infinite;
            filter: drop-shadow(0 0 60px rgba(106, 127, 70, 0.3));
        }
        
        @keyframes floatWatermark {
            0%, 100% { transform: translate(-50%, -50%) scale(1) rotate(0deg); }
            50% { transform: translate(-50%, -52%) scale(1.08) rotate(5deg); }
        }

        /* Sidebar - New Vibrant Design */
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
            opacity: 0;
            transform: translateX(-20px);
            animation: slideInLeft 0.8s ease forwards 0.5s;
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
            opacity: 0;
            transform: scale(0.8);
            animation: fadeInScale 0.8s ease forwards 0.7s;
            cursor: pointer;
            overflow: hidden;
        }

        body.dark-mode .user-avatar {
            background-color: var(--dark-bg-tertiary);
            color: var(--dark-text-primary);
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        }

        body.dark-mode .user-avatar:hover {
            box-shadow: 0 6px 15px var(--dark-shadow);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .user-name {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 5px;
            text-align: center;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.8s ease forwards 0.9s;
        }

        .user-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--panel-cream);
            opacity: 0.8;
            font-weight: 500;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.8s ease forwards 1.1s;
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
            opacity: 0;
            transform: translateX(-10px);
        }

        .nav-menu li:nth-child(1) { animation: slideInLeft 0.6s ease forwards 1.3s; }
        .nav-menu li:nth-child(2) { animation: slideInLeft 0.6s ease forwards 1.4s; }
        .nav-menu li:nth-child(3) { animation: slideInLeft 0.6s ease forwards 1.5s; }
        .nav-menu li:nth-child(4) { animation: slideInLeft 0.6s ease forwards 1.6s; }
        .nav-menu li:nth-child(5) { animation: slideInLeft 0.6s ease forwards 1.7s; }
        .nav-menu li:nth-child(6) { animation: slideInLeft 0.6s ease forwards 1.8s; }

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
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.8s ease forwards 1.9s;
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
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.8s ease forwards 1.2s;
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
            background-color: rgba(106, 127, 70, 0.3);
        }

        .flag-icon {
            width: 20px;
            height: 15px;
            border-radius: 2px;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 0.8s;
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
            transition: color 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 1s;
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
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 1.1s;
        }

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

        /* Real-time indicator styles */
        .real-time-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: rgba(46, 204, 113, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid rgba(46, 204, 113, 0.3);
            transition: all 0.3s ease;
        }

        body.dark-mode .real-time-indicator {
            background-color: rgba(46, 204, 113, 0.15);
            border-color: rgba(46, 204, 113, 0.4);
        }

        .real-time-dot {
            width: 8px;
            height: 8px;
            background-color: #2ECC71;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 1.7s;
        }

        body.dark-mode .dashboard-card {
            background-color: var(--dark-bg-secondary);
            border-color: var(--dark-border);
            box-shadow: 0 5px 15px var(--dark-shadow);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: border-color 0.3s ease;
        }

        body.dark-mode .card-header {
            border-bottom-color: var(--dark-border);
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.3px;
            transition: color 0.3s ease;
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

        /* Price Table */
        .price-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .price-table thead {
            position: sticky;
            top: 0;
        }

        .price-table th {
            background-color: rgba(106, 127, 70, 0.08);
            font-weight: 600;
            color: var(--icon-green);
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid rgba(106, 127, 70, 0.2);
            font-size: 14px;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }

        body.dark-mode .price-table th {
            background-color: rgba(106, 127, 70, 0.15);
            color: var(--dark-text-primary);
            border-bottom-color: var(--dark-border);
        }

        .price-table td {
            padding: 14px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        body.dark-mode .price-table td {
            border-bottom-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .price-table tr:last-child td {
            border-bottom: none;
        }

        .price-table tr:hover td {
            background-color: rgba(106, 127, 70, 0.03);
        }

        body.dark-mode .price-table tr:hover td {
            background-color: rgba(106, 127, 70, 0.1);
        }

        .trend-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Price Cards */
        .price-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .price-card {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            border-top: 4px solid var(--icon-green);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 1.4s;
        }
        
        body.dark-mode .price-card {
            background-color: var(--dark-bg-secondary);
            box-shadow: 0 3px 10px var(--dark-shadow);
        }
        
        .price-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        body.dark-mode .price-card:hover {
            box-shadow: 0 8px 20px var(--dark-shadow);
        }
        
        .price-card h3 {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-dark);
            font-size: 18px;
            transition: color 0.3s ease;
        }

        body.dark-mode .price-card h3 {
            color: var(--dark-text-primary);
        }
        
        .price-card i {
            font-size: 22px;
        }
        
        .price-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .price-value {
            color: var(--dark-text-primary);
        }
        
        .price-change {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            margin-bottom: 15px;
        }
        
        .price-change.up {
            color: var(--stock-green);
        }
        
        .price-change.down {
            color: var(--sales-orange);
        }
        
        .price-change.neutral {
            color: var(--text-dark);
            opacity: 0.7;
        }

        body.dark-mode .price-change.neutral {
            color: var(--dark-text-secondary);
        }
        
        .price-meta {
            font-size: 14px;
            color: var(--text-dark);
            opacity: 0.7;
            margin-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.05);
            padding-top: 15px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            transition: all 0.3s ease;
        }

        body.dark-mode .price-meta {
            color: var(--dark-text-secondary);
            border-top-color: var(--dark-border);
        }
        
        .price-meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .price-meta-label {
            font-size: 13px;
            margin-bottom: 3px;
        }
        
        .price-meta-value {
            font-weight: 500;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .price-meta-value {
            color: var(--dark-text-primary);
        }

        /* Material Icon */
        .material-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Charts grid layout for 4 charts */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .price-history-chart {
            height: 350px;
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease forwards 1.5s;
        }

        body.dark-mode .price-history-chart {
            background-color: var(--dark-bg-secondary);
            border-color: var(--dark-border);
            box-shadow: 0 3px 10px var(--dark-shadow);
        }
        
        .chart-container {
            position: relative;
            height: calc(100% - 40px);
            width: 100%;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s ease;
        }

        body.dark-mode .chart-title {
            color: var(--dark-text-primary);
        }

        /* Price Table Container */
        .price-table-container {
            overflow-x: auto;
            margin-top: 25px;
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 2px 5px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }

        body.dark-mode .price-table-container {
            border-color: var(--dark-border);
            box-shadow: 0 2px 5px var(--dark-shadow);
        }

        /* Price Tabs */
        .price-tabs {
            display: flex;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            margin-bottom: 25px;
            gap: 5px;
            transition: border-color 0.3s ease;
        }

        body.dark-mode .price-tabs {
            border-bottom-color: var(--dark-border);
        }
        
        .price-tab {
            padding: 12px 25px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            color: var(--text-dark);
            opacity: 0.7;
            transition: all 0.3s;
            border-radius: 5px 5px 0 0;
        }

        body.dark-mode .price-tab {
            color: var(--dark-text-primary);
        }
        
        .price-tab:hover {
            background-color: rgba(106, 127, 70, 0.05);
            opacity: 1;
        }

        body.dark-mode .price-tab:hover {
            background-color: rgba(106, 127, 70, 0.1);
        }
        
        .price-tab.active {
            border-bottom-color: var(--icon-green);
            color: var(--icon-green);
            background-color: rgba(106, 127, 70, 0.05);
            opacity: 1;
        }

        body.dark-mode .price-tab.active {
            background-color: rgba(106, 127, 70, 0.1);
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

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Responsive styles */
        @media (max-width: 1200px) {
            .sidebar {
                width: 240px;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
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
        }

        @media (max-width: 768px) {
            .price-cards {
                grid-template-columns: 1fr 1fr;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .price-history-chart {
                height: 300px;
            }
        }

        @media (max-width: 576px) {
            .price-cards {
                grid-template-columns: 1fr;
            }
            
            .price-history-chart {
                height: 250px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced animated background with mesh gradient effect -->
    <div class="bg-decoration bg-decoration-1"></div>
    <div class="bg-decoration bg-decoration-2"></div>
    <div class="bg-decoration bg-decoration-3"></div>
    <div class="bg-decoration bg-decoration-4"></div>
    
    <!-- Enhanced watermark with glow effect -->
    <img src="img/MainLogo.svg" alt="JunkValue Watermark" class="watermark-logo">
   
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

   
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar">
                <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile">
                <?php else: ?>
                    <?php echo $user_initials; ?>
                <?php endif; ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="user-status">
                <span class="status-indicator"></span>
                <span>Active</span>
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
            <li><a href="index.php"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="transaction.php"><i class="fas fa-history"></i> <?php echo $t['transaction_history']; ?></a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> <?php echo $t['schedule_pickup']; ?></a></li>
            <li><a href="#" class="active"><i class="fas fa-coins"></i> <?php echo $t['current_prices']; ?></a></li>
            <li><a href="rewards.php"><i class="fas fa-award"></i> <?php echo $t['loyalty_rewards']; ?></a></li>
            <li><a href="settings.php"><i class="fas fa-user-cog"></i> <?php echo $t['account_settings']; ?></a></li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
            </a>
        </div>
    </div>
    
   
    <div class="main-content">
        <div class="header">
            <h1 class="page-title"><?php echo $t['current_prices']; ?></h1>
            
            <div class="header-controls">
                <button class="dark-mode-toggle">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
                
                <div class="real-time-indicator">
                    <div class="real-time-dot"></div>
                    <span style="font-size: 14px; font-weight: 500;"><?php echo $t['live_prices']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-coins"></i> <?php echo $t['todays_scrap_prices']; ?></h3>
                <span style="color: var(--text-dark); opacity: 0.7; font-size: 14px;">
                    <i class="fas fa-sync-alt"></i> <?php echo $t['updated']; ?>: <span id="lastUpdate"><?php echo date('F j, Y g:i A'); ?></span>
                </span>
            </div>
            
            <div class="price-tabs">
                <div class="price-tab active">All Materials</div>
                <div class="price-tab">Metals</div>
                <div class="price-tab">E-Waste</div>
                <div class="price-tab">Plastics</div>
                <div class="price-tab">Paper</div>
            </div>
            
             
            <div class="price-cards" id="priceCards">
                <?php foreach ($prices as $material): 
                    $history = $price_history[$material['id']];
                    $trend_class = $history['trend'] === 'up' ? 'up' : ($history['trend'] === 'down' ? 'down' : 'neutral');
                    $trend_icon = $history['trend'] === 'up' ? 'fa-arrow-up' : ($history['trend'] === 'down' ? 'fa-arrow-down' : 'fa-equals');
                    $icon_color = $history['color'];
                    
                    // Set icons based on material type
                    $material_icon = '';
                    switch(true) {
                        case strpos($material['material_option'], 'Copper') !== false:
                            $material_icon = 'fa-bolt';
                            break;
                        case strpos($material['material_option'], 'Aluminum') !== false:
                            $material_icon = 'fa-cubes';
                            break;
                        case strpos($material['material_option'], 'Iron') !== false:
                            $material_icon = 'fa-weight-hanging';
                            break;
                        case strpos($material['material_option'], 'E-Waste') !== false:
                        case strpos($material['material_option'], 'Computer') !== false:
                            $material_icon = 'fa-microchip';
                            break;
                        case strpos($material['material_option'], 'Steel') !== false:
                            $material_icon = 'fa-industry';
                            break;
                        case strpos($material['material_option'], 'PET') !== false:
                        case strpos($material['material_option'], 'Glass') !== false:
                            $material_icon = 'fa-recycle';
                            break;
                        case strpos($material['material_option'], 'Batteries') !== false:
                            $material_icon = 'fa-battery-half';
                            break;
                        default:
                            $material_icon = 'fa-box';
                    }
                ?>
                <div class="price-card" style="border-top-color: <?php echo $icon_color; ?>" data-material="<?php echo $material['id']; ?>">
                    <div class="material-icon" style="background-color: <?php echo $icon_color; ?>">
                        <i class="fas <?php echo $material_icon; ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($material['material_option']); ?></h3>
                    <div class="price-value">₱<?php echo number_format($history['current'], 2); ?>/kg</div>
                    <div class="price-change <?php echo $trend_class; ?>">
                        <i class="fas <?php echo $trend_icon; ?>"></i>
                        <span>
                            <?php if ($history['trend'] !== 'equal'): ?>
                                <?php echo $history['trend'] === 'up' ? '+' : '-'; ?>
                            <?php endif; ?>
                            ₱<?php echo number_format($history['change_amount'], 2); ?> 
                            (<?php echo number_format($history['change_percent'], 1); ?>%)
                        </span>
                    </div>
                    <div class="price-meta">
                        <div class="price-meta-item">
                            <span class="price-meta-label"><?php echo $t['weekly_high']; ?></span>
                            <span class="price-meta-value">₱<?php echo number_format($history['weekly_high'], 2); ?></span>
                        </div>
                        <div class="price-meta-item">
                            <span class="price-meta-label"><?php echo $t['monthly_avg']; ?></span>
                            <span class="price-meta-value">₱<?php echo number_format($history['monthly_avg'], 2); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
             
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> <?php echo $t['real_time_trends']; ?> (<?php echo $t['last_30_days']; ?>)</h3>
                <a href="#" class="view-all">
                    <?php echo $t['view_all']; ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="charts-grid">
                <div class="price-history-chart">
                    <div class="chart-title">
                        <i class="fas fa-industry" style="color: var(--sales-orange);"></i>
                        <?php echo $t['metals_prices']; ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="metalsChart"></canvas>
                    </div>
                </div>
                
                <div class="price-history-chart">
                    <div class="chart-title">
                        <i class="fas fa-microchip" style="color: var(--icon-green);"></i>
                        <?php echo $t['electronics_ewaste']; ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="electronicsChart"></canvas>
                    </div>
                </div>
                
                <div class="price-history-chart">
                    <div class="chart-title">
                        <i class="fas fa-recycle" style="color: var(--accent-blue);"></i>
                        <?php echo $t['plastics_glass']; ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="plasticsChart"></canvas>
                    </div>
                </div>
                
                <div class="price-history-chart">
                    <div class="chart-title">
                        <i class="fas fa-box" style="color: var(--stock-green);"></i>
                        <?php echo $t['other_materials']; ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="othersChart"></canvas>
                    </div>
                </div>
            </div>
            
           
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> <?php echo $t['detailed_price_list']; ?></h3>
                <a href="#" class="view-all">
                    <?php echo $t['download_pdf']; ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="price-table-container">
                <table class="price-table">
                    <thead>
                        <tr>
                            <th><?php echo $t['material']; ?></th>
                            <th><?php echo $t['price_per_kg']; ?></th>
                            <th><?php echo $t['real_time_trend']; ?></th>
                            <th><?php echo $t['last_updated']; ?></th>
                        </tr>
                    </thead>
                    <tbody id="priceTableBody">
                        <?php foreach ($prices as $material): 
                            $history = $price_history[$material['id']];
                            $trend_icon = $history['trend'] === 'up' ? 'fa-arrow-up' : ($history['trend'] === 'down' ? 'fa-arrow-down' : 'fa-equals');
                            $trend_color = $history['trend'] === 'up' ? 'var(--stock-green)' : ($history['trend'] === 'down' ? 'var(--sales-orange)' : 'var(--text-dark)');
                        ?>
                        <tr data-material="<?php echo $material['id']; ?>">
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($material['material_option']); ?></td>
                            <td style="font-weight: 600;">₱<?php echo number_format($history['current'], 2); ?></td>
                            <td class="trend-cell">
                                <i class="fas <?php echo $trend_icon; ?>" style="color: <?php echo $trend_color; ?>; opacity: 0.8;"></i>
                                <span style="color: <?php echo $trend_color; ?>">
                                    <?php echo $history['trend'] === 'up' ? '+' : ($history['trend'] === 'down' ? '-' : ''); ?>
                                    <?php echo number_format($history['change_percent'], 1); ?>%
                                </span>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($material['last_updated'] ?? 'now')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Dark Mode Toggle
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

        // Language Dropdown
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

        let priceUpdateInterval;
        
        function updatePrices() {
            fetch('get_real_time_prices.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updatePriceCards(data.prices);
                        updatePriceTable(data.prices);
                        updateCharts(data.chartData);
                        document.getElementById('lastUpdate').textContent = new Date().toLocaleString();
                    }
                })
                .catch(error => console.error('Error updating prices:', error));
        }
        
        function updatePriceCards(prices) {
            prices.forEach(price => {
                const card = document.querySelector(`[data-material="${price.id}"]`);
                if (card) {
                    const priceValue = card.querySelector('.price-value');
                    const priceChange = card.querySelector('.price-change');
                    const trendIcon = priceChange.querySelector('i');
                    
                    priceValue.textContent = `₱${parseFloat(price.current_price).toFixed(2)}/kg`;
                    
                    // Update trend
                    priceChange.className = `price-change ${price.trend}`;
                    trendIcon.className = `fas fa-arrow-${price.trend === 'up' ? 'up' : price.trend === 'down' ? 'down' : 'equals'}`;
                    
                    const changeText = priceChange.querySelector('span');
                    const sign = price.trend === 'up' ? '+' : price.trend === 'down' ? '-' : '';
                    changeText.textContent = `${sign}₱${parseFloat(price.change_amount).toFixed(2)} (${parseFloat(price.change_percent).toFixed(1)}%)`;
                }
            });
        }
        
        function updatePriceTable(prices) {
            prices.forEach(price => {
                const row = document.querySelector(`#priceTableBody tr[data-material="${price.id}"]`);
                if (row) {
                    const cells = row.querySelectorAll('td');
                    cells[1].textContent = `₱${parseFloat(price.current_price).toFixed(2)}`;
                    
                    const trendCell = cells[2];
                    const trendIcon = trendCell.querySelector('i');
                    const trendText = trendCell.querySelector('span');
                    
                    const trendColor = price.trend === 'up' ? 'var(--stock-green)' : price.trend === 'down' ? 'var(--sales-orange)' : 'var(--text-dark)';
                    trendIcon.className = `fas fa-arrow-${price.trend === 'up' ? 'up' : price.trend === 'down' ? 'down' : 'equals'}`;
                    trendIcon.style.color = trendColor;
                    
                    const sign = price.trend === 'up' ? '+' : price.trend === 'down' ? '-' : '';
                    trendText.textContent = `${sign}${parseFloat(price.change_percent).toFixed(1)}%`;
                    trendText.style.color = trendColor;
                    
                    cells[3].textContent = new Date().toLocaleString();
                }
            });
        }

        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(46, 43, 41, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₱' + context.parsed.y.toFixed(2) + '/kg';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toFixed(2);
                        },
                        color: 'var(--text-dark)',
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxRotation: 45,
                        color: 'var(--text-dark)',
                        font: { size: 10 }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        };

        // Initialize charts
        const metalsData = <?php echo $metals_chart_data; ?>;
        const electronicsData = <?php echo $electronics_chart_data; ?>;
        const plasticsData = <?php echo $plastics_chart_data; ?>;
        const othersData = <?php echo $others_chart_data; ?>;

        const metalsChart = new Chart(document.getElementById('metalsChart'), {
            type: 'line',
            data: metalsData,
            options: chartOptions
        });

        const electronicsChart = new Chart(document.getElementById('electronicsChart'), {
            type: 'line',
            data: electronicsData,
            options: chartOptions
        });

        const plasticsChart = new Chart(document.getElementById('plasticsChart'), {
            type: 'line',
            data: plasticsData,
            options: chartOptions
        });

        const othersChart = new Chart(document.getElementById('othersChart'), {
            type: 'line',
            data: othersData,
            options: chartOptions
        });
        
        // Tab switching functionality
        const tabs = document.querySelectorAll('.price-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelector('.price-tab.active')?.classList.remove('active');
                this.classList.add('active');
            });
        });

        priceUpdateInterval = setInterval(updatePrices, 30000);
        
        // Update prices immediately on page load
        setTimeout(updatePrices, 2000);
    </script>
</body>
</html>