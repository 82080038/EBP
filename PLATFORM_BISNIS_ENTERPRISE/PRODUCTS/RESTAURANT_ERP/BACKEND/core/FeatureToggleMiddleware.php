<?php

if (!class_exists('FeatureToggleService')) {
    require_once __DIR__ . '/FeatureToggleService.php';
}

class FeatureToggleMiddleware
{
    private $service;

    public function __construct()
    {
        $this->service = new FeatureToggleService();
    }

    /**
     * Check if feature is enabled for current user
     * If not enabled, return 403 Forbidden
     */
    public function check($moduleCode)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        if (!$this->service->isFeatureEnabledForUser($user['user_id'], $moduleCode)) {
            Response::error("Feature '$moduleCode' is not enabled for your account", 403);
        }
    }

    /**
     * Check if feature is enabled for current user (soft check)
     * Returns boolean instead of throwing error
     */
    public function isEnabled($moduleCode)
    {
        try {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();
            return $this->service->isFeatureEnabledForUser($user['user_id'], $moduleCode);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if feature is enabled for specific user
     */
    public function checkForUser($userId, $moduleCode)
    {
        if (!$this->service->isFeatureEnabledForUser($userId, $moduleCode)) {
            Response::error("Feature '$moduleCode' is not enabled for this user", 403);
        }
    }

    /**
     * Check if feature is enabled for specific role
     */
    public function checkForRole($roleId, $moduleCode)
    {
        if (!$this->service->isFeatureEnabledForRole($roleId, $moduleCode)) {
            Response::error("Feature '$moduleCode' is not enabled for this role", 403);
        }
    }

    /**
     * Check if any of the given features are enabled
     */
    public function checkAny($moduleCodes)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        foreach ($moduleCodes as $moduleCode) {
            if ($this->service->isFeatureEnabledForUser($user['user_id'], $moduleCode)) {
                return; // At least one feature is enabled
            }
        }

        Response::error("None of the required features are enabled for your account", 403);
    }

    /**
     * Check if all of the given features are enabled
     */
    public function checkAll($moduleCodes)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        foreach ($moduleCodes as $moduleCode) {
            if (!$this->service->isFeatureEnabledForUser($user['user_id'], $moduleCode)) {
                Response::error("Feature '$moduleCode' is not enabled for your account", 403);
            }
        }
    }
}
