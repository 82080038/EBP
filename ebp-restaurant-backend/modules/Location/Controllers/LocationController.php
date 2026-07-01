<?php

require_once __DIR__ . '/../Services/LocationService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class LocationController
{
    private $service;

    public function __construct()
    {
        $this->service = new LocationService();
    }

    public function findNearbyBranches($request)
    {
        $data = $request['body'] ?? [];
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        $radiusKm = $data['radius_km'] ?? 10;

        if (!$latitude || !$longitude) {
            Response::error('Latitude and longitude are required');
            return;
        }

        $result = $this->service->findNearbyBranches($latitude, $longitude, $radiusKm);

        if ($result['success']) {
            Response::success('Nearby branches found', $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function checkDeliveryAvailability($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $branchId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if (!$latitude || !$longitude) {
            Response::error('Latitude and longitude are required');
            return;
        }

        $result = $this->service->checkDeliveryAvailability($branchId, $latitude, $longitude);

        if ($result['success']) {
            Response::success($result['message'], $result);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateBranchLocation($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'BRANCH_MANAGE');

        $branchId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        $deliveryRadius = $data['delivery_radius_km'] ?? 5;

        if (!$latitude || !$longitude) {
            Response::error('Latitude and longitude are required');
            return;
        }

        $result = $this->service->updateBranchLocation($branchId, $latitude, $longitude, $deliveryRadius, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBranchLocation($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $branchId = $request['params']['id'] ?? null;

        $result = $this->service->getBranchLocation($branchId, $user['tenant_id']);

        if ($result['success']) {
            Response::success('Branch location retrieved', $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function detectNearbyBranch($request)
    {
        $data = $request['body'] ?? [];
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if (!$latitude || !$longitude) {
            Response::error('Latitude and longitude are required');
            return;
        }

        $result = $this->service->findNearbyBranches($latitude, $longitude, 0.5); // 500m radius for auto-detection

        if ($result['success'] && count($result['data']) > 0) {
            // Return the nearest branch
            $nearestBranch = $result['data'][0];
            Response::success('Nearby branch detected', [
                'branch_id' => $nearestBranch['branch_id'],
                'branch_name' => $nearestBranch['branch_name'],
                'address' => $nearestBranch['address'],
                'distance_km' => $nearestBranch['distance_km'],
                'tenant_id' => $nearestBranch['tenant_id']
            ]);
        } else {
            Response::success('No nearby branch detected', null);
        }
    }
}
