<?php

namespace App\Modules\Customer\Models;

use App\Core\BaseModel;

class Customer extends BaseModel
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'preferred_language',
        'dietary_preferences',
        'favorite_items',
        'is_active',
        'is_vip',
        'email_subscribed',
        'sms_subscribed',
        'last_visit_at'
    ];

    /**
     * Get paginated customers
     */
    public function getPaginated($restaurantId, $search, $isVip, $tagId, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND is_active = TRUE";
        
        if ($search) {
            $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($isVip !== null) {
            $where .= " AND is_vip = ?";
            $params[] = $isVip;
        }
        
        if ($tagId) {
            $where .= " AND id IN (SELECT customer_id FROM customer_tag_assignments WHERE tag_id = ?)";
            $params[] = $tagId;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT c.*, GROUP_CONCAT(ct.tag_name) as tags
                FROM {$this->table} c
                LEFT JOIN customer_tag_assignments cta ON c.id = cta.customer_id
                LEFT JOIN customer_tags ct ON cta.tag_id = ct.id AND ct.is_active = TRUE
                {$where}
                GROUP BY c.id
                ORDER BY c.last_visit_at DESC, c.created_at DESC
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
     * Find by email
     */
    public function findByEmail($email, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$email, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by phone
     */
    public function findByPhone($phone, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE phone = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$phone, $restaurantId])->fetch();
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
     * Count VIP
     */
    public function countVip($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_vip = TRUE AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count active (visited in last 30 days)
     */
    public function countActive($restaurantId)
    {
        $sql = "SELECT COUNT(DISTINCT c.id) as count 
                FROM {$this->table} c
                INNER JOIN customer_visits cv ON c.id = cv.customer_id
                WHERE c.restaurant_id = ? AND c.is_active = TRUE 
                AND cv.visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
