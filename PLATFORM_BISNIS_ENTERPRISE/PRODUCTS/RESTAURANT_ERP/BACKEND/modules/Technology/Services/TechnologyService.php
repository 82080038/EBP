<?php

namespace App\Modules\Technology\Services;

use App\Modules\Technology\Models\TechnologyIntegration;
use App\Modules\Technology\Models\RoboticsDevice;
use App\Modules\Technology\Models\AutomationWorkflow;
use App\Core\Database;

class TechnologyService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get technology integrations
     */
    public function getIntegrations($restaurantId, $type, $status)
    {
        $integrationModel = new TechnologyIntegration();
        return $integrationModel->getByRestaurant($restaurantId, $type, $status);
    }

    /**
     * Create technology integration
     */
    public function createIntegration($restaurantId, $userId, $data)
    {
        $integrationModel = new TechnologyIntegration();
        
        $integrationData = [
            'restaurant_id' => $restaurantId,
            'integration_name' => $data->integration_name,
            'integration_type' => $data->integration_type,
            'integration_category' => $data->integration_category ?? null,
            'provider_name' => $data->provider_name ?? null,
            'provider_contact' => $data->provider_contact ?? null,
            'api_endpoint' => $data->api_endpoint ?? null,
            'api_key' => $data->api_key ?? null,
            'configuration' => json_encode($data->configuration ?? []),
            'integration_status' => 'disconnected',
            'created_by' => $userId
        ];
        
        $integrationId = $integrationModel->create($integrationData);
        
        if (!$integrationId) {
            return ['success' => false, 'message' => 'Failed to create integration'];
        }
        
        return ['success' => true, 'message' => 'Integration created', 'integration_id' => $integrationId];
    }

    /**
     * Get robotics devices
     */
    public function getRoboticsDevices($restaurantId, $integrationId, $status)
    {
        $deviceModel = new RoboticsDevice();
        return $deviceModel->getByRestaurant($restaurantId, $integrationId, $status);
    }

    /**
     * Get automation workflows
     */
    public function getAutomationWorkflows($restaurantId, $type, $status)
    {
        $workflowModel = new AutomationWorkflow();
        return $workflowModel->getByRestaurant($restaurantId, $type, $status);
    }

    /**
     * Create automation workflow
     */
    public function createAutomationWorkflow($restaurantId, $userId, $data)
    {
        $workflowModel = new AutomationWorkflow();
        
        $workflowData = [
            'restaurant_id' => $restaurantId,
            'workflow_name' => $data->workflow_name,
            'workflow_description' => $data->workflow_description ?? null,
            'workflow_type' => $data->workflow_type,
            'trigger_type' => $data->trigger_type,
            'trigger_config' => json_encode($data->trigger_config),
            'actions' => json_encode($data->actions),
            'conditions' => json_encode($data->conditions ?? []),
            'workflow_status' => 'draft',
            'created_by' => $userId
        ];
        
        $workflowId = $workflowModel->create($workflowData);
        
        if (!$workflowId) {
            return ['success' => false, 'message' => 'Failed to create workflow'];
        }
        
        return ['success' => true, 'message' => 'Workflow created', 'workflow_id' => $workflowId];
    }

    /**
     * Get performance
     */
    public function getPerformance($restaurantId, $integrationId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($integrationId) {
            $where .= " AND integration_id = ?";
            $params[] = $integrationId;
        }
        
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
        
        $sql = "SELECT * FROM technology_performance {$where} ORDER BY metric_date DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $integrationModel = new TechnologyIntegration();
        $deviceModel = new RoboticsDevice();
        $workflowModel = new AutomationWorkflow();
        
        // Active integrations
        $activeIntegrations = $integrationModel->countByStatus($restaurantId, 'active');
        
        // Active devices
        $activeDevices = $deviceModel->countByStatus($restaurantId, 'busy');
        
        // Active workflows
        $activeWorkflows = $workflowModel->countByStatus($restaurantId, 'active');
        
        // Latest performance
        $latestPerformance = $this->getPerformance($restaurantId, null, 'monthly', null, null, 1);
        
        return [
            'active_integrations' => $activeIntegrations,
            'active_devices' => $activeDevices,
            'active_workflows' => $activeWorkflows,
            'latest_performance' => $latestPerformance[0] ?? null
        ];
    }
}
