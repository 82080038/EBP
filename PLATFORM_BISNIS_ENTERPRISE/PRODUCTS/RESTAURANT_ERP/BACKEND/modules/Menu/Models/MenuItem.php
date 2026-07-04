<?php

namespace App\Modules\Menu\Models;

use App\Core\BaseModel;

class MenuItem extends BaseModel
{
    protected $table = 'menu_items';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'category_id',
        'item_code',
        'name',
        'description',
        'base_price',
        'cost_price',
        'is_available',
        'available_from',
        'available_until',
        'available_days',
        'image_url',
        'thumbnail_url',
        'display_order',
        'is_featured',
        'is_new',
        'is_vegetarian',
        'is_vegan',
        'is_gluten_free',
        'is_spicy',
        'spice_level',
        'preparation_time',
        'preparation_station',
        'tax_rate',
        'is_active'
    ];

    /**
     * Get paginated items
     */
    public function getPaginated($restaurantId, $categoryId, $isAvailable, $isFeatured, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND is_active = TRUE";
        
        if ($categoryId) {
            $where .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($isAvailable !== null) {
            $where .= " AND is_available = ?";
            $params[] = $isAvailable;
        }
        
        if ($isFeatured !== null) {
            $where .= " AND is_featured = ?";
            $params[] = $isFeatured;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT mi.*, mc.category_name 
                FROM {$this->table} mi
                LEFT JOIN menu_categories mc ON mi.category_id = mc.id
                {$where}
                ORDER BY mc.sort_order ASC, mi.display_order ASC, mi.name ASC
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
        $sql = "SELECT mi.*, mc.category_name 
                FROM {$this->table} mi
                LEFT JOIN menu_categories mc ON mi.category_id = mc.id
                WHERE mi.id = ? AND mi.restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by code
     */
    public function findByCode($itemCode, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE item_code = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$itemCode, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count active
     */
    public function countActive($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE AND is_available = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count featured
     */
    public function countFeatured($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE AND is_featured = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by category
     */
    public function getByCategory($restaurantId, $categoryId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND category_id = ? AND is_active = TRUE AND is_available = TRUE ORDER BY display_order ASC";
        return $this->db->query($sql, [$restaurantId, $categoryId])->fetchAll();
    }
}
