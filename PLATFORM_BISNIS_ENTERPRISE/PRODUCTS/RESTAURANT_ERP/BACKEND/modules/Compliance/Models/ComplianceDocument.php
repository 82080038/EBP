<?php

namespace App\Modules\Compliance\Models;

use App\Core\BaseModel;

class ComplianceDocument extends BaseModel
{
    protected $table = 'compliance_documents';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'document_type',
        'document_name',
        'document_number',
        'issuing_authority',
        'issue_date',
        'expiry_date',
        'is_valid',
        'file_path',
        'file_name',
        'file_size',
        'file_mime_type',
        'alert_days_before_expiry',
        'last_alert_sent_at',
        'status'
    ];

    /**
     * Count expiring
     */
    public function countExpiring($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND is_valid = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get expiring documents
     */
    public function getExpiring($restaurantId, $limit = 5)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND is_valid = TRUE
                ORDER BY expiry_date ASC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $documentType = null, $status = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($documentType) {
            $where .= " AND document_type = ?";
            $params[] = $documentType;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY expiry_date ASC";
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
}
