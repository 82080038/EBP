<?php

namespace App\Modules\Segment\Services;

use App\Modules\Segment\Models\SegmentConfiguration;
use App\Modules\Segment\Models\SegmentWorkflow;
use App\Modules\Segment\Models\SegmentTemplate;
use App\Core\Database;

class SegmentService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get segment configuration
     */
    public function getConfiguration($restaurantId)
    {
        $configModel = new SegmentConfiguration();
        return $configModel->getByRestaurant($restaurantId);
    }

    /**
     * Create segment configuration
     */
    public function createConfiguration($restaurantId, $userId, $data)
    {
        $configModel = new SegmentConfiguration();
        
        $configData = [
            'restaurant_id' => $restaurantId,
            'segment_type' => $data->segment_type,
            'segment_name' => $data->segment_name,
            'segment_description' => $data->segment_description ?? null,
            'segment_config' => json_encode($data->segment_config),
            'enabled_features' => json_encode($data->enabled_features ?? []),
            'is_active' => true,
            'created_by' => $userId
        ];
        
        $configId = $configModel->create($configData);
        
        if (!$configId) {
            return ['success' => false, 'message' => 'Failed to create segment configuration'];
        }
        
        return ['success' => true, 'message' => 'Segment configuration created', 'configuration_id' => $configId];
    }

    /**
     * Get segment workflows
     */
    public function getWorkflows($restaurantId, $type)
    {
        $workflowModel = new SegmentWorkflow();
        return $workflowModel->getByRestaurant($restaurantId, $type);
    }

    /**
     * Create segment workflow
     */
    public function createWorkflow($restaurantId, $userId, $data)
    {
        $workflowModel = new SegmentWorkflow();
        
        $workflowData = [
            'restaurant_id' => $restaurantId,
            'segment_configuration_id' => $data->segment_configuration_id,
            'workflow_name' => $data->workflow_name,
            'workflow_type' => $data->workflow_type,
            'workflow_steps' => json_encode($data->workflow_steps),
            'conditions' => json_encode($data->conditions ?? []),
            'is_active' => true,
            'created_by' => $userId
        ];
        
        $workflowId = $workflowModel->create($workflowData);
        
        if (!$workflowId) {
            return ['success' => false, 'message' => 'Failed to create workflow'];
        }
        
        return ['success' => true, 'message' => 'Workflow created', 'workflow_id' => $workflowId];
    }

    /**
     * Get segment templates
     */
    public function getTemplates($segmentType)
    {
        $templateModel = new SegmentTemplate();
        return $templateModel->getByType($segmentType);
    }

    /**
     * Apply template to restaurant
     */
    public function applyTemplate($restaurantId, $userId, $data)
    {
        $templateModel = new SegmentTemplate();
        $template = $templateModel->findById($data->template_id);
        
        if (!$template) {
            return ['success' => false, 'message' => 'Template not found'];
        }
        
        // Create configuration from template
        $configModel = new SegmentConfiguration();
        $configData = [
            'restaurant_id' => $restaurantId,
            'segment_type' => $template['segment_type'],
            'segment_name' => $data->segment_name ?? $template['template_name'],
            'segment_description' => $template['template_description'],
            'segment_config' => $template['default_config'],
            'enabled_features' => $template['default_features'],
            'is_active' => true,
            'created_by' => $userId
        ];
        
        $configId = $configModel->create($configData);
        
        if (!$configId) {
            return ['success' => false, 'message' => 'Failed to apply template'];
        }
        
        return ['success' => true, 'message' => 'Template applied', 'configuration_id' => $configId];
    }

    /**
     * Get analytics
     */
    public function getAnalytics($restaurantId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        $where .= " AND metric_type = ?";
        $params[] = $metricType;
        
        if ($dateFrom) {
            $where .= " AND metric_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND metric_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM segment_analytics {$where} ORDER BY metric_date DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $configModel = new SegmentConfiguration();
        $workflowModel = new SegmentWorkflow();
        
        // Active configuration
        $configuration = $configModel->getActive($restaurantId);
        
        // Active workflows
        $activeWorkflows = $workflowModel->countActive($restaurantId);
        
        // Latest analytics
        $latestAnalytics = $this->getAnalytics($restaurantId, 'monthly', null, null, 1);
        
        return [
            'configuration' => $configuration,
            'active_workflows' => $activeWorkflows,
            'latest_analytics' => $latestAnalytics[0] ?? null
        ];
    }
}
