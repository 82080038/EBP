-- Migration 029: Add GPS Tracking Tables
-- Date: 8 Mei 2026
-- Description: Add tables for GPS tracking and visit logging

-- Add GPS columns to pembayaran table
ALTER TABLE pembayaran
ADD COLUMN latitude DECIMAL(10, 8) DEFAULT NULL COMMENT 'GPS latitude',
ADD COLUMN longitude DECIMAL(11, 8) DEFAULT NULL COMMENT 'GPS longitude',
ADD COLUMN gps_accuracy DECIMAL(8, 2) DEFAULT NULL COMMENT 'GPS accuracy in meters',
ADD COLUMN captured_at DATETIME DEFAULT NULL COMMENT 'GPS capture timestamp',
ADD INDEX idx_gps (latitude, longitude);

-- Add GPS columns to cabang table for geofencing
ALTER TABLE cabang
ADD COLUMN latitude DECIMAL(10, 8) DEFAULT NULL COMMENT 'GPS latitude for geofencing',
ADD COLUMN longitude DECIMAL(11, 8) DEFAULT NULL COMMENT 'GPS longitude for geofencing',
ADD COLUMN geofence_radius INT DEFAULT 5000 COMMENT 'Geofence radius in meters (default 5km)',
ADD INDEX idx_cabang_gps (latitude, longitude);

-- Create visits table
CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    petugas_id INT NOT NULL,
    nasabah_id INT NOT NULL,
    cabang_id INT NOT NULL,
    visit_type VARCHAR(50) DEFAULT 'pembayaran' COMMENT 'Visit type: pembayaran, follow_up, survey',
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    gps_accuracy DECIMAL(8, 2) DEFAULT NULL,
    geofence_valid TINYINT(1) DEFAULT 1 COMMENT 'Whether location was within geofence',
    distance_from_cabang DECIMAL(10, 2) DEFAULT NULL COMMENT 'Distance from cabang in meters',
    visit_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    photo_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (nasabah_id) REFERENCES nasabah(id) ON DELETE CASCADE,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
    INDEX idx_petugas_id (petugas_id),
    INDEX idx_nasabah_id (nasabah_id),
    INDEX idx_cabang_id (cabang_id),
    INDEX idx_visit_date (visit_date),
    INDEX idx_gps (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Field officer visit logs with GPS tracking';

-- Create mobile_devices table
CREATE TABLE IF NOT EXISTS mobile_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    device_name VARCHAR(255) DEFAULT NULL,
    device_type VARCHAR(50) DEFAULT 'android' COMMENT 'android, ios, web',
    app_version VARCHAR(50) DEFAULT NULL,
    os_version VARCHAR(50) DEFAULT NULL,
    push_token VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY idx_device_id (device_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registered mobile devices';
