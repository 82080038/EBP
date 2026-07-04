<?php
/**
 * API: Pengeluaran (Expense Tracking)
 * 
 * Endpoints for managing expenses
 */

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/accounting_helper.php';
    require_once BASE_PATH . '/includes/pengeluaran.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Authentication check
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
// No longer using cabangId - single office structure
$kantor_id = 1;
$pengeluaran = new Pengeluaran($kantor_id);

switch ($method) {
    case 'GET':
        // Get expenses with filters
        $filters = [
            'kategori' => $_GET['kategori'] ?? null,
            'status' => $_GET['status'] ?? null,
            'tanggal_mulai' => $_GET['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $_GET['tanggal_selesai'] ?? null
        ];
        
        $expenses = $pengeluaran->getBranchExpenses($filters);
        
        echo json_encode([
            'success' => true,
            'data' => $expenses
        ]);
        break;
        
    case 'POST':
        // Create new expense
        $input = json_decode(file_get_contents('php://input'), true);
        
        $result = $pengeluaran->createExpense([
            'kategori' => $input['kategori'],
            'sub_kategori' => $input['sub_kategori'] ?? null,
            'jumlah' => $input['jumlah'],
            'tanggal' => $input['tanggal'],
            'keterangan' => $input['keterangan'] ?? null,
            'bukti' => $input['bukti'] ?? null,
            'petugas_id' => $input['petugas_id'] ?? null
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Pengeluaran berhasil ditambahkan',
                'id' => $result
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menambahkan pengeluaran']);
        }
        break;
        
    case 'PUT':
        // Update expense or approve/reject
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        $action = $_GET['action'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID pengeluaran diperlukan']);
            exit();
        }
        
        if ($action === 'approve') {
            // Approve expense
            $userId = getCurrentUser();
            $result = $pengeluaran->approveExpense($id, $userId['id']);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pengeluaran berhasil di-approve'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Gagal approve pengeluaran']);
            }
        } elseif ($action === 'reject') {
            // Reject expense
            $userId = getCurrentUser();
            $result = $pengeluaran->rejectExpense($id, $userId['id']);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pengeluaran berhasil di-reject'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Gagal reject pengeluaran']);
            }
        } else {
            // Update expense data
            $result = $pengeluaran->createExpense([
                'kategori_id' => $input['kategori_id'],
                'jumlah' => $input['jumlah'],
                'tanggal' => $input['tanggal'],
                'keterangan' => $input['keterangan'],
                'bukti' => $input['bukti'] ?? null,
                'created_by' => $user['id']
            ]);
            
            if ($result) {
                // Post accounting journal entry
                postJurnalPengeluaran($result, $cabangId);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pengeluaran berhasil ditambahkan'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Gagal mengupdate pengeluaran']);
            }
        }
        break;
        
    case 'DELETE':
        // Delete expense (reject)
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID pengeluaran diperlukan']);
            exit();
        }
        
        $result = $pengeluaran->deleteExpense($id);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Pengeluaran berhasil dihapus'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menghapus pengeluaran']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
