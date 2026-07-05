<?php

if (!class_exists('ReservationService')) {
    require_once __DIR__ . '/../Services/ReservationService.php';
}


// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';


class ReservationController
{
    private $reservationService;

    public function __construct()
    {
        $this->reservationService = new ReservationService();
    }

    public function getReservations(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $reservations = $this->reservationService->getAllReservations($tenantId, $branchId);

        return Response::success($reservations);
    }

    public function getReservationsByDate(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? 1;
        $date = $request['date'] ?? date('Y-m-d');

        $reservations = $this->reservationService->getReservationsByDate($tenantId, $branchId, $date);

        return Response::success($reservations);
    }

    public function getReservation(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $reservationId = $request['reservation_id'] ?? 0;

        $reservation = $this->reservationService->getReservation($tenantId, $reservationId);

        if (!$reservation) {
            return Response::error(Messages::RESERVATION_NOT_FOUND, 404);
        }

        return Response::success($reservation);
    }

    public function checkAvailability(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? 1;
        $date = $request['date'] ?? '';
        $time = $request['time'] ?? '';
        $partySize = $request['party_size'] ?? 0;

        // Validation
        if (empty($date)) {
            return Response::error(Messages::RESERVATION_DATE_REQUIRED, 400);
        }
        if (empty($time)) {
            return Response::error(Messages::RESERVATION_TIME_REQUIRED, 400);
        }
        if (empty($partySize)) {
            return Response::error(Messages::RESERVATION_PARTY_SIZE_REQUIRED, 400);
        }

        $isAvailable = $this->reservationService->checkAvailability($tenantId, $branchId, $date, $time, $partySize);

        return Response::success(['available' => $isAvailable]);
    }

    public function createReservation(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['branch_id'])) {
            return Response::error(Messages::RESERVATION_BRANCH_REQUIRED, 400);
        }
        if (empty($data['customer_name'])) {
            return Response::error(Messages::RESERVATION_CUSTOMER_NAME_REQUIRED, 400);
        }
        if (empty($data['reservation_date'])) {
            return Response::error(Messages::RESERVATION_DATE_REQUIRED, 400);
        }
        if (empty($data['reservation_time'])) {
            return Response::error(Messages::RESERVATION_TIME_REQUIRED, 400);
        }
        if (empty($data['party_size'])) {
            return Response::error(Messages::RESERVATION_PARTY_SIZE_REQUIRED, 400);
        }

        $result = $this->reservationService->createReservation($tenantId, $data);

        if ($result) {
            return Response::success(['message' => Messages::RESERVATION_CREATED]);
        }

        return Response::error(Messages::RESERVATION_FAILED_CREATE, 500);
    }

    public function updateReservation(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $reservationId = $request['reservation_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($reservationId)) {
            return Response::error(Messages::RESERVATION_ID_REQUIRED, 400);
        }
        if (empty($data['customer_name'])) {
            return Response::error(Messages::RESERVATION_CUSTOMER_NAME_REQUIRED, 400);
        }

        $result = $this->reservationService->updateReservation($tenantId, $reservationId, $data);

        if ($result) {
            return Response::success(['message' => Messages::RESERVATION_UPDATED]);
        }

        return Response::error(Messages::RESERVATION_FAILED_UPDATE, 500);
    }

    public function updateReservationStatus(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $reservationId = $request['reservation_id'] ?? 0;
        $status = $request['body']['status'] ?? '';

        // Validation
        if (empty($reservationId)) {
            return Response::error(Messages::RESERVATION_ID_REQUIRED, 400);
        }
        if (empty($status)) {
            return Response::error(Messages::RESERVATION_STATUS_REQUIRED, 400);
        }

        $validStatuses = ['PENDING', 'CONFIRMED', 'SEATED', 'COMPLETED', 'CANCELLED', 'NO_SHOW'];
        if (!in_array($status, $validStatuses)) {
            return Response::error(Messages::VALIDATION_INVALID, 400);
        }

        $result = $this->reservationService->updateReservationStatus($tenantId, $reservationId, $status);

        if ($result) {
            return Response::success(['message' => Messages::RESERVATION_UPDATED]);
        }

        return Response::error(Messages::RESERVATION_FAILED_UPDATE, 500);
    }

    public function deleteReservation(array $request)
    {
        // Permission checking is now handled in routes
        $tenantId = $request['tenant_id'] ?? 1;
        $reservationId = $request['reservation_id'] ?? 0;

        // Validation
        if (empty($reservationId)) {
            return Response::error(Messages::RESERVATION_ID_REQUIRED, 400);
        }

        $result = $this->reservationService->deleteReservation($tenantId, $reservationId);

        if ($result) {
            return Response::success(['message' => Messages::RESERVATION_DELETED]);
        }

        return Response::error(Messages::RESERVATION_FAILED_DELETE, 500);
    }
}
