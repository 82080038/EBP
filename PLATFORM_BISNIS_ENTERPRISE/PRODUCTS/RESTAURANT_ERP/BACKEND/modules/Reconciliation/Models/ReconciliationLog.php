<?php

namespace App\Modules\Reconciliation\Models;

use App\Core\BaseModel;

class ReconciliationLog extends BaseModel
{
    protected $table = 'reconciliation_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'reconciliation_transaction_id',
        'log_type',
        'log_message',
        'log_data',
        'source_type',
        'source_id',
        'action_by'
    ];

    /**
     * Get by transaction ID
     */
    public function getByTransactionId($transactionId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE reconciliation_transaction_id = ? 
                ORDER BY created_at DESC";
        return $this->db->query($sql, [$transactionId])->fetchAll();
    }

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $limit = 100)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get by log type
     */
    public function getByType($restaurantId, $logType, $limit = 50)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND log_type = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $logType, $limit])->fetchAll();
    }
}
