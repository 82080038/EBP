<?php

namespace App\Modules\Payment\Controllers;

use App\Core\BaseController;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Models\PaymentRefund;
use App\Modules\Payment\Models\PaymentMethod;
use App\Modules\Payment\Models\Tip;
use App\Modules\Payment\Services\PaymentService;
use App\Core\Auth;

class PaymentController extends BaseController
{
    private $paymentService;

    public function __construct()
    {
        parent::__construct();
        $this->paymentService = new PaymentService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get payments
     * GET /api/payments
     */
    public function getPayments()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $orderId = $this->request->get('order_id', null);
        $status = $this->request->get('status', null);
        $method = $this->request->get('method', null);
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->paymentService->getPayments($restaurantId, $orderId, $status, $method, $dateFrom, $dateTo, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single payment
     * GET /api/payments/{id}
     */
    public function getPayment($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $payment = $this->paymentService->getPayment($id, $restaurantId);
        
        if (!$payment) {
            $this->jsonResponse(['error' => 'Payment not found'], 404);
            return;
        }
        
        $this->jsonResponse($payment);
    }

    /**
     * Create payment
     * POST /api/payments
     */
    public function createPayment()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->paymentService->createPayment($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Process payment
     * POST /api/payments/{id}/process
     */
    public function processPayment($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $result = $this->paymentService->processPayment($id, $restaurantId, $userId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Refund payment
     * POST /api/payments/{id}/refund
     */
    public function refundPayment($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->paymentService->refundPayment($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get refunds
     * GET /api/payments/refunds
     */
    public function getRefunds()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->paymentService->getRefunds($restaurantId, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get payment methods
     * GET /api/payments/methods
     */
    public function getPaymentMethods()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $methods = $this->paymentService->getPaymentMethods($restaurantId);
        
        $this->jsonResponse($methods);
    }

    /**
     * Add payment method
     * POST /api/payments/methods
     */
    public function addPaymentMethod()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->paymentService->addPaymentMethod($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update payment method
     * PUT /api/payments/methods/{id}
     */
    public function updatePaymentMethod($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->paymentService->updatePaymentMethod($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Add tip
     * POST /api/payments/tips
     */
    public function addTip()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->paymentService->addTip($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get payment statistics
     * GET /api/payments/statistics
     */
    public function getStatistics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        $stats = $this->paymentService->getStatistics($restaurantId, $dateFrom, $dateTo);
        
        $this->jsonResponse($stats);
    }
}
