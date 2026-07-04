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

        $sql = "
            INSERT INTO journal_entries
            (tenant_id, branch_id, journal_number, transaction_date, reference_type, reference_id, description, status, created_at)
            VALUES (1, ?, CONCAT('JNL-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(?, 6, '0')), CURDATE(), 'ORDER', ?, 'Sales Order', 'POSTED', NOW())
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$branchId, $orderId, $orderId]);



        $journalId = $this->db->lastInsertId();



        $cashAccountId = $this->getAccountId('CASH');

        $revenueAccountId = $this->getAccountId('REVENUE');



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



    private function getAccountId($accountType)
    {

        $sql = "
            SELECT account_id FROM accounts
            WHERE account_code LIKE ?
            LIMIT 1
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$accountType . '%']);



        $result = $stmt->fetch(PDO::FETCH_ASSOC);



        return $result ? $result['account_id'] : 1;

    }

}
