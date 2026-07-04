<?php
namespace Kewer\Webhook;

/**
 * Webhook Service
 * Handles webhook delivery and management
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

class WebhookService {
    
    /**
     * Trigger webhook for an event
     */
    public static function trigger($eventType, $resourceType, $resourceId, $payload) {
        // Get active webhooks for this event type
        $webhooks = self::getActiveWebhooks($eventType);
        
        if (empty($webhooks)) {
            return ['success' => true, 'message' => 'No webhooks configured for this event'];
        }
        
        $deliveries = [];
        
        foreach ($webhooks as $webhook) {
            $deliveryId = self::createDelivery($webhook['id'], $eventType, $resourceType, $resourceId, $payload);
            
            $result = self::sendWebhook($webhook, $payload);
            
            self::logDelivery($deliveryId, $result);
            
            if (!$result['success']) {
                // Schedule retry if failed
                self::scheduleRetry($deliveryId, $webhook);
            }
            
            $deliveries[] = [
                'webhook_id' => $webhook['id'],
                'delivery_id' => $deliveryId,
                'success' => $result['success']
            ];
        }
        
        return [
            'success' => true,
            'triggered' => count($deliveries),
            'deliveries' => $deliveries
        ];
    }
    
    /**
     * Get active webhooks for an event type
     */
    private static function getActiveWebhooks($eventType) {
        $sql = "SELECT * FROM webhooks WHERE event_type = ? AND is_active = 1";
        $result = query($sql, [$eventType]);
        
        return is_array($result) ? $result : [];
    }
    
    /**
     * Create webhook delivery record
     */
    private static function createDelivery($webhookId, $eventType, $resourceType, $resourceId, $payload) {
        $sql = "INSERT INTO webhook_deliveries (webhook_id, event_type, resource_type, resource_id, payload, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        query($sql, [$webhookId, $eventType, $resourceType, $resourceId, json_encode($payload)]);
        
        return query("SELECT LAST_INSERT_ID() as id")[0]['id'];
    }
    
    /**
     * Send webhook to target URL
     */
    private static function sendWebhook($webhook, $payload) {
        $url = $webhook['target_url'];
        $secret = $webhook['secret_key'];
        $headers = json_decode($webhook['headers'], true) ?? [];
        
        // Add HMAC signature if secret key exists
        if ($secret) {
            $signature = self::generateSignature($payload, $secret);
            $headers['X-Webhook-Signature'] = $signature;
        }
        
        $startTime = microtime(true);
        
        try {
            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                ...array_map(function($key, $value) {
                    return "$key: $value";
                }, array_keys($headers), array_values($headers))
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);
            
            $durationMs = round((microtime(true) - $startTime) * 1000);
            
            if ($error) {
                return [
                    'success' => false,
                    'error' => $error,
                    'duration_ms' => $durationMs
                ];
            }
            
            // Log the API call
            self::logExternalApi('webhook', $url, 'POST', $payload, $response, $statusCode, $durationMs);
            
            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'status_code' => $statusCode,
                'response' => $response,
                'duration_ms' => $durationMs
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000)
            ];
        }
    }
    
    /**
     * Generate HMAC signature for webhook
     */
    private static function generateSignature($payload, $secret) {
        $payloadString = is_string($payload) ? $payload : json_encode($payload);
        return hash_hmac('sha256', $payloadString, $secret);
    }
    
    /**
     * Log webhook delivery
     */
    private static function logDelivery($deliveryId, $result) {
        $status = $result['success'] ? 'sent' : 'failed';
        
        $sql = "UPDATE webhook_deliveries 
                SET status = ?, sent_at = NOW(), retry_count = retry_count + 1, last_error = ?
                WHERE id = ?";
        
        $errorMsg = $result['success'] ? null : ($result['error'] ?? "HTTP {$result['status_code']}");
        
        query($sql, [$status, $errorMsg, $deliveryId]);
    }
    
    /**
     * Schedule retry for failed webhook
     */
    private static function scheduleRetry($deliveryId, $webhook) {
        $retryCount = $webhook['retry_count'] ?? 3;
        
        // Check if max retries reached
        $sql = "SELECT retry_count FROM webhook_deliveries WHERE id = ?";
        $result = query($sql, [$deliveryId]);
        
        if (is_array($result) && isset($result[0]) && $result[0]['retry_count'] >= $retryCount) {
            return false; // Max retries reached
        }
        
        // In production, this would use a job queue (e.g., Redis, RabbitMQ)
        // For now, just log that retry is needed
        error_log("Webhook delivery $deliveryId needs retry");
        
        return true;
    }
    
    /**
     * Process pending webhook deliveries
     */
    public static function processPendingDeliveries() {
        $sql = "SELECT wd.*, w.target_url, w.secret_key, w.headers 
                FROM webhook_deliveries wd
                JOIN webhooks w ON wd.webhook_id = w.id
                WHERE wd.status = 'pending'
                AND wd.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY wd.created_at ASC
                LIMIT 100";
        
        $result = query($sql, []);
        
        if (!is_array($result)) {
            return ['success' => false, 'message' => 'Failed to fetch pending deliveries'];
        }
        
        $processed = 0;
        
        foreach ($result as $delivery) {
            $payload = json_decode($delivery['payload'], true);
            
            $webhook = [
                'id' => $delivery['webhook_id'],
                'target_url' => $delivery['target_url'],
                'secret_key' => $delivery['secret_key'],
                'headers' => $delivery['headers']
            ];
            
            $sendResult = self::sendWebhook($webhook, $payload);
            self::logDelivery($delivery['id'], $sendResult);
            
            $processed++;
        }
        
        return [
            'success' => true,
            'processed' => $processed
        ];
    }
    
    /**
     * Create new webhook
     */
    public static function createWebhook($eventType, $targetUrl, $secretKey = null, $headers = null) {
        $sql = "INSERT INTO webhooks (event_type, target_url, secret_key, headers) 
                VALUES (?, ?, ?, ?)";
        
        query($sql, [$eventType, $targetUrl, $secretKey, json_encode($headers)]);
        
        return [
            'success' => true,
            'webhook_id' => query("SELECT LAST_INSERT_ID() as id")[0]['id']
        ];
    }
    
    /**
     * Update webhook
     */
    public static function updateWebhook($webhookId, $eventType, $targetUrl, $secretKey = null, $headers = null, $isActive = true) {
        $sql = "UPDATE webhooks 
                SET event_type = ?, target_url = ?, secret_key = ?, headers = ?, is_active = ?
                WHERE id = ?";
        
        query($sql, [$eventType, $targetUrl, $secretKey, json_encode($headers), $isActive ? 1 : 0, $webhookId]);
        
        return ['success' => true];
    }
    
    /**
     * Delete webhook
     */
    public static function deleteWebhook($webhookId) {
        $sql = "DELETE FROM webhooks WHERE id = ?";
        query($sql, [$webhookId]);
        
        return ['success' => true];
    }
    
    /**
     * Get webhooks list
     */
    public static function getWebhooks($eventType = null) {
        $sql = "SELECT w.*, COUNT(wd.id) as total_deliveries, 
                SUM(CASE WHEN wd.status = 'sent' THEN 1 ELSE 0 END) as successful_deliveries
                FROM webhooks w
                LEFT JOIN webhook_deliveries wd ON w.id = wd.webhook_id";
        
        $params = [];
        
        if ($eventType) {
            $sql .= " WHERE w.event_type = ?";
            $params[] = $eventType;
        }
        
        $sql .= " GROUP BY w.id ORDER BY w.created_at DESC";
        
        $result = query($sql, $params);
        
        return [
            'success' => true,
            'data' => is_array($result) ? $result : []
        ];
    }
    
    /**
     * Log external API call
     */
    private static function logExternalApi($apiName, $endpoint, $method, $requestBody, $responseBody, $statusCode, $durationMs) {
        $sql = "INSERT INTO external_api_logs (api_name, endpoint, method, request_body, response_body, status_code, status, duration_ms) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $status = ($statusCode >= 200 && $statusCode < 300) ? 'success' : 'error';
        
        query($sql, [
            $apiName,
            $endpoint,
            $method,
            json_encode($requestBody),
            is_string($responseBody) ? $responseBody : json_encode($responseBody),
            $statusCode,
            $status,
            $durationMs
        ]);
    }
}
