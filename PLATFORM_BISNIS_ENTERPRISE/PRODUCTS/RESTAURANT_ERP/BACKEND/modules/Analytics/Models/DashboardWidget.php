<?php

namespace App\Modules\Analytics\Models;

use App\Core\BaseModel;

class DashboardWidget extends BaseModel
{
    protected $table = 'dashboard_widgets';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'dashboard_id',
        'widget_type',
        'widget_name',
        'widget_config',
        'data_source',
        'data_query',
        'position_x',
        'position_y',
        'width',
        'height',
        'refresh_interval',
        'is_active'
    ];

    /**
     * Get by dashboard
     */
    public function getByDashboard($dashboardId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE dashboard_id = ? AND restaurant_id = ? AND is_active = TRUE ORDER BY position_y ASC, position_x ASC";
        return $this->db->query($sql, [$dashboardId, $restaurantId])->fetchAll();
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
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $widgetType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND widget_type = ? AND is_active = TRUE";
        return $this->db->query($sql, [$restaurantId, $widgetType])->fetchAll();
    }
}
