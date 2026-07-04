<?php
/**
 * Expense Tracking Module
 * 
 * Handles comprehensive expense tracking including:
 * - Salary (gaji)
 * - Overtime (lembur)
 * - Bonus
 * - Operational costs (bensin, transport, listrik)
 * - Routine expenses (belanja rutin, makan, dll)
 * - Approval workflow
 */

require_once __DIR__ . '/database_class.php';

class Pengeluaran {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Create new expense record
     */
    public function createExpense($data) {
        // Check if approval is required based on amount
        $thresholdSql = "SELECT setting_value FROM settings WHERE setting_key = 'require_approval_pengeluaran'";
        $threshold = $this->db->selectOne($thresholdSql);
        $approvalThreshold = $threshold ? (float)$threshold['setting_value'] : 500000;
        
        $status = ($data['jumlah'] >= $approvalThreshold) ? 'pending' : 'approved';
        
        $sql = "INSERT INTO pengeluaran 
                (kategori, sub_kategori, jumlah, tanggal, keterangan, bukti, petugas_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['kategori'],
            $data['sub_kategori'] ?? null,
            $data['jumlah'],
            $data['tanggal'],
            $data['keterangan'] ?? null,
            $data['bukti'] ?? null,
            $data['petugas_id'] ?? null,
            $status
        ]);
    }
    
    /**
     * Update expense record
     */
    public function updateExpense($id, $data) {
        $sql = "UPDATE pengeluaran 
                SET kategori = ?, sub_kategori = ?, jumlah = ?, tanggal = ?, 
                    keterangan = ?, bukti = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [
            $data['kategori'],
            $data['sub_kategori'] ?? null,
            $data['jumlah'],
            $data['tanggal'],
            $data['keterangan'] ?? null,
            $data['bukti'] ?? null,
            $id
        ]);
    }
    
    /**
     * Approve expense
     */
    public function approveExpense($id, $approverId) {
        $sql = "UPDATE pengeluaran 
                SET status = 'approved', approved_by = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [$approverId, $id]);
    }
    
    /**
     * Reject expense
     */
    public function rejectExpense($id, $approverId) {
        $sql = "UPDATE pengeluaran 
                SET status = 'rejected', approved_by = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [$approverId, $id]);
    }
    
    /**
     * Get expense by ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, c.nama_cabang, u1.nama as nama_petugas, u2.nama as nama_approver
                FROM pengeluaran p
                LEFT JOIN cabang c ON 1=1
                LEFT JOIN users u1 ON p.petugas_id = u1.id
                LEFT JOIN users u2 ON p.approved_by = u2.id
                WHERE p.id = ?";
        
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * Get all expenses for single office
     */
    public function getBranchExpenses($filters = []) {
        $sql = "SELECT p.*, c.nama_cabang, u1.nama as nama_petugas, u2.nama as nama_approver
                FROM pengeluaran p
                LEFT JOIN cabang c ON 1=1
                LEFT JOIN users u1 ON p.petugas_id = u1.id
                LEFT JOIN users u2 ON p.approved_by = u2.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['kategori'])) {
            $sql .= " AND p.kategori = ?";
            $params[] = $filters['kategori'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['tanggal_mulai'])) {
            $sql .= " AND p.tanggal >= ?";
            $params[] = $filters['tanggal_mulai'];
        }
        
        if (!empty($filters['tanggal_selesai'])) {
            $sql .= " AND p.tanggal <= ?";
            $params[] = $filters['tanggal_selesai'];
        }
        
        $sql .= " ORDER BY p.tanggal DESC, p.created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get pending expenses (awaiting approval)
     */
    public function getPendingExpenses() {
        $sql = "SELECT p.*, c.nama_cabang, u1.nama as nama_petugas
                FROM pengeluaran p
                LEFT JOIN cabang c ON 1=1
                LEFT JOIN users u1 ON p.petugas_id = u1.id
                WHERE p.status = 'pending'
                ORDER BY p.tanggal ASC, p.created_at ASC";
        
        return $this->db->select($sql);
    }
    
    /**
     * Get expense report by category
     */
    public function getReportByCategory($tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT 
                    kategori,
                    COUNT(*) as jumlah_transaksi,
                    SUM(jumlah) as total_pengeluaran
                FROM pengeluaran
                WHERE status = 'approved'
                AND tanggal BETWEEN ? AND ?
                GROUP BY kategori
                ORDER BY total_pengeluaran DESC";
        
        return $this->db->select($sql, [$tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Get expense report by sub-category
     */
    public function getReportBySubCategory($kategori, $tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT 
                    sub_kategori,
                    COUNT(*) as jumlah_transaksi,
                    SUM(jumlah) as total_pengeluaran
                FROM pengeluaran
                WHERE kategori = ?
                AND status = 'approved'
                AND tanggal BETWEEN ? AND ?
                GROUP BY sub_kategori
                ORDER BY total_pengeluaran DESC";
        
        return $this->db->select($sql, [$kategori, $tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Get total expenses for period
     */
    public function getTotalExpenses($tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT 
                    SUM(jumlah) as total_pengeluaran,
                    COUNT(*) as jumlah_transaksi
                FROM pengeluaran
                WHERE status = 'approved'
                AND tanggal BETWEEN ? AND ?";
        
        return $this->db->selectOne($sql, [$tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Get salary expenses
     */
    public function getSalaryExpenses($bulan, $tahun) {
        $sql = "SELECT * FROM pengeluaran
                WHERE kategori = 'gaji'
                AND status = 'approved'
                AND MONTH(tanggal) = ?
                AND YEAR(tanggal) = ?
                ORDER BY tanggal DESC";
        
        return $this->db->select($sql, [$bulan, $tahun]);
    }
    
    /**
     * Get overtime expenses
     */
    public function getOvertimeExpenses($bulan, $tahun) {
        $sql = "SELECT * FROM pengeluaran
                WHERE kategori = 'lembur'
                AND status = 'approved'
                AND MONTH(tanggal) = ?
                AND YEAR(tanggal) = ?
                ORDER BY tanggal DESC";
        
        return $this->db->select($sql, [$bulan, $tahun]);
    }
    
    /**
     * Get operational expenses
     */
    public function getOperationalExpenses($tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT * FROM pengeluaran
                WHERE kategori = 'operasional'
                AND status = 'approved'
                AND tanggal BETWEEN ? AND ?
                ORDER BY tanggal DESC";
        
        return $this->db->select($sql, [$tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Get routine expenses (belanja, makan, dll)
     */
    public function getRoutineExpenses($tanggalMulai, $tanggalSelesai) {
        $sql = "SELECT * FROM pengeluaran
                WHERE kategori = 'belanja'
                AND status = 'approved'
                AND tanggal BETWEEN ? AND ?
                ORDER BY tanggal DESC";
        
        return $this->db->select($sql, [$tanggalMulai, $tanggalSelesai]);
    }
    
    /**
     * Delete expense (soft delete - just mark as rejected)
     */
    public function deleteExpense($id) {
        return $this->rejectExpense($id, null);
    }
}
?>
