<?php

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\Models\DashboardConfiguration;
use App\Modules\Analytics\Models\DashboardWidget;
use App\Modules\Analytics\Models\KpiDefinition;
use App\Modules\Analytics\Models\KpiValue;
use App\Modules\Analytics\Models\AlertRule;
use App\Core\Database;

class AnalyticsService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get dashboards
     */
    public function getDashboards($restaurantId, $userId)
    {
        $dashboardModel = new DashboardConfiguration();
        return $dashboardModel->getByRestaurant($restaurantId, $userId);
    }

    /**
     * Create dashboard
     */
    public function createDashboard($restaurantId, $userId, $data)
    {
        $dashboardModel = new DashboardConfiguration();
        
        $dashboardData = [
            'restaurant_id' => $restaurantId,
            'user_id' => $userId,
            'dashboard_name' => $data->dashboard_name,
            'dashboard_description' => $data->dashboard_description ?? null,
            'layout_config' => json_encode($data->layout_config ?? []),
            'is_default' => $data->is_default ?? false,
            'is_public' => $data->is_public ?? false,
            'is_active' => true
        ];
        
        $dashboardId = $dashboardModel->create($dashboardData);
        
        if (!$dashboardId) {
            return ['success' => false, 'message' => 'Failed to create dashboard'];
        }
        
        return ['success' => true, 'message' => 'Dashboard created', 'dashboard_id' => $dashboardId];
    }

    /**
     * Get dashboard widgets
     */
    public function getDashboardWidgets($dashboardId, $restaurantId)
    {
        $widgetModel = new DashboardWidget();
        return $widgetModel->getByDashboard($dashboardId, $restaurantId);
    }

    /**
     * Create widget
     */
    public function createWidget($restaurantId, $data)
    {
        $widgetModel = new DashboardWidget();
        
        $widgetData = [
            'restaurant_id' => $restaurantId,
            'dashboard_id' => $data->dashboard_id ?? null,
            'widget_type' => $data->widget_type,
            'widget_name' => $data->widget_name,
            'widget_config' => json_encode($data->widget_config ?? []),
            'data_source' => $data->data_source ?? null,
            'data_query' => $data->data_query ?? null,
            'position_x' => $data->position_x ?? 0,
            'position_y' => $data->position_y ?? 0,
            'width' => $data->width ?? 4,
            'height' => $data->height ?? 3,
            'refresh_interval' => $data->refresh_interval ?? 300,
            'is_active' => true
        ];
        
        $widgetId = $widgetModel->create($widgetData);
        
        if (!$widgetId) {
            return ['success' => false, 'message' => 'Failed to create widget'];
        }
        
        return ['success' => true, 'message' => 'Widget created', 'widget_id' => $widgetId];
    }

    /**
     * Get KPI definitions
     */
    public function getKpiDefinitions($restaurantId)
    {
        $kpiModel = new KpiDefinition();
        return $kpiModel->getByRestaurant($restaurantId);
    }

    /**
     * Create KPI definition
     */
    public function createKpiDefinition($restaurantId, $data)
    {
        $kpiModel = new KpiDefinition();
        
        $kpiData = [
            'restaurant_id' => $restaurantId,
            'kpi_code' => $data->kpi_code,
            'kpi_name' => $data->kpi_name,
            'kpi_description' => $data->kpi_description ?? null,
            'kpi_type' => $data->kpi_type,
            'calculation_formula' => $data->calculation_formula ?? null,
            'data_source_table' => $data->data_source_table ?? null,
            'data_source_field' => $data->data_source_field ?? null,
            'target_value' => $data->target_value ?? null,
            'target_comparison' => $data->target_comparison ?? null,
            'unit' => $data->unit ?? null,
            'decimal_places' => $data->decimal_places ?? 2,
            'icon' => $data->icon ?? null,
            'color' => $data->color ?? null,
            'is_active' => true
        ];
        
        $kpiId = $kpiModel->create($kpiData);
        
        if (!$kpiId) {
            return ['success' => false, 'message' => 'Failed to create KPI definition'];
        }
        
        return ['success' => true, 'message' => 'KPI definition created', 'kpi_id' => $kpiId];
    }

    /**
     * Get KPI values
     */
    public function getKpiValues($kpiId, $restaurantId, $periodType, $limit)
    {
        $kpiValueModel = new KpiValue();
        return $kpiValueModel->getByKpi($kpiId, $restaurantId, $periodType, $limit);
    }

    /**
     * Get alert rules
     */
    public function getAlertRules($restaurantId)
    {
        $alertRuleModel = new AlertRule();
        return $alertRuleModel->getByRestaurant($restaurantId);
    }

    /**
     * Create alert rule
     */
    public function createAlertRule($restaurantId, $data)
    {
        $alertRuleModel = new AlertRule();
        
        $alertData = [
            'restaurant_id' => $restaurantId,
            'alert_name' => $data->alert_name,
            'alert_description' => $data->alert_description ?? null,
            'kpi_id' => $data->kpi_id,
            'condition_type' => $data->condition_type,
            'threshold_value' => $data->threshold_value,
            'notification_channels' => json_encode($data->notification_channels ?? []),
            'notification_recipients' => json_encode($data->notification_recipients ?? []),
            'is_active' => true
        ];
        
        $alertId = $alertRuleModel->create($alertData);
        
        if (!$alertId) {
            return ['success' => false, 'message' => 'Failed to create alert rule'];
        }
        
        return ['success' => true, 'message' => 'Alert rule created', 'alert_id' => $alertId];
    }

    /**
     * Get alert history
     */
    public function getAlertHistory($restaurantId, $status, $page, $limit)
    {
        $alertRuleModel = new AlertRule();
        return $alertRuleModel->getHistory($restaurantId, $status, $page, $limit);
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $kpiModel = new KpiDefinition();
        $kpiValueModel = new KpiValue();
        
        // Get all active KPIs
        $kpis = $kpiModel->getActive($restaurantId);
        
        $summary = [];
        
        foreach ($kpis as $kpi) {
            // Get latest value
            $latestValue = $kpiValueModel->getLatest($kpi['id'], $restaurantId);
            
            $summary[] = [
                'kpi_id' => $kpi['id'],
                'kpi_code' => $kpi['kpi_code'],
                'kpi_name' => $kpi['kpi_name'],
                'kpi_type' => $kpi['kpi_type'],
                'unit' => $kpi['unit'],
                'icon' => $kpi['icon'],
                'color' => $kpi['color'],
                'current_value' => $latestValue['kpi_value'] ?? 0,
                'previous_value' => $latestValue['previous_value'] ?? 0,
                'percentage_change' => $latestValue['percentage_change'] ?? 0,
                'target_value' => $kpi['target_value']
            ];
        }
        
        return $summary;
    }
}
