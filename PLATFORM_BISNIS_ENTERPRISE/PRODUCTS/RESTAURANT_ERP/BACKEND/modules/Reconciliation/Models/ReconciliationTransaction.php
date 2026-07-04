<?php

namespace App\Modules\Reconciliation\Models;

use App\Core\BaseModel;

class ReconciliationTransaction extends BaseModel
{
    protected $table = 'reconciliation_transactions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'external_order_id',
        'pos_order_id',
        'processor_transaction_id',
        'delivery_platform_order_id',
        'delivery_platform_name',
        'order_date',
        'order_amount',
        'order_currency',
        'pos_amount',
        'processor_amount',
        'delivery_platform_amount',
        'reconciliation_status',
        'match_confidence',
        'discrepancy_type',
        'discrepancy_amount',
        'discrepancy_notes',
        'manually_matched',
        'matched_by',
        'matched_at',
        'match_notes'
    ];

    /**
     * Count transactions by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count transactions by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE restaurant_id = ? AND reconciliation_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Sum discrepancy amount
     */
    public function sumDiscrepancyAmount($restaurantId)
    {
        $sql = "SELECT COALESCE(SUM(ABS(discrepancy_amount)), 0) as total 
                FROM {$this->table} 
                WHERE restaurant_id = ? AND reconciliation_status = 'discrepancy'";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get recent transactions
     */
    public function getRecent($restaurantId, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get paginated transactions
     */
    public function getPaginated($restaurantId, $page, $limit, $status = null, $dateFrom = null, $dateTo = null)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND reconciliation_status = ?";
            $params[] = $status;
        }
        
        if ($dateFrom) {
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT * FROM {$this->table} 
                {$where} 
                ORDER BY created_at DESC 
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
        $sql = "SELECT * FROM {$this->table} 
                WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
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
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count by status and date range
     */
    public function countByStatusAndDateRange($restaurantId, $status, $dateFrom, $dateTo)
    {
        $params = [$restaurantId, $status];
        $where = "WHERE restaurant_id = ? AND reconciliation_status = ?";
        
        if ($dateFrom) {
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Sum amount by date range
     */
    public function sumAmountByDateRange($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COALESCE(SUM(order_amount), 0) as total FROM {$this->table} {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Sum discrepancy amount by date range
     */
    public function sumDiscrepancyAmountByDateRange($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND reconciliation_status = 'discrepancy'";
        
        if ($dateFrom) {
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COALESCE(SUM(ABS(discrepancy_amount)), 0) as total FROM {$this->table} {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get discrepancies by date range
     */
    public function getDiscrepanciesByDateRange($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND reconciliation_status = 'discrepancy'";
        
        if ($dateFrom) {
            $where .= " AND order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND order_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY order_date DESC";
        return $this->db->query($sql, $params)->fetchAll();
    }
}
