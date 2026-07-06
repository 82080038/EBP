<?php



class BonusRepository
{
    private $db;

    public function __construct($db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            $host = 'localhost';
            $dbname = 'ebp_restaurant_db';
            $username = 'ebp_app';
            $password = 'ebp_secure_password_2026';
            $socket = '/opt/lampp/var/mysql/mysql.sock';

            $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
            $this->db = new PDO($dsn, $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function createBonus($data)
    {
        $sql = "INSERT INTO bonuses (tenant_id, branch_id, employee_id, bonus_type, bonus_amount, bonus_period_start, bonus_period_end, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['employee_id'],
            $data['bonus_type'],
            $data['bonus_amount'],
            $data['bonus_period_start'] ?? null,
            $data['bonus_period_end'] ?? null,
            $data['reason'] ?? null,
            $data['status'] ?? 'PENDING'
        ]);
        return $this->db->lastInsertId();
    }

    public function updateBonusStatus($bonusId, $status, $approvedBy)
    {
        $sql = "UPDATE bonuses SET status = ?, approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE bonus_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $approvedBy, $bonusId]);
    }

    public function getEmployeeBonuses($tenantId, $branchId, $employeeId)
    {
        $sql = "SELECT * FROM bonuses WHERE tenant_id = ? AND branch_id = ? AND employee_id = ? AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingBonuses($tenantId, $branchId)
    {
        $sql = "SELECT b.*, e.employee_name FROM bonuses b JOIN employees e ON b.employee_id = e.employee_id WHERE b.tenant_id = ? AND b.branch_id = ? AND b.status = 'PENDING' AND b.deleted_at IS NULL ORDER BY b.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
