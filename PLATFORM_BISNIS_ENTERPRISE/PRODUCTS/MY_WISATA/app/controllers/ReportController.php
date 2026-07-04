<?php
/**
 * MyWisata Application - Report Controller
 * 
 * Handles reporting and analytics.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class ReportController extends Controller {
    
    /**
     * Constructor - Require admin role
     */
    public function __construct() {
        parent::__construct();
        Middleware::requireRole('admin');
    }
    
    /**
     * Index - Show report dashboard
     */
    public function index() {
        $db = Database::getInstance();
        
        // Get overall statistics
        $stats = [
            'total_users' => $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'],
            'total_bookings' => $db->query("SELECT COUNT(*) as count FROM bookings")->fetch()['count'],
            'total_tickets' => $db->query("SELECT COUNT(*) as count FROM ticket_orders")->fetch()['count'],
            'total_revenue' => $db->query("SELECT COALESCE(SUM(net_amount), 0) as total FROM transactions WHERE payment_status = 'paid'")->fetch()['total'],
        ];
        
        // Get monthly revenue
        $monthlyRevenue = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(net_amount) as revenue 
                                       FROM transactions 
                                       WHERE payment_status = 'paid' 
                                       GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                                       ORDER BY month DESC 
                                       LIMIT 12")->fetchAll();
        
        // Get top destinations
        $topDestinations = $db->query("SELECT d.name, COUNT(to.id) as order_count 
                                        FROM destinations d 
                                        LEFT JOIN ticket_orders to ON d.id = to.destination_id 
                                        GROUP BY d.id 
                                        ORDER BY order_count DESC 
                                        LIMIT 10")->fetchAll();
        
        // Get top tour guides
        $topGuides = $db->query("SELECT tg.name, COUNT(b.id) as booking_count 
                                  FROM tour_guides tg 
                                  LEFT JOIN bookings b ON tg.id = b.guide_id 
                                  GROUP BY tg.id 
                                  ORDER BY booking_count DESC 
                                  LIMIT 10")->fetchAll();
        
        $data = [
            'title' => 'Laporan & Analitik - MyWisata',
            'stats' => $stats,
            'monthly_revenue' => $monthlyRevenue,
            'top_destinations' => $topDestinations,
            'top_guides' => $topGuides
        ];
        
        $this->view('admin/reports', $data);
    }
    
    /**
     * Export report
     */
    public function export() {
        $type = $this->get('type');
        $startDate = $this->get('start_date');
        $endDate = $this->get('end_date');
        
        $db = Database::getInstance();
        
        switch ($type) {
            case 'revenue':
                $data = $db->query("SELECT * FROM transactions 
                                   WHERE created_at BETWEEN :start_date AND :end_date 
                                   AND payment_status = 'paid'",
                                   ['start_date' => $startDate, 'end_date' => $endDate])->fetchAll();
                break;
            case 'bookings':
                $data = $db->query("SELECT * FROM bookings 
                                   WHERE created_at BETWEEN :start_date AND :end_date",
                                   ['start_date' => $startDate, 'end_date' => $endDate])->fetchAll();
                break;
            case 'tickets':
                $data = $db->query("SELECT * FROM ticket_orders 
                                   WHERE created_at BETWEEN :start_date AND :end_date",
                                   ['start_date' => $startDate, 'end_date' => $endDate])->fetchAll();
                break;
            default:
                $this->json(['status' => 'error', 'message' => 'Invalid report type'], 400);
        }
        
        $this->json(['status' => 'success', 'data' => $data]);
    }
}
