<?php

namespace Modules\Innovation\Repositories;

use Core\Database;

class InnovationRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all innovation projects for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT ip.*, u1.username as project_lead_name, u2.username as created_by_name
                FROM innovation_projects ip
                LEFT JOIN users u1 ON ip.project_lead = u1.user_id
                LEFT JOIN users u2 ON ip.created_by = u2.user_id
                WHERE ip.tenant_id = :tenant_id 
                AND ip.deleted_at IS NULL
                ORDER BY ip.start_date DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find innovation project by ID
     */
    public function findById($projectId, $tenantId)
    {
        $sql = "SELECT ip.*, u1.username as project_lead_name, u2.username as created_by_name
                FROM innovation_projects ip
                LEFT JOIN users u1 ON ip.project_lead = u1.user_id
                LEFT JOIN users u2 ON ip.created_by = u2.user_id
                WHERE ip.id = :project_id 
                AND ip.tenant_id = :tenant_id 
                AND ip.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['project_id' => $projectId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create innovation project
     */
    public function create($data)
    {
        $sql = "INSERT INTO innovation_projects (tenant_id, project_name, project_description, project_type,
                                               start_date, target_end_date, budget_amount, project_lead,
                                               team_members, project_status, completion_percentage,
                                               created_by, created_at)
                VALUES (:tenant_id, :project_name, :project_description, :project_type,
                        :start_date, :target_end_date, :budget_amount, :project_lead,
                        :team_members, :project_status, :completion_percentage,
                        :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update innovation project
     */
    public function update($projectId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE innovation_projects SET " . implode(', ', $setClause) . " 
                WHERE id = :project_id AND tenant_id = :tenant_id";
        
        $params = array_merge($data, ['project_id' => $projectId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update project progress
     */
    public function updateProgress($projectId, $completionPercentage, $tenantId)
    {
        $status = 'in_progress';
        if ($completionPercentage >= 100) {
            $status = 'completed';
        }
        
        $sql = "UPDATE innovation_projects 
                SET completion_percentage = :completion_percentage, 
                    project_status = :status, 
                    updated_at = NOW() 
                WHERE id = :project_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'project_id' => $projectId,
            'completion_percentage' => $completionPercentage,
            'status' => $status,
            'tenant_id' => $tenantId
        ]);
    }
    
    /**
     * Soft delete innovation project
     */
    public function delete($projectId, $tenantId)
    {
        $sql = "UPDATE innovation_projects 
                SET deleted_at = NOW() 
                WHERE id = :project_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['project_id' => $projectId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get projects by status
     */
    public function getByStatus($tenantId, $status, $limit = 100)
    {
        $sql = "SELECT ip.*, u1.username as project_lead_name
                FROM innovation_projects ip
                LEFT JOIN users u1 ON ip.project_lead = u1.user_id
                WHERE ip.tenant_id = :tenant_id 
                AND ip.project_status = :status
                AND ip.deleted_at IS NULL
                ORDER BY ip.start_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'status' => $status, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get projects by type
     */
    public function getByType($tenantId, $type, $limit = 100)
    {
        $sql = "SELECT ip.*, u1.username as project_lead_name
                FROM innovation_projects ip
                LEFT JOIN users u1 ON ip.project_lead = u1.user_id
                WHERE ip.tenant_id = :tenant_id 
                AND ip.project_type = :type
                AND ip.deleted_at IS NULL
                ORDER BY ip.start_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'type' => $type, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get active projects
     */
    public function getActive($tenantId)
    {
        $sql = "SELECT ip.*, u1.username as project_lead_name
                FROM innovation_projects ip
                LEFT JOIN users u1 ON ip.project_lead = u1.user_id
                WHERE ip.tenant_id = :tenant_id 
                AND ip.project_status = 'in_progress'
                AND ip.deleted_at IS NULL
                ORDER BY ip.start_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Count projects by status
     */
    public function countByStatus($tenantId, $status = null)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM innovation_projects 
                WHERE tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($status !== null) {
            $sql .= " AND project_status = :status";
            $params['status'] = $status;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get innovation ideas
     */
    public function getIdeas($tenantId, $limit = 100)
    {
        $sql = "SELECT ii.*, u.username as submitted_by_name
                FROM innovation_ideas ii
                LEFT JOIN users u ON ii.submitted_by = u.user_id
                WHERE ii.tenant_id = :tenant_id
                AND ii.deleted_at IS NULL
                ORDER BY ii.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get innovation metrics
     */
    public function getMetrics($tenantId, $projectId = null)
    {
        $sql = "SELECT im.* 
                FROM innovation_metrics im
                WHERE im.tenant_id = :tenant_id";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($projectId !== null) {
            $sql .= " AND im.project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        $sql .= " AND im.deleted_at IS NULL ORDER BY im.metric_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
