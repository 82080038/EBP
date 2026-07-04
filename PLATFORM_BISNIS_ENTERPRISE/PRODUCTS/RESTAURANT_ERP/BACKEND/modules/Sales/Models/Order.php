<?php

class Order
{

    public $order_id;

    public $tenant_id;

    public $branch_id;

    public $customer_id;

    public $total_amount;

    public $status;

    public $created_at;



    public function __construct($data = [])
    {

        foreach ($data as $key => $value) {

            if (property_exists($this, $key)) {

                $this->$key = $value;

            }

        }

    }

}
