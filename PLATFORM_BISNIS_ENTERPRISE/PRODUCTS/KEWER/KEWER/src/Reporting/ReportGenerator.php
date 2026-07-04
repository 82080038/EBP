<?php
namespace Kewer\Reporting;

class ReportGenerator {
    private $cabangId;
    private $startDate;
    private $endDate;
    
    public function __construct($cabangId = null, $startDate = null, $endDate = null) {
        $this->cabangId = $cabangId;
        $this->startDate = $startDate ?: date('Y-m-01');
        $this->endDate = $endDate ?: date('Y-m-t');
    }
    
    /**
     * Generate financial report
     */
    public function financialReport() {
        $where = ["created_at BETWEEN ? AND ?"];
        $params = [$this->startDate, $this->endDate];
        
        if ($this->cabangId) {
            $where[] = "cabang_id = ?";
            $params[] = $this->cabangId;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total loans disbursed
        $loansDisbursed = query("
            SELECT SUM(plafon) as total, COUNT(*) as count
            FROM pinjaman
            WHERE {$whereClause} AND status = 'aktif'
        ", $params);
        
        // Get total collections
        $collections = query("
            SELECT SUM(pembayaran.total_bayar) as total, COUNT(*) as count
            FROM pembayaran
            JOIN angsuran a ON pembayaran.angsuran_id = a.id
            WHERE pembayaran.cabang_id = ? AND pembayaran.tanggal_bayar BETWEEN ? AND ?
        ", [$this->cabangId, $this->startDate, $this->endDate]);
        
        // Get outstanding balance
        $outstanding = query("
            SELECT SUM(plafon) as total
            FROM pinjaman
            WHERE status = 'aktif'" . ($this->cabangId ? " AND cabang_id = ?" : "")
        , $this->cabangId ? [$this->cabangId] : []);
        
        // Get overdue payments
        $overdue = query("
            SELECT COUNT(*) as count, SUM(p.total_bayar) as total
            FROM angsuran a
            JOIN pembayaran p ON a.id = p.angsuran_id
            WHERE a.jatuh_tempo < CURDATE() AND a.status = 'belum'
            " . ($this->cabangId ? "AND a.cabang_id = ?" : "")
        , $this->cabangId ? [$this->cabangId] : []);
        
        return [
            'period' => [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate
            ],
            'loans_disbursed' => [
                'total' => $loansDisbursed[0]['total'] ?? 0,
                'count' => $loansDisbursed[0]['count'] ?? 0
            ],
            'collections' => [
                'total' => $collections[0]['total'] ?? 0,
                'count' => $collections[0]['count'] ?? 0
            ],
            'outstanding_balance' => $outstanding[0]['total'] ?? 0,
            'overdue' => [
                'count' => $overdue[0]['count'] ?? 0,
                'total' => $overdue[0]['total'] ?? 0
            ]
        ];
    }
    
    /**
     * Generate loan performance report
     */
    public function loanPerformanceReport() {
        $where = ["created_at BETWEEN ? AND ?"];
        $params = [$this->startDate, $this->endDate];
        
        if ($this->cabangId) {
            $where[] = "cabang_id = ?";
            $params[] = $this->cabangId;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get loan status distribution
        $statusDistribution = query("
            SELECT status, COUNT(*) as count, SUM(plafon) as total
            FROM pinjaman
            WHERE {$whereClause}
            GROUP BY status
        ", $params);
        
        if (!is_array($statusDistribution)) {
            $statusDistribution = [];
        }
        
        // Get payment performance
        $paymentPerformance = query("
            SELECT
                COUNT(CASE WHEN a.tanggal_bayar <= a.jatuh_tempo THEN 1 END) as on_time,
                COUNT(CASE WHEN a.tanggal_bayar > a.jatuh_tempo THEN 1 END) as late,
                COUNT(*) as total
            FROM angsuran a
            JOIN pembayaran p ON a.id = p.angsuran_id
            WHERE p.tanggal_bayar BETWEEN ? AND ?
            " . ($this->cabangId ? "AND a.cabang_id = ?" : "")
        , $this->cabangId ? [$this->startDate, $this->endDate, $this->cabangId] : [$this->startDate, $this->endDate]);
        
        if (!is_array($paymentPerformance) || empty($paymentPerformance)) {
            $paymentPerformance = [0];
        }
        
        return [
            'period' => [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate
            ],
            'status_distribution' => $statusDistribution,
            'payment_performance' => $paymentPerformance[0] ?? []
        ];
    }
    
    /**
     * Generate customer report
     */
    public function customerReport() {
        $where = ["created_at BETWEEN ? AND ?"];
        $params = [$this->startDate, $this->endDate];
        
        if ($this->cabangId) {
            $where[] = "cabang_id = ?";
            $params[] = $this->cabangId;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get new customers
        $newCustomers = query("
            SELECT COUNT(*) as count
            FROM nasabah
            WHERE {$whereClause}
        ", $params);
        
        // Get active customers
        $activeCustomers = query("
            SELECT COUNT(*) as count
            FROM nasabah
            WHERE status = 'aktif'" . ($this->cabangId ? " AND cabang_id = ?" : "")
        , $this->cabangId ? [$this->cabangId] : []);
        
        // Get customers with active loans
        $customersWithLoans = query("
            SELECT COUNT(DISTINCT nasabah_id) as count
            FROM pinjaman
            WHERE status = 'aktif'" . ($this->cabangId ? " AND cabang_id = ?" : "")
        , $this->cabangId ? [$this->cabangId] : []);
        
        return [
            'period' => [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate
            ],
            'new_customers' => $newCustomers[0]['count'] ?? 0,
            'active_customers' => $activeCustomers[0]['count'] ?? 0,
            'customers_with_loans' => $customersWithLoans[0]['count'] ?? 0
        ];
    }
    
    /**
     * Generate comprehensive report
     */
    public function comprehensiveReport() {
        return [
            'financial' => $this->financialReport(),
            'loan_performance' => $this->loanPerformanceReport(),
            'customer' => $this->customerReport()
        ];
    }
    
    /**
     * Set date range
     */
    public function setDateRange($startDate, $endDate) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        return $this;
    }
    
    /**
     * Set cabang ID
     */
    public function setCabangId($cabangId) {
        $this->cabangId = $cabangId;
        return $this;
    }
}
?>
