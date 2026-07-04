<?php
/**
 * Kas Bon Management (Employee Cash Advance)
 * 
 * Handles employee cash advance requests and deductions
 * 
 * Features:
 * - Create kas bon requests
 * - Approve/reject kas bon
 * - Track monthly deductions
 * - Calculate remaining balance
 * - Employee limit management
 */

require_once __DIR__ . '/database_class.php';

class KasBon {
    private $db;
    private $cabangId;
    
    public function __construct($cabangId = null) {
        $this->db = db();
        $this->cabangId = $cabangId;
    }
    
    /**
     * Create new kas bon request
     */
    public function createKasBon($data) {
        $sql = "INSERT INTO kas_bon 
                (cabang_id, karyawan_id, kode_kasbon, tanggal_pengajuan, jumlah, tenor_bulan, tujuan, catatan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['cabang_id'] ?? $this->cabangId,
            $data['karyawan_id'],
            $data['kode_kasbon'],
            $data['tanggal_pengajuan'] ?? date('Y-m-d'),
            $data['jumlah'],
            $data['tenor_bulan'] ?? 1,
            $data['tujuan'],
            $data['catatan'] ?? null
        ]);
    }
    
    /**
     * Get kas bon by ID
     */
    public function getById($id) {
        $sql = "SELECT kb.*, u.nama as nama_karyawan, u2.nama as nama_disetujui
                FROM kas_bon kb
                JOIN users u ON kb.karyawan_id = u.id
                LEFT JOIN users u2 ON kb.disetujui_oleh = u2.id
                WHERE kb.id = ?";
        
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * Get all kas bon with filters
     */
    public function getAll($filters = []) {
        $where = ["kb.cabang_id = ?"];
        $params = [$this->cabangId];
        
        if (!empty($filters['karyawan_id'])) {
            $where[] = "kb.karyawan_id = ?";
            $params[] = $filters['karyawan_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "kb.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['tanggal_mulai'])) {
            $where[] = "kb.tanggal_pengajuan >= ?";
            $params[] = $filters['tanggal_mulai'];
        }
        
        if (!empty($filters['tanggal_selesai'])) {
            $where[] = "kb.tanggal_pengajuan <= ?";
            $params[] = $filters['tanggal_selesai'];
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $sql = "SELECT kb.*, u.nama as nama_karyawan, u2.nama as nama_disetujui
                FROM kas_bon kb
                JOIN users u ON kb.karyawan_id = u.id
                LEFT JOIN users u2 ON kb.disetujui_oleh = u2.id
                $where_clause
                ORDER BY kb.tanggal_pengajuan DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Approve kas bon request
     */
    public function approveKasBon($id, $userId) {
        $sql = "UPDATE kas_bon 
                SET status = 'disetujui',
                    disetujui_oleh = ?,
                    tanggal_disetujui = NOW(),
                    updated_at = NOW()
                WHERE id = ? AND status = 'pengajuan'";
        
        $result = $this->db->update($sql, [$userId, $id]);
        
        if ($result) {
            // Calculate potongan per bulan
            $kasbon = $this->getById($id);
            $potonganPerBulan = $kasbon['jumlah'] / $kasbon['tenor_bulan'];
            
            // Update potongan_per_bulan
            $this->db->update(
                "UPDATE kas_bon SET potongan_per_bulan = ? WHERE id = ?",
                [$potonganPerBulan, $id]
            );
        }
        
        return $result;
    }
    
    /**
     * Give kas bon to employee
     */
    public function giveKasBon($id) {
        $sql = "UPDATE kas_bon 
                SET status = 'diberikan',
                    tanggal_pemberian = CURDATE(),
                    updated_at = NOW()
                WHERE id = ? AND status = 'disetujui'";
        
        return $this->db->update($sql, [$id]);
    }
    
    /**
     * Reject kas bon request
     */
    public function rejectKasBon($id, $userId, $alasan = null) {
        $sql = "UPDATE kas_bon 
                SET status = 'ditolak',
                    disetujui_oleh = ?,
                    tanggal_disetujui = NOW(),
                    catatan = CONCAT(IFNULL(catatan, ''), IF(catatan IS NULL, '', '; '), 'Ditolak: ', ?),
                    updated_at = NOW()
                WHERE id = ? AND status = 'pengajuan'";
        
        return $this->db->update($sql, [$userId, $alasan ?? 'Ditolak', $id]);
    }
    
    /**
     * Record monthly deduction
     */
    public function recordPotongan($kasbonId, $bulanPotong, $jumlahPotong, $potongOleh, $catatan = null) {
        $sql = "INSERT INTO kas_bon_potongan 
                (kas_bon_id, bulan_potong, jumlah_potong, tanggal_potong, potong_oleh, catatan)
                VALUES (?, ?, ?, CURDATE(), ?, ?)";
        
        return $this->db->insert($sql, [
            $kasbonId,
            $bulanPotong,
            $jumlahPotong,
            $potongOleh,
            $cataton ?? null
        ]);
    }
    
    /**
     * Get potongan history for kas bon
     */
    public function getPotonganHistory($kasbonId) {
        $sql = "SELECT kbp.*, u.nama as nama_petugas
                FROM kas_bon_potongan kbp
                LEFT JOIN users u ON kbp.potong_oleh = u.id
                WHERE kbp.kas_bon_id = ?
                ORDER BY kbp.bulan_potong ASC";
        
        return $this->db->select($sql, [$kasbonId]);
    }
    
    /**
     * Update kas bon status after deduction
     */
    public function updateAfterPotongan($kasbonId) {
        $kasbon = $this->getById($kasbonId);
        $potonganHistory = $this->getPotonganHistory($kasbonId);
        
        $totalDipotong = 0;
        foreach ($potonganHistory as $potongan) {
            $totalDipotong += $potongan['jumlah_potong'];
        }
        
        $sisaBon = $kasbon['jumlah'] - $totalDipotong;
        $potonganKe = count($potonganHistory);
        
        $sql = "UPDATE kas_bon 
                SET sisa_bon = ?,
                    potongan_ke = ?,
                    status = CASE 
                        WHEN ? >= ? THEN 'selesai'
                        ELSE 'dipotong'
                    END,
                    updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [
            $sisaBon,
            $potonganKe,
            $totalDipotong,
            $kasbon['jumlah'],
            $kasbonId
        ]);
    }
    
    /**
     * Get employee kas bon balance
     */
    public function getEmployeeBalance($karyawanId) {
        $sql = "SELECT 
                    COUNT(*) as total_kasbon,
                    SUM(CASE WHEN status IN ('disetujui', 'diberikan', 'dipotong', 'selesai') THEN jumlah ELSE 0 END) as total_dipinjam,
                    SUM(CASE WHEN status IN ('dipotong', 'selesai') THEN sisa_bon ELSE 0 END) as total_sisa,
                    SUM(CASE WHEN status = 'selesai' THEN jumlah ELSE 0 END) as total_lunas
                FROM kas_bon
                WHERE karyawan_id = ? AND status != 'ditolak'";
        
        return $this->db->selectOne($sql, [$karyawanId]);
    }
    
    /**
     * Check if employee can request kas bon
     */
    public function canRequestKasBon($karyawanId, $jumlah) {
        $balance = $this->getEmployeeBalance($karyawanId);
        $limit = $this->getEmployeeLimit($karyawanId);
        
        // Check if total existing + new request exceeds limit
        $totalDipinjam = $balance['total_dipinjam'] ?? 0;
        $sisaLimit = $limit - $totalDipinjam;
        
        return [
            'can_request' => ($jumlah <= $sisaLimit),
            'limit' => $limit,
            'total_dipinjam' => $totalDipinjam,
            'sisa_limit' => $sisaLimit
        ];
    }
    
    /**
     * Get employee kas bon limit
     */
    public function getEmployeeLimit($karyawanId) {
        $sql = "SELECT limit_kasbon FROM users WHERE id = ?";
        $result = $this->db->selectOne($sql, [$karyawanId]);
        return $result ? $result['limit_kasbon'] : 0;
    }
    
    /**
     * Get pending kas bon requests
     */
    public function getPendingRequests() {
        $sql = "SELECT kb.*, u.nama as nama_karyawan
                FROM kas_bon kb
                JOIN users u ON kb.karyawan_id = u.id
                WHERE kb.status = 'pengajuan' AND kb.cabang_id = ?
                ORDER BY kb.tanggal_pengajuan ASC";
        
        return $this->db->select($sql, [$this->cabangId]);
    }
    
    /**
     * Get kas bon statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_kasbon,
                    SUM(CASE WHEN status = 'pengajuan' THEN 1 ELSE 0 END) as pengajuan,
                    SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                    SUM(CASE WHEN status = 'diberikan' THEN 1 ELSE 0 END) as diberikan,
                    SUM(CASE WHEN status = 'dipotong' THEN 1 ELSE 0 END) as dipotong,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
                    SUM(jumlah) as total_jumlah,
                    SUM(sisa_bon) as total_sisa
                FROM kas_bon
                WHERE cabang_id = ?";
        
        return $this->db->selectOne($sql, [$this->cabangId]);
    }
    
    /**
     * Get monthly deduction report
     */
    public function getMonthlyReport($bulan, $tahun) {
        $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);
        
        $sql = "SELECT kb.*, u.nama as nama_karyawan, kbp.jumlah_potong, kbp.tanggal_potong
                FROM kas_bon_potongan kbp
                JOIN kas_bon kb ON kbp.kas_bon_id = kb.id
                JOIN users u ON kb.karyawan_id = u.id
                WHERE kbp.bulan_potong = ? AND kb.cabang_id = ?
                ORDER BY kbp.tanggal_potong ASC";
        
        return $this->db->select($sql, [$periode, $this->cabangId]);
    }
}
?>
