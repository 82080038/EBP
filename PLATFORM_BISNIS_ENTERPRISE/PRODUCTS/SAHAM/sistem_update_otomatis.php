<?php
/**
 * SISTEM UPDATE OTOMATIS HARGA SAHAM DAN DATA FUNDAMENTAL
 * File: sistem_update_otomatis.php
 * 
 * Fitur:
 * - Update harga real-time dari API
 * - Update data fundamental
 * - Update data IPO dan listing
 * - Logging aktivitas
 * - Error handling
 */

// Konfigurasi Database
$host = 'localhost';
$dbname = 'db_saham';
$username = 'root';
$password = '';

// Konfigurasi API
$api_config = [
    'alpha_vantage' => [
        'api_key' => 'YOUR_ALPHA_VANTAGE_API_KEY',
        'base_url' => 'https://www.alphavantage.co/query'
    ],
    'yahoo_finance' => [
        'base_url' => 'https://query1.finance.yahoo.com/v8/finance/chart'
    ],
    'idx_api' => [
        'base_url' => 'https://api.idx.co.id'
    ]
];

// Koneksi Database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    log_error("Database connection failed: " . $e->getMessage());
    exit;
}

/**
 * Fungsi untuk mendapatkan harga real-time dari API
 */
function get_real_time_price($symbol, $api_type = 'yahoo_finance') {
    global $api_config;
    
    switch($api_type) {
        case 'yahoo_finance':
            return get_yahoo_finance_price($symbol);
        case 'alpha_vantage':
            return get_alpha_vantage_price($symbol);
        case 'idx_api':
            return get_idx_api_price($symbol);
        default:
            return null;
    }
}

/**
 * Yahoo Finance API
 */
function get_yahoo_finance_price($symbol) {
    global $api_config;
    
    $url = $api_config['yahoo_finance']['base_url'] . "/$symbol.JK";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['chart']['result'][0]['meta']['regularMarketPrice'])) {
        return [
            'current_price' => $data['chart']['result'][0]['meta']['regularMarketPrice'],
            'previous_close' => $data['chart']['result'][0]['meta']['previousClose'],
            'day_high' => $data['chart']['result'][0]['meta']['dayHigh'],
            'day_low' => $data['chart']['result'][0]['meta']['dayLow'],
            'volume' => $data['chart']['result'][0]['meta']['regularMarketVolume']
        ];
    }
    
    return null;
}

/**
 * Alpha Vantage API
 */
function get_alpha_vantage_price($symbol) {
    global $api_config;
    
    $url = $api_config['alpha_vantage']['base_url'] . "?function=GLOBAL_QUOTE&symbol=$symbol.JK&apikey=" . $api_config['alpha_vantage']['api_key'];
    
    $response = @file_get_contents($url);
    
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['Global Quote'])) {
        $quote = $data['Global Quote'];
        return [
            'current_price' => floatval($quote['05. price']),
            'previous_close' => floatval($quote['08. previous close']),
            'day_high' => floatval($quote['03. high']),
            'day_low' => floatval($quote['04. low']),
            'volume' => intval($quote['06. volume'])
        ];
    }
    
    return null;
}

/**
 * IDX API (Mock - karena API resmi mungkin memerlukan autentikasi)
 */
function get_idx_api_price($symbol) {
    // Mock data untuk demo
    return [
        'current_price' => rand(100, 10000) / 100,
        'previous_close' => rand(100, 10000) / 100,
        'day_high' => rand(100, 10000) / 100,
        'day_low' => rand(100, 10000) / 100,
        'volume' => rand(1000000, 100000000)
    ];
}

/**
 * Update harga saham
 */
function update_stock_prices($pdo) {
    $stmt = $pdo->query("SELECT id, symbol FROM stocks WHERE is_active = 1");
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated_count = 0;
    $error_count = 0;
    
    foreach ($stocks as $stock) {
        $price_data = get_real_time_price($stock['symbol']);
        
        if ($price_data) {
            try {
                $update_stmt = $pdo->prepare("
                    UPDATE stocks 
                    SET current_price = ?, previous_close = ?, day_high = ?, day_low = ?, volume = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $update_stmt->execute([
                    $price_data['current_price'],
                    $price_data['previous_close'],
                    $price_data['day_high'],
                    $price_data['day_low'],
                    $price_data['volume'],
                    $stock['id']
                ]);
                
                // Insert ke price_history
                $history_stmt = $pdo->prepare("
                    INSERT INTO price_history (stock_id, price, volume, high, low, open_price, close_price, date, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), NOW())
                    ON DUPLICATE KEY UPDATE
                    price = VALUES(price),
                    volume = VALUES(volume),
                    high = VALUES(high),
                    low = VALUES(low),
                    close_price = VALUES(close_price),
                    updated_at = NOW()
                ");
                
                $history_stmt->execute([
                    $stock['id'],
                    $price_data['current_price'],
                    $price_data['volume'],
                    $price_data['day_high'],
                    $price_data['day_low'],
                    $price_data['previous_close'],
                    $price_data['current_price']
                ]);
                
                $updated_count++;
                log_info("Updated price for {$stock['symbol']}: {$price_data['current_price']}");
                
            } catch (Exception $e) {
                $error_count++;
                log_error("Failed to update {$stock['symbol']}: " . $e->getMessage());
            }
        } else {
            $error_count++;
            log_error("Failed to get price data for {$stock['symbol']}");
        }
        
        // Delay untuk menghindari rate limiting
        usleep(100000); // 0.1 detik
    }
    
    return ['updated' => $updated_count, 'errors' => $error_count];
}

/**
 * Update data fundamental
 */
function update_fundamental_data($pdo) {
    $stmt = $pdo->query("SELECT id, symbol FROM stocks WHERE is_active = 1");
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated_count = 0;
    
    foreach ($stocks as $stock) {
        // Mock data fundamental - dalam implementasi nyata, ambil dari API
        $fundamental_data = [
            'pe_ratio' => rand(5, 50),
            'pb_ratio' => rand(1, 10),
            'roe' => rand(5, 25),
            'roa' => rand(2, 15),
            'debt_to_equity' => rand(0, 2),
            'current_ratio' => rand(1, 3),
            'dividend_yield' => rand(0, 5)
        ];
        
        try {
            $update_stmt = $pdo->prepare("
                INSERT INTO financial_ratios (stock_id, period_year, period_quarter, pe_ratio, pb_ratio, roe, roa, debt_to_equity, current_ratio, dividend_yield, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                pe_ratio = VALUES(pe_ratio),
                pb_ratio = VALUES(pb_ratio),
                roe = VALUES(roe),
                roa = VALUES(roa),
                debt_to_equity = VALUES(debt_to_equity),
                current_ratio = VALUES(current_ratio),
                dividend_yield = VALUES(dividend_yield),
                updated_at = NOW()
            ");
            
            $update_stmt->execute([
                $stock['id'],
                date('Y'),
                ceil(date('n') / 3), // Quarter
                $fundamental_data['pe_ratio'],
                $fundamental_data['pb_ratio'],
                $fundamental_data['roe'],
                $fundamental_data['roa'],
                $fundamental_data['debt_to_equity'],
                $fundamental_data['current_ratio'],
                $fundamental_data['dividend_yield']
            ]);
            
            $updated_count++;
            log_info("Updated fundamental data for {$stock['symbol']}");
            
        } catch (Exception $e) {
            log_error("Failed to update fundamental data for {$stock['symbol']}: " . $e->getMessage());
        }
    }
    
    return $updated_count;
}

/**
 * Update data IPO dan listing
 */
function update_ipo_data($pdo) {
    // Mock data IPO - dalam implementasi nyata, ambil dari API atau web scraping
    $ipo_data = [
        ['symbol' => 'BBCA', 'ipo_date' => '2000-05-15', 'ipo_price' => 500.00],
        ['symbol' => 'BBRI', 'ipo_date' => '2003-11-10', 'ipo_price' => 200.00],
        ['symbol' => 'BMRI', 'ipo_date' => '2003-07-15', 'ipo_price' => 300.00],
        // Tambahkan data IPO lainnya
    ];
    
    $updated_count = 0;
    
    foreach ($ipo_data as $ipo) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO ipo_data (stock_id, ipo_date, ipo_price, listing_date, underwriter, created_at)
                SELECT id, ?, ?, ?, 'Mandiri Sekuritas', NOW()
                FROM stocks WHERE symbol = ?
                ON DUPLICATE KEY UPDATE
                ipo_date = VALUES(ipo_date),
                ipo_price = VALUES(ipo_price),
                listing_date = VALUES(listing_date),
                updated_at = NOW()
            ");
            
            $stmt->execute([
                $ipo['ipo_date'],
                $ipo['ipo_price'],
                $ipo['ipo_date'],
                $ipo['symbol']
            ]);
            
            $updated_count++;
            log_info("Updated IPO data for {$ipo['symbol']}");
            
        } catch (Exception $e) {
            log_error("Failed to update IPO data for {$ipo['symbol']}: " . $e->getMessage());
        }
    }
    
    return $updated_count;
}

/**
 * Update technical indicators
 */
function update_technical_indicators($pdo) {
    $stmt = $pdo->query("
        SELECT s.id, s.symbol, ph.price, ph.volume, ph.high, ph.low, ph.open_price, ph.close_price
        FROM stocks s
        JOIN price_history ph ON s.id = ph.stock_id
        WHERE s.is_active = 1 AND ph.date = CURDATE()
    ");
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated_count = 0;
    
    foreach ($stocks as $stock) {
        // Mock technical indicators - dalam implementasi nyata, hitung dari data historis
        $indicators = [
            'sma_5' => $stock['price'] * (1 + rand(-5, 5) / 100),
            'sma_20' => $stock['price'] * (1 + rand(-10, 10) / 100),
            'rsi_14' => rand(20, 80),
            'macd' => rand(-100, 100) / 100,
            'bollinger_upper' => $stock['price'] * 1.1,
            'bollinger_lower' => $stock['price'] * 0.9
        ];
        
        try {
            $update_stmt = $pdo->prepare("
                INSERT INTO technical_indicators (stock_id, date, sma_5, sma_20, rsi_14, macd, bollinger_upper, bollinger_lower, created_at)
                VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                sma_5 = VALUES(sma_5),
                sma_20 = VALUES(sma_20),
                rsi_14 = VALUES(rsi_14),
                macd = VALUES(macd),
                bollinger_upper = VALUES(bollinger_upper),
                bollinger_lower = VALUES(bollinger_lower),
                updated_at = NOW()
            ");
            
            $update_stmt->execute([
                $stock['id'],
                $indicators['sma_5'],
                $indicators['sma_20'],
                $indicators['rsi_14'],
                $indicators['macd'],
                $indicators['bollinger_upper'],
                $indicators['bollinger_lower']
            ]);
            
            $updated_count++;
            
        } catch (Exception $e) {
            log_error("Failed to update technical indicators for {$stock['symbol']}: " . $e->getMessage());
        }
    }
    
    return $updated_count;
}

/**
 * Logging functions
 */
function log_info($message) {
    $log = "[" . date('Y-m-d H:i:s') . "] INFO: $message" . PHP_EOL;
    file_put_contents('logs/update_' . date('Y-m-d') . '.log', $log, FILE_APPEND | LOCK_EX);
    echo $log;
}

function log_error($message) {
    $log = "[" . date('Y-m-d H:i:s') . "] ERROR: $message" . PHP_EOL;
    file_put_contents('logs/update_' . date('Y-m-d') . '.log', $log, FILE_APPEND | LOCK_EX);
    echo $log;
}

/**
 * Main execution
 */
function main() {
    global $pdo;
    
    // Buat direktori logs jika belum ada
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    log_info("Starting automatic update process...");
    
    $start_time = microtime(true);
    
    // Update harga saham
    log_info("Updating stock prices...");
    $price_result = update_stock_prices($pdo);
    log_info("Price update completed: {$price_result['updated']} updated, {$price_result['errors']} errors");
    
    // Update data fundamental
    log_info("Updating fundamental data...");
    $fundamental_count = update_fundamental_data($pdo);
    log_info("Fundamental data update completed: $fundamental_count updated");
    
    // Update data IPO
    log_info("Updating IPO data...");
    $ipo_count = update_ipo_data($pdo);
    log_info("IPO data update completed: $ipo_count updated");
    
    // Update technical indicators
    log_info("Updating technical indicators...");
    $technical_count = update_technical_indicators($pdo);
    log_info("Technical indicators update completed: $technical_count updated");
    
    $end_time = microtime(true);
    $execution_time = round($end_time - $start_time, 2);
    
    log_info("Automatic update process completed in {$execution_time} seconds");
}

// Jalankan jika dipanggil langsung
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    main();
}

?>
