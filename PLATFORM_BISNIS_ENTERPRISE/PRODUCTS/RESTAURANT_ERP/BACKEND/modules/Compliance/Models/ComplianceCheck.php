<?php

namespace App\Modules\Compliance\Models;

use App\Core\BaseModel;

class ComplianceCheck extends BaseModel
{
    protected $table = 'compliance_checks';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'compliance_rule_id',
        'check_date',
        'check_status',
        'check_result',
        'violations_found',
        'violation_details',
        'remediation_required',
        'remediation_deadline',
        'remediation_status',
        'checked_by',
        'checked_at',
        'notes'
    ];

    /**
     * Get recent checks
     */
    public function getRecent($restaurantId, $limit = 10)
    {
        $sql = "SELECT cc.*, cr.rule_name, cr.rule_type 
                FROM {$this->table} cc
                LEFT JOIN compliance_rules cr ON cc.compliance_rule_id = cr.id
                WHERE cc.restaurant_id = ? 
                ORDER BY cc.check_date DESC, cc.created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get paginated checks
     */
    public function getPaginated($restaurantId, $ruleId, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE cc.restaurant_id = ?";
        
        if ($ruleId) {
            $where .= " AND cc.compliance_rule_id = ?";
            $params[] = $ruleId;
        }
        
        if ($status) {
            $where .= " AND cc.check_status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} cc {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT cc.*, cr.rule_name, cr.rule_type 
                FROM {$this->table} cc
                LEFT JOIN compliance_rules cr ON cc.compliance_rule_id = cr.id
                {$where}
                ORDER BY cc.check_date DESC, cc.created_at DESC
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
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }
}
