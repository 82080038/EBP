-- Migration Phase 28: Multi-Language Support (Indonesian/English)
-- Provides internationalization (i18n) architecture with Indonesian as primary and English as secondary

-- Languages Table
CREATE TABLE IF NOT EXISTS languages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    language_code VARCHAR(10) NOT NULL UNIQUE,
    language_name VARCHAR(100) NOT NULL,
    native_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default languages
INSERT INTO languages (language_code, language_name, native_name, is_active, is_default, sort_order) VALUES
('id', 'Indonesian', 'Bahasa Indonesia', TRUE, TRUE, 1),
('en', 'English', 'English', TRUE, FALSE, 2);

-- Translations Table
CREATE TABLE IF NOT EXISTS translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    language_code VARCHAR(10) NOT NULL,
    translation_key VARCHAR(255) NOT NULL,
    translation_value TEXT NOT NULL,
    context VARCHAR(100) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_language_code (language_code),
    INDEX idx_translation_key (translation_key),
    INDEX idx_context (context),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_translation (language_code, translation_key, context),
    
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Language Preferences Table
CREATE TABLE IF NOT EXISTS user_language_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    is_primary BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_language_code (language_code),
    UNIQUE KEY unique_user_language (user_id, language_code),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restaurant Language Settings Table
CREATE TABLE IF NOT EXISTS restaurant_language_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    is_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_language_code (language_code),
    UNIQUE KEY unique_restaurant_language (restaurant_id, language_code),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Translation Groups Table (for organizing translations)
CREATE TABLE IF NOT EXISTS translation_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(100) NOT NULL UNIQUE,
    group_description TEXT NULL,
    parent_group_id INT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_parent_group_id (parent_group_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order),
    
    FOREIGN KEY (parent_group_id) REFERENCES translation_groups(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Translation Cache Table (for performance)
CREATE TABLE IF NOT EXISTS translation_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    language_code VARCHAR(10) NOT NULL,
    cache_key VARCHAR(255) NOT NULL,
    cache_value TEXT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_language_code (language_code),
    INDEX idx_cache_key (cache_key),
    INDEX idx_expires_at (expires_at),
    
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default translation groups
INSERT INTO translation_groups (group_name, group_description, sort_order, is_active) VALUES
('common', 'Common translations used across the application', 1, TRUE),
('auth', 'Authentication and authorization related translations', 2, TRUE),
('dashboard', 'Dashboard and analytics translations', 3, TRUE),
('orders', 'Order management translations', 4, TRUE),
('menu', 'Menu and product translations', 5, TRUE),
('inventory', 'Inventory management translations', 6, TRUE),
('staff', 'Staff and HR translations', 7, TRUE),
('customers', 'Customer management translations', 8, TRUE),
('reports', 'Reports and analytics translations', 9, TRUE),
('settings', 'Settings and configuration translations', 10, TRUE),
('errors', 'Error messages and alerts', 11, TRUE),
('success', 'Success messages and confirmations', 12, TRUE);

-- Insert default Indonesian translations for common keys
INSERT INTO translations (language_code, translation_key, translation_value, context, is_active) VALUES
-- Common
('id', 'common.welcome', 'Selamat Datang', 'common', TRUE),
('id', 'common.dashboard', 'Dasbor', 'common', TRUE),
('id', 'common.settings', 'Pengaturan', 'common', TRUE),
('id', 'common.logout', 'Keluar', 'common', TRUE),
('id', 'common.save', 'Simpan', 'common', TRUE),
('id', 'common.cancel', 'Batal', 'common', TRUE),
('id', 'common.delete', 'Hapus', 'common', TRUE),
('id', 'common.edit', 'Edit', 'common', TRUE),
('id', 'common.add', 'Tambah', 'common', TRUE),
('id', 'common.search', 'Cari', 'common', TRUE),
('id', 'common.filter', 'Filter', 'common', TRUE),
('id', 'common.export', 'Ekspor', 'common', TRUE),
('id', 'common.import', 'Impor', 'common', TRUE),
('id', 'common.print', 'Cetak', 'common', TRUE),
('id', 'common.close', 'Tutup', 'common', TRUE),
('id', 'common.back', 'Kembali', 'common', TRUE),
('id', 'common.next', 'Lanjut', 'common', TRUE),
('id', 'common.previous', 'Sebelumnya', 'common', TRUE),
('id', 'common.submit', 'Kirim', 'common', TRUE),
('id', 'common.confirm', 'Konfirmasi', 'common', TRUE),
('id', 'common.yes', 'Ya', 'common', TRUE),
('id', 'common.no', 'Tidak', 'common', TRUE),
('id', 'common.loading', 'Memuat...', 'common', TRUE),
('id', 'common.processing', 'Memproses...', 'common', TRUE),
('id', 'common.success', 'Berhasil', 'common', TRUE),
('id', 'common.error', 'Error', 'common', TRUE),
('id', 'common.warning', 'Peringatan', 'common', TRUE),
('id', 'common.info', 'Informasi', 'common', TRUE);

-- Insert default English translations for common keys
INSERT INTO translations (language_code, translation_key, translation_value, context, is_active) VALUES
-- Common
('en', 'common.welcome', 'Welcome', 'common', TRUE),
('en', 'common.dashboard', 'Dashboard', 'common', TRUE),
('en', 'common.settings', 'Settings', 'common', TRUE),
('en', 'common.logout', 'Logout', 'common', TRUE),
('en', 'common.save', 'Save', 'common', TRUE),
('en', 'common.cancel', 'Cancel', 'common', TRUE),
('en', 'common.delete', 'Delete', 'common', TRUE),
('en', 'common.edit', 'Edit', 'common', TRUE),
('en', 'common.add', 'Add', 'common', TRUE),
('en', 'common.search', 'Search', 'common', TRUE),
('en', 'common.filter', 'Filter', 'common', TRUE),
('en', 'common.export', 'Export', 'common', TRUE),
('en', 'common.import', 'Import', 'common', TRUE),
('en', 'common.print', 'Print', 'common', TRUE),
('en', 'common.close', 'Close', 'common', TRUE),
('en', 'common.back', 'Back', 'common', TRUE),
('en', 'common.next', 'Next', 'common', TRUE),
('en', 'common.previous', 'Previous', 'common', TRUE),
('en', 'common.submit', 'Submit', 'common', TRUE),
('en', 'common.confirm', 'Confirm', 'common', TRUE),
('en', 'common.yes', 'Yes', 'common', TRUE),
('en', 'common.no', 'No', 'common', TRUE),
('en', 'common.loading', 'Loading...', 'common', TRUE),
('en', 'common.processing', 'Processing...', 'common', TRUE),
('en', 'common.success', 'Success', 'common', TRUE),
('en', 'common.error', 'Error', 'common', TRUE),
('en', 'common.warning', 'Warning', 'common', TRUE),
('en', 'common.info', 'Info', 'common', TRUE);

-- Insert default Indonesian translations for auth
INSERT INTO translations (language_code, translation_key, translation_value, context, is_active) VALUES
('id', 'auth.login', 'Masuk', 'auth', TRUE),
('id', 'auth.logout', 'Keluar', 'auth', TRUE),
('id', 'auth.username', 'Nama Pengguna', 'auth', TRUE),
('id', 'auth.password', 'Kata Sandi', 'auth', TRUE),
('id', 'auth.email', 'Email', 'auth', TRUE),
('id', 'auth.remember_me', 'Ingat Saya', 'auth', TRUE),
('id', 'auth.forgot_password', 'Lupa Kata Sandi?', 'auth', TRUE),
('id', 'auth.reset_password', 'Reset Kata Sandi', 'auth', TRUE),
('id', 'auth.invalid_credentials', 'Kredensial tidak valid', 'auth', TRUE),
('id', 'auth.account_locked', 'Akun terkunci', 'auth', TRUE);

-- Insert default English translations for auth
INSERT INTO translations (language_code, translation_key, translation_value, context, is_active) VALUES
('en', 'auth.login', 'Login', 'auth', TRUE),
('en', 'auth.logout', 'Logout', 'auth', TRUE),
('en', 'auth.username', 'Username', 'auth', TRUE),
('en', 'auth.password', 'Password', 'auth', TRUE),
('en', 'auth.email', 'Email', 'auth', TRUE),
('en', 'auth.remember_me', 'Remember Me', 'auth', TRUE),
('en', 'auth.forgot_password', 'Forgot Password?', 'auth', TRUE),
('en', 'auth.reset_password', 'Reset Password', 'auth', TRUE),
('en', 'auth.invalid_credentials', 'Invalid credentials', 'auth', TRUE),
('en', 'auth.account_locked', 'Account locked', 'auth', TRUE);

-- Insert default language settings for new restaurants
DELIMITER //
CREATE TRIGGER insert_default_restaurant_languages
AFTER INSERT ON restaurants
FOR EACH NEW
BEGIN
    -- Set Indonesian as primary
    INSERT INTO restaurant_language_settings (restaurant_id, language_code, is_primary, is_enabled)
    VALUES (NEW.id, 'id', TRUE, TRUE);
    
    -- Enable English
    INSERT INTO restaurant_language_settings (restaurant_id, language_code, is_primary, is_enabled)
    VALUES (NEW.id, 'en', FALSE, TRUE);
END//
DELIMITER ;

-- Add language preference column to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS preferred_language VARCHAR(10) DEFAULT 'id' AFTER email,
ADD COLUMN IF NOT EXISTS preferred_language_set_at DATETIME NULL AFTER preferred_language;

-- Update existing users to have Indonesian as default
UPDATE users 
SET preferred_language = 'id', preferred_language_set_at = NOW()
WHERE preferred_language IS NULL;

-- Add foreign key for preferred language
ALTER TABLE users 
ADD CONSTRAINT fk_users_preferred_language 
FOREIGN KEY (preferred_language) REFERENCES languages(language_code) 
ON DELETE SET NULL;
