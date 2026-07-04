<?php
/**
 * MyWisata Application - Home Controller
 * 
 * Default controller for the application.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class HomeController extends Controller {
    
    /**
     * Index action - Default page
     */
    public function index() {
        $data = [
            'title' => 'MyWisata - Platform Marketplace Pariwisata',
            'description' => 'Temukan tour guide profesional, destinasi wisata, hotel, restoran, dan event budaya di Indonesia.',
        ];
        
        $this->view('home/index', $data);
    }
    
    /**
     * About page
     */
    public function about() {
        $data = [
            'title' => 'Tentang MyWisata',
        ];
        
        $this->view('home/about', $data);
    }
    
    /**
     * Contact page
     */
    public function contact() {
        $data = [];
        
        $this->view('home/contact', $data);
    }
}
