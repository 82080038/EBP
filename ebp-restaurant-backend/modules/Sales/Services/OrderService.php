<?php


require_once
"../Repositories/OrderRepository.php";

require_once
"../../../config/database.php";

require_once
"../../../core/Transaction.php";

require_once
"../../../core/Engines/StockEngine.php";

require_once
"../../../core/Engines/KitchenEngine.php";

require_once
"../../../core/Engines/AccountingEngine.php";

require_once
"../../../core/Audit.php";



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


        $orderId =
        $this->repository
        ->saveOrder([

            "tenant_id"=>$tenantId,

            "branch_id"=>$branchId,

            "customer_id"
                =>
                $data['customer_id'] ?? null,


            "total_amount"=>$total

        ]);



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


        $stockEngine = new StockEngine($this->db);

        $stockEngine->deductFromRecipe($orderId, $branchId);



        /*
        ================================
        KITCHEN ENGINE - CREATE KITCHEN ORDER
        ================================
        */


        $kitchenEngine = new KitchenEngine($this->db);

        $kitchenEngine->createKitchenOrder($orderId);



        /*
        ================================
        ACCOUNTING ENGINE - CREATE JOURNAL
        ================================
        */


        $accountingEngine = new AccountingEngine($this->db);

        $accountingEngine->createSalesJournal($orderId, $total, $branchId);



        /*
        ================================
        AUDIT TRAIL
        ================================
        */


        $audit = new Audit($this->db);

        $audit->log(

            $tenantId,

            $userId,

            'SALES',

            'CREATE_ORDER',

            $orderId,

            'orders',

            null,

            ['total_amount' => $total, 'items_count' => count($data['items'])]

        );



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



}
