<?php

if (!class_exists('SettingRepository')) {
    require_once __DIR__ . '/../Repositories/SettingRepository.php';
}



class SettingService
{
    private $settingRepository;
    private $transaction;
    private $audit;

    public function __construct()
    {
        $this->settingRepository = new SettingRepository();
        $this->transaction = new Transaction();
        $this->audit = new Audit();
    }

    public function getAllSettings(int $tenantId): array
    {
        $settings = $this->settingRepository->findAll($tenantId);
        
        // Group by prefix
        $grouped = [];
        foreach ($settings as $setting) {
            $key = $setting->setting_key;
            $prefix = explode('.', $key)[0];
            $grouped[$prefix][] = $setting->toArray();
        }
        
        return $grouped;
    }

    public function getSetting(int $tenantId, string $key): ?array
    {
        $setting = $this->settingRepository->findByKey($tenantId, $key);
        return $setting ? $setting->toArray() : null;
    }

    public function getSettingGroup(int $tenantId, string $prefix): array
    {
        $settings = $this->settingRepository->getGroup($tenantId, $prefix);
        return array_map(function($s) { return $s->toArray(); }, $settings);
    }

    public function createSetting(int $tenantId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            // Check if setting key already exists
            $existing = $this->settingRepository->findByKey($tenantId, $data['setting_key']);
            if ($existing) {
                $this->transaction->rollback();
                return false;
            }
            
            $data['tenant_id'] = $tenantId;
            $setting = new \Modules\Settings\Models\Setting($data);
            
            $result = $this->settingRepository->create($setting);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'SETTINGS',
                    'action' => 'CREATE_SETTING',
                    'record_id' => $this->transaction->getLastInsertId(),
                    'table_name' => 'settings',
                    'new_values' => json_encode($data)
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function updateSetting(int $tenantId, int $settingId, array $data): bool
    {
        $this->transaction->begin();
        
        try {
            $oldSetting = $this->settingRepository->findByKey($tenantId, $data['setting_key']);
            
            $data['tenant_id'] = $tenantId;
            $data['setting_id'] = $settingId;
            $setting = new \Modules\Settings\Models\Setting($data);
            
            $result = $this->settingRepository->update($setting);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'SETTINGS',
                    'action' => 'UPDATE_SETTING',
                    'record_id' => $settingId,
                    'table_name' => 'settings',
                    'old_values' => json_encode($oldSetting->toArray()),
                    'new_values' => json_encode($data)
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function upsertSetting(int $tenantId, string $key, $value, string $type = 'STRING', ?string $description = null): bool
    {
        $this->transaction->begin();
        
        try {
            $result = $this->settingRepository->upsert($tenantId, $key, $value, $type, $description);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'SETTINGS',
                    'action' => 'UPSERT_SETTING',
                    'table_name' => 'settings',
                    'new_values' => json_encode(['key' => $key, 'value' => $value])
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function deleteSetting(int $tenantId, int $settingId): bool
    {
        $this->transaction->begin();
        
        try {
            $setting = $this->settingRepository->findByKey($tenantId, ''); // Get setting by ID would need repository update
            
            $result = $this->settingRepository->delete($tenantId, $settingId);
            
            if ($result) {
                $this->audit->log([
                    'tenant_id' => $tenantId,
                    'module' => 'SETTINGS',
                    'action' => 'DELETE_SETTING',
                    'record_id' => $settingId,
                    'table_name' => 'settings',
                    'old_values' => json_encode($setting->toArray())
                ]);
                
                $this->transaction->commit();
                return true;
            }
            
            $this->transaction->rollback();
            return false;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    public function initializeDefaultSettings(int $tenantId): bool
    {
        $defaults = [
            // Restaurant settings
            'restaurant.name' => ['My Restaurant', 'STRING', 'Restaurant name'],
            'restaurant.phone' => ['', 'STRING', 'Restaurant phone number'],
            'restaurant.email' => ['', 'STRING', 'Restaurant email'],
            'restaurant.address' => ['', 'STRING', 'Restaurant address'],
            
            // Order settings
            'order.tax_rate' => [10, 'NUMBER', 'Default tax rate (%)'],
            'order.service_charge' => [5, 'NUMBER', 'Default service charge (%)'],
            'order.auto_print' => [true, 'BOOLEAN', 'Auto print receipt'],
            
            // Kitchen settings
            'kitchen.auto_send' => [true, 'BOOLEAN', 'Auto send orders to kitchen'],
            'kitchen.preparation_time' => [15, 'NUMBER', 'Default preparation time (minutes)'],
            
            // Reservation settings
            'reservation.max_party_size' => [20, 'NUMBER', 'Maximum party size'],
            'reservation.advance_booking_days' => [30, 'NUMBER', 'Maximum advance booking days'],
            
            // Currency settings
            'currency.code' => ['IDR', 'STRING', 'Currency code'],
            'currency.symbol' => ['Rp', 'STRING', 'Currency symbol'],
            'currency.decimals' => [0, 'NUMBER', 'Decimal places'],
        ];

        foreach ($defaults as $key => $config) {
            $this->upsertSetting($tenantId, $key, $config[0], $config[1], $config[2]);
        }

        return true;
    }
}
