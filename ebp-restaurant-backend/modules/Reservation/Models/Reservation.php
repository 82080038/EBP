<?php

namespace Modules\Reservation\Models;

class Reservation
{
    public $reservation_id;
    public $tenant_id;
    public $branch_id;
    public $reservation_number;
    public $customer_name;
    public $customer_phone;
    public $customer_email;
    public $table_id;
    public $reservation_date;
    public $reservation_time;
    public $party_size;
    public $status;
    public $notes;
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
