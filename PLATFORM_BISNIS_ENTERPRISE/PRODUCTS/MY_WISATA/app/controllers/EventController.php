<?php
/**
 * MyWisata Application - Event Controller
 * 
 * Handles event browsing and registration.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

// Load required models
require_once APP_ROOT . '/app/models/Event.php';

class EventController extends Controller {
    
    /**
     * Index - List all events
     */
    public function index() {
        $eventModel = new Event();
        
        $filters = [
            'city' => $this->get('city'),
            'search' => $this->get('search'),
            'is_active' => 1,
            'upcoming' => true
        ];
        
        $events = $eventModel->getAllWithFilters($filters);
        $upcoming = $eventModel->getUpcoming(6);
        
        $data = [
            'title' => 'Event & Budaya - MyWisata',
            'events' => $events,
            'upcoming' => $upcoming,
            'filters' => $filters
        ];
        
        $this->view('events/index', $data);
    }
    
    /**
     * Detail - Show event details
     */
    public function detail() {
        $id = $this->get('id');
        $eventModel = new Event();
        
        $event = $eventModel->findById($id);
        
        if (!$event) {
            Session::flash('error', 'Event tidak ditemukan');
            $this->redirect('events');
        }
        
        $reviews = $eventModel->getReviews($id, 10);
        
        $data = [
            'title' => $event['name'] . ' - MyWisata',
            'event' => $event,
            'reviews' => $reviews
        ];
        
        $this->view('events/detail', $data);
    }
    
    /**
     * Add review
     */
    public function addReview() {
        $userId = Session::get('user_id');
        
        if (!$userId) {
            $this->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu'], 401);
        }
        
        $data = [
            'event_id' => $this->post('event_id'),
            'user_id' => $userId,
            'rating' => $this->post('rating'),
            'comment' => $this->post('comment')
        ];
        
        $validator = new Validator($_POST);
        $validator->required(['event_id', 'rating', 'comment'])
                  ->numeric(['rating'])
                  ->in('rating', [1, 2, 3, 4, 5]);
        
        if ($validator->fails()) {
            $this->json(['status' => 'error', 'message' => $validator->firstError()], 400);
        }
        
        $eventModel = new Event();
        $eventModel->addReview($data);
        
        Logger::audit('ADD_EVENT_REVIEW', 'event_reviews', "Added review for event ID: {$data['event_id']}", [], $data);
        
        $this->json(['status' => 'success', 'message' => 'Review berhasil ditambahkan']);
    }
}
