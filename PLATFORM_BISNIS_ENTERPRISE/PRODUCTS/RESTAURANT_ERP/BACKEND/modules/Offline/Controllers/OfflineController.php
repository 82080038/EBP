<?php

namespace App\Modules\Offline\Controllers;

use App\Core\BaseController;
use App\Modules\Offline\Models\OfflineTransaction;
use App\Modules\Offline\Models\OfflineDataSnapshot;
use App\Modules\Offline\Models\OfflineConflict;
use App\Modules\Offline\Models\DeviceRegistration;
use App\Modules\Offline\Models\SyncQueue;
use App\Modules\Offline\Services\OfflineService;
use App\Core\Auth;

class OfflineController extends BaseController
{
    private $offlineService;

    public function __construct()
    {
        parent::__construct();
        $this->offlineService = new OfflineService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Register device
     * POST /api/offline/register-device
     */
    public function registerDevice()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->offlineService->registerDevice($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get device info
     * GET /api/offline/device/{deviceId}
     */
    public function getDeviceInfo($deviceId)
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $device = $this->offlineService->getDeviceInfo($deviceId, $restaurantId);
        
        if (!$device) {
            $this->jsonResponse(['error' => 'Device not found'], 404);
            return;
        }
        
        $this->jsonResponse($device);
    }

    /**
     * Upload offline transaction
     * POST /api/offline/transactions
     */
    public function uploadTransaction()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->offlineService->uploadTransaction($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get offline transactions
     * GET /api/offline/transactions
     */
    public function getTransactions()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        $deviceId = $this->request->get('device_id', null);
        $syncStatus = $this->request->get('sync_status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->offlineService->getTransactions($restaurantId, $deviceId, $syncStatus, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Sync offline transactions
     * POST /api/offline/sync
     */
    public function syncTransactions()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        $deviceId = $this->request->get('device_id', null);
        
        $result = $this->offlineService->syncTransactions($restaurantId, $userId, $deviceId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Download data snapshot
     * GET /api/offline/snapshot
     */
    public function downloadSnapshot()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        $deviceId = $this->request->get('device_id', null);
        $dataType = $this->request->get('data_type', null);
        
        $result = $this->offlineService->downloadSnapshot($restaurantId, $userId, $deviceId, $dataType);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Upload data snapshot
     * POST /api/offline/snapshot
     */
    public function uploadSnapshot()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->offlineService->uploadSnapshot($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get conflicts
     * GET /api/offline/conflicts
     */
    public function getConflicts()
    {
        $this->requirePermission('can_manage_offline_data');
        
        $restaurantId = Auth::user()->restaurant_id;
        $isResolved = $this->request->get('is_resolved', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->offlineService->getConflicts($restaurantId, $isResolved, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Resolve conflict
     * POST /api/offline/conflicts/{id}/resolve
     */
    public function resolveConflict($id)
    {
        $this->requirePermission('can_manage_offline_data');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->offlineService->resolveConflict($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get sync queue
     * GET /api/offline/sync-queue
     */
    public function getSyncQueue()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        $deviceId = $this->request->get('device_id', null);
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->offlineService->getSyncQueue($restaurantId, $deviceId, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get offline settings
     * GET /api/offline/settings
     */
    public function getSettings()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $settings = $this->offlineService->getSettings($restaurantId);
        
        $this->jsonResponse($settings);
    }

    /**
     * Update offline settings
     * PUT /api/offline/settings
     */
    public function updateSettings()
    {
        $this->requirePermission('can_manage_offline_data');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->offlineService->updateSettings($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Check offline status
     * GET /api/offline/status
     */
    public function getStatus()
    {
        $this->requirePermission('can_work_offline');
        
        $restaurantId = Auth::user()->restaurant_id;
        $deviceId = $this->request->get('device_id', null);
        
        $status = $this->offlineService->getStatus($restaurantId, $deviceId);
        
        $this->jsonResponse($status);
    }
}
