<?php

class ReportQueueService
{
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function enqueueReport($tenantId, $branchId, $userId, $reportType, $reportName, $parameters = [], $priority = 0)
    {
        $sql = "
            INSERT INTO report_jobs (tenant_id, branch_id, user_id, report_type, report_name, parameters, status)
            VALUES (?, ?, ?, ?, ?, ?, 'QUEUED')
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $userId,
            $reportType,
            $reportName,
            json_encode($parameters)
        ]);

        $reportJobId = $this->db->lastInsertId();

        // Add to queue
        $sql = "INSERT INTO report_queue (report_job_id, priority) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportJobId, $priority]);

        return [
            'success' => true,
            'report_job_id' => $reportJobId,
            'status' => 'QUEUED',
            'message' => 'Report enqueued successfully'
        ];
    }

    public function getReportJob($reportJobId)
    {
        $sql = "SELECT * FROM report_jobs WHERE report_job_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportJobId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserReportJobs($userId, $limit = 10)
    {
        $limit = (int)$limit;
        $sql = "SELECT * FROM report_jobs WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNextJob()
    {
        $sql = "
            SELECT rq.*, rj.* 
            FROM report_queue rq
            JOIN report_jobs rj ON rq.report_job_id = rj.report_job_id
            WHERE rj.status = 'QUEUED'
            ORDER BY rq.priority DESC, rq.queued_at ASC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markAsProcessing($reportJobId)
    {
        $sql = "UPDATE report_jobs SET status = 'PROCESSING', started_at = NOW() WHERE report_job_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportJobId]);
    }

    public function markAsCompleted($reportJobId, $filePath)
    {
        $sql = "UPDATE report_jobs SET status = 'COMPLETED', completed_at = NOW(), file_path = ? WHERE report_job_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$filePath, $reportJobId]);

        // Remove from queue
        $sql = "DELETE FROM report_queue WHERE report_job_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportJobId]);
    }

    public function markAsFailed($reportJobId, $errorMessage)
    {
        $sql = "UPDATE report_jobs SET status = 'FAILED', completed_at = NOW(), error_message = ? WHERE report_job_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$errorMessage, $reportJobId]);

        // Remove from queue
        $sql = "DELETE FROM report_queue WHERE report_job_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportJobId]);
    }

    public function processQueue()
    {
        while ($job = $this->getNextJob()) {
            $this->markAsProcessing($job['report_job_id']);

            try {
                $result = $this->generateReport($job);
                $this->markAsCompleted($job['report_job_id'], $result['file_path']);
            } catch (Exception $e) {
                $this->markAsFailed($job['report_job_id'], $e->getMessage());
            }
        }
    }

    private function generateReport($job)
    {
        $parameters = json_decode($job['parameters'], true);
        $reportType = $job['report_type'];

        // Generate report based on type
        switch ($reportType) {
            case 'TRIAL_BALANCE':
                return $this->generateTrialBalance($job, $parameters);
            case 'BALANCE_SHEET':
                return $this->generateBalanceSheet($job, $parameters);
            case 'PROFIT_LOSS':
                return $this->generateProfitLoss($job, $parameters);
            default:
                throw new Exception("Unknown report type: {$reportType}");
        }
    }

    private function generateTrialBalance($job, $parameters)
    {
        // Load accounting service
        if (!class_exists('AccountingService')) {
            require_once __DIR__ . '/../modules/Accounting/Services/AccountingService.php';
        }
        $accountingService = new AccountingService();

        $result = $accountingService->getTrialBalance(
            $job['tenant_id'],
            $job['branch_id'],
            $parameters['as_of_date']
        );

        if (!$result['success']) {
            throw new Exception($result['message']);
        }

        // Save to CSV file
        $filePath = $this->saveToCSV($job['report_name'], $result['data']);

        return ['file_path' => $filePath];
    }

    private function generateBalanceSheet($job, $parameters)
    {
        if (!class_exists('AccountingService')) {
            require_once __DIR__ . '/../modules/Accounting/Services/AccountingService.php';
        }
        $accountingService = new AccountingService();

        $result = $accountingService->getBalanceSheet(
            $job['tenant_id'],
            $job['branch_id'],
            $parameters['as_of_date']
        );

        if (!$result['success']) {
            throw new Exception($result['message']);
        }

        $filePath = $this->saveToCSV($job['report_name'], $result['data']);

        return ['file_path' => $filePath];
    }

    private function generateProfitLoss($job, $parameters)
    {
        if (!class_exists('AccountingService')) {
            require_once __DIR__ . '/../modules/Accounting/Services/AccountingService.php';
        }
        $accountingService = new AccountingService();

        $result = $accountingService->getProfitLoss(
            $job['tenant_id'],
            $job['branch_id'],
            $parameters['period_start'],
            $parameters['period_end']
        );

        if (!$result['success']) {
            throw new Exception($result['message']);
        }

        $filePath = $this->saveToCSV($job['report_name'], $result['data']);

        return ['file_path' => $filePath];
    }

    private function saveToCSV($reportName, $data)
    {
        $filename = strtolower(str_replace(' ', '_', $reportName)) . '_' . time() . '.csv';
        $filePath = __DIR__ . '/../public/reports/' . $filename;

        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = fopen($filePath, 'w');
        if (!empty($data)) {
            fputcsv($file, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        }
        fclose($file);

        return '/public/reports/' . $filename;
    }
}
