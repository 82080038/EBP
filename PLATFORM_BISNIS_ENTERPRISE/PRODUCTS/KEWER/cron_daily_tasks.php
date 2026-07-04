<?php
/**
 * Scheduled Task: Daily Tasks for Kewer Application
 * 
 * This script should be run daily via cron job to automate:
 * 1. Auto-create penagihan records for overdue installments
 * 2. Calculate daily penalties (denda)
 * 3. Send notifications for due installments
 * 4. Update kolektibilitas (credit risk classification)
 * 5. Auto-tag macet loans
 * 
 * Usage: php cron_daily_tasks.php
 * Cron: 0 0 * * * php /path/to/kewer/cron_daily_tasks.php
 */

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Include required files
require_once __DIR__ . '/config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/database_class.php';
require_once BASE_PATH . '/includes/business_logic.php';

// Log execution
$log_file = BASE_PATH . '/logs/cron_daily_' . date('Y-m-d') . '.log';
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    echo $log_entry;
}

logMessage("=== Starting Daily Scheduled Tasks ===");

try {
    // Task 1: Auto-create penagihan for overdue installments
    logMessage("Task 1: Auto-create penagihan for overdue installments...");
    $penagihan_result = autoCreatePenagihanOverdue();
    logMessage("Result: {$penagihan_result['message']} (Count: {$penagihan_result['count']})");
    
    // Task 2: Calculate daily penalties (denda)
    logMessage("Task 2: Calculate daily penalties...");
    $denda_updated = hitungDendaHarian();
    logMessage("Result: Updated {$denda_updated} penalty records");
    
    // Task 3: Send notifications for due installments
    logMessage("Task 3: Send notifications for due installments...");
    $notif_sent = kirimNotifJatuhTempo();
    logMessage("Result: Sent {$notif_sent} notifications");
    
    // Task 4: Update kolektibilitas
    logMessage("Task 4: Update kolektibilitas...");
    $kol_updated = hitungKolektibilitasSemua();
    logMessage("Result: Updated {$kol_updated} loan kolektibilitas records");
    
    // Task 5: Auto-tag macet loans
    logMessage("Task 5: Auto-tag macet loans...");
    $macet_tagged = autoTandaiMacet();
    logMessage("Result: Tagged {$macet_tagged} loans as macet");
    
    logMessage("=== Daily Scheduled Tasks Completed Successfully ===");
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    logMessage("=== Daily Scheduled Tasks Failed ===");
    exit(1);
}

logMessage("");
