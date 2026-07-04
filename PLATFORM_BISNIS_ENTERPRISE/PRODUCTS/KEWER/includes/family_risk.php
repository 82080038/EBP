<?php
/**
 * Family Risk Management System
 * 
 * Handles detection and prevention of loans to family members of problematic borrowers
 * 
 * Features:
 * - Track family relationships
 * - Assess family risk level
 * - Auto-detect problematic families
 * - Prevent loans to high-risk families
 * - Blacklist family feature
 */

require_once __DIR__ . '/database_class.php';

class FamilyRisk {
    private $db;
    private $cabangId;
    
    public function __construct($cabangId = null) {
        $this->db = db();
        $this->cabangId = $cabangId;
    }
    
    /**
     * Check if nasabah belongs to problematic family
     */
    public function checkFamilyRisk($nasabahId) {
        // Get nasabah information
        $sql = "SELECT * FROM nasabah WHERE id = ?";
        $nasabah = $this->db->selectOne($sql, [$nasabahId]);
        
        if (!$nasabah) {
            return ['risk' => 'unknown', 'message' => 'Nasabah tidak ditemukan'];
        }
        
        // Check if nasabah is already blacklisted
        if ($nasabah['status'] === 'blacklist') {
            return [
                'risk' => 'sangat_tinggi',
                'message' => 'Nasabah sudah di-blacklist',
                'reason' => 'Status nasabah: blacklist'
            ];
        }
        
        // Check family risk from family_risk table
        $sql = "SELECT * FROM family_risk 
                WHERE cabang_id = ? 
                AND alamat_keluarga LIKE CONCAT('%', SUBSTRING(?, 1, 50), '%')
                AND status = 'aktif'";
        
        $familyRisk = $this->db->selectOne($sql, [$this->cabangId, $nasabah['alamat']]);
        
        if ($familyRisk) {
            return [
                'risk' => $familyRisk['tingkat_risiko'],
                'message' => 'Nasabah berasal dari keluarga bermasalah',
                'reason' => $familyRisk['alasan'],
                'total_pinjaman_gagal' => $familyRisk['total_pinjaman_gagal'],
                'total_nasabah_bermasalah' => $familyRisk['total_nasabah_bermasalah']
            ];
        }
        
        // Check if nasabah has family members with problematic loans
        $sql = "SELECT n.* FROM nasabah n
                JOIN pinjaman p ON n.id = p.nasabah_id
                WHERE n.id != ?
                AND n.cabang_id = ?
                AND (n.alamat LIKE CONCAT('%', SUBSTRING(?, 1, 30), '%')
                     OR n.nama_ayah = ? 
                     OR n.nama_ibu = ?)
                AND p.status = 'macet'";
        
        $problematicFamily = $this->db->select($sql, [
            $nasabahId,
            $this->cabangId,
            $nasabah['alamat'],
            $nasabah['nama_ayah'] ?? '',
            $nasabah['nama_ibu'] ?? ''
        ]);
        
        if (!empty($problematicFamily)) {
            return [
                'risk' => 'tinggi',
                'message' => 'Keluarga memiliki riwayat pinjaman bermasalah',
                'problematic_members' => count($problematicFamily),
                'members' => $problematicFamily
            ];
        }
        
        // Check nasabah's risk score
        if ($nasabah['skor_risiko_keluarga'] > 0) {
            $riskLevel = $this->getRiskLevelFromScore($nasabah['skor_risiko_keluarga']);
            return [
                'risk' => $riskLevel,
                'message' => 'Skor risiko keluarga: ' . $nasabah['skor_risiko_keluarga'],
                'risk_score' => $nasabah['skor_risiko_keluarga']
            ];
        }
        
        return ['risk' => 'rendah', 'message' => 'Tidak ada risiko keluarga terdeteksi'];
    }
    
    /**
     * Get risk level from score
     */
    private function getRiskLevelFromScore($score) {
        if ($score >= 30) return 'sangat_tinggi';
        if ($score >= 20) return 'tinggi';
        if ($score >= 10) return 'sedang';
        return 'rendah';
    }
    
    /**
     * Add family relationship to nasabah
     */
    public function addFamilyRelationship($nasabahId, $data) {
        $sql = "INSERT INTO nasabah_family_link 
                (nasabah_id, jenis_hubungan, nama_keluarga, ktp_keluarga, alamat_keluarga, telp_keluarga, catatan)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $nasabahId,
            $data['jenis_hubungan'],
            $data['nama_keluarga'],
            $data['ktp_keluarga'] ?? null,
            $data['alamat_keluarga'] ?? null,
            $data['telp_keluarga'] ?? null,
            $data['catatan'] ?? null
        ]);
    }
    
    /**
     * Get family relationships for nasabah
     */
    public function getFamilyRelationships($nasabahId) {
        $sql = "SELECT * FROM nasabah_family_link WHERE nasabah_id = ?";
        return $this->db->select($sql, [$nasabahId]);
    }
    
    /**
     * Check if family member already has problematic loan
     */
    public function checkFamilyMemberLoan($ktp) {
        $sql = "SELECT n.nama, p.kode_pinjaman, p.status, p.total_pembayaran
                FROM nasabah n
                JOIN pinjaman p ON n.id = p.nasabah_id
                WHERE n.ktp = ?
                AND p.status IN ('aktif', 'macet')";
        
        return $this->db->selectOne($sql, [$ktp]);
    }
    
    /**
     * Create or update family risk record
     */
    public function createFamilyRisk($data) {
        // Check if family risk already exists for this address
        $sql = "SELECT * FROM family_risk 
                WHERE cabang_id = ? 
                AND alamat_keluarga LIKE CONCAT('%', SUBSTRING(?, 1, 50), '%')";
        
        $existing = $this->db->selectOne($sql, [$this->cabangId, $data['alamat_keluarga']]);
        
        if ($existing) {
            // Update existing
            $sql = "UPDATE family_risk 
                    SET tingkat_risiko = ?,
                        total_pinjaman_gagal = total_pinjaman_gagal + ?,
                        total_nasabah_bermasalah = total_nasabah_bermasalah + ?,
                        alasan = CONCAT(alasan, '; ', ?),
                        updated_at = NOW()
                    WHERE id = ?";
            
            return $this->db->update($sql, [
                $data['tingkat_risiko'],
                $data['total_pinjaman_gagal'] ?? 1,
                $data['total_nasabah_bermasalah'] ?? 1,
                $data['alasan'],
                $existing['id']
            ]);
        } else {
            // Create new
            $sql = "INSERT INTO family_risk 
                    (cabang_id, nama_kepala_keluarga, alamat_keluarga, tingkat_risiko, total_pinjaman_gagal, total_nasabah_bermasalah, tanggal_ditandai, alasan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->insert($sql, [
                $data['cabang_id'] ?? $this->cabangId,
                $data['nama_kepala_keluarga'],
                $data['alamat_keluarga'],
                $data['tingkat_risiko'],
                $data['total_pinjaman_gagal'] ?? 1,
                $data['total_nasabah_bermasalah'] ?? 1,
                $data['tanggal_ditandai'] ?? date('Y-m-d'),
                $data['alasan']
            ]);
        }
    }
    
    /**
     * Blacklist entire family
     */
    public function blacklistFamily($alamatKel, $alasan) {
        // Update family_risk to very high risk
        $sql = "UPDATE family_risk 
                SET tingkat_risiko = 'sangat_tinggi',
                    alasan = CONCAT(alasan, '; BLACKLISTED: ', ?),
                    updated_at = NOW()
                WHERE cabang_id = ? 
                AND alamat_keluarga LIKE CONCAT('%', SUBSTRING(?, 1, 50), '%')";
        
        $this->db->update($sql, [$alasan, $this->cabangId, $alamatKel]);
        
        // Blacklist all nasabah from this address
        $sql = "UPDATE nasabah 
                SET status = 'blacklist',
                    catatan_risiko = CONCAT(IFNULL(catatan_risiko, ''), '; BLACKLISTED FAMILY: ', ?),
                    updated_at = NOW()
                WHERE cabang_id = ? 
                AND alamat LIKE CONCAT('%', SUBSTRING(?, 1, 50), '%')";
        
        return $this->db->update($sql, [$alasan, $this->cabangId, $alamatKel]);
    }
    
    /**
     * Get all high-risk families
     */
    public function getHighRiskFamilies() {
        $sql = "SELECT * FROM family_risk 
                WHERE cabang_id = ? 
                AND status = 'aktif'
                AND tingkat_risiko IN ('tinggi', 'sangat_tinggi')
                ORDER BY tingkat_risiko DESC, total_pinjaman_gagal DESC";
        
        return $this->db->select($sql, [$this->cabangId]);
    }
    
    /**
     * Validate loan application for family risk
     * Returns true if loan can be approved, false if blocked
     */
    public function validateLoanApplication($nasabahId, $plafon) {
        $risk = $this->checkFamilyRisk($nasabahId);
        
        // Get threshold from settings
        $thresholdSql = "SELECT setting_value FROM settings WHERE setting_key = 'family_risk_threshold'";
        $threshold = $this->db->selectOne($thresholdSql);
        $riskThreshold = $threshold ? (int)$threshold['setting_value'] : 20;
        
        // Get verification threshold
        $verifySql = "SELECT setting_value FROM settings WHERE setting_key = 'require_family_verification'";
        $verifyThreshold = $this->db->selectOne($verifySql);
        $verifyAmount = $verifyThreshold ? (int)$verifyThreshold['setting_value'] : 500000;
        
        $result = [
            'approved' => true,
            'requires_verification' => false,
            'blocked' => false,
            'risk_level' => $risk['risk'],
            'message' => $risk['message']
        ];
        
        // Block very high risk
        if ($risk['risk'] === 'sangat_tinggi') {
            $result['approved'] = false;
            $result['blocked'] = true;
            $result['message'] = 'Pinjaman ditolak: Keluarga berisiko sangat tinggi';
        }
        
        // Require verification for high risk or large amounts
        if (($risk['risk'] === 'tinggi' || $risk['risk'] === 'sedang') || $plafon >= $verifyAmount) {
            $result['requires_verification'] = true;
            $result['message'] .= '. Memerlukan verifikasi keluarga tambahan';
        }
        
        // Check risk score
        if (isset($risk['risk_score']) && $risk['risk_score'] >= $riskThreshold) {
            $result['approved'] = false;
            $result['blocked'] = true;
            $result['message'] = 'Pinjaman ditolak: Skor risiko keluarga melebihi threshold';
        }
        
        return $result;
    }
    
    /**
     * Log risk event
     */
    public function logRiskEvent($data) {
        $sql = "INSERT INTO loan_risk_log 
                (cabang_id, nasabah_id, pinjaman_id, jenis_risiko, tingkat_risiko, deskripsi, tindakan_diambil, tanggal_kejadian)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['cabang_id'] ?? $this->cabangId,
            $data['nasabah_id'],
            $data['pinjaman_id'],
            $data['jenis_risiko'],
            $data['tingkat_risiko'],
            $data['deskripsi'],
            $data['tindakan_diambil'],
            $data['tanggal_kejadian'] ?? date('Y-m-d')
        ]);
    }
    
    /**
     * Get risk log for nasabah
     */
    public function getRiskLog($nasabahId) {
        $sql = "SELECT * FROM loan_risk_log WHERE nasabah_id = ? ORDER BY tanggal_kejadian DESC";
        return $this->db->select($sql, [$nasabahId]);
    }
    
    /**
     * Update nasabah risk score
     */
    public function updateNasabahRiskScore($nasabahId, $scoreIncrease) {
        $sql = "UPDATE nasabah SET skor_risiko_keluarga = skor_risiko_keluarga + ? WHERE id = ?";
        return $this->db->update($sql, [$scoreIncrease, $nasabahId]);
    }
}
