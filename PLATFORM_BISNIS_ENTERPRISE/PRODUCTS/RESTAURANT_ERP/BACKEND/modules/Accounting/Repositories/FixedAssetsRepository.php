<?php

class FixedAssetsRepository
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

    public function createAsset($data)
    {
        $sql = "INSERT INTO fixed_assets (tenant_id, branch_id, asset_code, asset_name, asset_category, purchase_date, purchase_cost, salvage_value, useful_life, depreciation_method, current_value, accumulated_depreciation, location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['asset_code'],
            $data['asset_name'],
            $data['asset_category'],
            $data['purchase_date'],
            $data['purchase_cost'],
            $data['salvage_value'],
            $data['useful_life'],
            $data['depreciation_method'],
            $data['current_value'],
            $data['accumulated_depreciation'],
            $data['location'],
            $data['status'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function getAssets($tenantId, $branchId, $status = null, $category = null)
    {
        $sql = "SELECT * FROM fixed_assets WHERE tenant_id = ? AND branch_id = ? AND deleted_at IS NULL";
        $params = [$tenantId, $branchId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if ($category) {
            $sql .= " AND asset_category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY purchase_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAsset($tenantId, $branchId, $assetId)
    {
        $sql = "SELECT * FROM fixed_assets WHERE tenant_id = ? AND branch_id = ? AND asset_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $assetId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAsset($assetId, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $assetId;
        $sql = "UPDATE fixed_assets SET " . implode(', ', $fields) . " WHERE asset_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function createDepreciation($data)
    {
        $sql = "INSERT INTO asset_depreciation (asset_id, fiscal_year, fiscal_month, depreciation_amount, accumulated_depreciation, book_value) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['asset_id'],
            $data['fiscal_year'],
            $data['fiscal_month'],
            $data['depreciation_amount'],
            $data['accumulated_depreciation'],
            $data['book_value']
        ]);
        return $this->db->lastInsertId();
    }

    public function getDepreciationSchedule($assetId)
    {
        $sql = "SELECT * FROM asset_depreciation WHERE asset_id = ? ORDER BY fiscal_year, fiscal_month";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assetId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDepreciationForPeriod($assetId, $fiscalYear, $fiscalMonth)
    {
        $sql = "SELECT * FROM asset_depreciation WHERE asset_id = ? AND fiscal_year = ? AND fiscal_month = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assetId, $fiscalYear, $fiscalMonth]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
