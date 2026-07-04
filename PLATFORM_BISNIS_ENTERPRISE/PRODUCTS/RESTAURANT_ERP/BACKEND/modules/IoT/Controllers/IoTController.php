<?php

namespace App\Modules\IoT\Controllers;

use App\Core\BaseController;
use App\Modules\IoT\Models\IoTDevice;
use App\Modules\IoT\Models\IoTDeviceReading;
use App\Modules\IoT\Models\SmartAutomation;
use App\Modules\IoT\Services\IoTService;
use App\Core\Auth;

class IoTController extends BaseController
{
    private $iotService;

    public function __construct()
    {
        parent::__construct();
        $this->iotService = new IoTService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get IoT devices
     * GET /api/iot/devices
     */
    public function getDevices()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $deviceType = $this->request->get('type', null);
        $status = $this->request->get('status', null);
        
        $devices = $this->iotService->getDevices($restaurantId, $deviceType, $status);
        
        $this->jsonResponse($devices);
    }

    /**
     * Get single device
     * GET /api/iot/devices/{id}
     */
    public function getDevice($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $device = $this->iotService->getDevice($id, $restaurantId);
        
        if (!$device) {
            $this->jsonResponse(['error' => 'Device not found'], 404);
            return;
        }
        
        $this->jsonResponse($device);
    }

    /**
     * Create device
     * POST /api/iot/devices
     */
    public function createDevice()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->iotService->createDevice($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update device
     * PUT /api/iot/devices/{id}
     */
    public function updateDevice($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->iotService->updateDevice($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get device readings
     * GET /api/iot/devices/{id}/readings
     */
    public function getDeviceReadings($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $readingType = $this->request->get('type', null);
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 100);
        
        $readings = $this->iotService->getDeviceReadings($id, $restaurantId, $readingType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($readings);
    }

    /**
     * Create device reading
     * POST /api/iot/devices/{id}/readings
     */
    public function createDeviceReading($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->iotService->createDeviceReading($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get smart automations
     * GET /api/iot/automations
     */
    public function getAutomations()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $triggerType = $this->request->get('trigger_type', null);
        $isActive = $this->request->get('is_active', null);
        
        $automations = $this->iotService->getAutomations($restaurantId, $triggerType, $isActive);
        
        $this->jsonResponse($automations);
    }

    /**
     * Create automation
     * POST /api/iot/automations
     */
    public function createAutomation()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->iotService->createAutomation($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get IoT summary
     * GET /api/iot/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->iotService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
