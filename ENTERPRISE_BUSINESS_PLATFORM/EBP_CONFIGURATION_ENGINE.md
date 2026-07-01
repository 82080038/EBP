# Enterprise Business Platform (EBP)

# Configuration Engine Specification


**Document ID:** EBP-CONFIGURATION-ENGINE-001

**Version:** 1.0

**Category:** Enterprise Engine Specification

**Status:** Official Specification



---

# 1. Introduction


Dokumen ini mendefinisikan Configuration Engine untuk Enterprise Business Platform (EBP).

Configuration Engine memungkinkan:


* Tenant customization tanpa coding
* Feature flag management
* Dynamic setting
* Business rule configuration
* UI customization

Tujuan:


```

Satu Platform

+

Banyak Variasi Bisnis

```



---

# 2. Problem Statement


Masalah yang dihadapi:


Restaurant A berbeda dengan Restaurant B.


### Restaurant A


* Menggunakan meja
* Ada reservasi
* Ada kitchen display
* Ada delivery
* Multi-branch


### Restaurant B


* Hanya take away
* Tidak memakai meja
* Tidak memakai kitchen display
* Single outlet


### Pertanyaan


Apakah kita membuat dua aplikasi?


### Jawaban


Tidak.


Solusi:


```

Restaurant ERP

+

Configuration Engine

```



---

# 3. Configuration Philosophy


EBP Configuration Engine menggunakan prinsip:


```

CODE ONCE

CONFIGURE EVERYWHERE

```


Artinya:


* Kode ditulis sekali
* Perilaku dikonfigurasi per tenant
* Tanpa perlu coding ulang



---

# 4. Configuration Types


## 1. Module Configuration


Mengaktifkan/menonaktifkan modul.


Contoh:


```
restaurant.table_management = true

restaurant.delivery = false

restaurant.kitchen_display = true

restaurant.reservation = false

```


## 2. Feature Configuration


Mengaktifkan/menonaktifkan fitur.


Contoh:


```
restaurant.multi_branch = true

restaurant.inventory_management = true

restaurant.accounting_integration = false

restaurant.ai_forecast = false

```


## 3. Business Rule Configuration


Mengatur aturan bisnis.


Contoh:


```
restaurant.auto_create_journal = true

restaurant.auto_deduct_stock = true

restaurant.require_approval_above = 1000000

restaurant.default_tax_rate = 10

```


## 4. UI Configuration


Mengatur tampilan.


Contoh:


```
restaurant.show_table_map = true

restaurant.show_kitchen_display = true

restaurant.theme_color = #FF5733

restaurant.logo_path = /uploads/logo.png

```


## 5. Integration Configuration


Mengatur integrasi.


Contoh:


```
restaurant.payment_gateway = midtrans

restaurant.sms_provider = twilio

restaurant.email_provider = sendgrid

restaurant.printer_type = epson

```



---

# 5. Database Schema


## tenant_settings


```sql
CREATE TABLE tenant_settings (
    setting_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    module VARCHAR(50) NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type ENUM('boolean', 'string', 'integer', 'float', 'json', 'array') NOT NULL,
    description TEXT,
    is_encrypted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_by BIGINT,
    
    UNIQUE KEY uk_tenant_module_key (tenant_id, module, setting_key),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_module (module),
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## feature_flags


```sql
CREATE TABLE feature_flags (
    flag_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    flag_name VARCHAR(100) NOT NULL UNIQUE,
    flag_description TEXT,
    is_global BOOLEAN DEFAULT TRUE,
    enabled_for_tenant_ids JSON,
    enabled_for_plans JSON,
    enabled_since TIMESTAMP NULL,
    enabled_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_flag_name (flag_name),
    INDEX idx_global (is_global)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## tenant_feature_flags


```sql
CREATE TABLE tenant_feature_flags (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    flag_name VARCHAR(100) NOT NULL,
    is_enabled BOOLEAN DEFAULT FALSE,
    enabled_at TIMESTAMP NULL,
    enabled_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_tenant_flag (tenant_id, flag_name),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_flag_name (flag_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```



---

# 6. Configuration Hierarchy


## Priority Order


```

1. Tenant Setting (Highest Priority)

2. Plan Setting

3. Global Default (Lowest Priority)

```


## Example


Global Default:


```
restaurant.kitchen_display = false

```


Plan Setting (Professional):


```
restaurant.kitchen_display = true

```


Tenant Setting:


```
restaurant.kitchen_display = false

```


Result:


```

Tenant Setting wins: kitchen_display = false

```



---

# 7. Configuration API


## Get Configuration


```php
class ConfigurationService
{
    public function get($tenantId, $module, $key, $default = null)
    {
        // Check tenant setting
        $tenantSetting = $this->getTenantSetting($tenantId, $module, $key);
        
        if ($tenantSetting !== null) {
            return $this->parseValue($tenantSetting);
        }
        
        // Check plan setting
        $planSetting = $this->getPlanSetting($tenantId, $module, $key);
        
        if ($planSetting !== null) {
            return $this->parseValue($planSetting);
        }
        
        // Return global default
        return $default;
    }
    
    public function getTenantSetting($tenantId, $module, $key)
    {
        return $this->db->query(
            "SELECT setting_value, setting_type 
             FROM tenant_settings 
             WHERE tenant_id = ? AND module = ? AND setting_key = ?",
            [$tenantId, $module, $key]
        )->fetch();
    }
}
```


## Set Configuration


```php
public function set($tenantId, $module, $key, $value, $type = 'string')
{
    $this->db->query(
        "INSERT INTO tenant_settings 
         (tenant_id, module, setting_key, setting_value, setting_type, updated_by)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE 
         setting_value = VALUES(setting_value),
         setting_type = VALUES(setting_type),
         updated_at = CURRENT_TIMESTAMP,
         updated_by = VALUES(updated_by)",
        [$tenantId, $module, $key, $this->serializeValue($value), $type, $this->userId]
    );
}
```


## Check Feature Flag


```php
public function isFeatureEnabled($tenantId, $featureName)
{
    // Check tenant-specific flag
    $tenantFlag = $this->db->query(
        "SELECT is_enabled FROM tenant_feature_flags 
         WHERE tenant_id = ? AND flag_name = ?",
        [$tenantId, $featureName]
    )->fetch();
    
    if ($tenantFlag !== null) {
        return (bool) $tenantFlag['is_enabled'];
    }
    
    // Check global flag
    $globalFlag = $this->db->query(
        "SELECT enabled_for_tenant_ids, enabled_for_plans 
         FROM feature_flags 
         WHERE flag_name = ? AND is_global = TRUE",
        [$featureName]
    )->fetch();
    
    if ($globalFlag) {
        $enabledTenants = json_decode($globalFlag['enabled_for_tenant_ids'], true);
        $enabledPlans = json_decode($globalFlag['enabled_for_plans'], true);
        
        if (in_array($tenantId, $enabledTenants)) {
            return true;
        }
        
        $tenantPlan = $this->getTenantPlan($tenantId);
        if (in_array($tenantPlan, $enabledPlans)) {
            return true;
        }
    }
    
    return false;
}
```



---

# 8. Configuration Categories


## Restaurant Configuration


```php
// Table Management
restaurant.table_management = true
restaurant.table_capacity = 50
restaurant.table_zones = ['main', 'outdoor', 'vip']

// Kitchen Display
restaurant.kitchen_display = true
restaurant.kitchen_display_layout = 'grid'
restaurant.kitchen_auto_refresh = 30

// Delivery
restaurant.delivery = false
restaurant.delivery_radius = 5
restaurant.delivery_fee = 10000

// Reservation
restaurant.reservation = true
restaurant.reservation_advance_days = 7
restaurant.reservation_deposit_required = true
```


## Hotel Configuration


```php
// Room Management
hotel.room_management = true
hotel.housekeeping_auto_assign = true
hotel.checkout_time = '12:00'
hotel.checkin_time = '14:00'

// Reservation
hotel.reservation = true
hotel.overbooking_allowed = false
hotel.deposit_policy = 'first_night'

// Pricing
hotel.dynamic_pricing = false
hotel.weekend_surcharge = 20
hotel.seasonal_pricing = false
```


## Parking Configuration


```php
// Slot Management
parking.slot_management = true
parking.valet_service = false
parking.monthly_subscription = true

// Pricing
parking.hourly_rate = 5000
parking.daily_max = 50000
parking.monthly_rate = 500000

// Access
parking.rfid_access = true
parking.plate_recognition = false
```



---

# 9. Configuration Validation


## Type Validation


```php
public function validateValue($value, $type)
{
    switch ($type) {
        case 'boolean':
            return is_bool($value) || in_array($value, ['true', 'false', '0', '1']);
        
        case 'integer':
            return is_int($value) || ctype_digit($value);
        
        case 'float':
            return is_numeric($value);
        
        case 'string':
            return is_string($value);
        
        case 'json':
            json_decode($value);
            return json_last_error() === JSON_ERROR_NONE;
        
        case 'array':
            return is_array($value);
        
        default:
            return true;
    }
}
```


## Range Validation


```php
public function validateRange($value, $min = null, $max = null)
{
    if ($min !== null && $value < $min) {
        return false;
    }
    
    if ($max !== null && $value > $max) {
        return false;
    }
    
    return true;
}
```


## Enum Validation


```php
public function validateEnum($value, $allowedValues)
{
    return in_array($value, $allowedValues);
}
```



---

# 10. Configuration Caching


## Cache Strategy


Configuration di-cache untuk:


* Mengurangi database query
* Meningkatkan performance
* Mengurangi latency


## Cache Implementation


```php
class ConfigurationCache
{
    private $cache;
    private $ttl = 3600; // 1 hour
    
    public function get($key)
    {
        return $this->cache->get($key);
    }
    
    public function set($key, $value)
    {
        return $this->cache->set($key, $value, $this->ttl);
    }
    
    public function invalidate($tenantId)
    {
        $pattern = "config:tenant:{$tenantId}:*";
        $this->cache->deleteByPattern($pattern);
    }
}
```


## Cache Invalidation


Cache di-invalidate ketika:


* Configuration diubah
* Tenant plan diubah
* Feature flag diubah
* Manual flush



---

# 11. Configuration History


## Audit Trail


Setiap perubahan configuration dicatat:


```sql
CREATE TABLE configuration_history (
    history_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    module VARCHAR(50) NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    changed_by BIGINT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    change_reason TEXT,
    
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## Rollback


```php
public function rollback($historyId)
{
    $history = $this->db->query(
        "SELECT * FROM configuration_history WHERE history_id = ?",
        [$historyId]
    )->fetch();
    
    if ($history) {
        $this->set(
            $history['tenant_id'],
            $history['module'],
            $history['setting_key'],
            $history['old_value']
        );
    }
}
```



---

# 12. Configuration Export/Import


## Export


```php
public function exportTenantConfiguration($tenantId)
{
    $settings = $this->db->query(
        "SELECT module, setting_key, setting_value, setting_type 
         FROM tenant_settings 
         WHERE tenant_id = ?",
        [$tenantId]
    )->fetchAll();
    
    return [
        'tenant_id' => $tenantId,
        'exported_at' => date('Y-m-d H:i:s'),
        'settings' => $settings
    ];
}
```


## Import


```php
public function importTenantConfiguration($tenantId, $config)
{
    foreach ($config['settings'] as $setting) {
        $this->set(
            $tenantId,
            $setting['module'],
            $setting['setting_key'],
            $setting['setting_value'],
            $setting['setting_type']
        );
    }
}
```



---

# 13. Configuration UI


## Admin Interface


Configuration dapat diubah melalui:


* Admin panel
* API
* CLI


## UI Structure


```

Configuration Panel

├── General Settings
│   ├── Company Info
│   ├── Business Hours
│   └── Contact Info
│
├── Module Settings
│   ├── Table Management
│   ├── Kitchen Display
│   ├── Delivery
│   └── Reservation
│
├── Feature Flags
│   ├── AI Forecast
│   ├── Multi Branch
│   └── Advanced Reporting
│
├── Integration Settings
│   ├── Payment Gateway
│   ├── SMS Provider
│   └── Email Provider
│
└── UI Settings
    ├── Theme
    ├── Logo
    └── Layout

```



---

# 14. Configuration Security


## Access Control


Hanya user dengan permission:


```

CONFIGURATION_MANAGE

```


boleleh mengubah configuration.


## Sensitive Configuration


Configuration sensitive di-encrypt:


```
restaurant.payment_gateway_api_key

restaurant.sms_provider_api_key

restaurant.email_provider_api_key

```


## Encryption


```php
public function encryptValue($value)
{
    return openssl_encrypt(
        $value,
        'AES-256-CBC',
        $this->encryptionKey,
        0,
        $this->iv
    );
}

public function decryptValue($encrypted)
{
    return openssl_decrypt(
        $encrypted,
        'AES-256-CBC',
        $this->encryptionKey,
        0,
        $this->iv
    );
}
```



---

# 15. Configuration Performance


## Optimization


* Caching (Redis)
* Lazy loading
* Batch loading
* Prefetching


## Metrics


Monitor:


* Configuration load time
* Cache hit rate
* Database query count



---

# 16. Configuration Testing


## Unit Tests


```php
public function testGetConfiguration()
{
    $this->configService->set(1, 'restaurant', 'table_management', true, 'boolean');
    
    $result = $this->configService->get(1, 'restaurant', 'table_management');
    
    $this->assertTrue($result);
}

public function testConfigurationHierarchy()
{
    // Set global default
    $this->configService->setGlobalDefault('restaurant', 'kitchen_display', false);
    
    // Set plan override
    $this->configService->setPlanSetting('professional', 'restaurant', 'kitchen_display', true);
    
    // Set tenant override
    $this->configService->set(1, 'restaurant', 'kitchen_display', false);
    
    $result = $this->configService->get(1, 'restaurant', 'kitchen_display');
    
    $this->assertFalse($result); // Tenant setting wins
}
```



---

# 17. Best Practices


## Naming Convention


Format:


```

module.setting_name

```


Example:


```
restaurant.table_management
restaurant.kitchen_display
hotel.checkout_time
parking.hourly_rate

```


## Default Values


Selalu sediakan default value:


```php
$enabled = $this->configService->get($tenantId, 'restaurant', 'kitchen_display', false);
```


## Documentation


Dokumentasikan setiap configuration:


```php
/**
 * Enable/disable table management
 * 
 * @type boolean
 * @default false
 * @module restaurant
 */
```



---

# 18. Conclusion


EBP Configuration Engine memungkinkan:


```

Satu Platform

+

Banyak Variasi Bisnis

+

Tanpa Coding

```


Manfaat:


* Fleksibilitas tanpa coding
* Time-to-market lebih cepat
* Maintenance lebih mudah
* Scalable untuk banyak tenant
* Professional SaaS platform


EBP Configuration Engine adalah kunci untuk menjadi platform software yang true enterprise.



---

# END OF DOCUMENT


Document ID:

EBP-CONFIGURATION-ENGINE-001


Version:

1.0
