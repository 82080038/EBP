-- Migration 031: Add Webhook Tables
-- Date: 8 Mei 2026
-- Description: Add tables for third-party API integration and webhook system

-- Create external_api_logs table
CREATE TABLE IF NOT EXISTS external_api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_name VARCHAR(100) NOT NULL COMMENT 'Name of external API (e.g., SLIK OJK, Payment Gateway)',
    endpoint VARCHAR(255) NOT NULL COMMENT 'API endpoint called',
    method VARCHAR(10) NOT NULL COMMENT 'GET, POST, PUT, DELETE',
    request_body JSON DEFAULT NULL COMMENT 'Request payload',
    response_body JSON DEFAULT NULL COMMENT 'Response payload',
    status_code INT DEFAULT NULL COMMENT 'HTTP status code',
    status VARCHAR(20) NOT NULL COMMENT 'success, error, timeout',
    error_message TEXT DEFAULT NULL,
    duration_ms INT DEFAULT NULL COMMENT 'Request duration in milliseconds',
    user_id INT DEFAULT NULL COMMENT 'User who initiated the request',
    cabang_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE SET NULL,
    INDEX idx_api_name (api_name),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='External API call logs';

-- Create api_keys table
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'User who owns the API key',
    partner_name VARCHAR(255) DEFAULT NULL COMMENT 'Partner organization name',
    key_name VARCHAR(100) NOT NULL COMMENT 'Name/description of the API key',
    api_key VARCHAR(255) NOT NULL UNIQUE COMMENT 'The actual API key',
    scopes JSON DEFAULT NULL COMMENT 'Allowed scopes/permissions',
    is_active TINYINT(1) DEFAULT 1,
    last_used_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_api_key (api_key),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='API key management for partner integrations';

-- Create webhooks table
CREATE TABLE IF NOT EXISTS webhooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL COMMENT 'User who created the webhook',
    event_type VARCHAR(100) NOT NULL COMMENT 'Event type (e.g., pinjaman.approved, pembayaran.received)',
    target_url VARCHAR(255) NOT NULL COMMENT 'Webhook endpoint URL',
    secret_key VARCHAR(255) DEFAULT NULL COMMENT 'Secret for HMAC signature',
    headers JSON DEFAULT NULL COMMENT 'Additional headers to send',
    is_active TINYINT(1) DEFAULT 1,
    retry_count INT DEFAULT 3 COMMENT 'Number of retry attempts',
    retry_interval INT DEFAULT 300 COMMENT 'Retry interval in seconds',
    last_triggered_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Webhook configuration for event triggers';

-- Create webhook_logs table
CREATE TABLE IF NOT EXISTS webhook_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    webhook_id INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL COMMENT 'Event payload',
    response_code INT DEFAULT NULL,
    response_body TEXT DEFAULT NULL,
    status VARCHAR(20) NOT NULL COMMENT 'success, failed, retrying',
    attempt_number INT DEFAULT 1 COMMENT 'Current attempt number',
    error_message TEXT DEFAULT NULL,
    triggered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE,
    INDEX idx_webhook_id (webhook_id),
    INDEX idx_event_type (event_type),
    INDEX idx_status (status),
    INDEX idx_triggered_at (triggered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Webhook delivery logs';

-- Create webhook_deliveries table (for tracking specific event deliveries)
CREATE TABLE IF NOT EXISTS webhook_deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    webhook_id INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    resource_type VARCHAR(100) NOT NULL COMMENT 'Type of resource (pinjaman, pembayaran, etc.)',
    resource_id INT NOT NULL COMMENT 'ID of the resource',
    payload JSON NOT NULL,
    status VARCHAR(20) NOT NULL COMMENT 'pending, sent, failed',
    sent_at DATETIME DEFAULT NULL,
    retry_count INT DEFAULT 0,
    last_error TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE,
    INDEX idx_webhook_id (webhook_id),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Webhook delivery queue';
