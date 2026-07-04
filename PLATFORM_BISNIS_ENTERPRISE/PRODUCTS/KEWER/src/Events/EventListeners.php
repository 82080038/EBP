<?php
namespace Kewer\Events;

use Kewer\Logging\Logger;

class EventListeners {
    
    /**
     * Register all event listeners
     */
    public static function register() {
        // Loan events
        EventDispatcher::listen('loan.created', [self::class, 'onLoanCreated']);
        EventDispatcher::listen('loan.approved', [self::class, 'onLoanApproved']);
        EventDispatcher::listen('loan.rejected', [self::class, 'onLoanRejected']);
        EventDispatcher::listen('loan.paid', [self::class, 'onLoanPaid']);
        
        // Customer events
        EventDispatcher::listen('customer.created', [self::class, 'onCustomerCreated']);
        EventDispatcher::listen('customer.updated', [self::class, 'onCustomerUpdated']);
        
        // Payment events
        EventDispatcher::listen('payment.received', [self::class, 'onPaymentReceived']);
        EventDispatcher::listen('payment.late', [self::class, 'onPaymentLate']);
        
        // User events
        EventDispatcher::listen('user.login', [self::class, 'onUserLogin']);
        EventDispatcher::listen('user.logout', [self::class, 'onUserLogout']);
    }
    
    /**
     * Handle loan created event
     */
    public static function onLoanCreated($payload) {
        Logger::info('Loan Created', [
            'loan_id' => $payload['id'] ?? null,
            'customer_id' => $payload['nasabah_id'] ?? null,
            'amount' => $payload['plafon'] ?? null,
            'user_id' => $payload['user_id'] ?? null
        ]);
        
        // Additional logic: Send notification, update statistics, etc.
    }
    
    /**
     * Handle loan approved event
     */
    public static function onLoanApproved($payload) {
        Logger::info('Loan Approved', [
            'loan_id' => $payload['id'] ?? null,
            'approved_by' => $payload['approved_by'] ?? null,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        
        // Generate installment schedule
        // Send notification to customer
        // Update branch statistics
    }
    
    /**
     * Handle loan rejected event
     */
    public static function onLoanRejected($payload) {
        Logger::info('Loan Rejected', [
            'loan_id' => $payload['id'] ?? null,
            'rejected_by' => $payload['rejected_by'] ?? null,
            'reason' => $payload['reason'] ?? null
        ]);
        
        // Send notification to customer
        // Update loan status
    }
    
    /**
     * Handle loan paid event
     */
    public static function onLoanPaid($payload) {
        Logger::info('Loan Paid', [
            'loan_id' => $payload['loan_id'] ?? null,
            'payment_id' => $payload['payment_id'] ?? null,
            'amount' => $payload['amount'] ?? null
        ]);
        
        // Update loan status if fully paid
        // Send confirmation notification
    }
    
    /**
     * Handle customer created event
     */
    public static function onCustomerCreated($payload) {
        Logger::info('Customer Created', [
            'customer_id' => $payload['id'] ?? null,
            'name' => $payload['nama'] ?? null,
            'created_by' => $payload['user_id'] ?? null
        ]);
        
        // Send welcome notification
        // Update customer statistics
    }
    
    /**
     * Handle customer updated event
     */
    public static function onCustomerUpdated($payload) {
        Logger::info('Customer Updated', [
            'customer_id' => $payload['id'] ?? null,
            'updated_by' => $payload['user_id'] ?? null
        ]);
    }
    
    /**
     * Handle payment received event
     */
    public static function onPaymentReceived($payload) {
        Logger::info('Payment Received', [
            'payment_id' => $payload['id'] ?? null,
            'amount' => $payload['jumlah'] ?? null,
            'customer_id' => $payload['customer_id'] ?? null
        ]);
        
        // Update installment status
        // Send payment confirmation
        // Update branch cash balance
    }
    
    /**
     * Handle payment late event
     */
    public static function onPaymentLate($payload) {
        Logger::warning('Payment Late', [
            'installment_id' => $payload['installment_id'] ?? null,
            'due_date' => $payload['due_date'] ?? null,
            'days_late' => $payload['days_late'] ?? null
        ]);
        
        // Calculate late fee
        // Send late payment notification
        // Update customer risk score
    }
    
    /**
     * Handle user login event
     */
    public static function onUserLogin($payload) {
        Logger::info('User Login', [
            'user_id' => $payload['user_id'] ?? null,
            'username' => $payload['username'] ?? null,
            'ip' => $payload['ip'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        // Update last login time
        // Check for suspicious activity
    }
    
    /**
     * Handle user logout event
     */
    public static function onUserLogout($payload) {
        Logger::info('User Logout', [
            'user_id' => $payload['user_id'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
?>
