<?php

namespace App\Modules\Reservation\Models;

use App\Core\BaseModel;

class Waitlist extends BaseModel
{
    protected $table = 'waitlist';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'waitlist_number',
        'party_size',
        'customer_name',
        'customer_phone',
        'customer_email',
        'preferred_table_type',
        'preferred_area',
        'special_requests',
        'waitlist_status',
        'joined_at',
        'estimated_wait_time',
        'notified_at',
        'seated_at',
        'cancelled_at',
        'created_by',
        'modified_by',
        'internal_notes',
        'cancellation_reason'
    ];

    /**
     * Get paginated waitlist
     */
    public function getPaginated($restaurantId, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND waitlist_status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT w.*, u1.username as created_by_name, u2.username as modified_by_name 
                FROM {$this->table} w
                LEFT JOIN users u1 ON w.created_by = u1.id
                LEFT JOIN users u2 ON w.modified_by = u2.id
                {$where}
                ORDER BY w.joined_at ASC
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
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by waitlist number
     */
    public function findByNumber($waitlistNumber, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE waitlist_number = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$waitlistNumber, $restaurantId])->fetch();
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
            $where .= " AND DATE(joined_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND DATE(joined_at) <= ?";
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
        $where = "WHERE restaurant_id = ? AND waitlist_status = ?";
        
        if ($dateFrom) {
            $where .= " AND DATE(joined_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND DATE(joined_at) <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get waiting (currently waiting)
     */
    public function getWaiting($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND waitlist_status = 'waiting'
                ORDER BY joined_at ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get by party size
     */
    public function getByPartySize($restaurantId, $partySize)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND party_size = ? AND waitlist_status = 'waiting'
                ORDER BY joined_at ASC";
        return $this->db->query($sql, [$restaurantId, $partySize])->fetchAll();
    }
}
