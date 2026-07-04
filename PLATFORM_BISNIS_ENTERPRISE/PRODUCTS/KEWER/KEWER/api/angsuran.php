<?php
/**
 * API: Angsuran (Installments)
 * 
 * Endpoints for managing loan installments
 */

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get installments with filters
        $pinjaman_id = $_GET['pinjaman_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
        $tanggal_selesai = $_GET['tanggal_selesai'] ?? null;
        
        $where = [];
        $params = [];
        
        if ($pinjaman_id) {
            $where[] = "a.pinjaman_id = ?";
            $params[] = $pinjaman_id;
        }
        
        if ($status) {
            $where[] = "a.status = ?";
            $params[] = $status;
        }
        
        if ($tanggal_mulai) {
            $where[] = "a.jatuh_tempo >= ?";
            $params[] = $tanggal_mulai;
        }
        
        if ($tanggal_selesai) {
            $where[] = "a.jatuh_tempo <= ?";
            $params[] = $tanggal_selesai;
        }
        
        // Isolasi data: hanya tampilkan angsuran dari cabang milik bos yang login
        $cabang_filter = buildCabangFilter('p.cabang_id');
        if ($cabang_filter) {
            $where[] = ltrim($cabang_filter['clause'], 'AND ');
            $params  = array_merge($params, $cabang_filter['params']);
        }

        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT a.*, n.nama as nama_nasabah, p.kode_pinjaman, u.nama as nama_petugas
                FROM angsuran a
                JOIN pinjaman p ON a.pinjaman_id = p.id
                JOIN nasabah n ON p.nasabah_id = n.id
                LEFT JOIN users u ON a.petugas_id = u.id
                $where_clause
                ORDER BY a.jatuh_tempo ASC, a.no_angsuran ASC";
        
        $angsuran = query($sql, $params);
        if (!is_array($angsuran)) $angsuran = [];
        
        echo json_encode([
            'success' => true,
            'data' => $angsuran
        ]);
        break;
        
    case 'POST':
        // Create new installment record (usually auto-generated)
        $input = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO angsuran (pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $result = query($sql, [
            $input['pinjaman_id'],
            $input['no_angsuran'],
            $input['jatuh_tempo'],
            $input['pokok'],
            $input['bunga'],
            $input['total_angsuran']
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Angsuran berhasil ditambahkan',
                'id' => $result
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menambahkan angsuran']);
        }
        break;
        
    case 'PUT':
        // Update installment (mark as paid)
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID angsuran diperlukan']);
            exit();
        }
        
        $sql = "UPDATE angsuran 
                SET status = ?, tanggal_bayar = ?, total_bayar = ?, petugas_id = ?, updated_at = NOW()
                WHERE id = ?";
        
        $result = query($sql, [
            $input['status'],
            $input['tanggal_bayar'],
            $input['total_bayar'],
            $input['petugas_id'],
            $id
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Angsuran berhasil diupdate'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal mengupdate angsuran']);
        }
        break;
        
    case 'DELETE':
        // Delete angsuran (soft delete)
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID angsuran diperlukan']);
            exit();
        }
        
        // Permission check
        if (!hasPermission('manage_pembayaran')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden - No permission to manage angsuran']);
            exit();
        }
        
        // Get existing angsuran
        $existing = query("SELECT * FROM angsuran WHERE id = ?", [$id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Angsuran not found']);
            exit();
        }
        $existing = $existing[0];
        
        // Check if angsuran has payments
        $pembayaran_count = query("SELECT COUNT(*) as count FROM pembayaran WHERE angsuran_id = ?", [$id])[0]['count'];
        
        if ($pembayaran_count > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete angsuran with existing payments']);
            exit();
        }
        
        // Soft delete - set status to deleted
        $result = query("UPDATE angsuran SET status = 'deleted', updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Angsuran deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete angsuran']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
