<?php

namespace App\Modules\Supplier\Controllers;

use App\Core\BaseController;
use App\Modules\Supplier\Models\Supplier;
use App\Modules\Supplier\Models\SupplierProduct;
use App\Modules\Supplier\Models\SupplierContract;
use App\Modules\Supplier\Models\SupplierPerformance;
use App\Modules\Supplier\Services\SupplierService;
use App\Core\Auth;

class SupplierController extends BaseController
{
    private $supplierService;

    public function __construct()
    {
        parent::__construct();
        $this->supplierService = new SupplierService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get suppliers
     * GET /api/suppliers
     */
    public function getSuppliers()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $type = $this->request->get('type', null);
        $isActive = $this->request->get('is_active', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->supplierService->getSuppliers($restaurantId, $type, $isActive, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single supplier
     * GET /api/suppliers/{id}
     */
    public function getSupplier($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $supplier = $this->supplierService->getSupplier($id, $restaurantId);
        
        if (!$supplier) {
            $this->jsonResponse(['error' => 'Supplier not found'], 404);
            return;
        }
        
        $this->jsonResponse($supplier);
    }

    /**
     * Create supplier
     * POST /api/suppliers
     */
    public function createSupplier()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->supplierService->createSupplier($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update supplier
     * PUT /api/suppliers/{id}
     */
    public function updateSupplier($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->supplierService->updateSupplier($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get supplier products
     * GET /api/suppliers/{id}/products
     */
    public function getSupplierProducts($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $products = $this->supplierService->getSupplierProducts($id, $restaurantId);
        
        $this->jsonResponse($products);
    }

    /**
     * Add supplier product
     * POST /api/suppliers/{id}/products
     */
    public function addSupplierProduct($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->supplierService->addSupplierProduct($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get supplier contracts
     * GET /api/suppliers/{id}/contracts
     */
    public function getSupplierContracts($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $contracts = $this->supplierService->getSupplierContracts($id, $restaurantId);
        
        $this->jsonResponse($contracts);
    }

    /**
     * Create supplier contract
     * POST /api/suppliers/{id}/contracts
     */
    public function createSupplierContract($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->supplierService->createSupplierContract($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get supplier performance
     * GET /api/suppliers/{id}/performance
     */
    public function getSupplierPerformance($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $performance = $this->supplierService->getSupplierPerformance($id, $restaurantId);
        
        $this->jsonResponse($performance);
    }

    /**
     * Rate supplier
     * POST /api/suppliers/{id}/rate
     */
    public function rateSupplier($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->supplierService->rateSupplier($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get supplier statistics
     * GET /api/suppliers/statistics
     */
    public function getStatistics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $stats = $this->supplierService->getStatistics($restaurantId);
        
        $this->jsonResponse($stats);
    }
}
