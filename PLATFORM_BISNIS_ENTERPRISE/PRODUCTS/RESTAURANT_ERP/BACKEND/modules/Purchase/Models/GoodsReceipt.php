<?php

namespace App\Modules\Purchase\Models;

use App\Core\BaseModel;

class GoodsReceipt extends BaseModel
{
    protected $table = 'goods_receipts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'purchase_order_id',
        'supplier_id',
        'receipt_number',
        'receipt_date',
        'delivery_note_number',
        'carrier_name',
        'vehicle_number',
        'receipt_status',
        'receiving_notes',
        'internal_notes',
        'received_by'
    ];

    /**
     * Get by purchase order
     */
    public function getByPurchaseOrder($purchaseOrderId, $restaurantId)
    {
        $sql = "SELECT gr.*, u.username as received_by_name 
                FROM {$this->table} gr
                LEFT JOIN users u ON gr.received_by = u.id
                WHERE gr.purchase_order_id = ? AND gr.restaurant_id = ?
                ORDER BY gr.receipt_date DESC";
        return $this->db->query($sql, [$purchaseOrderId, $restaurantId])->fetchAll();
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
     * Find by number
     */
    public function findByNumber($receiptNumber, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE receipt_number = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$receiptNumber, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get items
     */
    public function getItems($goodsReceiptId, $restaurantId)
    {
        $sql = "SELECT gri.*, ii.item_name, ii.item_code 
                FROM goods_receipt_items gri
                LEFT JOIN inventory_items ii ON gri.inventory_item_id = ii.id
                WHERE gri.goods_receipt_id = ? AND gri.restaurant_id = ?";
        return $this->db->query($sql, [$goodsReceiptId, $restaurantId])->fetchAll();
    }

    /**
     * Get by date range
     */
    public function getByDateRange($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND receipt_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND receipt_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY receipt_date DESC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
