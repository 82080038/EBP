<?php
/**
 * MyWisata Application - Map Controller
 * 
 * Handles map and GPS functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class MapController extends Controller {
    
    /**
     * Index - Show map with all destinations
     */
    public function index() {
        $destinationModel = new Destination();
        $destinations = $destinationModel->getAll(['is_active' => 1]);
        
        $data = [
            'title' => 'Peta Destinasi - MyWisata',
            'destinations' => $destinations
        ];
        
        $this->view('map/index', $data);
    }
    
    /**
     * Get destinations as JSON for map
     */
    public function getDestinations() {
        $destinationModel = new Destination();
        $destinations = $destinationModel->getAll(['is_active' => 1]);
        
        $markers = [];
        foreach ($destinations as $dest) {
            $markers[] = [
                'id' => $dest['id'],
                'name' => $dest['name'],
                'latitude' => $dest['latitude'],
                'longitude' => $dest['longitude'],
                'city' => $dest['city'],
                'category' => $dest['category_name'],
                'rating' => $dest['rating_avg'],
                'image' => $dest['main_image'] ? View::asset('uploads/destinations/' . $dest['main_image']) : null
            ];
        }
        
        $this->json(['status' => 'success', 'markers' => $markers]);
    }
    
    /**
     * Get nearby destinations
     */
    public function getNearby() {
        $latitude = $this->get('lat');
        $longitude = $this->get('lng');
        $radius = $this->get('radius', 10);
        
        $destinationModel = new Destination();
        $nearby = $destinationModel->getNearby($latitude, $longitude, $radius);
        
        $markers = [];
        foreach ($nearby as $dest) {
            $markers[] = [
                'id' => $dest['id'],
                'name' => $dest['name'],
                'latitude' => $dest['latitude'],
                'longitude' => $dest['longitude'],
                'city' => $dest['city'],
                'category' => $dest['category_name'],
                'rating' => $dest['rating_avg'],
                'distance' => $dest['distance']
            ];
        }
        
        $this->json(['status' => 'success', 'markers' => $markers]);
    }
}
