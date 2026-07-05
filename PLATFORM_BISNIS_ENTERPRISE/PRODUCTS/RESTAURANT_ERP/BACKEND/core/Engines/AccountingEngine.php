<?php

class AccountingEngine
{

    private $db;



    public function __construct($db)
    {

        $this->db = $db;

    }



    public function createSalesJournal($orderId, $totalAmount, $branchId)
    {
        // Get tenant_id from order
        $sql = "SELECT tenant_id FROM orders WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception("Order not found: {$orderId}");
        }
        
        $tenantId = $order['tenant_id'];

        $sql = "
            INSERT INTO journal_entries
            (tenant_id, branch_id, journal_number, transaction_date, reference_type, reference_id, description, status, created_at)
            VALUES (?, ?, CONCAT('JNL-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(?, 6, '0')), CURDATE(), 'ORDER', ?, 'Sales Order', 'POSTED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $orderId, $orderId]);

        $journalId = $this->db->lastInsertId();

        $cashAccountId = $this->getAccountId('CASH', $tenantId);
        $revenueAccountId = $this->getAccountId('REVENUE', $tenantId);

        $sql = "
            INSERT INTO journal_details
            (journal_id, account_id, debit, credit, created_at)
            VALUES
            (?, ?, ?, 0, NOW()),
            (?, ?, 0, ?, NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $journalId, $cashAccountId, $totalAmount,
            $journalId, $revenueAccountId, $totalAmount
        ]);

        return $journalId;
    }



    private function getAccountId($accountType, $tenantId = null)
    {
        $sql = "
            SELECT account_id FROM accounts
            WHERE account_code LIKE ?";
        
        $params = [$accountType . '%'];
        
        // Add tenant filter if provided
        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['account_id'] : 1;
    }

}
