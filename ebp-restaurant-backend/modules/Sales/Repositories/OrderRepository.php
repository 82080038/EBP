<?php


require_once
"../../../config/database.php";



class OrderRepository
{


private $db;



public function __construct()
{

    $database =
        new Database();


    $this->db =
        $database->connect();

}



public function saveOrder($data)
{


$sql="
INSERT INTO orders

(
tenant_id,
branch_id,
customer_id,
total_amount,
status,
created_at

)

VALUES

(
?,
?,
?,
?,
'NEW',
NOW()

)

";



$stmt =
$this->db->prepare($sql);



$stmt->execute([

$data['tenant_id'],

$data['branch_id'],

$data['customer_id'],

$data['total_amount']

]);



return 
$this->db->lastInsertId();



}





public function saveDetail(
$orderId,
$item
)
{


$sql="
INSERT INTO order_details

(
order_id,
menu_id,
qty,
price

)

VALUES

(
?,
?,
?,
?

)

";



$stmt =
$this->db->prepare($sql);



$stmt->execute([


$orderId,


$item['menu_id'],


$item['qty'],


$item['price']


]);



}


}
