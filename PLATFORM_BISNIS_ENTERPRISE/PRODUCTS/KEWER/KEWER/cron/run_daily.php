<?php
/**
 * Kewer Daily Cron Job
 * Jalankan via crontab: 0 6 * * * php /opt/lampp/htdocs/kewer/cron/run_daily.php >> /opt/lampp/htdocs/kewer/cron/logs/daily.log 2>&1
 *
 * Tugas:
 * 1. autoTandaiMacet()         — tandai pinjaman > 90 hari telat
 * 2. hitungHariTunggakan()      — update hari_tunggakan & kolektibilitas OJK
 * 3. hitungDendaHarian()        — update denda angsuran jatuh tempo
 * 4. kirimPengingatJatuhTempo() — notifikasi internal H-1, H-0
 * 5. kirimWaPengingat()         — kirim WA ke nasabah (jika WA dikonfigurasi)
 */

define('CLI_RUN', true);
require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/business_logic.php';
require_once BASE_PATH . '/includes/wa_notifikasi.php';

// ── Process Notification Queue ─────────────────────
if (isFeatureEnabled('wa_notifikasi_queue')) {
    $queue_result = processNotificationQueue(10);
    $log[] = "WA Queue: sent={$queue_result['sent']}, failed={$queue_result['failed']}, processed={$queue_result['processed']}";
}
// ─────────────────────────────────────────────────────

$log = function(string $msg) {
    $ts = date('[Y-m-d H:i:s]');
    echo "$ts $msg\n";
};

$log("=== Kewer Daily Cron Start ===");

// ── 1. Auto tandai macet ──────────────────────────────────────────
$macet = autoTandaiMacet();
$log("autoTandaiMacet: $macet pinjaman ditandai macet");

// ── 2. Update hari_tunggakan & kolektibilitas OJK ─────────────────
$updated = hitungKolektibilitasSemua();
$log("kolektibilitas: $updated pinjaman diupdate");

// ── 3. Hitung denda harian angsuran jatuh tempo ───────────────────
$denda = hitungDendaHarian();
$log("denda harian: $denda angsuran diupdate");

// ── 4. Notifikasi internal jatuh tempo H-1 dan H-0 ───────────────
$notif = kirimNotifJatuhTempo();
$log("notifikasi jatuh tempo: $notif dikirim");

// ── 5. WA pengingat (jika enabled) ───────────────────────────────
$wa_enabled = getenv('WA_ENABLED') === 'true' || (defined('WA_ENABLED') && WA_ENABLED);
if ($wa_enabled) {
    $wa = kirimWaPengingatBatch();
    $log("WA pengingat: {$wa['sent']} terkirim, {$wa['failed']} gagal");
} else {
    $log("WA notifikasi: disabled (set WA_ENABLED=true di .env untuk aktifkan)");
}

$log("=== Kewer Daily Cron Done ===\n");
