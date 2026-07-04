<?php
/**
 * MyWisata Application - AI Tour Guide Controller
 * 
 * Handles AI chat functionality for tour guidance.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class AITourGuideController extends Controller {
    
    /**
     * Index - Show AI chat interface
     */
    public function index() {
        $data = [
            'title' => 'AI Tour Guide - MyWisata'
        ];
        
        $this->view('aitourguide/index', $data);
    }
    
    /**
     * Chat - Process AI chat message
     */
    public function chat() {
        $message = $this->post('message');
        $userId = Session::get('user_id');
        
        if (empty($message)) {
            $this->json(['status' => 'error', 'message' => 'Message cannot be empty'], 400);
        }
        
        // Simple AI response (in production, integrate with actual AI API)
        $response = $this->generateAIResponse($message);
        
        // Log the conversation
        $db = Database::getInstance();
        $db->query("INSERT INTO ai_conversations (user_id, user_message, ai_response, created_at) 
                    VALUES (:user_id, :user_message, :ai_response, NOW())", 
                    ['user_id' => $userId, 'user_message' => $message, 'ai_response' => $response]);
        
        $this->json(['status' => 'success', 'response' => $response]);
    }
    
    /**
     * Generate AI response (placeholder - integrate with actual AI API)
     */
    private function generateAIResponse($message) {
        $message = strtolower($message);
        
        // Simple keyword-based responses
        if (strpos($message, 'destinasi') !== false || strpos($message, 'wisata') !== false) {
            return "Untuk destinasi wisata terbaik, saya merekomendasikan Bali, Yogyakarta, dan Raja Ampat. Destinasi mana yang ingin Anda ketahui lebih lanjut?";
        }
        
        if (strpos($message, 'hotel') !== false || strpos($message, 'penginapan') !== false) {
            return "Kami memiliki berbagai pilihan hotel mulai dari budget hingga luxury. Apakah Anda memiliki preferensi lokasi atau harga?";
        }
        
        if (strpos($message, 'makanan') !== false || strpos($message, 'kuliner') !== false || strpos($message, 'restoran') !== false) {
            return "Indonesia memiliki kuliner yang beragam seperti Rendang, Nasi Goreng, dan Sate. Apakah Anda ingin rekomendasi restoran di kota tertentu?";
        }
        
        if (strpos($message, 'tour guide') !== false || strpos($message, 'pemandu') !== false) {
            return "Kami menyediakan layanan tour guide profesional yang berbicara berbagai bahasa. Apakah Anda ingin mencari tour guide untuk destinasi tertentu?";
        }
        
        if (strpos($message, 'harga') !== false || strpos($message, 'biaya') !== false) {
            return "Harga bervariasi tergantung layanan yang Anda pilih. Tiket destinasi mulai dari Rp 50.000, tour guide mulai dari Rp 150.000 per jam. Apakah Anda ingin detail harga layanan tertentu?";
        }
        
        // Default response
        return "Terima kasih atas pertanyaan Anda. Saya adalah AI Tour Guide yang dapat membantu Anda merencanakan perjalanan wisata. Silakan tanyakan tentang destinasi, hotel, restoran, atau layanan tour guide.";
    }
}
