<?php

namespace App\Modules\Payment\Services;

use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Models\PaymentRefund;
use App\Modules\Payment\Models\PaymentMethod;
use App\Modules\Payment\Models\Tip;
use App\Core\Database;

class PaymentService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get payments
     */
    public function getPayments($restaurantId, $orderId, $status, $method, $dateFrom, $dateTo, $page, $limit)
    {
        $paymentModel = new Payment();
        return $paymentModel->getPaginated($restaurantId, $orderId, $status, $method, $dateFrom, $dateTo, $page, $limit);
    }

    /**
     * Get single payment
     */
    public function getPayment($id, $restaurantId)
    {
        $paymentModel = new Payment();
        $payment = $paymentModel->findById($id, $restaurantId);
        
        if ($payment) {
            // Get refunds
            $refundModel = new PaymentRefund();
            $payment['refunds'] = $refundModel->getByPaymentId($id);
        }
        
        return $payment;
    }

    /**
     * Create payment
     */
    public function createPayment($restaurantId, $userId, $data)
    {
        $paymentModel = new Payment();
        
        $paymentData = [
            'restaurant_id' => $restaurantId,
            'order_id' => $data->order_id,
            'payment_method' => $data->payment_method,
            'payment_status' => 'pending',
            'amount' => $data->amount,
            'currency' => 'IDR',
            'payment_gateway' => $data->payment_gateway ?? null,
            'card_last_four' => $data->card_last_four ?? null,
            'card_brand' => $data->card_brand ?? null,
            'e_wallet_provider' => $data->e_wallet_provider ?? null,
            'e_wallet_phone' => $data->e_wallet_phone ?? null,
            'bank_name' => $data->bank_name ?? null,
            'account_number' => $data->account_number ?? null,
            'account_name' => $data->account_name ?? null,
            'voucher_code' => $data->voucher_code ?? null,
            'voucher_amount' => $data->voucher_amount ?? null,
            'processed_by' => $userId,
            'notes' => $data->notes ?? null
        ];
        
        $paymentId = $paymentModel->create($paymentData);
        
        if (!$paymentId) {
            return ['success' => false, 'message' => 'Failed to create payment'];
        }
        
        // Log transaction
        $this->logTransaction($restaurantId, $paymentId, 'payment', 'pending', $data->amount, null);
        
        return ['success' => true, 'message' => 'Payment created successfully', 'payment_id' => $paymentId];
    }

    /**
     * Process payment
     */
    public function processPayment($id, $restaurantId, $userId)
    {
        $paymentModel = new Payment();
        $payment = $paymentModel->findById($id, $restaurantId);
        
        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }
        
        if ($payment['payment_status'] !== 'pending') {
            return ['success' => false, 'message' => 'Payment already processed'];
        }
        
        // Process based on payment method
        $result = $this->processPaymentMethod($payment);
        
        if ($result['success']) {
            $paymentModel->update($id, [
                'payment_status' => 'completed',
                'processed_at' => date('Y-m-d H:i:s'),
                'completed_at' => date('Y-m-d H:i:s'),
                'gateway_transaction_id' => $result['transaction_id'] ?? null,
                'gateway_response' => json_encode($result['response'] ?? [])
            ]);
            
            // Update order payment status
            $this->updateOrderPaymentStatus($payment['order_id']);
            
            // Log transaction
            $this->logTransaction($restaurantId, $id, 'payment', 'success', $payment['amount'], null);
            
            return ['success' => true, 'message' => 'Payment processed successfully'];
        } else {
            $paymentModel->update($id, [
                'payment_status' => 'failed',
                'failed_at' => date('Y-m-d H:i:s'),
                'failure_reason' => $result['message']
            ]);
            
            // Log transaction
            $this->logTransaction($restaurantId, $id, 'payment', 'failed', $payment['amount'], $result['message']);
            
            return ['success' => false, 'message' => $result['message']];
        }
    }

    /**
     * Process payment method
     */
    private function processPaymentMethod($payment)
    {
        // In real implementation, integrate with payment gateways
        // For now, simulate processing
        
        switch ($payment['payment_method']) {
            case 'cash':
                return ['success' => true, 'transaction_id' => 'CASH-' . time()];
            
            case 'card':
                // Simulate card processing
                return ['success' => true, 'transaction_id' => 'CARD-' . time()];
            
            case 'e_wallet':
                // Simulate e-wallet processing
                return ['success' => true, 'transaction_id' => 'EW-' . time()];
            
            case 'bank_transfer':
                // Bank transfer requires manual confirmation
                return ['success' => true, 'transaction_id' => 'BT-' . time()];
            
            case 'voucher':
                // Validate voucher
                return ['success' => true, 'transaction_id' => 'VOUCHER-' . time()];
            
            default:
                return ['success' => false, 'message' => 'Unknown payment method'];
        }
    }

    /**
     * Update order payment status
     */
    private function updateOrderPaymentStatus($orderId)
    {
        $paymentModel = new Payment();
        
        // Get total paid for order
        $sql = "SELECT SUM(amount) as total_paid FROM payments WHERE order_id = ? AND payment_status = 'completed'";
        $result = $this->db->query($sql, [$orderId])->fetch();
        $totalPaid = $result['total_paid'] ?? 0;
        
        // Get order total
        $sql = "SELECT total_amount FROM orders WHERE id = ?";
        $result = $this->db->query($sql, [$orderId])->fetch();
        $orderTotal = $result['total_amount'] ?? 0;
        
        // Update order payment status
        $paymentStatus = 'unpaid';
        if ($totalPaid >= $orderTotal) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }
        
        $sql = "UPDATE orders SET payment_status = ?, paid_amount = ? WHERE id = ?";
        $this->db->query($sql, [$paymentStatus, $totalPaid, $orderId]);
    }

    /**
     * Refund payment
     */
    public function refundPayment($id, $restaurantId, $userId, $data)
    {
        $paymentModel = new Payment();
        $payment = $paymentModel->findById($id, $restaurantId);
        
        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }
        
        if ($payment['payment_status'] !== 'completed') {
            return ['success' => false, 'message' => 'Payment cannot be refunded'];
        }
        
        $refundAmount = $data->refund_amount ?? $payment['amount'];
        
        if ($refundAmount > $payment['amount']) {
            return ['success' => false, 'message' => 'Refund amount exceeds payment amount'];
        }
        
        $refundModel = new PaymentRefund();
        
        $refundData = [
            'restaurant_id' => $restaurantId,
            'payment_id' => $id,
            'order_id' => $payment['order_id'],
            'refund_amount' => $refundAmount,
            'refund_reason' => $data->refund_reason,
            'refund_status' => 'pending',
            'requested_at' => date('Y-m-d H:i:s'),
            'requested_by' => $userId,
            'notes' => $data->notes ?? null
        ];
        
        $refundId = $refundModel->create($refundData);
        
        if (!$refundId) {
            return ['success' => false, 'message' => 'Failed to create refund'];
        }
        
        // Process refund
        $result = $this->processRefund($refundId, $payment);
        
        if ($result['success']) {
            $refundModel->update($refundId, [
                'refund_status' => 'completed',
                'processed_at' => date('Y-m-d H:i:s'),
                'gateway_refund_id' => $result['refund_id'] ?? null
            ]);
            
            // Update payment status if full refund
            if ($refundAmount >= $payment['amount']) {
                $paymentModel->update($id, ['payment_status' => 'refunded']);
            } else {
                $paymentModel->update($id, ['payment_status' => 'partial_refund']);
            }
            
            return ['success' => true, 'message' => 'Refund processed successfully', 'refund_id' => $refundId];
        } else {
            $refundModel->update($refundId, ['refund_status' => 'failed']);
            return ['success' => false, 'message' => $result['message']];
        }
    }

    /**
     * Process refund
     */
    private function processRefund($refundId, $payment)
    {
        // In real implementation, integrate with payment gateway refund API
        // For now, simulate refund
        
        return ['success' => true, 'refund_id' => 'REF-' . time()];
    }

    /**
     * Get refunds
     */
    public function getRefunds($restaurantId, $status, $page, $limit)
    {
        $refundModel = new PaymentRefund();
        return $refundModel->getPaginated($restaurantId, $status, $page, $limit);
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods($restaurantId)
    {
        $methodModel = new PaymentMethod();
        return $methodModel->getByRestaurant($restaurantId);
    }

    /**
     * Add payment method
     */
    public function addPaymentMethod($restaurantId, $data)
    {
        $methodModel = new PaymentMethod();
        
        $methodData = [
            'restaurant_id' => $restaurantId,
            'method_name' => $data->method_name,
            'method_type' => $data->method_type,
            'method_code' => $data->method_code,
            'gateway_config' => json_encode($data->gateway_config ?? []),
            'is_active' => $data->is_active ?? true,
            'is_default' => $data->is_default ?? false,
            'display_order' => $data->display_order ?? 0,
            'icon_url' => $data->icon_url ?? null
        ];
        
        $methodId = $methodModel->create($methodData);
        
        if (!$methodId) {
            return ['success' => false, 'message' => 'Failed to add payment method'];
        }
        
        return ['success' => true, 'message' => 'Payment method added', 'method_id' => $methodId];
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod($id, $restaurantId, $data)
    {
        $methodModel = new PaymentMethod();
        $method = $methodModel->findById($id, $restaurantId);
        
        if (!$method) {
            return ['success' => false, 'message' => 'Payment method not found'];
        }
        
        $updateData = [];
        
        if (isset($data->method_name)) {
            $updateData['method_name'] = $data->method_name;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        if (isset($data->is_default)) {
            $updateData['is_default'] = $data->is_default;
        }
        if (isset($data->display_order)) {
            $updateData['display_order'] = $data->display_order;
        }
        if (isset($data->gateway_config)) {
            $updateData['gateway_config'] = json_encode($data->gateway_config);
        }
        
        $updated = $methodModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update payment method'];
        }
        
        return ['success' => true, 'message' => 'Payment method updated'];
    }

    /**
     * Add tip
     */
    public function addTip($restaurantId, $userId, $data)
    {
        $tipModel = new Tip();
        
        $tipData = [
            'restaurant_id' => $restaurantId,
            'order_id' => $data->order_id,
            'payment_id' => $data->payment_id ?? null,
            'tip_amount' => $data->tip_amount,
            'tip_type' => $data->tip_type,
            'tip_percentage' => $data->tip_percentage ?? null,
            'distribution_method' => $data->distribution_method,
            'distribution_config' => json_encode($data->distribution_config ?? []),
            'staff_id' => $data->staff_id ?? null
        ];
        
        $tipId = $tipModel->create($tipData);
        
        if (!$tipId) {
            return ['success' => false, 'message' => 'Failed to add tip'];
        }
        
        return ['success' => true, 'message' => 'Tip added successfully', 'tip_id' => $tipId];
    }

    /**
     * Log transaction
     */
    private function logTransaction($restaurantId, $paymentId, $type, $status, $amount, $errorMessage)
    {
        $sql = "INSERT INTO payment_transaction_logs 
                (restaurant_id, payment_id, transaction_type, transaction_status, amount, processed_at, error_message)
                VALUES (?, ?, ?, ?, ?, NOW(), ?)";
        
        $this->db->query($sql, [$restaurantId, $paymentId, $type, $status, $amount, $errorMessage]);
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND created_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND created_at <= ?";
            $params[] = $dateTo;
        }
        
        // Total payments
        $sql = "SELECT COUNT(*) as total, SUM(amount) as total_amount FROM payments {$where} WHERE payment_status = 'completed'";
        $result = $this->db->query($sql, $params)->fetch();
        
        // Payments by method
        $sql = "SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM payments {$where} WHERE payment_status = 'completed' GROUP BY payment_method";
        $paymentsByMethod = $this->db->query($sql, $params)->fetchAll();
        
        // Refunds
        $sql = "SELECT COUNT(*) as total, SUM(refund_amount) as total_refunded FROM payment_refunds pr LEFT JOIN payments p ON pr.payment_id = p.id WHERE p.restaurant_id = ? AND pr.refund_status = 'completed'";
        $refunds = $this->db->query($sql, [$restaurantId])->fetch();
        
        // Tips
        $sql = "SELECT COUNT(*) as total, SUM(tip_amount) as total_tips FROM tips WHERE restaurant_id = ?";
        $tips = $this->db->query($sql, [$restaurantId])->fetch();
        
        return [
            'total_payments' => $result['total'] ?? 0,
            'total_amount' => $result['total_amount'] ?? 0,
            'payments_by_method' => $paymentsByMethod,
            'total_refunds' => $refunds['total'] ?? 0,
            'total_refunded' => $refunds['total_refunded'] ?? 0,
            'total_tips' => $tips['total'] ?? 0,
            'total_tip_amount' => $tips['total_tips'] ?? 0
        ];
    }
}
