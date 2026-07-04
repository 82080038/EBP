<?php
/**
 * Cash Tracking for Field Officers
 * 
 * Handles tracking of cash held by field officers:
 * - Saldo awal (morning cash from branch)
 * - Total dana dikutip (collections from customers)
 * - Total dana disetor (deposits to branch)
 * - Saldo akhir (remaining cash with officer)
 * - Alert system for discrepancies
 */

require_once __DIR__ . '/database_class.php';

class KasPetugas {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Create daily cash record for officer
     */
    public function createDailyRecord($data) {
        $sql = "INSERT INTO kas_petugas 
                (petugas_id, tanggal, saldo_awal, total_terima, total_disetor, catatan)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $result = $this->db->insert($sql, [
            $data['petugas_id'],
            $data['tanggal'],
            $data['saldo_awal'] ?? 0,
            $data['total_terima'] ?? 0,
            $data['total_disetor'] ?? 0,
            $data['catatan'] ?? null
        ]);
        
        return $result;
    }
    
    /**
     * Update cash record (add collections or deposits)
     */
    public function updateRecord($id, $data) {
        $sql = "UPDATE kas_petugas 
                SET total_terima = ?, total_disetor = ?, catatan = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [
            $data['total_terima'],
            $data['total_disetor'],
            $data['catatan'] ?? null,
            $id
        ]);
    }
    
    /**
     * Add collection amount to officer's daily record
     */
    public function addCollection($petugasId, $tanggal, $jumlah) {
        // Get existing record
        $sql = "SELECT * FROM kas_petugas 
                WHERE petugas_id = ? AND tanggal = ?";
        
        $record = $this->db->selectOne($sql, [$petugasId, $tanggal]);
        
        if ($record) {
            // Update existing record
            $newTotalTerima = $record['total_terima'] + $jumlah;
            return $this->updateRecord($record['id'], [
                'total_terima' => $newTotalTerima,
                'total_disetor' => $record['total_disetor'],
                'catatan' => $record['catatan']
            ]);
        } else {
            // Create new record
            return $this->createDailyRecord([
                'petugas_id' => $petugasId,
                'tanggal' => $tanggal,
                'saldo_awal' => 0,
                'total_terima' => $jumlah,
                'total_disetor' => 0
            ]);
        }
    }
    
    /**
     * Add deposit amount to officer's daily record
     */
    public function addDeposit($petugasId, $tanggal, $jumlah) {
        // Get existing record
        $sql = "SELECT * FROM kas_petugas 
                WHERE petugas_id = ? AND tanggal = ?";
        
        $record = $this->db->selectOne($sql, [$petugasId, $tanggal]);
        
        if ($record) {
            // Update existing record
            $newTotalDisetor = $record['total_disetor'] + $jumlah;
            return $this->updateRecord($record['id'], [
                'total_terima' => $record['total_terima'],
                'total_disetor' => $newTotalDisetor,
                'catatan' => $record['catatan']
            ]);
        } else {
            return false; // Cannot deposit without first having a record
        }
    }
    
    /**
     * Get officer's cash record for specific date
     */
    public function getDailyRecord($petugasId, $tanggal) {
        $sql = "SELECT * FROM kas_petugas 
                WHERE petugas_id = ? AND tanggal = ?";
        
        return $this->db->selectOne($sql, [$petugasId, $tanggal]);
    }
    
    /**
     * Get all cash records for single office
     */
    public function getBranchRecords($tanggal = null) {
        $sql = "SELECT kp.*, u.nama as nama_petugas 
                FROM kas_petugas kp
                JOIN users u ON kp.petugas_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if ($tanggal) {
            $sql .= " AND kp.tanggal = ?";
            $params[] = $tanggal;
        }
        
        $sql .= " ORDER BY kp.tanggal DESC, kp.created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get officers with cash discrepancies
     */
    public function getDiscrepancies($tanggal = null) {
        $sql = "SELECT kp.*, u.nama as nama_petugas 
                FROM kas_petugas kp
                JOIN users u ON kp.petugas_id = u.id
                WHERE kp.cabang_id = ? 
                AND kp.status != 'lengkap'";
        
        $params = [$this->cabangId];
        
        if ($tanggal) {
            $sql .= " AND kp.tanggal = ?";
            $params[] = $tanggal;
        }
        
        $sql .= " ORDER BY kp.tanggal DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Check if officer has cash discrepancy and alert
     */
    public function checkAlert($petugasId, $tanggal) {
        $record = $this->getDailyRecord($petugasId, $tanggal);
        
        if (!$record) {
            return null;
        }
        
        // Get alert threshold from settings
        $thresholdSql = "SELECT setting_value FROM settings WHERE setting_key = 'alert_kas_petugas_selisih'";
        $threshold = $this->db->selectOne($thresholdSql);
        $alertThreshold = $threshold ? (float)$threshold['setting_value'] : 100000;
        
        $selisih = abs($record['saldo_akhir']);
        
        if ($record['status'] != 'lengkap' && $selisih > $alertThreshold) {
            return [
                'alert' => true,
                'message' => "Petugas {$record['nama_petugas']} memiliki selisih kas Rp " . number_format($selisih, 0, ',', '.'),
                'selisih' => $selisih,
                'status' => $record['status'],
                'saldo_akhir' => $record['saldo_akhir']
            ];
        }
        
        return ['alert' => false];
    }
    
    /**
     * Get daily cash summary for branch
     */
    public function getDailySummary($tanggal) {
        $sql = "SELECT 
                    COUNT(*) as jumlah_petugas,
                    SUM(saldo_awal) as total_saldo_awal,
                    SUM(total_terima) as total_terima,
                    SUM(total_disetor) as total_disetor,
                    SUM(saldo_akhir) as total_saldo_akhir
                FROM kas_petugas
                WHERE cabang_id = ? AND tanggal = ?";
        
        return $this->db->selectOne($sql, [$this->cabangId, $tanggal]);
    }
    
    /**
     * Initialize morning cash for officer
     */
    public function initializeMorningCash($petugasId, $tanggal, $saldoAwal) {
        // Check if record already exists
        $existing = $this->getDailyRecord($petugasId, $tanggal);
        
        if ($existing) {
            return false; // Already initialized
        }
        
        return $this->createDailyRecord([
            'petugas_id' => $petugasId,
            'tanggal' => $tanggal,
            'saldo_awal' => $saldoAwal,
            'total_terima' => 0,
            'total_disetor' => 0,
            'catatan' => 'Inisialisasi kas pagi'
        ]);
    }
    
    /**
     * Finalize end-of-day cash
     */
    public function finalizeDay($petugasId, $tanggal, $catatan = null) {
        $record = $this->getDailyRecord($petugasId, $tanggal);
        
        if (!$record) {
            return false;
        }
        
        return $this->updateRecord($record['id'], [
            'total_terima' => $record['total_terima'],
            'total_disetor' => $record['total_disetor'],
            'catatan' => $catatan ?? $record['catatan']
        ]);
    }
}
?>
