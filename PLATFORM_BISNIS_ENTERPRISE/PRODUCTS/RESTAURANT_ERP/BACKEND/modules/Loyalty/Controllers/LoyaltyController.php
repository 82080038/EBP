<?php

namespace App\Modules\Loyalty\Controllers;

use App\Core\BaseController;
use App\Modules\Loyalty\Models\LoyaltyProgram;
use App\Modules\Loyalty\Models\LoyaltyTier;
use App\Modules\Loyalty\Models\CustomerLoyalty;
use App\Modules\Loyalty\Models\PointsTransaction;
use App\Modules\Loyalty\Models\Reward;
use App\Modules\Loyalty\Models\RewardRedemption;
use App\Modules\Loyalty\Services\LoyaltyService;
use App\Core\Auth;

class LoyaltyController extends BaseController
{
    private $loyaltyService;

    public function __construct()
    {
        parent::__construct();
        $this->loyaltyService = new LoyaltyService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get loyalty programs
     * GET /api/loyalty/programs
     */
    public function getPrograms()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $programs = $this->loyaltyService->getPrograms($restaurantId);
        
        $this->jsonResponse($programs);
    }

    /**
     * Create loyalty program
     * POST /api/loyalty/programs
     */
    public function createProgram()
    {
        $this->requirePermission('can_manage_loyalty');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->loyaltyService->createProgram($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get loyalty tiers
     * GET /api/loyalty/tiers
     */
    public function getTiers()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $programId = $this->request->get('program_id', null);
        
        $tiers = $this->loyaltyService->getTiers($restaurantId, $programId);
        
        $this->jsonResponse($tiers);
    }

    /**
     * Create loyalty tier
     * POST /api/loyalty/tiers
     */
    public function createTier()
    {
        $this->requirePermission('can_manage_loyalty');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->loyaltyService->createTier($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get customer loyalty
     * GET /api/loyalty/customers/{id}
     */
    public function getCustomerLoyalty($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $loyalty = $this->loyaltyService->getCustomerLoyalty($id, $restaurantId);
        
        if (!$loyalty) {
            $this->jsonResponse(['error' => 'Customer loyalty not found'], 404);
            return;
        }
        
        $this->jsonResponse($loyalty);
    }

    /**
     * Enroll customer in loyalty program
     * POST /api/loyalty/customers/{id}/enroll
     */
    public function enrollCustomer($id)
    {
        $this->requirePermission('can_manage_loyalty');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->loyaltyService->enrollCustomer($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Award points to customer
     * POST /api/loyalty/customers/{id}/award-points
     */
    public function awardPoints($id)
    {
        $this->requirePermission('can_manage_loyalty');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->loyaltyService->awardPoints($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get points transactions
     * GET /api/loyalty/transactions
     */
    public function getTransactions()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $customerId = $this->request->get('customer_id', null);
        $transactionType = $this->request->get('type', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->loyaltyService->getTransactions($restaurantId, $customerId, $transactionType, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get rewards
     * GET /api/loyalty/rewards
     */
    public function getRewards()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $programId = $this->request->get('program_id', null);
        
        $rewards = $this->loyaltyService->getRewards($restaurantId, $programId);
        
        $this->jsonResponse($rewards);
    }

    /**
     * Create reward
     * POST /api/loyalty/rewards
     */
    public function createReward()
    {
        $this->requirePermission('can_manage_loyalty');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->loyaltyService->createReward($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Redeem reward
     * POST /api/loyalty/rewards/{id}/redeem
     */
    public function redeemReward($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->loyaltyService->redeemReward($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get reward redemptions
     * GET /api/loyalty/redemptions
     */
    public function getRedemptions()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $customerId = $this->request->get('customer_id', null);
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->loyaltyService->getRedemptions($restaurantId, $customerId, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Apply reward redemption
     * POST /api/loyalty/redemptions/{id}/apply
     */
    public function applyRedemption($id)
    {
        $this->requirePermission('can_manage_loyalty');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $result = $this->loyaltyService->applyRedemption($id, $restaurantId, $userId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get loyalty statistics
     * GET /api/loyalty/statistics
     */
    public function getStatistics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $stats = $this->loyaltyService->getStatistics($restaurantId);
        
        $this->jsonResponse($stats);
    }
}
