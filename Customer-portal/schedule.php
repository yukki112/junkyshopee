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
        'schedule_pickup_title' => 'Schedule Pickup',
        'schedule_junk_pickup' => 'Schedule a Junk Pickup',
        'what_recycling' => 'What are you recycling?',
        'select_material' => 'Select material',
        'weight_kg' => 'Weight (kg)',
        'add_another_material' => 'Add another material',
        'estimated_value' => 'Estimated Value',
        'estimated_total' => 'Estimated Total',
        'final_amount_note' => 'Final amount may vary based on actual weight and quality at time of pickup.',
        'when_where' => 'When & Where?',
        'pickup_date' => 'Pickup Date',
        'time_slot' => 'Time Slot',
        'pickup_address' => 'Pickup Address',
        'use_saved_address' => 'Use my saved address',
        'special_instructions' => 'Special Instructions (Optional)',
        'shipping_method' => 'Shipping Method',
        'distance_junkshop' => 'Distance to junkshop',
        'confirm_pickup' => 'Confirm Your Pickup',
        'pickup_details' => 'Pickup Details',
        'materials' => 'Materials',
        'shipping_fee' => 'Shipping Fee',
        'total_amount' => 'Total Amount',
        'agree_terms' => 'I agree to the Terms of Service and confirm that all materials listed are acceptable for recycling per our guidelines.',
        'confirm_schedule_pickup' => 'Confirm & Schedule Pickup',
        'pickup_scheduled_success' => 'Pickup Scheduled Successfully!',
        'estimated_value_label' => 'Estimated Value',
        'shipping_method_label' => 'Shipping Method',
        'view_pickups' => 'View My Pickups',
        'return_dashboard' => 'Return to Dashboard',
        'continue' => 'Continue',
        'back' => 'Back',
        'success_message' => 'Your junk pickup has been scheduled for',
        'during' => 'during',
        'confirmation_email' => "We'll send you a confirmation email with all the details and notify you when our collector is on the way.",
        'transaction_id' => 'Transaction ID',
        'pickup_id' => 'Pickup ID'
    ],
    'tl' => [
        'dashboard' => 'Dashboard',
        'transaction_history' => 'Kasaysayan ng Transaksyon',
        'schedule_pickup' => 'I-skedyul ang Pickup',
        'current_prices' => 'Kasalukuyang Mga Presyo',
        'loyalty_rewards' => 'Mga Gantimpala ng Loyalty',
        'account_settings' => 'Mga Setting ng Account',
        'logout' => 'Logout',
        'schedule_pickup_title' => 'I-skedyul ang Pickup',
        'schedule_junk_pickup' => 'Mag-skedyul ng Junk Pickup',
        'what_recycling' => 'Ano ang iyong nire-recycle?',
        'select_material' => 'Pumili ng materyal',
        'weight_kg' => 'Timbang (kg)',
        'add_another_material' => 'Magdagdag ng ibang materyal',
        'estimated_value' => 'Tinantyang Halaga',
        'estimated_total' => 'Tinantyang Kabuuan',
        'final_amount_note' => 'Ang huling halaga ay maaaring mag-iba batay sa aktwal na timbang at kalidad sa oras ng pickup.',
        'when_where' => 'Kailan at Saan?',
        'pickup_date' => 'Petsa ng Pickup',
        'time_slot' => 'Oras ng Pickup',
        'pickup_address' => 'Address ng Pickup',
        'use_saved_address' => 'Gamitin ang aking naka-save na address',
        'special_instructions' => 'Espesyal na Mga Tagubilin (Opsyonal)',
        'shipping_method' => 'Paraan ng Pagpapadala',
        'distance_junkshop' => 'Distansya papunta sa junkshop',
        'confirm_pickup' => 'Kumpirmahin ang Iyong Pickup',
        'pickup_details' => 'Mga Detalye ng Pickup',
        'materials' => 'Mga Materyales',
        'shipping_fee' => 'Bayad sa Pagpapadala',
        'total_amount' => 'Kabuuang Halaga',
        'agree_terms' => 'Sumasang-ayon ako sa Mga Tuntunin ng Serbisyo at kinukumpirma na ang lahat ng nakalista na materyales ay katanggap-tanggap para sa recycling ayon sa aming mga alituntunin.',
        'confirm_schedule_pickup' => 'Kumpirmahin at I-skedyul ang Pickup',
        'pickup_scheduled_success' => 'Matagumpay na Na-skedyul ang Pickup!',
        'estimated_value_label' => 'Tinantyang Halaga',
        'shipping_method_label' => 'Paraan ng Pagpapadala',
        'view_pickups' => 'Tingnan ang Aking Mga Pickup',
        'return_dashboard' => 'Bumalik sa Dashboard',
        'continue' => 'Magpatuloy',
        'back' => 'Bumalik',
        'success_message' => 'Ang iyong junk pickup ay nai-schedule para sa',
        'during' => 'sa oras ng',
        'confirmation_email' => 'Magpapadala kami sa iyo ng kumpirmasyon sa email kasama ang lahat ng detalye at ipapaalam sa iyo kapag ang aming collector ay papunta na.',
        'transaction_id' => 'ID ng Transaksyon',
        'pickup_id' => 'ID ng Pickup'
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
$user_query = "SELECT first_name, last_name, profile_image, address FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);
$user_name = $user['first_name'] . ' ' . $user['last_name'];
$user_initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
$user_address = $user['address'] ?? '';

// Get booked time slots for date selection
$booked_slots = [];
$slot_query = "SELECT pickup_date, time_slot FROM pickups WHERE status = 'Scheduled'";
$slot_result = mysqli_query($conn, $slot_query);
if ($slot_result) {
    while ($row = mysqli_fetch_assoc($slot_result)) {
        $booked_slots[$row['pickup_date']][] = $row['time_slot'];
    }
}

// Update material prices in database
$material_prices = [
    ['Copper Wire', 450.00],
    ['PET Bottles', 9.00],
    ['Aluminum Cans', 75.00],
    ['Cardboard', 2.00],
    ['Steel', 8.00],
    ['Glass Bottles', 2.00],
    ['Computer Parts', 250.00],
    ['Yero (Corrugated Sheets)', 7.00],
    ['Batteries', 25.00]
];

foreach ($material_prices as $material) {
    $material_name = $material[0];
    $unit_price = $material[1];
    
    $check_query = "SELECT id FROM materials WHERE material_option = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $material_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE materials SET unit_price = ? WHERE material_option = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ds", $unit_price, $material_name);
        mysqli_stmt_execute($stmt);
    } else {
        $insert_query = "INSERT INTO materials (material_option, unit_price) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "sd", $material_name, $unit_price);
        mysqli_stmt_execute($stmt);
    }
}

// Get materials from database
$materials = [];
$material_query = "SELECT * FROM materials ORDER BY material_option";
$material_result = mysqli_query($conn, $material_query);
if ($material_result) {
    while ($row = mysqli_fetch_assoc($material_result)) {
        $materials[] = $row;
    }
}

// Get shipping methods from database
$shipping_methods = [];
$shipping_query = "SELECT * FROM shipping_methods WHERE is_active = 1";
$shipping_result = mysqli_query($conn, $shipping_query);
if ($shipping_result) {
    while ($row = mysqli_fetch_assoc($shipping_result)) {
        $shipping_methods[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'rate' => $row['rate'],
            'max_weight' => $row['max_weight']
        ];
    }
}

// Process form submission
$errors = [];
$success = false;
$transaction_id = '';
$pickup_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_pickup'])) {
    // Validate and sanitize inputs
    $pickup_date = mysqli_real_escape_string($conn, $_POST['pickup_date']);
    $time_slot = mysqli_real_escape_string($conn, $_POST['time_slot']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $special_instructions = mysqli_real_escape_string($conn, $_POST['special_instructions']);
    $shipping_method = mysqli_real_escape_string($conn, $_POST['shipping_method']);
    $estimated_value = 0;
    $total_weight = 0;
    
    // Calculate total weight and estimated value
    foreach ($_POST['materials'] as $material) {
        if (isset($material['id'], $material['quantity']) && is_numeric($material['quantity'])) {
            $material_id = intval($material['id']);
            $quantity = floatval($material['quantity']);
            $total_weight += $quantity;
            
            // Get unit price for this material
            $price_query = "SELECT unit_price FROM materials WHERE id = ?";
            $stmt = mysqli_prepare($conn, $price_query);
            mysqli_stmt_bind_param($stmt, "i", $material_id);
            mysqli_stmt_execute($stmt);
            $price_result = mysqli_stmt_get_result($stmt);
            if ($price_result && $price_row = mysqli_fetch_assoc($price_result)) {
                $estimated_value += $quantity * $price_row['unit_price'];
            }
        }
    }
    
    // Get shipping rate
    $shipping_fee = 0;
    $selected_shipping = null;
    
    foreach ($shipping_methods as $method) {
        if ($method['id'] === $shipping_method && $total_weight <= $method['max_weight']) {
            $shipping_fee = $method['rate'];
            $selected_shipping = $method;
            break;
        }
    }
    
    if (!$selected_shipping) {
        $errors[] = "Invalid shipping method selected or weight exceeds maximum limit";
    }
    
    // Check if time slot is already booked
    if (isset($booked_slots[$pickup_date])) {
        if (in_array($time_slot, $booked_slots[$pickup_date])) {
            $errors[] = "The selected time slot is already booked. Please choose another time.";
        }
    }
    
    // Validate materials
    if (!isset($_POST['materials']) || !is_array($_POST['materials']) || count($_POST['materials']) === 0) {
        $errors[] = "Please add at least one material";
    }
    
    if (empty($errors)) {
        // Calculate total amount with shipping
        $total_amount = $estimated_value + $shipping_fee;
        
        // Insert pickup record
        $pickup_query = "INSERT INTO pickups (user_id, pickup_date, time_slot, address, special_instructions, estimated_value, status, shipping_method, shipping_fee) 
                         VALUES (?, ?, ?, ?, ?, ?, 'Scheduled', ?, ?)";
        $stmt = mysqli_prepare($conn, $pickup_query);
        mysqli_stmt_bind_param($stmt, "issssdsd", $user_id, $pickup_date, $time_slot, $address, $special_instructions, $estimated_value, $shipping_method, $shipping_fee);
        
        if (mysqli_stmt_execute($stmt)) {
            $pickup_id = mysqli_insert_id($conn);
            
            // Generate transaction ID
            $transaction_id = 'TXN-' . date('Ymd') . '-' . str_pad($pickup_id, 5, '0', STR_PAD_LEFT);
            
            // Build materials list for transaction details
            $materials_list = [];
            foreach ($_POST['materials'] as $material) {
                if (isset($material['id'], $material['quantity']) && is_numeric($material['quantity'])) {
                    $material_id = intval($material['id']);
                    $quantity = floatval($material['quantity']);
                    
                    // Get material name
                    $material_name_query = "SELECT material_option FROM materials WHERE id = ?";
                    $material_name_stmt = mysqli_prepare($conn, $material_name_query);
                    mysqli_stmt_bind_param($material_name_stmt, "i", $material_id);
                    mysqli_stmt_execute($material_name_stmt);
                    $material_name_result = mysqli_stmt_get_result($material_name_stmt);
                    $material_name_row = mysqli_fetch_assoc($material_name_result);
                    
                    if ($material_name_row) {
                        $materials_list[] = $material_name_row['material_option'] . " (" . $quantity . "kg)";
                    }
                }
            }
            
            $item_details = "Scheduled Pickup: " . implode(", ", $materials_list);
            $additional_info = "Address: " . $address;
            if (!empty($special_instructions)) {
                $additional_info .= "\nSpecial Instructions: " . $special_instructions;
            }
            $additional_info .= "\nShipping Method: " . $selected_shipping['name'] . " (₱" . number_format($shipping_fee, 2) . ")";
            
            // Insert transaction record
            $transaction_query = "INSERT INTO transactions 
                                (transaction_id, user_id, name, transaction_type, transaction_date, transaction_time, 
                                item_details, additional_info, status, amount, created_at, pickup_date, time_slot) 
                                VALUES (?, ?, ?, 'Pickup', ?, ?, ?, ?, 'Pending', ?, NOW(), ?, ?)";
            $transaction_stmt = mysqli_prepare($conn, $transaction_query);

            $current_date = date('Y-m-d');
            $current_time = date('H:i:s');

            // Get just the last name for the transaction
            $user_last_name = $user['last_name'];

            mysqli_stmt_bind_param($transaction_stmt, "sisssssdss", 
                $transaction_id,
                $user_id,
                $user_last_name,
                $current_date,
                $current_time,
                $item_details,
                $additional_info,
                $total_amount,
                $pickup_date,
                $time_slot
            );
            
            if (!mysqli_stmt_execute($transaction_stmt)) {
                error_log("Failed to create transaction record: " . mysqli_error($conn));
            }
            
            // Insert pickup materials
            foreach ($_POST['materials'] as $material) {
                if (isset($material['id'], $material['quantity']) && is_numeric($material['quantity'])) {
                    $material_id = intval($material['id']);
                    $quantity = floatval($material['quantity']);
                    
                    // Get unit price for this material
                    $price_query = "SELECT unit_price FROM materials WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $price_query);
                    mysqli_stmt_bind_param($stmt, "i", $material_id);
                    mysqli_stmt_execute($stmt);
                    $price_result = mysqli_stmt_get_result($stmt);
                    if ($price_result && $price_row = mysqli_fetch_assoc($price_result)) {
                        $estimated_price = $quantity * $price_row['unit_price'];
                        
                        $material_query = "INSERT INTO pickup_materials (pickup_id, material_id, quantity_kg, estimated_price) 
                                         VALUES (?, ?, ?, ?)";
                        $material_stmt = mysqli_prepare($conn, $material_query);
                        mysqli_stmt_bind_param($material_stmt, "iidd", $pickup_id, $material_id, $quantity, $estimated_price);
                        mysqli_stmt_execute($material_stmt);
                        mysqli_stmt_close($material_stmt);
                    }
                }
            }
            
            $success = true;
        } else {
            $errors[] = "Error scheduling pickup: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JunkValue - Schedule Pickup</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="img/MainLogo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Carter+One&family=Gugi&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Newsreader:ital,opsz,wght@0,6..72,200;1,6..72,200&family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Pacifico&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Winky+Sans:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
            background: radial-gradient(circle, var(--icon-green) 0%, transparent 70%);
            top: -250px;
            left: -250px;
            animation: float 25s ease-in-out infinite;
        }
        
        .bg-decoration-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--stock-green) 0%, transparent 70%);
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

        body.dark-mode .mobile-menu-toggle {
            box-shadow: 0 3px 10px var(--dark-shadow);
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

        /* Pickup Steps */
        .pickup-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-progress-bar {
            position: absolute;
            height: 3px;
            background-color: #e9ecef;
            top: 15px;
            left: 0;
            right: 0;
            z-index: 1;
            transition: all 0.3s ease;
        }

        body.dark-mode .step-progress-bar {
            background-color: var(--dark-bg-tertiary);
        }
        
        .step-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--icon-green) 0%, var(--stock-green) 100%);
            width: 33%;
            transition: all 0.3s;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        body.dark-mode .step-number {
            background-color: var(--dark-bg-tertiary);
            color: var(--dark-text-primary);
        }
        
        .step.active .step-number {
            background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(106, 127, 70, 0.3);
        }
        
        .step.completed .step-number {
            background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
            color: white;
        }
        
        .step.completed .step-number::after {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }
        
        .step-label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .step-label {
            color: var(--dark-text-primary);
        }
        
        /* Form Sections */
        .pickup-form-section {
            display: none;
        }
        
        .pickup-form-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .form-group label {
            color: var(--dark-text-primary);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: white;
            color: var(--text-dark);
        }

        body.dark-mode .form-group input,
        body.dark-mode .form-group select,
        body.dark-mode .form-group textarea {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--icon-green);
            box-shadow: 0 0 0 3px rgba(106, 127, 70, 0.1);
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Material Rows */
        .material-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            gap: 15px;
        }
        
        .material-select {
            flex: 2;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            font-size: 15px;
            background-color: white;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        body.dark-mode .material-select {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        .material-quantity {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            max-width: 120px;
            font-size: 15px;
            background-color: white;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        body.dark-mode .material-quantity {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        .material-value {
            flex: 1;
            font-weight: 600;
            color: var(--icon-green);
            min-width: 100px;
            font-size: 15px;
        }
        
        .remove-material {
            color: #dc3545;
            cursor: pointer;
            padding: 10px;
            font-size: 16px;
            transition: all 0.2s;
        }
        
        .remove-material:hover {
            transform: scale(1.1);
        }
        
        .add-material {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--icon-green);
            cursor: pointer;
            margin: 20px 0;
            font-weight: 500;
            padding: 10px 0;
            transition: all 0.2s;
        }
        
        .add-material:hover {
            color: var(--stock-green);
        }
        
        .add-material i {
            font-size: 18px;
        }
        
        /* Summary */
        .pickup-summary {
            background-color: rgba(106, 127, 70, 0.05);
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
            border: 1px solid rgba(106, 127, 70, 0.1);
            transition: all 0.3s ease;
        }

        body.dark-mode .pickup-summary {
            background-color: rgba(106, 127, 70, 0.1);
            border-color: var(--dark-border);
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 15px;
            transition: color 0.3s ease;
        }

        body.dark-mode .summary-item {
            color: var(--dark-text-primary);
        }
        
        .summary-total {
            font-weight: 600;
            border-top: 1px solid rgba(0,0,0,0.1);
            padding-top: 15px;
            margin-top: 15px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        body.dark-mode .summary-total {
            border-top-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        /* Buttons */
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            border: none;
            font-size: 15px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-outline {
            background-color: white;
            border: 1px solid rgba(0,0,0,0.1);
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        body.dark-mode .btn-outline {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        .btn-outline:hover {
            background-color: #f8f9fa;
            border-color: rgba(0,0,0,0.2);
        }

        body.dark-mode .btn-outline:hover {
            background-color: var(--dark-bg-secondary);
            border-color: var(--dark-border);
        }
        
        .btn-primary {
            background: linear-gradient(90deg, var(--icon-green) 0%, var(--stock-green) 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(106, 127, 70, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 127, 70, 0.4);
        }
        
        /* Time Slots */
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }
        
        .time-slot {
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            background-color: white;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        body.dark-mode .time-slot {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        .time-slot:hover {
            border-color: var(--icon-green);
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .time-slot.selected {
            background: linear-gradient(135deg, var(--icon-green) 0%, var(--stock-green) 100%);
            color: white;
            border-color: var(--icon-green);
            box-shadow: 0 3px 10px rgba(106, 127, 70, 0.3);
        }
        
        .time-slot.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f8f9fa;
        }

        body.dark-mode .time-slot.disabled {
            background-color: var(--dark-bg-secondary);
        }
        
        /* Shipping Methods */
        .shipping-methods {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .shipping-method {
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            background-color: white;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        body.dark-mode .shipping-method {
            background-color: var(--dark-bg-tertiary);
            border-color: var(--dark-border);
            color: var(--dark-text-primary);
        }
        
        .shipping-method:hover {
            border-color: var(--icon-green);
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .shipping-method.selected {
            border: 2px solid var(--icon-green);
            background-color: rgba(106, 127, 70, 0.05);
        }

        body.dark-mode .shipping-method.selected {
            background-color: rgba(106, 127, 70, 0.1);
        }
        
        .shipping-method.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f8f9fa;
        }

        body.dark-mode .shipping-method.disabled {
            background-color: var(--dark-bg-secondary);
        }
        
        .shipping-method-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .shipping-method-name {
            color: var(--dark-text-primary);
        }
        
        .shipping-method-description {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        body.dark-mode .shipping-method-description {
            color: var(--dark-text-secondary);
        }
        
        .shipping-method-rate {
            font-weight: 600;
            color: var(--icon-green);
        }
        
        .map-container {
            height: 800px;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 15px;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            position: relative;
        }

        body.dark-mode .map-container {
            border-color: var(--dark-border);
            box-shadow: 0 3px 10px var(--dark-shadow);
        }

        .map-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.9);
            padding: 10px 15px;
            font-size: 14px;
            z-index: 1000;
            border-top: 1px solid rgba(0,0,0,0.1);
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        body.dark-mode .map-overlay {
            background: rgba(45, 45, 45, 0.9);
            border-top-color: var(--dark-border);
            color: var(--dark-text-primary);
        }

        .map-overlay strong {
            color: var(--icon-green);
        }
        
        /* Messages */
        .error-message {
            color: #dc3545;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8d7da;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
        }
        
        .success-message {
            color: #28a745;
            margin-bottom: 30px;
            padding: 40px;
            background-color: #d4edda;
            border-radius: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.2);
        }

        body.dark-mode .success-message {
            background-color: rgba(40, 167, 69, 0.1);
            border-color: rgba(40, 167, 69, 0.3);
            color: var(--dark-text-primary);
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.1);
        }

        .success-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--icon-green) 0%, var(--stock-green) 100%);
        }

        .success-message h3 {
            margin-top: 0;
            color: #28a745;
            font-size: 32px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-weight: 700;
        }

        .success-message p {
            margin-bottom: 15px;
            font-size: 17px;
            line-height: 1.7;
        }

        .success-message .btn {
            margin-top: 20px;
            padding: 14px 35px;
            font-size: 16px;
            border-radius: 12px;
            font-weight: 600;
        }

        /* Portrait Style Success Details */
        .success-details-portrait {
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 15px;
            padding: 30px;
            margin: 25px auto;
            text-align: left;
            border: 1px solid rgba(0,0,0,0.05);
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        body.dark-mode .success-details-portrait {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: var(--dark-border);
            box-shadow: 0 5px 15px var(--dark-shadow);
        }

        .success-details-portrait h4 {
            color: var(--icon-green);
            margin-bottom: 25px;
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            border-bottom: 2px solid rgba(106, 127, 70, 0.2);
            padding-bottom: 15px;
        }

        .success-details-grid-portrait {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .success-detail-item-portrait {
            display: flex;
            flex-direction: column;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }

        body.dark-mode .success-detail-item-portrait {
            border-bottom-color: var(--dark-border);
        }

        .success-detail-item-portrait:last-child {
            border-bottom: none;
        }

        .success-detail-label-portrait {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        body.dark-mode .success-detail-label-portrait {
            color: var(--dark-text-primary);
        }

        .success-detail-value-portrait {
            font-weight: 700;
            color: var(--icon-green);
            font-size: 16px;
        }

        .success-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: var(--icon-green);
            border-radius: 50%;
            opacity: 0.7;
            animation: confetti-fall 5s linear forwards;
            z-index: 1;
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        /* Terms Checkbox */
        .terms-checkbox {
            display: flex;
            align-items: center;
            margin: 30px 0;
            padding: 20px;
            background-color: rgba(106, 127, 70, 0.05);
            border-radius: 12px;
            border: 1px dashed rgba(106, 127, 70, 0.3);
            transition: all 0.3s ease;
        }

        body.dark-mode .terms-checkbox {
            background-color: rgba(106, 127, 70, 0.1);
            border-color: var(--dark-border);
        }

        .terms-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            accent-color: var(--icon-green);
        }

        .terms-checkbox label {
            font-size: 15px;
            cursor: pointer;
            color: var(--text-dark);
            transition: color 0.3s ease;
        }

        body.dark-mode .terms-checkbox label {
            color: var(--dark-text-primary);
        }

        .terms-checkbox a {
            color: var(--icon-green);
            text-decoration: none;
            font-weight: 500;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }
        
        /* Error states */
        .invalid {
            border-color: #dc3545 !important;
        }
        

        .time-slot.booked {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
            cursor: not-allowed;
            opacity: 0.7;
            position: relative;
        }

        body.dark-mode .time-slot.booked {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--dark-text-primary);
            border-color: rgba(220, 53, 69, 0.3);
        }

        .time-slot.booked::after {
            content: "\f00d";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: 5px;
            right: 5px;
            color: #dc3545;
        }
        
        /* Specific text elements to be white in dark mode */
        body.dark-mode .schedule-junk-pickup-text,
        body.dark-mode .estimated-value-text,
        body.dark-mode .final-amount-note,
        body.dark-mode .distance-junkshop-text,
        body.dark-mode .pickup-details-text {
            color: var(--dark-text-primary) !important;
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
        }

        @media (max-width: 768px) {
            .material-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .material-select,
            .material-quantity {
                width: 100%;
                max-width: none;
            }
            
            .time-slots,
            .shipping-methods {
                grid-template-columns: 1fr 1fr;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }

            .success-details-grid-portrait {
                grid-template-columns: 1fr;
            }

            .success-actions {
                flex-direction: column;
            }

            .success-actions .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .pickup-steps {
                margin-bottom: 20px;
            }
            
            .step-label {
                font-size: 12px;
            }
            
            .time-slots,
            .shipping-methods {
                grid-template-columns: 1fr;
            }
            
            .dashboard-card {
                padding: 20px;
            }
            
            .pickup-summary {
                padding: 20px;
            }

            .success-message {
                padding: 25px;
            }

            .success-message h3 {
                font-size: 24px;
            }

            .success-details-portrait {
                padding: 20px;
                margin: 15px auto;
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
    
    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="user-avatar" id="userAvatar">
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
            <li><a href="#" class="active"><i class="fas fa-calendar-alt"></i> <?php echo $t['schedule_pickup']; ?></a></li>
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
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1 class="page-title"><?php echo $t['schedule_pickup_title']; ?></h1>
            <div class="header-controls">
                <button class="dark-mode-toggle">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
                <!-- Notification bell removed as requested -->
            </div>
        </div>
        
        <div class="dashboard-card">
            <?php if ($success): ?>
                <div class="success-message" id="successMessage">
                    <h3><i class="fas fa-check-circle"></i> <?php echo $t['pickup_scheduled_success']; ?></h3>
                    <p><?php echo $t['success_message']; ?> <strong><?php echo htmlspecialchars($pickup_date); ?></strong> <?php echo $t['during']; ?> <strong><?php echo htmlspecialchars($time_slot); ?></strong>.</p>
                    
                    <!-- Portrait Style Pickup Details -->
                    <div class="success-details-portrait">
                        <h4><i class="fas fa-receipt"></i> <?php echo $t['pickup_details']; ?></h4>
                        <div class="success-details-grid-portrait">
                            <div class="success-detail-item-portrait">
                                <span class="success-detail-label-portrait"><?php echo $t['transaction_id']; ?></span>
                                <span class="success-detail-value-portrait"><?php echo htmlspecialchars($transaction_id); ?></span>
                            </div>
                            <div class="success-detail-item-portrait">
                                <span class="success-detail-label-portrait"><?php echo $t['pickup_id']; ?></span>
                                <span class="success-detail-value-portrait">#<?php echo htmlspecialchars($pickup_id); ?></span>
                            </div>
                            <div class="success-detail-item-portrait">
                                <span class="success-detail-label-portrait"><?php echo $t['estimated_value_label']; ?></span>
                                <span class="success-detail-value-portrait">₱<?php echo number_format($estimated_value, 2); ?></span>
                            </div>
                            <div class="success-detail-item-portrait">
                                <span class="success-detail-label-portrait"><?php echo $t['shipping_method_label']; ?></span>
                                <span class="success-detail-value-portrait"><?php echo htmlspecialchars($selected_shipping['name']); ?></span>
                            </div>
                            <div class="success-detail-item-portrait">
                                <span class="success-detail-label-portrait"><?php echo $t['shipping_fee']; ?></span>
                                <span class="success-detail-value-portrait">₱<?php echo number_format($shipping_fee, 2); ?></span>
                            </div>
                            <div class="success-detail-item-portrait">
                                <span class="success-detail-label-portrait"><?php echo $t['total_amount']; ?></span>
                                <span class="success-detail-value-portrait">₱<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <p><?php echo $t['confirmation_email']; ?></p>
                    
                    <div class="success-actions">
                        <a href="transaction.php" class="btn btn-primary">
                            <i class="fas fa-history"></i> <?php echo $t['view_pickups']; ?>
                        </a>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-home"></i> <?php echo $t['return_dashboard']; ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h2 style="margin-bottom: 20px; color: var(--text-dark); display: flex; align-items: center; gap: 10px;" class="schedule-junk-pickup-text">
                    <i class="fas fa-calendar-alt" style="color: var(--icon-green);"></i> <?php echo $t['schedule_junk_pickup']; ?>
                </h2>
                
                <!-- Step Progress -->
                <div class="pickup-steps">
                    <div class="step-progress-bar">
                        <div class="step-progress-fill" id="stepProgress"></div>
                    </div>
                    <div class="step active" id="step1">
                        <div class="step-number">1</div>
                        <div class="step-label"><?php echo $t['materials']; ?></div>
                    </div>
                    <div class="step" id="step2">
                        <div class="step-number">2</div>
                        <div class="step-label"><?php echo $t['when_where']; ?></div>
                    </div>
                    <div class="step" id="step3">
                        <div class="step-number">3</div>
                        <div class="step-label"><?php echo $t['confirm_pickup']; ?></div>
                    </div>
                </div>
                
                <form method="POST" id="pickupForm">
                    <!-- Step 1: Materials -->
                    <div class="pickup-form-section active" id="section1">
                        <h3 style="margin-bottom: 20px; color: var(--text-dark);"><?php echo $t['what_recycling']; ?></h3>
                        
                        <div id="materialList">
                            <div class="material-row" data-index="0">
                                <select class="material-select" name="materials[0][id]" required>
                                    <option value=""><?php echo $t['select_material']; ?></option>
                                    <?php foreach ($materials as $material): ?>
                                        <option value="<?php echo $material['id']; ?>" 
                                                data-price="<?php echo $material['unit_price']; ?>">
                                            <?php echo htmlspecialchars($material['material_option']); ?> (₱<?php echo number_format($material['unit_price'], 2); ?>/kg)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" class="material-quantity" name="materials[0][quantity]" 
                                       placeholder="<?php echo $t['weight_kg']; ?>" min="0.1" step="0.1" required>
                                <span class="material-value">₱0.00</span>
                                <span class="remove-material" style="visibility: hidden;">
                                    <i class="fas fa-times"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="add-material" id="addMaterial">
                            <i class="fas fa-plus-circle"></i>
                            <span><?php echo $t['add_another_material']; ?></span>
                        </div>
                        
                        <div class="pickup-summary" id="materialSummary">
                            <h4 style="margin-bottom: 15px; color: var(--text-dark);" class="estimated-value-text"><?php echo $t['estimated_value']; ?></h4>
                            <div id="summaryItems">
                                <!-- Items will be added here by JavaScript -->
                            </div>
                            <div class="summary-total">
                                <span><?php echo $t['estimated_total']; ?></span>
                                <span id="estimatedTotal">₱0.00</span>
                            </div>
                            <p style="font-size: 14px; color: var(--text-dark); opacity: 0.7; margin-top: 15px;" class="final-amount-note">
                                <i class="fas fa-info-circle"></i> <?php echo $t['final_amount_note']; ?>
                            </p>
                        </div>
                        
                        <div class="form-actions">
                            <button class="btn btn-outline" disabled><?php echo $t['back']; ?></button>
                            <button class="btn btn-primary" type="button" id="continueButton" onclick="validateMaterials()">
                                <i class="fas fa-arrow-right"></i> <?php echo $t['continue']; ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Step 2: Time & Address -->
                    <div class="pickup-form-section" id="section2">
                        <h3 style="margin-bottom: 20px; color: var(--text-dark);"><?php echo $t['when_where']; ?></h3>
                        
                        <div class="form-group">
                            <label><?php echo $t['pickup_date']; ?></label>
                            <input type="date" id="pickupDate" name="pickup_date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo $t['time_slot']; ?></label>
                            <div class="time-slots">
                                <div class="time-slot" data-value="8:00 - 10:00 AM">8:00 - 10:00 AM</div>
                                <div class="time-slot selected" data-value="10:00 - 12:00 PM">10:00 - 12:00 PM</div>
                                <div class="time-slot" data-value="1:00 - 3:00 PM">1:00 - 3:00 PM</div>
                                <div class="time-slot" data-value="3:00 - 5:00 PM">3:00 - 5:00 PM</div>
                            </div>
                            <input type="hidden" id="timeSlotInput" name="time_slot" value="10:00 - 12:00 PM" required>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo $t['pickup_address']; ?></label>
                            <textarea id="pickupAddress" name="address" required><?php echo htmlspecialchars($user_address); ?></textarea>
                            <button type="button" id="useSavedAddress" class="btn btn-outline" style="margin-top: 10px; padding: 8px 15px; font-size: 13px;">
                                <i class="fas fa-undo"></i> <?php echo $t['use_saved_address']; ?>
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo $t['special_instructions']; ?></label>
                            <textarea id="specialInstructions" name="special_instructions" placeholder="E.g. Gate code, landmarks, specific location on property, etc."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo $t['shipping_method']; ?></label>
                            <div class="shipping-methods" id="shippingMethods">
                                <?php foreach ($shipping_methods as $method): ?>
                                    <div class="shipping-method" 
                                         data-id="<?php echo $method['id']; ?>" 
                                         data-rate="<?php echo $method['rate']; ?>" 
                                         data-max="<?php echo $method['max_weight']; ?>">
                                        <div class="shipping-method-name"><?php echo htmlspecialchars($method['name']); ?></div>
                                        <div class="shipping-method-description"><?php echo htmlspecialchars($method['description']); ?></div>
                                        <div class="shipping-method-rate">₱<?php echo number_format($method['rate'], 2); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="shippingMethodInput" name="shipping_method" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Map</label>
                            <div class="map-container" id="map">
                                <div class="map-overlay">
                                    <strong class="distance-junkshop-text"><?php echo $t['distance_junkshop']; ?>:</strong> 0.74 km
                                </div>
                            </div>
                            <p style="font-size: 14px; color: var(--text-dark); opacity: 0.7; margin-top: 10px;" class="distance-junkshop-text">
                                <i class="fas fa-info-circle"></i> Distance from your location to our junkshop
                            </p>
                        </div>
                        
                        <div class="form-actions">
                            <button class="btn btn-outline" type="button" onclick="prevStep(2)">
                                <i class="fas fa-arrow-left"></i> <?php echo $t['back']; ?>
                            </button>
                            <button class="btn btn-primary" type="button" onclick="validateTimeAndAddress()">
                                <i class="fas fa-arrow-right"></i> <?php echo $t['continue']; ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Step 3: Confirmation -->
                    <div class="pickup-form-section" id="section3">
                        <h3 style="margin-bottom: 20px; color: var(--text-dark);" class="pickup-details-text"><?php echo $t['confirm_pickup']; ?></h3>
                        
                        <div class="pickup-summary">
                            <h4 style="margin-bottom: 20px; color: var(--text-dark);" class="pickup-details-text"><?php echo $t['pickup_details']; ?></h4>
                            
                            <div class="summary-item">
                                <span><strong><?php echo $t['materials']; ?>:</strong></span>
                                <span></span>
                            </div>
                            <div style="margin-left: 15px; margin-bottom: 15px;" id="confirmationMaterials">
                                <!-- Will be filled by JavaScript -->
                            </div>
                            
                            <div class="summary-item">
                                <span class="estimated-value-text"><?php echo $t['estimated_value']; ?>:</span>
                                <span id="confirmationValue">₱0.00</span>
                            </div>
                            
                            <div class="summary-item">
                                <span><?php echo $t['shipping_method']; ?>:</span>
                                <span id="confirmationShipping"></span>
                            </div>
                            
                            <div class="summary-item">
                                <span><?php echo $t['shipping_fee']; ?>:</span>
                                <span id="confirmationShippingFee"></span>
                            </div>
                            
                            <div class="summary-item">
                                <span><?php echo $t['pickup_date']; ?>:</span>
                                <span id="confirmationDate"></span>
                            </div>
                            
                            <div class="summary-item">
                                <span><?php echo $t['time_slot']; ?>:</span>
                                <span id="confirmationTime"></span>
                            </div>
                            
                            <div class="summary-item">
                                <span><?php echo $t['pickup_address']; ?>:</span>
                                <span id="confirmationAddress" style="max-width: 300px; display: inline-block; text-align: right;"></span>
                            </div>
                            
                            <div class="summary-item">
                                <span><?php echo $t['special_instructions']; ?>:</span>
                                <span id="confirmationInstructions" style="font-style: italic;"></span>
                            </div>
                            
                            <div class="summary-total">
                                <span><?php echo $t['total_amount']; ?>:</span>
                                <span id="confirmationTotal"></span>
                            </div>
                        </div>
                        
                        <div class="terms-checkbox">
                            <input type="checkbox" id="confirmTerms" name="confirm_terms" required>
                            <label for="confirmTerms">
                                I agree to the <a href="../terms-of-service.html" target="_blank">Terms of Service</a> and confirm that all materials listed are acceptable for recycling per our guidelines.
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button class="btn btn-outline" type="button" onclick="prevStep(3)">
                                <i class="fas fa-arrow-left"></i> <?php echo $t['back']; ?>
                            </button>
                            <button class="btn btn-primary" id="confirmBtn" type="submit" name="confirm_pickup" disabled>
                                <i class="fas fa-calendar-check"></i> <?php echo $t['confirm_schedule_pickup']; ?>
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

        // Profile avatar click handler
        document.getElementById('userAvatar').addEventListener('click', function() {
            window.location.href = 'settings.php';
        });
        
        // Set minimum date to today
        document.getElementById('pickupDate').min = new Date().toISOString().split('T')[0];
        
        // Time slot selection
        const timeSlots = document.querySelectorAll('.time-slot:not(.disabled)');
        timeSlots.forEach(slot => {
            slot.addEventListener('click', function() {
                if (!this.classList.contains('booked')) {
                    document.querySelector('.time-slot.selected')?.classList.remove('selected');
                    this.classList.add('selected');
                    document.getElementById('timeSlotInput').value = this.dataset.value;
                }
            });
        });
        
        // Shipping method selection
        const shippingMethods = document.querySelectorAll('.shipping-method');
        shippingMethods.forEach(method => {
            method.addEventListener('click', function() {
                if (!this.classList.contains('disabled')) {
                    document.querySelector('.shipping-method.selected')?.classList.remove('selected');
                    this.classList.add('selected');
                    document.getElementById('shippingMethodInput').value = this.dataset.id;
                }
            });
        });
        
        // Initialize map
        let map;
        let userMarker;
        let shopMarker;
        let routeLine;

        function initMap() {
            // Junkshop coordinates (replace with your actual coordinates)
            const shopCoords = [14.696535935739178, 121.08246738663877];
            
            // Initialize map centered on junkshop
            map = L.map('map', {
                zoomControl: false,
                attributionControl: false
            }).setView(shopCoords, 15);
            
            L.tileLayer('https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.jpg?key=gZtMDh9pV46hFgly6xCT', {
                tileSize: 512,
                zoomOffset: -1
            }).addTo(map);
            
            // Custom zoom control
            L.control.zoom({
                position: 'topright'
            }).addTo(map);
            
            // Add junkshop marker with custom icon
            const shopIcon = L.icon({
                iconUrl: 'img/junkshop-marker.png', // Replace with your icon
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });
            
            shopMarker = L.marker(shopCoords, {icon: shopIcon}).addTo(map)
                .bindPopup("<b>JunkValue Junkshop</b><br>Our location")
                .openPopup();
            
            // Try to get user's location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const userCoords = [position.coords.latitude, position.coords.longitude];
                        
                        // Add user marker with custom icon
                        const userIcon = L.icon({
                            iconUrl: 'img/user-marker.png', // Replace with your icon
                            iconSize: [32, 32],
                            iconAnchor: [16, 32],
                            popupAnchor: [0, -32]
                        });
                        
                        userMarker = L.marker(userCoords, {icon: userIcon}).addTo(map)
                            .bindPopup("<b>Your Location</b>")
                            .openPopup();
                        
                        // Add line between user and junkshop
                        routeLine = L.polyline([userCoords, shopCoords], {
                            color: '#D97A41',
                            weight: 3,
                            dashArray: '5, 5',
                            opacity: 0.8
                        }).addTo(map);
                        
                        // Calculate distance
                        const distance = calculateDistance(userCoords[0], userCoords[1], shopCoords[0], shopCoords[1]);
                        
                        // Update distance overlay
                        document.querySelector('.map-overlay').innerHTML = `<strong class="distance-junkshop-text"><?php echo $t['distance_junkshop']; ?>:</strong> ${distance.toFixed(2)} km`;
                        
                        // Update map view to show both locations with padding
                        const bounds = L.latLngBounds([userCoords, shopCoords]);
                        map.fitBounds(bounds, { padding: [50, 50] });
                    },
                    error => {
                        console.error("Geolocation error:", error);
                        // If geolocation fails, just show the junkshop location
                        map.setView(shopCoords, 15);
                        
                        // Update overlay
                        document.querySelector('.map-overlay').innerHTML = '<strong class="distance-junkshop-text">JunkValue Junkshop Location</strong>';
                    }
                );
            } else {
                // Browser doesn't support geolocation
                console.log("Geolocation is not supported by this browser.");
                map.setView(shopCoords, 15);
                
                // Update overlay
                document.querySelector('.map-overlay').innerHTML = '<strong class="distance-junkshop-text">JunkValue Junkshop Location</strong>';
            }
        }

        // Calculate distance between two coordinates in kilometers
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1); 
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2); 
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
            return R * c;
        }

        function deg2rad(deg) {
            return deg * (Math.PI/180);
        }
        
        // Initialize the map when the page loads
        window.addEventListener('load', initMap);
        
        // Check for booked time slots when date changes
        document.getElementById('pickupDate').addEventListener('change', function() {
            const selectedDate = this.value;
            const bookedSlots = <?php echo json_encode($booked_slots); ?>;
            
            // Reset all time slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                if (!slot.classList.contains('disabled')) {
                    slot.classList.remove('booked');
                    slot.removeAttribute('title');
                }
            });
            
            // Check if there are booked slots for this date
            if (bookedSlots[selectedDate]) {
                // Disable already booked slots
                bookedSlots[selectedDate].forEach(bookedSlot => {
                    document.querySelectorAll('.time-slot').forEach(slot => {
                        if (slot.dataset.value === bookedSlot) {
                            slot.classList.add('booked');
                            slot.title = 'This time slot is already booked';
                            
                            // If the selected slot is now booked, select the next available one
                            if (slot.classList.contains('selected')) {
                                const nextAvailable = document.querySelector('.time-slot:not(.booked):not(.disabled)');
                                if (nextAvailable) {
                                    nextAvailable.classList.add('selected');
                                    document.getElementById('timeSlotInput').value = nextAvailable.dataset.value;
                                } else {
                                    // No available slots, clear selection
                                    document.querySelector('.time-slot.selected')?.classList.remove('selected');
                                    document.getElementById('timeSlotInput').value = '';
                                }
                            }
                        }
                    });
                });
            }
        });
        
        // Terms checkbox
        document.getElementById('confirmTerms').addEventListener('change', function() {
            document.getElementById('confirmBtn').disabled = !this.checked;
        });
        
        // Use saved address button
        document.getElementById('useSavedAddress').addEventListener('click', function() {
            document.getElementById('pickupAddress').value = `<?php echo addslashes($user_address); ?>`;
        });
        
        function nextStep(currentStep) {
            // Hide current section and show next
            document.getElementById(`section${currentStep}`).classList.remove('active');
            document.getElementById(`section${currentStep + 1}`).classList.add('active');
            
            // Update step indicators
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.getElementById(`step${currentStep}`).classList.add('completed');
            document.getElementById(`step${currentStep + 1}`).classList.add('active');
            
            // Update progress bar
            document.getElementById('stepProgress').style.width = `${(currentStep / 3) * 100}%`;
            
            // Scroll to top of form
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function prevStep(currentStep) {
            document.getElementById(`section${currentStep}`).classList.remove('active');
            document.getElementById(`section${currentStep - 1}`).classList.add('active');
            
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.getElementById(`step${currentStep - 1}`).classList.add('active');
            document.getElementById(`step${currentStep - 1}`).classList.remove('completed');
            
            // Update progress bar
            document.getElementById('stepProgress').style.width = `${((currentStep - 2) / 3) * 100}%`;
            
            // Scroll to top of form
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Add material row
        let materialCounter = 1;
        document.getElementById('addMaterial').addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'material-row';
            newRow.dataset.index = materialCounter;
            newRow.innerHTML = `
                <select class="material-select" name="materials[${materialCounter}][id]" required>
                    <option value=""><?php echo $t['select_material']; ?></option>
                    <?php foreach ($materials as $material): ?>
                        <option value="<?php echo $material['id']; ?>" 
                                data-price="<?php echo $material['unit_price']; ?>">
                            <?php echo htmlspecialchars($material['material_option']); ?> (₱<?php echo number_format($material['unit_price'], 2); ?>/kg)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" class="material-quantity" name="materials[${materialCounter}][quantity]" 
                       placeholder="<?php echo $t['weight_kg']; ?>" min="0.1" step="0.1" required>
                <span class="material-value">₱0.00</span>
                <span class="remove-material">
                    <i class="fas fa-times"></i>
                </span>
            `;
            document.getElementById('materialList').appendChild(newRow);
            materialCounter++;
            
            // Add event listeners to the new row
            addMaterialEventListeners(newRow);
        });
        
        // Function to add event listeners to a material row
        function addMaterialEventListeners(row) {
            const select = row.querySelector('.material-select');
            const input = row.querySelector('.material-quantity');
            const valueSpan = row.querySelector('.material-value');
            const removeBtn = row.querySelector('.remove-material');
            
            // Calculate value when material or quantity changes
            select.addEventListener('change', function() {
                calculateRowValue(row);
                updateShippingMethods();
            });
            
            input.addEventListener('input', function() {
                calculateRowValue(row);
                updateShippingMethods();
            });
            
            // Remove row when X is clicked
            removeBtn.addEventListener('click', function() {
                row.remove();
                calculateTotalValue();
                updateShippingMethods();
            });
            
            function calculateRowValue() {
                const price = parseFloat(select.options[select.selectedIndex]?.dataset.price) || 0;
                const quantity = parseFloat(input.value) || 0;
                const total = price * quantity;
                valueSpan.textContent = `₱${total.toFixed(2)}`;
                calculateTotalValue();
            }
        }
        
        // Update shipping methods based on total weight
        function updateShippingMethods() {
            const rows = document.querySelectorAll('.material-row');
            let totalWeight = 0;
            
            rows.forEach(row => {
                const input = row.querySelector('.material-quantity');
                const quantity = parseFloat(input.value) || 0;
                totalWeight += quantity;
            });
            
            document.querySelectorAll('.shipping-method').forEach(method => {
                const maxWeight = parseFloat(method.dataset.max);
                
                if (totalWeight > maxWeight) {
                    method.classList.add('disabled');
                    method.classList.remove('selected');
                } else {
                    method.classList.remove('disabled');
                }
            });
            
            // If selected method is now disabled, clear selection
            const selectedMethod = document.querySelector('.shipping-method.selected');
            if (selectedMethod && selectedMethod.classList.contains('disabled')) {
                selectedMethod.classList.remove('selected');
                document.getElementById('shippingMethodInput').value = '';
            }
        }
        
        // Function to calculate total value of all materials
        function calculateTotalValue() {
            const rows = document.querySelectorAll('.material-row');
            let items = [];
            
            rows.forEach(row => {
                const select = row.querySelector('.material-select');
                const input = row.querySelector('.material-quantity');
                const materialId = select.value;
                const quantity = parseFloat(input.value) || 0;
                
                if (materialId && quantity > 0) {
                    const materialName = select.options[select.selectedIndex].text.split(' (')[0];
                    const price = parseFloat(select.options[select.selectedIndex].dataset.price) || 0;
                    const itemTotal = quantity * price;
                    
                    items.push({
                        name: materialName,
                        quantity: quantity,
                        price: price,
                        total: itemTotal
                    });
                }
            });
            
            // Update summary items
            const summaryItems = document.getElementById('summaryItems');
            summaryItems.innerHTML = '';
            
            let grandTotal = 0;
            items.forEach(item => {
                grandTotal += item.total;
                const itemElement = document.createElement('div');
                itemElement.className = 'summary-item';
                itemElement.innerHTML = `
                    <span>${item.name} (${item.quantity}kg)</span>
                    <span>₱${item.total.toFixed(2)}</span>
                `;
                summaryItems.appendChild(itemElement);
            });
            
            // Update grand total
            document.getElementById('estimatedTotal').textContent = `₱${grandTotal.toFixed(2)}`;
        }
        
        function validateMaterials() {
            let valid = true;
            const rows = document.querySelectorAll('.material-row');
            
            // Reset error states
            document.querySelectorAll('.material-select, .material-quantity').forEach(el => {
                el.classList.remove('invalid');
            });
            
            // Remove any existing error messages
            const existingError = document.querySelector('#section1 .error-message');
            if (existingError) existingError.remove();
            
            // Check if at least one material is selected with quantity
            let hasValidMaterial = false;
            rows.forEach(row => {
                const select = row.querySelector('.material-select');
                const input = row.querySelector('.material-quantity');
                
                if (!select.value || !input.value || parseFloat(input.value) <= 0) {
                    valid = false;
                    select.classList.add('invalid');
                    input.classList.add('invalid');
                } else {
                    hasValidMaterial = true;
                }
            });
            
            if (!valid || !hasValidMaterial) {
                // Create and show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerHTML = '<p><i class="fas fa-exclamation-circle"></i> Please select at least one material and enter a valid quantity (greater than 0)</p>';
                document.querySelector('#section1 h3').insertAdjacentElement('afterend', errorDiv);
                
                // Scroll to error message
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            
            // Proceed to next step if valid
            nextStep(1);
            return true;
        }
        
        function validateTimeAndAddress() {
            // Validate time and address
            const date = document.getElementById('pickupDate');
            const address = document.getElementById('pickupAddress');
            const shippingMethod = document.getElementById('shippingMethodInput');
            
            // Reset error states
            date.classList.remove('invalid');
            address.classList.remove('invalid');
            shippingMethod.classList.remove('invalid');
            
            // Remove any existing error messages
            const existingError = document.querySelector('#section2 .error-message');
            if (existingError) existingError.remove();
            
            let isValid = true;
            let errorMessage = '';
            
            if (!date.value) {
                date.classList.add('invalid');
                errorMessage += '<p><i class="fas fa-exclamation-circle"></i> Please select a pickup date</p>';
                isValid = false;
            }
            
            if (!document.getElementById('timeSlotInput').value) {
                errorMessage += '<p><i class="fas fa-exclamation-circle"></i> Please select a time slot</p>';
                isValid = false;
            }
            
            // Check if selected time slot is booked
            const selectedDate = date.value;
            const selectedSlot = document.getElementById('timeSlotInput').value;
            const bookedSlots = <?php echo json_encode($booked_slots); ?>;
            
            if (bookedSlots[selectedDate] && bookedSlots[selectedDate].includes(selectedSlot)) {
                errorMessage += '<p><i class="fas fa-exclamation-circle"></i> The selected time slot is already booked. Please choose another time.</p>';
                isValid = false;
            }
            
            if (!address.value) {
                address.classList.add('invalid');
                errorMessage += '<p><i class="fas fa-exclamation-circle"></i> Please enter a pickup address</p>';
                isValid = false;
            }
            
            if (!shippingMethod.value) {
                shippingMethod.classList.add('invalid');
                errorMessage += '<p><i class="fas fa-exclamation-circle"></i> Please select a shipping method</p>';
                isValid = false;
            }
            
            if (!isValid) {
                // Create and show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerHTML = errorMessage;
                document.querySelector('#section2 h3').insertAdjacentElement('afterend', errorDiv);
                
                // Scroll to error message
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            
            updateConfirmationDetails();
            nextStep(2);
        }
        
        // Update confirmation details
        function updateConfirmationDetails() {
            // Materials
            const materialsHtml = [];
            const materialItems = document.querySelectorAll('.material-row');
            materialItems.forEach(item => {
                const select = item.querySelector('.material-select');
                const input = item.querySelector('.material-quantity');
                if (select.value && input.value) {
                    const materialName = select.options[select.selectedIndex].text.split(' (')[0];
                    materialsHtml.push(`• ${input.value}kg ${materialName}`);
                }
            });
            document.getElementById('confirmationMaterials').innerHTML = materialsHtml.join('<br>');
            
            // Estimated value
            document.getElementById('confirmationValue').textContent = document.getElementById('estimatedTotal').textContent;
            
            // Shipping method
            const selectedMethod = document.querySelector('.shipping-method.selected');
            if (selectedMethod) {
                const methodName = selectedMethod.querySelector('.shipping-method-name').textContent;
                const methodRate = selectedMethod.dataset.rate;
                
                document.getElementById('confirmationShipping').textContent = methodName;
                document.getElementById('confirmationShippingFee').textContent = `₱${parseFloat(methodRate).toFixed(2)}`;
            }
            
            // Date
            const date = new Date(document.getElementById('pickupDate').value);
            document.getElementById('confirmationDate').textContent = date.toLocaleDateString('en-US', { 
                year: 'numeric', month: 'long', day: 'numeric' 
            });
            
            // Time
            document.getElementById('confirmationTime').textContent = document.getElementById('timeSlotInput').value;
            
            // Address
            document.getElementById('confirmationAddress').textContent = document.getElementById('pickupAddress').value;
            
            // Instructions
            const instructions = document.getElementById('specialInstructions').value || 'None provided';
            document.getElementById('confirmationInstructions').textContent = instructions;
            
            // Total amount
            const estimatedValue = parseFloat(document.getElementById('estimatedTotal').textContent.replace('₱', '')) || 0;
            const shippingFee = selectedMethod ? parseFloat(selectedMethod.dataset.rate) : 0;
            const totalAmount = estimatedValue + shippingFee;
            
            document.getElementById('confirmationTotal').textContent = `₱${totalAmount.toFixed(2)}`;
        }
        
        // Add event listeners to existing material selects/inputs
        document.querySelectorAll('#materialList .material-select, #materialList .material-quantity').forEach(el => {
            if (el.classList.contains('material-select') || el.classList.contains('material-quantity')) {
                el.addEventListener('change', function() {
                    const row = this.closest('.material-row');
                    calculateRowValue(row);
                    updateShippingMethods();
                });
                el.addEventListener('input', function() {
                    const row = this.closest('.material-row');
                    calculateRowValue(row);
                    updateShippingMethods();
                });
            }
        });
        
        // Initialize calculation
        calculateTotalValue();
        updateShippingMethods();
        
        // Helper function to calculate value for a single row
        function calculateRowValue(row) {
            const select = row.querySelector('.material-select');
            const input = row.querySelector('.material-quantity');
            const valueSpan = row.querySelector('.material-value');
            
            const price = parseFloat(select.options[select.selectedIndex]?.dataset.price) || 0;
            const quantity = parseFloat(input.value) || 0;
            const total = price * quantity;
            valueSpan.textContent = `₱${total.toFixed(2)}`;
            calculateTotalValue();
        }

        // Create confetti effect for success message
        function createConfetti() {
            const successMessage = document.getElementById('successMessage');
            if (!successMessage) return;
            
            const colors = ['#6A7F46', '#708B4C', '#D97A41', '#4A89DC', '#F2EAD3'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 5 + 's';
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = confetti.style.width;
                
                successMessage.appendChild(confetti);
            }
        }

        // Create confetti when success message is shown
        <?php if ($success): ?>
            window.addEventListener('load', createConfetti);
        <?php endif; ?>
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>