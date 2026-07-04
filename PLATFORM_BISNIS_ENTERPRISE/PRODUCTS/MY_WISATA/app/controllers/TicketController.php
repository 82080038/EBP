<?php
/**
 * MyWisata Application - Ticket Controller
 * 
 * Handles destination ticket operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class TicketController extends Controller {
    
    /**
     * Constructor - Require wisatawan role
     */
    public function __construct() {
        parent::__construct();
        Middleware::requireRole('wisatawan');
    }
    
    /**
     * Index - List user tickets
     */
    public function index() {
        $userId = Session::get('user_id');
        $ticketModel = new Ticket();
        
        $status = $this->get('status', 'all');
        $tickets = $ticketModel->getByUserId($userId, $status === 'all' ? null : $status);
        
        $data = [
            'title' => 'Tiket Saya - MyWisata',
            'tickets' => $tickets,
            'status_filter' => $status
        ];
        
        $this->view('tickets/index', $data);
    }
    
    /**
     * Create - Show ticket purchase form
     */
    public function create() {
        $destinationId = $this->get('destination_id');
        $destinationModel = new Destination();
        $destination = $destinationModel->findById($destinationId);
        
        if (!$destination) {
            Session::flash('error', 'Destinasi tidak ditemukan');
            $this->redirect('destinations');
        }
        
        $data = [
            'title' => 'Beli Tiket - MyWisata',
            'destination' => $destination
        ];
        
        $this->view('tickets/create', $data);
    }
    
    /**
     * Store - Create new ticket order
     */
    public function store() {
        $userId = Session::get('user_id');
        
        $destinationModel = new Destination();
        $destination = $destinationModel->findById($this->post('destination_id'));
        
        $quantity = $this->post('quantity');
        $totalAmount = $quantity * $destination['entry_fee'];
        
        $data = [
            'order_code' => 'TK' . date('YmdHis') . rand(1000, 9999),
            'user_id' => $userId,
            'destination_id' => $this->post('destination_id'),
            'quantity' => $quantity,
            'unit_price' => $destination['entry_fee'],
            'total_amount' => $totalAmount,
            'visit_date' => $this->post('visit_date')
        ];
        
        $validator = new Validator($_POST);
        $validator->required(['destination_id', 'quantity', 'visit_date'])
                  ->numeric(['quantity'])
                  ->date('visit_date');
        
        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('ticket/create?destination_id=' . $data['destination_id']);
        }
        
        $ticketModel = new Ticket();
        $ticketId = $ticketModel->create($data);
        
        // Create transaction
        $transactionModel = new Transaction();
        $transactionData = [
            'transaction_code' => 'TX' . date('YmdHis') . rand(1000, 9999),
            'user_id' => $userId,
            'booking_id' => null,
            'type' => 'ticket',
            'gross_amount' => $totalAmount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'net_amount' => $totalAmount,
            'payment_method' => 'pending'
        ];
        $transactionModel->create($transactionData);
        
        Logger::audit('CREATE_TICKET_ORDER', 'ticket_orders', "Created ticket order ID: {$ticketId}", [], $data);
        
        Session::flash('success', 'Tiket berhasil dipesan. Silakan lanjutkan pembayaran.');
        $this->redirect('tickets/index');
    }
}
