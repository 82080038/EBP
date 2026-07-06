<?php

if (!class_exists('FixedAssetsService')) {
    require_once __DIR__ . '/../Services/FixedAssetsService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class FixedAssetsController
{
    private $service;

    public function __construct()
    {
        $this->service = new FixedAssetsService();
    }

    public function createAsset($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createAsset($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success(['asset_id' => $result['asset_id'], 'asset_code' => $result['asset_code']], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAssets($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['query'] ?? [];
        $status = $params['status'] ?? null;
        $category = $params['category'] ?? null;

        $result = $this->service->getAssets($user['tenant_id'], $user['branch_id'], $status, $category);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAsset($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $assetId = $params['id'];

        if (!$assetId) {
            Response::error('Asset ID is required');
        }

        $result = $this->service->getAsset($user['tenant_id'], $user['branch_id'], $assetId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function calculateDepreciation($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];
        $assetId = $data['asset_id'] ?? null;
        $fiscalYear = $data['fiscal_year'] ?? date('Y');
        $fiscalMonth = $data['fiscal_month'] ?? date('m');

        if (!$assetId) {
            Response::error('Asset ID is required');
        }

        $result = $this->service->calculateDepreciation($user['tenant_id'], $user['branch_id'], $assetId, $fiscalYear, $fiscalMonth, $user['user_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getDepreciationSchedule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $assetId = $params['id'];

        if (!$assetId) {
            Response::error('Asset ID is required');
        }

        $result = $this->service->getDepreciationSchedule($user['tenant_id'], $user['branch_id'], $assetId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function disposeAsset($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];
        $assetId = $data['asset_id'] ?? null;
        $disposalType = $data['disposal_type'] ?? null;
        $disposalValue = $data['disposal_value'] ?? 0;

        if (!$assetId || !$disposalType) {
            Response::error('Asset ID and disposal type are required');
        }

        $result = $this->service->disposeAsset($user['tenant_id'], $user['branch_id'], $assetId, $disposalType, $disposalValue, $user['user_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
