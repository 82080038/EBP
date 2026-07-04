<?php

namespace App\Modules\Franchise\Models;

use App\Core\BaseModel;

class FranchiseAgreement extends BaseModel
{
    protected $table = 'franchise_agreements';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'franchisee_id',
        'agreement_number',
        'agreement_type',
        'start_date',
        'end_date',
        'territory_description',
        'territory_exclusive',
        'franchise_fee',
        'royalty_rate',
        'marketing_fee_rate',
        'agreement_terms',
        'agreement_status',
        'agreement_document_url',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $franchiseeId, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($franchiseeId) {
            $where .= " AND franchisee_id = ?";
            $params[] = $franchiseeId;
        }
        
        if ($status) {
            $where .= " AND agreement_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT fa.*, f.franchisee_name, u.username as created_by_name 
                FROM {$this->table} fa
                LEFT JOIN franchisees f ON fa.franchisee_id = f.id
                LEFT JOIN users u ON fa.created_by = u.id
                {$where}
                ORDER BY fa.start_date DESC";
        
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
     * Find by number
     */
    public function findByNumber($agreementNumber, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE agreement_number = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$agreementNumber, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND agreement_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get expiring soon
     */
    public function getExpiringSoon($restaurantId, $days = 90)
    {
        $sql = "SELECT fa.*, f.franchisee_name 
                FROM {$this->table} fa
                LEFT JOIN franchisees f ON fa.franchisee_id = f.id
                WHERE fa.restaurant_id = ? AND fa.agreement_status = 'active'
                AND fa.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY fa.end_date ASC";
        return $this->db->query($sql, [$restaurantId, $days])->fetchAll();
    }

    /**
     * Get by franchisee
     */
    public function getByFranchisee($franchiseeId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE franchisee_id = ? AND restaurant_id = ? ORDER BY start_date DESC";
        return $this->db->query($sql, [$franchiseeId, $restaurantId])->fetchAll();
    }
}
