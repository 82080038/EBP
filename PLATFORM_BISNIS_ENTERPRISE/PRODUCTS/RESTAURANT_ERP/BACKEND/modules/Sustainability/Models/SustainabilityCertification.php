<?php

namespace App\Modules\Sustainability\Models;

use App\Core\BaseModel;

class SustainabilityCertification extends BaseModel
{
    protected $table = 'sustainability_certifications';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'certification_name',
        'certification_type',
        'issuing_organization',
        'issue_date',
        'expiry_date',
        'certification_status',
        'certificate_number',
        'certificate_document_url',
        'notes',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $certType, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($certType) {
            $where .= " AND certification_type = ?";
            $params[] = $certType;
        }
        
        if ($status) {
            $where .= " AND certification_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT sc.*, u.username as created_by_name 
                FROM {$this->table} sc
                LEFT JOIN users u ON sc.created_by = u.id
                {$where}
                ORDER BY sc.issue_date DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
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
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND certification_status = 'active' ORDER BY expiry_date ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get expiring soon
     */
    public function getExpiringSoon($restaurantId, $days = 30)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND certification_status = 'active'
                AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY expiry_date ASC";
        return $this->db->query($sql, [$restaurantId, $days])->fetchAll();
    }

    /**
     * Get expired
     */
    public function getExpired($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND expiry_date < CURDATE()
                ORDER BY expiry_date DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND certification_status = 'active'";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Update expired status
     */
    public function updateExpiredStatus($restaurantId)
    {
        $sql = "UPDATE {$this->table} 
                SET certification_status = 'expired' 
                WHERE restaurant_id = ? AND expiry_date < CURDATE() AND certification_status = 'active'";
        return $this->db->query($sql, [$restaurantId]);
    }
}
