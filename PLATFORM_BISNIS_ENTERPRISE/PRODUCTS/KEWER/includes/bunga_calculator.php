<?php
/**
 * Dynamic Interest Rate Calculator
 * 
 * Handles dynamic interest rate calculation based on:
 * - Loan type
 * - Tenor
 * - Customer risk profile
 * - Collateral type
 * - Branch-specific settings
 */

require_once __DIR__ . '/database_class.php';

class BungaCalculator {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get interest rate by frequency ID
     */
    private function getBungaByFrekuensi($frekuensi_id) {
        $result = query("SELECT * FROM setting_bunga WHERE frekuensi_id = ? AND is_active = 1 LIMIT 1", [$frekuensi_id]);
        if ($result && is_array($result) && !empty($result)) {
            return $result[0];
        }
        return null;
    }
    
    /**
     * Get default interest rate for loan type and tenor
     */
    public function getBungaDasar($jenisPinjaman, $tenor, $frekuensi = 'bulanan') {
        // Support both frekuensi_id (numeric) and frekuensi enum (string)
        $frekuensi_id = null;
        if (is_numeric($frekuensi)) {
            $frekuensi_id = $frekuensi;
            $frekuensi = getFrequencyCode($frekuensi_id);
        }
        
        $sql = "SELECT bunga_default, bunga_min, bunga_max 
                FROM setting_bunga 
                WHERE jenis_pinjaman = ? 
                AND tenor_min <= ? 
                AND tenor_max >= ? 
                AND frekuensi_id = ?
                AND status = 'aktif'
                AND cabang_id IS NULL";
        $result = $this->db->selectOne($sql, [$jenisPinjaman, $tenor, $tenor, $frekuensi_id]);
        
        // Fallback: try with frekuensi_id if available
        if (!$result && $frekuensi_id) {
            $sql3 = "SELECT bunga_default, bunga_min, bunga_max 
                    FROM setting_bunga 
                    WHERE jenis_pinjaman = ? 
                    AND tenor_min <= ? 
                    AND tenor_max >= ? 
                    AND frekuensi_id = ?
                    AND status = 'aktif'
                    AND cabang_id IS NULL";
            $result = $this->db->selectOne($sql3, [$jenisPinjaman, $tenor, $tenor, $frekuensi_id]);
        }
        
        // Fallback: try without frekuensi filter (backward compat)
        if (!$result) {
            $sql2 = "SELECT bunga_default, bunga_min, bunga_max 
                    FROM setting_bunga 
                    WHERE jenis_pinjaman = ? 
                    AND tenor_min <= ? 
                    AND tenor_max >= ? 
                    AND status = 'aktif'
                    AND cabang_id IS NULL";
            $result = $this->db->selectOne($sql2, [$jenisPinjaman, $tenor, $tenor]);
        }
        
        if (!$result) {
            // Return default if no specific setting found
            return [
                'bunga_default' => 2.5,
                'bunga_min' => 1.5,
                'bunga_max' => 4.0
            ];
        }
        
        return $result;
    }
    
    /**
     * Get risk adjustment based on customer history
     */
    public function getRisikoAdjustment($nasabahId) {
        // Check customer's payment history
        $sql = "SELECT 
                    COUNT(*) as total_pinjaman,
                    SUM(CASE WHEN p.status = 'lunas' THEN 1 ELSE 0 END) as lunas,
                    SUM(CASE WHEN a.status = 'telat' THEN 1 ELSE 0 END) as telat
                FROM pinjaman p
                LEFT JOIN angsuran a ON p.id = a.pinjaman_id
                WHERE p.nasabah_id = ?";
        
        $history = $this->db->selectOne($sql, [$nasabahId]);
        
        if (!$history || $history['total_pinjaman'] == 0) {
            // New customer - higher risk
            return 1.0; // +1% adjustment
        }
        
        $lunas_ratio = $history['lunas'] / $history['total_pinjaman'];
        $telat_ratio = $history['total_pinjaman'] > 0 ? $history['telat'] / $history['total_pinjaman'] : 0;
        
        // Risk scoring
        if ($lunas_ratio >= 0.9 && $telat_ratio <= 0.1) {
            return -0.5; // Excellent customer - lower rate
        } elseif ($lunas_ratio >= 0.7 && $telat_ratio <= 0.3) {
            return 0; // Good customer - standard rate
        } elseif ($telat_ratio > 0.5) {
            return 1.5; // High risk - higher rate
        } else {
            return 0.5; // Fair customer - slight increase
        }
    }
    
    /**
     * Get collateral adjustment
     */
    public function getJaminanAdjustment($jaminanTipe) {
        $adjustments = [
            'tanpa' => 1.0,        // No collateral - +1%
            'bpkb' => 0,            // BPKB - no adjustment
            'shm' => -0.5,          // SHM - -0.5%
            'ajb' => 0,             // AJB - no adjustment
            'tabungan' => -0.5      // Savings guarantee - -0.5%
        ];
        
        return $adjustments[$jaminanTipe] ?? 1.0;
    }
    
    /**
     * Calculate final interest rate
     */
    public function hitungBungaDinamis($jenisPinjaman, $tenor, $nasabahId = null, $jaminanTipe = 'tanpa', $frekuensi = 'bulanan') {
        // Support both frekuensi_id (numeric) and frekuensi enum (string)
        $frekuensi_code = getFrequencyCode($frekuensi);
        
        $bungaDasar = $this->getBungaDasar($jenisPinjaman, $tenor, $frekuensi);
        $risikoAdjustment = $nasabahId ? $this->getRisikoAdjustment($nasabahId) : 1.0;
        $jaminanAdjustment = $this->getJaminanAdjustment($jaminanTipe);
        
        $sukuBunga = $bungaDasar['bunga_default'] + $risikoAdjustment + $jaminanAdjustment;
        
        return [
            'suku_bunga' => round($sukuBunga, 2),
            'bunga_dasar' => $bungaDasar['bunga_default'],
            'risiko_adjustment' => $risikoAdjustment,
            'jaminan_adjustment' => $jaminanAdjustment,
            'jenis_pinjaman' => $jenisPinjaman,
            'tenor' => $tenor,
            'frekuensi' => $frekuensi_code
        ];
    }
    
    /**
     * Calculate loan schedule with dynamic interest
     */
    public function hitungAngsuran($pokok, $tenor, $sukuBunga, $metode = 'flat', $frekuensi = 'bulanan') {
        // Support both frekuensi_id (numeric) and frekuensi enum (string)
        $frekuensi_code = getFrequencyCode($frekuensi);
        
        $totalBunga = 0;
        $angsuranPokok = 0;
        $angsuranBunga = 0;
        $angsuranTotal = 0;
        
        // Convert monthly rate to per-period rate
        switch ($frekuensi_code) {
            case 'HARIAN':  $bungaPerPeriod = $sukuBunga / 30; break;
            case 'MINGGUAN': $bungaPerPeriod = $sukuBunga / 4; break;
            default:         $bungaPerPeriod = $sukuBunga; break;
        }
        
        switch ($metode) {
            case 'flat':
                // Flat rate: bunga tetap dari pokok awal
                $totalBunga = $pokok * ($bungaPerPeriod / 100) * $tenor;
                $angsuranPokok = $pokok / $tenor;
                $angsuranBunga = $totalBunga / $tenor;
                $angsuranTotal = $angsuranPokok + $angsuranBunga;
                break;
                
            case 'efektif':
                // Effective rate: bunga menurun dari sisa pokok
                $angsuranPokok = $pokok / $tenor;
                $sisaPokok = $pokok;
                $totalBunga = 0;
                
                for ($i = 0; $i < $tenor; $i++) {
                    $bungaPeriode = $sisaPokok * ($bungaPerPeriod / 100);
                    $totalBunga += $bungaPeriode;
                    $sisaPokok -= $angsuranPokok;
                }
                
                $angsuranBunga = $totalBunga / $tenor;
                $angsuranTotal = $angsuranPokok + $angsuranBunga;
                break;
                
            case 'anuitas':
                // Anuitas: angsuran tetap
                $i = $bungaPerPeriod / 100;
                $n = $tenor;
                $angsuranTotal = $pokok * ($i * pow(1 + $i, $n)) / (pow(1 + $i, $n) - 1);
                $totalPembayaran = $angsuranTotal * $tenor;
                $totalBunga = $totalPembayaran - $pokok;
                $angsuranPokok = $pokok / $tenor;
                $angsuranBunga = $angsuranTotal - $angsuranPokok;
                break;
        }
        
        return [
            'total_bunga' => round($totalBunga, 2),
            'total_pembayaran' => round($pokok + $totalBunga, 2),
            'angsuran_pokok' => round($angsuranPokok, 2),
            'angsuran_bunga' => round($angsuranBunga, 2),
            'angsuran_total' => round($angsuranTotal, 2),
            'metode' => $metode,
            'frekuensi_id' => $frekuensi_id,
            'frekuensi_code' => getFrequencyCode($frekuensi_id),
            'bunga_per_period' => round($bungaPerPeriod, 4)
        ];
    }
    
    /**
     * Get all interest rate settings for single office
     */
    public function getAllSettings() {
        $sql = "SELECT sb.*, c.nama_cabang 
                FROM setting_bunga sb
                LEFT JOIN cabang c ON 1=1
                WHERE sb.status = 'aktif' AND sb.cabang_id IS NULL";
        
        return $this->db->select($sql);
    }
    
    /**
     * Update interest rate setting
     */
    public function updateSetting($id, $data) {
        $sql = "UPDATE setting_bunga 
                SET bunga_default = ?, bunga_min = ?, bunga_max = ?, 
                    faktor_risiko = ?, jaminan_adjustment = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [
            $data['bunga_default'],
            $data['bunga_min'],
            $data['bunga_max'],
            $data['faktor_risiko'] ?? 0,
            $data['jaminan_adjustment'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Create new interest rate setting
     */
    public function createSetting($data) {
        // Support both frekuensi_id (numeric) and frekuensi enum (string)
        $frekuensi_id = null;
        $frekuensi = $data['frekuensi'] ?? 'bulanan';
        if (is_numeric($frekuensi)) {
            $frekuensi_id = $frekuensi;
            $frekuensi = getFrequencyCode($frekuensi_id);
        }
        
        $sql = "INSERT INTO setting_bunga 
                (cabang_id, jenis_pinjaman, frekuensi_id, tenor_min, tenor_max, bunga_default, bunga_min, bunga_max, faktor_risiko, jaminan_adjustment)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            null, // Single office - always null (global)
            $data['jenis_pinjaman'],
            $frekuensi_id,
            $data['tenor_min'],
            $data['tenor_max'],
            $data['bunga_default'],
            $data['bunga_min'],
            $data['bunga_max'],
            $data['faktor_risiko'] ?? 0,
            $data['jaminan_adjustment'] ?? 0
        ]);
    }
}
