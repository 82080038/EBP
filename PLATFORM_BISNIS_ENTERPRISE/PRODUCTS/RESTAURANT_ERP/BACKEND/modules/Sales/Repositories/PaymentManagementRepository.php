<?php



class PaymentManagementRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCreditNote($data)
    {
        $sql = "INSERT INTO credit_notes (tenant_id, customer_id, credit_note_number, total_amount, remaining_amount, issue_date, due_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['customer_id'] ?? null,
            $data['credit_note_number'],
            $data['total_amount'],
            $data['remaining_amount'],
            $data['issue_date'],
            $data['due_date'] ?? null,
            $data['status'] ?? 'ACTIVE',
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function createInstallment($data)
    {
        $sql = "INSERT INTO credit_note_installments (credit_note_id, installment_number, due_date, amount, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['credit_note_id'],
            $data['installment_number'],
            $data['due_date'],
            $data['amount'],
            $data['status'] ?? 'PENDING'
        ]);
        return $this->db->lastInsertId();
    }

    public function createVoucher($data)
    {
        $sql = "INSERT INTO vouchers (tenant_id, voucher_code, voucher_name, voucher_type, discount_value, max_discount, min_purchase_amount, valid_from, valid_until, usage_limit, customer_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['voucher_code'],
            $data['voucher_name'],
            $data['voucher_type'],
            $data['discount_value'],
            $data['max_discount'] ?? null,
            $data['min_purchase_amount'] ?? 0,
            $data['valid_from'],
            $data['valid_until'],
            $data['usage_limit'] ?? null,
            $data['customer_id'] ?? null,
            $data['status'] ?? 'ACTIVE'
        ]);
        return $this->db->lastInsertId();
    }

    public function getVoucherByCode($voucherCode, $tenantId)
    {
        $sql = "SELECT * FROM vouchers WHERE voucher_code = ? AND tenant_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$voucherCode, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCashDrawer($drawerId, $tenantId)
    {
        $sql = "SELECT * FROM cash_drawers WHERE drawer_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$drawerId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCashDrawer($drawerId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $drawerId;
        
        $sql = "UPDATE cash_drawers SET " . implode(', ', $setClauses) . " WHERE drawer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function createDrawerTransaction($data)
    {
        $sql = "INSERT INTO cash_drawer_transactions (drawer_id, transaction_type, amount, payment_method, reference_number, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['drawer_id'],
            $data['transaction_type'],
            $data['amount'],
            $data['payment_method'] ?? null,
            $data['reference_number'] ?? null,
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getPaymentMethodByType($type, $tenantId)
    {
        $sql = "SELECT * FROM payment_methods WHERE method_type = ? AND tenant_id = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
