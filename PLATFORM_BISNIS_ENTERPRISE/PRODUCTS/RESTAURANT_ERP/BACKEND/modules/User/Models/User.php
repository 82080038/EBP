<?php

class User
{
    public $user_id;
    public $tenant_id;
    public $branch_id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $phone;
    public $status;
    public $is_platform_owner;
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
        $data = get_object_vars($this);
        unset($data['password']); // Don't expose password
        return $data;
    }
}
