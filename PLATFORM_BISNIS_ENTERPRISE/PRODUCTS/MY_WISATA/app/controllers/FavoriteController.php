<?php
/**
 * MyWisata Application - Favorite Controller
 * 
 * Handles user favorites.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class FavoriteController extends Controller {
    
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
     * Index - List user favorites
     */
    public function index() {
        $userId = Session::get('user_id');
        $favoriteModel = new Favorite();
        
        $itemType = $this->get('type', 'all');
        $favorites = $favoriteModel->getUserFavorites($userId, $itemType === 'all' ? null : $itemType);
        
        $data = [
            'title' => 'Favorit Saya - MyWisata',
            'favorites' => $favorites,
            'type_filter' => $itemType
        ];
        
        $this->view('favorites/index', $data);
    }
    
    /**
     * Add to favorites
     */
    public function add() {
        $userId = Session::get('user_id');
        $itemType = $this->post('item_type');
        $itemId = $this->post('item_id');
        
        $favoriteModel = new Favorite();
        $favoriteModel->add($userId, $itemType, $itemId);
        
        Logger::audit('ADD_FAVORITE', 'user_favorites', "Added favorite: {$itemType} ID: {$itemId}");
        
        $this->json(['status' => 'success', 'message' => 'Ditambahkan ke favorit']);
    }
    
    /**
     * Remove from favorites
     */
    public function remove() {
        $userId = Session::get('user_id');
        $itemType = $this->post('item_type');
        $itemId = $this->post('item_id');
        
        $favoriteModel = new Favorite();
        $favoriteModel->remove($userId, $itemType, $itemId);
        
        Logger::audit('REMOVE_FAVORITE', 'user_favorites', "Removed favorite: {$itemType} ID: {$itemId}");
        
        $this->json(['status' => 'success', 'message' => 'Dihapus dari favorit']);
    }
}
