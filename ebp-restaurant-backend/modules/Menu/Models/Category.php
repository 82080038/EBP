<?php

namespace Modules\Menu\Models;

class Category
{
    public $category_id;
    public $tenant_id;
    public $category_code;
    public $category_name;
    public $description;
    public $parent_id;
    public $sort_order;
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
