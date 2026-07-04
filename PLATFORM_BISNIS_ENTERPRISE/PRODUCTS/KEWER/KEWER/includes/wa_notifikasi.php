<?php
/**
 * In-App Notification System — Kewer v2.3.0
 * Sistem notifikasi internal tanpa ketergantungan layanan eksternal
 *
 * Notifikasi disimpan di notification_queue dan ditampilkan di dashboard
 */

/**
 * Kirim notifikasi in-app.
 * Notifikasi dimasukkan ke queue untuk ditampilkan di dashboard.
 */
function kirimWA(string $nomor, string $pesan, ?int $nasabah_id = null, ?int $petugas_id = null, string $tipe = 'pengingat', bool $use_queue = true): array {
    // Masukkan ke queue untuk notifikasi in-app
    return enqueueNotification($nomor, $pesan, $nasabah_id, $petugas_id, $tipe);
}

/**
 * Kirim notifikasi langsung (tanpa queue) - untuk in-app notification
 */
function sendWADirect(string $nomor, string $pesan, ?int $nasabah_id = null, ?int $petugas_id = null, string $tipe = 'pengingat'): array {
    // Untuk in-app notification, kita hanya log ke database
    // Tidak mengirim ke layanan eksternal
    $sql = "INSERT INTO notification_queue (
        nasabah_id, petugas_id, tipe, nomor_wa, pesan, priority, status, provider, sent_at
    ) VALUES (?, ?, ?, ?, ?, 5, 'sent', 'in_app', NOW())";
    
    global $conn;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iissss', $nasabah_id, $petugas_id, $tipe, $nomor, $pesan);
    $result = $stmt->execute();
    
    if ($result) {
        return ['success' => true, 'http_code' => 200];
    } else {
        return ['success' => false, 'error' => 'Gagal menyimpan notifikasi', 'http_code' => 500];
    }
}

/**
 * Masukkan notifikasi ke queue.
 */
function enqueueNotification(string $nomor, string $pesan, ?int $nasabah_id = null, ?int $petugas_id = null, string $tipe = 'pengingat', int $priority = 5): array {
    // Format nomor: hapus leading 0, tambah 62
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    }

    $result = query(
        "INSERT INTO notification_queue (nasabah_id, petugas_id, tipe, nomor_wa, pesan, priority, status, provider)
         VALUES (?, ?, ?, ?, ?, ?, 'pending', 'in_app')",
        [$nasabah_id, $petugas_id, $tipe, $nomor, $pesan, $priority]
    );

    return ['success' => (bool)$result, 'queued' => true, 'nomor' => $nomor];
}

/**
 * Proses queue notifikasi (dipanggil dari cron).
 */
function processNotificationQueue(int $batch_size = 10): array {
    $sent = 0;
    $failed = 0;

    // Ambil batch pending notifications, urut by priority then created_at
    $queue_items = query(
        "SELECT * FROM notification_queue
         WHERE status = 'pending'
           AND (scheduled_at IS NULL OR scheduled_at <= NOW())
         ORDER BY priority ASC, created_at ASC
         LIMIT ?",
        [$batch_size]
    );

    if (!$queue_items || !is_array($queue_items)) {
        return ['sent' => 0, 'failed' => 0, 'queue_empty' => true];
    }

    foreach ($queue_items as $item) {
        // Update status ke processing
        query("UPDATE notification_queue SET status = 'processing', updated_at = NOW() WHERE id = ?", [$item['id']]);

        // Kirim WA
        $result = sendWADirect($item['nomor_wa'], $item['pesan'], $item['nasabah_id'], $item['petugas_id'], $item['tipe']);

        if ($result['success']) {
            query(
                "UPDATE notification_queue SET status = 'sent', sent_at = NOW(), response_code = ?, response_body = ?, updated_at = NOW() WHERE id = ?",
                [$result['http_code'], '', $item['id']]
            );
            $sent++;
        } else {
            $retry_count = $item['retry_count'] + 1;
            if ($retry_count >= $item['max_retry']) {
                $status = 'failed';
                $error_msg = $result['error'] ?? 'Unknown error';
            } else {
                $status = 'pending';
                $error_msg = $result['error'] ?? 'Unknown error';
            }
            query(
                "UPDATE notification_queue SET status = ?, retry_count = ?, error_message = ?, response_code = ?, updated_at = NOW() WHERE id = ?",
                [$status, $retry_count, $error_msg, $result['http_code'] ?? 0, $item['id']]
            );
            $failed++;
        }

        // Rate limiting: jeda 1 detik tiap pesan
        sleep(1);
    }

    return ['sent' => $sent, 'failed' => $failed, 'processed' => $sent + $failed];
}

/**
 * Template pesan WA: pengingat angsuran jatuh tempo.
 */
function templateWaPengingat(array $nasabah, array $pinjaman, array $angsuran, string $tipe = 'H-1'): string {
    $tgl = date('d/m/Y', strtotime($angsuran['jatuh_tempo']));
    $angsuran_fmt = 'Rp ' . number_format($angsuran['total_bayar'], 0, ',', '.');

    if ($tipe === 'H-0') {
        return "Yth. *{$nasabah['nama']}*,\n\nAngsuran Anda *hari ini* ({$tgl}) jatuh tempo.\n\nDetail:\n• Kode Pinjaman: {$pinjaman['kode_pinjaman']}\n• Angsuran ke-{$angsuran['ke']}: *{$angsuran_fmt}*\n\nMohon segera lakukan pembayaran kepada petugas kami.\n\nTerima kasih — *Kewer Koperasi*";
    } elseif ($tipe === 'TELAT') {
        $hari = $angsuran['hari_telat'] ?? 1;
        return "Yth. *{$nasabah['nama']}*,\n\nAngsuran Anda *telah melewati jatuh tempo* sejak {$tgl} ({$hari} hari lalu).\n\nSegera hubungi petugas kami untuk menghindari denda tambahan.\n\nTerima kasih — *Kewer Koperasi*";
    } elseif ($tipe === 'H-3') {
        return "Yth. *{$nasabah['nama']}*,\n\nPengingat: Angsuran Anda akan jatuh tempo dalam 3 hari ({$tgl}).\n\nDetail:\n• Kode Pinjaman: {$pinjaman['kode_pinjaman']}\n• Angsuran ke-{$angsuran['ke']}: *{$angsuran_fmt}*\n\nMohon siapkan pembayaran Anda.\n\nTerima kasih — *Kewer Koperasi*";
    }

    // H-1
    return "Yth. *{$nasabah['nama']}*,\n\nPengingat: Angsuran Anda jatuh tempo *besok* ({$tgl}).\n\nDetail:\n• Kode Pinjaman: {$pinjaman['kode_pinjaman']}\n• Angsuran ke-{$angsuran['ke']}: *{$angsuran_fmt}*\n\nSiapkan pembayaran Anda.\n\nTerima kasih — *Kewer Koperasi*";
}

/**
 * Template pesan WA: konfirmasi pembayaran berhasil.
 */
function templateWaKonfirmasiBayar(array $nasabah, array $pinjaman, array $pembayaran): string {
    $tgl  = date('d/m/Y', strtotime($pembayaran['tanggal_bayar']));
    $jml  = 'Rp ' . number_format($pembayaran['total_bayar'], 0, ',', '.');
    return "Yth. *{$nasabah['nama']}*,\n\nPembayaran Anda telah diterima ✅\n\nDetail:\n• Kode: {$pembayaran['kode_pembayaran']}\n• Jumlah: *{$jml}*\n• Tanggal: {$tgl}\n• Pinjaman: {$pinjaman['kode_pinjaman']}\n\nTerima kasih atas pembayaran tepat waktu Anda.\n\n*Kewer Koperasi*";
}

/**
 * Batch kirim WA pengingat (dipanggil dari cron).
 * Kirim ke nasabah dengan angsuran jatuh tempo H-3, H-1, dan H-0.
 */
function kirimWaPengingatBatch(): array {
    $sent   = 0;
    $failed = 0;
    $h3     = date('Y-m-d', strtotime('+3 days'));
    $besok  = date('Y-m-d', strtotime('+1 day'));
    $hari_ini = date('Y-m-d');

    // Angsuran H-3, H-1, dan H-0 yang belum lunas
    $angsuran_list = query(
        "SELECT a.*, p.kode_pinjaman, p.nasabah_id, n.nama as nama_nasabah, n.telepon,
                DATEDIFF(CURDATE(), a.jatuh_tempo) as hari_telat
         FROM angsuran a
         JOIN pinjaman p ON a.pinjaman_id = p.id
         JOIN nasabah n ON p.nasabah_id = n.id
         WHERE a.status != 'lunas'
           AND a.jatuh_tempo IN (?, ?, ?)
           AND n.telepon IS NOT NULL AND n.telepon != ''
           AND p.status = 'aktif'",
        [$h3, $hari_ini, $besok]
    );

    if (!$angsuran_list) return ['sent' => 0, 'failed' => 0];

    foreach ($angsuran_list as $row) {
        if ($row['jatuh_tempo'] === $hari_ini) {
            $tipe = 'H-0';
        } elseif ($row['jatuh_tempo'] === $besok) {
            $tipe = 'H-1';
        } else {
            $tipe = 'H-3';
        }
        $nasabah  = ['nama' => $row['nama_nasabah']];
        $pinjaman = ['kode_pinjaman' => $row['kode_pinjaman']];
        $angsuran_data = ['ke' => $row['ke'] ?? '-', 'jatuh_tempo' => $row['jatuh_tempo'],
                          'total_bayar' => $row['total_bayar'], 'hari_telat' => $row['hari_telat']];

        $pesan = templateWaPengingat($nasabah, $pinjaman, $angsuran_data, $tipe);
        $r = kirimWA($row['telepon'], $pesan, $row['nasabah_id'], null, 'jatuh_tempo');

        if ($r['success']) $sent++; else $failed++;

        // Rate limiting: jeda 1 detik tiap pesan
        sleep(1);
    }

    return ['sent' => $sent, 'failed' => $failed];
}
