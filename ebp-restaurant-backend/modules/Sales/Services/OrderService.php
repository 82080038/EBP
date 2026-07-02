<?php

if (!class_exists('OrderRepository')) {
    require_once __DIR__ . '/../Repositories/OrderRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}
if (!class_exists('Transaction')) {
    require_once __DIR__ . '/../../../core/Transaction.php';
}
if (!class_exists('StockEngine')) {
    require_once __DIR__ . '/../../../core/Engines/StockEngine.php';
}

if (!class_exists('KitchenEngine')) {
    require_once __DIR__ . '/../../../core/Engines/KitchenEngine.php';
}
if (!class_exists('AccountingEngine')) {
    require_once __DIR__ . '/../../../core/Engines/AccountingEngine.php';
}
if (!class_exists('Audit')) {
    require_once __DIR__ . '/../../../core/Audit.php';
}



class OrderService
{


private $repository;

private $db;



public function __construct()
{

    $this->repository =
        new OrderRepository();

    $database = new Database();

    $this->db = $database->connect();

}



public function createOrder($data, $userId, $tenantId, $branchId)
{


    /*
    ================================
    VALIDATION
    ================================
    */


    if(
        empty($data['items'])
    ){

        return [

            "success"=>false,

            "message"=>"Order kosong"

        ];

    }



    /*
    ================================
    HITUNG TOTAL
    ================================
    */


    $total=0;



    foreach(
        $data['items']
        as $item
    ){

        $total +=
        $item['price']
        *
        $item['qty'];

    }

    // Add delivery fee if applicable
    if (isset($data['delivery_fee'])) {
        $total += $data['delivery_fee'];
    }

    // Add service charge if applicable
    if (isset($data['service_charge'])) {
        $total += $data['service_charge'];
    }



    /*
    ================================
    DATABASE TRANSACTION
    ================================
    */


    $transaction = new Transaction($this->db);

    $transaction->begin();



    try {



        /*
        ================================
        SIMPAN ORDER
        ================================
        */


        $orderData = [
            "tenant_id"=>$tenantId,
            "branch_id"=>$branchId,
            "user_id"=>$userId,
            "table_id"=>$data['table_id'] ?? null,
            "order_type"=>$data['order_type'] ?? 'DINE_IN',
            "is_open_order"=>isset($data['is_open_order']) ? ($data['is_open_order'] ? 1 : 0) : 1,
            "is_priority"=>isset($data['is_priority']) ? ($data['is_priority'] ? 1 : 0) : 0,
            "is_held"=>isset($data['is_held']) ? ($data['is_held'] ? 1 : 0) : 0,
            "customer_name"=>$data['customer_name'] ?? null,
            "customer_phone"=>$data['customer_phone'] ?? null,
            "customer_address"=>$data['customer_address'] ?? null,
            "delivery_fee"=>$data['delivery_fee'] ?? 0,
            "delivery_time"=>$data['delivery_time'] ?? null,
            "subtotal"=>$total,
            "total_amount"=>$total,
            "notes"=>$data['notes'] ?? null
        ];

        $orderId =
        $this->repository
        ->saveOrder($orderData);



        /*
        SIMPAN DETAIL

        */

        foreach(
            $data['items']
            as $item
        ){

            $this->repository
            ->saveDetail(

                $orderId,

                $item

            );

        }



        /*
        ================================
        STOCK ENGINE - DEDUCT INVENTORY
        ================================
        */


        // Skip stock engine for Phase 1 (requires recipe tables)
        // $stockEngine = new StockEngine($this->db);
        // $stockEngine->deductFromRecipe($orderId, $branchId);



        /*
        ================================
        KITCHEN ENGINE - CREATE KITCHEN ORDER
        ================================
        */


        // Skip kitchen engine for Phase 1 (requires recipe tables)
        // $kitchenEngine = new KitchenEngine($this->db);
        // $kitchenEngine->createKitchenOrder($orderId);



        /*
        ================================
        ACCOUNTING ENGINE - CREATE JOURNAL
        ================================
        */


        // Skip accounting engine for Phase 1 (requires accounting tables)
        // $accountingEngine = new AccountingEngine($this->db);
        // $accountingEngine->createSalesJournal($orderId, $total, $branchId);



        /*
        ================================
        AUDIT TRAIL
        ================================
        */


        // Skip audit trail for Phase 1 (requires audit table)
        // $audit = new Audit($this->db);
        // $audit->log(
        //     $tenantId,
        //     $userId,
        //     'SALES',
        //     'CREATE_ORDER',
        //     $orderId,
        //     'orders',
        //     null,
        //     ['total_amount' => $total, 'items_count' => count($data['items'])]
        // );



        /*
        ================================
        COMMIT TRANSACTION
        ================================
        */


        $transaction->commit();


        return [

            "success"=>true,

            "message"=>"Order berhasil",

            "order_id"=>$orderId,

            "total"=>$total

        ];



    } catch (Exception $e) {



        /*
        ================================
        ROLLBACK ON ERROR
        ================================
        */


        $transaction->rollback();



        return [

            "success"=>false,

            "message"=>"Order gagal: " . $e->getMessage()

        ];



    }
}

public function updateOrder($orderId, $data, $userId, $tenantId)
{
    $transaction = new Transaction($this->db);
    $transaction->begin();

    try {
        // Update order basic info
        $updateData = [];
        
        if (isset($data['table_id'])) {
            $updateData['table_id'] = $data['table_id'];
        }
        
        if (isset($data['is_held'])) {
            $updateData['is_held'] = $data['is_held'];
            $updateData['hold_reason'] = $data['hold_reason'] ?? null;
        }
        
        if (isset($data['is_priority'])) {
            $updateData['is_priority'] = $data['is_priority'];
        }
        
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }
        
        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }

        if (!empty($updateData)) {
            $this->repository->updateOrder($orderId, $updateData);
        }

        // Update order items if provided
        if (isset($data['items'])) {
            // Remove existing items
            $this->repository->deleteOrderItems($orderId);
            
            // Add new items
            $total = 0;
            foreach ($data['items'] as $item) {
                $this->repository->saveDetail($orderId, $item);
                $total += $item['price'] * $item['qty'];
            }
            
            // Update order total
            $this->repository->updateOrder($orderId, [
                'subtotal' => $total,
                'total_amount' => $total
            ]);
        }

        // Skip audit trail for Phase 1
        // $audit = new Audit($this->db);
        // $audit->log(
        //     $tenantId,
        //     $userId,
        //     'SALES',
        //     'UPDATE_ORDER',
        //     $orderId,
        //     'orders',
        //     null,
        //     $updateData
        // );

        $transaction->commit();

        return [
            "success" => true,
            "message" => "Order updated successfully"
        ];

    } catch (Exception $e) {
        $transaction->rollback();
        return [
            "success" => false,
            "message" => "Order update failed: " . $e->getMessage()
        ];
    }
}

public function closeOrder($orderId, $userId, $tenantId)
{
    $transaction = new Transaction($this->db);
    $transaction->begin();

    try {
        $this->repository->updateOrder($orderId, [
            'is_open_order' => 0,
            'status' => 'COMPLETED'
        ]);

        // Skip audit trail for Phase 1
        // $audit = new Audit($this->db);
        // $audit->log(
        //     $tenantId,
        //     $userId,
        //     'SALES',
        //     'CLOSE_ORDER',
        //     $orderId,
        //     'orders',
        //     null,
        //     null
        // );

        $transaction->commit();

        return [
            "success" => true,
            "message" => "Order closed successfully"
        ];

    } catch (Exception $e) {
        $transaction->rollback();
        return [
            "success" => false,
            "message" => "Order close failed: " . $e->getMessage()
        ];
    }
}

public function holdOrder($orderId, $reason, $userId, $tenantId)
{
    $transaction = new Transaction($this->db);
    $transaction->begin();

    try {
        $this->repository->updateOrder($orderId, [
            'is_held' => 1,
            'hold_reason' => $reason
        ]);

        // Skip audit trail for Phase 1
        // $audit = new Audit($this->db);
        // $audit->log(
        //     $tenantId,
        //     $userId,
        //     'SALES',
        //     'HOLD_ORDER',
        //     $orderId,
        //     'orders',
        //     null,
        //     ['reason' => $reason]
        // );

        $transaction->commit();

        return [
            "success" => true,
            "message" => "Order held successfully"
        ];

    } catch (Exception $e) {
        $transaction->rollback();
        return [
            "success" => false,
            "message" => "Order hold failed: " . $e->getMessage()
        ];
    }
}

public function recallOrder($orderId, $userId, $tenantId)
{
    $transaction = new Transaction($this->db);
    $transaction->begin();

    try {
        $this->repository->updateOrder($orderId, [
            'is_held' => 0,
            'hold_reason' => null
        ]);

        // Skip audit trail for Phase 1
        // $audit = new Audit($this->db);
        // $audit->log(
        //     $tenantId,
        //     $userId,
        //     'SALES',
        //     'RECALL_ORDER',
        //     $orderId,
        //     'orders',
        //     null,
        //     null
        // );

        $transaction->commit();

        return [
            "success" => true,
            "message" => "Order recalled successfully"
        ];

    } catch (Exception $e) {
        $transaction->rollback();
        return [
            "success" => false,
            "message" => "Order recall failed: " . $e->getMessage()
        ];
    }
}

public function setPriorityOrder($orderId, $isPriority, $userId, $tenantId)
{
    $transaction = new Transaction($this->db);
    $transaction->begin();

    try {
        $this->repository->updateOrder($orderId, [
            'is_priority' => $isPriority ? 1 : 0
        ]);

        // Skip audit trail for Phase 1
        // $audit = new Audit($this->db);
        // $audit->log(
        //     $tenantId,
        //     $userId,
        //     'SALES',
        //     'SET_PRIORITY',
        //     $orderId,
        //     'orders',
        //     null,
        //     ['is_priority' => $isPriority]
        // );

        $transaction->commit();

        return [
            "success" => true,
            "message" => "Order priority updated successfully"
        ];

    } catch (Exception $e) {
        $transaction->rollback();
        return [
            "success" => false,
            "message" => "Priority update failed: " . $e->getMessage()
        ];
    }
}

public function splitBill($orderId, $splitType, $totalSplits, $splitData, $userId, $tenantId)
{
    $transaction = new Transaction($this->db);
    $transaction->begin();

    try {
        // Create split bill record
        $splitBillId = $this->repository->createSplitBill($orderId, $splitType, $totalSplits);

        // Create split bill items
        foreach ($splitData as $splitItem) {
            $this->repository->createSplitBillItem($splitBillId, $splitItem);
        }

        // Skip audit trail for Phase 1
        // $audit = new Audit($this->db);
        // $audit->log(
        //     $tenantId,
        //     $userId,
        //     'SALES',
        //     'SPLIT_BILL',
        //     $splitBillId,
        //     'split_bills',
        //     null,
        //     ['split_type' => $splitType, 'total_splits' => $totalSplits]
        // );

        $transaction->commit();

        return [
            "success" => true,
            "message" => "Bill split successfully",
            "split_bill_id" => $splitBillId
        ];

    } catch (Exception $e) {
        $transaction->rollback();
        return [
            "success" => false,
            "message" => "Bill split failed: " . $e->getMessage()
        ];
    }
}

public function addPayment($orderId, $paymentMethod, $amount, $referenceNumber, $userId, $tenantId)
{
    $transaction = new Transaction($this->db);
    $transaction->begin();

    try {
        // Create payment record
        $paymentId = $this->repository->createPayment($orderId, $paymentMethod, $amount, $referenceNumber);

        // Update order paid amount
        $order = $this->repository->getOrderById($orderId);
        $newPaidAmount = $order['paid_amount'] + $amount;
        $paymentStatus = ($newPaidAmount >= $order['total_amount']) ? 'PAID' : 'PARTIAL';
        
        $this->repository->updateOrder($orderId, [
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus
        ]);

        // Skip audit trail for Phase 1
        // $audit = new Audit($this->db);
        // $audit->log(
        //     $tenantId,
        //     $userId,
        //     'SALES',
        //     'ADD_PAYMENT',
        //     $paymentId,
        //     'payments',
        //     null,
        //     ['payment_method' => $paymentMethod, 'amount' => $amount]
        // );

        $transaction->commit();

        return [
            "success" => true,
            "message" => "Payment added successfully",
            "payment_id" => $paymentId
        ];

    } catch (Exception $e) {
        $transaction->rollback();
        return [
            "success" => false,
            "message" => "Payment failed: " . $e->getMessage()
        ];
    }
}



}
