<?php

use PDO;

class StockEngine
{

    private $db;



    public function __construct($db)
    {

        $this->db = $db;

    }



    public function deductFromRecipe($orderId, $branchId)
    {
        // Get order details to determine tenant
        $sql = "SELECT tenant_id FROM orders WHERE order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return false;
        }
        
        $tenantId = $order['tenant_id'];

        $sql = "
            SELECT od.product_id, od.quantity, ri.ingredient_id, ri.quantity as recipe_qty
            FROM order_items od
            INNER JOIN recipes r ON od.product_id = r.product_id
            INNER JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
            WHERE od.order_id = ? AND r.status = 'ACTIVE'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        $recipeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Validate stock availability before deduction
        foreach ($recipeItems as $item) {
            $requiredQty = $item['quantity'] * $item['recipe_qty'];
            $currentStock = $this->getCurrentStock($tenantId, $branchId, $item['ingredient_id']);
            
            if ($currentStock < $requiredQty) {
                // Insufficient stock - throw exception or return error
                throw new Exception("Insufficient stock for ingredient ID {$item['ingredient_id']}. Required: {$requiredQty}, Available: {$currentStock}");
            }
        }

        // Proceed with stock deduction
        foreach ($recipeItems as $item) {
            $requiredQty = $item['quantity'] * $item['recipe_qty'];

            $this->updateStock(
                $tenantId,
                $branchId,
                $item['ingredient_id'],
                -$requiredQty,
                'SALE_USAGE',
                $orderId
            );
        }
        
        return true;
    }

    /**
     * Get current stock balance for an ingredient
     * 
     * @param int $tenantId Tenant ID
     * @param int $branchId Branch ID
     * @param int $inventoryId Inventory/Ingredient ID
     * @return float Current stock quantity
     */
    private function getCurrentStock($tenantId, $branchId, $inventoryId)
    {
        $sql = "
            SELECT COALESCE(quantity, 0) as quantity
            FROM stock_balances
            WHERE tenant_id = ? AND branch_id = ? AND inventory_id = ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $inventoryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (float)$result['quantity'] : 0;
    }



    private function updateStock($tenantId, $branchId, $inventoryId, $quantity, $type, $referenceId)
    {
        // First, ensure stock balance exists
        $sql = "INSERT INTO stock_balances (tenant_id, branch_id, inventory_id, quantity, last_transaction_date) 
                VALUES (?, ?, ?, 0, NOW())
                ON DUPLICATE KEY UPDATE last_transaction_date = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $inventoryId]);

        $sql = "
            UPDATE stock_balances
            SET quantity = quantity + ?,
                last_transaction_date = NOW()
            WHERE branch_id = ? AND inventory_id = ?
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$quantity, $branchId, $inventoryId]);

        // Get product_id from inventory
        $sql = "SELECT product_id FROM inventory WHERE inventory_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inventoryId]);
        $inventory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$inventory) {
            return;
        }
        
        $productId = $inventory['product_id'];
        
        // Determine transaction type
        $transactionType = ($quantity < 0) ? 'OUT' : 'IN';
        $absQuantity = abs($quantity);
        
        // Get unit from inventory
        $sql = "SELECT unit FROM inventory WHERE inventory_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$inventoryId]);
        $unit = $stmt->fetchColumn();

        $sql = "
            INSERT INTO stock_transactions
            (tenant_id, branch_id, product_id, transaction_type, quantity, unit, reference_type, reference_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'ORDER', ?, NOW())
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$tenantId, $branchId, $productId, $transactionType, $absQuantity, $unit, $referenceId]);

    }

}
