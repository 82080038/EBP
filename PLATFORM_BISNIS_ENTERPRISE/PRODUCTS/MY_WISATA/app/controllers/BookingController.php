<?php
/**
 * MyWisata Application - Booking Controller
 * 
 * Handles tour guide booking operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class BookingController extends Controller {
    
    /**
     * Constructor - Require wisatawan role
     */
    public function __construct() {
        parent::__construct();
        Middleware::requireRole('wisatawan');
    }
    
    /**
     * Index - List user bookings
     */
    public function index() {
        $userId = Session::get('user_id');
        $bookingModel = new Booking();
        
        $status = $this->get('status', 'all');
        $bookings = $bookingModel->getByUserId($userId, $status === 'all' ? null : $status);
        
        $data = [
            'title' => 'Booking Saya - MyWisata',
            'bookings' => $bookings,
            'status_filter' => $status
        ];
        
        $this->view('bookings/index', $data);
    }
    
    /**
     * Create - Show booking form
     */
    public function create() {
        $guideId = $this->get('guide_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findById($guideId);
        
        if (!$guide) {
            Session::flash('error', 'Tour guide tidak ditemukan');
            $this->redirect('home');
        }
        
        $data = [
            'title' => 'Booking Tour Guide - MyWisata',
            'guide' => $guide
        ];
        
        $this->view('bookings/create', $data);
    }
    
    /**
     * Store - Create new booking
     */
    public function store() {
        $userId = Session::get('user_id');
        
        $data = [
            'booking_code' => 'BK' . date('YmdHis') . rand(1000, 9999),
            'user_id' => $userId,
            'guide_id' => $this->post('guide_id'),
            'booking_date' => $this->post('booking_date'),
            'booking_time' => $this->post('booking_time'),
            'duration_hours' => $this->post('duration_hours'),
            'participants' => $this->post('participants'),
            'special_requests' => $this->post('special_requests'),
            'total_amount' => $this->post('total_amount')
        ];
        
        $validator = new Validator($_POST);
        $validator->required(['guide_id', 'booking_date', 'booking_time', 'duration_hours', 'participants'])
                  ->numeric(['duration_hours', 'participants', 'total_amount'])
                  ->date('booking_date');
        
        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('booking/create?guide_id=' . $data['guide_id']);
        }
        
        $bookingModel = new Booking();
        $bookingId = $bookingModel->create($data);
        
        // Create transaction
        $transactionModel = new Transaction();
        $transactionData = [
            'transaction_code' => 'TX' . date('YmdHis') . rand(1000, 9999),
            'user_id' => $userId,
            'booking_id' => $bookingId,
            'type' => 'booking_guide',
            'gross_amount' => $data['total_amount'],
            'discount_amount' => 0,
            'tax_amount' => 0,
            'net_amount' => $data['total_amount'],
            'payment_method' => 'pending'
        ];
        $transactionModel->create($transactionData);
        
        Logger::audit('CREATE_BOOKING', 'bookings', "Created booking ID: {$bookingId}", [], $data);
        
        Session::flash('success', 'Booking berhasil dibuat. Silakan lanjutkan pembayaran.');
        $this->redirect('bookings/index');
    }
    
    /**
     * Cancel booking
     */
    public function cancel() {
        $bookingId = $this->post('booking_id');
        $reason = $this->post('reason');
        $userId = Session::get('user_id');
        
        $bookingModel = new Booking();
        $booking = $bookingModel->findById($bookingId);
        
        if (!$booking || $booking['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $bookingModel->cancel($bookingId, $reason);
        
        Logger::audit('CANCEL_BOOKING', 'bookings', "Cancelled booking ID: {$bookingId}", [], ['reason' => $reason]);
        
        $this->json(['status' => 'success', 'message' => 'Booking berhasil dibatalkan']);
    }
}
