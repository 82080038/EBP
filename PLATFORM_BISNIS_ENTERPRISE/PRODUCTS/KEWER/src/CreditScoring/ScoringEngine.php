<?php
namespace Kewer\CreditScoring;

/**
 * Credit Scoring Engine
 * Calculates risk scores for nasabah based on multiple factors
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

class ScoringEngine {
    
    /**
     * Calculate credit score for a nasabah
     * 
     * @param int $nasabah_id
     * @return array Score details
     */
    public static function calculateScore($nasabah_id) {
        // Get nasabah data
        $nasabah = self::getNasabahData($nasabah_id);
        if (!$nasabah) {
            return ['success' => false, 'message' => 'Nasabah not found'];
        }
        
        // Calculate individual scores
        $paymentHistoryScore = self::calculatePaymentHistoryScore($nasabah_id);
        $demographicScore = self::calculateDemographicScore($nasabah);
        $locationScore = self::calculateLocationScore($nasabah);
        $familyRiskScore = self::calculateFamilyRiskScore($nasabah_id);
        $durationScore = self::calculateDurationScore($nasabah);
        
        // Calculate weighted total score
        $totalScore = (
            ($paymentHistoryScore * 0.40) +
            ($demographicScore * 0.20) +
            ($locationScore * 0.15) +
            ($familyRiskScore * 0.15) +
            ($durationScore * 0.10)
        );
        
        // Determine risk level
        $riskLevel = self::getRiskLevel($totalScore);
        
        // Log the scoring
        self::logScoring($nasabah_id, $totalScore, $riskLevel, [
            'payment_history' => $paymentHistoryScore,
            'demographic' => $demographicScore,
            'location' => $locationScore,
            'family_risk' => $familyRiskScore,
            'duration' => $durationScore
        ]);
        
        return [
            'success' => true,
            'data' => [
                'nasabah_id' => $nasabah_id,
                'total_score' => round($totalScore, 2),
                'risk_level' => $riskLevel,
                'breakdown' => [
                    'payment_history' => round($paymentHistoryScore, 2),
                    'demographic' => round($demographicScore, 2),
                    'location' => round($locationScore, 2),
                    'family_risk' => round($familyRiskScore, 2),
                    'duration' => round($durationScore, 2)
                ]
            ]
        ];
    }
    
    /**
     * Calculate payment history score (40% weight)
     */
    private static function calculatePaymentHistoryScore($nasabah_id) {
        // Get all completed pinjaman for this nasabah
        $sql = "SELECT p.id, p.tanggal_pengajuan, p.tanggal_disetujui,
                COUNT(DISTINCT a.id) as total_angsuran,
                SUM(CASE WHEN a.status = 'lunas' AND DATEDIFF(a.tanggal_bayar, a.tanggal_jatuh_tempo) <= 3 THEN 1 ELSE 0 END) as tepat_waktu,
                SUM(CASE WHEN a.status = 'belum_lunas' AND DATEDIFF(CURDATE(), a.tanggal_jatuh_tempo) > 30 THEN 1 ELSE 0 END) as tunggakan
                FROM pinjaman p
                LEFT JOIN angsuran a ON p.id = a.pinjaman_id
                WHERE p.nasabah_id = ? AND p.status = 'disetujui'
                GROUP BY p.id";
        
        $result = query($sql, [$nasabah_id]);
        if (!is_array($result) || empty($result)) {
            return 70; // Default score for new nasabah
        }
        
        $totalPinjaman = count($result);
        $totalAngsuran = 0;
        $totalTepatWaktu = 0;
        $totalTunggakan = 0;
        
        foreach ($result as $row) {
            $totalAngsuran += $row['total_angsuran'] ?? 0;
            $totalTepatWaktu += $row['tepat_waktu'] ?? 0;
            $totalTunggakan += $row['tunggakan'] ?? 0;
        }
        
        if ($totalAngsuran == 0) {
            return 70; // No payment history yet
        }
        
        // Calculate on-time payment percentage
        $onTimePercentage = ($totalTepatWaktu / $totalAngsuran) * 100;
        
        // Penalty for tunggakan
        $tunggakanPenalty = min($totalTunggakan * 5, 30); // Max 30 points penalty
        
        $score = $onTimePercentage - $tunggakanPenalty;
        
        return max(0, min(100, $score));
    }
    
    /**
     * Calculate demographic score (20% weight)
     */
    private static function calculateDemographicScore($nasabah) {
        $score = 50; // Base score
        
        // Age factor (25-55 is ideal)
        if (isset($nasabah['tanggal_lahir'])) {
            $age = self::calculateAge($nasabah['tanggal_lahir']);
            if ($age >= 25 && $age <= 55) {
                $score += 20;
            } elseif ($age >= 18 && $age < 25) {
                $score += 10;
            } elseif ($age > 55) {
                $score += 5;
            }
        }
        
        // Employment status
        if (!empty($nasabah['pekerjaan'])) {
            $score += 15;
        }
        
        // Income (if available)
        if (!empty($nasabah['pendapatan'])) {
            $income = (int) $nasabah['pendapatan'];
            if ($income > 5000000) {
                $score += 15;
            } elseif ($income > 3000000) {
                $score += 10;
            } elseif ($income > 1000000) {
                $score += 5;
            }
        }
        
        return min(100, $score);
    }
    
    /**
     * Calculate location score (15% weight)
     */
    private static function calculateLocationScore($nasabah) {
        $score = 70; // Base score
        
        // If address is in db_orang, it's verified
        if (!empty($nasabah['db_orang_address_id'])) {
            $score += 20;
        }
        
        // If in urban area (based on village data)
        if (!empty($nasabah['village_id'])) {
            // Check if urban area
            $sql = "SELECT klasifikasi FROM db_alamat.villages WHERE id = ?";
            $result = query_alamat($sql, [$nasabah['village_id']]);
            if (is_array($result) && isset($result[0]) && $result[0]['klasifikasi'] == 'urban') {
                $score += 10;
            }
        }
        
        return min(100, $score);
    }
    
    /**
     * Calculate family risk score (15% weight)
     */
    private static function calculateFamilyRiskScore($nasabah_id) {
        $score = 70; // Base score
        
        // Check family risk assessment
        $sql = "SELECT risk_score FROM family_risk WHERE nasabah_id = ? ORDER BY created_at DESC LIMIT 1";
        $result = query($sql, [$nasabah_id]);
        
        if (is_array($result) && isset($result[0])) {
            $riskScore = (int) $result[0]['risk_score'];
            // Convert risk score to credit score (inverse)
            $score = 100 - $riskScore;
        }
        
        return max(0, min(100, $score));
    }
    
    /**
     * Calculate duration score (10% weight)
     */
    private static function calculateDurationScore($nasabah) {
        $score = 50; // Base score
        
        // Get first pinjaman date
        $sql = "SELECT MIN(tanggal_pengajuan) as first_pinjaman FROM pinjaman WHERE nasabah_id = ?";
        $result = query($sql, [$nasabah['id']]);
        
        if (is_array($result) && isset($result[0]) && $result[0]['first_pinjaman']) {
            $firstPinjaman = $result[0]['first_pinjaman'];
            $duration = self::calculateDurationInMonths($firstPinjaman);
            
            if ($duration >= 24) {
                $score += 40;
            } elseif ($duration >= 12) {
                $score += 30;
            } elseif ($duration >= 6) {
                $score += 20;
            } elseif ($duration >= 3) {
                $score += 10;
            }
        }
        
        return min(100, $score);
    }
    
    /**
     * Get risk level based on score
     */
    private static function getRiskLevel($score) {
        if ($score >= 80) {
            return 'Sangat Rendah';
        } elseif ($score >= 60) {
            return 'Rendah';
        } elseif ($score >= 40) {
            return 'Sedang';
        } elseif ($score >= 20) {
            return 'Tinggi';
        } else {
            return 'Sangat Tinggi';
        }
    }
    
    /**
     * Log scoring result
     */
    private static function logScoring($nasabah_id, $score, $riskLevel, $breakdown) {
        $sql = "INSERT INTO credit_scoring_logs (nasabah_id, score, risk_level, breakdown, created_at) VALUES (?, ?, ?, ?, NOW())";
        query($sql, [$nasabah_id, $score, $riskLevel, json_encode($breakdown)]);
    }
    
    /**
     * Get nasabah data
     */
    private static function getNasabahData($nasabah_id) {
        $sql = "SELECT n.*, p.id as db_orang_person_id, a.id as db_orang_address_id
                FROM nasabah n
                LEFT JOIN db_orang.people p ON n.db_orang_user_id = p.id
                LEFT JOIN db_orang.addresses a ON n.db_orang_address_id = a.id
                WHERE n.id = ?";
        
        $result = query($sql, [$nasabah_id]);
        return is_array($result) && isset($result[0]) ? $result[0] : null;
    }
    
    /**
     * Calculate age from birth date
     */
    private static function calculateAge($birthDate) {
        $birth = new \DateTime($birthDate);
        $today = new \DateTime();
        return $today->diff($birth)->y;
    }
    
    /**
     * Calculate duration in months
     */
    private static function calculateDurationInMonths($startDate) {
        $start = new \DateTime($startDate);
        $today = new \DateTime();
        $diff = $today->diff($start);
        return ($diff->y * 12) + $diff->m;
    }
    
    /**
     * Auto-approve pinjaman based on credit score
     */
    public static function shouldAutoApprove($nasabah_id, $jumlahPinjaman) {
        $result = self::calculateScore($nasabah_id);
        
        if (!$result['success']) {
            return ['auto_approve' => false, 'reason' => 'Unable to calculate score'];
        }
        
        $score = $result['data']['total_score'];
        $riskLevel = $result['data']['risk_level'];
        
        // Auto-approve if score >= 70 and risk level is low
        if ($score >= 70 && in_array($riskLevel, ['Sangat Rendah', 'Rendah'])) {
            // Check if pinjaman amount is within reasonable limit
            $maxAmount = self::getMaxPinjamanAmount($score);
            if ($jumlahPinjaman <= $maxAmount) {
                return [
                    'auto_approve' => true,
                    'reason' => 'Credit score sufficient',
                    'score' => $score,
                    'risk_level' => $riskLevel
                ];
            }
        }
        
        return [
            'auto_approve' => false,
            'reason' => 'Credit score or risk level not sufficient',
            'score' => $score,
            'risk_level' => $riskLevel
        ];
    }
    
    /**
     * Get maximum pinjaman amount based on credit score
     */
    private static function getMaxPinjamanAmount($score) {
        // Base amount: Rp 1,000,000
        // Add Rp 100,000 for each point above 50
        $baseAmount = 1000000;
        $additionalAmount = max(0, $score - 50) * 100000;
        
        return $baseAmount + $additionalAmount;
    }
}
