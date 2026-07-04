<?php

namespace App\Modules\Supplier\Models;

use App\Core\BaseModel;

class SupplierPerformance extends BaseModel
{
    protected $table = 'supplier_performance';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'supplier_id',
        'evaluation_period_start',
        'evaluation_period_end',
        'on_time_delivery_rate',
        'quality_score',
        'defect_rate',
        'response_time_hours',
        'order_accuracy_rate',
        'total_orders',
        'total_value',
        'overall_score',
        'performance_rating'
    ];

    /**
     * Get by supplier
     */
    public function getBySupplier($supplierId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_id = ? AND restaurant_id = ? ORDER BY evaluation_period_start DESC";
        return $this->db->query($sql, [$supplierId, $restaurantId])->fetchAll();
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
     * Get latest
     */
    public function getLatest($supplierId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_id = ? AND restaurant_id = ? ORDER BY evaluation_period_start DESC LIMIT 1";
        $result = $this->db->query($sql, [$supplierId, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get top performers
     */
    public function getTopPerformers($restaurantId, $limit = 10)
    {
        $sql = "SELECT sp.*, s.supplier_name 
                FROM {$this->table} sp
                LEFT JOIN suppliers s ON sp.supplier_id = s.id
                WHERE sp.restaurant_id = ?
                ORDER BY sp.overall_score DESC
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get by rating
     */
    public function getByRating($restaurantId, $rating)
    {
        $sql = "SELECT sp.*, s.supplier_name 
                FROM {$this->table} sp
                LEFT JOIN suppliers s ON sp.supplier_id = s.id
                WHERE sp.restaurant_id = ? AND sp.performance_rating = ?
                ORDER BY sp.overall_score DESC";
        return $this->db->query($sql, [$restaurantId, $rating])->fetchAll();
    }
}
