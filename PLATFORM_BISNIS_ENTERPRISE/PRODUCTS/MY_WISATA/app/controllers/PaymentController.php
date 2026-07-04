<?php
/**
 * MyWisata Application - Payment Controller
 * 
 * Handles payment processing.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class PaymentController extends Controller {
    
    /**
     * Constructor - Require login
     */
    public function __construct() {
        parent::__construct();
        if (!Session::get('user_id')) {
            $this->redirect('auth/login');
        }
    }
    
    /**
     * Index - Show payment form
     */
    public function index() {
        $transactionId = $this->get('transaction_id');
        $transactionModel = new Transaction();
        $transaction = $transactionModel->findById($transactionId);
        
        if (!$transaction || $transaction['user_id'] != Session::get('user_id')) {
            Session::flash('error', 'Transaksi tidak ditemukan');
            $this->redirect('home');
        }
        
        $data = [
            'title' => 'Pembayaran - MyWisata',
            'transaction' => $transaction
        ];
        
        $this->view('payment/index', $data);
    }
    
    /**
     * Process payment
     */
    public function process() {
        $transactionId = $this->post('transaction_id');
        $paymentMethod = $this->post('payment_method');
        $userId = Session::get('user_id');
        
        $transactionModel = new Transaction();
        $transaction = $transactionModel->findById($transactionId);
        
        if (!$transaction || $transaction['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
        }
        
        if ($transaction['payment_status'] === 'paid') {
            $this->json(['status' => 'error', 'message' => 'Transaksi sudah dibayar'], 400);
        }
        
        // Update transaction
        $transactionModel->updatePaymentStatus($transactionId, 'paid');
        $transactionModel->updatePaymentMethod($transactionId, $paymentMethod);
        
        // Update related booking/ticket status
        if ($transaction['type'] === 'booking_guide' && $transaction['booking_id']) {
            $bookingModel = new Booking();
            $bookingModel->updateStatus($transaction['booking_id'], 'confirmed');
        }
        
        Logger::audit('PROCESS_PAYMENT', 'transactions', "Processed payment for transaction ID: {$transactionId}", [], ['payment_method' => $paymentMethod]);
        
        $this->json(['status' => 'success', 'message' => 'Pembayaran berhasil']);
    }
}
