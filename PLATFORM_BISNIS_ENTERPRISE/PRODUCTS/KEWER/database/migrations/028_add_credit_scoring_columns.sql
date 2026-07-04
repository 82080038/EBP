-- Migration 028: Add Credit Scoring Columns
-- Date: 8 Mei 2026
-- Description: Add credit_score and risk_level columns to nasabah table
--              Add auto_approved and approval_reason columns to pinjaman table

-- Add credit scoring columns to nasabah table
ALTER TABLE nasabah 
ADD COLUMN credit_score DECIMAL(5,2) DEFAULT NULL COMMENT 'Credit score (0-100)',
ADD COLUMN risk_level VARCHAR(50) DEFAULT NULL COMMENT 'Risk level: Sangat Rendah, Rendah, Sedang, Tinggi, Sangat Tinggi',
ADD COLUMN score_updated_at DATETIME DEFAULT NULL COMMENT 'Last credit score update';

-- Add auto-approval columns to pinjaman table
ALTER TABLE pinjaman
ADD COLUMN auto_approved TINYINT(1) DEFAULT 0 COMMENT 'Auto-approved flag',
ADD COLUMN approval_reason TEXT DEFAULT NULL COMMENT 'Reason for approval/rejection',
ADD COLUMN credit_score_at_approval DECIMAL(5,2) DEFAULT NULL COMMENT 'Credit score at time of approval';

-- Create index for credit score
CREATE INDEX idx_nasabah_credit_score ON nasabah(credit_score);
CREATE INDEX idx_nasabah_risk_level ON nasabah(risk_level);

-- Create credit_scoring_logs table if not exists
CREATE TABLE IF NOT EXISTS credit_scoring_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nasabah_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    risk_level VARCHAR(50) NOT NULL,
    breakdown JSON DEFAULT NULL COMMENT 'Score breakdown details',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nasabah_id) REFERENCES nasabah(id) ON DELETE CASCADE,
    INDEX idx_nasabah_id (nasabah_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Credit scoring audit trail';
