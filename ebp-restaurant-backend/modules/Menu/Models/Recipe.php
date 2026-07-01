<?php

namespace Modules\Menu\Models;

class Recipe
{
    public $recipe_id;
    public $tenant_id;
    public $product_id;
    public $recipe_code;
    public $recipe_name;
    public $instructions;
    public $yield_quantity;
    public $yield_unit;
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
