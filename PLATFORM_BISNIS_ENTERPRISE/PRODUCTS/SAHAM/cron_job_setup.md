# 🕐 Setup Cron Job untuk Update Otomatis

## 1. **Cron Job untuk Windows (Task Scheduler)**

### **Setup Task Scheduler:**
1. Buka **Task Scheduler** (taskschd.msc)
2. Klik **Create Basic Task**
3. **Name**: "Update Saham Data"
4. **Description**: "Update harga saham dan data fundamental setiap hari"
5. **Trigger**: Daily
6. **Start**: 16:30 (setelah pasar tutup)
7. **Action**: Start a program
8. **Program**: `php.exe`
9. **Arguments**: `D:\xampp\htdocs\saham\sistem_update_otomatis.php`

### **PowerShell Script:**
```powershell
# update_saham.ps1
$phpPath = "D:\xampp\php\php.exe"
$scriptPath = "D:\xampp\htdocs\saham\sistem_update_otomatis.php"
& $phpPath $scriptPath
```

## 2. **Cron Job untuk Linux/Mac**

### **Setup Cron:**
```bash
# Edit crontab
crontab -e

# Tambahkan baris berikut:
# Update setiap hari jam 16:30 (setelah pasar tutup)
30 16 * * 1-5 /usr/bin/php /path/to/saham/sistem_update_otomatis.php

# Update setiap 15 menit saat jam trading (09:00-16:00)
*/15 9-16 * * 1-5 /usr/bin/php /path/to/saham/sistem_update_otomatis.php
```

## 3. **Batch File untuk Windows**

### **update_saham.bat:**
```batch
@echo off
cd /d D:\xampp\htdocs\saham
D:\xampp\php\php.exe sistem_update_otomatis.php
echo Update completed at %date% %time%
pause
```

## 4. **Konfigurasi Database untuk Update**

### **Tabel yang Diperlukan:**
```sql
-- Tabel untuk menyimpan log update
CREATE TABLE update_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    update_type VARCHAR(50) NOT NULL,
    status ENUM('SUCCESS', 'ERROR', 'PARTIAL') NOT NULL,
    records_updated INT DEFAULT 0,
    error_message TEXT,
    execution_time DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk menyimpan data IPO
CREATE TABLE ipo_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stock_id INT NOT NULL,
    ipo_date DATE NOT NULL,
    ipo_price DECIMAL(10,2) NOT NULL,
    listing_date DATE NOT NULL,
    underwriter VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE
);
```

## 5. **Monitoring dan Alerting**

### **Email Notification:**
```php
// Tambahkan di sistem_update_otomatis.php
function send_email_notification($subject, $message) {
    $to = 'admin@yourdomain.com';
    $headers = 'From: system@yourdomain.com';
    mail($to, $subject, $message, $headers);
}
```

### **Slack Notification:**
```php
function send_slack_notification($message) {
    $webhook_url = 'YOUR_SLACK_WEBHOOK_URL';
    $data = ['text' => $message];
    $options = [
        'http' => [
            'header' => "Content-type: application/json",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    file_get_contents($webhook_url, false, stream_context_create($options));
}
```

## 6. **Error Handling dan Recovery**

### **Retry Mechanism:**
```php
function retry_update($function, $max_retries = 3) {
    for ($i = 0; $i < $max_retries; $i++) {
        try {
            return $function();
        } catch (Exception $e) {
            if ($i == $max_retries - 1) {
                throw $e;
            }
            sleep(5); // Wait 5 seconds before retry
        }
    }
}
```

## 7. **Performance Optimization**

### **Batch Processing:**
```php
// Update dalam batch untuk performa lebih baik
function update_stocks_batch($pdo, $batch_size = 50) {
    $offset = 0;
    do {
        $stmt = $pdo->prepare("
            SELECT id, symbol FROM stocks 
            WHERE is_active = 1 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$batch_size, $offset]);
        $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($stocks)) break;
        
        // Process batch
        foreach ($stocks as $stock) {
            // Update logic here
        }
        
        $offset += $batch_size;
    } while (count($stocks) == $batch_size);
}
```

## 8. **Testing dan Debugging**

### **Test Script:**
```php
// test_update.php
<?php
require_once 'sistem_update_otomatis.php';

// Test dengan 5 saham saja
$test_stocks = ['BBCA', 'BBRI', 'BMRI', 'TLKM', 'ASII'];

foreach ($test_stocks as $symbol) {
    $price_data = get_real_time_price($symbol);
    echo "Symbol: $symbol, Price: " . ($price_data['current_price'] ?? 'N/A') . "\n";
}
?>
```

## 9. **Backup dan Recovery**

### **Database Backup:**
```bash
# Backup sebelum update
mysqldump -u root -p db_saham > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore jika ada masalah
mysql -u root -p db_saham < backup_20241201_163000.sql
```

## 10. **Monitoring Dashboard**

### **Status Check:**
```php
// status_check.php
<?php
$pdo = new PDO("mysql:host=localhost;dbname=db_saham", "root", "");

// Check last update
$stmt = $pdo->query("SELECT MAX(updated_at) as last_update FROM stocks");
$last_update = $stmt->fetchColumn();

// Check error logs
$error_count = $pdo->query("SELECT COUNT(*) FROM update_logs WHERE status = 'ERROR' AND DATE(created_at) = CURDATE()")->fetchColumn();

echo "Last Update: $last_update\n";
echo "Errors Today: $error_count\n";
?>
```

## 📋 **Checklist Setup:**

- [ ] Install PHP dan MySQL
- [ ] Setup database dengan tabel yang diperlukan
- [ ] Konfigurasi API keys
- [ ] Test script update manual
- [ ] Setup cron job / task scheduler
- [ ] Setup monitoring dan alerting
- [ ] Test error handling
- [ ] Setup backup strategy
- [ ] Monitor performa dan optimasi

## ⚠️ **Peringatan:**

1. **Rate Limiting**: Pastikan tidak melebihi limit API
2. **Error Handling**: Selalu handle error dengan baik
3. **Backup**: Backup database sebelum update besar
4. **Monitoring**: Monitor log dan performa secara berkala
5. **Testing**: Test di environment development dulu
