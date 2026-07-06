<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('HolidayService')) {
    require_once __DIR__ . '/../Services/HolidayService.php';
}

class HolidayController
{
    private $service;

    public function __construct()
    {
        $this->service = new HolidayService();
    }

    /**
     * Create holiday
     */
    public function create($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'HOLIDAY_MANAGE');

        $data = $request['body'] ?? [];

        if (empty($data['holiday_name']) || empty($data['holiday_date'])) {
            Response::error('Holiday name and date are required');
        }

        $result = $this->service->createHoliday($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success(['holiday_id' => $result['holiday_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get holidays
     */
    public function getHolidays($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $startDate = $query['start_date'] ?? null;
        $endDate = $query['end_date'] ?? null;

        $holidays = $this->service->getHolidays($user['tenant_id'], $user['branch_id'], $startDate, $endDate);
        Response::success($holidays, 'Holidays retrieved successfully');
    }

    /**
     * Update holiday
     */
    public function update($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'HOLIDAY_MANAGE');

        $data = $request['body'] ?? [];
        $holidayId = $request['params']['holiday_id'] ?? null;

        if (!$holidayId) {
            Response::error('Holiday ID is required');
        }

        if (empty($data['holiday_name']) || empty($data['holiday_date'])) {
            Response::error('Holiday name and date are required');
        }

        $result = $this->service->updateHoliday($holidayId, $data);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Delete holiday
     */
    public function delete($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'HOLIDAY_MANAGE');

        $holidayId = $request['params']['holiday_id'] ?? null;

        if (!$holidayId) {
            Response::error('Holiday ID is required');
        }

        $result = $this->service->deleteHoliday($holidayId);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Check if date is holiday
     */
    public function checkHoliday($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $date = $query['date'] ?? date('Y-m-d');

        $isHoliday = $this->service->isHoliday($user['tenant_id'], $user['branch_id'], $date);
        Response::success(['is_holiday' => $isHoliday], 'Holiday status checked');
    }
}
