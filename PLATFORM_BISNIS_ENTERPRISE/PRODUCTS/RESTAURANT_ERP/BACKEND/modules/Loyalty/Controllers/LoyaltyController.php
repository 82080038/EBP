<?php

if (!class_exists('LoyaltyService')) {
    require_once __DIR__ . '/../Services/LoyaltyService.php';
}

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class LoyaltyController
{
    private $loyaltyService;

    public function __construct()
    {
        $this->loyaltyService = new LoyaltyService();
    }

    // ==================== Loyalty Points Endpoints ====================

    public function getPoints(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $customerId = $request['customer_id'] ?? null;
        $points = $this->loyaltyService->getAllPoints($tenantId, $customerId);

        return Response::success($points);
    }

    public function awardPoints(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $createdBy = $request['user_id'] ?? null;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['user_id'])) {
            return Response::error('User ID is required', 400);
        }
        if (empty($data['points']) || $data['points'] <= 0) {
            return Response::error('Points must be greater than 0', 400);
        }

        $result = $this->loyaltyService->awardPoints(
            $tenantId,
            $data['user_id'],
            $data['points'],
            $data['transaction_type'] ?? 'EARNED',
            $data['reference_id'] ?? null,
            $data['reference_type'] ?? null,
            $data['notes'] ?? null,
            $createdBy
        );

        if ($result['success']) {
            return Response::success($result['data'], $result['message']);
        }

        return Response::error($result['message'], 400, $result['errors'] ?? []);
    }

    public function redeemPoints(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $createdBy = $request['user_id'] ?? null;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['user_id'])) {
            return Response::error('User ID is required', 400);
        }
        if (empty($data['points']) || $data['points'] <= 0) {
            return Response::error('Points must be greater than 0', 400);
        }

        $result = $this->loyaltyService->redeemPoints(
            $tenantId,
            $data['user_id'],
            $data['points'],
            $data['reference_id'] ?? null,
            $data['reference_type'] ?? null,
            $data['notes'] ?? null,
            $createdBy
        );

        if ($result['success']) {
            return Response::success($result['data'], $result['message']);
        }

        return Response::error($result['message'], 400);
    }

    // ==================== Loyalty Rewards Endpoints ====================

    public function getRewards(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'LOYALTY_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $status = $request['status'] ?? null;
        $rewards = $this->loyaltyService->getAllRewards($tenantId, $status);

        return Response::success($rewards);
    }

    public function getReward(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'LOYALTY_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $rewardId = $request['reward_id'] ?? 0;

        $reward = $this->loyaltyService->getRewardById($rewardId, $tenantId);

        if (!$reward) {
            return Response::error('Reward not found', 404);
        }

        return Response::success($reward);
    }

    public function createReward(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $createdBy = $request['user_id'] ?? null;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['reward_code'])) {
            return Response::error('Reward code is required', 400);
        }
        if (empty($data['reward_name'])) {
            return Response::error('Reward name is required', 400);
        }
        if (empty($data['points_required']) || $data['points_required'] <= 0) {
            return Response::error('Points required must be greater than 0', 400);
        }
        if (empty($data['reward_type'])) {
            return Response::error('Reward type is required', 400);
        }

        $result = $this->loyaltyService->createReward($tenantId, $data, $userId);

        if ($result['success']) {
            return Response::success($result['data'], $result['message']);
        }

        return Response::error($result['message'], 400, $result['errors'] ?? []);
    }

    public function updateReward(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $rewardId = $request['reward_id'] ?? 0;
        $userId = $request['user_id'] ?? null;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($rewardId)) {
            return Response::error('Reward ID is required', 400);
        }
        if (empty($data['reward_name'])) {
            return Response::error('Reward name is required', 400);
        }
        if (empty($data['points_required']) || $data['points_required'] <= 0) {
            return Response::error('Points required must be greater than 0', 400);
        }

        $result = $this->loyaltyService->updateReward($rewardId, $tenantId, $data, $userId);

        if ($result['success']) {
            return Response::success([], $result['message']);
        }

        return Response::error($result['message'], 400);
    }

    public function deleteReward(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $rewardId = $request['reward_id'] ?? 0;
        $userId = $request['user_id'] ?? null;

        // Validation
        if (empty($rewardId)) {
            return Response::error('Reward ID is required', 400);
        }

        $result = $this->loyaltyService->deleteReward($rewardId, $tenantId, $userId);

        if ($result['success']) {
            return Response::success([], $result['message']);
        }

        return Response::error($result['message']);
    }

    public function redeemReward(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $createdBy = $request['user_id'] ?? null;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['user_id'])) {
            return Response::error('User ID is required', 400);
        }
        if (empty($data['reward_id'])) {
            return Response::error('Reward ID is required', 400);
        }

        $result = $this->loyaltyService->redeemReward($tenantId, $data['user_id'], $data['reward_id'], $createdBy);

        if ($result['success']) {
            return Response::success($result['data'], $result['message']);
        }

        return Response::error($result['message'], 400);
    }

    // ==================== Customer Loyalty Endpoints ====================

    public function getCustomerLoyalty(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'LOYALTY_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $loyalty = $this->loyaltyService->getAllCustomerLoyalty($tenantId);

        return Response::success($loyalty);
    }

    public function getCustomerLoyaltyByCustomer(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'LOYALTY_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $userId = $request['user_id'] ?? 0;

        $loyalty = $this->loyaltyService->getCustomerLoyaltyByUser($userId, $tenantId);

        if (!$loyalty) {
            return Response::error('Customer loyalty not found', 404);
        }

        return Response::success($loyalty);
    }

    public function enrollCustomer(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $createdBy = $request['user_id'] ?? null;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['user_id'])) {
            return Response::error('User ID is required', 400);
        }

        $result = $this->loyaltyService->enrollCustomer($tenantId, $data['user_id'], $createdBy);

        if ($result['success']) {
            return Response::success($result['data'], $result['message']);
        }

        return Response::error($result['message'], 400);
    }

    public function getTopCustomers(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $limit = $request['limit'] ?? 10;
        $customers = $this->loyaltyService->getTopCustomers($tenantId, $limit);

        return Response::success($customers);
    }

    public function getCustomersByTier(array $request)
    {
        $request = AuthMiddleware::handle($request);
        // PermissionMiddleware::handle($request, 'LOYALTY_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $tier = $request['tier'] ?? 'BRONZE';
        $customers = $this->loyaltyService->getCustomersByTier($tenantId, $tier);

        return Response::success($customers);
    }
}
