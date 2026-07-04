<?php
/**
 * MyWisata Application - Restaurant Controller
 * 
 * Handles restaurant browsing and ordering.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

// Load required models
require_once APP_ROOT . '/app/models/Restaurant.php';

class RestaurantController extends Controller {
    
    /**
     * Index - List all restaurants
     */
    public function index() {
        $restaurantModel = new Restaurant();
        
        $filters = [
            'city' => $this->get('city'),
            'search' => $this->get('search'),
            'is_approved' => 1
        ];
        
        $restaurants = $restaurantModel->getAllWithFilters($filters);
        
        $data = [
            'title' => 'Restoran & UMKM - MyWisata',
            'restaurants' => $restaurants,
            'filters' => $filters
        ];
        
        $this->view('restaurants/index', $data);
    }
    
    /**
     * Detail - Show restaurant details
     */
    public function detail() {
        $id = $this->get('id');
        $restaurantModel = new Restaurant();
        
        $restaurant = $restaurantModel->findById($id);
        
        if (!$restaurant) {
            Session::flash('error', 'Restoran tidak ditemukan');
            $this->redirect('restaurants');
        }
        
        $menuItems = $restaurantModel->getMenuItems($id);
        $reviews = $restaurantModel->getReviews($id, 10);
        
        $data = [
            'title' => $restaurant['name'] . ' - MyWisata',
            'restaurant' => $restaurant,
            'menu_items' => $menuItems,
            'reviews' => $reviews
        ];
        
        $this->view('restaurants/detail', $data);
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
            'restaurant_id' => $this->post('restaurant_id'),
            'user_id' => $userId,
            'rating' => $this->post('rating'),
            'comment' => $this->post('comment')
        ];
        
        $validator = new Validator($_POST);
        $validator->required(['restaurant_id', 'rating', 'comment'])
                  ->numeric(['rating'])
                  ->in('rating', [1, 2, 3, 4, 5]);
        
        if ($validator->fails()) {
            $this->json(['status' => 'error', 'message' => $validator->firstError()], 400);
        }
        
        $restaurantModel = new Restaurant();
        $restaurantModel->addReview($data);
        
        Logger::audit('ADD_RESTAURANT_REVIEW', 'restaurant_reviews', "Added review for restaurant ID: {$data['restaurant_id']}", [], $data);
        
        $this->json(['status' => 'success', 'message' => 'Review berhasil ditambahkan']);
    }
}
