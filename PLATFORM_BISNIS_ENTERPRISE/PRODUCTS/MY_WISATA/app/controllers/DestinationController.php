<?php
/**
 * MyWisata Application - Destination Controller
 * 
 * Handles destination browsing and details.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

// Load required models
require_once APP_ROOT . '/app/models/Destination.php';

class DestinationController extends Controller {
    
    /**
     * Index - List all destinations
     */
    public function index() {
        $destinationModel = new Destination();
        
        $filters = [
            'category_id' => $this->get('category'),
            'city' => $this->get('city'),
            'search' => $this->get('search'),
            'is_active' => 1
        ];
        
        $destinations = $destinationModel->getAllWithFilters($filters);
        $featured = $destinationModel->getFeatured(6);
        $popular = $destinationModel->getPopular(6);
        
        $db = Database::getInstance();
        $categories = $db->query("SELECT * FROM destination_categories ORDER BY name")->fetchAll();
        
        $data = [
            'title' => 'Destinasi Wisata - MyWisata',
            'destinations' => $destinations,
            'featured' => $featured,
            'popular' => $popular,
            'categories' => $categories,
            'filters' => $filters
        ];
        
        $this->view('destinations/index', $data);
    }
    
    /**
     * Detail - Show destination details
     */
    public function detail() {
        $id = $this->get('id');
        $destinationModel = new Destination();
        
        $destination = $destinationModel->findById($id);
        
        if (!$destination) {
            Session::flash('error', 'Destinasi tidak ditemukan');
            $this->redirect('destinations');
        }
        
        $images = $destinationModel->getImages($id);
        $reviews = $destinationModel->getReviews($id, 10);
        
        $db = Database::getInstance();
        $nearby = $destinationModel->getNearby($destination['latitude'], $destination['longitude'], 10);
        
        $data = [
            'title' => $destination['name'] . ' - MyWisata',
            'destination' => $destination,
            'images' => $images,
            'reviews' => $reviews,
            'nearby' => $nearby
        ];
        
        $this->view('destinations/detail', $data);
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
            'destination_id' => $this->post('destination_id'),
            'user_id' => $userId,
            'rating' => $this->post('rating'),
            'comment' => $this->post('comment')
        ];
        
        $validator = new Validator($_POST);
        $validator->required(['destination_id', 'rating', 'comment'])
                  ->numeric(['rating'])
                  ->in('rating', [1, 2, 3, 4, 5]);
        
        if ($validator->fails()) {
            $this->json(['status' => 'error', 'message' => $validator->firstError()], 400);
        }
        
        $destinationModel = new Destination();
        $destinationModel->addReview($data);
        $destinationModel->updateRating($data['destination_id']);
        
        Logger::audit('ADD_DESTINATION_REVIEW', 'destination_reviews', "Added review for destination ID: {$data['destination_id']}", [], $data);
        
        $this->json(['status' => 'success', 'message' => 'Review berhasil ditambahkan']);
    }
}
