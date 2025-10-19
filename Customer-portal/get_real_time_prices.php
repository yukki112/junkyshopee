<?php
header('Content-Type: application/json');
require_once 'db_connection.php';

try {
    // Get current prices with real-time trend calculations
    $prices = [];
    $price_query = "SELECT m.*, 
                    COALESCE(mp.buying_price, m.unit_price) as current_price,
                    DATE(m.updated_at) as last_updated
                    FROM materials m 
                    LEFT JOIN market_prices mp ON m.id = mp.material_id 
                    WHERE m.status = 'active' 
                    ORDER BY m.material_option";
    
    $price_result = mysqli_query($conn, $price_query);
    
    if ($price_result) {
        while ($row = mysqli_fetch_assoc($price_result)) {
            // Simulate real-time price fluctuations (±2%)
            $base_price = $row['current_price'] ?? $row['unit_price'];
            $fluctuation = (rand(-200, 200) / 10000); // ±2% fluctuation
            $new_price = $base_price * (1 + $fluctuation);
            
            // Determine trend based on price change
            $trend = 'equal';
            $change_amount = abs($new_price - $base_price);
            $change_percent = ($base_price > 0) ? (($new_price - $base_price) / $base_price) * 100 : 0;
            
            if ($new_price > $base_price) {
                $trend = 'up';
            } elseif ($new_price < $base_price) {
                $trend = 'down';
            }
            
            // Update database with new price and trend
            $update_query = "UPDATE materials SET 
                           unit_price = ?, 
                           trend_direction = ?, 
                           trend_change = ?,
                           updated_at = NOW() 
                           WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "dsdi", $new_price, $trend, $change_amount, $row['id']);
            mysqli_stmt_execute($update_stmt);
            
            $prices[] = [
                'id' => $row['id'],
                'material_option' => $row['material_option'],
                'current_price' => $new_price,
                'trend' => $trend,
                'change_amount' => $change_amount,
                'change_percent' => abs($change_percent),
                'last_updated' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    // Generate updated chart data
    $material_colors = [
        'Copper Wire' => '#D97A41',
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
    
    $chart_data = [
        'metals' => [],
        'electronics' => [],
        'plastics' => [],
        'others' => []
    ];
    
    foreach ($prices as $material) {
        // Generate sample historical data for charts
        $history_data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('M j', strtotime("-$i days"));
            $fluctuation = (rand(-10, 10) / 100);
            $history_data[] = max(1, $material['current_price'] * (1 + $fluctuation));
        }
        
        $chart_item = [
            'label' => $material['material_option'],
            'data' => $history_data,
            'borderColor' => $material_colors[$material['material_option']] ?? '#6A7F46',
            'backgroundColor' => 'rgba(106, 127, 70, 0.1)',
            'borderWidth' => 2,
            'tension' => 0.3,
            'fill' => true
        ];
        
        // Categorize for different charts
        if (in_array($material['material_option'], ['Copper Wire', 'Aluminum Cans', 'Iron Scrap', 'Stainless Steel', 'Steel'])) {
            $chart_data['metals'][] = $chart_item;
        } elseif (in_array($material['material_option'], ['E-Waste', 'Computer Parts', 'Batteries'])) {
            $chart_data['electronics'][] = $chart_item;
        } elseif (in_array($material['material_option'], ['PET Bottles', 'Glass Bottles'])) {
            $chart_data['plastics'][] = $chart_item;
        } else {
            $chart_data['others'][] = $chart_item;
        }
    }
    
    echo json_encode([
        'success' => true,
        'prices' => $prices,
        'chartData' => $chart_data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
