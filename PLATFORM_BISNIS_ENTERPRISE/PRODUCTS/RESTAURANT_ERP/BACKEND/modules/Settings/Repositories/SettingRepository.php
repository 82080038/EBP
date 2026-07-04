<?php




class SettingRepository
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function findAll(int $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM settings 
            WHERE tenant_id = :tenant_id 
            ORDER BY setting_key ASC
        ");
        $stmt->execute(['tenant_id' => $tenantId]);
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[] = new Setting($row);
        }
        
        return $settings;
    }

    public function findByKey(int $tenantId, string $key): ?Setting
    {
        $stmt = $this->db->prepare("
            SELECT * FROM settings 
            WHERE tenant_id = :tenant_id AND setting_key = :setting_key
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'setting_key' => $key]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Setting($row) : null;
    }

    public function getGroup(int $tenantId, string $prefix): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM settings 
            WHERE tenant_id = :tenant_id AND setting_key LIKE :prefix
            ORDER BY setting_key ASC
        ");
        $stmt->execute(['tenant_id' => $tenantId, 'prefix' => $prefix . '%']);
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[] = new Setting($row);
        }
        
        return $settings;
    }

    public function create(Setting $setting): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO settings 
            (tenant_id, setting_key, setting_value, setting_type, description)
            VALUES 
            (:tenant_id, :setting_key, :setting_value, :setting_type, :description)
        ");
        
        return $stmt->execute([
            'tenant_id' => $setting->tenant_id,
            'setting_key' => $setting->setting_key,
            'setting_value' => $this->serializeValue($setting->setting_value, $setting->setting_type),
            'setting_type' => $setting->setting_type ?? 'STRING',
            'description' => $setting->description
        ]);
    }

    public function update(Setting $setting): bool
    {
        $stmt = $this->db->prepare("
            UPDATE settings 
            SET setting_value = :setting_value,
                setting_type = :setting_type,
                description = :description,
                updated_at = CURRENT_TIMESTAMP
            WHERE tenant_id = :tenant_id AND setting_id = :setting_id
        ");
        
        return $stmt->execute([
            'tenant_id' => $setting->tenant_id,
            'setting_id' => $setting->setting_id,
            'setting_value' => $this->serializeValue($setting->setting_value, $setting->setting_type),
            'setting_type' => $setting->setting_type,
            'description' => $setting->description
        ]);
    }

    public function upsert(int $tenantId, string $key, $value, string $type = 'STRING', ?string $description = null): bool
    {
        $existing = $this->findByKey($tenantId, $key);
        
        if ($existing) {
            $existing->setting_value = $value;
            $existing->setting_type = $type;
            if ($description !== null) {
                $existing->description = $description;
            }
            return $this->update($existing);
        } else {
            $setting = new Setting([
                'tenant_id' => $tenantId,
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => $type,
                'description' => $description
            ]);
            return $this->create($setting);
        }
    }

    public function delete(int $tenantId, int $settingId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM settings 
            WHERE tenant_id = :tenant_id AND setting_id = :setting_id
        ");
        
        return $stmt->execute(['tenant_id' => $tenantId, 'setting_id' => $settingId]);
    }

    private function serializeValue($value, string $type): string
    {
        switch ($type) {
            case 'NUMBER':
                return (string) (float) $value;
            case 'BOOLEAN':
                return $value ? '1' : '0';
            case 'JSON':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }
}
