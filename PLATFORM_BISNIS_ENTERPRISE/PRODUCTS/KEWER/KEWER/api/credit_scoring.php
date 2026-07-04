<?php
/**
 * Credit Scoring API
 * Provides credit scoring functionality for nasabah
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../src/CreditScoring/ScoringEngine.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check permission
if (!hasPermission('credit_scoring_view')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

$action = $_GET['action'] ?? 'calculate';

try {
    switch ($action) {
        case 'calculate':
            $nasabah_id = $_POST['nasabah_id'] ?? null;
            if (!$nasabah_id) {
                echo json_encode(['success' => false, 'message' => 'nasabah_id required']);
                exit;
            }
            echo json_encode(\Kewer\CreditScoring\ScoringEngine::calculateScore($nasabah_id));
            break;
            
        case 'auto_approve':
            $nasabah_id = $_POST['nasabah_id'] ?? null;
            $jumlah_pinjaman = $_POST['jumlah_pinjaman'] ?? null;
            if (!$nasabah_id || !$jumlah_pinjaman) {
                echo json_encode(['success' => false, 'message' => 'nasabah_id and jumlah_pinjaman required']);
                exit;
            }
            echo json_encode(\Kewer\CreditScoring\ScoringEngine::shouldAutoApprove($nasabah_id, $jumlah_pinjaman));
            break;
            
        case 'history':
            $nasabah_id = $_GET['nasabah_id'] ?? null;
            if (!$nasabah_id) {
                echo json_encode(['success' => false, 'message' => 'nasabah_id required']);
                exit;
            }
            echo json_encode(getCreditScoringHistory($nasabah_id));
            break;
            
        case 'batch_calculate':
            $cabang_id = $_GET['cabang_id'] ?? $_SESSION['cabang_id'] ?? null;
            echo json_encode(batchCalculateScores($cabang_id));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Get credit scoring history for a nasabah
 */
function getCreditScoringHistory($nasabah_id) {
    $sql = "SELECT * FROM credit_scoring_logs WHERE nasabah_id = ? ORDER BY created_at DESC LIMIT 20";
    $result = query($sql, [$nasabah_id]);
    
    return [
        'success' => true,
        'data' => is_array($result) ? $result : []
    ];
}

/**
 * Batch calculate scores for all nasabah in a cabang
 */
function batchCalculateScores($cabang_id) {
    $cabang_filter = $cabang_id ? "WHERE cabang_id = ?" : "";
    $params = $cabang_id ? [$cabang_id] : [];
    
    // Get all nasabah
    $sql = "SELECT id FROM nasabah $cabang_filter";
    $result = query($sql, $params);
    
    if (!is_array($result)) {
        return ['success' => false, 'message' => 'Failed to fetch nasabah'];
    }
    
    $processed = 0;
    $failed = 0;
    
    foreach ($result as $row) {
        $nasabah_id = $row['id'];
        $scoreResult = \Kewer\CreditScoring\ScoringEngine::calculateScore($nasabah_id);
        
        if ($scoreResult['success']) {
            // Update nasabah table with latest score
            $updateSql = "UPDATE nasabah SET credit_score = ?, risk_level = ?, score_updated_at = NOW() WHERE id = ?";
            query($updateSql, [$scoreResult['data']['total_score'], $scoreResult['data']['risk_level'], $nasabah_id]);
            $processed++;
        } else {
            $failed++;
        }
    }
    
    return [
        'success' => true,
        'data' => [
            'processed' => $processed,
            'failed' => $failed,
            'total' => count($result)
        ]
    ];
}
