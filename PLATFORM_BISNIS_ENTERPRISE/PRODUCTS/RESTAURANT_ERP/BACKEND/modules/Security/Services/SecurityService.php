<?php

namespace App\Modules\Security\Services;

use App\Modules\Security\Models\SecurityAuditLog;
use App\Modules\Security\Models\SecurityIncident;
use App\Modules\Security\Models\EncryptionKey;
use App\Modules\Security\Models\SecurityPolicy;
use App\Core\Database;

class SecurityService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get security dashboard
     */
    public function getDashboard($restaurantId)
    {
        $auditLogModel = new SecurityAuditLog();
        $incidentModel = new SecurityIncident();
        $failedLoginModel = new SecurityPolicy();
        
        // Get summary stats
        $stats = [
            'total_audit_logs' => $auditLogModel->countByRestaurant($restaurantId),
            'failed_logins_today' => $this->countFailedLoginsToday($restaurantId),
            'open_incidents' => $incidentModel->countByStatus($restaurantId, 'open'),
            'active_encryption_keys' => $this->countActiveKeys($restaurantId),
            'security_score' => $this->calculateSecurityScore($restaurantId)
        ];
        
        // Get recent audit logs
        $recentAuditLogs = $auditLogModel->getRecent($restaurantId, 10);
        
        // Get open incidents
        $openIncidents = $incidentModel->getByStatus($restaurantId, 'open');
        
        // Get recent failed logins
        $recentFailedLogins = $this->getRecentFailedLogins($restaurantId, 5);
        
        return [
            'stats' => $stats,
            'recent_audit_logs' => $recentAuditLogs,
            'open_incidents' => $openIncidents,
            'recent_failed_logins' => $recentFailedLogins
        ];
    }

    /**
     * Calculate security score
     */
    private function calculateSecurityScore($restaurantId)
    {
        // In real implementation, calculate based on various security metrics
        // For now, return a default score
        return 85;
    }

    /**
     * Count failed logins today
     */
    private function countFailedLoginsToday($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM failed_login_attempts 
                WHERE restaurant_id = ? 
                AND DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count active keys
     */
    private function countActiveKeys($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM encryption_keys 
                WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get recent failed logins
     */
    private function getRecentFailedLogins($restaurantId, $limit)
    {
        $sql = "SELECT * FROM failed_login_attempts 
                WHERE restaurant_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get audit logs
     */
    public function getAuditLogs($restaurantId, $actionType, $actionCategory, $actionStatus, $page, $limit)
    {
        $auditLogModel = new SecurityAuditLog();
        return $auditLogModel->getPaginated($restaurantId, $actionType, $actionCategory, $actionStatus, $page, $limit);
    }

    /**
     * Log security action
     */
    public function logSecurityAction($restaurantId, $userId, $actionType, $actionCategory, $actionDescription, $actionStatus, $additionalData = [])
    {
        $auditLogModel = new SecurityAuditLog();
        
        $logData = [
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'action_category' => $actionCategory,
            'action_description' => $actionDescription,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'request_url' => $_SERVER['REQUEST_URI'] ?? null,
            'action_status' => $actionStatus,
            'action_data' => json_encode($additionalData)
        ];
        
        return $auditLogModel->create($logData);
    }

    /**
     * Get security incidents
     */
    public function getIncidents($restaurantId, $incidentType, $incidentStatus, $page, $limit)
    {
        $incidentModel = new SecurityIncident();
        return $incidentModel->getPaginated($restaurantId, $incidentType, $incidentStatus, $page, $limit);
    }

    /**
     * Create security incident
     */
    public function createIncident($restaurantId, $userId, $data)
    {
        $incidentModel = new SecurityIncident();
        
        $incidentData = [
            'restaurant_id' => $restaurantId,
            'incident_type' => $data->incident_type,
            'incident_severity' => $data->incident_severity ?? 'medium',
            'incident_title' => $data->incident_title,
            'incident_description' => $data->incident_description,
            'detected_at' => date('Y-m-d H:i:s'),
            'incident_status' => 'open',
            'incident_data' => json_encode($data->incident_data ?? [])
        ];
        
        $incidentId = $incidentModel->create($incidentData);
        
        if (!$incidentId) {
            return ['success' => false, 'message' => 'Failed to create incident'];
        }
        
        // Log the action
        $this->logSecurityAction($restaurantId, $userId, 'incident_created', 'system', 'Security incident created', 'success', [
            'incident_id' => $incidentId,
            'incident_type' => $data->incident_type
        ]);
        
        return ['success' => true, 'message' => 'Incident created successfully', 'incident_id' => $incidentId];
    }

    /**
     * Update security incident
     */
    public function updateIncident($id, $restaurantId, $userId, $data)
    {
        $incidentModel = new SecurityIncident();
        $incident = $incidentModel->findById($id, $restaurantId);
        
        if (!$incident) {
            return ['success' => false, 'message' => 'Incident not found'];
        }
        
        $updateData = [];
        
        if (isset($data->incident_status)) {
            $updateData['incident_status'] = $data->incident_status;
        }
        if (isset($data->incident_severity)) {
            $updateData['incident_severity'] = $data->incident_severity;
        }
        if (isset($data->response_actions)) {
            $updateData['response_actions'] = $data->response_actions;
        }
        if (isset($data->incident_status) && $data->incident_status === 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
            $updateData['resolved_by'] = $userId;
        }
        if (isset($data->resolution_notes)) {
            $updateData['resolution_notes'] = $data->resolution_notes;
        }
        
        $updated = $incidentModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update incident'];
        }
        
        // Log the action
        $this->logSecurityAction($restaurantId, $userId, 'incident_updated', 'system', 'Security incident updated', 'success', [
            'incident_id' => $id,
            'updates' => $updateData
        ]);
        
        return ['success' => true, 'message' => 'Incident updated successfully'];
    }

    /**
     * Get encryption keys
     */
    public function getEncryptionKeys($restaurantId)
    {
        $keyModel = new EncryptionKey();
        return $keyModel->getByRestaurant($restaurantId);
    }

    /**
     * Generate encryption key
     */
    public function generateEncryptionKey($restaurantId, $userId, $data)
    {
        $keyModel = new EncryptionKey();
        
        // Generate key based on type
        $keyValue = $this->generateKey($data->key_type, $data->key_size ?? 256);
        
        $keyData = [
            'restaurant_id' => $restaurantId,
            'key_name' => $data->key_name,
            'key_type' => $data->key_type,
            'key_purpose' => $data->key_purpose,
            'key_value_encrypted' => $this->encryptKey($keyValue),
            'key_algorithm' => $this->getAlgorithm($data->key_type),
            'key_size' => $data->key_size ?? 256,
            'key_version' => 1,
            'valid_from' => date('Y-m-d H:i:s'),
            'valid_until' => $data->valid_until ?? null,
            'is_active' => true,
            'last_rotated_at' => date('Y-m-d H:i:s'),
            'rotation_frequency_days' => $data->rotation_frequency_days ?? 90,
            'next_rotation_date' => date('Y-m-d', strtotime('+' . ($data->rotation_frequency_days ?? 90) . ' days')),
            'created_by' => $userId
        ];
        
        $keyId = $keyModel->create($keyData);
        
        if (!$keyId) {
            return ['success' => false, 'message' => 'Failed to generate key'];
        }
        
        // Log the action
        $this->logSecurityAction($restaurantId, $userId, 'key_generated', 'system', 'Encryption key generated', 'success', [
            'key_id' => $keyId,
            'key_name' => $data->key_name,
            'key_type' => $data->key_type
        ]);
        
        return ['success' => true, 'message' => 'Key generated successfully', 'key_id' => $keyId];
    }

    /**
     * Generate key
     */
    private function generateKey($keyType, $keySize)
    {
        // In real implementation, use proper cryptographic functions
        // For now, generate a random key
        return bin2hex(random_bytes($keySize / 8));
    }

    /**
     * Encrypt key
     */
    private function encryptKey($key)
    {
        // In real implementation, use proper encryption
        // For now, use base64
        return base64_encode($key);
    }

    /**
     * Get algorithm
     */
    private function getAlgorithm($keyType)
    {
        switch ($keyType) {
            case 'aes':
                return 'AES-256-GCM';
            case 'rsa':
                return 'RSA-2048';
            case 'hmac':
                return 'SHA-256';
            default:
                return 'UNKNOWN';
        }
    }

    /**
     * Rotate encryption key
     */
    public function rotateEncryptionKey($id, $restaurantId, $userId)
    {
        $keyModel = new EncryptionKey();
        $key = $keyModel->findById($id, $restaurantId);
        
        if (!$key) {
            return ['success' => false, 'message' => 'Key not found'];
        }
        
        // Generate new key
        $newKeyValue = $this->generateKey($key['key_type'], $key['key_size']);
        
        $updateData = [
            'key_value_encrypted' => $this->encryptKey($newKeyValue),
            'key_version' => $key['key_version'] + 1,
            'last_rotated_at' => date('Y-m-d H:i:s'),
            'next_rotation_date' => date('Y-m-d', strtotime('+' . $key['rotation_frequency_days'] . ' days'))
        ];
        
        $updated = $keyModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to rotate key'];
        }
        
        // Log the action
        $this->logSecurityAction($restaurantId, $userId, 'key_rotated', 'system', 'Encryption key rotated', 'success', [
            'key_id' => $id,
            'key_name' => $key['key_name'],
            'new_version' => $key['key_version'] + 1
        ]);
        
        return ['success' => true, 'message' => 'Key rotated successfully'];
    }

    /**
     * Get security policies
     */
    public function getPolicies($restaurantId, $policyType)
    {
        $policyModel = new SecurityPolicy();
        return $policyModel->getByRestaurant($restaurantId, $policyType);
    }

    /**
     * Update security policy
     */
    public function updatePolicy($id, $restaurantId, $data)
    {
        $policyModel = new SecurityPolicy();
        $policy = $policyModel->findById($id, $restaurantId);
        
        if (!$policy) {
            return ['success' => false, 'message' => 'Policy not found'];
        }
        
        $updateData = [];
        
        if (isset($data->policy_config)) {
            $updateData['policy_config'] = json_encode($data->policy_config);
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        if (isset($data->is_enforced)) {
            $updateData['is_enforced'] = $data->is_enforced;
        }
        if (isset($data->priority)) {
            $updateData['priority'] = $data->priority;
        }
        
        $updated = $policyModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update policy'];
        }
        
        return ['success' => true, 'message' => 'Policy updated successfully'];
    }

    /**
     * Get access control list
     */
    public function getACL($restaurantId, $userId, $resourceType)
    {
        $sql = "SELECT acl.*, u.username, u.full_name 
                FROM access_control_list acl
                LEFT JOIN users u ON acl.user_id = u.id
                WHERE acl.restaurant_id = ?";
        
        $params = [$restaurantId];
        
        if ($userId) {
            $sql .= " AND acl.user_id = ?";
            $params[] = $userId;
        }
        
        if ($resourceType) {
            $sql .= " AND acl.resource_type = ?";
            $params[] = $resourceType;
        }
        
        $sql .= " AND acl.is_active = TRUE ORDER BY acl.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Grant access
     */
    public function grantAccess($restaurantId, $userId, $data)
    {
        $sql = "INSERT INTO access_control_list 
                (restaurant_id, user_id, resource_type, resource_id, permission, granted_by, granted_at, is_active)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), TRUE)";
        
        $params = [
            $restaurantId,
            $data->user_id,
            $data->resource_type,
            $data->resource_id ?? null,
            $data->permission,
            $userId
        ];
        
        $result = $this->db->query($sql, $params);
        
        if ($result) {
            return ['success' => true, 'message' => 'Access granted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to grant access'];
    }

    /**
     * Revoke access
     */
    public function revokeAccess($id, $restaurantId)
    {
        $sql = "UPDATE access_control_list 
                SET is_active = FALSE, updated_at = NOW() 
                WHERE id = ? AND restaurant_id = ?";
        
        $result = $this->db->query($sql, [$id, $restaurantId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Access revoked successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to revoke access'];
    }

    /**
     * Get failed login attempts
     */
    public function getFailedLogins($restaurantId, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM failed_login_attempts WHERE restaurant_id = ?";
        $totalResult = $this->db->query($countSql, [$restaurantId])->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT * FROM failed_login_attempts 
                WHERE restaurant_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $data = $this->db->query($sql, [$restaurantId, $limit, $offset])->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Unlock account
     */
    public function unlockAccount($restaurantId, $data)
    {
        $sql = "UPDATE failed_login_attempts 
                SET is_locked = FALSE, locked_until = NULL 
                WHERE restaurant_id = ? 
                AND username_or_email = ?";
        
        $result = $this->db->query($sql, [$restaurantId, $data->username_or_email]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Account unlocked successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to unlock account'];
    }
}
