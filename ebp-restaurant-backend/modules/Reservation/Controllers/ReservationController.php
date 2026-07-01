<?php

require_once __DIR__ . '/../Services/ReservationService.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
require_once __DIR__ . '/../../../core/Response.php';

class ReservationController
{
    private $reservationService;

    public function __construct()
    {
        $this->reservationService = new ReservationService();
    }

    public function getReservations(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'RESERVATION_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $reservations = $this->reservationService->getAllReservations($tenantId, $branchId);

        return Response::success($reservations);
    }

    public function getReservationsByDate(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'RESERVATION_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? 1;
        $date = $request['date'] ?? date('Y-m-d');

        $reservations = $this->reservationService->getReservationsByDate($tenantId, $branchId, $date);

        return Response::success($reservations);
    }

    public function getReservation(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'RESERVATION_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $reservationId = $request['reservation_id'] ?? 0;

        $reservation = $this->reservationService->getReservation($tenantId, $reservationId);

        if (!$reservation) {
            return Response::error('Reservation not found', 404);
        }

        return Response::success($reservation);
    }

    public function checkAvailability(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'RESERVATION_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? 1;
        $date = $request['date'] ?? '';
        $time = $request['time'] ?? '';
        $partySize = $request['party_size'] ?? 0;

        // Validation
        if (empty($date)) {
            return Response::error('Date is required', 400);
        }
        if (empty($time)) {
            return Response::error('Time is required', 400);
        }
        if (empty($partySize)) {
            return Response::error('Party size is required', 400);
        }

        $isAvailable = $this->reservationService->checkAvailability($tenantId, $branchId, $date, $time, $partySize);

        return Response::success(['available' => $isAvailable]);
    }

    public function createReservation(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'RESERVATION_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['branch_id'])) {
            return Response::error('Branch ID is required', 400);
        }
        if (empty($data['customer_name'])) {
            return Response::error('Customer name is required', 400);
        }
        if (empty($data['reservation_date'])) {
            return Response::error('Reservation date is required', 400);
        }
        if (empty($data['reservation_time'])) {
            return Response::error('Reservation time is required', 400);
        }
        if (empty($data['party_size'])) {
            return Response::error('Party size is required', 400);
        }

        $result = $this->reservationService->createReservation($tenantId, $data);

        if ($result) {
            return Response::success(['message' => 'Reservation created successfully']);
        }

        return Response::error('Failed to create reservation or no available tables', 500);
    }

    public function updateReservation(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'RESERVATION_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $reservationId = $request['reservation_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($reservationId)) {
            return Response::error('Reservation ID is required', 400);
        }
        if (empty($data['customer_name'])) {
            return Response::error('Customer name is required', 400);
        }

        $result = $this->reservationService->updateReservation($tenantId, $reservationId, $data);

        if ($result) {
            return Response::success(['message' => 'Reservation updated successfully']);
        }

        return Response::error('Failed to update reservation or no available tables', 500);
    }

    public function updateReservationStatus(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'RESERVATION_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $reservationId = $request['reservation_id'] ?? 0;
        $status = $request['body']['status'] ?? '';

        // Validation
        if (empty($reservationId)) {
            return Response::error('Reservation ID is required', 400);
        }
        if (empty($status)) {
            return Response::error('Status is required', 400);
        }

        $validStatuses = ['PENDING', 'CONFIRMED', 'SEATED', 'COMPLETED', 'CANCELLED', 'NO_SHOW'];
        if (!in_array($status, $validStatuses)) {
            return Response::error('Invalid status', 400);
        }

        $result = $this->reservationService->updateReservationStatus($tenantId, $reservationId, $status);

        if ($result) {
            return Response::success(['message' => 'Reservation status updated successfully']);
        }

        return Response::error('Failed to update reservation status', 500);
    }

    public function deleteReservation(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'RESERVATION_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $reservationId = $request['reservation_id'] ?? 0;

        // Validation
        if (empty($reservationId)) {
            return Response::error('Reservation ID is required', 400);
        }

        $result = $this->reservationService->deleteReservation($tenantId, $reservationId);

        if ($result) {
            return Response::success(['message' => 'Reservation deleted successfully']);
        }

        return Response::error('Failed to delete reservation', 500);
    }
}
