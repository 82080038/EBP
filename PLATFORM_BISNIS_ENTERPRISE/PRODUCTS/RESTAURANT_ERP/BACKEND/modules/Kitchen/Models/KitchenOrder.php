<?php

class KitchenOrder
{
    public $kitchen_order_id;
    public $tenant_id;
    public $branch_id;
    public $order_id;
    public $kitchen_order_number;
    public $status;
    public $priority;
    public $started_at;
    public $completed_at;
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
