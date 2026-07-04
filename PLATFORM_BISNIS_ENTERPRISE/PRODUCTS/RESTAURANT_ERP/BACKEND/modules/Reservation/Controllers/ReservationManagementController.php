<?php

namespace App\Modules\Reservation\Controllers;

use App\Core\BaseController;
use App\Modules\Reservation\Models\Reservation;
use App\Modules\Reservation\Models\Waitlist;
use App\Modules\Reservation\Models\TableAvailability;
use App\Modules\Reservation\Services\ReservationService;
use App\Core\Auth;

class ReservationManagementController extends BaseController
{
    private $reservationService;

    public function __construct()
    {
        parent::__construct();
        $this->reservationService = new ReservationService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get reservations
     * GET /api/reservations
     */
    public function getReservations()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $date = $this->request->get('date', null);
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->reservationService->getReservations($restaurantId, $date, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single reservation
     * GET /api/reservations/{id}
     */
    public function getReservation($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $reservation = $this->reservationService->getReservation($id, $restaurantId);
        
        if (!$reservation) {
            $this->jsonResponse(['error' => 'Reservation not found'], 404);
            return;
        }
        
        $this->jsonResponse($reservation);
    }

    /**
     * Create reservation
     * POST /api/reservations
     */
    public function createReservation()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reservationService->createReservation($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update reservation
     * PUT /api/reservations/{id}
     */
    public function updateReservation($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reservationService->updateReservation($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Confirm reservation
     * POST /api/reservations/{id}/confirm
     */
    public function confirmReservation($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reservationService->confirmReservation($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Cancel reservation
     * POST /api/reservations/{id}/cancel
     */
    public function cancelReservation($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reservationService->cancelReservation($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Seat reservation
     * POST /api/reservations/{id}/seat
     */
    public function seatReservation($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reservationService->seatReservation($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get waitlist
     * GET /api/waitlist
     */
    public function getWaitlist()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->reservationService->getWaitlist($restaurantId, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Add to waitlist
     * POST /api/waitlist
     */
    public function addToWaitlist()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reservationService->addToWaitlist($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update waitlist status
     * PATCH /api/waitlist/{id}/status
     */
    public function updateWaitlistStatus($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reservationService->updateWaitlistStatus($id, $restaurantId, $userId, $data->status);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get table availability
     * GET /api/reservations/availability
     */
    public function getAvailability()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $date = $this->request->get('date', null);
        $time = $this->request->get('time', null);
        
        $availability = $this->reservationService->getAvailability($restaurantId, $date, $time);
        
        $this->jsonResponse($availability);
    }

    /**
     * Get reservation statistics
     * GET /api/reservations/statistics
     */
    public function getStatistics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        $stats = $this->reservationService->getStatistics($restaurantId, $dateFrom, $dateTo);
        
        $this->jsonResponse($stats);
    }
}
