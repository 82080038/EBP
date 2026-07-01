<?php

namespace Modules\Inventory\Models;

class StockTransaction
{
    public $stock_transaction_id;
    public $tenant_id;
    public $branch_id;
    public $product_id;
    public $transaction_type;
    public $quantity;
    public $unit;
    public $reference_type;
    public $reference_id;
    public $notes;
    public $created_at;

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
