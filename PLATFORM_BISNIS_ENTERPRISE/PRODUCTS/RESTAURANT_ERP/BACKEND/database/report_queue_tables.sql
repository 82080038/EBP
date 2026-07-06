-- Report Queue Tables for Asynchronous Report Generation

-- Report jobs table
CREATE TABLE IF NOT EXISTS report_jobs (
    report_job_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED NOT NULL,
    report_type VARCHAR(100) NOT NULL,
    report_name VARCHAR(255) NOT NULL,
    parameters TEXT,
    status ENUM('QUEUED', 'PROCESSING', 'COMPLETED', 'FAILED') DEFAULT 'QUEUED',
    file_path VARCHAR(500),
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    INDEX idx_tenant_user (tenant_id, user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Report queue table for job management
CREATE TABLE IF NOT EXISTS report_queue (
    queue_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_job_id BIGINT UNSIGNED NOT NULL,
    priority INT DEFAULT 0,
    queued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_priority (priority),
    INDEX idx_queued_at (queued_at),
    FOREIGN KEY (report_job_id) REFERENCES report_jobs(report_job_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
