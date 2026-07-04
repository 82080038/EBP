<?php

namespace App\Modules\Compliance\Services;

use App\Modules\Compliance\Models\ComplianceRule;
use App\Modules\Compliance\Models\ComplianceCheck;
use App\Modules\Compliance\Models\ComplianceDocument;
use App\Modules\Compliance\Models\ComplianceAlert;
use App\Core\Database;

class ComplianceService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get compliance dashboard
     */
    public function getDashboard($restaurantId)
    {
        $ruleModel = new ComplianceRule();
        $alertModel = new ComplianceAlert();
        $documentModel = new ComplianceDocument();
        
        // Get summary stats
        $stats = [
            'total_rules' => $ruleModel->countByRestaurant($restaurantId),
            'active_rules' => $ruleModel->countActive($restaurantId),
            'pending_checks' => $ruleModel->countPendingChecks($restaurantId),
            'unresolved_alerts' => $alertModel->countUnresolved($restaurantId),
            'expiring_documents' => $documentModel->countExpiring($restaurantId),
            'overall_compliance' => $this->calculateOverallCompliance($restaurantId)
        ];
        
        // Get recent checks
        $checkModel = new ComplianceCheck();
        $recentChecks = $checkModel->getRecent($restaurantId, 10);
        
        // Get recent alerts
        $recentAlerts = $alertModel->getRecent($restaurantId, 5);
        
        // Get expiring documents
        $expiringDocuments = $documentModel->getExpiring($restaurantId, 5);
        
        return [
            'stats' => $stats,
            'recent_checks' => $recentChecks,
            'recent_alerts' => $recentAlerts,
            'expiring_documents' => $expiringDocuments
        ];
    }

    /**
     * Calculate overall compliance score
     */
    private function calculateOverallCompliance($restaurantId)
    {
        $checkModel = new ComplianceCheck();
        
        // Get last 30 days of checks
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN check_status = 'passed' THEN 1 ELSE 0 END) as passed
                FROM compliance_checks
                WHERE restaurant_id = ? 
                AND check_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        
        if (!$result || $result['total'] == 0) {
            return 100; // Default to 100% if no checks
        }
        
        return round(($result['passed'] / $result['total']) * 100, 2);
    }

    /**
     * Get compliance rules
     */
    public function getRules($restaurantId, $ruleType = null)
    {
        $ruleModel = new ComplianceRule();
        return $ruleModel->getByRestaurant($restaurantId, $ruleType);
    }

    /**
     * Add compliance rule
     */
    public function addRule($restaurantId, $data)
    {
        $ruleModel = new ComplianceRule();
        
        $ruleData = [
            'restaurant_id' => $restaurantId,
            'rule_type' => $data->rule_type,
            'rule_name' => $data->rule_name,
            'rule_description' => $data->rule_description ?? null,
            'rule_config' => json_encode($data->rule_config),
            'check_frequency' => $data->check_frequency,
            'next_check_date' => $this->calculateNextCheckDate($data->check_frequency),
            'priority' => $data->priority ?? 'medium',
            'is_active' => true
        ];
        
        $ruleId = $ruleModel->create($ruleData);
        
        if (!$ruleId) {
            return ['success' => false, 'message' => 'Failed to create rule'];
        }
        
        return ['success' => true, 'message' => 'Rule added successfully', 'rule_id' => $ruleId];
    }

    /**
     * Calculate next check date
     */
    private function calculateNextCheckDate($frequency)
    {
        switch ($frequency) {
            case 'daily':
                return date('Y-m-d', strtotime('+1 day'));
            case 'weekly':
                return date('Y-m-d', strtotime('+1 week'));
            case 'monthly':
                return date('Y-m-d', strtotime('+1 month'));
            case 'quarterly':
                return date('Y-m-d', strtotime('+3 months'));
            case 'yearly':
                return date('Y-m-d', strtotime('+1 year'));
            default:
                return null;
        }
    }

    /**
     * Update compliance rule
     */
    public function updateRule($id, $restaurantId, $data)
    {
        $ruleModel = new ComplianceRule();
        $rule = $ruleModel->findById($id, $restaurantId);
        
        if (!$rule) {
            return ['success' => false, 'message' => 'Rule not found'];
        }
        
        $updateData = [];
        
        if (isset($data->rule_name)) {
            $updateData['rule_name'] = $data->rule_name;
        }
        if (isset($data->rule_description)) {
            $updateData['rule_description'] = $data->rule_description;
        }
        if (isset($data->rule_config)) {
            $updateData['rule_config'] = json_encode($data->rule_config);
        }
        if (isset($data->check_frequency)) {
            $updateData['check_frequency'] = $data->check_frequency;
            $updateData['next_check_date'] = $this->calculateNextCheckDate($data->check_frequency);
        }
        if (isset($data->priority)) {
            $updateData['priority'] = $data->priority;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        
        $updated = $ruleModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update rule'];
        }
        
        return ['success' => true, 'message' => 'Rule updated successfully'];
    }

    /**
     * Delete compliance rule
     */
    public function deleteRule($id, $restaurantId)
    {
        $ruleModel = new ComplianceRule();
        $rule = $ruleModel->findById($id, $restaurantId);
        
        if (!$rule) {
            return ['success' => false, 'message' => 'Rule not found'];
        }
        
        $deleted = $ruleModel->delete($id);
        
        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to delete rule'];
        }
        
        return ['success' => true, 'message' => 'Rule deleted successfully'];
    }

    /**
     * Run compliance check
     */
    public function runCheck($restaurantId, $userId, $ruleId = null)
    {
        $ruleModel = new ComplianceRule();
        
        if ($ruleId) {
            $rules = [$ruleModel->findById($ruleId, $restaurantId)];
        } else {
            $rules = $ruleModel->getPendingChecks($restaurantId);
        }
        
        if (empty($rules)) {
            return ['success' => false, 'message' => 'No rules to check'];
        }
        
        $totalChecked = 0;
        $totalPassed = 0;
        $totalFailed = 0;
        $alertsCreated = 0;
        
        foreach ($rules as $rule) {
            $result = $this->runSingleCheck($rule, $restaurantId, $userId);
            $totalChecked++;
            
            if ($result['status'] === 'passed') {
                $totalPassed++;
            } else {
                $totalFailed++;
                if ($result['alert_created']) {
                    $alertsCreated++;
                }
            }
            
            // Update next check date
            $ruleModel->update($rule['id'], [
                'next_check_date' => $this->calculateNextCheckDate($rule['check_frequency'])
            ]);
        }
        
        return [
            'success' => true,
            'message' => 'Compliance check completed',
            'summary' => [
                'total_checked' => $totalChecked,
                'total_passed' => $totalPassed,
                'total_failed' => $totalFailed,
                'alerts_created' => $alertsCreated
            ]
        ];
    }

    /**
     * Run single compliance check
     */
    private function runSingleCheck($rule, $restaurantId, $userId)
    {
        $checkModel = new ComplianceCheck();
        
        // Execute check based on rule type
        $result = $this->executeCheck($rule, $restaurantId);
        
        // Create check record
        $checkData = [
            'restaurant_id' => $restaurantId,
            'compliance_rule_id' => $rule['id'],
            'check_date' => date('Y-m-d'),
            'check_status' => $result['status'],
            'check_result' => json_encode($result['details']),
            'violations_found' => $result['violations'],
            'violation_details' => json_encode($result['violation_details']),
            'remediation_required' => $result['remediation_required'],
            'checked_by' => $userId,
            'checked_at' => date('Y-m-d H:i:s')
        ];
        
        $checkId = $checkModel->create($checkData);
        
        // Create alert if failed
        $alertCreated = false;
        if ($result['status'] === 'failed' || $result['status'] === 'warning') {
            $this->createComplianceAlert($restaurantId, $rule, $result, $checkId);
            $alertCreated = true;
        }
        
        return [
            'status' => $result['status'],
            'violations' => $result['violations'],
            'remediation_required' => $result['remediation_required'],
            'alert_created' => $alertCreated
        ];
    }

    /**
     * Execute check based on rule type
     */
    private function executeCheck($rule, $restaurantId)
    {
        $config = json_decode($rule['rule_config'], true);
        
        switch ($rule['rule_type']) {
            case 'labor_law':
                return $this->checkLaborLaw($config, $restaurantId);
            
            case 'tax':
                return $this->checkTax($config, $restaurantId);
            
            case 'food_safety':
                return $this->checkFoodSafety($config, $restaurantId);
            
            case 'licensing':
                return $this->checkLicensing($config, $restaurantId);
            
            default:
                return [
                    'status' => 'skipped',
                    'violations' => 0,
                    'remediation_required' => false,
                    'details' => ['message' => 'Unknown rule type'],
                    'violation_details' => []
                ];
        }
    }

    /**
     * Check labor law compliance
     */
    private function checkLaborLaw($config, $restaurantId)
    {
        // In real implementation, check actual work hours
        // For now, simulate check
        
        $violations = 0;
        $violationDetails = [];
        
        // Simulate checking daily hours
        if (isset($config['max_daily_hours'])) {
            // In real implementation, query actual work hours
            $violations += rand(0, 2);
        }
        
        $status = $violations > 0 ? 'failed' : 'passed';
        
        return [
            'status' => $status,
            'violations' => $violations,
            'remediation_required' => $violations > 0,
            'details' => ['max_daily_hours' => $config['max_daily_hours'] ?? null],
            'violation_details' => $violationDetails
        ];
    }

    /**
     * Check tax compliance
     */
    private function checkTax($config, $restaurantId)
    {
        // In real implementation, check tax filings
        // For now, simulate check
        
        $violations = 0;
        $violationDetails = [];
        
        // Simulate checking tax filing
        if (isset($config['filing_day'])) {
            $currentDay = date('j');
            if ($currentDay > $config['filing_day']) {
                $violations++;
                $violationDetails[] = 'Tax filing overdue';
            }
        }
        
        $status = $violations > 0 ? 'failed' : 'passed';
        
        return [
            'status' => $status,
            'violations' => $violations,
            'remediation_required' => $violations > 0,
            'details' => ['filing_day' => $config['filing_day'] ?? null],
            'violation_details' => $violationDetails
        ];
    }

    /**
     * Check food safety compliance
     */
    private function checkFoodSafety($config, $restaurantId)
    {
        // In real implementation, check food safety certificates
        // For now, simulate check
        
        $violations = 0;
        $violationDetails = [];
        
        // Simulate checking certificate expiry
        if (isset($config['alert_days_before'])) {
            $sql = "SELECT COUNT(*) as count FROM compliance_documents 
                    WHERE restaurant_id = ? 
                    AND document_type = 'certificate'
                    AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                    AND expiry_date > CURDATE()";
            
            $result = $this->db->query($sql, [$restaurantId, $config['alert_days_before']])->fetch();
            $violations = $result['count'] ?? 0;
        }
        
        $status = $violations > 0 ? 'warning' : 'passed';
        
        return [
            'status' => $status,
            'violations' => $violations,
            'remediation_required' => $violations > 0,
            'details' => ['alert_days_before' => $config['alert_days_before'] ?? null],
            'violation_details' => $violationDetails
        ];
    }

    /**
     * Check licensing compliance
     */
    private function checkLicensing($config, $restaurantId)
    {
        // In real implementation, check licenses
        // For now, simulate check
        
        $violations = 0;
        $violationDetails = [];
        
        // Simulate checking license expiry
        $sql = "SELECT COUNT(*) as count FROM compliance_documents 
                WHERE restaurant_id = ? 
                AND document_type = 'license'
                AND expiry_date < CURDATE()";
        
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        $violations = $result['count'] ?? 0;
        
        $status = $violations > 0 ? 'failed' : 'passed';
        
        return [
            'status' => $status,
            'violations' => $violations,
            'remediation_required' => $violations > 0,
            'details' => [],
            'violation_details' => $violationDetails
        ];
    }

    /**
     * Create compliance alert
     */
    private function createComplianceAlert($restaurantId, $rule, $checkResult, $checkId)
    {
        $alertModel = new ComplianceAlert();
        
        $alertData = [
            'restaurant_id' => $restaurantId,
            'alert_type' => 'check_failed',
            'alert_severity' => $rule['priority'],
            'alert_title' => 'Compliance Check Failed: ' . $rule['rule_name'],
            'alert_message' => $checkResult['details']['message'] ?? 'Compliance check failed',
            'alert_data' => json_encode($checkResult),
            'compliance_rule_id' => $rule['id'],
            'compliance_check_id' => $checkId
        ];
        
        $alertModel->create($alertData);
    }

    /**
     * Get compliance checks
     */
    public function getChecks($restaurantId, $ruleId, $status, $page, $limit)
    {
        $checkModel = new ComplianceCheck();
        return $checkModel->getPaginated($restaurantId, $ruleId, $status, $page, $limit);
    }

    /**
     * Get compliance documents
     */
    public function getDocuments($restaurantId, $documentType, $status)
    {
        $documentModel = new ComplianceDocument();
        return $documentModel->getByRestaurant($restaurantId, $documentType, $status);
    }

    /**
     * Add compliance document
     */
    public function addDocument($restaurantId, $data)
    {
        $documentModel = new ComplianceDocument();
        
        $documentData = [
            'restaurant_id' => $restaurantId,
            'document_type' => $data->document_type,
            'document_name' => $data->document_name,
            'document_number' => $data->document_number ?? null,
            'issuing_authority' => $data->issuing_authority ?? null,
            'issue_date' => $data->issue_date ?? null,
            'expiry_date' => $data->expiry_date ?? null,
            'file_path' => $data->file_path ?? null,
            'file_name' => $data->file_name ?? null,
            'file_size' => $data->file_size ?? null,
            'file_mime_type' => $data->file_mime_type ?? null,
            'alert_days_before_expiry' => $data->alert_days_before_expiry ?? 30,
            'status' => 'active'
        ];
        
        $documentId = $documentModel->create($documentData);
        
        if (!$documentId) {
            return ['success' => false, 'message' => 'Failed to create document'];
        }
        
        return ['success' => true, 'message' => 'Document added successfully', 'document_id' => $documentId];
    }

    /**
     * Update compliance document
     */
    public function updateDocument($id, $restaurantId, $data)
    {
        $documentModel = new ComplianceDocument();
        $document = $documentModel->findById($id, $restaurantId);
        
        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }
        
        $updateData = [];
        
        if (isset($data->document_name)) {
            $updateData['document_name'] = $data->document_name;
        }
        if (isset($data->expiry_date)) {
            $updateData['expiry_date'] = $data->expiry_date;
        }
        if (isset($data->file_path)) {
            $updateData['file_path'] = $data->file_path;
        }
        if (isset($data->status)) {
            $updateData['status'] = $data->status;
        }
        
        $updated = $documentModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update document'];
        }
        
        return ['success' => true, 'message' => 'Document updated successfully'];
    }

    /**
     * Delete compliance document
     */
    public function deleteDocument($id, $restaurantId)
    {
        $documentModel = new ComplianceDocument();
        $document = $documentModel->findById($id, $restaurantId);
        
        if (!$document) {
            return ['success' => false, 'message' => 'Document not found'];
        }
        
        $deleted = $documentModel->delete($id);
        
        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to delete document'];
        }
        
        return ['success' => true, 'message' => 'Document deleted successfully'];
    }

    /**
     * Get compliance alerts
     */
    public function getAlerts($restaurantId, $isResolved, $page, $limit)
    {
        $alertModel = new ComplianceAlert();
        return $alertModel->getPaginated($restaurantId, $isResolved, $page, $limit);
    }

    /**
     * Resolve compliance alert
     */
    public function resolveAlert($id, $restaurantId, $userId, $data)
    {
        $alertModel = new ComplianceAlert();
        $alert = $alertModel->findById($id, $restaurantId);
        
        if (!$alert) {
            return ['success' => false, 'message' => 'Alert not found'];
        }
        
        $updated = $alertModel->update($id, [
            'is_resolved' => true,
            'resolved_by' => $userId,
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolution_notes' => $data->notes ?? null
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to resolve alert'];
        }
        
        return ['success' => true, 'message' => 'Alert resolved successfully'];
    }

    /**
     * Get labor law compliance
     */
    public function getLaborLawCompliance($restaurantId)
    {
        $sql = "SELECT * FROM labor_law_compliance WHERE restaurant_id = ?";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update labor law compliance
     */
    public function updateLaborLawCompliance($id, $restaurantId, $data)
    {
        $updateFields = [];
        $params = [];
        
        if (isset($data->max_daily_hours)) {
            $updateFields[] = 'max_daily_hours = ?';
            $params[] = $data->max_daily_hours;
        }
        if (isset($data->max_weekly_hours)) {
            $updateFields[] = 'max_weekly_hours = ?';
            $params[] = $data->max_weekly_hours;
        }
        if (isset($data->minimum_hourly_rate)) {
            $updateFields[] = 'minimum_hourly_rate = ?';
            $params[] = $data->minimum_hourly_rate;
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        $params[] = $id;
        $params[] = $restaurantId;
        
        $sql = "UPDATE labor_law_compliance 
                SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND restaurant_id = ?";
        
        $result = $this->db->query($sql, $params);
        
        if ($result) {
            return ['success' => true, 'message' => 'Labor law compliance updated'];
        }
        
        return ['success' => false, 'message' => 'Failed to update labor law compliance'];
    }

    /**
     * Get tax compliance
     */
    public function getTaxCompliance($restaurantId)
    {
        $sql = "SELECT * FROM tax_compliance WHERE restaurant_id = ?";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update tax compliance
     */
    public function updateTaxCompliance($id, $restaurantId, $data)
    {
        $updateFields = [];
        $params = [];
        
        if (isset($data->tax_rate)) {
            $updateFields[] = 'tax_rate = ?';
            $params[] = $data->tax_rate;
        }
        if (isset($data->next_filing_date)) {
            $updateFields[] = 'next_filing_date = ?';
            $params[] = $data->next_filing_date;
        }
        if (isset($data->last_filing_date)) {
            $updateFields[] = 'last_filing_date = ?';
            $params[] = $data->last_filing_date;
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        $params[] = $id;
        $params[] = $restaurantId;
        
        $sql = "UPDATE tax_compliance 
                SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND restaurant_id = ?";
        
        $result = $this->db->query($sql, $params);
        
        if ($result) {
            return ['success' => true, 'message' => 'Tax compliance updated'];
        }
        
        return ['success' => false, 'message' => 'Failed to update tax compliance'];
    }

    /**
     * Get food safety compliance
     */
    public function getFoodSafetyCompliance($restaurantId)
    {
        $sql = "SELECT * FROM food_safety_compliance WHERE restaurant_id = ? ORDER BY inspection_date DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Add food safety inspection
     */
    public function addFoodSafetyInspection($restaurantId, $data)
    {
        $sql = "INSERT INTO food_safety_compliance 
                (restaurant_id, inspection_type, inspection_date, inspector_name, inspector_agency,
                 inspection_score, inspection_grade, inspection_status, violations_found, violation_details,
                 follow_up_required, follow_up_date, certificate_number, certificate_expiry)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $restaurantId,
            $data->inspection_type,
            $data->inspection_date ?? null,
            $data->inspector_name ?? null,
            $data->inspector_agency ?? null,
            $data->inspection_score ?? null,
            $data->inspection_grade ?? null,
            $data->inspection_status ?? null,
            $data->violations_found ?? 0,
            json_encode($data->violation_details ?? []),
            $data->follow_up_required ?? false,
            $data->follow_up_date ?? null,
            $data->certificate_number ?? null,
            $data->certificate_expiry ?? null
        ];
        
        $result = $this->db->query($sql, $params);
        
        if ($result) {
            return ['success' => true, 'message' => 'Food safety inspection added', 'id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'message' => 'Failed to add food safety inspection'];
    }
}
