<?php

namespace App\Modules\Franchise\Controllers;

use App\Core\BaseController;
use App\Modules\Franchise\Models\Franchisee;
use App\Modules\Franchise\Models\FranchiseAgreement;
use App\Modules\Franchise\Models\FranchisePerformance;
use App\Modules\Franchise\Models\FranchiseRoyalty;
use App\Modules\Franchise\Services\FranchiseService;
use App\Core\Auth;

class FranchiseController extends BaseController
{
    private $franchiseService;

    public function __construct()
    {
        parent::__construct();
        $this->franchiseService = new FranchiseService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get franchisees
     * GET /api/franchise/franchisees
     */
    public function getFranchisees()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        
        $franchisees = $this->franchiseService->getFranchisees($restaurantId, $status);
        
        $this->jsonResponse($franchisees);
    }

    /**
     * Create franchisee
     * POST /api/franchise/franchisees
     */
    public function createFranchisee()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->franchiseService->createFranchisee($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get franchise agreements
     * GET /api/franchise/agreements
     */
    public function getAgreements()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $franchiseeId = $this->request->get('franchisee_id', null);
        $status = $this->request->get('status', null);
        
        $agreements = $this->franchiseService->getAgreements($restaurantId, $franchiseeId, $status);
        
        $this->jsonResponse($agreements);
    }

    /**
     * Create franchise agreement
     * POST /api/franchise/agreements
     */
    public function createAgreement()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->franchiseService->createAgreement($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get franchise performance
     * GET /api/franchise/performance
     */
    public function getPerformance()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $franchiseeId = $this->request->get('franchisee_id', null);
        
        $performance = $this->franchiseService->getPerformance($restaurantId, $franchiseeId);
        
        $this->jsonResponse($performance);
    }

    /**
     * Get royalties
     * GET /api/franchise/royalties
     */
    public function getRoyalties()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $franchiseeId = $this->request->get('franchisee_id', null);
        $status = $this->request->get('status', null);
        
        $royalties = $this->franchiseService->getRoyalties($restaurantId, $franchiseeId, $status);
        
        $this->jsonResponse($royalties);
    }

    /**
     * Create royalty record
     * POST /api/franchise/royalties
     */
    public function createRoyalty()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->franchiseService->createRoyalty($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get support tickets
     * GET /api/franchise/support-tickets
     */
    public function getSupportTickets()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $franchiseeId = $this->request->get('franchisee_id', null);
        $status = $this->request->get('status', null);
        
        $tickets = $this->franchiseService->getSupportTickets($restaurantId, $franchiseeId, $status);
        
        $this->jsonResponse($tickets);
    }

    /**
     * Create support ticket
     * POST /api/franchise/support-tickets
     */
    public function createSupportTicket()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->franchiseService->createSupportTicket($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get franchise summary
     * GET /api/franchise/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->franchiseService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
