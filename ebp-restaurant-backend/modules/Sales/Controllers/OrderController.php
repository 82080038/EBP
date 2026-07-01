<?php


require_once 
"../Services/OrderService.php";

require_once 
"../../../core/Response.php";

require_once 
"../../../core/Middleware/AuthMiddleware.php";

require_once 
"../../../core/Middleware/PermissionMiddleware.php";



class OrderController
{


    private $service;



    public function __construct()
    {

        $this->service =
            new OrderService();

    }



    public function create()
    {


        $authMiddleware = new AuthMiddleware();

        $user = $authMiddleware->authenticate();



        $permissionMiddleware = new PermissionMiddleware();

        $permissionMiddleware->check($user['user_id'], 'ORDER_CREATE');



        $input =
            json_decode(
                file_get_contents("php://input"),
                true
            );



        $result =
            $this->service
            ->createOrder(
                $input,
                $user['user_id'],
                $user['tenant_id'],
                $user['branch_id']
            );



        if ($result['success']) {

            Response::success(
                $result['message'],
                [
                    'order_id' => $result['order_id'],
                    'total' => $result['total']
                ]
            );

        } else {

            Response::error($result['message']);

        }


    }


}
