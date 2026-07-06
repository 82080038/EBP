<?php

if (!class_exists('KitchenOrder')) {
    require_once __DIR__ . '/../Models/KitchenOrder.php';
}
if (!class_exists('KitchenOrderItem')) {
    require_once __DIR__ . '/../Models/KitchenOrderItem.php';
}

use PDO;

class KitchenRepository
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
            SELECT ko.*, o.order_number, o.table_id 
            FROM kitchen_orders ko
            JOIN orders o ON ko.order_id = o.order_id
            WHERE ko.tenant_id = :tenant_id
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND ko.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY 
            CASE ko.priority 
                WHEN 'URGENT' THEN 1
                WHEN 'HIGH' THEN 2
                WHEN 'NORMAL' THEN 3
                WHEN 'LOW' THEN 4
            END ASC,
            ko.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $kitchenOrders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $kitchenOrders[] = new KitchenOrder($row);
        }
        
        return $kitchenOrders;
    }

    public function findByStatus(int $tenantId, ?int $branchId = null, string $status = 'PENDING'): array
    {
        $sql = "
            SELECT ko.*, o.order_number, o.table_id 
            FROM kitchen_orders ko
            JOIN orders o ON ko.order_id = o.order_id
            WHERE ko.tenant_id = :tenant_id AND ko.status = :status
        ";
        
        $params = ['tenant_id' => $tenantId, 'status' => $status];
        
        if ($branchId !== null) {
            $sql .= " AND ko.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY 
            CASE ko.priority 
                WHEN 'URGENT' THEN 1
                WHEN 'HIGH' THEN 2
                WHEN 'NORMAL' THEN 3
                WHEN 'LOW' THEN 4
            END ASC,
            ko.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $kitchenOrders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $kitchenOrders[] = new KitchenOrder($row);
        }
        
        return $kitchenOrders;
    }

    public function findById(int $tenantId, int $kitchenOrderId): ?KitchenOrder
    {
        $stmt = $this->db->prepare("
            SELECT ko.*, o.order_number, o.table_id 
            FROM kitchen_orders ko
            JOIN orders o ON ko.order_id = o.order_id
            WHERE ko.tenant_id = :tenant_id AND ko.kitchen_order_id = :kitchen_order_id
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'kitchen_order_id' => $kitchenOrderId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new KitchenOrder($row) : null;
    }

    public function findByOrderId(int $tenantId, int $orderId): ?KitchenOrder
    {
        $stmt = $this->db->prepare("
            SELECT * FROM kitchen_orders 
            WHERE tenant_id = :tenant_id AND order_id = :order_id
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'order_id' => $orderId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new KitchenOrder($row) : null;
    }

    public function create(KitchenOrder $kitchenOrder): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO kitchen_orders 
            (tenant_id, branch_id, order_id, kitchen_order_number, status, priority)
            VALUES 
            (:tenant_id, :branch_id, :order_id, :kitchen_order_number, :status, :priority)
        ");
        
        return $stmt->execute([
            'tenant_id' => $kitchenOrder->tenant_id,
            'branch_id' => $kitchenOrder->branch_id,
            'order_id' => $kitchenOrder->order_id,
            'kitchen_order_number' => $kitchenOrder->kitchen_order_number,
            'status' => $kitchenOrder->status ?? 'PENDING',
            'priority' => $kitchenOrder->priority ?? 'NORMAL'
        ]);
    }

    public function updateStatus(int $tenantId, int $kitchenOrderId, string $status): bool
    {
        $sql = "UPDATE kitchen_orders SET status = :status, updated_at = CURRENT_TIMESTAMP";
        
        if ($status === 'IN_PROGRESS') {
            $sql .= ", started_at = CURRENT_TIMESTAMP";
        } elseif ($status === 'READY' || $status === 'SERVED') {
            $sql .= ", completed_at = CURRENT_TIMESTAMP";
        }
        
        $sql .= " WHERE tenant_id = :tenant_id AND kitchen_order_id = :kitchen_order_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['tenant_id' => $tenantId, 'kitchen_order_id' => $kitchenOrderId, 'status' => $status]);
    }

    public function updatePriority(int $tenantId, int $kitchenOrderId, string $priority): bool
    {
        $stmt = $this->db->prepare("
            UPDATE kitchen_orders 
            SET priority = :priority, updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND kitchen_order_id = :kitchen_order_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'kitchen_order_id' => $kitchenOrderId, 'priority' => $priority]);
    }

    public function getItems(int $kitchenOrderId): array
    {
        $stmt = $this->db->prepare("
            SELECT koi.*, p.product_name, p.product_code 
            FROM kitchen_order_items koi
            JOIN products p ON koi.product_id = p.product_id
            WHERE koi.kitchen_order_id = :kitchen_order_id
            ORDER BY koi.created_at ASC
        ");
        $stmt->execute(['kitchen_order_id' => $kitchenOrderId]);
        
        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new KitchenOrderItem($row);
        }
        
        return $items;
    }

    public function createItem(KitchenOrderItem $item): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO kitchen_order_items 
            (kitchen_order_id, order_item_id, product_id, quantity, notes, status)
            VALUES 
            (:kitchen_order_id, :order_item_id, :product_id, :quantity, :notes, :status)
        ");
        
        return $stmt->execute([
            'kitchen_order_id' => $item->kitchen_order_id,
            'order_item_id' => $item->order_item_id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'notes' => $item->notes,
            'status' => $item->status ?? 'PENDING'
        ]);
    }

    public function updateItemStatus(int $kitchenOrderItemId, string $status): bool
    {
        $stmt = $this->db->prepare("
            UPDATE kitchen_order_items 
            SET status = :status, updated_at = CURRENT_TIMESTAMP
            WHERE kitchen_order_item_id = :kitchen_order_item_id
        ");
        
        return $stmt->execute(['kitchen_order_item_id' => $kitchenOrderItemId, 'status' => $status]);
    }

    public function generateKitchenOrderNumber(int $tenantId, int $branchId): string
    {
        $prefix = 'KIT';
        $date = date('Ymd');
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM kitchen_orders 
            WHERE tenant_id = :tenant_id AND branch_id = :branch_id AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'branch_id' => $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . $date . $sequence;
    }
}
