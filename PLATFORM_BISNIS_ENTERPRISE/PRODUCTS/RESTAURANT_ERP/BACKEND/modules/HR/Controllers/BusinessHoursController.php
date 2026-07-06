<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('BusinessHoursService')) {
    require_once __DIR__ . '/../Services/BusinessHoursService.php';
}

class BusinessHoursController
{
    private $service;

    public function __construct()
    {
        $this->service = new BusinessHoursService();
    }

    /**
     * Set business hours
     */
    public function setHours($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'BUSINESS_HOURS_MANAGE');

        $data = $request['body'] ?? [];

        if (empty($data['day_of_week']) || empty($data['open_time']) || empty($data['close_time'])) {
            Response::error('Day of week, open time, and close time are required');
        }

        $result = $this->service->setBusinessHours(
            $user['tenant_id'],
            $user['branch_id'],
            $data['day_of_week'],
            $data['open_time'],
            $data['close_time'],
            $data['is_closed'] ?? false,
            $data['break_start_time'] ?? null,
            $data['break_end_time'] ?? null
        );

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get business hours
     */
    public function getHours($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $hours = $this->service->getBusinessHours($user['tenant_id'], $user['branch_id']);
        Response::success($hours, 'Business hours retrieved successfully');
    }

    /**
     * Check if open
     */
    public function checkOpen($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $dateTime = $query['datetime'] ?? null;

        if ($dateTime) {
            $dateTime = new DateTime($dateTime);
        }

        $isOpen = $this->service->isOpen($user['tenant_id'], $user['branch_id'], $dateTime);
        Response::success(['is_open' => $isOpen], 'Business status checked');
    }

    /**
     * Create special schedule
     */
    public function createSpecialSchedule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'BUSINESS_HOURS_MANAGE');

        $data = $request['body'] ?? [];

        if (empty($data['schedule_date'])) {
            Response::error('Schedule date is required');
        }

        $result = $this->service->createSpecialSchedule($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get special schedules
     */
    public function getSpecialSchedules($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $startDate = $query['start_date'] ?? null;
        $endDate = $query['end_date'] ?? null;

        $schedules = $this->service->getSpecialSchedules($user['tenant_id'], $user['branch_id'], $startDate, $endDate);
        Response::success($schedules, 'Special schedules retrieved successfully');
    }

    /**
     * Delete special schedule
     */
    public function deleteSpecialSchedule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'BUSINESS_HOURS_MANAGE');

        $scheduleId = $request['params']['schedule_id'] ?? null;

        if (!$scheduleId) {
            Response::error('Schedule ID is required');
        }

        $result = $this->service->deleteSpecialSchedule($scheduleId);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
