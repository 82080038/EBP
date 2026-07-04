<?php

class Setting
{
    public $setting_id;
    public $tenant_id;
    public $setting_key;
    public $setting_value;
    public $setting_type;
    public $description;
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
        $data = get_object_vars($this);
        
        // Convert setting value based on type
        switch ($this->setting_type) {
            case 'NUMBER':
                $data['setting_value'] = (float) $this->setting_value;
                break;
            case 'BOOLEAN':
                $data['setting_value'] = (bool) $this->setting_value;
                break;
            case 'JSON':
                $data['setting_value'] = json_decode($this->setting_value, true);
                break;
            default:
                $data['setting_value'] = (string) $this->setting_value;
        }
        
        return $data;
    }
}
