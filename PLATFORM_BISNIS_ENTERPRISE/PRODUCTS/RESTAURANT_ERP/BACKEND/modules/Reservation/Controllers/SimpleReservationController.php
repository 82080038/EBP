<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class SimpleReservationController
{
    // Simple endpoint to get reservations without middleware
    public function getReservations($request = null)
    {
        $database = new Database();
        $db = $database->connect();

        $sql = "SELECT r.reservation_id, r.customer_name, r.customer_phone as phone, r.reservation_date, r.reservation_time, 
                r.party_size as number_of_guests, r.status, r.notes,
                t.table_number
                FROM reservations r
                LEFT JOIN tables t ON r.table_id = t.table_id
                WHERE r.reservation_date >= CURDATE() AND r.status != 'CANCELLED' AND r.status != 'NO_SHOW'
                ORDER BY r.reservation_date ASC, r.reservation_time ASC
                LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($reservations);
    }
}
