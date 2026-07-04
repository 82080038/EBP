<?php

namespace App\Modules\Sustainability\Models;

use App\Core\BaseModel;

class WasteTracking extends BaseModel
{
    protected $table = 'waste_tracking';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'waste_date',
        'waste_type',
        'waste_category',
        'waste_quantity',
        'waste_unit',
        'disposal_method',
        'disposal_cost',
        'waste_source',
        'notes',
        'recorded_by'
    ];

    /**
     * Get paginated waste records
     */
    public function getPaginated($restaurantId, $wasteType, $dateFrom, $dateTo, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($wasteType) {
            $where .= " AND waste_type = ?";
            $params[] = $wasteType;
        }
        
        if ($dateFrom) {
            $where .= " AND waste_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND waste_date <= ?";
            $params[] = $dateTo;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT wt.*, u.username as recorded_by_name 
                FROM {$this->table} wt
                LEFT JOIN users u ON wt.recorded_by = u.id
                {$where}
                ORDER BY wt.waste_date DESC, wt.created_at DESC
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
     * Get total for month
     */
    public function getTotalForMonth($restaurantId)
    {
        $sql = "SELECT SUM(waste_quantity) as total, SUM(disposal_cost) as total_cost 
                FROM {$this->table} 
                WHERE restaurant_id = ? AND waste_date >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result ?: ['total' => 0, 'total_cost' => 0];
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $wasteType, $limit = 30)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND waste_type = ? ORDER BY waste_date DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $wasteType, $limit])->fetchAll();
    }

    /**
     * Get waste breakdown by type
     */
    public function getBreakdownByType($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND waste_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND waste_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT waste_type, SUM(waste_quantity) as total_quantity, SUM(disposal_cost) as total_cost
                FROM {$this->table} {$where}
                GROUP BY waste_type
                ORDER BY total_quantity DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
}
