<?php
// Auto-Confirm System for Loan Approvals
// This system allows owner/admin to set automatic approval rules for loan applications
// made by field officers (petugas) at branches

/**
 * Check if auto-confirm is enabled for a specific cabang or globally
 * 
 * @param int|null $cabang_id Branch ID (null for global setting)
 * @return array Auto-confirm settings
 */
function getAutoConfirmSettings($cabang_id = null) {
    // First check for branch-specific setting
    if ($cabang_id) {
        $setting = query("SELECT * FROM auto_confirm_settings WHERE cabang_id = ? AND enabled = TRUE", [$cabang_id]);
        if ($setting && is_array($setting) && count($setting) > 0) {
            return $setting[0];
        }
    }
    
    // Fall back to global setting
    $setting = query("SELECT * FROM auto_confirm_settings WHERE cabang_id IS NULL", []);
    if ($setting && is_array($setting) && count($setting) > 0) {
        return $setting[0];
    }
    
    // Return default disabled settings
    return [
        'enabled' => false,
        'plafon_threshold' => 0,
        'tenor_limit' => 0,
        'max_risk_score' => 10,
        'require_nasabah_history' => true,
        'min_nasabah_history_months' => 3
    ];
}

/**
 * Check if a loan application qualifies for auto-confirmation
 * 
 * @param int $pinjaman_id Loan ID
 * @return array Result with 'approved' boolean and 'reason' string
 */
function checkAutoConfirmEligibility($pinjaman_id) {
    $pinjaman = query("SELECT p.*, n.kode_nasabah, c.id as cabang_id 
                      FROM pinjaman p 
                      JOIN nasabah n ON p.nasabah_id = n.id 
                      JOIN cabang c ON p.cabang_id = c.id 
                      WHERE p.id = ?", [$pinjaman_id]);
    
    if (!$pinjaman || !is_array($pinjaman) || count($pinjaman) === 0) {
        return ['approved' => false, 'reason' => 'Pinjaman tidak ditemukan'];
    }
    
    $pinjaman = $pinjaman[0];
    
    // Get auto-confirm settings for this branch
    $settings = getAutoConfirmSettings($pinjaman['cabang_id']);
    
    if (!$settings['enabled']) {
        return ['approved' => false, 'reason' => 'Auto-confirm tidak diaktifkan'];
    }
    
    // Check plafon threshold
    if ($settings['plafon_threshold'] > 0 && $pinjaman['plafon'] > $settings['plafon_threshold']) {
        return ['approved' => false, 'reason' => "Plafon melebihi batas auto-confirm (Rp" . number_format($settings['plafon_threshold'], 0, ',', '.') . ")"];
    }
    
    // Check tenor limit
    if ($settings['tenor_limit'] > 0 && $pinjaman['tenor'] > $settings['tenor_limit']) {
        return ['approved' => false, 'reason' => "Tenor melebihi batas auto-confirm ({$settings['tenor_limit']} bulan)"];
    }
    
    // Check nasabah history requirement
    if ($settings['require_nasabah_history']) {
        // Get nasabah's first loan date
        $firstLoan = query("SELECT MIN(created_at) as first_loan FROM pinjaman WHERE nasabah_id = ?", [$pinjaman['nasabah_id']]);
        
        if ($firstLoan && is_array($firstLoan) && count($firstLoan) > 0 && $firstLoan[0]['first_loan']) {
            $firstLoanDate = new DateTime($firstLoan[0]['first_loan']);
            $currentDate = new DateTime();
            $monthsSinceFirstLoan = $firstLoanDate->diff($currentDate)->m + ($firstLoanDate->diff($currentDate)->y * 12);
            
            if ($monthsSinceFirstLoan < $settings['min_nasabah_history_months']) {
                return ['approved' => false, 'reason' => "Nasabah belum memiliki riwayat cukup ({$monthsSinceFirstLoan} bulan, minimum {$settings['min_nasabah_history_months']} bulan)"];
            }
        } else {
            // New nasabah
            return ['approved' => false, 'reason' => "Nasabah baru belum memiliki riwayat pinjaman"];
        }
    }
    
    // Check family risk if available
    require_once __DIR__ . '/family_risk.php';
    $cabangId = $pinjaman['cabang_id'];
    $familyRisk = new FamilyRisk($cabangId);
    $riskCheck = $familyRisk->validateLoanApplication($pinjaman['nasabah_id'], $pinjaman['plafon']);
    
    if (!$riskCheck['approved']) {
        return ['approved' => false, 'reason' => "Family risk check gagal: " . $riskCheck['message']];
    }
    
    // Check risk score (if we have risk assessment data)
    // This would require integration with a risk assessment system
    // For now, we'll assume it passes if family risk passes
    
    return ['approved' => true, 'reason' => 'Memenuhi semua kriteria auto-confirm'];
}

/**
 * Apply auto-confirmation to a loan
 * 
 * @param int $pinjaman_id Loan ID
 * @param int $user_id User ID who triggered the auto-confirm
 * @return array Result
 */
function applyAutoConfirm($pinjaman_id, $user_id) {
    $eligibility = checkAutoConfirmEligibility($pinjaman_id);
    
    if (!$eligibility['approved']) {
        return [
            'success' => false,
            'message' => 'Auto-confirm tidak dapat diterapkan: ' . $eligibility['reason']
        ];
    }
    
    // Get loan details
    $pinjaman = query("SELECT * FROM pinjaman WHERE id = ?", [$pinjaman_id]);
    if (!$pinjaman || !is_array($pinjaman) || count($pinjaman) === 0) {
        return ['success' => false, 'message' => 'Pinjaman tidak ditemukan'];
    }
    $pinjaman = $pinjaman[0];
    
    // Update status to disetujui
    $result = query("UPDATE pinjaman SET status = 'disetujui' WHERE id = ?", [$pinjaman_id]);
    if (!$result) {
        return ['success' => false, 'message' => 'Gagal update status ke disetujui'];
    }
    
    // Create loan schedule
    require_once __DIR__ . '/functions.php';
    $frek = $pinjaman['frekuensi_id'] ?? $pinjaman['frekuensi'] ?? 'bulanan';
    $scheduleResult = createLoanSchedule($pinjaman_id, $pinjaman['plafon'], $pinjaman['tenor'], $pinjaman['bunga_per_bulan'], $pinjaman['tanggal_akad'], $frek);
    
    // Update to aktif
    query("UPDATE pinjaman SET status = 'aktif' WHERE id = ?", [$pinjaman_id]);
    
    // Mark as auto-confirmed
    query("UPDATE pinjaman SET auto_confirmed = TRUE, auto_confirmed_at = NOW(), auto_confirmed_by = ? WHERE id = ?", [$user_id, $pinjaman_id]);
    
    return [
        'success' => true,
        'message' => 'Pinjaman berhasil di-auto-confirm dan diaktifkan'
    ];
}

/**
 * Get auto-confirm statistics
 * 
 * @param int|null $cabang_id Branch ID (null for global)
 * @return array Statistics
 */
function getAutoConfirmStats($cabang_id = null) {
    $whereClause = $cabang_id ? "WHERE cabang_id = ?" : "WHERE cabang_id IS NOT NULL";
    $params = $cabang_id ? [$cabang_id] : [];
    
    $stats = query("
        SELECT 
            COUNT(*) as total_pinjaman,
            SUM(CASE WHEN auto_confirmed = TRUE THEN 1 ELSE 0 END) as auto_confirmed_count,
            SUM(CASE WHEN auto_confirmed = FALSE THEN 1 ELSE 0 END) as manual_approved_count,
            AVG(CASE WHEN auto_confirmed = TRUE THEN plafon ELSE NULL END) as avg_auto_confirmed_plafon
        FROM pinjaman
        $whereClause AND status = 'aktif'
    ", $params);
    
    return $stats && is_array($stats) && count($stats) > 0 ? $stats[0] : [
        'total_pinjaman' => 0,
        'auto_confirmed_count' => 0,
        'manual_approved_count' => 0,
        'avg_auto_confirmed_plafon' => 0
    ];
}
