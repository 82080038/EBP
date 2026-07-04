<?php

namespace App\Modules\Reservation\Models;

use App\Core\BaseModel;

class ReservationModel extends BaseModel
{
    protected $table = 'reservations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'reservation_number',
        'reservation_date',
        'reservation_time',
        'party_size',
        'table_id',
        'table_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'special_requests',
        'dietary_restrictions',
        'occasion',
        'reservation_status',
        'is_confirmed',
        'confirmed_at',
        'confirmed_by',
        'confirmation_method',
        'estimated_duration',
        'actual_arrival_time',
        'seated_at',
        'completed_at',
        'cancelled_at',
        'created_by',
        'modified_by',
        'internal_notes',
        'cancellation_reason'
    ];

    /**
     * Get paginated reservations
     */
    public function getPaginated($restaurantId, $date, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($date) {
            $where .= " AND reservation_date = ?";
            $params[] = $date;
        }
        
        if ($status) {
            $where .= " AND reservation_status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT r.*, t.table_number, u1.username as created_by_name, u2.username as modified_by_name 
                FROM {$this->table} r
                LEFT JOIN tables t ON r.table_id = t.id
                LEFT JOIN users u1 ON r.created_by = u1.id
                LEFT JOIN users u2 ON r.modified_by = u2.id
                {$where}
                ORDER BY r.reservation_date ASC, r.reservation_time ASC
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $data = $this->db->query($sql, $params)->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT r.*, t.table_number, c.first_name, c.last_name 
                FROM {$this->table} r
                LEFT JOIN tables t ON r.table_id = t.id
                LEFT JOIN customers c ON r.customer_id = c.id
                WHERE r.id = ? AND r.restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by reservation number
     */
    public function findByNumber($reservationNumber, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE reservation_number = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$reservationNumber, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count by date range
     */
    public function countByDateRange($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND reservation_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND reservation_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status, $dateFrom = null, $dateTo = null)
    {
        $params = [$restaurantId, $status];
        $where = "WHERE restaurant_id = ? AND reservation_status = ?";
        
        if ($dateFrom) {
            $where .= " AND reservation_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND reservation_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by date
     */
    public function getByDate($restaurantId, $date)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND reservation_date = ? ORDER BY reservation_time ASC";
        return $this->db->query($sql, [$restaurantId, $date])->fetchAll();
    }
}
