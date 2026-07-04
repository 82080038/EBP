<?php

class Product
{
    public $product_id;
    public $tenant_id;
    public $category_id;
    public $product_code;
    public $product_name;
    public $description;
    public $price;
    public $cost;
    public $image_url;
    public $is_available;
    public $preparation_time;
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
