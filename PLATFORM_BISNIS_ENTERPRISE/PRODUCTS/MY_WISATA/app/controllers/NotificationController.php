<?php
/**
 * MyWisata Application - Notification Controller
 * 
 * Handles notification functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class NotificationController extends Controller {
    
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
     * Index - List user notifications
     */
    public function index() {
        $userId = Session::get('user_id');
        $db = Database::getInstance();
        
        $notifications = $db->query("SELECT n.* 
                                     FROM notifications n 
                                     WHERE n.user_id = :user_id 
                                     ORDER BY n.is_read ASC, n.created_at DESC 
                                     LIMIT 50", 
                                     ['user_id' => $userId])->fetchAll();
        
        // Mark all as read
        $db->query("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id", ['user_id' => $userId]);
        
        $data = [
            'title' => 'Notifikasi - MyWisata',
            'notifications' => $notifications
        ];
        
        $this->view('notifications/index', $data);
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount() {
        $userId = Session::get('user_id');
        $db = Database::getInstance();
        
        $count = $db->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0", 
                            ['user_id' => $userId])->fetch()['count'];
        
        $this->json(['status' => 'success', 'count' => $count]);
    }
    
    /**
     * Mark as read
     */
    public function markAsRead() {
        $notificationId = $this->post('notification_id');
        $userId = Session::get('user_id');
        
        $db = Database::getInstance();
        $db->query("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id", 
                  ['id' => $notificationId, 'user_id' => $userId]);
        
        $this->json(['status' => 'success']);
    }
}
