<?php

class StockEngine
{

    private $db;



    public function __construct($db)
    {

        $this->db = $db;

    }



    public function deductFromRecipe($orderId, $branchId)
    {

        $sql = "
            SELECT od.menu_id, od.qty, rd.item_id, rd.quantity
            FROM order_details od
            INNER JOIN recipe_details rd ON od.menu_id = rd.menu_id
            WHERE od.order_id = ?
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$orderId]);

        $recipeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);



        foreach ($recipeItems as $item) {

            $requiredQty = $item['qty'] * $item['quantity'];



            $this->updateStock(

                $branchId,

                $item['item_id'],

                -$requiredQty,

                'SALE_USAGE',

                $orderId

            );

        }

    }



    private function updateStock($branchId, $itemId, $quantity, $type, $referenceId)
    {

        $sql = "
            UPDATE stock_balances
            SET quantity = quantity + ?,
                last_transaction_date = NOW()
            WHERE branch_id = ? AND item_id = ?
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$quantity, $branchId, $itemId]);



        $sql = "
            INSERT INTO stock_transactions
            (tenant_id, branch_id, item_id, transaction_type, quantity, reference_type, reference_id, transaction_date)
            VALUES (1, ?, ?, ?, ?, 'ORDER', ?, NOW())
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([$branchId, $itemId, $type, $quantity, $referenceId]);

    }

}
