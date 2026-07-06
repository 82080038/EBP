<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('EmergencyClosureService')) {
    require_once __DIR__ . '/../Services/EmergencyClosureService.php';
}

class EmergencyClosureController
{
    private $service;

    public function __construct()
    {
        $this->service = new EmergencyClosureService();
    }

    /**
     * Create emergency closure
     */
    public function create($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'EMERGENCY_MANAGE');

        $data = $request['body'] ?? [];

        if (empty($data['closure_type']) || empty($data['start_time'])) {
            Response::error('Closure type and start time are required');
        }

        $result = $this->service->createClosure($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success(['closure_id' => $result['closure_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get active closures
     */
    public function getActive($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $closures = $this->service->getActiveClosures($user['tenant_id'], $user['branch_id']);
        Response::success($closures, 'Active closures retrieved successfully');
    }

    /**
     * Get all closures
     */
    public function getAll($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $startDate = $query['start_date'] ?? null;
        $endDate = $query['end_date'] ?? null;

        $closures = $this->service->getAllClosures($user['tenant_id'], $user['branch_id'], $startDate, $endDate);
        Response::success($closures, 'All closures retrieved successfully');
    }

    /**
     * Update closure
     */
    public function update($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'EMERGENCY_MANAGE');

        $data = $request['body'] ?? [];
        $closureId = $request['params']['closure_id'] ?? null;

        if (!$closureId) {
            Response::error('Closure ID is required');
        }

        $result = $this->service->updateClosure($closureId, $data);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Close emergency closure
     */
    public function close($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'EMERGENCY_MANAGE');

        $closureId = $request['params']['closure_id'] ?? null;
        $data = $request['body'] ?? [];
        $endTime = $data['end_time'] ?? null;

        if (!$closureId) {
            Response::error('Closure ID is required');
        }

        $result = $this->service->closeClosure($closureId, $endTime);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Update notification status
     */
    public function updateNotification($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'EMERGENCY_MANAGE');

        $closureId = $request['params']['closure_id'] ?? null;
        $data = $request['body'] ?? [];
        $notifiedEmployees = $data['notified_employees'] ?? false;
        $notifiedCustomers = $data['notified_customers'] ?? false;

        if (!$closureId) {
            Response::error('Closure ID is required');
        }

        $result = $this->service->updateNotificationStatus($closureId, $notifiedEmployees, $notifiedCustomers);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Check if emergency closed
     */
    public function checkStatus($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $dateTime = $query['datetime'] ?? null;

        $isClosed = $this->service->isEmergencyClosed($user['tenant_id'], $user['branch_id'], $dateTime);
        Response::success(['is_emergency_closed' => $isClosed], 'Emergency status checked');
    }
}
