<?php
/**
 * MyWisata Application - Audio Guide Controller
 * 
 * Handles audio guide functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class AudioGuideController extends Controller {
    
    /**
     * Index - List available audio guides
     */
    public function index() {
        $db = Database::getInstance();
        
        $audioGuides = $db->query("SELECT ag.*, d.name as destination_name 
                                    FROM audio_guides ag 
                                    LEFT JOIN destinations d ON ag.destination_id = d.id 
                                    WHERE ag.is_active = 1 
                                    ORDER BY ag.created_at DESC")->fetchAll();
        
        $data = [
            'title' => 'Audio Guide - MyWisata',
            'audio_guides' => $audioGuides
        ];
        
        $this->view('audioguide/index', $data);
    }
    
    /**
     * Play - Play audio guide
     */
    public function play() {
        $id = $this->get('id');
        $db = Database::getInstance();
        
        $audioGuide = $db->query("SELECT ag.*, d.name as destination_name 
                                  FROM audio_guides ag 
                                  LEFT JOIN destinations d ON ag.destination_id = d.id 
                                  WHERE ag.id = :id", ['id' => $id])->fetch();
        
        if (!$audioGuide) {
            Session::flash('error', 'Audio guide tidak ditemukan');
            $this->redirect('audioguide');
        }
        
        $data = [
            'title' => $audioGuide['title'] . ' - Audio Guide',
            'audio_guide' => $audioGuide
        ];
        
        $this->view('audioguide/play', $data);
    }
}
