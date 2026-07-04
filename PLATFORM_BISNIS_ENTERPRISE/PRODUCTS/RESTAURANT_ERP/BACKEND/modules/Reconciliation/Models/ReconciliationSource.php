<?php

namespace App\Modules\Reconciliation\Models;

use App\Core\BaseModel;

class ReconciliationSource extends BaseModel
{
    protected $table = 'reconciliation_sources';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'source_type',
        'source_name',
        'source_identifier',
        'api_endpoint',
        'api_key_encrypted',
        'api_secret_encrypted',
        'webhook_url',
        'webhook_secret_encrypted',
        'sync_frequency',
        'last_sync_at',
        'last_sync_status',
        'last_sync_error',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get active by restaurant
     */
    public function getActiveByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = TRUE 
                ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
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
     * Get sync status
     */
    public function getSyncStatus($restaurantId)
    {
        $sql = "SELECT 
                    source_type,
                    source_name,
                    last_sync_at,
                    last_sync_status,
                    is_active
                FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY last_sync_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get performance report
     */
    public function getPerformanceReport($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE rs.restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND rt.order_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND rt.order_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT 
                    rs.source_type,
                    rs.source_name,
                    COUNT(rt.id) as transaction_count,
                    SUM(rt.order_amount) as total_amount,
                    SUM(CASE WHEN rt.reconciliation_status = 'matched' THEN 1 ELSE 0 END) as matched_count,
                    SUM(CASE WHEN rt.reconciliation_status = 'discrepancy' THEN 1 ELSE 0 END) as discrepancy_count
                FROM {$this->table} rs
                LEFT JOIN reconciliation_transactions rt ON rs.restaurant_id = rt.restaurant_id
                {$where}
                GROUP BY rs.id, rs.source_type, rs.source_name
                ORDER BY transaction_count DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
}
