<?php
if (!class_exists('database')) {
    require_once __DIR__ . "/../../../config/database.php";
}
if (!class_exists('Reservation')) {
    require_once __DIR__ . '/../Models/Reservation.php';
}

use PDO;

class ReservationRepository
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function findAll(int $tenantId, ?int $branchId = null): array
    {
        $sql = "
            SELECT * FROM reservations 
            WHERE tenant_id = :tenant_id AND deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY reservation_date DESC, reservation_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $reservations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservations[] = new Reservation($row);
        }
        
        return $reservations;
    }

    public function findById(int $tenantId, int $reservationId): ?Reservation
    {
        $stmt = $this->db->prepare("
            SELECT * FROM reservations 
            WHERE tenant_id = :tenant_id AND reservation_id = :reservation_id AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'reservation_id' => $reservationId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Reservation($row) : null;
    }

    public function findByDate(int $tenantId, int $branchId, string $date): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM reservations 
            WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND reservation_date = :date AND deleted_at IS NULL
            ORDER BY reservation_time ASC
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'date' => $date]);
        
        $reservations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reservations[] = new Reservation($row);
        }
        
        return $reservations;
    }

    public function checkAvailability(int $tenantId, int $branchId, string $date, string $time, int $partySize): bool
    {
        // Check if there are available tables for the given date, time, and party size
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM tables t
            WHERE t.tenant_id = :tenant_id 
            AND t.branch_id = :branch_id
            AND t.capacity >= :party_size
            AND t.status = 'AVAILABLE'
            AND t.deleted_at IS NULL
            AND t.table_id NOT IN (
                SELECT r.table_id 
                FROM reservations r
                WHERE r.tenant_id = :tenant_id
                AND r.branch_id = :branch_id
                AND r.reservation_date = :date
                AND r.reservation_time = :time
                AND r.status IN ('PENDING', 'CONFIRMED')
                AND r.deleted_at IS NULL
            )
        ");
        $stmt->execute([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'party_size' => $partySize,
            'date' => $date,
            'time' => $time
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function create(Reservation $reservation): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO reservations 
            (tenant_id, branch_id, reservation_number, customer_name, customer_phone, customer_email, table_id, reservation_date, reservation_time, party_size, status, notes)
            VALUES 
            (:tenant_id, :branch_id, :reservation_number, :customer_name, :customer_phone, :customer_email, :table_id, :reservation_date, :reservation_time, :party_size, :status, :notes)
        ");
        
        return $stmt->execute([
            'tenant_id' => $reservation->tenant_id,
            'branch_id' => $reservation->branch_id,
            'reservation_number' => $reservation->reservation_number,
            'customer_name' => $reservation->customer_name,
            'customer_phone' => $reservation->customer_phone,
            'customer_email' => $reservation->customer_email,
            'table_id' => $reservation->table_id,
            'reservation_date' => $reservation->reservation_date,
            'reservation_time' => $reservation->reservation_time,
            'party_size' => $reservation->party_size,
            'status' => $reservation->status ?? 'PENDING',
            'notes' => $reservation->notes
        ]);
    }

    public function update(Reservation $reservation): bool
    {
        $stmt = $this->db->prepare("
            UPDATE reservations 
            SET customer_name = :customer_name,
                customer_phone = :customer_phone,
                customer_email = :customer_email,
                table_id = :table_id,
                reservation_date = :reservation_date,
                reservation_time = :reservation_time,
                party_size = :party_size,
                status = :status,
                notes = :notes,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND reservation_id = :reservation_id
        ");
        
        return $stmt->execute([
            'tenant_id' => $reservation->tenant_id,
            'reservation_id' => $reservation->reservation_id,
            'customer_name' => $reservation->customer_name,
            'customer_phone' => $reservation->customer_phone,
            'customer_email' => $reservation->customer_email,
            'table_id' => $reservation->table_id,
            'reservation_date' => $reservation->reservation_date,
            'reservation_time' => $reservation->reservation_time,
            'party_size' => $reservation->party_size,
            'status' => $reservation->status,
            'notes' => $reservation->notes
        ]);
    }

    public function updateStatus(int $tenantId, int $reservationId, string $status): bool
    {
        $stmt = $this->db->prepare("
            UPDATE reservations 
            SET status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND reservation_id = :reservation_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'reservation_id' => $reservationId, 'status' => $status]);
    }

    public function delete(int $tenantId, int $reservationId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE reservations 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE tenant_id = :tenant_id AND reservation_id = :reservation_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'reservation_id' => $reservationId]);
    }

    public function generateReservationNumber(int $tenantId, int $branchId): string
    {
        $prefix = 'RES';
        $date = date('Ymd');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM reservations 
            WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND reservation_date = CURDATE()
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'branch_id' => $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . $date . $sequence;
    }
}
