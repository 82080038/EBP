<?php
/**
 * Accounting Helper Functions
 * 
 * Functions for recording journal entries and managing accounting transactions
 */

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/config/database.php';

/**
 * Generate journal number
 */
function generateNomorJurnal() {
    $kantor_id = 1; // Single office
    $prefix = 'JRNL-' . date('Ymd') . '-' . str_pad($kantor_id, 3, '0', STR_PAD_LEFT) . '-';
    $last_journal = query("SELECT nomor_jurnal FROM jurnal WHERE nomor_jurnal LIKE ? ORDER BY id DESC LIMIT 1", [$prefix . '%']);
    
    if ($last_journal) {
        $last_num = (int)substr($last_journal[0]['nomor_jurnal'], -4);
        $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $new_num = '0001';
    }
    
    return $prefix . $new_num;
}

/**
 * Generate transaction number
 */
function generateNomorTransaksi($tipe) {
    $kantor_id = 1; // Single office
    $prefix_map = [
        'pinjaman' => 'PINJ',
        'angsuran' => 'ANGS',
        'pembayaran' => 'BYR',
        'pengeluaran' => 'PENG',
        'kas_masuk' => 'KM',
        'kas_keluar' => 'KK',
        'kas_bon' => 'KSB',
        'kas_setoran' => 'KST',
        'rekonsiliasi' => 'REK'
    ];
    
    $prefix = ($prefix_map[$tipe] ?? 'TRX') . '-' . date('Ymd') . '-' . str_pad($kantor_id, 3, '0', STR_PAD_LEFT) . '-';
    $last_trans = query("SELECT nomor_transaksi FROM transaksi_log WHERE nomor_transaksi LIKE ? ORDER BY id DESC LIMIT 1", [$prefix . '%']);
    
    if ($last_trans) {
        $last_num = (int)substr($last_trans[0]['nomor_transaksi'], -4);
        $new_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $new_num = '0001';
    }
    
    return $prefix . $new_num;
}

/**
 * Create journal entry
 */
function createJurnal($data) {
    $nomor_jurnal = $data['nomor_jurnal'] ?? generateNomorJurnal();
    $tanggal_jurnal = $data['tanggal_jurnal'] ?? date('Y-m-d');
    $tanggal_transaksi = $data['tanggal_transaksi'] ?? date('Y-m-d');
    $keterangan = $data['keterangan'] ?? '';
    $kantor_id = 1; // Single office
    $created_by = $data['created_by'] ?? getCurrentUser()['id'];
    $details = $data['details'] ?? []; // Array of debit/credit entries
    
    // Validate debit equals credit
    $total_debit = array_sum(array_column($details, 'debit'));
    $total_kredit = array_sum(array_column($details, 'kredit'));
    
    if (abs($total_debit - $total_kredit) > 0.01) {
        return [
            'success' => false,
            'error' => 'Debit tidak sama dengan Kredit',
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit
        ];
    }
    
    // Insert journal header
    $result = query("INSERT INTO jurnal (nomor_jurnal, tanggal_jurnal, tanggal_transaksi, keterangan, cabang_id, created_by) VALUES (?, ?, ?, ?, ?, ?)", [
        $nomor_jurnal,
        $tanggal_jurnal,
        $tanggal_transaksi,
        $keterangan,
        $kantor_id,
        $created_by
    ]);
    
    if (!$result) {
        return ['success' => false, 'error' => 'Gagal membuat jurnal header'];
    }
    
    $jurnal_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
    
    // Insert journal details
    foreach ($details as $detail) {
        $detail['jurnal_id'] = $jurnal_id;
        $detail_result = query("INSERT INTO jurnal_detail (jurnal_id, akun_kode, akun_nama, debit, kredit, referensi_tipe, referensi_id) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $detail['jurnal_id'],
            $detail['akun_kode'],
            $detail['akun_nama'],
            $detail['debit'] ?? 0,
            $detail['kredit'] ?? 0,
            $detail['referensi_tipe'] ?? null,
            $detail['referensi_id'] ?? null
        ]);
        
        if (!$detail_result) {
            return ['success' => false, 'error' => 'Gagal membuat jurnal detail'];
        }
    }
    
    return [
        'success' => true,
        'jurnal_id' => $jurnal_id,
        'nomor_jurnal' => $nomor_jurnal
    ];
}

/**
 * Log transaction
 */
function logTransaksi($data) {
    $nomor_transaksi = $data['nomor_transaksi'] ?? generateNomorTransaksi($data['tipe_transaksi']);
    $tanggal_transaksi = $data['tanggal_transaksi'] ?? date('Y-m-d');
    $tipe_transaksi = $data['tipe_transaksi'];
    $jumlah = $data['jumlah'];
    $kantor_id = 1; // Single office
    $nasabah_id = $data['nasabah_id'] ?? null;
    $pinjaman_id = $data['pinjaman_id'] ?? null;
    $angsuran_id = $data['angsuran_id'] ?? null;
    $user_id = $data['user_id'] ?? getCurrentUser()['id'];
    $keterangan = $data['keterangan'] ?? '';
    $status = $data['status'] ?? 'pending';
    
    $result = query("INSERT INTO transaksi_log (nomor_transaksi, tanggal_transaksi, tipe_transaksi, jumlah, cabang_id, nasabah_id, pinjaman_id, angsuran_id, user_id, keterangan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
        $nomor_transaksi,
        $tanggal_transaksi,
        $tipe_transaksi,
        $jumlah,
        $kantor_id,
        $nasabah_id,
        $pinjaman_id,
        $angsuran_id,
        $user_id,
        $keterangan,
        $status
    ]);
    
    if (!$result) {
        return ['success' => false, 'error' => 'Gagal log transaksi'];
    }
    
    $transaksi_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
    
    return [
        'success' => true,
        'transaksi_id' => $transaksi_id,
        'nomor_transaksi' => $nomor_transaksi
    ];
}

/**
 * Link transaction to journal
 */
function linkTransaksiToJurnal($transaksi_id, $jurnal_id) {
    $result = query("UPDATE transaksi_log SET jurnal_id = ?, status = 'posted' WHERE id = ?", [$jurnal_id, $transaksi_id]);
    
    if ($result) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'Gagal link transaksi ke jurnal'];
    }
}

/**
 * Get account by code
 */
function getAkun($kode) {
    $akun = query("SELECT * FROM akun WHERE kode = ?", [$kode]);
    return $akun[0] ?? null;
}

/**
 * Get all accounts
 */
function getAllAkun($tipe = null) {
    if ($tipe) {
        return query("SELECT * FROM akun WHERE tipe = ? AND is_active = 1 ORDER BY kode", [$tipe]);
    }
    return query("SELECT * FROM akun WHERE is_active = 1 ORDER BY kode");
}

/**
 * Post journal entry for pinjaman
 */
function postJurnalPinjaman($pinjaman_id, $cabang_id) {
    $pinjaman = query("SELECT * FROM pinjaman WHERE id = ?", [$pinjaman_id]);
    if (!$pinjaman) {
        return ['success' => false, 'error' => 'Pinjaman tidak ditemukan'];
    }
    $pinjaman = $pinjaman[0];
    
    $user_id = getCurrentUser()['id'];
    
    // Log transaction
    $trans_result = logTransaksi([
        'tipe_transaksi' => 'pinjaman',
        'jumlah' => $pinjaman['plafon'],
        'cabang_id' => $cabang_id,
        'nasabah_id' => $pinjaman['nasabah_id'],
        'pinjaman_id' => $pinjaman_id,
        'keterangan' => "Pencairan pinjaman {$pinjaman['kode_pinjaman']}",
        'status' => 'pending'
    ]);
    
    if (!$trans_result['success']) {
        return $trans_result;
    }
    
    // Create journal entry
    // Debit: Piutang Pinjaman (1-2001)
    // Credit: Kas Cabang (1-1002)
    $jurnal_result = createJurnal([
        'cabang_id' => $cabang_id,
        'created_by' => $user_id,
        'keterangan' => "Pencairan pinjaman {$pinjaman['kode_pinjaman']} untuk nasabah",
        'tanggal_transaksi' => $pinjaman['tanggal_akad'],
        'details' => [
            [
                'akun_kode' => '1-2001',
                'akun_nama' => 'Piutang Pinjaman',
                'debit' => $pinjaman['plafon'],
                'kredit' => 0,
                'referensi_tipe' => 'pinjaman',
                'referensi_id' => $pinjaman_id
            ],
            [
                'akun_kode' => '1-1002',
                'akun_nama' => 'Kas Cabang',
                'debit' => 0,
                'kredit' => $pinjaman['plafon'],
                'referensi_tipe' => 'pinjaman',
                'referensi_id' => $pinjaman_id
            ]
        ]
    ]);
    
    if (!$jurnal_result['success']) {
        return $jurnal_result;
    }
    
    // Link transaction to journal
    linkTransaksiToJurnal($trans_result['transaksi_id'], $jurnal_result['jurnal_id']);
    
    return [
        'success' => true,
        'transaksi_id' => $trans_result['transaksi_id'],
        'jurnal_id' => $jurnal_result['jurnal_id']
    ];
}

/**
 * Post journal entry for pembayaran angsuran
 */
function postJurnalPembayaran($pembayaran_id, $cabang_id) {
    $pembayaran = query("SELECT p.*, a.pinjaman_id, a.nominal as angsuran_nominal FROM pembayaran p LEFT JOIN angsuran a ON p.angsuran_id = a.id WHERE p.id = ?", [$pembayaran_id]);
    if (!$pembayaran) {
        return ['success' => false, 'error' => 'Pembayaran tidak ditemukan'];
    }
    $pembayaran = $pembayaran[0];
    
    $user_id = getCurrentUser()['id'];
    
    // Log transaction
    $trans_result = logTransaksi([
        'tipe_transaksi' => 'pembayaran',
        'jumlah' => $pembayaran['jumlah_bayar'],
        'cabang_id' => $cabang_id,
        'pinjaman_id' => $pembayaran['pinjaman_id'],
        'angsuran_id' => $pembayaran['angsuran_id'],
        'keterangan' => "Pembayaran angsuran",
        'status' => 'pending'
    ]);
    
    if (!$trans_result['success']) {
        return $trans_result;
    }
    
    // Calculate bunga and pokok portions
    $bunga = $pembayaran['denda'] ?? 0;
    $pokok = $pembayaran['jumlah_bayar'] - $bunga;
    
    // Create journal entry
    // Debit: Kas Cabang (1-1002)
    // Credit: Piutang Pinjaman (1-2001) - pokok
    // Credit: Pendapatan Bunga (4-1001) - bunga
    $jurnal_result = createJurnal([
        'cabang_id' => $cabang_id,
        'created_by' => $user_id,
        'keterangan' => "Pembayaran angsuran {$pembayaran['kode_pembayaran']}",
        'tanggal_transaksi' => $pembayaran['tanggal_bayar'],
        'details' => [
            [
                'akun_kode' => '1-1002',
                'akun_nama' => 'Kas Cabang',
                'debit' => $pembayaran['jumlah_bayar'],
                'kredit' => 0,
                'referensi_tipe' => 'pembayaran',
                'referensi_id' => $pembayaran_id
            ],
            [
                'akun_kode' => '1-2001',
                'akun_nama' => 'Piutang Pinjaman',
                'debit' => 0,
                'kredit' => $pokok,
                'referensi_tipe' => 'pembayaran',
                'referensi_id' => $pembayaran_id
            ],
            [
                'akun_kode' => '4-1001',
                'akun_nama' => 'Pendapatan Bunga Pinjaman',
                'debit' => 0,
                'kredit' => $bunga,
                'referensi_tipe' => 'pembayaran',
                'referensi_id' => $pembayaran_id
            ]
        ]
    ]);
    
    if (!$jurnal_result['success']) {
        return $jurnal_result;
    }
    
    // Link transaction to journal
    linkTransaksiToJurnal($trans_result['transaksi_id'], $jurnal_result['jurnal_id']);
    
    return [
        'success' => true,
        'transaksi_id' => $trans_result['transaksi_id'],
        'jurnal_id' => $jurnal_result['jurnal_id']
    ];
}

/**
 * Post journal entry for pengeluaran
 */
function postJurnalPengeluaran($pengeluaran_id, $cabang_id) {
    $pengeluaran = query("SELECT * FROM pengeluaran WHERE id = ?", [$pengeluaran_id]);
    if (!$pengeluaran) {
        return ['success' => false, 'error' => 'Pengeluaran tidak ditemukan'];
    }
    $pengeluaran = $pengeluaran[0];
    
    $user_id = getCurrentUser()['id'];
    
    // Log transaction
    $trans_result = logTransaksi([
        'tipe_transaksi' => 'pengeluaran',
        'jumlah' => $pengeluaran['jumlah'],
        'cabang_id' => $cabang_id,
        'keterangan' => $pengeluaran['keterangan'],
        'status' => 'pending'
    ]);
    
    if (!$trans_result['success']) {
        return $trans_result;
    }
    
    // Create journal entry
    // Debit: Beban Operasional (5-2002)
    // Credit: Kas Cabang (1-1002)
    $jurnal_result = createJurnal([
        'cabang_id' => $cabang_id,
        'created_by' => $user_id,
        'keterangan' => "Pengeluaran: {$pengeluaran['keterangan']}",
        'tanggal_transaksi' => $pengeluaran['tanggal'],
        'details' => [
            [
                'akun_kode' => '5-2002',
                'akun_nama' => 'Beban Operasional',
                'debit' => $pengeluaran['jumlah'],
                'kredit' => 0,
                'referensi_tipe' => 'pengeluaran',
                'referensi_id' => $pengeluaran_id
            ],
            [
                'akun_kode' => '1-1002',
                'akun_nama' => 'Kas Cabang',
                'debit' => 0,
                'kredit' => $pengeluaran['jumlah'],
                'referensi_tipe' => 'pengeluaran',
                'referensi_id' => $pengeluaran_id
            ]
        ]
    ]);
    
    if (!$jurnal_result['success']) {
        return $jurnal_result;
    }
    
    // Link transaction to journal
    linkTransaksiToJurnal($trans_result['transaksi_id'], $jurnal_result['jurnal_id']);
    
    return [
        'success' => true,
        'transaksi_id' => $trans_result['transaksi_id'],
        'jurnal_id' => $jurnal_result['jurnal_id']
    ];
}
