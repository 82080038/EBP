<?php

namespace App\Modules\Security\Models;

use App\Core\BaseModel;

class SecurityIncident extends BaseModel
{
    protected $table = 'security_incidents';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'incident_type',
        'incident_severity',
        'incident_title',
        'incident_description',
        'detected_at',
        'started_at',
        'resolved_at',
        'affected_users',
        'affected_data',
        'impact_assessment',
        'response_actions',
        'resolved_by',
        'resolution_notes',
        'incident_status',
        'incident_data'
    ];

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND incident_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by status
     */
    public function getByStatus($restaurantId, $status)
    {
        $sql = "SELECT si.*, u.username, u.full_name as resolved_by_name 
                FROM {$this->table} si
                LEFT JOIN users u ON si.resolved_by = u.id
                WHERE si.restaurant_id = ? AND si.incident_status = ?
                ORDER BY si.detected_at DESC";
        return $this->db->query($sql, [$restaurantId, $status])->fetchAll();
    }

    /**
     * Get paginated incidents
     */
    public function getPaginated($restaurantId, $incidentType, $incidentStatus, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($incidentType) {
            $where .= " AND incident_type = ?";
            $params[] = $incidentType;
        }
        
        if ($incidentStatus) {
            $where .= " AND incident_status = ?";
            $params[] = $incidentStatus;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT si.*, u.username, u.full_name as resolved_by_name 
                FROM {$this->table} si
                LEFT JOIN users u ON si.resolved_by = u.id
                {$where}
                ORDER BY si.detected_at DESC
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
}
