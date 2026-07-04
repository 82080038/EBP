<?php

if (!class_exists('WhatsAppRepository')) {
    require_once __DIR__ . '/../Repositories/WhatsAppRepository.php';
}


class WhatsAppService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new WhatsAppRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function saveSettings($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['provider']) || empty($data['api_token'])) {
                return [
                    'success' => false,
                    'message' => 'Provider and API token are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            
            $existing = $this->repository->getSettings($tenantId, $branchId);
            
            if ($existing) {
                $this->repository->updateSettings($existing['setting_id'], $data);
                $settingId = $existing['setting_id'];
            } else {
                $settingId = $this->repository->createSettings($data);
            }

            return [
                'success' => true,
                'message' => 'WhatsApp settings saved successfully',
                'setting_id' => $settingId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ];
        }
    }

    public function getSettings($tenantId, $branchId)
    {
        try {
            $settings = $this->repository->getSettings($tenantId, $branchId);
            
            if ($settings) {
                // Hide sensitive data
                unset($settings['api_token']);
            }
            
            return [
                'success' => true,
                'message' => 'Settings retrieved successfully',
                'data' => $settings
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get settings: ' . $e->getMessage()
            ];
        }
    }

    public function sendMessage($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['recipient_number']) || empty($data['message_content'])) {
                return [
                    'success' => false,
                    'message' => 'Recipient number and message content are required'
                ];
            }

            $settings = $this->repository->getSettings($tenantId, $branchId);
            
            if (!$settings || !$settings['is_enabled']) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp is not configured or enabled'
                ];
            }

            // Log the message
            $logId = $this->repository->logMessage([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'recipient_number' => $data['recipient_number'],
                'message_type' => $data['message_type'] ?? 'CUSTOM',
                'message_content' => $data['message_content'],
                'status' => 'PENDING'
            ]);

            // Send via Fonnte API
            $result = $this->sendViaFonnte($settings, $data['recipient_number'], $data['message_content']);
            
            // Update log
            $this->repository->updateMessageLog($logId, $result['status'], $result['response']);

            return [
                'success' => $result['success'],
                'message' => $result['message'],
                'log_id' => $logId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ];
        }
    }

    public function sendReport($reportType, $tenantId, $branchId, $dateFrom = null, $dateTo = null)
    {
        try {
            $settings = $this->repository->getSettings($tenantId, $branchId);
            
            if (!$settings || !$settings['is_enabled']) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp is not configured or enabled'
                ];
            }

            $schedules = $this->repository->getReportSchedules($tenantId, $branchId, $reportType);
            
            if (empty($schedules)) {
                return [
                    'success' => false,
                    'message' => 'No recipients configured for this report type'
                ];
            }

            $reportContent = $this->generateReportContent($reportType, $tenantId, $branchId, $dateFrom, $dateTo);
            
            $recipients = json_decode($schedules[0]['recipient_numbers'], true);
            $sentCount = 0;
            $failedCount = 0;

            foreach ($recipients as $recipient) {
                $result = $this->sendViaFonnte($settings, $recipient, $reportContent);
                
                $this->repository->logMessage([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'recipient_number' => $recipient,
                    'message_type' => 'REPORT',
                    'message_content' => $reportContent,
                    'status' => $result['status']
                ]);

                if ($result['success']) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            }

            // Update last sent time
            $this->repository->updateScheduleLastSent($schedules[0]['schedule_id']);

            return [
                'success' => true,
                'message' => "Report sent: $sentCount successful, $failedCount failed",
                'sent_count' => $sentCount,
                'failed_count' => $failedCount
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send report: ' . $e->getMessage()
            ];
        }
    }

    public function createReportSchedule($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['report_type']) || empty($data['recipient_numbers']) || empty($data['schedule_time'])) {
                return [
                    'success' => false,
                    'message' => 'Report type, recipient numbers, and schedule time are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            
            $scheduleId = $this->repository->createReportSchedule($data);

            return [
                'success' => true,
                'message' => 'Report schedule created successfully',
                'schedule_id' => $scheduleId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ];
        }
    }

    public function getReportSchedules($tenantId, $branchId)
    {
        try {
            $schedules = $this->repository->getReportSchedules($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Report schedules retrieved successfully',
                'data' => $schedules
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get schedules: ' . $e->getMessage()
            ];
        }
    }

    public function getMessageLogs($tenantId, $branchId, $limit = 50)
    {
        try {
            $logs = $this->repository->getMessageLogs($tenantId, $branchId, $limit);
            
            return [
                'success' => true,
                'message' => 'Message logs retrieved successfully',
                'data' => $logs
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get logs: ' . $e->getMessage()
            ];
        }
    }

    private function sendViaFonnte($settings, $recipient, $message)
    {
        try {
            $url = $settings['api_url'] ?? 'https://api.fonnte.com/send';
            $token = $settings['api_token'];
            $sender = $settings['sender_number'] ?? null;

            $postData = [
                'target' => $recipient,
                'message' => $message,
                'countryCode' => '62'
            ];

            if ($sender) {
                $postData['sender'] = $sender;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . $token
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) {
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'response' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to send message',
                    'response' => $response
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage(),
                'response' => null
            ];
        }
    }

    private function generateReportContent($reportType, $tenantId, $branchId, $dateFrom, $dateTo)
    {
        $dateFrom = $dateFrom ?? date('Y-m-01');
        $dateTo = $dateTo ?? date('Y-m-t');
        $branchName = $this->getBranchName($branchId);

        switch ($reportType) {
            case 'DAILY_SALES':
                $report = $this->getSalesReport($tenantId, $branchId, $dateFrom, $dateTo);
                $content = "📊 *Laporan Penjualan Harian*\n\n";
                $content .= "🏪 $branchName\n";
                $content .= "📅 $dateFrom s/d $dateTo\n\n";
                $content .= "Total Transaksi: " . number_format($report['total_orders']) . "\n";
                $content .= "Total Penjualan: Rp " . number_format($report['total_sales']) . "\n";
                $content .= "Rata-rata: Rp " . number_format($report['average_order']) . "\n";
                break;

            case 'WEEKLY_SALES':
                $report = $this->getSalesReport($tenantId, $branchId, $dateFrom, $dateTo);
                $content = "📊 *Laporan Penjualan Mingguan*\n\n";
                $content .= "🏪 $branchName\n";
                $content .= "📅 $dateFrom s/d $dateTo\n\n";
                $content .= "Total Transaksi: " . number_format($report['total_orders']) . "\n";
                $content .= "Total Penjualan: Rp " . number_format($report['total_sales']) . "\n";
                break;

            case 'MONTHLY_SALES':
                $report = $this->getSalesReport($tenantId, $branchId, $dateFrom, $dateTo);
                $content = "📊 *Laporan Penjualan Bulanan*\n\n";
                $content .= "🏪 $branchName\n";
                $content .= "📅 $dateFrom s/d $dateTo\n\n";
                $content .= "Total Transaksi: " . number_format($report['total_orders']) . "\n";
                $content .= "Total Penjualan: Rp " . number_format($report['total_sales']) . "\n";
                $content .= "Tertinggi: Rp " . number_format($report['max_order']) . "\n";
                break;

            case 'INVENTORY':
                $report = $this->getInventoryReport($tenantId, $branchId);
                $content = "📦 *Laporan Stok*\n\n";
                $content .= "🏪 $branchName\n";
                $content .= "📅 " . date('Y-m-d') . "\n\n";
                $content .= "Total Produk: " . count($report) . "\n";
                $content .= "Stok Rendah: " . $report['low_stock'] . "\n";
                break;

            case 'PERFORMANCE':
                $report = $this->getPerformanceReport($tenantId, $branchId, $dateFrom, $dateTo);
                $content = "📈 *Laporan Performa*\n\n";
                $content .= "🏪 $branchName\n";
                $content .= "📅 $dateFrom s/d $dateTo\n\n";
                $content .= "Total Orders: " . number_format($report['total_orders']) . "\n";
                $content .= "On-Time Rate: " . $report['on_time_rate'] . "%\n";
                break;

            default:
                $content = "Report tidak tersedia";
        }

        $content .= "\n\n_Generated by EBP Restaurant System_";
        return $content;
    }

    private function getBranchName($branchId)
    {
        $stmt = $this->db->prepare("SELECT branch_name FROM branches WHERE branch_id = ?");
        $stmt->execute([$branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['branch_name'] : 'Unknown Branch';
    }

    private function getSalesReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_sales, AVG(total_amount) as average_order, MAX(total_amount) as max_order FROM orders WHERE tenant_id = ? AND branch_id = ? AND created_at BETWEEN ? AND ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getInventoryReport($tenantId, $branchId)
    {
        $sql = "SELECT COUNT(*) as total_products, SUM(CASE WHEN quantity <= min_stock THEN 1 ELSE 0 END) as low_stock FROM inventory WHERE tenant_id = ? AND branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getPerformanceReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT COUNT(*) as total_orders, ROUND((SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, created_at, completed_at) <= 15 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as on_time_rate FROM orders WHERE tenant_id = ? AND branch_id = ? AND created_at BETWEEN ? AND ? AND status = 'COMPLETED' AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
