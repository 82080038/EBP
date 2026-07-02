<?php

if (!class_exists('SettingService')) {
    require_once __DIR__ . '/../Services/SettingService.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}

class SettingController
{
    private $settingService;

    public function __construct()
    {
        $this->settingService = new SettingService();
    }

    public function getSettings(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'SETTINGS_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $settings = $this->settingService->getAllSettings($tenantId);

        return Response::success($settings);
    }

    public function getSetting(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'SETTINGS_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $key = $request['key'] ?? '';

        if (empty($key)) {
            return Response::error('Setting key is required', 400);
        }

        $setting = $this->settingService->getSetting($tenantId, $key);

        if (!$setting) {
            return Response::error('Setting not found', 404);
        }

        return Response::success($setting);
    }

    public function getSettingGroup(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'SETTINGS_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $prefix = $request['prefix'] ?? '';

        if (empty($prefix)) {
            return Response::error('Prefix is required', 400);
        }

        $settings = $this->settingService->getSettingGroup($tenantId, $prefix);

        return Response::success($settings);
    }

    public function createSetting(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'SETTINGS_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['setting_key'])) {
            return Response::error('Setting key is required', 400);
        }
        if (!isset($data['setting_value'])) {
            return Response::error('Setting value is required', 400);
        }

        $result = $this->settingService->createSetting($tenantId, $data);

        if ($result) {
            return Response::success(['message' => 'Setting created successfully']);
        }

        return Response::error('Failed to create setting or key already exists', 500);
    }

    public function updateSetting(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'SETTINGS_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $settingId = $request['setting_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($settingId)) {
            return Response::error('Setting ID is required', 400);
        }
        if (empty($data['setting_key'])) {
            return Response::error('Setting key is required', 400);
        }
        if (!isset($data['setting_value'])) {
            return Response::error('Setting value is required', 400);
        }

        $result = $this->settingService->updateSetting($tenantId, $settingId, $data);

        if ($result) {
            return Response::success(['message' => 'Setting updated successfully']);
        }

        return Response::error('Failed to update setting', 500);
    }

    public function upsertSetting(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'SETTINGS_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['setting_key'])) {
            return Response::error('Setting key is required', 400);
        }
        if (!isset($data['setting_value'])) {
            return Response::error('Setting value is required', 400);
        }

        $result = $this->settingService->upsertSetting(
            $tenantId,
            $data['setting_key'],
            $data['setting_value'],
            $data['setting_type'] ?? 'STRING',
            $data['description'] ?? null
        );

        if ($result) {
            return Response::success(['message' => 'Setting saved successfully']);
        }

        return Response::error('Failed to save setting', 500);
    }

    public function deleteSetting(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'SETTINGS_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $settingId = $request['setting_id'] ?? 0;

        // Validation
        if (empty($settingId)) {
            return Response::error('Setting ID is required', 400);
        }

        $result = $this->settingService->deleteSetting($tenantId, $settingId);

        if ($result) {
            return Response::success(['message' => 'Setting deleted successfully']);
        }

        return Response::error('Failed to delete setting', 500);
    }

    public function initializeSettings(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'SETTINGS_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;

        $result = $this->settingService->initializeDefaultSettings($tenantId);

        if ($result) {
            return Response::success(['message' => 'Default settings initialized successfully']);
        }

        return Response::error('Failed to initialize default settings', 500);
    }
}
