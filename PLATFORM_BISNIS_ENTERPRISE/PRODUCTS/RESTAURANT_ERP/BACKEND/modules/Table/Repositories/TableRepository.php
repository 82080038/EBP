<?php

if (!class_exists('Table')) {
    require_once __DIR__ . '/../Models/Table.php';
}

use PDO;

class TableRepository
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

    public function findAll(int $tenantId, ?int $branchId = null): array
    {
        $sql = "
            SELECT * FROM tables 
            WHERE tenant_id = :tenant_id AND deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY area ASC, table_number ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tables[] = new Table($row);
        }
        
        return $tables;
    }

    public function findById(int $tenantId, int $tableId): ?Table
    {
        $stmt = $this->db->prepare("
            SELECT * FROM tables 
            WHERE tenant_id = :tenant_id AND table_id = :table_id AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'table_id' => $tableId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Table($row) : null;
    }

    public function findByNumber(int $tenantId, int $branchId, string $tableNumber): ?Table
    {
        $stmt = $this->db->prepare("
            SELECT * FROM tables 
            WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND table_number = :table_number AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'table_number' => $tableNumber]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Table($row) : null;
    }

    public function findAvailable(int $tenantId, ?int $branchId = null): array
    {
        $sql = "
            SELECT * FROM tables 
            WHERE tenant_id = :tenant_id AND status = 'AVAILABLE' AND deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY area ASC, table_number ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tables[] = new Table($row);
        }
        
        return $tables;
    }

    public function create(Table $table): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tables 
            (tenant_id, branch_id, table_number, table_name, capacity, area, status)
            VALUES 
            (:tenant_id, :branch_id, :table_number, :table_name, :capacity, :area, :status)
        ");
        
        return $stmt->execute([
            'tenant_id' => $table->tenant_id,
            'branch_id' => $table->branch_id,
            'table_number' => $table->table_number,
            'table_name' => $table->table_name,
            'capacity' => $table->capacity ?? 4,
            'area' => $table->area,
            'status' => $table->status ?? 'AVAILABLE'
        ]);
    }

    public function update(Table $table): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tables 
            SET table_name = :table_name,
                capacity = :capacity,
                area = :area,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND table_id = :table_id
        ");
        
        return $stmt->execute([
            'tenant_id' => $table->tenant_id,
            'table_id' => $table->table_id,
            'table_name' => $table->table_name,
            'capacity' => $table->capacity,
            'area' => $table->area,
            'status' => $table->status
        ]);
    }

    public function updateStatus(int $tenantId, int $tableId, string $status): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tables 
            SET status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND table_id = :table_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'table_id' => $tableId, 'status' => $status]);
    }

    public function delete(int $tenantId, int $tableId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tables 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE tenant_id = :tenant_id AND table_id = :table_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'table_id' => $tableId]);
    }
}
