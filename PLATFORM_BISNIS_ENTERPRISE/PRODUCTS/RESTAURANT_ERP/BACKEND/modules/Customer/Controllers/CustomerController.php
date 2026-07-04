<?php

namespace App\Modules\Customer\Controllers;

use App\Core\BaseController;
use App\Modules\Customer\Models\Customer;
use App\Modules\Customer\Models\CustomerPreference;
use App\Modules\Customer\Models\CustomerAddress;
use App\Modules\Customer\Models\CustomerNote;
use App\Modules\Customer\Models\CustomerTag;
use App\Modules\Customer\Models\CustomerVisit;
use App\Modules\Customer\Services\CustomerService;
use App\Core\Auth;

class CustomerController extends BaseController
{
    private $customerService;

    public function __construct()
    {
        parent::__construct();
        $this->customerService = new CustomerService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get customers
     * GET /api/customers
     */
    public function getCustomers()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $search = $this->request->get('search', null);
        $isVip = $this->request->get('vip', null);
        $tagId = $this->request->get('tag_id', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->customerService->getCustomers($restaurantId, $search, $isVip, $tagId, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single customer
     * GET /api/customers/{id}
     */
    public function getCustomer($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $customer = $this->customerService->getCustomer($id, $restaurantId);
        
        if (!$customer) {
            $this->jsonResponse(['error' => 'Customer not found'], 404);
            return;
        }
        
        $this->jsonResponse($customer);
    }

    /**
     * Create customer
     * POST /api/customers
     */
    public function createCustomer()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerService->createCustomer($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update customer
     * PUT /api/customers/{id}
     */
    public function updateCustomer($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerService->updateCustomer($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get customer preferences
     * GET /api/customers/{id}/preferences
     */
    public function getPreferences($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $preferences = $this->customerService->getPreferences($id, $restaurantId);
        
        $this->jsonResponse($preferences);
    }

    /**
     * Update customer preferences
     * PUT /api/customers/{id}/preferences
     */
    public function updatePreferences($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerService->updatePreferences($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get customer addresses
     * GET /api/customers/{id}/addresses
     */
    public function getAddresses($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $addresses = $this->customerService->getAddresses($id);
        
        $this->jsonResponse($addresses);
    }

    /**
     * Add customer address
     * POST /api/customers/{id}/addresses
     */
    public function addAddress($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerService->addAddress($id, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get customer notes
     * GET /api/customers/{id}/notes
     */
    public function getNotes($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $notes = $this->customerService->getNotes($id, $restaurantId);
        
        $this->jsonResponse($notes);
    }

    /**
     * Add customer note
     * POST /api/customers/{id}/notes
     */
    public function addNote($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerService->addNote($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get customer tags
     * GET /api/customers/tags
     */
    public function getTags()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $tags = $this->customerService->getTags($restaurantId);
        
        $this->jsonResponse($tags);
    }

    /**
     * Create customer tag
     * POST /api/customers/tags
     */
    public function createTag()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerService->createTag($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Assign tag to customer
     * POST /api/customers/{id}/tags
     */
    public function assignTag($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerService->assignTag($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get customer visit history
     * GET /api/customers/{id}/visits
     */
    public function getVisits($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->customerService->getVisits($id, $restaurantId, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get customer statistics
     * GET /api/customers/statistics
     */
    public function getStatistics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $stats = $this->customerService->getStatistics($restaurantId);
        
        $this->jsonResponse($stats);
    }
}
