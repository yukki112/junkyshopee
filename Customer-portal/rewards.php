<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and include database connection
session_start();
require_once 'db_connection.php';
require_once 'fpdf/fpdf.php'; // Include FPDF library for voucher receipt generation

// Language handling
$language = 'en'; // Default language
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'tl'])) {
    $language = $_GET['lang'];
    $_SESSION['language'] = $language;
} elseif (isset($_SESSION['language'])) {
    $language = $_SESSION['language'];
}

// Language strings - UPDATED WITH MISSING KEYS
$translations = [
    'en' => [
        'dashboard' => 'Dashboard',
        'transaction_history' => 'Transaction History',
        'schedule_pickup' => 'Schedule Pickup',
        'current_prices' => 'Current Prices',
        'loyalty_rewards' => 'Loyalty Rewards',
        'account_settings' => 'Account Settings',
        'logout' => 'Logout',
        'your_points_summary' => 'Your Points Summary',
        'points' => 'points',
        'how_it_works' => 'How It Works',
        'your_current_status' => 'Your Current Status',
        'toward' => 'toward',
        'tier' => 'tier',
        'youve_reached_the_highest_tier' => "You've reached the highest tier! Congratulations!",
        'maximum_level_reached' => 'Maximum Level Reached',
        'on_all_scrap_sales' => 'On all scrap sales',
        'per_month' => 'Worth 50 Pesos',
        'tier_vouchers' => 'Tier Vouchers',
        'generate_exclusive_vouchers' => 'Generate exclusive vouchers for your tier level',
        'requires' => 'Requires',
        'need' => 'Need',
        'more_points' => 'more points',
        'available' => 'Available',
        'generate' => 'Generate',
        'your_vouchers' => 'Your Vouchers',
        'total' => 'total',
        'value' => 'Value',
        'expires' => 'Expires',
        'view_receipt' => 'View Receipt',
        'next_tier_preview' => 'Next Tier Preview',
        'reach' => 'Reach',
        'points_to_unlock' => 'points to unlock',
        'points_needed' => 'points needed',
        'earn_bonus_points' => 'Earn Bonus Points',
        'redeem_your_points' => 'Redeem Your Points',
        'points_history_transaction_log' => 'Points History & Transaction Log',
        'view_all' => 'View All',
        'transaction_type' => 'Transaction Type',
        'all_types' => 'All Types',
        'points_earned' => 'Points Earned',
        'points_redeemed' => 'Points Redeemed',
        'sales' => 'Sales',
        'rewards' => 'Rewards',
        'redemptions' => 'Redemptions',
        'from_date' => 'From Date',
        'to_date' => 'To Date',
        'filter' => 'Filter',
        'clear' => 'Clear',
        'date' => 'Date',
        'transaction_details' => 'Transaction Details',
        'type' => 'Type',
        'balance' => 'Balance',
        'receipt' => 'Receipt',
        'no_transaction_history_found' => 'No transaction history found',
        'processed_by' => 'Processed by',
        'no_receipt' => 'No receipt',
        'previous' => 'Previous',
        'next' => 'Next',
        'voucher_generated_successfully' => 'Voucher Generated Successfully!',
        'voucher_code' => 'Voucher Code',
        'next_steps' => 'Next Steps',
        'print_receipt_present' => 'Print the receipt and present it at the JunkValue counter to redeem your reward!',
        'bring_valid_id' => 'Make sure to bring a valid ID for verification',
        'visit_business_hours' => 'Visit us during business hours for redemption',
        'voucher_valid_7_days' => 'Your voucher is valid for 7 days from today',
        'close' => 'Close',
        'generating_voucher' => 'Generating Voucher...',
        'please_wait_processing' => 'Please wait while we process your request',
        'how_loyalty_rewards_work' => 'How Loyalty Rewards Work',
        'earn_points_description' => 'Earn loyalty points for every scrap transaction you make with JunkValue. The more you sell, the more points you accumulate!',
        'tier_progression' => 'Tier Progression',
        'tier_progression_description' => "As you earn points, you'll advance through different tiers: Bronze → Silver → Gold → Platinum → Diamond → Ethereal. Each tier unlocks better benefits and exclusive vouchers.",
        'generate_vouchers' => 'Generate Vouchers',
        'generate_vouchers_description' => 'Use your points to generate tier-specific vouchers. Each tier offers unique rewards like free pickups, cash vouchers, and premium services.',
        'redeem_at_counter' => 'Redeem at Counter',
        'redeem_at_counter_description' => 'Generated vouchers must be redeemed in person at our junkshop counter. Present your voucher receipt to claim your reward!',
        'voucher_validity' => 'Voucher Validity',
        'voucher_validity_description' => 'All vouchers are valid for 7 days from the date of generation. Make sure to redeem them before they expire!',
        'tier_benefits' => 'Tier Benefits',
        'tier_benefits_description' => 'Higher tiers provide ongoing benefits like bonus percentages on sales, free monthly pickups, and priority processing.',
        'active' => 'Active',
        'expired' => 'Expired',
        'redeemed' => 'Redeemed',
        // ADDED MISSING TRANSLATION KEYS
        'bonus' => 'Bonus',
        'free_pickups' => 'Free Pickups',
        'earn_points' => 'Earn Points',
        'youre' => "You're",
        'not_enough_points' => 'Not enough points',
        'redeem' => 'Redeem'
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'transaction_history' => 'Kasaysayan ng Transaksyon',
        'schedule_pickup' => 'I-skedyul ang Pickup',
        'current_prices' => 'Kasalukuyang Mga Presyo',
        'loyalty_rewards' => 'Mga Gantimpala ng Loyalty',
        'account_settings' => 'Mga Setting ng Account',
        'logout' => 'Logout',
        'your_points_summary' => 'Iyong Buod ng Mga Puntos',
        'points' => 'mga puntos',
        'how_it_works' => 'Paano Ito Gumagana',
        'your_current_status' => 'Iyong Kasalukuyang Katayuan',
        'toward' => 'patungo sa',
        'tier' => 'tier',
        'youve_reached_the_highest_tier' => 'Naabot mo na ang pinakamataas na tier! Binabati kita!',
        'maximum_level_reached' => 'Naabot na ang Pinakamataas na Antas',
        'on_all_scrap_sales' => 'Sa lahat ng scrap sales',
        'per_month' => 'Bawat buwan',
        'tier_vouchers' => 'Mga Voucher ng Tier',
        'generate_exclusive_vouchers' => 'Gumawa ng mga eksklusibong voucher para sa iyong antas ng tier',
        'requires' => 'Nangangailangan ng',
        'need' => 'Kailangan ng',
        'more_points' => 'pang puntos',
        'available' => 'Available',
        'generate' => 'Bumuo',
        'your_vouchers' => 'Iyong Mga Voucher',
        'total' => 'kabuuan',
        'value' => 'Halaga',
        'expires' => 'Mag-e-expire',
        'view_receipt' => 'Tingnan ang Resibo',
        'next_tier_preview' => 'Preview ng Susunod na Tier',
        'reach' => 'Abot',
        'points_to_unlock' => 'mga puntos para i-unlock',
        'points_needed' => 'mga puntos na kailangan',
        'earn_bonus_points' => 'Kumita ng Mga Bonus Points',
        'redeem_your_points' => 'I-redeem ang Iyong Mga Puntos',
        'points_history_transaction_log' => 'Kasaysayan ng Puntos at Log ng Transaksyon',
        'view_all' => 'Tingnan Lahat',
        'transaction_type' => 'Uri ng Transaksyon',
        'all_types' => 'Lahat ng Uri',
        'points_earned' => 'Mga Puntos na Nakuha',
        'points_redeemed' => 'Mga Puntos na Nai-redeem',
        'sales' => 'Mga Benta',
        'rewards' => 'Mga Gantimpala',
        'redemptions' => 'Mga Pag-redeem',
        'from_date' => 'Mula Petsa',
        'to_date' => 'Hanggang Petsa',
        'filter' => 'Salain',
        'clear' => 'I-clear',
        'date' => 'Petsa',
        'transaction_details' => 'Mga Detalye ng Transaksyon',
        'type' => 'Uri',
        'balance' => 'Balanse',
        'receipt' => 'Resibo',
        'no_transaction_history_found' => 'Walang nakitang kasaysayan ng transaksyon',
        'processed_by' => 'Prosesado ni',
        'no_receipt' => 'Walang resibo',
        'previous' => 'Nakaraan',
        'next' => 'Susunod',
        'voucher_generated_successfully' => 'Matagumpay na Nagawa ang Voucher!',
        'voucher_code' => 'Code ng Voucher',
        'next_steps' => 'Mga Susunod na Hakbang',
        'print_receipt_present' => 'I-print ang resibo at ipakita ito sa counter ng JunkValue para makuha ang iyong gantimpala!',
        'bring_valid_id' => 'Siguraduhing magdala ng wastong ID para sa pagpapatunay',
        'visit_business_hours' => 'Bisitahin kami sa oras ng negosyo para sa pag-redeem',
        'voucher_valid_7_days' => 'Ang iyong voucher ay may bisa sa 7 araw mula ngayon',
        'close' => 'Isara',
        'generating_voucher' => 'Gumagawa ng Voucher...',
        'please_wait_processing' => 'Mangyaring maghintay habang pinoproseso namin ang iyong kahilingan',
        'how_loyalty_rewards_work' => 'Paano Gumagana ang Mga Gantimpala sa Loyalty',
        'earn_points_description' => 'Kumita ng mga loyalty point para sa bawat transaksyon ng scrap na ginagawa mo sa JunkValue. Ang mas marami kang ibenta, mas maraming puntos ang naipon mo!',
        'tier_progression' => 'Pag-unlad ng Tier',
        'tier_progression_description' => 'Habang kumikita ka ng mga puntos, magpapatuloy ka sa iba\'t ibang tier: Bronze → Silver → Gold → Platinum → Diamond → Ethereal. Ang bawat tier ay nagbubukas ng mas mahusay na benepisyo at eksklusibong mga voucher.',
        'generate_vouchers' => 'Bumuo ng Mga Voucher',
        'generate_vouchers_description' => 'Gamitin ang iyong mga puntos upang bumuo ng mga voucher na partikular sa tier. Ang bawat tier ay nag-aalok ng mga natatanging gantimpala tulad ng mga libreng pickup, cash voucher, at premium na serbisyo.',
        'redeem_at_counter' => 'I-redeem sa Counter',
        'redeem_at_counter_description' => 'Ang mga nabuong voucher ay dapat i-redeem nang personal sa aming junkshop counter. Ipakita ang iyong resibo ng voucher upang makuha ang iyong gantimpala!',
        'voucher_validity' => 'Pagiging Wasto ng Voucher',
        'voucher_validity_description' => 'Ang lahat ng mga voucher ay may bisa sa 7 araw mula sa petsa ng pagbuo. Siguraduhing i-redeem ang mga ito bago mag-expire!',
        'tier_benefits' => 'Mga Benepisyo ng Tier',
        'tier_benefits_description' => 'Ang mas mataas na tier ay nagbibigay ng patuloy na benepisyo tulad ng mga bonus percentage sa mga benta, libreng buwanang pickup, at priyoridad na pagproseso.',
        'active' => 'Aktibo',
        'expired' => 'Expired',
        'redeemed' => 'Nai-redeem',
        // ADDED MISSING TRANSLATION KEYS
        'bonus' => 'Bonus',
        'free_pickups' => 'Libreng Pickup',
        'earn_points' => 'Kumita ng Puntos',
        'youre' => 'Ikaw ay',
        'not_enough_points' => 'Hindi sapat na puntos',
        'redeem' => 'I-redeem'
    ]
];

$t = $translations[$language];

// Add CSRF token generation at the top after session_start()
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user ID
$user_id = $_SESSION['user_id'];

// Get user info for header (updated to include profile_image)
$user_query = "SELECT first_name, last_name, profile_image FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);
$user_name = $user['first_name'] . ' ' . $user['last_name'];
$user_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

// Get user loyalty data from users table instead
$user_loyalty_query = "SELECT loyalty_points, loyalty_tier FROM users WHERE id = ?";
$user_loyalty_stmt = mysqli_prepare($conn, $user_loyalty_query);
mysqli_stmt_bind_param($user_loyalty_stmt, "i", $user_id);
mysqli_stmt_execute($user_loyalty_stmt);
$user_loyalty_result = mysqli_stmt_get_result($user_loyalty_stmt);
$user_loyalty_data = mysqli_fetch_assoc($user_loyalty_result);

// Define tier thresholds and benefits
$tier_config = [
    'bronze' => ['min_points' => 0, 'bonus_percentage' => 0, 'free_pickups' => 0, 'next' => 'silver'],
    'silver' => ['min_points' => 1000, 'bonus_percentage' => 3, 'free_pickups' => 1, 'next' => 'gold'],
    'gold' => ['min_points' => 3000, 'bonus_percentage' => 5, 'free_pickups' => 2, 'next' => 'platinum'],
    'platinum' => ['min_points' => 7500, 'bonus_percentage' => 8, 'free_pickups' => 3, 'next' => 'diamond'],
    'diamond' => ['min_points' => 15000, 'bonus_percentage' => 12, 'free_pickups' => 5, 'next' => 'ethereal'],
    'ethereal' => ['min_points' => 30000, 'bonus_percentage' => 20, 'free_pickups' => 10, 'next' => null]
];

// Define tier vouchers
$tier_vouchers = [
    'silver' => [
        'type' => 'Free Pickup',
        'description' => 'One free scrap pickup service',
        'value' => 50.00,
        'points_cost' => 500,
        'icon' => 'fa-truck'
    ],
    'gold' => [
        'type' => 'Cash Voucher',
        'description' => '₱100 cash voucher for scrap sales',
        'value' => 100.00,
        'points_cost' => 800,
        'icon' => 'fa-money-bill-wave'
    ],
    'platinum' => [
        'type' => 'Premium Service',
        'description' => 'Priority processing and 15% bonus',
        'value' => 150.00,
        'points_cost' => 1200,
        'icon' => 'fa-star'
    ],
    'diamond' => [
        'type' => 'VIP Package',
        'description' => 'VIP treatment with 20% bonus and free pickup',
        'value' => 250.00,
        'points_cost' => 2000,
        'icon' => 'fa-gem'
    ],
    'ethereal' => [
        'type' => 'Ultimate Reward',
        'description' => 'Ultimate package with 25% bonus and premium perks',
        'value' => 500.00,
        'points_cost' => 3500,
        'icon' => 'fa-crown'
    ]
];

// Calculate tier progress using data from users table
if ($user_loyalty_data) {
    $current_points = $user_loyalty_data['loyalty_points'];
    $current_tier = strtolower($user_loyalty_data['loyalty_tier']);
    $current_tier_name = ucfirst($current_tier);
    
    // Get current tier config
    $current_tier_config = $tier_config[$current_tier];
    $next_tier_name = $current_tier_config['next'] ? ucfirst($current_tier_config['next']) : 'Max Level';
    $next_min_points = $current_tier_config['next'] ? $tier_config[$current_tier_config['next']]['min_points'] : $current_points;
    $current_min_points = $current_tier_config['min_points'];
    
    if ($current_tier_config['next']) {
        $progress_percentage = ($current_points - $current_min_points) / 
                             ($next_min_points - $current_min_points) * 100;
        $progress_percentage = min(max($progress_percentage, 0), 100);
    } else {
        $progress_percentage = 100; // Max tier reached
    }
    
    $loyalty_data = [
        'bonus_percentage' => $current_tier_config['bonus_percentage'],
        'free_pickups' => $current_tier_config['free_pickups']
    ];
} else {
    // Default values if no loyalty data exists
    $current_points = 0;
    $current_tier = 'bronze';
    $current_tier_name = 'Bronze';
    $next_tier_name = 'Silver';
    $next_min_points = 1000;
    $current_min_points = 0;
    $progress_percentage = 0;
    $loyalty_data = [
        'bonus_percentage' => 0,
        'free_pickups' => 0
    ];
}

function getSystemEmployeeId($conn) {
    // First, try to find an existing system employee
    $check_system_employee = "SELECT id FROM employees WHERE username = 'system' AND is_active = 1 LIMIT 1";
    $result = mysqli_query($conn, $check_system_employee);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['id'];
    }
    
    // If no system employee exists, try to get any active employee
    $check_any_employee = "SELECT id FROM employees WHERE is_active = 1 LIMIT 1";
    $result = mysqli_query($conn, $check_any_employee);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['id'];
    }
    
    // If no employees exist, return null (we'll handle this case)
    return null;
}

// Function to generate voucher code
function generateVoucherCode($tier) {
    return strtoupper($tier) . date('Ymd') . rand(1000, 9999);
}

// Function to generate voucher receipt using FPDF
function generateVoucherReceipt($voucher_code, $user_name, $tier, $voucher_type, $description, $value, $expires_at) {
    date_default_timezone_set('Asia/Manila');
    
    $pdf = new FPDF();
    $pdf->AddPage();

    // Header
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 15, 'JunkValue', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 7, '10 Sto. Nino St. Barangay Commonwealth Quezon city', 0, 1, 'C');
    $pdf->Cell(0, 12, 'Contact: 0947 884 4412', 0, 1, 'C');
    
    // Title
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 15, 'VOUCHER RECEIPT', 0, 1, 'C');
    
    // Voucher details
    $current_datetime = date('M j, Y h:i A');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Voucher Code:', 0, 0);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, $voucher_code, 0, 1);
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Generated:', 0, 0);
    $pdf->Cell(0, 7, $current_datetime, 0, 1);
    $pdf->Cell(40, 7, 'Customer:', 0, 0);
    $pdf->Cell(0, 7, $user_name, 0, 1);
    $pdf->Cell(40, 7, 'Tier Level:', 0, 0);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, strtoupper($tier), 0, 1);
    
    // Voucher info box
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 10, 'VOUCHER DETAILS', 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, $voucher_type, 0, 1, 'C');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 6, $description, 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(220, 53, 69);
    $pdf->Cell(0, 12, 'Value: P' . number_format($value, 2), 0, 1, 'C');
    
    // Expiry info
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'EXPIRES: ' . date('M j, Y h:i A', strtotime($expires_at)), 0, 1, 'C');
    
    // Terms and conditions
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 7, 'TERMS & CONDITIONS:', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, '* This voucher is valid for 7 days from the date of generation', 0, 1);
    $pdf->Cell(0, 5, '* Voucher must be presented during redemption', 0, 1);
    $pdf->Cell(0, 5, '* Non-transferable and cannot be exchanged for cash', 0, 1);
    $pdf->Cell(0, 5, '* Valid only for the specified service or discount', 0, 1);
    $pdf->Cell(0, 5, '* JunkValue reserves the right to verify voucher authenticity', 0, 1);
    
    // Footer
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, 'Thank you for your loyalty to JunkValue!', 0, 1, 'C');
    $pdf->Cell(0, 7, 'Present this voucher to redeem your reward', 0, 1, 'C');
    
    // Save the PDF
    $receipt_dir = 'receipts/vouchers/';
    if (!file_exists($receipt_dir)) {
        mkdir($receipt_dir, 0755, true);
    }
    
    $filename = $receipt_dir . $voucher_code . '.pdf';
    $pdf->Output($filename, 'F');
    
    return $filename;
}

$voucher_generated = false;
$generated_voucher_code = '';
$error_message = ''; // Initialize error message

// Handle voucher generation with proper validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_voucher'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid request. Please try again.";
    }
    // Check for duplicate submission
    elseif (isset($_SESSION['last_voucher_request']) && 
            $_SESSION['last_voucher_request'] === $_POST['csrf_token']) {
        $error_message = "Voucher already generated. Please refresh the page.";
    }
    else {
        $requested_tier = $_POST['tier'];
        
        // Check if user has reached the required tier
        $tier_levels = ['bronze' => 0, 'silver' => 1, 'gold' => 2, 'platinum' => 3, 'diamond' => 4, 'ethereal' => 5];
        $user_tier_level = $tier_levels[$current_tier];
        $requested_tier_level = $tier_levels[$requested_tier];
        
        if ($user_tier_level >= $requested_tier_level && isset($tier_vouchers[$requested_tier])) {
            $voucher_info = $tier_vouchers[$requested_tier];
            
            // Check if user has enough points
            if ($current_points >= $voucher_info['points_cost']) {
                // Generate voucher code
                $voucher_code = generateVoucherCode($requested_tier);
                $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
                
                // Insert voucher into database
                $insert_voucher = "INSERT INTO vouchers (voucher_code, user_id, tier, voucher_type, description, value, points_cost, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $voucher_stmt = mysqli_prepare($conn, $insert_voucher);
                mysqli_stmt_bind_param($voucher_stmt, "sisssdis", $voucher_code, $user_id, $requested_tier, $voucher_info['type'], $voucher_info['description'], $voucher_info['value'], $voucher_info['points_cost'], $expires_at);
                
                if (mysqli_stmt_execute($voucher_stmt)) {
                    // Mark this request as processed
                    $_SESSION['last_voucher_request'] = $_POST['csrf_token'];
                    
                    // Deduct points from user
                    $new_points = $current_points - $voucher_info['points_cost'];
                    $update_points = "UPDATE users SET loyalty_points = ? WHERE id = ?";
                    $points_stmt = mysqli_prepare($conn, $update_points);
                    mysqli_stmt_bind_param($points_stmt, "ii", $new_points, $user_id);
                    mysqli_stmt_execute($points_stmt);
                    
                    // Generate receipt
                    $receipt_path = generateVoucherReceipt($voucher_code, $user_name, $requested_tier, $voucher_info['type'], $voucher_info['description'], $voucher_info['value'], $expires_at);
                    
                    // Update voucher with receipt path
                    $update_receipt = "UPDATE vouchers SET receipt_path = ? WHERE voucher_code = ?";
                    $receipt_stmt = mysqli_prepare($conn, $update_receipt);
                    mysqli_stmt_bind_param($receipt_stmt, "ss", $receipt_path, $voucher_code);
                    mysqli_stmt_execute($receipt_stmt);
                    
                    $system_employee_id = getSystemEmployeeId($conn);
                    
                    // Log the transaction with proper employee handling
                    if ($system_employee_id !== null) {
                        $log_transaction = "INSERT INTO loyalty_point_logs (customer_id, employee_id, points_change, reason, previous_points, new_points, receipt_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $log_stmt = mysqli_prepare($conn, $log_transaction);
                        $points_change = -$voucher_info['points_cost'];
                        $reason = "Voucher generated: " . $voucher_info['type'];
                        mysqli_stmt_bind_param($log_stmt, "iisiiss", $user_id, $system_employee_id, $points_change, $reason, $current_points, $new_points, $receipt_path);
                    } else {
                        // If no employees exist, we need to handle this case - either create a system employee or modify the table structure
                        // For now, let's create a basic system employee
                        $create_system_employee = "INSERT INTO employees (first_name, last_name, username, password_hash, role_id, is_active) VALUES ('System', 'Auto', 'system', '', 1, 1)";
                        if (mysqli_query($conn, $create_system_employee)) {
                            $system_employee_id = mysqli_insert_id($conn);
                            $log_transaction = "INSERT INTO loyalty_point_logs (customer_id, employee_id, points_change, reason, previous_points, new_points, receipt_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $log_stmt = mysqli_prepare($conn, $log_transaction);
                            $points_change = -$voucher_info['points_cost'];
                            $reason = "Voucher generated: " . $voucher_info['type'];
                            mysqli_stmt_bind_param($log_stmt, "iisiiss", $user_id, $system_employee_id, $points_change, $reason, $current_points, $new_points, $receipt_path);
                        } else {
                            // If we can't create a system employee, skip logging for now
                            $error_message = "Voucher generated but transaction logging failed. Please contact support.";
                        }
                    }
                    
                    if (isset($log_stmt)) {
                        mysqli_stmt_execute($log_stmt);
                    }
                    
                    $voucher_generated = true;
                    $generated_voucher_code = $voucher_code;
                    $current_points = $new_points; // Update current points for display
                    
                    // Regenerate CSRF token for next request
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $error_message = "Failed to generate voucher. Please try again.";
                }
            } else {
                $error_message = "You don't have enough points for this voucher.";
            }
        } else {
            $error_message = "You haven't reached the required tier for this voucher.";
        }
    }
}

$vouchers_per_page = 5;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $vouchers_per_page;

// Get total count of user's vouchers
$count_query = "SELECT COUNT(*) as total FROM vouchers WHERE user_id = ?";
$count_stmt = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($count_stmt, "i", $user_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_vouchers = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_vouchers / $vouchers_per_page);

// Get user's vouchers with pagination
$user_vouchers_query = "SELECT * FROM vouchers WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$user_vouchers_stmt = mysqli_prepare($conn, $user_vouchers_query);
mysqli_stmt_bind_param($user_vouchers_stmt, "iii", $user_id, $vouchers_per_page, $offset);
mysqli_stmt_execute($user_vouchers_stmt);
$user_vouchers_result = mysqli_stmt_get_result($user_vouchers_stmt);
$user_vouchers = mysqli_fetch_all($user_vouchers_result, MYSQLI_ASSOC);

// Get active challenges (assuming this table exists, if not, we'll create empty array)
$active_challenges = [];
$challenges_query = "SHOW TABLES LIKE 'loyalty_challenges'";
$table_check = mysqli_query($conn, $challenges_query);
if (mysqli_num_rows($table_check) > 0) {
    $challenges_query = "SELECT c.id, c.challenge_name, c.description, c.target_value, 
                        c.target_metric, c.points_reward, c.start_date, c.end_date,
                        ucp.current_value, ucp.is_completed
                        FROM loyalty_challenges c
                        LEFT JOIN user_challenge_progress ucp ON c.id = ucp.challenge_id AND ucp.user_id = ?
                        WHERE c.is_active = 1 AND c.end_date >= CURDATE()";
    $challenges_stmt = mysqli_prepare($conn, $challenges_query);
    mysqli_stmt_bind_param($challenges_stmt, "i", $user_id);
    mysqli_stmt_execute($challenges_stmt);
    $challenges_result = mysqli_stmt_get_result($challenges_stmt);
    $active_challenges = mysqli_fetch_all($challenges_result, MYSQLI_ASSOC);
}

// Get available rewards (assuming this table exists, if not, we'll create empty array)
$available_rewards = [];
$rewards_query = "SHOW TABLES LIKE 'rewards'";
$table_check = mysqli_query($conn, $rewards_query);
if (mysqli_num_rows($table_check) > 0) {
    $rewards_query = "SELECT * FROM rewards WHERE is_active = 1";
    $rewards_result = mysqli_query($conn, $rewards_query);
    $available_rewards = mysqli_fetch_all($rewards_result, MYSQLI_ASSOC);
}

$history_per_page = 10;
$history_current_page = isset($_GET['history_page']) ? max(1, intval($_GET['history_page'])) : 1;
$history_offset = ($history_current_page - 1) * $history_per_page;

$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_date_from = isset($_GET['filter_date_from']) ? $_GET['filter_date_from'] : '';
$filter_date_to = isset($_GET['filter_date_to']) ? $_GET['filter_date_to'] : '';

$where_conditions = ["lpl.customer_id = ?"];
$params = [$user_id];
$param_types = "i";

if (!empty($filter_type)) {
    switch ($filter_type) {
        case 'sale':
            $where_conditions[] = "(lpl.reason LIKE '%scrap sale%' OR lpl.reason LIKE '%transaction%' OR lpl.reason LIKE '%sale%')";
            break;
        case 'reward':
            $where_conditions[] = "(lpl.reason LIKE '%reward%' OR lpl.reason LIKE '%bonus%')";
            break;
        case 'redemption':
            $where_conditions[] = "(lpl.reason LIKE '%redeem%' OR lpl.reason LIKE '%purchase%' OR lpl.reason LIKE '%Voucher%')";
            break;
        case 'earned':
            $where_conditions[] = "lpl.points_change > 0";
            break;
        case 'redeemed':
            $where_conditions[] = "lpl.points_change < 0";
            break;
    }
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "DATE(lpl.created_at) >= ?";
    $params[] = $filter_date_from;
    $param_types .= "s";
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "DATE(lpl.created_at) <= ?";
    $params[] = $filter_date_to;
    $param_types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

$count_history_query = "SELECT COUNT(*) as total 
                       FROM loyalty_point_logs lpl
                       LEFT JOIN employees e ON lpl.employee_id = e.id
                       WHERE $where_clause";
$count_history_stmt = mysqli_prepare($conn, $count_history_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_history_stmt, $param_types, ...$params);
}
mysqli_stmt_execute($count_history_stmt);
$count_history_result = mysqli_stmt_get_result($count_history_stmt);
$total_history = mysqli_fetch_assoc($count_history_result)['total'];
$total_history_pages = ceil($total_history / $history_per_page);

$points_history = [];
$points_query = "SELECT 
                    lpl.points_change, 
                    lpl.reason as description, 
                    lpl.new_points as balance_after, 
                    lpl.created_at,
                    lpl.receipt_path,
                    CONCAT(e.first_name, ' ', e.last_name) as processed_by,
                    CASE 
                        WHEN lpl.points_change > 0 THEN 'earned'
                        WHEN lpl.points_change < 0 THEN 'redeemed'
                        ELSE 'adjusted'
                    END as transaction_type,
                    CASE 
                        WHEN lpl.reason LIKE '%scrap sale%' OR lpl.reason LIKE '%transaction%' OR lpl.reason LIKE '%sale%' THEN 'sale'
                        WHEN lpl.reason LIKE '%reward%' OR lpl.reason LIKE '%bonus%' THEN 'reward'
                        WHEN lpl.reason LIKE '%redeem%' OR lpl.reason LIKE '%purchase%' OR lpl.reason LIKE '%Voucher%' THEN 'redemption'
                        ELSE 'other'
                    END as category
                FROM loyalty_point_logs lpl
                LEFT JOIN employees e ON lpl.employee_id = e.id
                WHERE $where_clause
                ORDER BY lpl.created_at DESC 
                LIMIT ? OFFSET ?";

$params[] = $history_per_page;
$params[] = $history_offset;
$param_types .= "ii";

$points_stmt = mysqli_prepare($conn, $points_query);
mysqli_stmt_bind_param($points_stmt, $param_types, ...$params);
mysqli_stmt_execute($points_stmt);
$points_result = mysqli_stmt_get_result($points_stmt);
$points_history = mysqli_fetch_all($points_result, MYSQLI_ASSOC);

// Handle reward redemption
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_reward'])) {
    $reward_id = intval($_POST['reward_id']);
    
    // Fetch reward details to check points_cost
    if (!empty($available_rewards)) {
        $reward_fetch_query = "SELECT points_cost FROM rewards WHERE id = ?";
        $reward_fetch_stmt = mysqli_prepare($conn, $reward_fetch_query);
        mysqli_stmt_bind_param($reward_fetch_stmt, "i", $reward_id);
        mysqli_stmt_execute($reward_fetch_stmt);
        $reward_fetch_result = mysqli_stmt_get_result($reward_fetch_stmt);
        $reward = mysqli_fetch_assoc($reward_fetch_result);

        // Verify user has enough points
        if ($reward && $current_points >= $reward['points_cost']) {
            // Process redemption (implementation depends on your business logic)
            // For now, just set a success message
            $success_message = "Reward redeemed successfully!";
            // In a real application, you would deduct points, add to order history, etc.
        } else {
            $error_message = "You don't have enough points for this reward.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - <?php echo $t['loyalty_rewards']; ?></title>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            background: radial-gradient(circle, var(--bronze) 0%, transparent 70%);
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

        /* Added styles for loading state and modal */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .loading-content {
            background: var(--dark-bg-secondary);
            color: var(--dark-text-primary);
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--sales-orange);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Added success modal for voucher generation */
        .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }

        .success-modal-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }

        body.dark-mode .success-modal-content {
            background: var(--dark-bg-secondary);
            color: var(--dark-text-primary);
        }

        .success-modal h2 {
            color: #28a745;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .success-modal .check-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .success-modal .next-steps {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }

        body.dark-mode .success-modal .next-steps {
            background-color: var(--dark-bg-tertiary);
        }

        .success-modal .next-steps h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
        }

        body.dark-mode .success-modal .next-steps h3 {
            color: var(--dark-text-primary);
        }

        .success-modal .next-steps p {
            color: #6c757d;
            margin: 10px 0;
            line-height: 1.5;
        }

        body.dark-mode .success-modal .next-steps p {
            color: var(--dark-text-secondary);
        }

        .success-modal .validity-info {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #ffeaa7;
        }

        body.dark-mode .success-modal .validity-info {
            background-color: #332701;
            color: #ffd351;
            border-color: #665002;
        }

        .success-modal .close-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        .success-modal .close-btn:hover {
            background-color: #218838;
        }

        .success-message {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
            text-align: center;
        }

        body.dark-mode .success-message {
            background-color: rgba(40, 167, 69, 0.2);
        }

        .success-message h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .success-message p {
            margin: 5px 0;
            font-size: 14px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideDown 0.3s;
        }

        body.dark-mode .modal-content {
            background-color: var(--dark-bg-secondary);
            color: var(--dark-text-primary);
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

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close:hover,
        .close:focus {
            color: var(--sales-orange);
            transform: rotate(90deg);
        }

        .modal h2 {
            color: var(--topbar-brown);
            margin-bottom: 20px;
            padding-right: 40px;
        }

        body.dark-mode .modal h2 {
            color: var(--dark-text-primary);
        }

        .how-it-works-step {
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(106, 127, 70, 0.05);
            border-radius: 8px;
            border-left: 4px solid var(--icon-green);
        }

        body.dark-mode .how-it-works-step {
            background-color: rgba(106, 127, 70, 0.1);
        }

        .how-it-works-step h4 {
            color: var(--icon-green);
            margin-bottom: 8px;
        }

        /* Added filter styles */
        .filter-section {
            background-color: rgba(106, 127, 70, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid rgba(106, 127, 70, 0.2);
        }

        body.dark-mode .filter-section {
            background-color: rgba(106, 127, 70, 0.1);
            border-color: var(--dark-border);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }

        .filter-group label {
            font-weight: 500;
            margin-bottom: 5px;
            color: var(--text-dark);
            font-size: 14px;
        }

        body.dark-mode .filter-group label {
            color: var(--dark-text-primary);
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background-color: white;
        }

        body.dark-mode .filter-group select,
        body.dark-mode .filter-group input {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        .btn-filter {
            background-color: var(--icon-green);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-filter:hover {
            background-color: var(--stock-green);
        }

        .btn-clear {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-clear:hover {
            background-color: #5a6268;
        }

        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        body.dark-mode .pagination a, 
        body.dark-mode .pagination span {
            color: var(--dark-text-primary);
            border-color: var(--dark-border);
        }

        .pagination a:hover {
            background-color: var(--icon-green);
            color: white;
            border-color: var(--icon-green);
        }

        .pagination .current {
            background-color: var(--sales-orange);
            color: white;
            border-color: var(--sales-orange);
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        }

        body.dark-mode .notification-bell {
            background-color: var(--dark-bg-secondary);
            box-shadow: 0 3px 10px var(--dark-shadow);
            color: var(--dark-text-primary);
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
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
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

        /* Loyalty Status Card */
        .loyalty-status {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            background-color: rgba(106, 127, 70, 0.05);
            padding: 20px;
            border-radius: 10px;
        }

        body.dark-mode .loyalty-status {
            background-color: rgba(106, 127, 70, 0.1);
        }

        .loyalty-badge {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #e9ecef 0%, #d1d7dc 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--sales-orange);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        body.dark-mode .loyalty-badge {
            box-shadow: 0 3px 10px var(--dark-shadow);
        }

        .loyalty-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(106, 127, 70, 0.3);
        }

        body.dark-mode .loyalty-badge:hover {
            box-shadow: 0 5px 15px var(--dark-shadow);
        }

        .progress-bar {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin: 12px 0;
            overflow: hidden;
        }

        body.dark-mode .progress-bar {
            background-color: var(--dark-bg-tertiary);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--icon-green) 0%, var(--stock-green) 100%);
            border-radius: 5px;
            position: relative;
            animation: progressAnimation 1.5s ease-in-out;
        }

        @keyframes progressAnimation {
            from { width: 0; }
        }

        /* Tier Cards */
        .tier-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        body.dark-mode .tier-card {
            border-color: var(--dark-border);
            box-shadow: 0 5px 15px var(--dark-shadow);
        }

        .tier-card.bronze {
            background: linear-gradient(135deg, var(--bronze) 0%, #a66928 100%);
            border: 1px solid rgba(112, 139, 76, 0.2);
        }

        .tier-card.silver {
            background: linear-gradient(135deg, var(--silver) 0%, #a8a8a8 100%);
            border: 1px solid rgba(0,0,0,0.1);
        }

        .tier-card.gold {
            background: linear-gradient(135deg, var(--gold) 0%, #e6c200 100%);
            border: 1px solid rgba(217, 122, 65, 0.2);
        }

        .tier-card.platinum {
            background: linear-gradient(135deg, var(--platinum) 0%, #b8b6b4 100%);
            border: 1px solid rgba(184, 182, 180, 0.3);
            box-shadow: 0 5px 20px rgba(229, 228, 226, 0.4);
        }

        .tier-card.diamond {
            background: linear-gradient(135deg, var(--diamond) 0%, #00d4ff 100%);
            border: 1px solid rgba(0, 212, 255, 0.3);
            box-shadow: 0 5px 20px rgba(185, 242, 255, 0.4);
        }

        .tier-card.ethereal {
            background: linear-gradient(135deg, var(--ethereal) 0%, #9370db 100%);
            border: 1px solid rgba(221, 160, 221, 0.3);
            box-shadow: 0 5px 20px rgba(147, 112, 219, 0.4);
            position: relative;
        }

        .tier-card.ethereal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .tier-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: rgba(0,0,0,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
            color: white;
            backdrop-filter: blur(10px);
        }

        .tier-benefits {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .benefit-item {
            background-color: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
        }

        .benefit-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Voucher Cards */
        .voucher-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        body.dark-mode .voucher-card {
            background-color: var(--dark-bg-secondary);
            border-color: var(--dark-border);
            box-shadow: 0 3px 15px var(--dark-shadow);
        }

        .voucher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        body.dark-mode .voucher-card:hover {
            box-shadow: 0 5px 20px var(--dark-shadow);
        }

        .voucher-card.expired {
            opacity: 0.6;
            background-color: #f8f9fa;
        }

        body.dark-mode .voucher-card.expired {
            background-color: var(--dark-bg-tertiary);
        }

        .voucher-card.redeemed {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        body.dark-mode .voucher-card.redeemed {
            background-color: rgba(40, 167, 69, 0.2);
            border-color: rgba(40, 167, 69, 0.3);
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .voucher-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 18px;
            color: var(--sales-orange);
        }

        .voucher-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .voucher-status.active {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .voucher-status.expired {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .voucher-status.redeemed {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        /* Rewards Grid */
        .rewards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .reward-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        body.dark-mode .reward-card {
            background-color: var(--dark-bg-secondary);
            border-color: var(--dark-border);
            box-shadow: 0 3px 15px var(--dark-shadow);
        }

        .reward-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        body.dark-mode .reward-card:hover {
            box-shadow: 0 5px 20px var(--dark-shadow);
        }

        .reward-card h3 {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reward-card i {
            color: var(--icon-green);
            font-size: 20px;
        }

        .reward-points {
            display: inline-block;
            background-color: rgba(106, 127, 70, 0.1);
            color: var(--icon-green);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 5px;
        }

        body.dark-mode .reward-points {
            background-color: rgba(106, 127, 70, 0.2);
        }

        .reward-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        body.dark-mode .reward-actions {
            border-top-color: var(--dark-border);
        }

        /* Challenge Cards */
        .challenge-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
            border-left: 4px solid var(--icon-green);
        }

        body.dark-mode .challenge-card {
            background-color: var(--dark-bg-secondary);
            box-shadow: 0 3px 15px var(--dark-shadow);
        }

        .challenge-progress {
            margin-top: 15px;
        }

        .progress-details {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-top: 5px;
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

        .btn-warning {
            background: linear-gradient(90deg, var(--sales-orange) 0%, #e68a4f 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(217, 122, 65, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(217, 122, 65, 0.4);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Enhanced disabled button styles */
        .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
            background: #6c757d !important;
            background-image: none !important;
        }

        .btn:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        /* Added insufficient points styling */
        .insufficient-points {
            opacity: 0.5;
            position: relative;
        }

        .insufficient-points::after {
            content: 'Insufficient Points';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            white-space: nowrap;
            z-index: 2;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text-dark);
        }

        body.dark-mode .form-group label {
            color: var(--dark-text-primary);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
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

        /* Table Styles */
        .price-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .price-table th {
            background-color: rgba(106, 127, 70, 0.08);
            font-weight: 600;
            color: var(--icon-green);
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid rgba(106, 127, 70, 0.2);
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

        /* Enhanced transaction history styles */
        .transaction-row {
            transition: all 0.3s ease;
        }

        .transaction-row:hover {
            background-color: rgba(106, 127, 70, 0.05) !important;
            transform: translateX(3px);
        }

        body.dark-mode .transaction-row:hover {
            background-color: rgba(106, 127, 70, 0.1) !important;
        }

        .transaction-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .transaction-type.sale {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .transaction-type.reward {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .transaction-type.redemption {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .transaction-type.other {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .points-change {
            font-weight: bold;
            font-size: 16px;
        }

        .points-positive {
            color: var(--icon-green);
        }

        .points-negative {
            color: var(--sales-orange);
        }

        .transaction-details {
            font-size: 12px;
            color: #6c757d;
            margin-top: 2px;
        }

        body.dark-mode .transaction-details {
            color: var(--dark-text-secondary);
        }

        .receipt-link {
            color: var(--accent-blue);
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .receipt-link:hover {
            text-decoration: underline;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .alert i {
            font-size: 20px;
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

            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                min-width: auto;
            }

            .filter-actions {
                justify-content: center;
                margin-top: 15px;
            }
        }

        @media (max-width: 768px) {
            .tier-benefits, .rewards-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .tier-benefits, .rewards-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-card {
                padding: 20px 15px;
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
            <li><a href="index.php"><i class="fas fa-home"></i> <?php echo $t['dashboard']; ?></a></li>
            <li><a href="transaction.php"><i class="fas fa-history"></i> <?php echo $t['transaction_history']; ?></a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> <?php echo $t['schedule_pickup']; ?></a></li>
            <li><a href="prices.php"><i class="fas fa-coins"></i> <?php echo $t['current_prices']; ?></a></li>
            <li><a href="#" class="active"><i class="fas fa-award"></i> <?php echo $t['loyalty_rewards']; ?></a></li>
            <li><a href="settings.php"><i class="fas fa-user-cog"></i> <?php echo $t['account_settings']; ?></a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
            </a>
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

    <!-- Added loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <h3><?php echo $t['generating_voucher']; ?></h3>
            <p><?php echo $t['please_wait_processing']; ?></p>
        </div>
    </div>

    <!-- Added success modal for voucher generation -->
    <div class="success-modal" id="successModal">
        <div class="success-modal-content">
            <div class="check-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2><?php echo $t['voucher_generated_successfully']; ?></h2>
            
            <div class="next-steps">
                <h3><?php echo $t['next_steps']; ?>:</h3>
                <p><strong>1.</strong> <?php echo $t['print_receipt_present']; ?></p>
                <p><strong>2.</strong> <?php echo $t['bring_valid_id']; ?></p>
                <p><strong>3.</strong> <?php echo $t['visit_business_hours']; ?></p>
            </div>
            
            <div class="validity-info">
                <i class="fas fa-clock"></i>
                <strong><?php echo $t['voucher_valid_7_days']; ?></strong>
            </div>
            
            <button class="close-btn" onclick="closeSuccessModal()">
                <i class="fas fa-times"></i> <?php echo $t['close']; ?>
            </button>
        </div>
    </div>

    <!-- Added How It Works modal -->
    <div id="howItWorksModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-info-circle"></i> <?php echo $t['how_loyalty_rewards_work']; ?></h2>
            
            <div class="how-it-works-step">
                <h4><i class="fas fa-coins"></i> <?php echo $t['earn_points']; ?></h4>
                <p><?php echo $t['earn_points_description']; ?></p>
            </div>

            <div class="how-it-works-step">
                <h4><i class="fas fa-level-up-alt"></i> <?php echo $t['tier_progression']; ?></h4>
                <p><?php echo $t['tier_progression_description']; ?></p>
            </div>

            <div class="how-it-works-step">
                <h4><i class="fas fa-ticket-alt"></i> <?php echo $t['generate_vouchers']; ?></h4>
                <p><?php echo $t['generate_vouchers_description']; ?></p>
            </div>

            <div class="how-it-works-step">
                <h4><i class="fas fa-store"></i> <?php echo $t['redeem_at_counter']; ?></h4>
                <p><?php echo $t['redeem_at_counter_description']; ?></p>
            </div>

            <div class="how-it-works-step">
                <h4><i class="fas fa-clock"></i> <?php echo $t['voucher_validity']; ?></h4>
                <p><?php echo $t['voucher_validity_description']; ?></p>
            </div>

            <div class="how-it-works-step">
                <h4><i class="fas fa-gift"></i> <?php echo $t['tier_benefits']; ?></h4>
                <p><?php echo $t['tier_benefits_description']; ?></p>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h1 class="page-title"><?php echo $t['loyalty_rewards']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
            </div>
        </div>

        <!-- Added success message display for voucher generation -->
        <?php if ($voucher_generated): ?>
            <div class="success-message">
                <h3><i class="fas fa-check-circle"></i> <?php echo $t['voucher_generated_successfully']; ?></h3>
                <p><strong><?php echo $t['voucher_code']; ?>:</strong> <?php echo htmlspecialchars($generated_voucher_code); ?></p>
                <p><strong><?php echo $t['next_steps']; ?>:</strong> <?php echo $t['print_receipt_present']; ?></p>
                <p><em><?php echo $t['voucher_valid_7_days']; ?></em></p>
            </div>
        <?php elseif (isset($error_message) && !empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-coins"></i> <?php echo $t['your_points_summary']; ?></h3>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="background-color: rgba(106, 127, 70, 0.1); padding: 8px 15px; border-radius: 20px; font-weight: bold; color: var(--icon-green);">
                        <i class="fas fa-coins"></i> <?php echo number_format($current_points); ?> <?php echo $t['points']; ?>
                    </div>
                    <!-- Added How It Works button -->
                    <button class="btn btn-primary btn-sm" onclick="openHowItWorksModal()">
                        <i class="fas fa-gift"></i> <?php echo $t['how_it_works']; ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Current Tier Card -->
        <div class="dashboard-card tier-card <?php echo $current_tier; ?>">
            <div class="tier-badge"><?php echo $current_tier_name; ?> <?php echo $t['tier']; ?></div>
            <h3 style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?php echo $t['your_current_status']; ?></h3>
            <p style="color: white; opacity: 0.9;">
                <?php if ($current_tier_config['next']): ?>
                    <?php echo $t['youre']; ?> <?php echo round($progress_percentage); ?>% <?php echo $t['toward']; ?> <?php echo $next_tier_name; ?> <?php echo $t['tier']; ?>
                <?php else: ?>
                    <?php echo $t['youve_reached_the_highest_tier']; ?>
                <?php endif; ?>
            </p>
            <div class="tier-progress">
                <div class="progress-text">
                    <span><?php echo number_format($current_points); ?> <?php echo $t['points']; ?></span>
                    <?php if ($current_tier_config['next']): ?>
                        <span><?php echo number_format($current_points); ?>/<?php echo number_format($next_min_points); ?> <?php echo $t['points']; ?></span>
                    <?php else: ?>
                        <span><?php echo $t['maximum_level_reached']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
                </div>
            </div>
            <div class="tier-benefits">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-percentage" style="color: white;"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"><?php echo $loyalty_data['bonus_percentage']; ?>% <?php echo $t['bonus']; ?></h4>
                        <p style="margin: 0; color: white; opacity: 0.8; font-size: 14px;"><?php echo $t['on_all_scrap_sales']; ?></p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-truck" style="color: white;"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"><?php echo $loyalty_data['free_pickups']; ?> <?php echo $t['free_pickups']; ?></h4>
                        <p style="margin: 0; color: white; opacity: 0.8; font-size: 14px;"><?php echo $t['per_month']; ?></p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-<?php echo ($current_tier == 'ethereal') ? 'crown' : (($current_tier == 'diamond') ? 'gem' : (($current_tier == 'platinum') ? 'star' : 'clock')); ?>" style="color: white;"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                            <?php 
                            switch($current_tier) {
                                case 'ethereal': echo 'Ethereal Status'; break;
                                case 'diamond': echo 'Diamond Priority'; break;
                                case 'platinum': echo 'Platinum Service'; break;
                                case 'gold': echo 'VIP Treatment'; break;
                                default: echo 'Priority Service';
                            }
                            ?>
                        </h4>
                        <p style="margin: 0; color: white; opacity: 0.8; font-size: 14px;">
                            <?php 
                            switch($current_tier) {
                                case 'ethereal': echo 'Ultimate benefits'; break;
                                case 'diamond': echo 'Premium support'; break;
                                case 'platinum': echo 'Enhanced features'; break;
                                default: echo 'Faster processing';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tier Vouchers Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-ticket-alt"></i> <?php echo $t['tier_vouchers']; ?></h3>
                <span style="font-size: 14px; color: #6c757d;"><?php echo $t['generate_exclusive_vouchers']; ?></span>
            </div>
            <div class="rewards-grid">
                <?php foreach ($tier_vouchers as $tier => $voucher): ?>
                    <?php 
                    $tier_levels = ['bronze' => 0, 'silver' => 1, 'gold' => 2, 'platinum' => 3, 'diamond' => 4, 'ethereal' => 5];
                    $user_tier_level = $tier_levels[$current_tier];
                    $voucher_tier_level = $tier_levels[$tier];
                    $can_generate = $user_tier_level >= $voucher_tier_level;
                    $has_enough_points = $current_points >= $voucher['points_cost'];
                    $is_available = $can_generate && $has_enough_points;
                    ?>
                    <div class="reward-card <?php echo !$can_generate ? 'opacity-50' : (!$has_enough_points ? 'insufficient-points' : ''); ?>">
                        <h3>
                            <i class="fas <?php echo $voucher['icon']; ?>"></i> 
                            <?php echo $voucher['type']; ?>
                        </h3>
                        <p><?php echo $voucher['description']; ?></p>
                        <div class="reward-points"><?php echo number_format($voucher['points_cost']); ?> <?php echo $t['points']; ?></div>
                        <div style="margin: 10px 0; padding: 8px; background-color: rgba(<?php 
                            switch($tier) {
                                case 'silver': echo '192, 192, 192'; break;
                                case 'gold': echo '255, 215, 0'; break;
                                case 'platinum': echo '229, 228, 226'; break;
                                case 'diamond': echo '185, 242, 255'; break;
                                case 'ethereal': echo '221, 160, 221'; break;
                            }
                        ?>, 0.1); border-radius: 5px; text-align: center;">
                            <strong><?php echo strtoupper($tier); ?> <?php echo $t['tier']; ?></strong>
                        </div>
                        <div class="reward-actions">
                            <span style="color: var(--text-light); font-size: 14px;">
                                <?php 
                                if (!$can_generate) {
                                    echo $t['requires'] . ' ' . ucfirst($tier) . ' ' . $t['tier'];
                                } elseif (!$has_enough_points) {
                                    echo $t['need'] . ' ' . number_format($voucher['points_cost'] - $current_points) . ' ' . $t['more_points'];
                                } else {
                                    echo $t['available'];
                                }
                                ?>
                            </span>
                            <!-- Enhanced generate button with better disabled state -->
                            <form method="POST" id="voucherForm_<?php echo $tier; ?>" onsubmit="return generateVoucherSubmit(event, '<?php echo $tier; ?>')">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="generate_voucher" value="1">
                                <input type="hidden" name="tier" value="<?php echo $tier; ?>">
                                <button type="submit" 
                                        class="btn btn-warning btn-sm" 
                                        <?php echo !$is_available ? 'disabled' : ''; ?>
                                        data-points-needed="<?php echo $voucher['points_cost']; ?>"
                                        data-user-points="<?php echo $current_points; ?>"
                                        data-tier-required="<?php echo $tier; ?>"
                                        data-user-tier="<?php echo $current_tier; ?>">
                                    <i class="fas fa-ticket-alt"></i> <?php echo $t['generate']; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- User's Vouchers with Pagination -->
        <?php if (!empty($user_vouchers)): ?>
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> <?php echo $t['your_vouchers']; ?> (<?php echo $total_vouchers; ?> <?php echo $t['total']; ?>)</h3>
            </div>
            <?php foreach ($user_vouchers as $voucher): ?>
                <?php 
                $is_expired = strtotime($voucher['expires_at']) < time();
                $is_redeemed = $voucher['is_redeemed'];
                ?>
                <div class="voucher-card <?php echo $is_expired ? 'expired' : ($is_redeemed ? 'redeemed' : ''); ?>">
                    <div class="voucher-header">
                        <div class="voucher-code"><?php echo htmlspecialchars($voucher['voucher_code']); ?></div>
                        <div class="voucher-status <?php echo $is_redeemed ? 'redeemed' : ($is_expired ? 'expired' : 'active'); ?>">
                            <?php echo $is_redeemed ? $t['redeemed'] : ($is_expired ? $t['expired'] : $t['active']); ?>
                        </div>
                    </div>
                    <h4><?php echo htmlspecialchars($voucher['voucher_type']); ?></h4>
                    <p><?php echo htmlspecialchars($voucher['description']); ?></p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                        <div>
                            <strong><?php echo $t['value']; ?>: ₱<?php echo number_format($voucher['value'], 2); ?></strong><br>
                            <small><?php echo $t['expires']; ?>: <?php echo date('M j, Y h:i A', strtotime($voucher['expires_at'])); ?></small>
                        </div>
                        <?php if (!empty($voucher['receipt_path'])): ?>
                            <a href="<?php echo htmlspecialchars($voucher['receipt_path']); ?>" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-file-pdf"></i> <?php echo $t['view_receipt']; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Added pagination controls -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>">
                            <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>">
                            <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($current_tier_config['next']): ?>
            <?php $next_tier_config = $tier_config[$current_tier_config['next']]; ?>
            <div class="dashboard-card tier-card <?php echo $current_tier_config['next']; ?>" style="opacity: 0.8;">
                <div class="tier-badge"><?php echo $next_tier_name; ?> <?php echo $t['tier']; ?></div>
                <h3 style="color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?php echo $t['next_tier_preview']; ?></h3>
                <p style="color: white; opacity: 0.9;"><?php echo $t['reach']; ?> <?php echo number_format($next_min_points); ?> <?php echo $t['points_to_unlock']; ?></p>
                <div class="tier-progress">
                    <div class="progress-text">
                        <span><?php echo number_format($next_min_points - $current_points); ?> <?php echo $t['points_needed']; ?></span>
                        <span><?php echo number_format($next_min_points); ?> <?php echo $t['points']; ?></span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                </div>
                <div class="tier-benefits">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-percentage" style="color: white;"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"><?php echo $next_tier_config['bonus_percentage']; ?>% <?php echo $t['bonus']; ?></h4>
                            <p style="margin: 0; color: white; opacity: 0.8; font-size: 14px;"><?php echo $t['on_all_scrap_sales']; ?></p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-truck" style="color: white;"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3);"><?php echo $next_tier_config['free_pickups']; ?> <?php echo $t['free_pickups']; ?></h4>
                            <p style="margin: 0; color: white; opacity: 0.8; font-size: 14px;"><?php echo $t['per_month']; ?></p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-<?php echo ($current_tier_config['next'] == 'ethereal') ? 'crown' : (($current_tier_config['next'] == 'diamond') ? 'gem' : (($current_tier_config['next'] == 'platinum') ? 'star' : 'gift')); ?>" style="color: white;"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                <?php 
                                switch($current_tier_config['next']) {
                                    case 'ethereal': echo 'Ethereal Powers'; break;
                                    case 'diamond': echo 'Diamond Perks'; break;
                                    case 'platinum': echo 'Platinum Access'; break;
                                    default: echo 'Enhanced Benefits';
                                }
                                ?>
                            </h4>
                            <p style="margin: 0; color: white; opacity: 0.8; font-size: 14px;">
                                <?php 
                                switch($current_tier_config['next']) {
                                    case 'ethereal': echo 'Transcendent rewards'; break;
                                    case 'diamond': echo 'Exclusive privileges'; break;
                                    case 'platinum': echo 'Premium features'; break;
                                    default: echo 'Exclusive offers';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($active_challenges)): ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-trophy"></i> <?php echo $t['earn_bonus_points']; ?></h3>
                </div>
                <?php foreach ($active_challenges as $challenge): ?>
                    <div class="challenge-card">
                        <h4><?php echo htmlspecialchars($challenge['challenge_name']); ?></h4>
                        <p><?php echo htmlspecialchars($challenge['description']); ?></p>
                        <div class="challenge-progress">
                            <div class="progress-bar">
                                <?php
                                $progress = ($challenge['current_value'] / $challenge['target_value']) * 100;
                                $progress = min(max($progress, 0), 100);
                                ?>
                                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <div class="progress-details">
                                <span><?php echo $challenge['current_value']; ?>/<?php echo $challenge['target_value']; ?> <?php echo $challenge['target_metric']; ?></span>
                                <span>+<?php echo $challenge['points_reward']; ?> <?php echo $t['points']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($available_rewards)): ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-gift"></i> <?php echo $t['redeem_your_points']; ?></h3>
                </div>
                <div class="rewards-grid">
                    <?php foreach ($available_rewards as $reward): ?>
                        <div class="reward-card">
                            <h3>
                                <i class="fas <?php 
                                    switch($reward['reward_type']) {
                                        case 'cash': echo 'fa-money-bill-wave'; break;
                                        case 'service': echo 'fa-truck'; break;
                                        case 'discount': echo 'fa-store'; break;
                                        default: echo 'fa-gift';
                                    }
                                ?>"></i> 
                                <?php echo htmlspecialchars($reward['reward_name']); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($reward['description']); ?></p>
                            <div class="reward-points"><?php echo number_format($reward['points_cost']); ?> <?php echo $t['points']; ?></div>
                            <div class="reward-actions">
                                <span style="color: var(--text-light); font-size: 14px;">
                                    <?php echo ($current_points >= $reward['points_cost']) ? $t['available'] : $t['not_enough_points']; ?>
                                </span>
                                <form method="POST">
                                    <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                                    <button type="submit" name="redeem_reward" class="btn btn-primary btn-sm" 
                                        <?php echo ($current_points < $reward['points_cost']) ? 'disabled' : ''; ?>>
                                        <?php echo $t['redeem']; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> <?php echo $t['points_history_transaction_log']; ?></h3>
                <a href="#" class="view-all"><?php echo $t['view_all']; ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <!-- Added filter section -->
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="filter_type"><?php echo $t['transaction_type']; ?>:</label>
                            <select name="filter_type" id="filter_type">
                                <option value=""><?php echo $t['all_types']; ?></option>
                                <option value="earned" <?php echo $filter_type == 'earned' ? 'selected' : ''; ?>><?php echo $t['points_earned']; ?></option>
                                <option value="redeemed" <?php echo $filter_type == 'redeemed' ? 'selected' : ''; ?>><?php echo $t['points_redeemed']; ?></option>
                                <option value="sale" <?php echo $filter_type == 'sale' ? 'selected' : ''; ?>><?php echo $t['sales']; ?></option>
                                <option value="reward" <?php echo $filter_type == 'reward' ? 'selected' : ''; ?>><?php echo $t['rewards']; ?></option>
                                <option value="redemption" <?php echo $filter_type == 'redemption' ? 'selected' : ''; ?>><?php echo $t['redemptions']; ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="filter_date_from"><?php echo $t['from_date']; ?>:</label>
                            <input type="date" name="filter_date_from" id="filter_date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="filter_date_to"><?php echo $t['to_date']; ?>:</label>
                            <input type="date" name="filter_date_to" id="filter_date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-filter"></i> <?php echo $t['filter']; ?>
                            </button>
                            <a href="?" class="btn-clear">
                                <i class="fas fa-times"></i> <?php echo $t['clear']; ?>
                            </a>
                        </div>
                    </div>
                    <!-- Preserve history page parameter -->
                    <input type="hidden" name="history_page" value="<?php echo $history_current_page; ?>">
                </form>
            </div>
            
            <table class="price-table">
                <thead>
                    <tr>
                        <th><?php echo $t['date']; ?></th>
                        <th><?php echo $t['transaction_details']; ?></th>
                        <th><?php echo $t['type']; ?></th>
                        <th><?php echo $t['points']; ?></th>
                        <th><?php echo $t['balance']; ?></th>
                        <th><?php echo $t['receipt']; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($points_history)): ?>
                        <?php foreach ($points_history as $history): ?>
                            <tr class="transaction-row">
                                <td><?php echo date('M j, Y g:i A', strtotime($history['created_at'])); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($history['description']); ?></div>
                                    <?php if (!empty($history['processed_by'])): ?>
                                        <div class="transaction-details">
                                            <i class="fas fa-user"></i> <?php echo $t['processed_by']; ?>: <?php echo htmlspecialchars($history['processed_by']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="transaction-type <?php echo $history['category']; ?>">
                                        <?php echo ucfirst($history['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="points-change <?php echo ($history['points_change'] > 0) ? 'points-positive' : 'points-negative'; ?>">
                                        <?php echo ($history['points_change'] > 0 ? '+' : '') . $history['points_change']; ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($history['balance_after']); ?></td>
                                <td>
                                    <?php if (!empty($history['receipt_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($history['receipt_path']); ?>" class="receipt-link" target="_blank">
                                            <i class="fas fa-file-pdf"></i> <?php echo $t['view_receipt']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #6c757d; font-size: 12px;"><?php echo $t['no_receipt']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-light);"><?php echo $t['no_transaction_history_found']; ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Added pagination for transaction history -->
            <?php if ($total_history_pages > 1): ?>
                <div class="pagination">
                    <?php if ($history_current_page > 1): ?>
                        <a href="?history_page=<?php echo $history_current_page - 1; ?><?php echo !empty($filter_type) ? '&filter_type=' . urlencode($filter_type) : ''; ?><?php echo !empty($filter_date_from) ? '&filter_date_from=' . urlencode($filter_date_from) : ''; ?><?php echo !empty($filter_date_to) ? '&filter_date_to=' . urlencode($filter_date_to) : ''; ?>">
                            <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <i class="fas fa-chevron-left"></i> <?php echo $t['previous']; ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_history_pages; $i++): ?>
                        <?php if ($i == $history_current_page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?history_page=<?php echo $i; ?><?php echo !empty($filter_type) ? '&filter_type=' . urlencode($filter_type) : ''; ?><?php echo !empty($filter_date_from) ? '&filter_date_from=' . urlencode($filter_date_from) : ''; ?><?php echo !empty($filter_date_to) ? '&filter_date_to=' . urlencode($filter_date_to) : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($history_current_page < $total_history_pages): ?>
                        <a href="?history_page=<?php echo $history_current_page + 1; ?><?php echo !empty($filter_type) ? '&filter_type=' . urlencode($filter_type) : ''; ?><?php echo !empty($filter_date_from) ? '&filter_date_from=' . urlencode($filter_date_from) : ''; ?><?php echo !empty($filter_date_to) ? '&filter_date_to=' . urlencode($filter_date_to) : ''; ?>">
                            <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <?php echo $t['next']; ?> <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
            if (event.target.classList.contains('profile-modal')) {
                event.target.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });

        let isSubmitting = false;

        function generateVoucherSubmit(event, tier) {
            event.preventDefault(); // Prevent immediate form submission

            if (isSubmitting) {
                return false; // Already submitting
            }

            const form = event.target;
            const button = form.querySelector('button[type="submit"]');
            
            // Check if button is disabled
            if (button.disabled) {
                const pointsNeeded = parseInt(button.getAttribute('data-points-needed'));
                const userPoints = parseInt(button.getAttribute('data-user-points'));
                const tierRequired = button.getAttribute('data-tier-required');
                const userTier = button.getAttribute('data-user-tier');
                
                const tierLevels = {'bronze': 0, 'silver': 1, 'gold': 2, 'platinum': 3, 'diamond': 4, 'ethereal': 5};
                
                if (tierLevels[userTier] < tierLevels[tierRequired]) {
                    alert(`You need to reach ${tierRequired.charAt(0).toUpperCase() + tierRequired.slice(1)} tier to generate this voucher.`);
                } else if (userPoints < pointsNeeded) {
                    alert(`You need ${(pointsNeeded - userPoints).toLocaleString()} more points to generate this voucher.`);
                }
                return false;
            }

            const csrfTokenInput = form.querySelector('input[name="csrf_token"]');
            const selectedTierInput = form.querySelector('input[name="tier"]');

            if (!selectedTierInput.value) {
                alert('Please select a voucher tier first.');
                return false;
            }

            if (confirm(`Are you sure you want to generate a ${tier.toUpperCase()} voucher?`)) {
                isSubmitting = true;
                // Disable the form to prevent double submission
                const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitButtons.forEach(button => button.disabled = true);

                // Show loading modal
                document.getElementById('loadingOverlay').style.display = 'flex';

                // Add minimum loading time for better UX
                setTimeout(() => {
                    // Use fetch to submit form and handle response
                    const formData = new FormData(form);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        // Check if voucher was generated successfully
                        if (data.includes('voucher_generated = true') || data.includes('Voucher Generated Successfully')) {
                            showSuccessModal();
                        } else {
                            // If there was an error, reload the page to show the error message
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        window.location.reload();
                    })
                    .finally(() => {
                        isSubmitting = false;
                        document.getElementById('loadingOverlay').style.display = 'none';
                        // Re-enable buttons if submission failed or if we are not reloading
                        if (!data || !data.includes('Voucher Generated Successfully')) {
                            submitButtons.forEach(button => button.disabled = false);
                        }
                    });
                }, 2000); // Minimum 2 seconds loading
            }
            return false; // Prevent default form submission if confirmation is cancelled
        }

        function showSuccessModal() {
            document.getElementById('successModal').style.display = 'flex';
        }

        function closeSuccessModal() {
            document.getElementById('successModal').style.display = 'none';
            // Reload page to show updated points and vouchers
            window.location.reload();
        }

        function openHowItWorksModal() {
            document.getElementById('howItWorksModal').style.display = 'block';
        }

        // Close modal when clicking the X
        document.querySelector('.close').onclick = function() {
            document.getElementById('howItWorksModal').style.display = 'none';
        }

        // Clear form state on page load
        window.addEventListener('load', function() {
            isSubmitting = false;
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitButtons.forEach(button => {
                    const pointsNeeded = parseInt(button.getAttribute('data-points-needed'));
                    const userPoints = parseInt(button.getAttribute('data-user-points'));
                    const tierRequired = button.getAttribute('data-tier-required');
                    const userTier = button.getAttribute('data-user-tier');
                    
                    if (pointsNeeded && userPoints !== null && tierRequired && userTier) {
                        const tierLevels = {'bronze': 0, 'silver': 1, 'gold': 2, 'platinum': 3, 'diamond': 4, 'ethereal': 5};
                        const canGenerate = tierLevels[userTier] >= tierLevels[tierRequired] && userPoints >= pointsNeeded;
                        button.disabled = !canGenerate;
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>