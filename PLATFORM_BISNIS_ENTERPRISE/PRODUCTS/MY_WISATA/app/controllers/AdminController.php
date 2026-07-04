<?php
/**
 * MyWisata Application - Admin Controller
 * 
 * Handles administrator dashboard and management functions.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class AdminController extends Controller {
    
    /**
     * Constructor - Require admin role
     */
    public function __construct() {
        parent::__construct();
        Middleware::requireRole('admin');
    }
    
    /**
     * Dashboard - Main admin dashboard
     */
    public function dashboard() {
        $db = Database::getInstance();
        
        // Get statistics
        $stats = [
            'total_users' => $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch()['count'],
            'total_guides' => $db->query("SELECT COUNT(*) as count FROM tour_guides WHERE is_verified = 1")->fetch()['count'],
            'total_destinations' => $db->query("SELECT COUNT(*) as count FROM destinations WHERE is_active = 1")->fetch()['count'],
            'total_transactions' => $db->query("SELECT COUNT(*) as count FROM transactions WHERE payment_status = 'paid'")->fetch()['count'],
            'pending_guides' => $db->query("SELECT COUNT(*) as count FROM tour_guides WHERE is_verified = 0")->fetch()['count'],
            'pending_hotels' => $db->query("SELECT COUNT(*) as count FROM hotels WHERE is_approved = 0")->fetch()['count'],
            'pending_restaurants' => $db->query("SELECT COUNT(*) as count FROM restaurants WHERE is_approved = 0")->fetch()['count'],
        ];
        
        // Get monthly revenue
        $monthly_revenue = $db->query("SELECT COALESCE(SUM(net_amount), 0) as total FROM transactions 
                                       WHERE payment_status = 'paid' 
                                       AND MONTH(created_at) = MONTH(CURRENT_DATE) 
                                       AND YEAR(created_at) = YEAR(CURRENT_DATE)")->fetch()['total'];
        
        // Get recent bookings
        $recent_bookings = $db->query("SELECT b.*, u.name as user_name, tg.user_id as guide_id 
                                       FROM bookings b 
                                       LEFT JOIN users u ON b.user_id = u.id 
                                       LEFT JOIN tour_guides tg ON b.guide_id = tg.id 
                                       ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
        
        $data = [
            'title' => 'Dashboard Admin - MyWisata',
            'stats' => $stats,
            'monthly_revenue' => $monthly_revenue,
            'recent_bookings' => $recent_bookings
        ];
        
        $this->view('admin/dashboard', $data);
    }
    
    /**
     * Users management - List all users
     */
    public function users() {
        $db = Database::getInstance();
        
        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $search = $this->get('search', '');
        $role_filter = $this->get('role', '');
        $status_filter = $this->get('status', '');
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(name LIKE :search OR email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        
        if (!empty($role_filter)) {
            $where[] = "role = :role";
            $params['role'] = $role_filter;
        }
        
        if (!empty($status_filter)) {
            $where[] = "status = :status";
            $params['status'] = $status_filter;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $users = $db->query("SELECT * FROM users WHERE {$whereClause} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}", $params)->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM users WHERE {$whereClause}", $params)->fetch()['count'];
        
        $data = [
            'title' => 'Manajemen Pengguna - MyWisata',
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'role_filter' => $role_filter,
            'status_filter' => $status_filter
        ];
        
        $this->view('admin/users/index', $data);
    }
    
    /**
     * Edit user
     */
    public function editUser() {
        $id = $this->get('id');
        
        if (!$id) {
            $this->redirect('admin/users');
        }
        
        $db = Database::getInstance();
        $user = $db->query("SELECT * FROM users WHERE id = :id", ['id' => $id])->fetch();
        
        if (!$user) {
            Session::flash('error', 'Pengguna tidak ditemukan');
            $this->redirect('admin/users');
        }
        
        $data = [
            'title' => 'Edit Pengguna - MyWisata',
            'user' => $user
        ];
        
        $this->view('admin/users/edit', $data);
    }
    
    /**
     * Update user
     */
    public function updateUser() {
        $id = $this->post('id');
        $name = $this->post('name');
        $email = $this->post('email');
        $phone = $this->post('phone');
        $role = $this->post('role');
        $status = $this->post('status');
        
        $validator = new Validator($_POST);
        $validator->required(['id', 'name', 'email', 'role', 'status'])
                  ->email('email');
        
        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('admin/users/edit?id=' . $id);
        }
        
        $db = Database::getInstance();
        $db->query("UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, status = :status WHERE id = :id",
                   ['name' => $name, 'email' => $email, 'phone' => $phone, 'role' => $role, 'status' => $status, 'id' => $id]);
        
        Logger::audit('UPDATE_USER', 'users', "Updated user ID: {$id}", [], ['id' => $id, 'name' => $name, 'role' => $role, 'status' => $status]);
        
        Session::flash('success', 'Pengguna berhasil diperbarui');
        $this->redirect('admin/users');
    }
    
    /**
     * Tour guides management
     */
    public function guides() {
        $db = Database::getInstance();
        
        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $guides = $db->query("SELECT tg.*, u.name, u.email, u.phone 
                             FROM tour_guides tg 
                             LEFT JOIN users u ON tg.user_id = u.id 
                             ORDER BY tg.created_at DESC LIMIT {$limit} OFFSET {$offset}")->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM tour_guides")->fetch()['count'];
        
        $data = [
            'title' => 'Manajemen Tour Guide - MyWisata',
            'guides' => $guides,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
        
        $this->view('admin/guides/index', $data);
    }
    
    /**
     * Approve tour guide
     */
    public function approveGuide() {
        $id = $this->post('id');
        
        if (!$id) {
            $this->json(['status' => 'error', 'message' => 'ID tidak valid'], 400);
        }
        
        $db = Database::getInstance();
        $db->query("UPDATE tour_guides SET is_verified = 1, verified_at = NOW(), verified_by = :admin_id WHERE id = :id",
                   ['id' => $id, 'admin_id' => Middleware::userId()]);
        
        Logger::audit('APPROVE_GUIDE', 'tour_guides', "Approved guide ID: {$id}", [], ['id' => $id]);
        
        $this->json(['status' => 'success', 'message' => 'Tour guide berhasil disetujui']);
    }
    
    /**
     * Destinations management
     */
    public function destinations() {
        $db = Database::getInstance();
        
        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $destinations = $db->query("SELECT d.*, dc.name as category_name 
                                    FROM destinations d 
                                    LEFT JOIN destination_categories dc ON d.category_id = dc.id 
                                    ORDER BY d.created_at DESC LIMIT {$limit} OFFSET {$offset}")->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM destinations")->fetch()['count'];
        
        $data = [
            'title' => 'Manajemen Destinasi - MyWisata',
            'destinations' => $destinations,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
        
        $this->view('admin/destinations/index', $data);
    }
    
    /**
     * Settings
     */
    public function settings() {
        $db = Database::getInstance();
        
        if ($this->post()) {
            foreach ($_POST as $key => $value) {
                if ($key !== 'csrf_token') {
                    $db->query("INSERT INTO settings (key_name, value, updated_at) VALUES (:key, :value, NOW()) 
                               ON DUPLICATE KEY UPDATE value = :value, updated_at = NOW()",
                               ['key' => $key, 'value' => $value]);
                }
            }
            
            Logger::audit('UPDATE_SETTINGS', 'settings', 'Updated system settings');
            Session::flash('success', 'Pengaturan berhasil disimpan');
            $this->redirect('admin/settings');
        }
        
        $settings = $db->query("SELECT * FROM settings ORDER BY key_name")->fetchAll();
        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['key_name']] = $setting['value'];
        }
        
        $data = [
            'title' => 'Pengaturan - MyWisata',
            'settings' => $settingsArray
        ];
        
        $this->view('admin/settings', $data);
    }
}
