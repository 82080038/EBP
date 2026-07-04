<?php

namespace App\Modules\Inventory\Models;

use App\Core\BaseModel;

class StockTransfer extends BaseModel
{
    protected $table = 'stock_transfers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'transfer_number',
        'transfer_type',
        'from_location_id',
        'to_location_id',
        'from_branch_id',
        'to_branch_id',
        'transfer_status',
        'transfer_date',
        'received_at',
        'created_by',
        'received_by',
        'notes'
    ];

    /**
     * Get paginated transfers
     */
    public function getPaginated($restaurantId, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND transfer_status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT st.*, u1.username as created_by_name, u2.username as received_by_name 
                FROM {$this->table} st
                LEFT JOIN users u1 ON st.created_by = u1.id
                LEFT JOIN users u2 ON st.received_by = u2.id
                {$where}
                ORDER BY st.transfer_date DESC
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
     * Find by transfer number
     */
    public function findByTransferNumber($transferNumber, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE transfer_number = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$transferNumber, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by status
     */
    public function getByStatus($restaurantId, $status)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND transfer_status = ? ORDER BY transfer_date DESC";
        return $this->db->query($sql, [$restaurantId, $status])->fetchAll();
    }

    /**
     * Get transfer items
     */
    public function getTransferItems($transferId)
    {
        $sql = "SELECT sti.*, ii.item_name, ii.item_code 
                FROM stock_transfer_items sti
                LEFT JOIN inventory_items ii ON sti.inventory_item_id = ii.id
                WHERE sti.stock_transfer_id = ?";
        return $this->db->query($sql, [$transferId])->fetchAll();
    }
}
