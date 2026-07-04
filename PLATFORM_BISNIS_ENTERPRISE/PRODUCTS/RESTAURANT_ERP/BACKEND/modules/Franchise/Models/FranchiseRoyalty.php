<?php

namespace App\Modules\Franchise\Models;

use App\Core\BaseModel;

class FranchiseRoyalty extends BaseModel
{
    protected $table = 'franchise_royalties';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'franchisee_id',
        'agreement_id',
        'royalty_period_start',
        'royalty_period_end',
        'gross_revenue',
        'royalty_amount',
        'marketing_fee_amount',
        'total_due',
        'payment_status',
        'payment_date',
        'payment_reference',
        'notes'
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
            $where .= " AND payment_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT fr.*, f.franchisee_name, fa.agreement_number 
                FROM {$this->table} fr
                LEFT JOIN franchisees f ON fr.franchisee_id = f.id
                LEFT JOIN franchise_agreements fa ON fr.agreement_id = fa.id
                {$where}
                ORDER BY fr.royalty_period_start DESC";
        
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
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND payment_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get total due
     */
    public function getTotalDue($restaurantId)
    {
        $sql = "SELECT SUM(total_due) as total FROM {$this->table} WHERE restaurant_id = ? AND payment_status = 'pending'";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get by franchisee
     */
    public function getByFranchisee($franchiseeId, $restaurantId, $limit = 12)
    {
        $sql = "SELECT * FROM {$this->table} WHERE franchisee_id = ? AND restaurant_id = ? ORDER BY royalty_period_start DESC LIMIT ?";
        return $this->db->query($sql, [$franchiseeId, $restaurantId, $limit])->fetchAll();
    }

    /**
     * Get overdue
     */
    public function getOverdue($restaurantId)
    {
        $sql = "SELECT fr.*, f.franchisee_name 
                FROM {$this->table} fr
                LEFT JOIN franchisees f ON fr.franchisee_id = f.id
                WHERE fr.restaurant_id = ? AND fr.payment_status = 'pending'
                AND fr.royalty_period_end < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                ORDER BY fr.royalty_period_end ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($id, $status, $paymentDate = null, $paymentReference = null)
    {
        $updateData = ['payment_status' => $status];
        
        if ($paymentDate) {
            $updateData['payment_date'] = $paymentDate;
        }
        
        if ($paymentReference) {
            $updateData['payment_reference'] = $paymentReference;
        }
        
        return $this->update($id, $updateData);
    }
}
