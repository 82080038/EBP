<?php

namespace App\Modules\Supplier\Models;

use App\Core\BaseModel;

class Supplier extends BaseModel
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'supplier_code',
        'supplier_name',
        'supplier_type',
        'contact_person',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_id',
        'business_license',
        'payment_terms',
        'payment_method',
        'bank_account',
        'is_active',
        'is_preferred',
        'notes'
    ];

    /**
     * Get paginated suppliers
     */
    public function getPaginated($restaurantId, $type, $isActive, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($type) {
            $where .= " AND supplier_type = ?";
            $params[] = $type;
        }
        
        if ($isActive !== null) {
            $where .= " AND is_active = ?";
            $params[] = $isActive;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY is_preferred DESC, supplier_name ASC LIMIT ? OFFSET ?";
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
     * Find by code
     */
    public function findByCode($supplierCode, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_code = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$supplierCode, $restaurantId])->fetch();
        return $result ?: null;
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

    /**
     * Count active
     */
    public function countActive($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count preferred
     */
    public function countPreferred($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_preferred = TRUE AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $supplierType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND supplier_type = ? AND is_active = TRUE ORDER BY supplier_name ASC";
        return $this->db->query($sql, [$restaurantId, $supplierType])->fetchAll();
    }
}
