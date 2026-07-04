<?php

class Inventory
{
    public $inventory_id;
    public $tenant_id;
    public $branch_id;
    public $product_id;
    public $quantity;
    public $unit;
    public $minimum_stock;
    public $maximum_stock;
    public $status;
    public $created_at;
    public $updated_at;
    public $deleted_at;

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
