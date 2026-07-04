<?php
/**
 * API: Webhooks
 * 
 * Endpoints for managing webhooks
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/src/Webhook/WebhookService.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
}

// Auth check
try {
    requireLogin();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication failed: ' . $e->getMessage()]);
    exit();
}

try {
    $user = getCurrentUser();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Failed to get user: ' . $e->getMessage()]);
    exit();
}

// Permission check
if (!hasPermission('webhook_manage')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - No permission to manage webhooks']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        $webhook_id = $_GET['id'] ?? null;
        
        if ($webhook_id) {
            // Get single webhook
            $webhook = query("SELECT * FROM webhooks WHERE id = ?", [$webhook_id]);
            if (!is_array($webhook) || empty($webhook)) {
                http_response_code(404);
                echo json_encode(['error' => 'Webhook not found']);
                exit();
            }
            echo json_encode(['success' => true, 'data' => $webhook[0]]);
        } else {
            // List webhooks with delivery stats
            $webhooks = \Kewer\Webhook\WebhookService::getWebhooks();
            echo json_encode($webhooks);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'test') {
            // Test webhook
            $webhook_id = $_GET['id'] ?? null;
            if (!$webhook_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Webhook ID is required for test']);
                exit();
            }
            
            $webhook = query("SELECT * FROM webhooks WHERE id = ?", [$webhook_id]);
            if (!is_array($webhook) || empty($webhook)) {
                http_response_code(404);
                echo json_encode(['error' => 'Webhook not found']);
                exit();
            }
            
            $test_payload = [
                'event' => 'test',
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => [
                    'message' => 'Test webhook payload',
                    'triggered_by' => $user['username']
                ]
            ];
            
            $result = \Kewer\Webhook\WebhookService::trigger('test', 'test', 0, $test_payload);
            echo json_encode(['success' => true, 'result' => $result]);
            break;
        }
        
        // Create or update webhook
        $webhook_id = $input['webhook_id'] ?? null;
        $event_type = $input['event_type'] ?? '';
        $target_url = $input['target_url'] ?? '';
        $secret_key = $input['secret_key'] ?? null;
        $retry_count = (int)($input['retry_count'] ?? 3);
        $retry_interval = (int)($input['retry_interval'] ?? 300);
        $is_active = isset($input['is_active']) ? 1 : 0;
        
        if (!$event_type || !$target_url) {
            http_response_code(400);
            echo json_encode(['error' => 'Event type and target URL are required']);
            exit();
        }
        
        if ($webhook_id) {
            // Update existing webhook
            $result = \Kewer\Webhook\WebhookService::updateWebhook(
                $webhook_id,
                $event_type,
                $target_url,
                $secret_key,
                null, // headers
                $is_active
            );
        } else {
            // Create new webhook
            $result = \Kewer\Webhook\WebhookService::createWebhook(
                $event_type,
                $target_url,
                $secret_key,
                null // headers
            );
        }
        
        echo json_encode($result);
        break;
        
    case 'DELETE':
        $webhook_id = $_GET['id'] ?? null;
        
        if (!$webhook_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Webhook ID is required']);
            exit();
        }
        
        $result = \Kewer\Webhook\WebhookService::deleteWebhook($webhook_id);
        echo json_encode($result);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
