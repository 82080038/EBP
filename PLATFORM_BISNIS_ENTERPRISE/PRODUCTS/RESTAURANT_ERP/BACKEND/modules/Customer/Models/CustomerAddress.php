<?php

namespace App\Modules\Customer\Models;

use App\Core\BaseModel;

class CustomerAddress extends BaseModel
{
    protected $table = 'customer_addresses';
    protected $primaryKey = 'id';
    protected $fillable = [
        'customer_id',
        'address_type',
        'address_label',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default',
        'delivery_notes'
    ];

    /**
     * Get by customer
     */
    public function getByCustomer($customerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? ORDER BY is_default DESC, created_at DESC";
        return $this->db->query($sql, [$customerId])->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }

    /**
     * Get default address
     */
    public function getDefault($customerId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? AND is_default = TRUE LIMIT 1";
        $result = $this->db->query($sql, [$customerId])->fetch();
        return $result ?: null;
    }

    /**
     * Set as default
     */
    public function setAsDefault($id, $customerId)
    {
        // Remove default from all addresses
        $sql = "UPDATE {$this->table} SET is_default = FALSE WHERE customer_id = ?";
        $this->db->query($sql, [$customerId]);
        
        // Set this as default
        return $this->update($id, ['is_default' => true]);
    }
}
