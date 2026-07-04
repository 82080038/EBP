<?php

class KitchenOrderItem
{
    public $kitchen_order_item_id;
    public $kitchen_order_id;
    public $order_item_id;
    public $product_id;
    public $quantity;
    public $notes;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
