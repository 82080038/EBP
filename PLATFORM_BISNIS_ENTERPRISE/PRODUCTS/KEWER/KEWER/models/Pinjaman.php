<?php
/**
 * Pinjaman Model
 * 
 * Handles loan-related database operations
 */

require_once __DIR__ . '/../includes/database_class.php';

class Pinjaman {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get loan by ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, n.nama as nama_nasabah, n.ktp, n.telp, c.nama_cabang, u.nama as nama_petugas
                FROM pinjaman p
                JOIN nasabah n ON p.nasabah_id = n.id
                LEFT JOIN cabang c ON 1=1
                LEFT JOIN users u ON p.petugas_id = u.id
                WHERE p.id = ?";
        
        return $this->db->selectOne($sql, [$id]);
    }
    
    /**
     * Get all loans with filters
     */
    public function getAll($filters = []) {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['nasabah_id'])) {
            $where[] = "p.nasabah_id = ?";
            $params[] = $filters['nasabah_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['petugas_id'])) {
            $where[] = "p.petugas_id = ?";
            $params[] = $filters['petugas_id'];
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $sql = "SELECT p.*, n.nama as nama_nasabah, n.ktp, c.nama_cabang, u.nama as nama_petugas
                FROM pinjaman p
                JOIN nasabah n ON p.nasabah_id = n.id
                LEFT JOIN cabang c ON 1=1
                LEFT JOIN users u ON p.petugas_id = u.id
                $where_clause
                ORDER BY p.created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Create new loan
     */
    public function create($data) {
        $sql = "INSERT INTO pinjaman 
                (kode_pinjaman, nasabah_id, plafon, tenor, frekuensi_id, bunga_per_bulan, 
                 total_bunga, total_pembayaran, angsuran_pokok, angsuran_bunga, angsuran_total,
                 tanggal_akad, tanggal_jatuh_tempo, tujuan_pinjaman, jaminan, 
                 jaminan_tipe, jaminan_nilai, jaminan_dokumen, status, petugas_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['kode_pinjaman'],
            $data['nasabah_id'],
            $data['plafon'],
            $data['tenor'],
            $data['frekuensi_id'] ?? null,
            $data['bunga_per_bulan'],
            $data['total_bunga'],
            $data['total_pembayaran'],
            $data['angsuran_pokok'],
            $data['angsuran_bunga'],
            $data['angsuran_total'],
            $data['tanggal_akad'],
            $data['tanggal_jatuh_tempo'],
            $data['tujuan_pinjaman'],
            $data['jaminan'],
            $data['jaminan_tipe'] ?? 'tanpa',
            $data['jaminan_nilai'] ?? null,
            $data['jaminan_dokumen'] ?? null,
            $data['status'] ?? 'pengajuan',
            $data['petugas_id']
        ]);
    }
    
    /**
     * Update loan
     */
    public function update($id, $data) {
        $sql = "UPDATE pinjaman 
                SET plafon = ?, tenor = ?, frekuensi_id = ?, frekuensi = ?, bunga_per_bulan = ?, 
                    total_bunga = ?, total_pembayaran = ?, 
                    angsuran_pokok = ?, angsuran_bunga = ?, angsuran_total = ?,
                    tanggal_akad = ?, tanggal_jatuh_tempo = ?, tujuan_pinjaman = ?, 
                    jaminan = ?, jaminan_tipe = ?, jaminan_nilai = ?, jaminan_dokumen = ?,
                    status = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->update($sql, [
            $data['plafon'],
            $data['tenor'],
            $data['frekuensi_id'] ?? null,
            $data['frekuensi'] ?? 'bulanan',
            $data['bunga_per_bulan'],
            $data['total_bunga'],
            $data['total_pembayaran'],
            $data['angsuran_pokok'],
            $data['angsuran_bunga'],
            $data['angsuran_total'],
            $data['tanggal_akad'],
            $data['tanggal_jatuh_tempo'],
            $data['tujuan_pinjaman'],
            $data['jaminan'],
            $data['jaminan_tipe'] ?? 'tanpa',
            $data['jaminan_nilai'] ?? null,
            $data['jaminan_dokumen'] ?? null,
            $data['status'],
            $id
        ]);
    }
    
    /**
     * Update loan status
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE pinjaman SET status = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->update($sql, [$status, $id]);
    }
    
    /**
     * Delete loan
     */
    public function delete($id) {
        $sql = "DELETE FROM pinjaman WHERE id = ?";
        return $this->db->delete($sql, [$id]);
    }
    
    /**
     * Count loans by status
     */
    public function countByStatus($cabangId = null) {
        $where = $cabangId ? "WHERE cabang_id = ?" : "";
        $params = $cabangId ? [$cabangId] : [];
        
        $sql = "SELECT 
                    status,
                    COUNT(*) as total
                FROM pinjaman
                $where
                GROUP BY status";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get active loans
     */
    public function getActiveLoans($cabangId = null) {
        $where = ["status = 'aktif'"];
        $params = [];
        
        if ($cabangId) {
            $where[] = "cabang_id = ?";
            $params[] = $cabangId;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $sql = "SELECT p.*, n.nama as nama_nasabah, n.telp
                FROM pinjaman p
                JOIN nasabah n ON p.nasabah_id = n.id
                $where_clause
                ORDER BY p.tanggal_jatuh_tempo ASC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get overdue loans
     */
    public function getOverdueLoans($cabangId = null) {
        $where = ["status = 'aktif'", "tanggal_jatuh_tempo < CURDATE()"];
        $params = [];
        
        if ($cabangId) {
            $where[] = "p.cabang_id = ?";
            $params[] = $cabangId;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where);
        
        $sql = "SELECT p.*, n.nama as nama_nasabah, n.telp, n.alamat,
                DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) as hari_telat
                FROM pinjaman p
                JOIN nasabah n ON p.nasabah_id = n.id
                $where_clause
                ORDER BY p.tanggal_jatuh_tempo ASC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Get loan statistics
     */
    public function getStatistics($cabangId = null) {
        $where = $cabangId ? "WHERE cabang_id = ?" : "";
        $params = $cabangId ? [$cabangId] : [];
        
        $sql = "SELECT 
                    COUNT(*) as total_pinjaman,
                    SUM(CASE WHEN status = 'pengajuan' THEN 1 ELSE 0 END) as pengajuan,
                    SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                    SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
                    SUM(CASE WHEN status = 'lunas' THEN 1 ELSE 0 END) as lunas,
                    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
                    SUM(CASE WHEN status = 'macet' THEN 1 ELSE 0 END) as macet,
                    SUM(plafon) as total_plafon,
                    SUM(total_pembayaran) as total_tagihan
                FROM pinjaman
                $where";
        
        return $this->db->selectOne($sql, $params);
    }
}
?>
