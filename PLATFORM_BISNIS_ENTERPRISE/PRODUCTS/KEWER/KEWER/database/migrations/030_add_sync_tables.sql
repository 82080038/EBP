-- Migration 030: Add Sync Tables
-- Date: 8 Mei 2026
-- Description: Add tables for multi-branch synchronization

-- Create sync_logs table
CREATE TABLE IF NOT EXISTS sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    direction VARCHAR(20) NOT NULL COMMENT 'to_central, from_central',
    sync_type VARCHAR(20) NOT NULL COMMENT 'full, incremental',
    status VARCHAR(20) NOT NULL COMMENT 'started, completed, failed',
    table_name VARCHAR(100) DEFAULT NULL COMMENT 'Table being synced (if single table sync)',
    details JSON DEFAULT NULL COMMENT 'Sync details including counts, conflicts, etc.',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
    INDEX idx_cabang_id (cabang_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Data synchronization logs';

-- Create sync_conflicts table
CREATE TABLE IF NOT EXISTS sync_conflicts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    conflict_type VARCHAR(50) NOT NULL COMMENT 'version_mismatch, data_mismatch, delete_conflict',
    local_data JSON DEFAULT NULL COMMENT 'Local version of data',
    remote_data JSON DEFAULT NULL COMMENT 'Remote version of data',
    resolved TINYINT(1) DEFAULT 0 COMMENT 'Whether conflict is resolved',
    resolution VARCHAR(50) DEFAULT NULL COMMENT 'keep_local, keep_remote, merge',
    resolved_by INT DEFAULT NULL COMMENT 'User ID who resolved',
    resolved_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabang_id) REFERENCES cabang(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_cabang_id (cabang_id),
    INDEX idx_table_name (table_name),
    INDEX idx_resolved (resolved),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Sync conflict tracking';
