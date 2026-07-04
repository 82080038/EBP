<?php

namespace App\Modules\Innovation\Services;

use App\Modules\Innovation\Models\InnovationIdea;
use App\Modules\Innovation\Models\InnovationProject;
use App\Modules\Innovation\Models\InnovationMetric;
use App\Core\Database;

class InnovationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get innovation ideas
     */
    public function getIdeas($restaurantId, $category, $status, $page, $limit)
    {
        $ideaModel = new InnovationIdea();
        return $ideaModel->getPaginated($restaurantId, $category, $status, $page, $limit);
    }

    /**
     * Create innovation idea
     */
    public function createIdea($restaurantId, $userId, $data)
    {
        $ideaModel = new InnovationIdea();
        
        $ideaData = [
            'restaurant_id' => $restaurantId,
            'idea_title' => $data->idea_title,
            'idea_description' => $data->idea_description,
            'idea_category' => $data->idea_category,
            'potential_impact' => $data->potential_impact,
            'estimated_cost' => $data->estimated_cost ?? null,
            'estimated_roi' => $data->estimated_roi ?? null,
            'priority_level' => $data->priority_level ?? 'medium',
            'idea_status' => 'submitted',
            'submitted_by' => $userId
        ];
        
        $ideaId = $ideaModel->create($ideaData);
        
        if (!$ideaId) {
            return ['success' => false, 'message' => 'Failed to create idea'];
        }
        
        return ['success' => true, 'message' => 'Idea created', 'idea_id' => $ideaId];
    }

    /**
     * Update idea status
     */
    public function updateIdeaStatus($id, $restaurantId, $data)
    {
        $ideaModel = new InnovationIdea();
        $idea = $ideaModel->findById($id, $restaurantId);
        
        if (!$idea) {
            return ['success' => false, 'message' => 'Idea not found'];
        }
        
        $updateData = [];
        
        if (isset($data->idea_status)) {
            $updateData['idea_status'] = $data->idea_status;
        }
        if (isset($data->assigned_to)) {
            $updateData['assigned_to'] = $data->assigned_to;
        }
        if (isset($data->review_notes)) {
            $updateData['review_notes'] = $data->review_notes;
        }
        if (isset($data->implementation_notes)) {
            $updateData['implementation_notes'] = $data->implementation_notes;
        }
        
        $updated = $ideaModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update idea'];
        }
        
        return ['success' => true, 'message' => 'Idea updated'];
    }

    /**
     * Get innovation projects
     */
    public function getProjects($restaurantId, $status, $type)
    {
        $projectModel = new InnovationProject();
        return $projectModel->getByRestaurant($restaurantId, $status, $type);
    }

    /**
     * Create innovation project
     */
    public function createProject($restaurantId, $userId, $data)
    {
        $projectModel = new InnovationProject();
        
        $projectData = [
            'restaurant_id' => $restaurantId,
            'project_name' => $data->project_name,
            'project_description' => $data->project_description ?? null,
            'project_type' => $data->project_type,
            'start_date' => $data->start_date,
            'target_end_date' => $data->target_end_date,
            'budget_amount' => $data->budget_amount,
            'project_lead' => $data->project_lead,
            'team_members' => json_encode($data->team_members ?? []),
            'project_status' => 'planning',
            'created_by' => $userId
        ];
        
        $projectId = $projectModel->create($projectData);
        
        if (!$projectId) {
            return ['success' => false, 'message' => 'Failed to create project'];
        }
        
        return ['success' => true, 'message' => 'Project created', 'project_id' => $projectId];
    }

    /**
     * Get innovation metrics
     */
    public function getMetrics($restaurantId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $metricModel = new InnovationMetric();
        return $metricModel->getByRestaurant($restaurantId, $metricType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $ideaModel = new InnovationIdea();
        $projectModel = new InnovationProject();
        $metricModel = new InnovationMetric();
        
        // Total ideas
        $totalIdeas = $ideaModel->countByRestaurant($restaurantId);
        
        // Ideas by status
        $submittedIdeas = $ideaModel->countByStatus($restaurantId, 'submitted');
        $approvedIdeas = $ideaModel->countByStatus($restaurantId, 'approved');
        $implementedIdeas = $ideaModel->countByStatus($restaurantId, 'implemented');
        
        // Projects
        $activeProjects = $projectModel->countByStatus($restaurantId, 'in_progress');
        $completedProjects = $projectModel->countByStatus($restaurantId, 'completed');
        
        // Latest metrics
        $latestMetrics = $metricModel->getLatest($restaurantId);
        
        return [
            'total_ideas' => $totalIdeas,
            'submitted_ideas' => $submittedIdeas,
            'approved_ideas' => $approvedIdeas,
            'implemented_ideas' => $implementedIdeas,
            'active_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
            'latest_metrics' => $latestMetrics
        ];
    }
}
