<?php
// Start session and include database connection
session_start();
require_once 'db_connection.php';
require_once 'maintenance_check.php';

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
        'welcome_back' => 'Welcome back',
        'ready_to_turn_scrap' => 'Ready to turn your scrap into cash? Check today\'s prices, schedule a pickup, or track your rewards below.',
        'new_transaction' => 'New Transaction',
        'your_referral_qr' => 'Your Referral QR Code',
        'share_qr_earn' => 'Share this QR code with friends to earn 100 points for each successful referral!',
        'your_referral_code' => 'Your Referral Code',
        'share_code_earn' => 'Share your code with friends and earn bonus points when they sign up and make their first transaction.',
        'earn_points_referral' => 'You\'ll earn 100 points for each successful referral.',
        'request_pickup' => 'Request Pickup',
        'schedule_collection' => 'Schedule a collection for your recyclables',
        'price_calculator' => 'Price Calculator',
        'estimate_earnings' => 'Estimate your earnings',
        'scan_qr_code' => 'Scan QR Code',
        'quick_checkin' => 'Quick check-in at our facility',
        'refer_friend' => 'Refer a Friend',
        'earn_bonus_points' => 'Earn bonus points for referrals',
        'todays_scrap_prices' => 'Today\'s Scrap Prices',
        'updated' => 'Updated',
        'material' => 'Material',
        'price_per_kg' => 'Price (per kg)',
        'trend' => 'Trend',
        'your_loyalty_rewards' => 'Your Loyalty Rewards',
        'member' => 'Member',
        'points' => 'points',
        'progress_to_tier' => 'Progress to',
        'tier' => 'tier',
        'need_more_points' => 'Need',
        'more_points_to_reach' => 'more points to reach',
        'congratulations_highest' => 'Congratulations! You\'re at the ultimate Ethereal tier with',
        'points_exclamation' => 'points!',
        'next_tier' => 'Next Tier',
        'recent_transactions' => 'Recent Transactions',
        'view_all' => 'View All',
        'no_recent_transactions' => 'No recent transactions found',
        'no_price_data' => 'No price data available',
        'calculate_value' => 'Calculate Value',
        'estimated_value' => 'Estimated Value',
        'your_qr_code' => 'Your QR Code',
        'scan_quick_checkin' => 'Scan this code at our facility for quick check-in',
        'download_qr_code' => 'Download QR Code',
        'refer_friend_earn' => 'Share your referral link and earn 100 loyalty points for each friend who signs up and completes their first transaction!',
        'or_share_directly' => 'Or share directly',
        'weight' => 'Weight' // Added missing translation key
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'transaction_history' => 'Kasaysayan ng Transaksyon',
        'schedule_pickup' => 'I-skedyul ang Pickup',
        'current_prices' => 'Kasalukuyang Mga Presyo',
        'loyalty_rewards' => 'Mga Gantimpala ng Loyalty',
        'account_settings' => 'Mga Setting ng Account',
        'logout' => 'Logout',
        'welcome_back' => 'Maligayang pagbabalik',
        'ready_to_turn_scrap' => 'Handa nang gawing pera ang iyong scrap? Tingnan ang mga presyo ngayon, mag-skedyul ng pickup, o subaybayan ang iyong mga gantimpala sa ibaba.',
        'new_transaction' => 'Bagong Transaksyon',
        'your_referral_qr' => 'Iyong Referral QR Code',
        'share_qr_earn' => 'Ibahagi ang QR code na ito sa mga kaibigan upang kumita ng 100 puntos para sa bawat matagumpay na referral!',
        'your_referral_code' => 'Iyong Referral Code',
        'share_code_earn' => 'Ibahagi ang iyong code sa mga kaibigan at kumita ng mga bonus point kapag nag-sign up sila at ginawa ang kanilang unang transaksyon.',
        'earn_points_referral' => 'Makakakuha ka ng 100 puntos para sa bawat matagumpay na referral.',
        'request_pickup' => 'Mag-request ng Pickup',
        'schedule_collection' => 'Mag-skedyul ng koleksyon para sa iyong mga recyclable',
        'price_calculator' => 'Calculator ng Presyo',
        'estimate_earnings' => 'Tantyahin ang iyong mga kita',
        'scan_qr_code' => 'I-scan ang QR Code',
        'quick_checkin' => 'Mabilis na check-in sa aming pasilidad',
        'refer_friend' => 'I-refer ang Kaibigan',
        'earn_bonus_points' => 'Kumita ng mga bonus point para sa mga referral',
        'todays_scrap_prices' => 'Mga Presyo ng Scrap Ngayon',
        'updated' => 'Na-update',
        'material' => 'Materyal',
        'price_per_kg' => 'Presyo (bawat kg)',
        'trend' => 'Trend',
        'your_loyalty_rewards' => 'Iyong Mga Gantimpala sa Loyalty',
        'member' => 'Miyembro',
        'points' => 'mga puntos',
        'progress_to_tier' => 'Pag-unlad sa',
        'tier' => 'tier',
        'need_more_points' => 'Kailangan',
        'more_points_to_reach' => 'pang puntos upang maabot ang',
        'congratulations_highest' => 'Congratulations! Nasa ultimate Ethereal tier ka na na may',
        'points_exclamation' => 'mga puntos!',
        'next_tier' => 'Susunod na Tier',
        'recent_transactions' => 'Mga Kamakailang Transaksyon',
        'view_all' => 'Tingnan Lahat',
        'no_recent_transactions' => 'Walang nakitang mga kamakailang transaksyon',
        'no_price_data' => 'Walang available na data ng presyo',
        'calculate_value' => 'Kalkulahin ang Halaga',
        'estimated_value' => 'Tinantyang Halaga',
        'your_qr_code' => 'Iyong QR Code',
        'scan_quick_checkin' => 'I-scan ang code na ito sa aming pasilidad para sa mabilis na check-in',
        'download_qr_code' => 'I-download ang QR Code',
        'refer_friend_earn' => 'Ibahagi ang iyong referral link at kumita ng 100 loyalty point para sa bawat kaibigan na mag-sign up at makumpleto ang kanilang unang transaksyon!',
        'or_share_directly' => 'O ibahagi nang direkta',
        'weight' => 'Timbang' // Added missing translation key
    ]
];

$t = $translations[$language];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login/Login.php");
    exit();
}

// Get current user ID and info
$user_id = $_SESSION['user_id'];
$user_query = "SELECT first_name, last_name, profile_image, loyalty_points, loyalty_tier, referral_code FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);
$user_name = $user['first_name'] . ' ' . $user['last_name'];
$user_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

// Calculate loyalty progress
$current_points = $user['loyalty_points'];
$current_tier = $user['loyalty_tier'];
$referral_code = $user['referral_code'];

$tiers = [
    'bronze' => [
        'min_points' => 0,
        'max_points' => 999,
        'next_tier' => 'silver',
        'previous_tier' => null,
        'benefits' => [
            '1% bonus on all sales',
            '1 free pickup/month'
        ]
    ],
    'silver' => [
        'min_points' => 1000,
        'max_points' => 2499,
        'next_tier' => 'gold',
        'previous_tier' => 'bronze',
        'benefits' => [
            '5% bonus on all sales',
            '2 free pickups/month',
            'Priority service'
        ]
    ],
    'gold' => [
        'min_points' => 2500,
        'max_points' => 4999,
        'next_tier' => 'platinum',
        'previous_tier' => 'silver',
        'benefits' => [
            '10% bonus on all sales',
            '3 free pickups/month',
            'Priority service',
            'Exclusive offers',
            'Dedicated support'
        ]
    ],
    'platinum' => [
        'min_points' => 5000,
        'max_points' => 9999,
        'next_tier' => 'diamond',
        'previous_tier' => 'gold',
        'benefits' => [
            '12% bonus on all sales',
            '4 free pickups/month',
            'VIP priority service',
            'Exclusive premium offers',
            'Dedicated premium support',
            'Special platinum rewards'
        ]
    ],
    'diamond' => [
        'min_points' => 10000,
        'max_points' => 99999,
        'next_tier' => 'ethereal',
        'previous_tier' => 'platinum',
        'benefits' => [
            '15% bonus on all sales',
            '5 free pickups/month',
            'VIP priority service',
            'Exclusive premium offers',
            'Dedicated premium support',
            'Special diamond rewards'
        ]
    ],
    'ethereal' => [
        'min_points' => 100000,
        'max_points' => PHP_INT_MAX,
        'next_tier' => null,
        'previous_tier' => 'diamond',
        'benefits' => [
            '25% bonus on all sales',
            'Unlimited free pickups',
            'Ultimate VIP service',
            'Exclusive ethereal offers',
            'Personal account manager',
            'Special ethereal rewards',
            'Lifetime benefits'
        ]
    ]
];

if (!isset($tiers[$current_tier])) {
    $current_tier = 'bronze'; // Default to bronze if tier is not defined
}

// Calculate progress to next tier
$progress_percentage = 0;
$points_to_next_tier = 0;
$next_tier = $tiers[$current_tier]['next_tier'];

if ($next_tier) {
    $points_to_next_tier = $tiers[$next_tier]['min_points'] - $current_points;
    $tier_range = $tiers[$next_tier]['min_points'] - $tiers[$current_tier]['min_points'];
    $progress_percentage = min(100, max(0, round(($current_points - $tiers[$current_tier]['min_points']) / $tier_range * 100)));
}

$materials_query = "SELECT material_option, unit_price, trend_change, trend_direction FROM materials WHERE status = 'active' ORDER BY material_option";
$materials_result = mysqli_query($conn, $materials_query);

$transaction_query = "SELECT * FROM transactions 
                     WHERE user_id = ? 
                     ORDER BY transaction_date DESC, transaction_time DESC 
                     LIMIT 11";
$transaction_stmt = mysqli_prepare($conn, $transaction_query);
mysqli_stmt_bind_param($transaction_stmt, "i", $user_id);
mysqli_stmt_execute($transaction_stmt);
$transaction_result = mysqli_stmt_get_result($transaction_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Customer Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Carter+One&family=Gugi&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Newsreader:ital,opsz,wght@0,6..72,200;1,6..72,200&family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Winky+Sans:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        --bronze: #cd7f32;
        --silver: #c0c0c0;
        --gold: #ffd700;
        --platinum: #e5e4e2; /* Added platinum color */
        --diamond: #b9f2ff;
        --ethereal: #9d4edd; /* Added ethereal color */
        
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
        background: radial-gradient(circle, var(--accent-gold) 0%, transparent 70%);
        top: -250px;
        left: -250px;
        animation: float 25s ease-in-out infinite;
    }
    
    .bg-decoration-2 {
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, var(--accent-green) 0%, transparent 70%);
        bottom: -150px;
        right: -150px;
        animation: float 20s ease-in-out infinite reverse;
    }
    
    .bg-decoration-3 {
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, var(--accent-bronze) 0%, transparent 70%);
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

    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, var(--panel-cream) 0%, #E8DFC8 100%);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(106, 127, 70, 0.3);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.8s ease forwards 1.2s;
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
        transition: color 0.3s ease;
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
        transition: color 0.3s ease;
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
        color: rgba(106, 127, 70, 0.1);
        z-index: 1;
    }

    body.dark-mode .welcome-icon {
        color: rgba(106, 127, 70, 0.05);
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .action-card {
        background-color: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        color: var(--text-dark);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        opacity: 0;
        transform: translateY(20px);
    }

    .action-card:nth-child(1) { animation: fadeInUp 0.8s ease forwards 1.3s; }
    .action-card:nth-child(2) { animation: fadeInUp 0.8s ease forwards 1.4s; }
    .action-card:nth-child(3) { animation: fadeInUp 0.8s ease forwards 1.5s; }
    .action-card:nth-child(4) { animation: fadeInUp 0.8s ease forwards 1.6s; }

    body.dark-mode .action-card {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
        color: var(--dark-text-primary);
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, var(--sales-orange), var(--icon-green));
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    body.dark-mode .action-card:hover {
        box-shadow: 0 10px 25px var(--dark-shadow);
    }

    .action-icon {
        font-size: 30px;
        color: var(--icon-green);
        margin-bottom: 20px;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background-color: rgba(106, 127, 70, 0.1);
        transition: all 0.3s ease;
    }

    body.dark-mode .action-icon {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .action-card:hover .action-icon {
        transform: rotate(10deg) scale(1.1);
        background-color: rgba(106, 127, 70, 0.2);
    }

    body.dark-mode .action-card:hover .action-icon {
        background-color: rgba(106, 127, 70, 0.3);
    }

    .action-title {
        font-weight: 600;
        font-size: 17px;
        margin-bottom: 10px;
        letter-spacing: 0.3px;
    }

    .action-desc {
        font-size: 14px;
        color: var(--text-dark);
        opacity: 0.8;
        line-height: 1.4;
        transition: color 0.3s ease;
    }

    body.dark-mode .action-desc {
        color: var(--dark-text-secondary);
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
        font-size: 18px;
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

    /* Added trend styles */
    .trend-up {
        color: #28a745;
    }

    .trend-down {
        color: #dc3545;
    }

    .trend-neutral {
        color: #6c757d;
    }

    /* Loyalty Program */
    .loyalty-card {
        display: flex;
        align-items: center;
        gap: 25px;
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.05) 0%, rgba(242, 234, 211, 0.5) 100%);
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        border: 1px solid rgba(106, 127, 70, 0.1);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    body.dark-mode .loyalty-card {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.1) 0%, rgba(45, 45, 45, 0.5) 100%);
        border-color: var(--dark-border);
    }

    .loyalty-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: radial-gradient(circle, rgba(106,127,70,0.05) 0%, rgba(106,127,70,0) 70%);
        z-index: 1;
    }

    .loyalty-badge {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        flex-shrink: 0;
        transition: all 0.3s ease;
        z-index: 2;
    }

    body.dark-mode .loyalty-badge {
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .bronze-badge {
        background: linear-gradient(135deg, var(--bronze) 0%, #e6b17e 100%);
        color: white;
    }

    .silver-badge {
        background: linear-gradient(135deg, var(--silver) 0%, #e6e6e6 100%);
        color: #333;
    }

    .gold-badge {
        background: linear-gradient(135deg, var(--gold) 0%, #fff3b0 100%);
        color: #333;
    }

    /* Added platinum badge styles */
    .platinum-badge {
        background: linear-gradient(135deg, var(--platinum) 0%, #d4d3d0 100%);
        color: #333;
    }

    .diamond-badge {
        background: linear-gradient(135deg, var(--diamond) 0%, #a8e6f0 100%);
        color: #333;
    }

    /* Added ethereal badge styles */
    .ethereal-badge {
        background: linear-gradient(135deg, var(--ethereal) 0%, #c77dff 100%);
        color: white;
    }

    .loyalty-badge:hover {
        transform: rotate(15deg) scale(1.1);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    body.dark-mode .loyalty-badge:hover {
        box-shadow: 0 8px 20px var(--dark-shadow);
    }

    .progress-container {
        flex-grow: 1;
        z-index: 2;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    body.dark-mode .progress-label {
        color: var(--dark-text-primary);
    }
 

    .progress-bar {
        height: 10px;
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    body.dark-mode .progress-bar {
        background-color: var(--dark-bg-tertiary);
    }

    .progress-fill {
        height: 100%;
        border-radius: 5px;
        position: relative;
        animation: progressAnimation 1.5s ease-in-out;
    }

    .bronze-fill {
        background: linear-gradient(90deg, var(--bronze) 0%, #e6b17e 100%);
    }

    .silver-fill {
        background: linear-gradient(90deg, var(--silver) 0%, #e6e6e6 100%);
    }

    .gold-fill {
        background: linear-gradient(90deg, var(--gold) 0%, #fff3b0 100%);
    }

    /* Added platinum fill styles */
    .platinum-fill {
        background: linear-gradient(90deg, var(--platinum) 0%, #d4d3d0 100%);
    }

    /* Added diamond fill styles */
    .diamond-fill {
        background: linear-gradient(90deg, var(--diamond) 0%, #a8e6f0 100%);
    }

    /* Added ethereal fill styles */
    .ethereal-fill {
        background: linear-gradient(90deg, var(--ethereal) 0%, #c77dff 100%);
    }

    @keyframes progressAnimation {
        from { width: 0; }
        to { width: var(--progress-width); }
    }

    /* Benefits Grid */
    .benefits-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .benefits-list {
        list-style: none;
    }

    .benefits-list li {
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 10px 0;
        border-bottom: 1px dashed rgba(0,0,0,0.1);
        transition: border-color 0.3s ease;
    }

    body.dark-mode .benefits-list li {
        border-bottom-color: var(--dark-border);
    }

    .benefits-list i {
        color: var(--icon-green);
        font-size: 20px;
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        background-color: rgba(106, 127, 70, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    body.dark-mode .benefits-list i {
        background-color: rgba(106, 127, 70, 0.2);
    }

    /* Reward Card */
    .reward-card {
        background-color: rgba(106, 127, 70, 0.05);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border: 1px dashed rgba(106, 127, 70, 0.3);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    body.dark-mode .reward-card {
        background-color: rgba(106, 127, 70, 0.1);
        border-color: var(--dark-border);
    }

    .reward-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        z-index: 1;
    }

    .reward-card p {
        font-size: 15px;
        margin-bottom: 15px;
        position: relative;
        z-index: 2;
        line-height: 1.5;
        transition: color 0.3s ease;
    }

    body.dark-mode .reward-card p {
        color: var(--dark-text-primary);
    }

    .progress-mini {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        z-index: 2;
    }

    .progress-mini-bar {
        flex-grow: 1;
        height: 8px;
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    body.dark-mode .progress-mini-bar {
        background-color: var(--dark-bg-tertiary);
    }

    .progress-mini-fill {
        height: 100%;
        border-radius: 4px;
    }

    /* Buttons */
    .btn {
        display: inline-block;
        padding: 12px 25px;
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

    /* Transactions */
    .transaction-list {
        display: grid;
        gap: 15px;
    }

    .transaction-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 20px;
        border-radius: 12px;
        transition: all 0.3s ease;
        background-color: white;
        box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
    }

    body.dark-mode .transaction-item {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 3px 10px var(--dark-shadow);
        border-color: var(--dark-border);
    }

    .transaction-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        border-color: rgba(106, 127, 70, 0.3);
    }

    body.dark-mode .transaction-item:hover {
        box-shadow: 0 8px 20px var(--dark-shadow);
    }

    .transaction-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .transaction-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background-color: rgba(106, 127, 70, 0.1);
        color: var(--icon-green);
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    body.dark-mode .transaction-icon {
        background-color: rgba(106, 127, 70, 0.2);
    }

    .transaction-details h4 {
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 15px;
        transition: color 0.3s ease;
    }

    body.dark-mode .transaction-details h4 {
        color: var(--dark-text-primary);
    }

    .transaction-details p {
        font-size: 13px;
        color: var(--text-dark);
        opacity: 0.7;
        transition: color 0.3s ease;
    }

    body.dark-mode .transaction-details p {
        color: var(--dark-text-secondary);
    }

    .transaction-amount {
        font-weight: 700;
        font-size: 16px;
        color: var(--icon-green);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-dark);
        opacity: 0.7;
        transition: color 0.3s ease;
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

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        animation: fadeIn 0.3s;
    }

    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 30px;
        border-radius: 15px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        animation: slideDown 0.3s;
        transition: all 0.3s ease;
    }

    body.dark-mode .modal-content {
        background-color: var(--dark-bg-secondary);
        box-shadow: 0 10px 30px var(--dark-shadow);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideDown {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
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

    .modal h3 {
        color: var(--icon-green);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 22px;
        font-weight: 700;
    }

    /* Calculator Styles */
    .calculator-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-weight: 600;
        color: var(--text-dark);
        font-size: 14px;
        transition: color 0.3s ease;
    }

    body.dark-mode .form-group label {
        color: var(--dark-text-primary);
    }

    .form-group select, .form-group input {
        padding: 14px 15px;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        font-size: 15px;
        background-color: white;
        transition: all 0.3s ease;
        color: var(--text-dark);
    }

    body.dark-mode .form-group select, 
    body.dark-mode .form-group input {
        background-color: var(--dark-bg-tertiary);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .form-group select:focus, .form-group input:focus {
        outline: none;
        border-color: var(--icon-green);
        box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
    }

    .result-container {
        margin-top: 20px;
        text-align: center;
        padding: 25px;
        background-color: rgba(106, 127, 70, 0.05);
        border-radius: 10px;
        border: 1px dashed rgba(106, 127, 70, 0.3);
        transition: all 0.3s ease;
    }

    body.dark-mode .result-container {
        background-color: rgba(106, 127, 70, 0.1);
        border-color: var(--dark-border);
    }

    #calculated-result {
        font-size: 32px;
        font-weight: 700;
        color: var(--icon-green);
        margin-top: 10px;
    }

    /* QR Code Styles */
    .qr-code-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        padding: 20px 0;
    }

    .qr-code-image {
        width: 220px;
        height: 220px;
        border: 1px solid rgba(0,0,0,0.1);
        padding: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }

    body.dark-mode .qr-code-image {
        border-color: var(--dark-border);
        background: var(--dark-bg-tertiary);
        box-shadow: 0 3px 10px var(--dark-shadow);
    }

    /* Improved referral link container design */
    .referral-link-container {
        display: flex;
        margin: 20px 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 100%;
        border: 2px solid rgba(106, 127, 70, 0.1);
        transition: all 0.3s ease;
    }

    body.dark-mode .referral-link-container {
        border-color: var(--dark-border);
        box-shadow: 0 4px 15px var(--dark-shadow);
    }

    #referral-code, #referral-link {
        flex-grow: 1;
        padding: 15px 20px;
        border: none;
        font-size: 14px;
        background-color: #f8f9fa;
        font-family: 'Courier New', monospace;
        font-weight: 500;
        color: var(--text-dark);
        transition: all 0.3s ease;
    }

    body.dark-mode #referral-code,
    body.dark-mode #referral-link {
        background-color: var(--dark-bg-tertiary);
        color: var(--dark-text-primary);
    }

    .btn-copy {
        background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
        color: white;
        border: none;
        padding: 0 25px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        min-width: 100px;
    }

    .btn-copy:hover {
        background: linear-gradient(135deg, var(--stock-green) 0%, var(--icon-green) 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(106, 127, 70, 0.3);
    }

    .referral-info {
        font-size: 13px;
        color: var(--text-dark);
        opacity: 0.7;
        margin-top: 15px;
        text-align: center;
        padding: 15px;
        background-color: rgba(106, 127, 70, 0.05);
        border-radius: 8px;
        border: 1px dashed rgba(106, 127, 70, 0.2);
        transition: all 0.3s ease;
    }

    body.dark-mode .referral-info {
        background-color: rgba(106, 127, 70, 0.1);
        border-color: var(--dark-border);
        color: var(--dark-text-secondary);
    }

    .referral-info i {
        margin-right: 8px;
        color: var(--icon-green);
    }

    /* Improved referral content styling */
    .referral-content {
        text-align: center;
    }

    .referral-content > p:first-of-type {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.05) 0%, rgba(242, 234, 211, 0.3) 100%);
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        border: 1px solid rgba(106, 127, 70, 0.1);
        font-size: 15px;
        line-height: 1.6;
        transition: all 0.3s ease;
    }

    body.dark-mode .referral-content > p:first-of-type {
        background: linear-gradient(135deg, rgba(106, 127, 70, 0.1) 0%, rgba(45, 45, 45, 0.3) 100%);
        border-color: var(--dark-border);
        color: var(--dark-text-primary);
    }

    .referral-content > p:nth-of-type(2) {
        font-weight: 600;
        color: var(--text-dark);
        margin: 25px 0 15px 0;
        font-size: 16px;
        transition: color 0.3s ease;
    }

    body.dark-mode .referral-content > p:nth-of-type(2) {
        color: var(--dark-text-primary);
    }

    .social-share {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }

    .social-btn {
        flex: 1;
        padding: 15px 12px;
        border: none;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s;
        font-size: 14px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .social-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .social-btn.facebook {
        background: linear-gradient(135deg, #3b5998 0%, #4c70ba 100%);
    }

    .social-btn.whatsapp {
        background: linear-gradient(135deg, #25D366 0%, #2ecc71 100%);
    }

    .social-btn.email {
        background: linear-gradient(135deg, var(--sales-orange) 0%, #6A7F46 100%);
    }

    /* Referral Section */
    .referral-section {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }

    .referral-card {
        flex: 1;
        background-color: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.8s ease forwards 1.5s;
    }

    body.dark-mode .referral-card {
        background-color: var(--dark-bg-secondary);
        border-color: var(--dark-border);
        box-shadow: 0 5px 15px var(--dark-shadow);
    }

    .referral-card h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }

    body.dark-mode .referral-card h3 {
        color: var(--dark-text-primary);
    }

    .referral-card h3 i {
        color: var(--icon-green);
    }

    .referral-card p {
        font-size: 14px;
        color: var(--text-dark);
        opacity: 0.8;
        margin-bottom: 15px;
        transition: color 0.3s ease;
    }

    body.dark-mode .referral-card p {
        color: var(--dark-text-secondary);
    }

    /* Tier Info */
    .tier-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .tier-name {
        font-weight: 600;
        text-transform: capitalize;
        transition: color 0.3s ease;
    }

    body.dark-mode .tier-name {
        color: var(--dark-text-primary);
    }

    .tier-points {
        font-weight: 500;
        color: var(--text-dark);
        opacity: 0.7;
        transition: color 0.3s ease;
    }

    body.dark-mode .tier-points {
        color: var(--dark-text-secondary);
    }

    /* Profile Picture Modal Styles */
    .profile-modal {
        display: none;
        position: fixed;
        z-index: 1001;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        animation: fadeIn 0.3s;
    }

    .profile-modal-content {
        position: relative;
        margin: 5% auto;
        width: 90%;
        max-width: 500px;
        text-align: center;
        animation: slideDown 0.3s;
    }

    .profile-modal-image {
        max-width: 100%;
        max-height: 80vh;
        border-radius: 10px;
        box-shadow: 0 5px 30px rgba(0,0,0,0.5);
    }

    .profile-modal-close {
        position: absolute;
        top: -40px;
        right: 0;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
    }

    .profile-modal-close:hover {
        color: var(--icon-green);
        transform: scale(1.1);
    }

    .profile-modal-name {
        color: white;
        margin-top: 20px;
        font-size: 24px;
        font-weight: 600;
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
            display: block;
        }
    }

    @media (max-width: 768px) {
        .quick-actions {
            grid-template-columns: 1fr 1fr;
        }
        
        .benefits-grid {
            grid-template-columns: 1fr;
        }
        
        .loyalty-card {
            flex-direction: column;
            text-align: center;
        }

        .referral-section {
            flex-direction: column;
        }
    }

    @media (max-width: 576px) {
        .quick-actions {
            grid-template-columns: 1fr;
        }
        
        .dashboard-card {
            padding: 20px;
        }

        .social-share {
            flex-direction: column;
        }
        
        .modal-content {
            padding: 20px;
        }
    }

    /* Animations */
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    .action-card:hover .action-icon {
        animation: float 1.5s ease-in-out infinite;
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

    @media (max-width: 992px) {
        .mobile-menu-toggle {
            display: flex;
        }
    }

    /* Fix for highest tier message in dark mode */
    .highest-tier-message {
        color: var(--text-dark);
        transition: color 0.3s ease;
    }

    body.dark-mode .highest-tier-message {
        color: var(--dark-text-primary) !important;
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
            <div class="user-avatar" id="profilePicture">
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
            <li><a href="#" class="active"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="transaction.php"><i class="fas fa-history"></i> <?php echo $t['transaction_history']; ?></a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> <?php echo $t['schedule_pickup']; ?></a></li>
            <li><a href="prices.php"><i class="fas fa-coins"></i> <?php echo $t['current_prices']; ?></a></li>
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
            <h1 class="page-title"><?php echo $t['dashboard']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
                <!-- Notification bell removed as requested -->
            </div>
        </div>
        
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2><?php echo $t['welcome_back']; ?>, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
                <p><?php echo $t['ready_to_turn_scrap']; ?></p>
                <a href="schedule.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 10px;">
                    <i class="fas fa-plus"></i> <?php echo $t['new_transaction']; ?>
                </a>
            </div>
            <div class="welcome-icon">
                <i class="fas fa-recycle"></i>
            </div>
        </div>

        
        <div class="referral-section">
            <div class="referral-card">
                <h3><i class="fas fa-qrcode"></i> <?php echo $t['your_referral_qr']; ?></h3>
                <div style="text-align: center;">
                    <?php 
                    $qr_code_data = 'JUNKPRO-REF-' . $user_id . '-' . $referral_code;
                    ?>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($qr_code_data); ?>" 
                         alt="Referral QR Code" style="width: 150px; height: 150px; margin: 0 auto;">
                    <p><?php echo $t['share_qr_earn']; ?></p>
                </div>
            </div>
            
            <div class="referral-card">
                <h3><i class="fas fa-user-plus"></i> <?php echo $t['your_referral_code']; ?></h3>
                <p><?php echo $t['share_code_earn']; ?></p>
                <div class="referral-link-container">
                    <input type="text" id="referral-code" value="<?php echo htmlspecialchars($referral_code); ?>" readonly>
                    <button class="btn-copy" onclick="copyReferralCode()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <p style="margin-top: 40px; text-align: center; font-size: 13px; color: var(--text-dark); opacity: 0.7;">
                    <i class="fas fa-info-circle"></i> <?php echo $t['earn_points_referral']; ?>
                </p>
            </div>
        </div>
        
        <div class="quick-actions">
            <a href="schedule.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3 class="action-title"><?php echo $t['request_pickup']; ?></h3>
                <p class="action-desc"><?php echo $t['schedule_collection']; ?></p>
            </a>
            
            <div class="action-card" onclick="document.getElementById('priceCalculatorModal').style.display='block'">
                <div class="action-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <h3 class="action-title"><?php echo $t['price_calculator']; ?></h3>
                <p class="action-desc"><?php echo $t['estimate_earnings']; ?></p>
            </div>
            
            <div class="action-card" onclick="document.getElementById('qrCodeModal').style.display='block'">
                <div class="action-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h3 class="action-title"><?php echo $t['scan_qr_code']; ?></h3>
                <p class="action-desc"><?php echo $t['quick_checkin']; ?></p>
            </div>
            
            <div class="action-card" onclick="document.getElementById('referFriendModal').style.display='block'">
                <div class="action-icon">
                    <i class="fas fa-share-alt"></i>
                </div>
                <h3 class="action-title"><?php echo $t['refer_friend']; ?></h3>
                <p class="action-desc"><?php echo $t['earn_bonus_points']; ?></p>
            </div>
        </div>
        
        
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-coins"></i> <?php echo $t['todays_scrap_prices']; ?></h3>
                <span style="color: var(--text-dark); opacity: 0.7; font-size: 14px;">
                    <i class="fas fa-sync-alt"></i> <?php echo $t['updated']; ?>: <?php echo date('g:i A'); ?>
                </span>
            </div>
            <div style="overflow-x: auto;">
                <table class="price-table">
                    <thead>
                        <tr>
                            <th><?php echo $t['material']; ?></th>
                            <th><?php echo $t['price_per_kg']; ?></th>
                            <th><?php echo $t['trend']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($materials_result) > 0): 
                            while ($material = mysqli_fetch_assoc($materials_result)): 
                                $trend_icon = '';
                                $trend_class = '';
                                $trend_text = '';
                                
                                switch($material['trend_direction']) {
                                    case 'up':
                                        $trend_icon = 'fas fa-arrow-up';
                                        $trend_class = 'trend-up';
                                        $trend_text = '+₱' . number_format($material['trend_change'], 2);
                                        break;
                                    case 'down':
                                        $trend_icon = 'fas fa-arrow-down';
                                        $trend_class = 'trend-down';
                                        $trend_text = '-₱' . number_format($material['trend_change'], 2);
                                        break;
                                    default:
                                        $trend_icon = 'fas fa-equals';
                                        $trend_class = 'trend-neutral';
                                        $trend_text = '';
                                }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['material_option']); ?></td>
                            <td>₱<?php echo number_format($material['unit_price'], 2); ?></td>
                            <td>
                                <i class="<?php echo $trend_icon; ?> <?php echo $trend_class; ?>"></i> 
                                <?php echo $trend_text; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px;"><?php echo $t['no_price_data']; ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
       
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
           
           <!-- Your Loyalty Rewards Card -->
<div class="dashboard-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-award"></i> <?php echo $t['your_loyalty_rewards']; ?></h3>
    </div>
    
    <div class="loyalty-card">
        <div class="loyalty-badge <?php echo $current_tier; ?>-badge">
            <?php if ($current_tier == 'bronze'): ?>
                <i class="fas fa-medal"></i>
            <?php elseif ($current_tier == 'silver'): ?>
                <i class="fas fa-star"></i>
            <?php elseif ($current_tier == 'gold'): ?>
                <i class="fas fa-trophy"></i>
            <?php elseif ($current_tier == 'platinum'): ?>
                <i class="fas fa-crown"></i>
            <?php elseif ($current_tier == 'diamond'): ?>
                <i class="fas fa-gem"></i>
            <?php elseif ($current_tier == 'ethereal'): ?>
                <i class="fas fa-infinity"></i>
            <?php endif; ?>
        </div>
        <div class="progress-container">
            <div class="tier-info">
                <span class="tier-name" style="color: var(--<?php echo $current_tier; ?>); font-weight: 700; text-transform: capitalize;">
                    <?php echo $current_tier; ?> <?php echo $t['member']; ?>
                </span>
                <span class="tier-points">
                    <?php echo number_format($current_points); ?> <?php echo $t['points']; ?>
                </span>
            </div>
            
            <?php if ($next_tier): ?>
                <div class="progress-label">
                    <span><?php echo $t['progress_to_tier']; ?> <?php echo ucfirst($next_tier); ?> <?php echo $t['tier']; ?></span>
                    <span><?php echo number_format($current_points - $tiers[$current_tier]['min_points']); ?>/<?php echo number_format($tiers[$next_tier]['min_points'] - $tiers[$current_tier]['min_points']); ?> <?php echo $t['points']; ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill <?php echo $current_tier; ?>-fill" style="--progress-width: <?php echo $progress_percentage; ?>%; width: <?php echo $progress_percentage; ?>%"></div>
                </div>
                <p style="margin-top: 10px; font-size: 13px; color: var(--text-dark); opacity: 0.7;">
                    <?php echo $t['need_more_points']; ?> <?php echo number_format($points_to_next_tier); ?> <?php echo $t['more_points_to_reach']; ?> <?php echo ucfirst($next_tier); ?> <?php echo $t['tier']; ?>
                </p>
            <?php else: ?>
                <div class="progress-label">
                    <span>You've reached the highest tier!</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill <?php echo $current_tier; ?>-fill" style="--progress-width: 100%; width: 100%"></div>
                </div>
                <p style="margin-top: 10px; font-size: 13px;" class="highest-tier-message">
                    <?php echo $t['congratulations_highest']; ?> <?php echo number_format($current_points); ?> <?php echo $t['points_exclamation']; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tier Navigation Section -->
    <div class="tier-navigation" style="margin-top: 25px;">
        <?php if ($next_tier): ?>
            <!-- Show next tier info -->
            <div class="tier-card next-tier" style="background: linear-gradient(135deg, rgba(106, 127, 70, 0.05) 0%, rgba(242, 234, 211, 0.3) 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid var(--<?php echo $next_tier; ?>);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div class="loyalty-badge <?php echo $next_tier; ?>-badge" style="width: 50px; height: 50px; font-size: 20px;">
                        <?php if ($next_tier == 'bronze'): ?>
                            <i class="fas fa-medal"></i>
                        <?php elseif ($next_tier == 'silver'): ?>
                            <i class="fas fa-star"></i>
                        <?php elseif ($next_tier == 'gold'): ?>
                            <i class="fas fa-trophy"></i>
                        <?php elseif ($next_tier == 'platinum'): ?>
                            <i class="fas fa-crown"></i>
                        <?php elseif ($next_tier == 'diamond'): ?>
                            <i class="fas fa-gem"></i>
                        <?php elseif ($next_tier == 'ethereal'): ?>
                            <i class="fas fa-infinity"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: var(--<?php echo $next_tier; ?>);">Next Tier: <?php echo ucfirst($next_tier); ?></h4>
                        <p style="margin: 5px 0 0 0; font-size: 14px; color: var(--text-dark); opacity: 0.8;">
                            <?php echo number_format($points_to_next_tier); ?> points needed
                        </p>
                    </div>
                </div>
                <ul class="benefits-list" style="margin-top: 10px;">
                    <?php foreach ($tiers[$next_tier]['benefits'] as $benefit): ?>
                        <li style="padding: 8px 0; border-bottom: 1px dashed rgba(0,0,0,0.1);">
                            <i class="fas fa-arrow-circle-right" style="color: var(--<?php echo $next_tier; ?>);"></i>
                            <?php echo htmlspecialchars($benefit); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Show tier after next if available -->
            <?php 
            $tier_after_next = $tiers[$next_tier]['next_tier'];
            if ($tier_after_next): 
                $points_to_tier_after_next = $tiers[$tier_after_next]['min_points'] - $current_points;
            ?>
                <div class="tier-card future-tier" style="background: rgba(106, 127, 70, 0.03); padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid var(--<?php echo $tier_after_next; ?>); opacity: 0.7;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div class="loyalty-badge <?php echo $tier_after_next; ?>-badge" style="width: 50px; height: 50px; font-size: 20px; opacity: 0.7;">
                            <?php if ($tier_after_next == 'bronze'): ?>
                                <i class="fas fa-medal"></i>
                            <?php elseif ($tier_after_next == 'silver'): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($tier_after_next == 'gold'): ?>
                                <i class="fas fa-trophy"></i>
                            <?php elseif ($tier_after_next == 'platinum'): ?>
                                <i class="fas fa-crown"></i>
                            <?php elseif ($tier_after_next == 'diamond'): ?>
                                <i class="fas fa-gem"></i>
                            <?php elseif ($tier_after_next == 'ethereal'): ?>
                                <i class="fas fa-infinity"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: var(--<?php echo $tier_after_next; ?>);">After <?php echo ucfirst($next_tier); ?>: <?php echo ucfirst($tier_after_next); ?></h4>
                            <p style="margin: 5px 0 0 0; font-size: 14px; color: var(--text-dark); opacity: 0.8;">
                                <?php echo number_format($points_to_tier_after_next); ?> points needed
                            </p>
                        </div>
                    </div>
                    <ul class="benefits-list" style="margin-top: 10px;">
                        <?php foreach ($tiers[$tier_after_next]['benefits'] as $benefit): ?>
                            <li style="padding: 8px 0; border-bottom: 1px dashed rgba(0,0,0,0.1);">
                                <i class="fas fa-arrow-circle-right" style="color: var(--<?php echo $tier_after_next; ?>);"></i>
                                <?php echo htmlspecialchars($benefit); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Show previous tiers for highest tier -->
            <?php 
            $previous_tier = $tiers[$current_tier]['previous_tier'];
            if ($previous_tier): 
                $points_from_previous = $current_points - $tiers[$previous_tier]['min_points'];
            ?>
                <div class="tier-card previous-tier" style="background: linear-gradient(135deg, rgba(106, 127, 70, 0.05) 0%, rgba(242, 234, 211, 0.3) 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid var(--<?php echo $previous_tier; ?>);">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div class="loyalty-badge <?php echo $previous_tier; ?>-badge" style="width: 50px; height: 50px; font-size: 20px;">
                            <?php if ($previous_tier == 'bronze'): ?>
                                <i class="fas fa-medal"></i>
                            <?php elseif ($previous_tier == 'silver'): ?>
                                <i class="fas fa-star"></i>
                            <?php elseif ($previous_tier == 'gold'): ?>
                                <i class="fas fa-trophy"></i>
                            <?php elseif ($previous_tier == 'platinum'): ?>
                                <i class="fas fa-crown"></i>
                            <?php elseif ($previous_tier == 'diamond'): ?>
                                <i class="fas fa-gem"></i>
                            <?php elseif ($previous_tier == 'ethereal'): ?>
                                <i class="fas fa-infinity"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: var(--<?php echo $previous_tier; ?>);">Previous Tier: <?php echo ucfirst($previous_tier); ?></h4>
                            <p style="margin: 5px 0 0 0; font-size: 14px; color: var(--text-dark); opacity: 0.8;">
                                You earned <?php echo number_format($points_from_previous); ?> points in this tier
                            </p>
                        </div>
                    </div>
                    <ul class="benefits-list" style="margin-top: 10px;">
                        <?php foreach ($tiers[$previous_tier]['benefits'] as $benefit): ?>
                            <li style="padding: 8px 0; border-bottom: 1px dashed rgba(0,0,0,0.1);">
                                <i class="fas fa-check-circle" style="color: var(--<?php echo $previous_tier; ?>);"></i>
                                <?php echo htmlspecialchars($benefit); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Show tier before previous if available -->
                <?php 
                $tier_before_previous = $tiers[$previous_tier]['previous_tier'];
                if ($tier_before_previous): 
                    $points_from_before_previous = $tiers[$previous_tier]['min_points'] - $tiers[$tier_before_previous]['min_points'];
                ?>
                    <div class="tier-card past-tier" style="background: rgba(106, 127, 70, 0.03); padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid var(--<?php echo $tier_before_previous; ?>); opacity: 0.7;">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                            <div class="loyalty-badge <?php echo $tier_before_previous; ?>-badge" style="width: 50px; height: 50px; font-size: 20px; opacity: 0.7;">
                                <?php if ($tier_before_previous == 'bronze'): ?>
                                    <i class="fas fa-medal"></i>
                                <?php elseif ($tier_before_previous == 'silver'): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($tier_before_previous == 'gold'): ?>
                                    <i class="fas fa-trophy"></i>
                                <?php elseif ($tier_before_previous == 'platinum'): ?>
                                    <i class="fas fa-crown"></i>
                                <?php elseif ($tier_before_previous == 'diamond'): ?>
                                    <i class="fas fa-gem"></i>
                                <?php elseif ($tier_before_previous == 'ethereal'): ?>
                                    <i class="fas fa-infinity"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 style="margin: 0; color: var(--<?php echo $tier_before_previous; ?>);">Before <?php echo ucfirst($previous_tier); ?>: <?php echo ucfirst($tier_before_previous); ?></h4>
                                <p style="margin: 5px 0 0 0; font-size: 14px; color: var(--text-dark); opacity: 0.8;">
                                    You earned <?php echo number_format($points_from_before_previous); ?> points in this tier
                                </p>
                            </div>
                        </div>
                        <ul class="benefits-list" style="margin-top: 10px;">
                            <?php foreach ($tiers[$tier_before_previous]['benefits'] as $benefit): ?>
                                <li style="padding: 8px 0; border-bottom: 1px dashed rgba(0,0,0,0.1);">
                                    <i class="fas fa-check-circle" style="color: var(--<?php echo $tier_before_previous; ?>);"></i>
                                    <?php echo htmlspecialchars($benefit); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
          
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> <?php echo $t['recent_transactions']; ?></h3>
                    <a href="transaction.php" class="view-all">
                        <?php echo $t['view_all']; ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="transaction-list">
                    <?php if (mysqli_num_rows($transaction_result) > 0): ?>
                        <?php while ($transaction = mysqli_fetch_assoc($transaction_result)): ?>
                            <?php
                            // Determine icon and color based on transaction type
                            $icon = '';
                            $color = 'var(--icon-green)';
                            switch($transaction['transaction_type']) {
                                case 'Pickup':
                                    $icon = 'fa-truck-loading';
                                    break;
                                case 'Walk-in':
                                    $icon = 'fa-coins';
                                    break;
                                case 'Loyalty':
                                    $icon = 'fa-award';
                                    $color = 'var(--sales-orange)';
                                    break;
                                default:
                                    $icon = 'fa-exchange-alt';
                            }
                            ?>
                            <div class="transaction-item">
                                <div class="transaction-info">
                                    <div class="transaction-icon" style="color: <?php echo $color; ?>">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="transaction-details">
                                        <h4><?php echo htmlspecialchars($transaction['name']); ?></h4>
                                        <p>
                                            <?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?> • 
                                            <?php echo htmlspecialchars($transaction['item_details']); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="transaction-amount" style="color: <?php echo $color; ?>">
                                    +₱<?php echo number_format($transaction['amount'], 2); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-info-circle"></i>
                            <p><?php echo $t['no_recent_transactions']; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

   
    <div id="priceCalculatorModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="document.getElementById('priceCalculatorModal').style.display='none'">&times;</span>
            <h3><i class="fas fa-calculator"></i> <?php echo $t['price_calculator']; ?></h3>
            <div class="calculator-form">
                <div class="form-group">
                    <label for="material-type"><?php echo $t['material']; ?></label>
                    <select id="material-type">
                        <?php 
                        // Fetch materials again for the select dropdown, ensuring the pointer is reset
                        mysqli_data_seek($materials_result, 0); // Reset result pointer
                        while ($material = mysqli_fetch_assoc($materials_result)): 
                        ?>
                        <option value="<?php echo htmlspecialchars($material['unit_price']); ?>">
                            <?php echo htmlspecialchars($material['material_option']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="weight"><?php echo $t['weight']; ?> (kg)</label>
                    <input type="number" id="weight" placeholder="Enter weight in kilograms" step="0.01">
                </div>
                <button id="calculate-btn" class="btn btn-primary"><?php echo $t['calculate_value']; ?></button>
                <div class="result-container">
                    <h4><?php echo $t['estimated_value']; ?>:</h4>
                    <div id="calculated-result">₱0.00</div>
                </div>
            </div>
        </div>
    </div>

    
    <div id="qrCodeModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="document.getElementById('qrCodeModal').style.display='none'">&times;</span>
            <h3><i class="fas fa-qrcode"></i> <?php echo $t['your_qr_code']; ?></h3>
            <div class="qr-code-container">
                <?php 
                $qr_code_data = 'JUNKPRO-' . $user_id . '-' . bin2hex(random_bytes(3));
                ?>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($qr_code_data); ?>" 
                     alt="QR Code for <?php echo htmlspecialchars($user_name); ?>" class="qr-code-image">
                <p><?php echo $t['scan_quick_checkin']; ?></p>
                <button class="btn btn-primary" onclick="downloadQRCode()">
                    <i class="fas fa-download"></i> <?php echo $t['download_qr_code']; ?>
                </button>
            </div>
        </div>
    </div>

    
    <div id="referFriendModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="document.getElementById('referFriendModal').style.display='none'">&times;</span>
            <h3><i class="fas fa-share-alt"></i> <?php echo $t['refer_friend']; ?></h3>
            <div class="referral-content">
                <p><?php echo $t['refer_friend_earn']; ?></p>
                <div class="referral-link-container">
                    <input type="text" id="referral-link" value="https://junkvalue.xo.je/Customer-portal/Login/Register.php?ref=<?php echo $referral_code; ?>" readonly>
                    <button class="btn-copy" onclick="copyReferralLink()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <p><?php echo $t['or_share_directly']; ?>:</p>
                <div class="social-share">
                    <button class="social-btn facebook" onclick="shareOnFacebook()">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </button>
                    <button class="social-btn whatsapp" onclick="shareOnWhatsApp()">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </button>
                    <button class="social-btn email" onclick="shareViaEmail()">
                        <i class="fas fa-envelope"></i> Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Picture Modal -->
    <div id="profilePictureModal" class="profile-modal">
        <div class="profile-modal-content">
            <span class="profile-modal-close" onclick="document.getElementById('profilePictureModal').style.display='none'">&times;</span>
            <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture" class="profile-modal-image">
            <?php else: ?>
                <div style="width: 300px; height: 300px; background-color: var(--panel-cream); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 80px; font-weight: bold; color: var(--topbar-brown); margin: 0 auto;">
                    <?php echo $user_initials; ?>
                </div>
            <?php endif; ?>
            <div class="profile-modal-name"><?php echo htmlspecialchars($user_name); ?></div>
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

        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Profile picture modal functionality
        document.getElementById('profilePicture').addEventListener('click', function() {
            document.getElementById('profilePictureModal').style.display = 'block';
        });

        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const calculateBtn = document.getElementById('calculate-btn');
            calculateBtn.addEventListener('click', calculatePrice);
            
            function calculatePrice() {
                const materialSelect = document.getElementById('material-type');
                const pricePerKg = parseFloat(materialSelect.value) || 0;
                const weight = parseFloat(document.getElementById('weight').value) || 0;
                
                const total = (pricePerKg * weight).toFixed(2);
                
                document.getElementById('calculated-result').textContent = `₱${total}`;
            }

            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
                if (event.target.classList.contains('profile-modal')) {
                    event.target.style.display = 'none';
                }
            }
        });
        
        // Download QR Code
        function downloadQRCode() {
            const qrCodeImage = document.querySelector('.qr-code-image');
            const link = document.createElement('a');
            link.href = qrCodeImage.src;
            link.download = 'JunkValue-qrcode.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Copy referral link
        function copyReferralLink() {
            const referralLink = document.getElementById('referral-link');
            referralLink.select();
            document.execCommand('copy');
            
            // Show copied message
            const copyBtn = document.querySelector('#referral-link + .btn-copy');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            
            setTimeout(function() {
                copyBtn.innerHTML = originalText;
            }, 2000);
        }

        // Copy referral code
        function copyReferralCode() {
            const referralCode = document.getElementById('referral-code');
            referralCode.select();
            document.execCommand('copy');
            
            // Show copied message
            const copyBtn = document.querySelector('#referral-code + .btn-copy');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            
            setTimeout(function() {
                copyBtn.innerHTML = originalText;
            }, 2000);
        }

        // Social sharing functions
        function shareOnFacebook() {
            const url = encodeURIComponent(document.getElementById('referral-link').value);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }

        function shareOnWhatsApp() {
            const text = encodeURIComponent("Join me on JunkValue and get bonus points on your first transaction! ");
            const url = encodeURIComponent(document.getElementById('referral-link').value);
            window.open(`https://wa.me/?text=${text}${url}`, '_blank');
        }

        function shareViaEmail() {
            const subject = encodeURIComponent("Join me on JunkValue!");
            const body = encodeURIComponent(`Hi there,\n\nI thought you might be interested in JunkValue. Use my referral link to sign up and get bonus points on your first transaction!\n\n${document.getElementById('referral-link').value}\n\nBest regards,\n<?php echo htmlspecialchars($user_name); ?>`);
            window.open(`mailto:?subject=${subject}&body=${body}`);
        }
    </script>
</body>
</html>
<?php
// Close prepared statements
mysqli_stmt_close($user_stmt);
mysqli_stmt_close($transaction_stmt);
// Close connection
mysqli_close($conn);
?>