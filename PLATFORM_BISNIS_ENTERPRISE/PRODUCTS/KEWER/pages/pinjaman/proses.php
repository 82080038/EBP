<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';
require_once BASE_PATH . '/includes/auto_confirm.php';
requireLogin();

// Permission check
if (!hasPermission('pinjaman.approve')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if (!$action || !$id) {
    header('Location: ' . baseUrl('pages/pinjaman/index.php'));
    exit();
}

// Get pinjaman data (remove cabang_id restriction for simulation)
$pinjaman = query("SELECT * FROM pinjaman WHERE id = ?", [$id]);

if (!$pinjaman) {
    header('Location: ' . baseUrl('pages/pinjaman/index.php'));
    exit();
}

$pinjaman = $pinjaman[0];

switch ($action) {
    case 'approve':
        if ($pinjaman['status'] !== 'pengajuan') {
            $_SESSION['error'] = 'Hanya dapat menyetujui pinjaman dengan status pengajuan';
        } else {
            // Skip family risk check for simulation - approve directly
            // Check if auto-confirm should be applied
            $currentUser = getCurrentUser();
            $canAutoConfirm = hasPermission('pinjaman.auto_confirm');
            
            if ($canAutoConfirm) {
                $autoConfirmResult = applyAutoConfirm($id, $currentUser['id']);
                if ($autoConfirmResult['success']) {
                    $_SESSION['success'] = 'Pinjaman berhasil di-auto-confirm dan diaktifkan';
                } else {
                    // Auto-confirm failed, fall back to manual approval
                    $_SESSION['warning'] = 'Auto-confirm tidak dapat diterapkan: ' . $autoConfirmResult['message'] . '. Melakukan approval manual.';
                    // Continue to manual approval below
                }
            }
            
            // Manual approval (if auto-confirm not applied or failed)
            if (!isset($autoConfirmResult) || !$autoConfirmResult['success']) {
                    // Update status to disetujui
                    $result = query("UPDATE pinjaman SET status = 'disetujui' WHERE id = ?", [$id]);
                    
                    if ($result) {
                        // Create loan schedule
                        $frek = $pinjaman['frekuensi_id'] ?? $pinjaman['frekuensi'] ?? 'bulanan';
                        createLoanSchedule($id, $pinjaman['plafon'], $pinjaman['tenor'], $pinjaman['bunga_per_bulan'], $pinjaman['tanggal_akad'], $frek);
                        
                        // Update to aktif
                        query("UPDATE pinjaman SET status = 'aktif' WHERE id = ?", [$id]);
                        
                        $_SESSION['success'] = 'Pinjaman berhasil disetujui dan diaktifkan';

                        // Send notification
                        $nasabah_result = query("SELECT telp FROM nasabah WHERE id = ?", [$pinjaman['nasabah_id']]);
                        $nasabah = is_array($nasabah_result) && isset($nasabah_result[0]) ? $nasabah_result[0] : ['telp' => null];
                        $message = "Pinjaman Anda dengan kode {$pinjaman['kode_pinjaman']} telah disetujui. Plafon: " . formatRupiah($pinjaman['plafon']);
                        if ($nasabah['telp'] && isFeatureEnabled('wa_notifikasi')) {
                            sendWhatsApp($nasabah['telp'], $message);
                        }
                    } else {
                        $_SESSION['error'] = 'Gagal menyetujui pinjaman';
                    }
                }
            }
        break;
        
    case 'reject':
        if ($pinjaman['status'] !== 'pengajuan') {
            $_SESSION['error'] = 'Hanya dapat menolak pinjaman dengan status pengajuan';
        } else {
            $result = query("UPDATE pinjaman SET status = 'ditolak' WHERE id = ?", [$id]);
            
            if ($result) {
                $_SESSION['success'] = 'Pinjaman berhasil ditolak';
                
                // Send notification
                $nasabah_result = query("SELECT telp FROM nasabah WHERE id = ?", [$pinjaman['nasabah_id']]);
                $nasabah = is_array($nasabah_result) && isset($nasabah_result[0]) ? $nasabah_result[0] : ['telp' => null];
                $message = "Mohon maaf, pengajuan pinjaman Anda dengan kode {$pinjaman['kode_pinjaman']} ditolak.";
                if ($nasabah['telp'] && isFeatureEnabled('wa_notifikasi')) {
                    sendWhatsApp($nasabah['telp'], $message);
                }
            } else {
                $_SESSION['error'] = 'Gagal menolak pinjaman';
            }
        }
        break;
        
    case 'lunas':
        if ($pinjaman['status'] !== 'aktif') {
            $_SESSION['error'] = 'Hanya dapat melunasi pinjaman dengan status aktif';
        } else {
            // Check if all installments are paid
            $angsuran_result = query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas FROM angsuran WHERE pinjaman_id = ?", [$id]);
            $angsuran = is_array($angsuran_result) && isset($angsuran_result[0]) ? $angsuran_result[0] : ['total' => 0, 'lunas' => 0];
            
            if ($angsuran['total'] == $angsuran['lunas']) {
                $result = query("UPDATE pinjaman SET status = 'lunas' WHERE id = ?", [$id]);
                
                if ($result) {
                    $_SESSION['success'] = 'Pinjaman berhasil dilunasi';
                } else {
                    $_SESSION['error'] = 'Gagal melunasi pinjaman';
                }
            } else {
                $_SESSION['error'] = 'Masih ada angsuran yang belum lunas';
            }
        }
        break;
        
    default:
        $_SESSION['error'] = 'Aksi tidak valid';
        break;
}

header('Location: index.php');
exit();
?>
