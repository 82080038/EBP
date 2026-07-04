<?php

namespace App\Modules\Segment\Models;

use App\Core\BaseModel;

class SegmentTemplate extends BaseModel
{
    protected $table = 'segment_templates';
    protected $primaryKey = 'id';
    protected $fillable = [
        'template_name',
        'segment_type',
        'default_config',
        'default_features',
        'default_workflows',
        'template_description',
        'is_active'
    ];

    /**
     * Get by type
     */
    public function getByType($segmentType)
    {
        $params = [];
        $where = "WHERE is_active = TRUE";
        
        if ($segmentType) {
            $where .= " AND segment_type = ?";
            $params[] = $segmentType;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY template_name ASC";
        return $this->db->query($sql, $params)->fetchAll();
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
     * Get all active
     */
    public function getActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY segment_type, template_name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get by segment type
     */
    public function getBySegmentType($segmentType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE segment_type = ? AND is_active = TRUE ORDER BY template_name ASC";
        return $this->db->query($sql, [$segmentType])->fetchAll();
    }
}
