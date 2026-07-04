<?php
/**
 * Angsuran Model
 * 
 * Handles installment-related database operations
 */

require_once __DIR__ . '/../includes/database_class.php';

class Angsuran {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get installment by ID
     */
    public function getById($id) {
        $sql = "SELECT a.*, p.kode_pinjaman, n.nama as nama_nasabah, u.nama as nama_petugas
                FROM angsuran a
                JOIN pinjaman p ON a.pinjaman_id = p.id
                JOIN nasabah n ON p.nasabah_id = n.id
                LEFT JOIN users u ON a.petugas_id = u.id
                WHERE a.id = ?";
        
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * Get all installments with filters
     */
    public function getAll($filters = []) {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['pinjaman_id'])) {
            $where[] = "a.pinjaman_id = ?";
            $params[] = $filters['pinjaman_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['petugas_id'])) {
            $where[] = "a.petugas_id = ?";
            $params[] = $filters['petugas_id'];
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $sql = "SELECT a.*, p.kode_pinjaman, n.nama as nama_nasabah, u.nama as nama_petugas
                FROM angsuran a
                JOIN pinjaman p ON a.pinjaman_id = p.id
                JOIN nasabah n ON p.nasabah_id = n.id
                LEFT JOIN users u ON a.petugas_id = u.id
                $where_clause
                ORDER BY a.jatuh_tempo ASC, a.no_angsuran ASC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get installments by loan ID
     */
    public function getByPinjamanId($pinjamanId) {
        $sql = "SELECT * FROM angsuran WHERE pinjaman_id = ? ORDER BY no_angsuran ASC";
        return $this->db->select($sql, [$pinjamanId]);
    }
    
    /**
     * Create new installment
     */
    public function create($data) {
        $sql = "INSERT INTO angsuran 
                (pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['pinjaman_id'],
            $data['no_angsuran'],
            $data['jatuh_tempo'],
            $data['pokok'],
            $data['bunga'],
            $data['total_angsuran']
        ]);
    }
    
    /**
     * Update installment
     */
    public function update($id, $data) {
        $sql = "UPDATE angsuran 
                SET status = ?, tanggal_bayar = ?, total_bayar = ?, petugas_id = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [
            $data['status'],
            $data['tanggal_bayar'],
            $data['total_bayar'],
            $data['petugas_id'],
            $id
        ]);
    }
    
    /**
     * Mark installment as paid
     */
    public function markAsPaid($id, $tanggalBayar, $totalBayar, $petugasId) {
        $sql = "UPDATE angsuran 
                SET status = 'lunas', tanggal_bayar = ?, total_bayar = ?, petugas_id = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [$tanggalBayar, $totalBayar, $petugasId, $id]);
    }
    
    /**
     * Delete installment
     */
    public function delete($id) {
        $sql = "DELETE FROM angsuran WHERE id = ?";
        return $this->db->delete($sql, [$id]);
    }
    
    /**
     * Get overdue installments
     */
    public function getOverdue() {
        $where = ["status = 'belum'", "jatuh_tempo < CURDATE()"];
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $sql = "SELECT a.*, p.kode_pinjaman, n.nama as nama_nasabah, n.telp, n.alamat,
                DATEDIFF(CURDATE(), a.jatuh_tempo) as hari_telat
                FROM angsuran a
                JOIN pinjaman p ON a.pinjaman_id = p.id
                JOIN nasabah n ON p.nasabah_id = n.id
                $where_clause
                ORDER BY a.jatuh_tempo ASC";
        
        return $this->db->select($sql);
    }
    
    /**
     * Get installment statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_angsuran,
                    SUM(CASE WHEN status = 'belum' THEN 1 ELSE 0 END) as belum,
                    SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
                    SUM(CASE WHEN status = 'telat' THEN 1 ELSE 0 END) as telat,
                    SUM(total_angsuran) as total_tagihan,
                    SUM(total_bayar) as total_dibayar
                FROM angsuran";
        
        return $this->db->selectOne($sql);
    }
    
    /**
     * Check and update late payments
     */
    public function updateLatePayments() {
        $where = ["status = 'belum'", "jatuh_tempo < CURDATE()"];
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $sql = "UPDATE angsuran SET status = 'telat', updated_at = NOW() $where_clause";
        return $this->db->update($sql);
    }
}
?>
