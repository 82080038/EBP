<?php
/**
 * business_logic.php
 * ==================
 * Implementasi business rules koperasi keliling berdasarkan skenario nyata lapangan.
 * Dipanggil dari API dan pages yang membutuhkan validasi bisnis kompleks.
 *
 * Skenario yang diantisipasi:
 *   A. Nasabah: meninggal, blacklist lintas koperasi, kelebihan bayar, pindah lokasi
 *   B. Pinjaman: restrukturisasi, pelunasan dipercepat, macet/write-off, pinjaman ganda
 *   C. Petugas: pengganti, selisih kas, petugas kabur
 *   D. Keuangan: jurnal kas otomatis, skor kredit
 *   E. Sistem: pembayaran offline, notifikasi
 */

// ================================================================
// A. NASABAH BUSINESS LOGIC
// ================================================================

/**
 * Cek apakah nasabah boleh mengajukan pinjaman.
 * Return: ['allowed' => bool, 'reason' => string, 'warnings' => []]
 */
function cekKelayakanNasabah($nasabah_id, $owner_bos_id) {
    $nasabah = query("SELECT * FROM nasabah WHERE id = ? AND owner_bos_id = ?", [$nasabah_id, $owner_bos_id]);
    if (!$nasabah) return ['allowed' => false, 'reason' => 'Nasabah tidak ditemukan', 'warnings' => []];
    $n = $nasabah[0];

    $warnings = [];

    // Blacklist platform-wide (lintas koperasi)
    if ($n['platform_blacklist']) {
        return ['allowed' => false, 'reason' => 'Nasabah diblacklist oleh platform (lintas koperasi)', 'warnings' => []];
    }

    // Blacklist lokal koperasi
    if ($n['status'] === 'blacklist') {
        return ['allowed' => false, 'reason' => 'Nasabah diblacklist di koperasi ini', 'warnings' => []];
    }

    // Nonaktif / meninggal
    if ($n['status'] === 'nonaktif') {
        return ['allowed' => false, 'reason' => 'Nasabah tidak aktif', 'warnings' => []];
    }
    if ($n['tanggal_meninggal']) {
        return ['allowed' => false, 'reason' => 'Nasabah telah meninggal dunia pada ' . $n['tanggal_meninggal'], 'warnings' => []];
    }

    // Pinjaman aktif
    $pinjaman_aktif = query(
        "SELECT COUNT(*) as jml FROM pinjaman WHERE nasabah_id = ? AND status IN ('aktif','disetujui','pengajuan') AND override_pinjaman_aktif = 0",
        [$nasabah_id]
    );
    $jml_aktif = (int)($pinjaman_aktif[0]['jml'] ?? 0);
    if ($jml_aktif > 0) {
        $warnings[] = "Nasabah memiliki {$jml_aktif} pinjaman aktif. Diperlukan override dari bos/manager.";
    }

    // Skor kredit rendah
    if ($n['skor_kredit'] <= 50) {
        $warnings[] = "Skor kredit nasabah rendah ({$n['skor_kredit']}/100). Pertimbangkan jaminan tambahan.";
    }
    if ($n['skor_kredit'] <= 20) {
        return ['allowed' => false, 'reason' => "Skor kredit terlalu rendah ({$n['skor_kredit']}/100). Tidak bisa mengajukan pinjaman.", 'warnings' => $warnings];
    }

    return ['allowed' => true, 'reason' => '', 'warnings' => $warnings, 'jml_pinjaman_aktif' => $jml_aktif];
}

/**
 * Proses nasabah meninggal dunia.
 * - Set tanggal_meninggal
 * - Set status nonaktif
 * - Tandai semua pinjaman aktif → macet (jika tidak ada ahli waris penjamin)
 * - Buat notifikasi ke manager
 */
function prosesNasabahMeninggal($nasabah_id, $tanggal_meninggal, $catatan, $actor_id) {
    $nasabah = query("SELECT * FROM nasabah WHERE id = ?", [$nasabah_id]);
    if (!$nasabah) return ['success' => false, 'error' => 'Nasabah tidak ditemukan'];
    $n = $nasabah[0];

    // Update nasabah
    query("UPDATE nasabah SET status = 'nonaktif', tanggal_meninggal = ?, updated_at = NOW() WHERE id = ?",
        [$tanggal_meninggal, $nasabah_id]);

    // Cek apakah ada ahli waris penjamin
    $penjamin = query("SELECT id FROM ahli_waris WHERE nasabah_id = ? AND adalah_penjamin = 1", [$nasabah_id]);

    // Ambil pinjaman aktif
    $pinjaman_aktif = query("SELECT id, kode_pinjaman, sisa_pokok_berjalan, total_pembayaran FROM pinjaman WHERE nasabah_id = ? AND status = 'aktif'", [$nasabah_id]);

    $pinjaman_ids = [];
    foreach ($pinjaman_aktif as $p) {
        $pinjaman_ids[] = $p['id'];
        if (!$penjamin) {
            // Tidak ada penjamin — tandai macet
            query("UPDATE pinjaman SET status = 'macet', updated_at = NOW() WHERE id = ?", [$p['id']]);
        }
    }

    // Audit log
    logAudit('nasabah_meninggal', 'nasabah', $nasabah_id,
        ['status' => 'aktif'],
        ['status' => 'nonaktif', 'tanggal_meninggal' => $tanggal_meninggal, 'catatan' => $catatan,
         'pinjaman_terdampak' => count($pinjaman_aktif), 'ada_penjamin' => !empty($penjamin)]
    );

    // Notifikasi ke manager
    buatNotifikasi(
        $n['cabang_id'],
        null,
        'manager_pusat',
        'peringatan',
        "Nasabah Meninggal: {$n['nama']}",
        "Nasabah {$n['nama']} ({$n['kode_nasabah']}) meninggal pada {$tanggal_meninggal}. " .
        count($pinjaman_aktif) . " pinjaman aktif." . ($penjamin ? " Ada penjamin/ahli waris." : " Tidak ada penjamin — pinjaman ditandai macet."),
        'nasabah', $nasabah_id
    );

    return [
        'success' => true,
        'pinjaman_terdampak' => count($pinjaman_aktif),
        'ada_penjamin' => !empty($penjamin),
        'status_pinjaman' => $penjamin ? 'tetap_aktif_dilanjut_penjamin' : 'macet',
    ];
}

/**
 * Proses kelebihan bayar nasabah.
 * Dipanggil saat pembayaran melebihi saldo angsuran.
 */
function prosesKelebihanBayar($nasabah_id, $pinjaman_id, $pembayaran_id, $jumlah_lebih, $actor_id) {
    if ($jumlah_lebih <= 0) return false;

    query(
        "INSERT INTO kelebihan_bayar (nasabah_id, pinjaman_id, pembayaran_id, jumlah, status)
         VALUES (?, ?, ?, ?, 'pending')",
        [$nasabah_id, $pinjaman_id, $pembayaran_id, $jumlah_lebih]
    );

    $nasabah = query("SELECT nama, kode_nasabah, cabang_id FROM nasabah WHERE id = ?", [$nasabah_id]);
    $n = $nasabah[0] ?? null;
    if ($n) {
        buatNotifikasi(
            $n['cabang_id'], null, 'admin_pusat', 'info',
            "Kelebihan Bayar: {$n['nama']}",
            "Nasabah {$n['nama']} membayar lebih Rp " . number_format($jumlah_lebih) . ". Perlu diproses: dikembalikan atau dikompensasi ke pinjaman berikutnya.",
            'kelebihan_bayar', null
        );
    }
    return true;
}

// ================================================================
// B. PINJAMAN BUSINESS LOGIC
// ================================================================

/**
 * Hitung pelunasan dipercepat (early repayment).
 * Model: flat rate — bunga sisa dihitung proporsional berdasarkan pokok sisa.
 * Return: array detail perhitungan
 */
function hitungPelunasanDipercepat($pinjaman_id, $tanggal_lunas = null) {
    $tanggal_lunas = $tanggal_lunas ?: date('Y-m-d');

    $p = query("SELECT p.*, n.nama as nama_nasabah FROM pinjaman p JOIN nasabah n ON p.nasabah_id = n.id WHERE p.id = ?", [$pinjaman_id]);
    if (!$p) return ['error' => 'Pinjaman tidak ditemukan'];
    $p = $p[0];

    if (!in_array($p['status'], ['aktif', 'disetujui'])) {
        return ['error' => 'Pinjaman tidak dalam status aktif'];
    }

    // Hitung angsuran sudah dibayar dan sisa pokok
    $sudah_bayar = query(
        "SELECT COUNT(*) as jml, SUM(a.pokok) as total_pokok, SUM(a.bunga) as total_bunga
         FROM angsuran a
         WHERE a.pinjaman_id = ? AND a.status = 'lunas'",
        [$pinjaman_id]
    );
    $bayar = $sudah_bayar[0] ?? ['jml' => 0, 'total_pokok' => 0, 'total_bunga' => 0];

    $sisa_pokok      = $p['plafon'] - floatval($bayar['total_pokok']);
    $angsuran_sisa   = $p['tenor'] - intval($bayar['jml']);
    $bunga_sisa      = $sisa_pokok * ($p['bunga_per_bulan'] / 100) * $angsuran_sisa;

    // Denda terhutang (angsuran jatuh tempo yang belum dibayar)
    $denda_terhutang = query(
        "SELECT SUM(a.denda_terhitung - a.denda_dibebaskan) as total_denda
         FROM angsuran a WHERE a.pinjaman_id = ? AND a.status != 'lunas' AND a.jatuh_tempo < ?",
        [$pinjaman_id, $tanggal_lunas]
    );
    $total_denda = floatval($denda_terhutang[0]['total_denda'] ?? 0);

    // Diskon bunga untuk pelunasan dipercepat (kebijakan umum: diskon 50% bunga sisa)
    $diskon_bunga  = $bunga_sisa * 0.5;
    $bunga_dibayar = $bunga_sisa - $diskon_bunga;

    $total_harus_dibayar = $sisa_pokok + $bunga_dibayar + $total_denda;

    return [
        'pinjaman_id'        => $pinjaman_id,
        'nama_nasabah'       => $p['nama_nasabah'],
        'kode_pinjaman'      => $p['kode_pinjaman'],
        'plafon'             => floatval($p['plafon']),
        'angsuran_total'     => $p['tenor'],
        'angsuran_sudah'     => intval($bayar['jml']),
        'angsuran_sisa'      => $angsuran_sisa,
        'sisa_pokok'         => round($sisa_pokok, 2),
        'bunga_sisa_normal'  => round($bunga_sisa, 2),
        'diskon_bunga'       => round($diskon_bunga, 2),
        'bunga_dibayar'      => round($bunga_dibayar, 2),
        'denda_terhutang'    => round($total_denda, 2),
        'total_harus_dibayar'=> round($total_harus_dibayar, 2),
        'tanggal_hitung'     => $tanggal_lunas,
        'catatan'            => 'Diskon 50% bunga sisa untuk pelunasan dipercepat',
    ];
}

/**
 * Eksekusi pelunasan dipercepat berdasarkan hasil hitungPelunasanDipercepat().
 * Tandai semua angsuran sisa sebagai lunas, update status pinjaman → lunas.
 */
function prosesLunasDipercepat($pinjaman_id, $actor_id, $cara_bayar = 'tunai') {
    $hitung = hitungPelunasanDipercepat($pinjaman_id);
    if (isset($hitung['error'])) return ['success' => false, 'error' => $hitung['error']];

    $p = query("SELECT * FROM pinjaman WHERE id = ?", [$pinjaman_id]);
    if (!$p) return ['success' => false, 'error' => 'Pinjaman tidak ditemukan'];
    $p = $p[0];

    $total_bayar = $hitung['total_harus_dibayar'];

    // Tandai semua angsuran belum lunas sebagai lunas sekaligus
    query("UPDATE angsuran SET status = 'lunas', tanggal_bayar = NOW(), cara_bayar = ? WHERE pinjaman_id = ? AND status != 'lunas'",
        [$cara_bayar, $pinjaman_id]);

    // Update status pinjaman
    query("UPDATE pinjaman SET status = 'lunas', tanggal_lunas = NOW(), sisa_pokok_berjalan = 0, updated_at = NOW() WHERE id = ?",
        [$pinjaman_id]);

    // Update cache total_pinjaman_aktif nasabah
    query("UPDATE nasabah SET total_pinjaman_aktif = (SELECT COUNT(*) FROM pinjaman WHERE nasabah_id = ? AND status IN ('aktif','disetujui','pengajuan')) WHERE id = ?",
        [$p['nasabah_id'], $p['nasabah_id']]);

    // Jurnal kas masuk
    catatJurnalKas($p['cabang_id'] ?? 1, 'masuk', 'pelunasan_dipercepat',
        'pinjaman', $pinjaman_id, $total_bayar,
        "Pelunasan dipercepat {$p['kode_pinjaman']} (diskon bunga {$hitung['diskon_bunga']})", $actor_id);

    // Update skor kredit +5
    updateSkorKredit($p['nasabah_id'], +5, 'lunas_dipercepat', $pinjaman_id);

    // Notifikasi ke manager
    buatNotifikasi($p['cabang_id'] ?? 1, null, 'manager_pusat', 'info',
        'Pelunasan Dipercepat: ' . $p['kode_pinjaman'],
        "Pinjaman {$p['kode_pinjaman']} dilunasi dipercepat. Total dibayar: Rp " . number_format($total_bayar),
        'pinjaman', $pinjaman_id);

    logAudit('lunas_dipercepat', 'pinjaman', $pinjaman_id, ['status' => 'aktif'],
        ['status' => 'lunas', 'total_dibayar' => $total_bayar, 'diskon_bunga' => $hitung['diskon_bunga']]);

    return array_merge(['success' => true, 'message' => 'Pinjaman berhasil dilunasi dipercepat'], $hitung);
}

/**
 * Buat restrukturisasi pinjaman.
 * Tipe: reschedule (perpanjang tenor), reconditioning (turunkan bunga),
 *       restructuring (kombinasi), refinancing (pinjaman baru tutup yang lama)
 */
function buatRestrukturisasi($pinjaman_id, $tipe, $data, $disetujui_oleh) {
    $pinjaman = query("SELECT * FROM pinjaman WHERE id = ?", [$pinjaman_id]);
    if (!$pinjaman) return ['success' => false, 'error' => 'Pinjaman tidak ditemukan'];
    $p = $pinjaman[0];

    if (!in_array($p['status'], ['aktif', 'macet'])) {
        return ['success' => false, 'error' => 'Hanya pinjaman aktif atau macet yang bisa direstrukturisasi'];
    }

    // Hitung sisa pokok
    $sudah_bayar = query("SELECT SUM(pokok) as sp FROM angsuran WHERE pinjaman_id = ? AND status = 'lunas'", [$pinjaman_id]);
    $sisa_pokok = $p['plafon'] - floatval($sudah_bayar[0]['sp'] ?? 0);

    $restruk_data = [
        $pinjaman_id,
        $tipe,
        $data['alasan'] ?? 'lainnya',
        $data['alasan_detail'] ?? null,
        $sisa_pokok,
        $data['tenor_baru'] ?? null,
        $data['bunga_baru'] ?? null,
        $data['angsuran_baru'] ?? null,
        $data['tanggal_efektif'] ?? date('Y-m-d'),
        $data['denda_dibebaskan'] ?? 0,
        $disetujui_oleh,
        'disetujui',
        $data['catatan'] ?? null,
    ];

    $result = query(
        "INSERT INTO restrukturisasi (pinjaman_id, tipe, alasan, alasan_detail, sisa_pokok,
         tenor_baru, bunga_baru, angsuran_baru, tanggal_efektif, denda_dibebaskan, disetujui_oleh, status, catatan)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
        $restruk_data
    );

    if (!$result) return ['success' => false, 'error' => 'Gagal menyimpan restrukturisasi'];

    $restruk_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];

    // Update pinjaman: tandai restrukturisasi, update tenor/bunga jika berlaku
    $update_fields = ['is_restrukturisasi = 1', 'status = "aktif"', 'updated_at = NOW()'];
    $update_params = [];

    if ($tipe === 'reschedule' && !empty($data['tenor_baru'])) {
        $update_fields[] = 'tenor = ?';
        $update_params[] = $data['tenor_baru'];
        $update_fields[] = 'tanggal_jatuh_tempo = ?';
        $update_params[] = date('Y-m-d', strtotime($data['tanggal_efektif'] . " +" . $data['tenor_baru'] . " months"));
    }
    if (in_array($tipe, ['reconditioning','restructuring']) && !empty($data['bunga_baru'])) {
        $update_fields[] = 'bunga_per_bulan = ?';
        $update_params[] = $data['bunga_baru'];
    }

    $update_params[] = $pinjaman_id;
    query("UPDATE pinjaman SET " . implode(', ', $update_fields) . " WHERE id = ?", $update_params);

    // Regenerasi angsuran sisa jika tenor/bunga berubah
    if ($tipe !== 'reconditioning' && !empty($data['tenor_baru'])) {
        _regenerasiAngsuranSisa($pinjaman_id, $sisa_pokok, $data);
    }

    logAudit('restrukturisasi_pinjaman', 'restrukturisasi', $restruk_id,
        ['status_pinjaman' => $p['status']],
        ['tipe' => $tipe, 'sisa_pokok' => $sisa_pokok, 'tanggal_efektif' => $data['tanggal_efektif'] ?? date('Y-m-d')]
    );

    return ['success' => true, 'restrukturisasi_id' => $restruk_id];
}

/**
 * Regenerasi angsuran sisa setelah restrukturisasi tenor.
 * Menghapus angsuran belum bayar dan membuat ulang.
 */
function _regenerasiAngsuranSisa($pinjaman_id, $sisa_pokok, $data) {
    // Hapus angsuran yang belum lunas
    query("DELETE FROM angsuran WHERE pinjaman_id = ? AND status != 'lunas'", [$pinjaman_id]);

    $tenor_baru       = (int)$data['tenor_baru'];
    $bunga_per_angsuran = ($data['bunga_baru'] ?? null) ?: null;
    $tanggal_efektif  = $data['tanggal_efektif'] ?? date('Y-m-d');
    $frekuensi        = $data['frekuensi'] ?? 'bulanan';

    if (!$bunga_per_angsuran) {
        // Ambil dari pinjaman
        $p = query("SELECT bunga_per_bulan, frekuensi_id FROM pinjaman WHERE id = ?", [$pinjaman_id]);
        $bunga_per_angsuran = floatval($p[0]['bunga_per_bulan'] ?? 0);
        if (!$frekuensi) $frekuensi = $p[0]['frekuensi_id'] ?? 3;
    }

    $pokok_per_angsuran = $sisa_pokok / $tenor_baru;
    $bunga_per_angsuran_val = $sisa_pokok * ($bunga_per_angsuran / 100);
    $total_per_angsuran = $pokok_per_angsuran + $bunga_per_angsuran_val;

    // Convert frequency ID to code if needed
    $frekuensi_code = getFrequencyCode($frekuensi);

    $interval = match($frekuensi_code) {
        'HARIAN'   => '+1 day',
        'MINGGUAN' => '+1 week',
        default    => '+1 month',
    };

    // Ambil nomor angsuran terakhir yang sudah lunas
    $last = query("SELECT MAX(no_angsuran) as max_no FROM angsuran WHERE pinjaman_id = ? AND status = 'lunas'", [$pinjaman_id]);
    $start_no = (int)($last[0]['max_no'] ?? 0) + 1;

    $jatuh_tempo = $tanggal_efektif;
    for ($i = 0; $i < $tenor_baru; $i++) {
        $jatuh_tempo = date('Y-m-d', strtotime($jatuh_tempo . ' ' . $interval));
        query(
            "INSERT INTO angsuran (pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran, status)
             VALUES (?, ?, ?, ?, ?, ?, 'belum_lunas')",
            [$pinjaman_id, $start_no + $i, $jatuh_tempo,
             round($pokok_per_angsuran, 2), round($bunga_per_angsuran_val, 2), round($total_per_angsuran, 2)]
        );
    }
}

/**
 * Write-off pinjaman macet.
 * Hanya bos yang bisa menyetujui. Memerlukan dokumentasi upaya penagihan.
 */
function prosesWriteOff($pinjaman_id, $data, $disetujui_oleh) {
    $pinjaman = query("SELECT p.*, n.nama as nama_nasabah, n.cabang_id FROM pinjaman p JOIN nasabah n ON p.nasabah_id = n.id WHERE p.id = ?", [$pinjaman_id]);
    if (!$pinjaman) return ['success' => false, 'error' => 'Pinjaman tidak ditemukan'];
    $p = $pinjaman[0];

    if ($p['status'] !== 'macet') return ['success' => false, 'error' => 'Hanya pinjaman macet yang bisa di-write-off'];

    // Cek apakah sudah di-write-off sebelumnya
    $existing = query("SELECT id FROM write_off WHERE pinjaman_id = ?", [$pinjaman_id]);
    if ($existing) return ['success' => false, 'error' => 'Pinjaman sudah pernah di-write-off'];

    // Hitung sisa pokok & denda
    $sudah_bayar = query("SELECT SUM(pokok) as sp FROM angsuran WHERE pinjaman_id = ? AND status = 'lunas'", [$pinjaman_id]);
    $sisa_pokok = $p['plafon'] - floatval($sudah_bayar[0]['sp'] ?? 0);
    $total_denda = floatval($p['total_denda_terhutang'] ?? 0);

    $result = query(
        "INSERT INTO write_off (pinjaman_id, nasabah_id, sisa_pokok, total_denda, total_kerugian, alasan, alasan_detail, upaya_penagihan, disetujui_oleh, tanggal_writeoff, status_aset, dokumen)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$pinjaman_id, $p['nasabah_id'], $sisa_pokok, $total_denda, $sisa_pokok + $total_denda,
         $data['alasan'] ?? 'lainnya', $data['alasan_detail'] ?? null, $data['upaya_penagihan'] ?? null,
         $disetujui_oleh, date('Y-m-d'), $data['status_aset'] ?? 'tidak_ada', $data['dokumen'] ?? null]
    );

    if (!$result) return ['success' => false, 'error' => 'Gagal menyimpan write-off'];

    // Tutup pinjaman dengan status khusus (gunakan 'lunas' tapi tandai write-off via write_off table)
    query("UPDATE pinjaman SET status = 'lunas', tanggal_lunas = ?, updated_at = NOW() WHERE id = ?",
        [date('Y-m-d'), $pinjaman_id]);

    // Update skor kredit nasabah
    updateSkorKredit($p['nasabah_id'], -50, 'writeoff', $pinjaman_id);

    // Catat di jurnal kas sebagai kerugian
    $bos_id = query("SELECT owner_bos_id FROM nasabah WHERE id = ?", [$p['nasabah_id']]);
    catatJurnalKas($p['cabang_id'], 'keluar', 'lainnya', 'write_off', $pinjaman_id, $sisa_pokok + $total_denda,
        "Write-off pinjaman {$p['kode_pinjaman']} — {$p['nama_nasabah']}", $disetujui_oleh);

    logAudit('write_off_pinjaman', 'write_off', $pinjaman_id,
        ['status' => 'macet', 'sisa_pokok' => $sisa_pokok],
        ['alasan' => $data['alasan'] ?? 'lainnya', 'total_kerugian' => $sisa_pokok + $total_denda]
    );

    return ['success' => true, 'sisa_pokok' => $sisa_pokok, 'total_denda' => $total_denda, 'total_kerugian' => $sisa_pokok + $total_denda];
}

// ================================================================
// C. PETUGAS BUSINESS LOGIC
// ================================================================

/**
 * Buat penugasan petugas pengganti.
 * Saat petugas asli sakit/izin, semua nasabah hari itu di-assign ke petugas pengganti.
 */
function buatPenggantiPetugas($cabang_id, $petugas_id, $pengganti_id, $tanggal_mulai, $tanggal_selesai, $alasan, $catatan, $disetujui_oleh) {
    // Alias compat: jika dipanggil dengan signature lama (5 args)
    // Cek overlap: sudah ada pengganti aktif di periode ini?
    $existing = query(
        "SELECT id FROM pengganti_petugas WHERE petugas_id = ? AND tanggal_mulai <= ? AND tanggal_selesai >= ? AND status IN ('pending','aktif')",
        [$petugas_id, $tanggal_selesai, $tanggal_mulai]
    );
    if ($existing) return ['success' => false, 'error' => 'Pengganti sudah ada untuk periode ini'];

    $result = query(
        "INSERT INTO pengganti_petugas (cabang_id, petugas_id, pengganti_id, tanggal_mulai, tanggal_selesai, alasan_ketidakhadiran, catatan, disetujui_oleh, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aktif')",
        [$cabang_id, $petugas_id, $pengganti_id, $tanggal_mulai, $tanggal_selesai, $alasan, $catatan, $disetujui_oleh]
    );

    if (!$result) return ['success' => false, 'error' => 'Gagal menyimpan penugasan pengganti'];

    $pp_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];

    // Notifikasi ke petugas pengganti
    buatNotifikasi($cabang_id, $pengganti_id, null, 'info',
        "Penugasan Pengganti — {$tanggal_mulai}",
        "Anda ditugaskan sebagai petugas pengganti mulai {$tanggal_mulai} s/d {$tanggal_selesai}. Alasan: {$alasan}.",
        'pengganti_petugas', $pp_id
    );

    logAudit('penugasan_pengganti', 'pengganti_petugas', $pp_id,
        [], ['petugas_id' => $petugas_id, 'pengganti_id' => $pengganti_id,
             'tanggal_mulai' => $tanggal_mulai, 'tanggal_selesai' => $tanggal_selesai, 'alasan' => $alasan]
    );

    return ['success' => true, 'id' => $pp_id];
}

/**
 * Verifikasi kas petugas oleh admin/manager.
 * Menghitung selisih dan mengunci record agar tidak bisa diedit.
 */
function verifikasiKasPetugas($kas_id, $verified_by) {
    $kas = query("SELECT * FROM kas_petugas WHERE id = ?", [$kas_id]);
    if (!$kas) return ['success' => false, 'error' => 'Kas tidak ditemukan'];
    $k = $kas[0];

    if ($k['is_locked']) return ['success' => false, 'error' => 'Kas sudah diverifikasi sebelumnya'];

    $selisih = $k['saldo_akhir'] - ($k['saldo_awal'] + $k['total_terima'] - $k['total_disetor']);
    $status  = $selisih == 0 ? 'lengkap' : ($selisih > 0 ? 'lebih' : 'kurang');

    query(
        "UPDATE kas_petugas SET selisih = ?, status = ?, verified_by = ?, verified_at = NOW(), is_locked = 1, updated_at = NOW() WHERE id = ?",
        [$selisih, $status, $verified_by, $kas_id]
    );

    // Jika ada selisih signifikan, buat notifikasi
    if (abs($selisih) > 10000) {
        $petugas = query("SELECT nama, cabang_id FROM users WHERE id = ?", [$k['petugas_id']]);
        if ($petugas) {
            $tipe_selisih = $selisih > 0 ? 'LEBIH' : 'KURANG';
            buatNotifikasi(
                $petugas[0]['cabang_id'], null, 'manager_pusat', 'kas_selisih',
                "Selisih Kas Petugas {$tipe_selisih}",
                "Kas petugas {$petugas[0]['nama']} pada {$k['tanggal']} selisih Rp " . number_format(abs($selisih)) . " ({$tipe_selisih}). Perlu investigasi.",
                'kas_petugas', $kas_id
            );
        }
    }

    return ['success' => true, 'selisih' => $selisih, 'status' => $status];
}

// ================================================================
// D. KEUANGAN BUSINESS LOGIC
// ================================================================

/**
 * Catat jurnal kas otomatis dari setiap transaksi.
 * Dipanggil setiap kali ada pembayaran, pencairan, pengeluaran.
 */
function catatJurnalKas($cabang_id, $tipe, $kategori, $referensi_tabel, $referensi_id, $jumlah, $keterangan, $created_by) {
    if (!$cabang_id || $jumlah <= 0) return false;

    // Hitung saldo terakhir cabang ini
    $last = query("SELECT saldo_sesudah FROM jurnal_kas WHERE cabang_id = ? ORDER BY id DESC LIMIT 1", [$cabang_id]);
    $saldo_sebelum = floatval($last[0]['saldo_sesudah'] ?? 0);
    $saldo_sesudah = $tipe === 'masuk' ? $saldo_sebelum + $jumlah : $saldo_sebelum - $jumlah;

    return query(
        "INSERT INTO jurnal_kas (cabang_id, tanggal, tipe, kategori, referensi_tabel, referensi_id, jumlah, saldo_sebelum, saldo_sesudah, keterangan, created_by)
         VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$cabang_id, $tipe, $kategori, $referensi_tabel, $referensi_id, $jumlah, $saldo_sebelum, $saldo_sesudah, $keterangan, $created_by]
    );
}

/**
 * Update skor kredit nasabah.
 * Dipanggil setiap ada pembayaran tepat waktu/telat, restrukturisasi, write-off.
 */
function updateSkorKredit($nasabah_id, $delta, $alasan, $referensi_id = null) {
    $nasabah = query("SELECT id, skor_kredit, owner_bos_id FROM nasabah WHERE id = ?", [$nasabah_id]);
    if (!$nasabah) return false;
    $n = $nasabah[0];

    $skor_sebelum = (int)$n['skor_kredit'];
    $skor_sesudah = max(0, min(100, $skor_sebelum + $delta));

    query("UPDATE nasabah SET skor_kredit = ?, updated_at = NOW() WHERE id = ?", [$skor_sesudah, $nasabah_id]);

    query(
        "INSERT INTO riwayat_skor_kredit (nasabah_id, owner_bos_id, skor_sebelum, skor_sesudah, delta, alasan, referensi_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$nasabah_id, $n['owner_bos_id'], $skor_sebelum, $skor_sesudah, $delta, $alasan, $referensi_id]
    );

    return $skor_sesudah;
}

// ================================================================
// E. NOTIFIKASI & SISTEM
// ================================================================

/**
 * Buat notifikasi internal.
 */
function buatNotifikasi($cabang_id, $user_id, $target_role, $tipe, $judul, $pesan, $ref_table = null, $ref_id = null) {
    return query(
        "INSERT INTO notifikasi (cabang_id, user_id, target_role, tipe, judul, pesan, referensi_tabel, referensi_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$cabang_id, $user_id, $target_role, $tipe, $judul, $pesan, $ref_table, $ref_id]
    );
}

/**
 * Ambil notifikasi user yang belum dibaca.
 */
function getNotifikasiUser($user_id, $user_role, $cabang_id, $limit = 20) {
    return query(
        "SELECT * FROM notifikasi
         WHERE is_read = 0
           AND (user_id = ? OR (target_role = ? AND cabang_id = ?) OR (target_role = ? AND cabang_id IS NULL))
         ORDER BY created_at DESC LIMIT ?",
        [$user_id, $user_role, $cabang_id, $user_role, $limit]
    ) ?: [];
}

/**
 * Tandai notifikasi sebagai sudah dibaca.
 */
function bacaNotifikasi($notifikasi_id, $user_id) {
    return query("UPDATE notifikasi SET is_read = 1, read_at = NOW() WHERE id = ? AND (user_id = ? OR user_id IS NULL)", [$notifikasi_id, $user_id]);
}

/**
 * Proses pembayaran dari antrian offline.
 * Dipanggil saat petugas kembali online dan sync data lapangan.
 */
function prosesOfflineQueue($queue_id, $processed_by) {
    $q = query("SELECT * FROM pembayaran_offline_queue WHERE id = ? AND status = 'pending'", [$queue_id]);
    if (!$q) return ['success' => false, 'error' => 'Antrian tidak ditemukan atau sudah diproses'];
    $q = $q[0];

    // Cek duplikat — apakah angsuran ini sudah punya pembayaran
    $existing = query("SELECT id FROM pembayaran WHERE angsuran_id = ? AND DATE(tanggal_bayar) = ?", [$q['angsuran_id'], $q['tanggal_kutip']]);
    if ($existing) {
        query("UPDATE pembayaran_offline_queue SET status = 'duplicate', processed_at = NOW(), processed_by = ?, error_message = 'Angsuran sudah dibayar pada tanggal ini' WHERE id = ?", [$processed_by, $queue_id]);
        return ['success' => false, 'error' => 'Duplikat — angsuran sudah tercatat dibayar'];
    }

    // Ambil data angsuran
    $angsuran = query("SELECT * FROM angsuran WHERE id = ?", [$q['angsuran_id']]);
    if (!$angsuran) {
        query("UPDATE pembayaran_offline_queue SET status = 'failed', error_message = 'Angsuran tidak ditemukan' WHERE id = ?", [$queue_id]);
        return ['success' => false, 'error' => 'Angsuran tidak ditemukan'];
    }
    $a = $angsuran[0];

    // Buat pembayaran
    $kode = 'BYR-' . strtoupper(uniqid());
    $result = query(
        "INSERT INTO pembayaran (cabang_id, pinjaman_id, angsuran_id, kode_pembayaran, jumlah_bayar, denda, total_bayar, tanggal_bayar, cara_bayar, petugas_id, is_offline, offline_reason, tanggal_kutip)
         VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 1, 'Pembayaran offline - sync dari lapangan', ?)",
        [$q['cabang_id'], $q['pinjaman_id'], $q['angsuran_id'], $kode, $q['jumlah_bayar'], $q['jumlah_bayar'], $q['tanggal_kutip'], $q['cara_bayar'], $q['petugas_id'], $q['tanggal_kutip']]
    );

    if ($result) {
        $bayar_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
        query("UPDATE angsuran SET status = 'lunas' WHERE id = ?", [$q['angsuran_id']]);
        query("UPDATE pembayaran_offline_queue SET status = 'processed', processed_at = NOW(), processed_by = ?, pembayaran_id = ? WHERE id = ?", [$processed_by, $bayar_id, $queue_id]);
        // Update skor kredit
        $selisih_hari = (int)((strtotime($q['tanggal_kutip']) - strtotime($a['jatuh_tempo'])) / 86400);
        $result_pinjaman = query("SELECT nasabah_id FROM pinjaman WHERE id = ?", [$q['pinjaman_id']]);
        $nasabah_id = is_array($result_pinjaman) && isset($result_pinjaman[0]) ? $result_pinjaman[0]['nasabah_id'] : 0;
        updateSkorKredit(
            $nasabah_id,
            $selisih_hari > 0 ? -2 : +1,
            $selisih_hari > 0 ? 'bayar_telat' : 'bayar_tepat_waktu',
            $bayar_id
        );
        return ['success' => true, 'pembayaran_id' => $bayar_id];
    }

    query("UPDATE pembayaran_offline_queue SET status = 'failed', error_message = 'Gagal insert pembayaran' WHERE id = ?", [$queue_id]);
    return ['success' => false, 'error' => 'Gagal membuat pembayaran'];
}

/**
 * Cek dan tandai pinjaman macet secara otomatis.
 * Dipanggil oleh cron job atau saat halaman laporan dibuka.
 * Pinjaman dianggap macet jika ada angsuran > 90 hari telat.
 */
function autoTandaiMacet($cabang_id = null) {
    $where = $cabang_id ? "AND p.cabang_id = ?" : "";
    $params = $cabang_id ? ['aktif', 90, $cabang_id] : ['aktif', 90];

    $macet_candidates = query(
        "SELECT DISTINCT p.id as pinjaman_id, p.nasabah_id, p.cabang_id, p.kode_pinjaman,
                n.nama as nama_nasabah,
                MAX(DATEDIFF(CURDATE(), a.jatuh_tempo)) as max_hari_telat
         FROM pinjaman p
         JOIN angsuran a ON a.pinjaman_id = p.id
         JOIN nasabah n ON p.nasabah_id = n.id
         WHERE p.status = ? AND a.status = 'belum_lunas' AND a.jatuh_tempo < CURDATE() - INTERVAL ? DAY
         $where
         GROUP BY p.id",
        $params
    );

    $ditandai = 0;
    foreach ($macet_candidates as $m) {
        query("UPDATE pinjaman SET status = 'macet', total_denda_terhutang = (SELECT COALESCE(SUM(denda_terhitung - denda_dibebaskan),0) FROM angsuran WHERE pinjaman_id = ? AND status != 'lunas'), updated_at = NOW() WHERE id = ?",
            [$m['pinjaman_id'], $m['pinjaman_id']]);
        buatNotifikasi($m['cabang_id'], null, 'manager_pusat', 'macet',
            "Pinjaman Macet: {$m['nama_nasabah']}",
            "Pinjaman {$m['kode_pinjaman']} ({$m['nama_nasabah']}) ditandai MACET. Telat {$m['max_hari_telat']} hari.",
            'pinjaman', $m['pinjaman_id']
        );
        $ditandai++;
    }
    return $ditandai;
}

// ================================================================
// D. KOLEKTIBILITAS OJK (5 Level)
// ================================================================

/**
 * Hitung kolektibilitas OJK berdasarkan hari tunggakan.
 * Level: 1=Lancar, 2=DPK, 3=KurangLancar, 4=Diragukan, 5=Macet
 */
function hitungKolektibilitas(int $hari_tunggakan): int {
    if ($hari_tunggakan <= 0)   return 1; // Lancar
    if ($hari_tunggakan <= 30)  return 2; // Dalam Perhatian Khusus
    if ($hari_tunggakan <= 60)  return 3; // Kurang Lancar
    if ($hari_tunggakan <= 90)  return 4; // Diragukan
    return 5;                             // Macet
}

function labelKolektibilitas(int $level): string {
    return match($level) {
        1 => 'Lancar',
        2 => 'Dalam Perhatian Khusus',
        3 => 'Kurang Lancar',
        4 => 'Diragukan',
        5 => 'Macet',
        default => 'Tidak Diketahui'
    };
}

function badgeKolektibilitas(int $level): string {
    $label = labelKolektibilitas($level);
    $color = match($level) {
        1 => 'success', 2 => 'warning', 3 => 'orange',
        4 => 'danger',  5 => 'dark',    default => 'secondary'
    };
    return "<span class=\"badge bg-{$color}\">{$label}</span>";
}

/**
 * Update kolektibilitas dan hari_tunggakan semua pinjaman aktif/macet.
 * Dipanggil dari cron harian.
 */
function hitungKolektibilitasSemua(): int {
    $pinjaman_list = query(
        "SELECT p.id,
                MAX(CASE WHEN a.status != 'lunas' AND a.jatuh_tempo < CURDATE()
                    THEN DATEDIFF(CURDATE(), a.jatuh_tempo) ELSE 0 END) as max_hari_telat
         FROM pinjaman p
         LEFT JOIN angsuran a ON a.pinjaman_id = p.id
         WHERE p.status IN ('aktif','macet','disetujui')
         GROUP BY p.id"
    );
    if (!$pinjaman_list) return 0;

    $updated = 0;
    foreach ($pinjaman_list as $row) {
        $hari = (int)($row['max_hari_telat'] ?? 0);
        $kol  = hitungKolektibilitas($hari);
        query("UPDATE pinjaman SET hari_tunggakan = ?, kolektibilitas = ?, updated_at = NOW() WHERE id = ?",
            [$hari, $kol, $row['id']]);
        $updated++;
    }
    return $updated;
}

// ================================================================
// E. DENDA HARIAN & NOTIFIKASI JATUH TEMPO
// ================================================================

/**
 * Hitung dan update denda harian untuk angsuran yang sudah jatuh tempo.
 * Dipanggil dari cron harian.
 */
function hitungDendaHarian(): int {
    $setting = query("SELECT denda_per_hari, denda_maks_persen FROM setting_denda LIMIT 1");
    $denda_persen = floatval($setting[0]['denda_per_hari'] ?? 0.1);  // 0.1% per hari default
    $maks_persen  = floatval($setting[0]['denda_maks_persen'] ?? 30); // maks 30% dari pokok

    $telat = query(
        "SELECT a.id, a.pokok, a.denda_terhitung, a.denda_dibebaskan,
                DATEDIFF(CURDATE(), a.jatuh_tempo) as hari_telat
         FROM angsuran a
         WHERE a.status != 'lunas' AND a.jatuh_tempo < CURDATE() AND DATEDIFF(CURDATE(), a.jatuh_tempo) > 0"
    );
    if (!$telat) return 0;

    $updated = 0;
    foreach ($telat as $a) {
        $hari   = (int)$a['hari_telat'];
        $denda  = round($a['pokok'] * ($denda_persen / 100) * $hari, 2);
        $maks   = round($a['pokok'] * ($maks_persen / 100), 2);
        $denda  = min($denda, $maks);
        if ($denda != floatval($a['denda_terhitung'])) {
            query("UPDATE angsuran SET denda_terhitung = ?, updated_at = NOW() WHERE id = ?", [$denda, $a['id']]);
            $updated++;
        }
    }
    return $updated;
}

/**
 * Kirim notifikasi internal ke petugas untuk angsuran jatuh tempo H-1 & H-0.
 */
function kirimNotifJatuhTempo(): int {
    $besok    = date('Y-m-d', strtotime('+1 day'));
    $hari_ini = date('Y-m-d');

    $list = query(
        "SELECT a.id as angsuran_id, a.jatuh_tempo, a.total_bayar,
                p.id as pinjaman_id, p.kode_pinjaman, p.petugas_id, p.cabang_id,
                n.nama as nama_nasabah
         FROM angsuran a
         JOIN pinjaman p ON a.pinjaman_id = p.id
         JOIN nasabah n ON p.nasabah_id = n.id
         WHERE a.status != 'lunas' AND a.jatuh_tempo IN (?, ?) AND p.status = 'aktif'",
        [$hari_ini, $besok]
    );
    if (!$list) return 0;

    $sent = 0;
    foreach ($list as $row) {
        $label  = ($row['jatuh_tempo'] === $hari_ini) ? 'HARI INI' : 'BESOK';
        $jml    = 'Rp ' . number_format($row['total_bayar'], 0, ',', '.');
        $petugas_id = $row['petugas_id'] ?? null;
        buatNotifikasi(
            $row['cabang_id'],
            $petugas_id,
            null,
            'jatuh_tempo',
            "Angsuran Jatuh Tempo {$label}: {$row['nama_nasabah']}",
            "Pinjaman {$row['kode_pinjaman']} — {$row['nama_nasabah']} jatuh tempo {$label}. Tagih: {$jml}.",
            'angsuran', $row['angsuran_id']
        );
        $sent++;
    }
    return $sent;
}

// ================================================================
// F. TARGET PETUGAS
// ================================================================

/**
 * Ambil realisasi vs target petugas untuk bulan tertentu.
 */
function getRealisasiVsTarget(int $petugas_id, string $bulan): array {
    $start = $bulan . '-01';
    $end   = date('Y-m-t', strtotime($start));

    $target = query("SELECT * FROM target_petugas WHERE petugas_id = ? AND bulan = ?", [$petugas_id, $bulan]);
    $t = $target[0] ?? ['target_kutipan' => 0, 'target_nasabah_baru' => 0,
                         'target_pinjaman_baru' => 0, 'target_collection_rate' => 90];

    $real_kutipan = query(
        "SELECT COALESCE(SUM(py.total_bayar), 0) as total
         FROM pembayaran py JOIN pinjaman p ON py.pinjaman_id = p.id
         WHERE p.petugas_id = ? AND DATE(py.tanggal_bayar) BETWEEN ? AND ?",
        [$petugas_id, $start, $end]
    );
    // Nasabah baru yang pinjaman pertamanya dibuat oleh petugas ini di periode ini
    $real_nasabah = query(
        "SELECT COUNT(DISTINCT p.nasabah_id) as total
         FROM pinjaman p
         WHERE p.petugas_id = ? AND DATE(p.created_at) BETWEEN ? AND ?
           AND NOT EXISTS (
               SELECT 1 FROM pinjaman p2
               WHERE p2.nasabah_id = p.nasabah_id AND DATE(p2.created_at) < ?
           )",
        [$petugas_id, $start, $end, $start]
    );
    $real_pinjaman = query(
        "SELECT COUNT(*) as total FROM pinjaman WHERE petugas_id = ? AND DATE(created_at) BETWEEN ? AND ?",
        [$petugas_id, $start, $end]
    );
    // Collection rate: angsuran lunas / total angsuran jatuh tempo periode ini
    $collection = query(
        "SELECT
            COUNT(CASE WHEN a.status = 'lunas' THEN 1 END) as lunas,
            COUNT(*) as total
         FROM angsuran a JOIN pinjaman p ON a.pinjaman_id = p.id
         WHERE p.petugas_id = ? AND a.jatuh_tempo BETWEEN ? AND ?",
        [$petugas_id, $start, $end]
    );
    $col    = $collection[0] ?? ['lunas' => 0, 'total' => 1];
    $col_rate = $col['total'] > 0 ? round($col['lunas'] / $col['total'] * 100, 1) : 0;

    $rk = floatval($real_kutipan[0]['total'] ?? 0);
    $rn = intval($real_nasabah[0]['total'] ?? 0);
    $rp = intval($real_pinjaman[0]['total'] ?? 0);
    $tk = floatval($t['target_kutipan']);

    return [
        'bulan'                => $bulan,
        'petugas_id'           => $petugas_id,
        'target_kutipan'       => $tk,
        'realisasi_kutipan'    => $rk,
        'pct_kutipan'          => $tk > 0 ? round($rk / $tk * 100, 1) : 0,
        'target_nasabah_baru'  => (int)$t['target_nasabah_baru'],
        'realisasi_nasabah'    => $rn,
        'target_pinjaman_baru' => (int)$t['target_pinjaman_baru'],
        'realisasi_pinjaman'   => $rp,
        'target_collection_rate' => floatval($t['target_collection_rate']),
        'realisasi_collection_rate' => $col_rate,
    ];
}

// ================================================================
// G. PENAGIHAN SYSTEM INTEGRATION
// ================================================================

/**
 * Auto-create penagihan records for overdue installments
 * This function should be called by a scheduled task (cron job)
 */
function autoCreatePenagihanOverdue() {
    // Get overdue installments that don't have penagihan records
    $overdue = query("
        SELECT a.id, a.pinjaman_id, a.no_angsuran, a.jatuh_tempo, a.total_angsuran,
               p.kode_pinjaman, n.nama as nama_nasabah, n.telp, n.alamat
        FROM angsuran a
        JOIN pinjaman p ON a.pinjaman_id = p.id
        JOIN nasabah n ON p.nasabah_id = n.id
        WHERE a.status != 'lunas'
        AND a.jatuh_tempo < CURDATE()
        AND NOT EXISTS (
            SELECT 1 FROM penagihan pen WHERE pen.angsuran_id = a.id
        )
        ORDER BY a.jatuh_tempo ASC
    ");
    
    if (!$overdue || !is_array($overdue) || empty($overdue)) {
        return ['success' => true, 'message' => 'No overdue installments found', 'count' => 0];
    }
    
    $created = 0;
    foreach ($overdue as $item) {
        // Calculate days overdue
        $hari_telat = (strtotime(date('Y-m-d')) - strtotime($item['jatuh_tempo'])) / 86400;
        
        // Determine jenis_penagihan_id based on days overdue
        if ($hari_telat <= 7) {
            $jenis_penagihan_id = 2; // TELAT_1_7
        } elseif ($hari_telat <= 14) {
            $jenis_penagihan_id = 3; // TELAT_8_14
        } elseif ($hari_telat <= 30) {
            $jenis_penagihan_id = 4; // TELAT_15_30
        } else {
            $jenis_penagihan_id = 5; // TELAT_30_PLUS
        }
        
        // Create penagihan record
        query("INSERT INTO penagihan (pinjaman_id, angsuran_id, jenis_penagihan_id, status, tanggal_jatuh_tempo, hasil) VALUES (?, ?, ?, 'pending', ?, 'Angsuran jatuh tempo - belum dibayar')", 
            [$item['pinjaman_id'], $item['id'], $jenis_penagihan_id, $item['jatuh_tempo']]);
        
        $penagihan_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
        
        // Log creation
        query("INSERT INTO penagihan_log (penagihan_id, aksi, hasil) VALUES (?, 'auto_create', 'Auto-created penagihan record for overdue installment: " . $hari_telat . " days late')", 
            [$penagihan_id]);
        
        $created++;
    }
    
    return ['success' => true, 'message' => "Created {$created} penagihan records", 'count' => $created];
}
