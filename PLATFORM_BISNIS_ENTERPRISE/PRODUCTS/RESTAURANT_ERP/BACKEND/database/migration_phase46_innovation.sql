-- Migration Phase 46: Innovation Management
-- Provides innovation tracking, idea management, and R&D project management

-- Innovation Ideas Table
CREATE TABLE IF NOT EXISTS innovation_ideas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Idea Details
    idea_title VARCHAR(255) NOT NULL,
    idea_description TEXT NOT NULL,
    idea_category ENUM('product', 'process', 'technology', 'service', 'marketing', 'sustainability', 'other') NOT NULL,
    
    -- Impact
    potential_impact ENUM('low', 'medium', 'high', 'transformative') NOT NULL,
    estimated_cost DECIMAL(15,2) NULL,
    estimated_roi DECIMAL(5,2) NULL,
    
    -- Priority
    priority_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    
    -- Status
    idea_status ENUM('submitted', 'under_review', 'approved', 'rejected', 'in_development', 'implemented', 'on_hold', 'cancelled') DEFAULT 'submitted',
    
    -- Staff
    submitted_by BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    
    -- Review
    review_date DATE NULL,
    review_notes TEXT NULL,
    
    -- Implementation
    implementation_date DATE NULL,
    implementation_notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_idea_category (idea_category),
    INDEX idx_idea_status (idea_status),
    INDEX idx_priority_level (priority_level),
    INDEX idx_submitted_by (submitted_by),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Innovation Projects Table
CREATE TABLE IF NOT EXISTS innovation_projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Project Details
    project_name VARCHAR(255) NOT NULL,
    project_description TEXT NULL,
    project_type ENUM('rd', 'pilot', 'implementation', 'improvement', 'experiment') NOT NULL,
    
    -- Period
    start_date DATE NOT NULL,
    target_end_date DATE NOT NULL,
    actual_end_date DATE NULL,
    
    -- Budget
    budget_amount DECIMAL(15,2) NOT NULL,
    actual_spent DECIMAL(15,2) DEFAULT 0.00,
    
    -- Team
    project_lead BIGINT UNSIGNED NOT NULL,
    team_members JSON NULL,
    
    -- Status
    project_status ENUM('planning', 'in_progress', 'on_hold', 'completed', 'cancelled') DEFAULT 'planning',
    completion_percentage INT DEFAULT 0,
    
    -- Results
    project_outcome TEXT NULL,
    lessons_learned TEXT NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_project_type (project_type),
    INDEX idx_project_status (project_status),
    INDEX idx_start_date (start_date),
    INDEX idx_project_lead (project_lead),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_lead) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Innovation Milestones Table
CREATE TABLE IF NOT EXISTS innovation_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    
    -- Milestone Details
    milestone_name VARCHAR(255) NOT NULL,
    milestone_description TEXT NULL,
    
    -- Dates
    target_date DATE NOT NULL,
    actual_date DATE NULL,
    
    -- Status
    milestone_status ENUM('pending', 'in_progress', 'completed', 'delayed', 'cancelled') DEFAULT 'pending',
    
    -- Dependencies
    dependencies JSON NULL,
    
    -- Notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_project_id (project_id),
    INDEX idx_target_date (target_date),
    INDEX idx_milestone_status (milestone_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES innovation_projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Innovation Metrics Table
CREATE TABLE IF NOT EXISTS innovation_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    
    -- Period
    metric_date DATE NOT NULL,
    metric_type ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    
    -- Idea Metrics
    total_ideas_submitted INT DEFAULT 0,
    ideas_approved INT DEFAULT 0,
    ideas_implemented INT DEFAULT 0,
    implementation_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Project Metrics
    active_projects INT DEFAULT 0,
    completed_projects INT DEFAULT 0,
    project_success_rate DECIMAL(5,2) DEFAULT 0.00,
    
    -- Financial Metrics
    total_investment DECIMAL(15,2) DEFAULT 0.00,
    realized_savings DECIMAL(15,2) DEFAULT 0.00,
    roi_percentage DECIMAL(10,2) DEFAULT 0.00,
    
    -- Impact Metrics
    customer_satisfaction_impact DECIMAL(5,2) DEFAULT 0.00,
    operational_efficiency_impact DECIMAL(5,2) DEFAULT 0.00,
    revenue_impact DECIMAL(15,2) DEFAULT 0.00,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_metric_date (metric_date),
    INDEX idx_metric_type (metric_type),
    UNIQUE KEY unique_date (restaurant_id, metric_date, metric_type),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Innovation Collaboration Table
CREATE TABLE IF NOT EXISTS innovation_collaboration (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NULL,
    idea_id BIGINT UNSIGNED NULL,
    
    -- Collaboration Details
    collaboration_type ENUM('internal', 'external', 'partner', 'academic', 'customer') NOT NULL,
    partner_name VARCHAR(255) NULL,
    partner_type VARCHAR(100) NULL,
    
    -- Role
    collaboration_role VARCHAR(100) NOT NULL,
    contribution_description TEXT NULL,
    
    -- Period
    start_date DATE NOT NULL,
    end_date DATE NULL,
    
    -- Status
    collaboration_status ENUM('active', 'completed', 'suspended', 'cancelled') DEFAULT 'active',
    
    -- Contact
    contact_person VARCHAR(255) NULL,
    contact_email VARCHAR(255) NULL,
    
    -- Staff
    created_by BIGINT UNSIGNED NOT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_project_id (project_id),
    INDEX idx_idea_id (idea_id),
    INDEX idx_collaboration_type (collaboration_type),
    INDEX idx_collaboration_status (collaboration_status),
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES innovation_projects(id) ON DELETE SET NULL,
    FOREIGN KEY (idea_id) REFERENCES innovation_ideas(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
