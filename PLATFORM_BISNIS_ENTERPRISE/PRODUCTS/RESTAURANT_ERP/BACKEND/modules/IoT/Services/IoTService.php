<?php

namespace App\Modules\IoT\Services;

use App\Modules\IoT\Models\IoTDevice;
use App\Modules\IoT\Models\IoTDeviceReading;
use App\Modules\IoT\Models\SmartAutomation;
use App\Core\Database;

class IoTService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get IoT devices
     */
    public function getDevices($restaurantId, $deviceType, $status)
    {
        $deviceModel = new IoTDevice();
        return $deviceModel->getByRestaurant($restaurantId, $deviceType, $status);
    }

    /**
     * Get single device
     */
    public function getDevice($id, $restaurantId)
    {
        $deviceModel = new IoTDevice();
        $device = $deviceModel->findById($id, $restaurantId);
        
        if ($device) {
            // Get latest readings
            $readingModel = new IoTDeviceReading();
            $device['latest_readings'] = $readingModel->getLatestByDevice($id, $restaurantId, 5);
        }
        
        return $device;
    }

    /**
     * Create device
     */
    public function createDevice($restaurantId, $data)
    {
        $deviceModel = new IoTDevice();
        
        $deviceData = [
            'restaurant_id' => $restaurantId,
            'device_name' => $data->device_name,
            'device_type' => $data->device_type,
            'device_category' => $data->device_category ?? null,
            'device_serial' => $data->device_serial ?? null,
            'device_model' => $data->device_model ?? null,
            'manufacturer' => $data->manufacturer ?? null,
            'connection_type' => $data->connection_type,
            'ip_address' => $data->ip_address ?? null,
            'mac_address' => $data->mac_address ?? null,
            'location' => $data->location ?? null,
            'installation_date' => $data->installation_date ?? null,
            'device_status' => 'offline',
            'configuration' => json_encode($data->configuration ?? []),
            'notes' => $data->notes ?? null
        ];
        
        $deviceId = $deviceModel->create($deviceData);
        
        if (!$deviceId) {
            return ['success' => false, 'message' => 'Failed to create device'];
        }
        
        return ['success' => true, 'message' => 'Device created', 'device_id' => $deviceId];
    }

    /**
     * Update device
     */
    public function updateDevice($id, $restaurantId, $data)
    {
        $deviceModel = new IoTDevice();
        $device = $deviceModel->findById($id, $restaurantId);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }
        
        $updateData = [];
        
        if (isset($data->device_name)) {
            $updateData['device_name'] = $data->device_name;
        }
        if (isset($data->location)) {
            $updateData['location'] = $data->location;
        }
        if (isset($data->device_status)) {
            $updateData['device_status'] = $data->device_status;
        }
        if (isset($data->configuration)) {
            $updateData['configuration'] = json_encode($data->configuration);
        }
        
        $updated = $deviceModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update device'];
        }
        
        return ['success' => true, 'message' => 'Device updated'];
    }

    /**
     * Get device readings
     */
    public function getDeviceReadings($deviceId, $restaurantId, $readingType, $dateFrom, $dateTo, $limit)
    {
        $readingModel = new IoTDeviceReading();
        return $readingModel->getByDevice($deviceId, $restaurantId, $readingType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Create device reading
     */
    public function createDeviceReading($deviceId, $restaurantId, $data)
    {
        $readingModel = new IoTDeviceReading();
        
        $readingData = [
            'restaurant_id' => $restaurantId,
            'device_id' => $deviceId,
            'reading_type' => $data->reading_type,
            'reading_value' => $data->reading_value,
            'reading_unit' => $data->reading_unit ?? null,
            'reading_quality' => $data->reading_quality ?? 'good',
            'reading_timestamp' => $data->reading_timestamp ?? date('Y-m-d H:i:s'),
            'additional_data' => json_encode($data->additional_data ?? [])
        ];
        
        $readingId = $readingModel->create($readingData);
        
        if (!$readingId) {
            return ['success' => false, 'message' => 'Failed to create reading'];
        }
        
        return ['success' => true, 'message' => 'Reading created', 'reading_id' => $readingId];
    }

    /**
     * Get smart automations
     */
    public function getAutomations($restaurantId, $triggerType, $isActive)
    {
        $automationModel = new SmartAutomation();
        return $automationModel->getByRestaurant($restaurantId, $triggerType, $isActive);
    }

    /**
     * Create automation
     */
    public function createAutomation($restaurantId, $userId, $data)
    {
        $automationModel = new SmartAutomation();
        
        $automationData = [
            'restaurant_id' => $restaurantId,
            'automation_name' => $data->automation_name,
            'automation_description' => $data->automation_description ?? null,
            'trigger_type' => $data->trigger_type,
            'trigger_config' => json_encode($data->trigger_config),
            'action_type' => $data->action_type,
            'action_config' => json_encode($data->action_config),
            'is_active' => true,
            'created_by' => $userId
        ];
        
        $automationId = $automationModel->create($automationData);
        
        if (!$automationId) {
            return ['success' => false, 'message' => 'Failed to create automation'];
        }
        
        return ['success' => true, 'message' => 'Automation created', 'automation_id' => $automationId];
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $deviceModel = new IoTDevice();
        $automationModel = new SmartAutomation();
        
        // Total devices
        $totalDevices = $deviceModel->countByRestaurant($restaurantId);
        
        // Online devices
        $onlineDevices = $deviceModel->countByStatus($restaurantId, 'online');
        
        // Active automations
        $activeAutomations = $automationModel->countActive($restaurantId);
        
        return [
            'total_devices' => $totalDevices,
            'online_devices' => $onlineDevices,
            'offline_devices' => $totalDevices - $onlineDevices,
            'active_automations' => $activeAutomations
        ];
    }
}
