<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('AttendanceService')) {
    require_once __DIR__ . '/../Services/AttendanceService.php';
}

class AttendanceController
{
    private $service;

    public function __construct()
    {
        $this->service = new AttendanceService();
    }

    /**
     * Check in employee
     */
    public function checkIn($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ATTENDANCE_MANAGE');

        $data = $request['body'] ?? [];
        $employeeId = $data['employee_id'] ?? $user['user_id'];
        $checkInTime = $data['check_in_time'] ?? null;

        if (!$employeeId) {
            Response::error('Employee ID is required');
        }

        $result = $this->service->checkIn($employeeId, $user['tenant_id'], $user['branch_id'], $checkInTime);

        if ($result['success']) {
            Response::success(['attendance_id' => $result['attendance_id']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Check out employee
     */
    public function checkOut($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();
        // $permissionMiddleware->check($user['user_id'], 'ATTENDANCE_MANAGE');

        $data = $request['body'] ?? [];
        $employeeId = $data['employee_id'] ?? $user['user_id'];
        $checkOutTime = $data['check_out_time'] ?? null;

        if (!$employeeId) {
            Response::error('Employee ID is required');
        }

        $result = $this->service->checkOut($employeeId, $checkOutTime);

        if ($result['success']) {
            Response::success(['work_hours' => $result['work_hours']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get attendance records
     */
    public function getAttendance($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $employeeId = $query['employee_id'] ?? null;
        $startDate = $query['start_date'] ?? null;
        $endDate = $query['end_date'] ?? null;

        $attendance = $this->service->getAttendance($user['tenant_id'], $user['branch_id'], $employeeId, $startDate, $endDate);
        Response::success($attendance, 'Attendance records retrieved successfully');
    }

    /**
     * Start break
     */
    public function startBreak($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $data = $request['body'] ?? [];
        $employeeId = $data['employee_id'] ?? $user['user_id'];

        if (!$employeeId) {
            Response::error('Employee ID is required');
        }

        $result = $this->service->startBreak($employeeId);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * End break
     */
    public function endBreak($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $data = $request['body'] ?? [];
        $employeeId = $data['employee_id'] ?? $user['user_id'];

        if (!$employeeId) {
            Response::error('Employee ID is required');
        }

        $result = $this->service->endBreak($employeeId);

        if ($result['success']) {
            Response::success([], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * Get attendance summary
     */
    public function getSummary($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $query = $request['query'] ?? [];
        $startDate = $query['start_date'] ?? date('Y-m-01');
        $endDate = $query['end_date'] ?? date('Y-m-t');

        if (!$startDate || !$endDate) {
            Response::error('Start date and end date are required');
        }

        $summary = $this->service->getAttendanceSummary($user['tenant_id'], $user['branch_id'], $startDate, $endDate);
        Response::success($summary, 'Attendance summary retrieved successfully');
    }
}
