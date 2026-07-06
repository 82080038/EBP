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

        // Check if journal entry already exists for this order (idempotency)
        $sql = "SELECT journal_id FROM journal_entries WHERE reference_type = 'ORDER' AND reference_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return [
                'success' => true,
                'journal_id' => $existing['journal_id'],
                'message' => 'Journal entry already exists for this order'
            ];
        }

        $journalNumber = 'JE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO journal_entries
            (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status, created_at)
            VALUES (?, ?, ?, CURDATE(), 'ORDER', ?, 'Sales Order', 'POSTED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $journalNumber, $orderId]);

        $journalId = $this->db->lastInsertId();

        $cashAccountId = $this->getAccountId('CASH', $tenantId);
        $revenueAccountId = $this->getAccountId('REVENUE', $tenantId);

        $sql = "
            INSERT INTO journal_lines
            (journal_entry_id, account_id, debit, credit, description, created_at)
            VALUES
            (?, ?, ?, 0, 'Cash from sales', NOW()),
            (?, ?, 0, ?, 'Sales revenue', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $journalId, $cashAccountId, $totalAmount,
            $journalId, $revenueAccountId, $totalAmount
        ]);

        // Post to general ledger
        $this->postToGeneralLedger($journalId, $tenantId, $branchId);

        return $journalId;
    }

    public function createInventoryJournal($stockTransactionId, $amount, $branchId, $transactionType = 'OUT')
    {
        // Get tenant_id from stock transaction
        $sql = "SELECT tenant_id FROM stock_transactions WHERE stock_transaction_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$stockTransactionId]);
        $stockTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$stockTransaction) {
            throw new Exception("Stock transaction not found: {$stockTransactionId}");
        }
        
        $tenantId = $stockTransaction['tenant_id'];

        // Check if journal entry already exists for this stock transaction (idempotency)
        $sql = "SELECT journal_id FROM journal_entries WHERE reference_type = 'STOCK_TRANSACTION' AND reference_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$stockTransactionId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return [
                'success' => true,
                'journal_id' => $existing['journal_id'],
                'message' => 'Journal entry already exists for this stock transaction'
            ];
        }

        $journalNumber = 'JE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO journal_entries
            (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status, created_at)
            VALUES (?, ?, ?, CURDATE(), 'STOCK_TRANSACTION', ?, 'Inventory movement', 'POSTED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $journalNumber, $stockTransactionId]);

        $journalId = $this->db->lastInsertId();

        $inventoryAccountId = $this->getAccountId('INVENTORY', $tenantId);
        $cogsAccountId = $this->getAccountId('COGS', $tenantId);

        if ($transactionType === 'OUT') {
            // Inventory going out (sale)
            $sql = "
                INSERT INTO journal_lines
                (journal_entry_id, account_id, debit, credit, description, created_at)
                VALUES
                (?, ?, 0, ?, 'Cost of goods sold', NOW()),
                (?, ?, ?, 0, 'Inventory reduction', NOW())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $journalId, $cogsAccountId, $amount,
                $journalId, $inventoryAccountId, $amount
            ]);
        } else {
            // Inventory coming in (purchase)
            $sql = "
                INSERT INTO journal_lines
                (journal_entry_id, account_id, debit, credit, description, created_at)
                VALUES
                (?, ?, ?, 0, 'Inventory addition', NOW()),
                (?, ?, 0, ?, 'Accounts payable or cash', NOW())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $journalId, $inventoryAccountId, $amount,
                $journalId, $cogsAccountId, $amount
            ]);
        }

        // Post to general ledger
        $this->postToGeneralLedger($journalId, $tenantId, $branchId);

        return $journalId;
    }

    public function createPaymentJournal($paymentId, $amount, $branchId, $paymentMethod = 'CASH')
    {
        // Get tenant_id from payment
        $sql = "SELECT tenant_id FROM payments WHERE payment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            throw new Exception("Payment not found: {$paymentId}");
        }
        
        $tenantId = $payment['tenant_id'];

        // Check if journal entry already exists for this payment (idempotency)
        $sql = "SELECT journal_id FROM journal_entries WHERE reference_type = 'PAYMENT' AND reference_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$paymentId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return [
                'success' => true,
                'journal_id' => $existing['journal_id'],
                'message' => 'Journal entry already exists for this payment'
            ];
        }

        $journalNumber = 'JE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $sql = "
            INSERT INTO journal_entries
            (tenant_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, status, created_at)
            VALUES (?, ?, ?, CURDATE(), 'PAYMENT', ?, 'Payment received', 'POSTED', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $journalNumber, $paymentId]);

        $journalId = $this->db->lastInsertId();

        $cashAccountId = $this->getAccountId('CASH', $tenantId);
        $arAccountId = $this->getAccountId('ACCOUNTS_RECEIVABLE', $tenantId);

        $sql = "
            INSERT INTO journal_lines
            (journal_entry_id, account_id, debit, credit, description, created_at)
            VALUES
            (?, ?, ?, 0, 'Cash received', NOW()),
            (?, ?, 0, ?, 'Accounts receivable reduction', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $journalId, $cashAccountId, $amount,
            $journalId, $arAccountId, $amount
        ]);

        // Post to general ledger
        $this->postToGeneralLedger($journalId, $tenantId, $branchId);

        return $journalId;
    }

    private function postToGeneralLedger($journalId, $tenantId, $branchId)
    {
        // Get journal entry
        $sql = "SELECT * FROM journal_entries WHERE journal_entry_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$journalId]);
        $journalEntry = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get journal lines
        $sql = "SELECT * FROM journal_lines WHERE journal_entry_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$journalId]);
        $journalLines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Post each line to general ledger
        foreach ($journalLines as $line) {
            $sql = "
                INSERT INTO general_ledger
                (tenant_id, branch_id, journal_entry_id, journal_line_id, account_id, transaction_date, reference_type, reference_id, description, debit_amount, credit_amount, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $tenantId,
                $branchId,
                $journalId,
                $line['journal_line_id'],
                $line['account_id'],
                $journalEntry['journal_date'],
                $journalEntry['reference_type'],
                $journalEntry['reference_id'],
                $line['description'],
                $line['debit'],
                $line['credit']
            ]);
        }
    }



    private function getAccountId($accountType, $tenantId = null)
    {
        $sql = "
            SELECT account_id FROM chart_of_accounts
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
