<?php

namespace App\Modules\Reservation\Services;

use App\Modules\Reservation\Models\Reservation;
use App\Modules\Reservation\Models\Waitlist;
use App\Modules\Reservation\Models\TableAvailability;
use App\Core\Database;

class ReservationManagementService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get reservations
     */
    public function getReservations($restaurantId, $date, $status, $page, $limit)
    {
        $reservationModel = new Reservation();
        return $reservationModel->getPaginated($restaurantId, $date, $status, $page, $limit);
    }

    /**
     * Get single reservation
     */
    public function getReservation($id, $restaurantId)
    {
        $reservationModel = new Reservation();
        $reservation = $reservationModel->findById($id, $restaurantId);
        
        if ($reservation) {
            // Get history
            $reservation['history'] = $this->getReservationHistory($id);
        }
        
        return $reservation;
    }

    /**
     * Get reservation history
     */
    private function getReservationHistory($reservationId)
    {
        $sql = "SELECT rh.*, u.username as performed_by_name 
                FROM reservation_history rh
                LEFT JOIN users u ON rh.performed_by = u.id
                WHERE rh.reservation_id = ?
                ORDER BY rh.performed_at DESC";
        return $this->db->query($sql, [$reservationId])->fetchAll();
    }

    /**
     * Create reservation
     */
    public function createReservation($restaurantId, $userId, $data)
    {
        $reservationModel = new Reservation();
        
        $reservationData = [
            'restaurant_id' => $restaurantId,
            'customer_id' => $data->customer_id ?? null,
            'reservation_date' => $data->reservation_date,
            'reservation_time' => $data->reservation_time,
            'party_size' => $data->party_size,
            'table_id' => $data->table_id ?? null,
            'customer_name' => $data->customer_name,
            'customer_phone' => $data->customer_phone,
            'customer_email' => $data->customer_email ?? null,
            'special_requests' => $data->special_requests ?? null,
            'dietary_restrictions' => $data->dietary_restrictions ?? null,
            'occasion' => $data->occasion ?? null,
            'reservation_status' => 'pending',
            'is_confirmed' => false,
            'estimated_duration' => $data->estimated_duration ?? null,
            'created_by' => $userId,
            'internal_notes' => $data->internal_notes ?? null
        ];
        
        $reservationId = $reservationModel->create($reservationData);
        
        if (!$reservationId) {
            return ['success' => false, 'message' => 'Failed to create reservation'];
        }
        
        // Log history
        $this->logReservationHistory($reservationId, $restaurantId, 'created', null, 'pending', $userId);
        
        // Update table availability if table assigned
        if (isset($data->table_id) && $data->table_id) {
            $this->updateTableAvailability($restaurantId, $data->table_id, $data->reservation_date, $data->reservation_time, $reservationId);
        }
        
        return ['success' => true, 'message' => 'Reservation created', 'reservation_id' => $reservationId];
    }

    /**
     * Update reservation
     */
    public function updateReservation($id, $restaurantId, $userId, $data)
    {
        $reservationModel = new Reservation();
        $reservation = $reservationModel->findById($id, $restaurantId);
        
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }
        
        $updateData = [];
        $oldStatus = $reservation['reservation_status'];
        
        if (isset($data->reservation_date)) {
            $updateData['reservation_date'] = $data->reservation_date;
        }
        if (isset($data->reservation_time)) {
            $updateData['reservation_time'] = $data->reservation_time;
        }
        if (isset($data->party_size)) {
            $updateData['party_size'] = $data->party_size;
        }
        if (isset($data->table_id)) {
            $updateData['table_id'] = $data->table_id;
        }
        if (isset($data->customer_name)) {
            $updateData['customer_name'] = $data->customer_name;
        }
        if (isset($data->customer_phone)) {
            $updateData['customer_phone'] = $data->customer_phone;
        }
        if (isset($data->special_requests)) {
            $updateData['special_requests'] = $data->special_requests;
        }
        if (isset($data->internal_notes)) {
            $updateData['internal_notes'] = $data->internal_notes;
        }
        
        $updateData['modified_by'] = $userId;
        
        $updated = $reservationModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update reservation'];
        }
        
        return ['success' => true, 'message' => 'Reservation updated'];
    }

    /**
     * Confirm reservation
     */
    public function confirmReservation($id, $restaurantId, $userId, $data)
    {
        $reservationModel = new Reservation();
        $reservation = $reservationModel->findById($id, $restaurantId);
        
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }
        
        $updated = $reservationModel->update($id, [
            'is_confirmed' => true,
            'confirmed_at' => date('Y-m-d H:i:s'),
            'confirmed_by' => $userId,
            'confirmation_method' => $data->confirmation_method ?? 'phone',
            'reservation_status' => 'confirmed',
            'modified_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to confirm reservation'];
        }
        
        // Log history
        $this->logReservationHistory($id, $restaurantId, 'confirmed', $reservation['reservation_status'], 'confirmed', $userId);
        
        return ['success' => true, 'message' => 'Reservation confirmed'];
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation($id, $restaurantId, $userId, $data)
    {
        $reservationModel = new Reservation();
        $reservation = $reservationModel->findById($id, $restaurantId);
        
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }
        
        if ($reservation['reservation_status'] === 'cancelled' || $reservation['reservation_status'] === 'completed') {
            return ['success' => false, 'message' => 'Cannot cancel this reservation'];
        }
        
        $updated = $reservationModel->update($id, [
            'reservation_status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $data->reason ?? null,
            'modified_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to cancel reservation'];
        }
        
        // Log history
        $this->logReservationHistory($id, $restaurantId, 'cancelled', $reservation['reservation_status'], 'cancelled', $userId);
        
        // Release table availability
        if ($reservation['table_id']) {
            $this->releaseTableAvailability($reservation['table_id'], $reservation['reservation_date'], $reservation['reservation_time']);
        }
        
        return ['success' => true, 'message' => 'Reservation cancelled'];
    }

    /**
     * Seat reservation
     */
    public function seatReservation($id, $restaurantId, $userId, $data)
    {
        $reservationModel = new Reservation();
        $reservation = $reservationModel->findById($id, $restaurantId);
        
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }
        
        $updated = $reservationModel->update($id, [
            'reservation_status' => 'seated',
            'actual_arrival_time' => date('Y-m-d H:i:s'),
            'seated_at' => date('Y-m-d H:i:s'),
            'table_id' => $data->table_id ?? $reservation['table_id'],
            'modified_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to seat reservation'];
        }
        
        // Log history
        $this->logReservationHistory($id, $restaurantId, 'seated', $reservation['reservation_status'], 'seated', $userId);
        
        return ['success' => true, 'message' => 'Reservation seated'];
    }

    /**
     * Log reservation history
     */
    private function logReservationHistory($reservationId, $restaurantId, $actionType, $oldStatus, $newStatus, $userId)
    {
        $sql = "INSERT INTO reservation_history (reservation_id, restaurant_id, action_type, old_status, new_status, performed_by, performed_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $this->db->query($sql, [$reservationId, $restaurantId, $actionType, $oldStatus, $newStatus, $userId]);
    }

    /**
     * Update table availability
     */
    private function updateTableAvailability($restaurantId, $tableId, $date, $time, $reservationId)
    {
        $sql = "INSERT INTO table_availability (restaurant_id, table_id, availability_date, availability_time, is_available, reservation_id)
                VALUES (?, ?, ?, ?, FALSE, ?)
                ON DUPLICATE KEY UPDATE is_available = FALSE, reservation_id = VALUES(reservation_id)";
        
        $this->db->query($sql, [$restaurantId, $tableId, $date, $time, $reservationId]);
    }

    /**
     * Release table availability
     */
    private function releaseTableAvailability($tableId, $date, $time)
    {
        $sql = "UPDATE table_availability SET is_available = TRUE, reservation_id = NULL 
                WHERE table_id = ? AND availability_date = ? AND availability_time = ?";
        
        $this->db->query($sql, [$tableId, $date, $time]);
    }

    /**
     * Get waitlist
     */
    public function getWaitlist($restaurantId, $status, $page, $limit)
    {
        $waitlistModel = new Waitlist();
        return $waitlistModel->getPaginated($restaurantId, $status, $page, $limit);
    }

    /**
     * Add to waitlist
     */
    public function addToWaitlist($restaurantId, $userId, $data)
    {
        $waitlistModel = new Waitlist();
        
        $waitlistData = [
            'restaurant_id' => $restaurantId,
            'customer_id' => $data->customer_id ?? null,
            'party_size' => $data->party_size,
            'customer_name' => $data->customer_name,
            'customer_phone' => $data->customer_phone,
            'customer_email' => $data->customer_email ?? null,
            'preferred_table_type' => $data->preferred_table_type ?? 'any',
            'preferred_area' => $data->preferred_area ?? null,
            'special_requests' => $data->special_requests ?? null,
            'waitlist_status' => 'waiting',
            'joined_at' => date('Y-m-d H:i:s'),
            'estimated_wait_time' => $data->estimated_wait_time ?? null,
            'created_by' => $userId,
            'internal_notes' => $data->internal_notes ?? null
        ];
        
        $waitlistId = $waitlistModel->create($waitlistData);
        
        if (!$waitlistId) {
            return ['success' => false, 'message' => 'Failed to add to waitlist'];
        }
        
        return ['success' => true, 'message' => 'Added to waitlist', 'waitlist_id' => $waitlistId];
    }

    /**
     * Update waitlist status
     */
    public function updateWaitlistStatus($id, $restaurantId, $userId, $status)
    {
        $waitlistModel = new Waitlist();
        $waitlist = $waitlistModel->findById($id, $restaurantId);
        
        if (!$waitlist) {
            return ['success' => false, 'message' => 'Waitlist entry not found'];
        }
        
        $updateData = ['waitlist_status' => $status, 'modified_by' => $userId];
        
        if ($status === 'seated') {
            $updateData['seated_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'cancelled') {
            $updateData['cancelled_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'notified') {
            $updateData['notified_at'] = date('Y-m-d H:i:s');
        }
        
        $updated = $waitlistModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update waitlist status'];
        }
        
        return ['success' => true, 'message' => 'Waitlist status updated'];
    }

    /**
     * Get availability
     */
    public function getAvailability($restaurantId, $date, $time)
    {
        $availabilityModel = new TableAvailability();
        return $availabilityModel->getByDateTime($restaurantId, $date, $time);
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId, $dateFrom, $dateTo)
    {
        $reservationModel = new Reservation();
        $waitlistModel = new Waitlist();
        
        // Total reservations
        $totalReservations = $reservationModel->countByDateRange($restaurantId, $dateFrom, $dateTo);
        
        // Confirmed reservations
        $confirmedReservations = $reservationModel->countByStatus($restaurantId, 'confirmed', $dateFrom, $dateTo);
        
        // Seated reservations
        $seatedReservations = $reservationModel->countByStatus($restaurantId, 'seated', $dateFrom, $dateTo);
        
        // No-shows
        $noShows = $reservationModel->countByStatus($restaurantId, 'no_show', $dateFrom, $dateTo);
        
        // Total waitlist entries
        $totalWaitlist = $waitlistModel->countByDateRange($restaurantId, $dateFrom, $dateTo);
        
        // Seated from waitlist
        $seatedFromWaitlist = $waitlistModel->countByStatus($restaurantId, 'seated', $dateFrom, $dateTo);
        
        return [
            'total_reservations' => $totalReservations,
            'confirmed_reservations' => $confirmedReservations,
            'seated_reservations' => $seatedReservations,
            'no_shows' => $noShows,
            'total_waitlist' => $totalWaitlist,
            'seated_from_waitlist' => $seatedFrom_waitlist
        ];
    }
}
