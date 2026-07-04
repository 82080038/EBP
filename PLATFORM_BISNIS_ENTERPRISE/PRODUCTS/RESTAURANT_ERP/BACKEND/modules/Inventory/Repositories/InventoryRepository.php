<?php

if (!class_exists('Inventory')) {
    require_once __DIR__ . '/../Models/Inventory.php';
}

use PDO;

class InventoryRepository
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function findAll(int $tenantId, ?int $branchId = null): array
    {
        $sql = "
            SELECT i.*, p.product_name, p.product_code 
            FROM inventory i
            JOIN products p ON i.product_id = p.product_id
            WHERE i.tenant_id = :tenant_id AND i.deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND i.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY p.product_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $inventory = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inventory[] = new Inventory($row);
        }
        
        return $inventory;
    }

    public function findById(int $tenantId, int $inventoryId): ?Inventory
    {
        $stmt = $this->db->prepare("
            SELECT i.*, p.product_name, p.product_code 
            FROM inventory i
            JOIN products p ON i.product_id = p.product_id
            WHERE i.tenant_id = :tenant_id AND i.inventory_id = :inventory_id AND i.deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'inventory_id' => $inventoryId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Inventory($row) : null;
    }

    public function findByProduct(int $tenantId, int $branchId, int $productId): ?Inventory
    {
        $stmt = $this->db->prepare("
            SELECT * FROM inventory 
            WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND product_id = :product_id AND deleted_at IS NULL
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'branch_id' => $branchId, 'product_id' => $productId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Inventory($row) : null;
    }

    public function getLowStock(int $tenantId, ?int $branchId = null): array
    {
        $sql = "
            SELECT i.*, p.product_name, p.product_code 
            FROM inventory i
            JOIN products p ON i.product_id = p.product_id
            WHERE i.tenant_id = :tenant_id 
            AND i.deleted_at IS NULL
            AND i.quantity <= i.minimum_stock
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND i.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY i.quantity ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $inventory = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inventory[] = new Inventory($row);
        }
        
        return $inventory;
    }

    public function create(Inventory $inventory): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO inventory 
            (tenant_id, branch_id, product_id, quantity, unit, minimum_stock, maximum_stock, status)
            VALUES 
            (:tenant_id, :branch_id, :product_id, :quantity, :unit, :minimum_stock, :maximum_stock, :status)
        ");
        
        return $stmt->execute([
            'tenant_id' => $inventory->tenant_id,
            'branch_id' => $inventory->branch_id,
            'product_id' => $inventory->product_id,
            'quantity' => $inventory->quantity ?? 0,
            'unit' => $inventory->unit ?? 'unit',
            'minimum_stock' => $inventory->minimum_stock ?? 0,
            'maximum_stock' => $inventory->maximum_stock ?? 0,
            'status' => $inventory->status ?? 'ACTIVE'
        ]);
    }

    public function update(Inventory $inventory): bool
    {
        $stmt = $this->db->prepare("
            UPDATE inventory 
            SET quantity = :quantity,
                unit = :unit,
                minimum_stock = :minimum_stock,
                maximum_stock = :maximum_stock,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND inventory_id = :inventory_id
        ");
        
        return $stmt->execute([
            'tenant_id' => $inventory->tenant_id,
            'inventory_id' => $inventory->inventory_id,
            'quantity' => $inventory->quantity,
            'unit' => $inventory->unit,
            'minimum_stock' => $inventory->minimum_stock,
            'maximum_stock' => $inventory->maximum_stock,
            'status' => $inventory->status
        ]);
    }

    public function updateQuantity(int $tenantId, int $branchId, int $productId, float $quantity): bool
    {
        $stmt = $this->db->prepare("
            UPDATE inventory 
            SET quantity = :quantity,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND product_id = :product_id
        ");
        
        return $stmt->execute([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
    }

    public function delete(int $tenantId, int $inventoryId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE inventory 
            SET deleted_at = CURRENT_TIMESTAMP 
            WHERE tenant_id = :tenant_id AND inventory_id = :inventory_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'inventory_id' => $inventoryId]);
    }

    public function recordTransaction(StockTransaction $transaction): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO stock_transactions 
            (tenant_id, branch_id, product_id, transaction_type, quantity, unit, reference_type, reference_id, notes)
            VALUES 
            (:tenant_id, :branch_id, :product_id, :transaction_type, :quantity, :unit, :reference_type, :reference_id, :notes)
        ");
        
        return $stmt->execute([
            'tenant_id' => $transaction->tenant_id,
            'branch_id' => $transaction->branch_id,
            'product_id' => $transaction->product_id,
            'transaction_type' => $transaction->transaction_type,
            'quantity' => $transaction->quantity,
            'unit' => $transaction->unit,
            'reference_type' => $transaction->reference_type,
            'reference_id' => $transaction->reference_id,
            'notes' => $transaction->notes
        ]);
    }

    public function getTransactions(int $tenantId, ?int $branchId = null, ?int $productId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "
            SELECT st.*, p.product_name, p.product_code 
            FROM stock_transactions st
            JOIN products p ON st.product_id = p.product_id
            WHERE st.tenant_id = :tenant_id
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND st.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        if ($productId !== null) {
            $sql .= " AND st.product_id = :product_id";
            $params['product_id'] = $productId;
        }
        
        if ($dateFrom !== null) {
            $sql .= " AND st.created_at >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo !== null) {
            $sql .= " AND st.created_at <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $sql .= " ORDER BY st.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $transactions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $transactions[] = new StockTransaction($row);
        }
        
        return $transactions;
    }
}
