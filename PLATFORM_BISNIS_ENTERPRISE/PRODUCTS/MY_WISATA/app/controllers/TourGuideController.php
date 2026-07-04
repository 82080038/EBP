<?php
/**
 * MyWisata Application - TourGuide Controller
 * 
 * Handles tour guide dashboard and management functions.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

// Load required models
require_once APP_ROOT . '/app/models/TourGuide.php';

class TourGuideController extends Controller {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Index - List all tour guides (public)
     */
    public function index() {
        $tourGuideModel = new TourGuide();
        $guides = $tourGuideModel->getAllWithFilters(['is_verified' => 1, 'is_available' => 1]);
        
        $data = [
            'title' => 'Tour Guide - MyWisata',
            'guides' => $guides
        ];
        
        $this->view('tourguide/index', $data);
    }
    
    /**
     * Dashboard - Main tour guide dashboard (protected)
     */
    public function dashboard() {
        Middleware::requireRole('tour_guide');
        $userId = Session::get('user_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        if (!$guide) {
            Session::flash('error', 'Profil tour guide belum diisi');
            $this->redirect('tourguide/profile');
        }
        
        $db = Database::getInstance();
        
        // Get statistics
        $stats = [
            'active_bookings' => $db->query("SELECT COUNT(*) as count FROM bookings WHERE guide_id = :guide_id AND status = 'confirmed'", ['guide_id' => $guide['id']])->fetch()['count'],
            'pending_bookings' => $db->query("SELECT COUNT(*) as count FROM bookings WHERE guide_id = :guide_id AND status = 'pending'", ['guide_id' => $guide['id']])->fetch()['count'],
            'completed_tours' => $db->query("SELECT COUNT(*) as count FROM bookings WHERE guide_id = :guide_id AND status = 'completed'", ['guide_id' => $guide['id']])->fetch()['count'],
            'rating_avg' => $guide['rating_avg'],
        ];
        
        // Get monthly earnings
        $earnings = $tourGuideModel->getEarnings($guide['id'], 'month');
        
        // Get pending bookings
        $pendingBookings = $tourGuideModel->getBookings($guide['id'], 'pending');
        
        // Get today's bookings
        $todayBookings = $db->query("SELECT b.*, u.name as user_name 
                                      FROM bookings b 
                                      LEFT JOIN users u ON b.user_id = u.id 
                                      WHERE b.guide_id = :guide_id 
                                      AND b.status = 'confirmed' 
                                      AND DATE(b.booking_date) = CURDATE()", 
                                      ['guide_id' => $guide['id']])->fetchAll();
        
        // Get recent reviews
        $recentReviews = $db->query("SELECT gr.*, u.name as user_name 
                                      FROM guide_reviews gr 
                                      LEFT JOIN users u ON gr.user_id = u.id 
                                      WHERE gr.guide_id = :guide_id 
                                      ORDER BY gr.created_at DESC LIMIT 5", 
                                      ['guide_id' => $guide['id']])->fetchAll();
        
        $data = [
            'title' => 'Dashboard Tour Guide - MyWisata',
            'guide' => $guide,
            'stats' => $stats,
            'earnings' => $earnings,
            'pending_bookings' => $pendingBookings,
            'today_bookings' => $todayBookings,
            'recent_reviews' => $recentReviews
        ];
        
        $this->view('tourguide/dashboard', $data);
    }
    
    /**
     * Profile - Edit tour guide profile (protected)
     */
    public function profile() {
        Middleware::requireRole('tour_guide');
        $userId = Session::get('user_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        $data = [
            'title' => 'Profil Tour Guide - MyWisata',
            'guide' => $guide
        ];
        
        $this->view('tourguide/profile', $data);
    }
    
    /**
     * Update profile
     */
    public function updateProfile() {
        $userId = Session::get('user_id');
        
        $data = [
            'user_id' => $userId,
            'name' => $this->post('name'),
            'phone' => $this->post('phone'),
            'bio' => $this->post('bio'),
            'license_number' => $this->post('license_number'),
            'experience_years' => $this->post('experience_years'),
            'hourly_rate' => $this->post('hourly_rate'),
            'daily_rate' => $this->post('daily_rate'),
            'city' => $this->post('city'),
            'latitude' => $this->post('latitude'),
            'longitude' => $this->post('longitude'),
            'is_available' => $this->post('is_available') ? 1 : 0
        ];
        
        $validator = new Validator($_POST);
        $validator->required(['name', 'phone', 'hourly_rate', 'daily_rate'])
                  ->numeric(['hourly_rate', 'daily_rate', 'experience_years']);
        
        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('tourguide/profile');
        }
        
        $tourGuideModel = new TourGuide();
        $guideId = $tourGuideModel->save($data);
        
        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            try {
                $avatar = FileUpload::upload($_FILES['avatar'], APP_ROOT . '/public/uploads/avatars/');
                $db = Database::getInstance();
                $db->query("UPDATE tour_guides SET avatar = :avatar WHERE id = :guide_id", 
                          ['avatar' => $avatar, 'guide_id' => $guideId]);
            } catch (Exception $e) {
                Session::flash('error', 'Gagal upload avatar: ' . $e->getMessage());
            }
        }
        
        Logger::audit('UPDATE_GUIDE_PROFILE', 'tour_guides', "Updated guide profile for user ID: {$userId}", [], $data);
        
        Session::flash('success', 'Profil berhasil diperbarui');
        $this->redirect('tourguide/profile');
    }
    
    /**
     * Skills - Manage languages and specializations
     */
    public function skills() {
        $userId = Session::get('user_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        if (!$guide) {
            Session::flash('error', 'Profil tour guide belum diisi');
            $this->redirect('tourguide/profile');
        }
        
        $db = Database::getInstance();
        
        // Get all languages
        $languages = $db->query("SELECT * FROM languages ORDER BY name")->fetchAll();
        
        // Get all specializations
        $specializations = $db->query("SELECT * FROM specializations ORDER BY name")->fetchAll();
        
        // Get guide's languages
        $guideLanguages = $tourGuideModel->getLanguages($guide['id']);
        
        // Get guide's specializations
        $guideSpecializations = $tourGuideModel->getSpecializations($guide['id']);
        
        $data = [
            'title' => 'Bahasa & Spesialisasi - MyWisata',
            'guide' => $guide,
            'languages' => $languages,
            'specializations' => $specializations,
            'guide_languages' => $guideLanguages,
            'guide_specializations' => $guideSpecializations
        ];
        
        $this->view('tourguide/skills', $data);
    }
    
    /**
     * Add language (protected)
     */
    public function addLanguage() {
        Middleware::requireRole('tour_guide');
        $userId = Session::get('user_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        $languageId = $this->post('language_id');
        $proficiency = $this->post('proficiency');
        
        $tourGuideModel->addLanguage($guide['id'], $languageId, $proficiency);
        
        $this->json(['status' => 'success', 'message' => 'Bahasa berhasil ditambahkan']);
    }
    
    /**
     * Remove language
     */
    public function removeLanguage() {
        $userId = Session::get('user_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        $languageId = $this->post('language_id');
        
        $tourGuideModel->removeLanguage($guide['id'], $languageId);
        
        $this->json(['status' => 'success', 'message' => 'Bahasa berhasil dihapus']);
    }
    
    /**
     * Add specialization (protected)
     */
    public function addSpecialization() {
        Middleware::requireRole('tour_guide');
        $userId = Session::get('user_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        $specializationId = $this->post('specialization_id');
        
        $tourGuideModel->addSpecialization($guide['id'], $specializationId);
        
        $this->json(['status' => 'success', 'message' => 'Spesialisasi berhasil ditambahkan']);
    }
    
    /**
     * Remove specialization
     */
    public function removeSpecialization() {
        $userId = Session::get('user_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        $specializationId = $this->post('specialization_id');
        
        $tourGuideModel->removeSpecialization($guide['id'], $specializationId);
        
        $this->json(['status' => 'success', 'message' => 'Spesialisasi berhasil dihapus']);
    }
    
    /**
     * Bookings - View bookings
     */
    public function bookings() {
        $guide = $tourGuideModel->findByUserId($userId);
        
        if (!$guide) {
            Session::flash('error', 'Profil tour guide belum diisi');
            $this->redirect('tourguide/profile');
        }
        
        $status = $this->get('status', 'all');
        $bookings = $tourGuideModel->getBookings($guide['id'], $status === 'all' ? null : $status);
        
        $data = [
            'title' => 'Booking Tour Guide - MyWisata',
            'guide' => $guide,
            'bookings' => $bookings,
            'status_filter' => $status
        ];
        
        $this->view('tourguide/bookings', $data);
    }
    
    /**
     * Accept booking (protected)
     */
    public function acceptBooking() {
        Middleware::requireRole('tour_guide');
        $bookingId = $this->post('booking_id');
        $userId = Session::get('user_id');
        
        $db = Database::getInstance();
        
        // Verify booking belongs to this guide
        $booking = $db->query("SELECT * FROM bookings WHERE id = :id", ['id' => $bookingId])->fetch();
        
        if (!$booking) {
            $this->json(['status' => 'error', 'message' => 'Booking tidak ditemukan'], 404);
        }
        
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        if ($booking['guide_id'] != $guide['id']) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $db->query("UPDATE bookings SET status = 'confirmed', updated_at = NOW() WHERE id = :id", ['id' => $bookingId]);
        
        Logger::audit('ACCEPT_BOOKING', 'bookings', "Accepted booking ID: {$bookingId}", [], ['booking_id' => $bookingId]);
        
        $this->json(['status' => 'success', 'message' => 'Booking berhasil diterima']);
    }
    
    /**
     * Reject booking (protected)
     */
    public function rejectBooking() {
        Middleware::requireRole('tour_guide');
        $bookingId = $this->post('booking_id');
        $reason = $this->post('reason');
        $userId = Session::get('user_id');
        
        $db = Database::getInstance();
        
        // Verify booking belongs to this guide
        $booking = $db->query("SELECT * FROM bookings WHERE id = :id", ['id' => $bookingId])->fetch();
        
        if (!$booking) {
            $this->json(['status' => 'error', 'message' => 'Booking tidak ditemukan'], 404);
        }
        
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        if ($booking['guide_id'] != $guide['id']) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $db->query("UPDATE bookings SET status = 'rejected', rejection_reason = :reason, updated_at = NOW() WHERE id = :id", 
                  ['id' => $bookingId, 'reason' => $reason]);
        
        Logger::audit('REJECT_BOOKING', 'bookings', "Rejected booking ID: {$bookingId}", [], ['booking_id' => $bookingId, 'reason' => $reason]);
        
        $this->json(['status' => 'success', 'message' => 'Booking berhasil ditolak']);
    }
    
    /**
     * Earnings - View earnings (protected)
     */
    public function earnings() {
        Middleware::requireRole('tour_guide');
        $userId = Session::get('user_id');
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->findByUserId($userId);
        
        if (!$guide) {
            Session::flash('error', 'Profil tour guide belum diisi');
            $this->redirect('tourguide/profile');
        }
        
        $monthlyEarnings = $tourGuideModel->getEarnings($guide['id'], 'month');
        $yearlyEarnings = $tourGuideModel->getEarnings($guide['id'], 'year');
        $totalEarnings = $tourGuideModel->getEarnings($guide['id'], 'all');
        
        $db = Database::getInstance();
        $transactions = $db->query("SELECT t.*, b.booking_code 
                                     FROM transactions t 
                                     LEFT JOIN bookings b ON t.booking_id = b.id 
                                     WHERE t.guide_id = :guide_id 
                                     AND t.payment_status = 'paid' 
                                     ORDER BY t.created_at DESC LIMIT 20", 
                                     ['guide_id' => $guide['id']])->fetchAll();
        
        $data = [
            'title' => 'Pendapatan Tour Guide - MyWisata',
            'guide' => $guide,
            'monthly_earnings' => $monthlyEarnings,
            'yearly_earnings' => $yearlyEarnings,
            'total_earnings' => $totalEarnings,
            'transactions' => $transactions
        ];
        
        $this->view('tourguide/earnings', $data);
    }
}
