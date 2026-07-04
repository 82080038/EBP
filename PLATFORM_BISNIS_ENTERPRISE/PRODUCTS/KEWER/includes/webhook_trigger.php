<?php
/**
 * Webhook Trigger Helper
 * Triggers webhooks for specific events
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

require_once __DIR__ . '/../src/Webhook/WebhookService.php';

/**
 * Trigger webhook for pinjaman approval
 */
function triggerPinjamanApproved($pinjamanId, $pinjamanData) {
    $payload = [
        'event' => 'pinjaman.approved',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'pinjaman_id' => $pinjamanId,
            'no_pinjaman' => $pinjamanData['no_pinjaman'] ?? '',
            'nasabah_id' => $pinjamanData['nasabah_id'] ?? '',
            'nama_nasabah' => $pinjamanData['nama_nasabah'] ?? '',
            'jumlah_pinjaman' => $pinjamanData['jumlah_pinjaman'] ?? 0,
            'tanggal_disetujui' => $pinjamanData['tanggal_disetujui'] ?? '',
            'cabang_id' => $pinjamanData['cabang_id'] ?? '',
            'auto_approved' => $pinjamanData['auto_approved'] ?? false,
            'credit_score' => $pinjamanData['credit_score_at_approval'] ?? null
        ]
    ];
    
    \Kewer\Webhook\WebhookService::trigger('pinjaman.approved', 'pinjaman', $pinjamanId, $payload);
}

/**
 * Trigger webhook for pinjaman rejection
 */
function triggerPinjamanRejected($pinjamanId, $pinjamanData, $reason) {
    $payload = [
        'event' => 'pinjaman.rejected',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'pinjaman_id' => $pinjamanId,
            'no_pinjaman' => $pinjamanData['no_pinjaman'] ?? '',
            'nasabah_id' => $pinjamanData['nasabah_id'] ?? '',
            'nama_nasabah' => $pinjamanData['nama_nasabah'] ?? '',
            'jumlah_pinjaman' => $pinjamanData['jumlah_pinjaman'] ?? 0,
            'rejection_reason' => $reason,
            'cabang_id' => $pinjamanData['cabang_id'] ?? ''
        ]
    ];
    
    \Kewer\Webhook\WebhookService::trigger('pinjaman.rejected', 'pinjaman', $pinjamanId, $payload);
}

/**
 * Trigger webhook for pembayaran received
 */
function triggerPembayaranReceived($pembayaranId, $pembayaranData) {
    $payload = [
        'event' => 'pembayaran.received',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'pembayaran_id' => $pembayaranId,
            'angsuran_id' => $pembayaranData['angsuran_id'] ?? '',
            'pinjaman_id' => $pembayaranData['pinjaman_id'] ?? '',
            'nasabah_id' => $pembayaranData['nasabah_id'] ?? '',
            'nama_nasabah' => $pembayaranData['nama_nasabah'] ?? '',
            'nominal' => $pembayaranData['nominal'] ?? 0,
            'tanggal_bayar' => $pembayaranData['tanggal_bayar'] ?? '',
            'cabang_id' => $pembayaranData['cabang_id'] ?? '',
            'petugas_id' => $pembayaranData['petugas_id'] ?? null,
            'gps_latitude' => $pembayaranData['latitude'] ?? null,
            'gps_longitude' => $pembayaranData['longitude'] ?? null
        ]
    ];
    
    \Kewer\Webhook\WebhookService::trigger('pembayaran.received', 'pembayaran', $pembayaranId, $payload);
}

/**
 * Trigger webhook for nasabah blacklist
 */
function triggerNasabahBlacklisted($nasabahId, $nasabahData, $reason) {
    $payload = [
        'event' => 'nasabah.blacklisted',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'nasabah_id' => $nasabahId,
            'nama' => $nasabahData['nama'] ?? '',
            'ktp' => $nasabahData['ktp'] ?? '',
            'blacklist_reason' => $reason,
            'cabang_id' => $nasabahData['cabang_id'] ?? ''
        ]
    ];
    
    \Kewer\Webhook\WebhookService::trigger('nasabah.blacklisted', 'nasabah', $nasabahId, $payload);
}

/**
 * Trigger webhook for nasabah registration
 */
function triggerNasabahRegistered($nasabahId, $nasabahData) {
    $payload = [
        'event' => 'nasabah.registered',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'nasabah_id' => $nasabahId,
            'nama' => $nasabahData['nama'] ?? '',
            'ktp' => $nasabahData['ktp'] ?? '',
            'cabang_id' => $nasabahData['cabang_id'] ?? '',
            'tanggal_daftar' => $nasabahData['tanggal_daftar'] ?? ''
        ]
    ];
    
    \Kewer\Webhook\WebhookService::trigger('nasabah.registered', 'nasabah', $nasabahId, $payload);
}

/**
 * Trigger webhook for overdue payment
 */
function triggerOverduePayment($angsuranId, $angsuranData) {
    $payload = [
        'event' => 'angsuran.overdue',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'angsuran_id' => $angsuranId,
            'pinjaman_id' => $angsuranData['pinjaman_id'] ?? '',
            'nasabah_id' => $angsuranData['nasabah_id'] ?? '',
            'nama_nasabah' => $angsuranData['nama_nasabah'] ?? '',
            'nominal' => $angsuranData['nominal'] ?? 0,
            'tanggal_jatuh_tempo' => $angsuranData['tanggal_jatuh_tempo'] ?? '',
            'days_overdue' => $angsuranData['days_overdue'] ?? 0,
            'cabang_id' => $angsuranData['cabang_id'] ?? ''
        ]
    ];
    
    \Kewer\Webhook\WebhookService::trigger('angsuran.overdue', 'angsuran', $angsuranId, $payload);
}
