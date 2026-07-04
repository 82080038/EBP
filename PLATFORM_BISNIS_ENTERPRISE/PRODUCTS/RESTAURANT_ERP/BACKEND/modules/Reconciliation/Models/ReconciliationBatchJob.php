<?php

namespace App\Modules\Reconciliation\Models;

use App\Core\BaseModel;

class ReconciliationBatchJob extends BaseModel
{
    protected $table = 'reconciliation_batch_jobs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'job_type',
        'job_config',
        'job_status',
        'total_processed',
        'total_matched',
        'total_discrepancies',
        'total_errors',
        'started_at',
        'completed_at',
        'error_message',
        'triggered_by',
        'triggered_by_user_id'
    ];

    /**
     * Get paginated batch jobs
     */
    public function getPaginated($restaurantId, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE restaurant_id = ?";
        $totalResult = $this->db->query($countSql, [$restaurantId])->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        $data = $this->db->query($sql, [$restaurantId, $limit, $offset])->fetchAll();
        
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
     * Get recent jobs
     */
    public function getRecent($restaurantId, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }
}
