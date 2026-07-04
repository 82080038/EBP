-- Migration Phase 25: True Offline Capability
- Provides offline-first architecture with local data storage, conflict resolution, and automatic sync

-- Offline Transactions Table
CREATE TABLE IF NOT EXISTS offline_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    
    -- Transaction Details
    transaction_type ENUM('order', 'payment', 'inventory', 'staff', 'other') NOT NULL,
    transaction_data JSON NOT NULL,
    
    -- Sync Status
    sync_status ENUM('pending', 'syncing', 'synced', 'conflict', 'failed') DEFAULT 'pending',
    sync_attempts INT DEFAULT 0,
    last_sync_attempt_at DATETIME NULL,
    
    -- Conflict Resolution
    conflict_data JSON NULL,
    conflict_resolved BOOLEAN DEFAULT FALSE,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    resolution_notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    synced_at DATETIME NULL,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_device_id (device_id),
    INDEX idx_sync_status (sync_status),
    INDEX idx_created_at (created_at),
    INDEX idx_synced_at (synced_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offline Data Snapshots Table
CREATE TABLE IF NOT EXISTS offline_data_snapshots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Snapshot Details
    data_type ENUM('menu', 'inventory', 'customers', 'staff', 'settings', 'other') NOT NULL,
    snapshot_data JSON NOT NULL,
    snapshot_version INT DEFAULT 1,
    
    -- Sync Information
    last_synced_at DATETIME NULL,
    sync_status ENUM('synced', 'pending', 'failed') DEFAULT 'synced',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_device_id (device_id),
    INDEX idx_user_id (user_id),
    INDEX idx_data_type (data_type),
    INDEX idx_last_synced_at (last_synced_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offline Conflict Resolution Table
CREATE TABLE IF NOT EXISTS offline_conflicts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    offline_transaction_id BIGINT UNSIGNED NOT NULL,
    
    -- Conflict Details
    conflict_type ENUM('data_mismatch', 'duplicate', 'version_conflict', 'validation_error', 'other') NOT NULL,
    conflict_description TEXT NOT NULL,
    local_data JSON NOT NULL,
    remote_data JSON NOT NULL,
    
    -- Resolution
    resolution_action ENUM('keep_local', 'keep_remote', 'merge', 'manual') NULL,
    resolved_data JSON NULL,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    resolution_notes TEXT NULL,
    
    -- Status
    is_resolved BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_offline_transaction_id (offline_transaction_id),
    INDEX idx_conflict_type (conflict_type),
    INDEX idx_is_resolved (is_resolved),
    INDEX idx_created_at (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (offline_transaction_id) REFERENCES offline_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Device Registration Table
CREATE TABLE IF NOT EXISTS device_registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Device Information
    device_id VARCHAR(255) NOT NULL,
    device_name VARCHAR(255) NULL,
    device_type ENUM('pos', 'tablet', 'mobile', 'kiosk', 'other') NOT NULL,
    device_os VARCHAR(100) NULL,
    device_os_version VARCHAR(100) NULL,
    app_version VARCHAR(50) NULL,
    
    -- Device Capabilities
    storage_capacity_mb INT NULL,
    available_storage_mb INT NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    last_seen_at DATETIME NULL,
    
    -- Timestamps
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_device_id (device_id),
    INDEX idx_is_active (is_active),
    INDEX idx_last_seen_at (last_seen_at),
    UNIQUE KEY unique_device (restaurant_id, device_id),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sync Queue Table
CREATE TABLE IF NOT EXISTS sync_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    
    -- Queue Item Details
    queue_type ENUM('upload', 'download', 'conflict_resolution') NOT NULL,
    priority ENUM('low', 'normal', 'high', 'critical') DEFAULT 'normal',
    payload JSON NOT NULL,
    
    -- Processing Status
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    processing_attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    
    -- Processing Details
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    error_message TEXT NULL,
    error_details JSON NULL,
    
    -- Dependencies
    depends_on_id BIGINT UNSIGNED NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_device_id (device_id),
    INDEX idx_queue_type (queue_type),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at),
    INDEX idx_depends_on_id (depends_on_id),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (depends_on_id) REFERENCES sync_queue(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offline Settings Table
CREATE TABLE IF NOT EXISTS offline_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Settings
    setting_key VARCHAR(100) NOT NULL,
    setting_value JSON NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_setting_key (setting_key),
    UNIQUE KEY unique_setting (restaurant_id, setting_key),
    
    -- Foreign Keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default offline settings for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_offline_settings
AFTER INSERT ON restaurants
FOR EACH ROW
BEGIN
    -- Auto-sync when online
    INSERT INTO offline_settings (restaurant_id, setting_key, setting_value)
    VALUES (
        NEW.id,
        'auto_sync_enabled',
        JSON_OBJECT('enabled', TRUE, 'sync_interval_minutes', 5)
    );
    
    -- Conflict resolution strategy
    INSERT INTO offline_settings (restaurant_id, setting_key, setting_value)
    VALUES (
        NEW.id,
        'conflict_resolution_strategy',
        JSON_OBJECT('strategy', 'manual', 'auto_merge_enabled', FALSE)
    );
    
    -- Data retention policy
    INSERT INTO offline_settings (restaurant_id, setting_key, setting_value)
    VALUES (
        NEW.id,
        'data_retention_days',
        JSON_OBJECT('offline_transactions', 30, 'snapshots', 7, 'conflicts', 90)
    );
    
    -- Storage limits
    INSERT INTO offline_settings (restaurant_id, setting_key, setting_value)
    VALUES (
        NEW.id,
        'storage_limits',
        JSON_OBJECT('max_offline_transactions', 1000, 'max_snapshot_size_mb', 50)
    );
END//
DELIMITER ;

-- Add offline permissions to roles table
ALTER TABLE roles 
ADD COLUMN IF NOT EXISTS can_work_offline BOOLEAN DEFAULT FALSE AFTER can_view_integrations,
ADD COLUMN IF NOT EXISTS can_manage_offline_data BOOLEAN DEFAULT FALSE AFTER can_work_offline;

-- Update admin role
UPDATE roles 
SET can_work_offline = TRUE,
    can_manage_offline_data = TRUE
WHERE role_name = 'admin';

-- Update manager role
UPDATE roles 
SET can_work_offline = TRUE,
    can_manage_offline_data = TRUE
WHERE role_name = 'manager';

-- Update staff role
UPDATE roles 
SET can_work_offline = TRUE
WHERE role_name = 'staff';
