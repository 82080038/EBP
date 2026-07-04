<?php

namespace App\Modules\Security\Controllers;

use App\Core\BaseController;
use App\Modules\Security\Models\SecurityAuditLog;
use App\Modules\Security\Models\SecurityIncident;
use App\Modules\Security\Models\EncryptionKey;
use App\Modules\Security\Models\SecurityPolicy;
use App\Modules\Security\Services\SecurityService;
use App\Core\Auth;

class SecurityController extends BaseController
{
    private $securityService;

    public function __construct()
    {
        parent::__construct();
        $this->securityService = new SecurityService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get security dashboard
     * GET /api/security/dashboard
     */
    public function dashboard()
    {
        $this->requirePermission('can_view_security_logs');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $dashboard = $this->securityService->getDashboard($restaurantId);
        
        $this->jsonResponse($dashboard);
    }

    /**
     * Get security audit logs
     * GET /api/security/audit-logs
     */
    public function getAuditLogs()
    {
        $this->requirePermission('can_view_security_logs');
        
        $restaurantId = Auth::user()->restaurant_id;
        $actionType = $this->request->get('action_type', null);
        $actionCategory = $this->request->get('action_category', null);
        $actionStatus = $this->request->get('action_status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->securityService->getAuditLogs($restaurantId, $actionType, $actionCategory, $actionStatus, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get security incidents
     * GET /api/security/incidents
     */
    public function getIncidents()
    {
        $this->requirePermission('can_view_security_logs');
        
        $restaurantId = Auth::user()->restaurant_id;
        $incidentType = $this->request->get('incident_type', null);
        $incidentStatus = $this->request->get('incident_status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->securityService->getIncidents($restaurantId, $incidentType, $incidentStatus, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Create security incident
     * POST /api/security/incidents
     */
    public function createIncident()
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->securityService->createIncident($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update security incident
     * PUT /api/security/incidents/{id}
     */
    public function updateIncident($id)
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->securityService->updateIncident($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get encryption keys
     * GET /api/security/encryption-keys
     */
    public function getEncryptionKeys()
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $keys = $this->securityService->getEncryptionKeys($restaurantId);
        
        $this->jsonResponse($keys);
    }

    /**
     * Generate encryption key
     * POST /api/security/encryption-keys
     */
    public function generateEncryptionKey()
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->securityService->generateEncryptionKey($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Rotate encryption key
     * POST /api/security/encryption-keys/{id}/rotate
     */
    public function rotateEncryptionKey($id)
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $result = $this->securityService->rotateEncryptionKey($id, $restaurantId, $userId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get security policies
     * GET /api/security/policies
     */
    public function getPolicies()
    {
        $this->requirePermission('can_view_security_logs');
        
        $restaurantId = Auth::user()->restaurant_id;
        $policyType = $this->request->get('policy_type', null);
        
        $policies = $this->securityService->getPolicies($restaurantId, $policyType);
        
        $this->jsonResponse($policies);
    }

    /**
     * Update security policy
     * PUT /api/security/policies/{id}
     */
    public function updatePolicy($id)
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->securityService->updatePolicy($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get access control list
     * GET /api/security/acl
     */
    public function getACL()
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = $this->request->get('user_id', null);
        $resourceType = $this->request->get('resource_type', null);
        
        $acl = $this->securityService->getACL($restaurantId, $userId, $resourceType);
        
        $this->jsonResponse($acl);
    }

    /**
     * Grant access
     * POST /api/security/acl
     */
    public function grantAccess()
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->securityService->grantAccess($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Revoke access
     * DELETE /api/security/acl/{id}
     */
    public function revokeAccess($id)
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $result = $this->securityService->revokeAccess($id, $restaurantId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse(['message' => 'Access revoked successfully']);
    }

    /**
     * Get failed login attempts
     * GET /api/security/failed-logins
     */
    public function getFailedLogins()
    {
        $this->requirePermission('can_view_security_logs');
        
        $restaurantId = Auth::user()->restaurant_id;
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->securityService->getFailedLogins($restaurantId, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Unlock account
     * POST /api/security/unlock-account
     */
    public function unlockAccount()
    {
        $this->requirePermission('can_manage_security_settings');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->securityService->unlockAccount($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }
}
