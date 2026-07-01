<?php

namespace Modules\Table\Models;

class Table
{
    public $table_id;
    public $tenant_id;
    public $branch_id;
    public $table_number;
    public $table_name;
    public $capacity;
    public $area;
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
