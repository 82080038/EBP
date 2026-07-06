<?php



class LocationRepository
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

    public function getAllBranches()
    {
        $sql = "SELECT branch_id, tenant_id, branch_code, branch_name, address, phone, email, latitude, longitude, delivery_radius_km, status FROM branches WHERE status = 'ACTIVE' AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBranchById($branchId)
    {
        $sql = "SELECT * FROM branches WHERE branch_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBranchesByTenant($tenantId)
    {
        $sql = "SELECT * FROM branches WHERE tenant_id = ? AND status = 'ACTIVE' AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateLocation($branchId, $latitude, $longitude, $deliveryRadius)
    {
        $sql = "UPDATE branches SET latitude = ?, longitude = ?, delivery_radius_km = ? WHERE branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$latitude, $longitude, $deliveryRadius, $branchId]);
    }
}
