-- Migration 007: AI Infrastructure Tables
-- AI Implementation (Phase 14 - RESEARCH_37)

-- Create ai_models table
CREATE TABLE IF NOT EXISTS ai_models (
    model_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_code VARCHAR(50) NOT NULL UNIQUE,
    model_name VARCHAR(150) NOT NULL,
    model_category ENUM('PREDICTIVE', 'DECISION_SUPPORT', 'OPERATIONAL', 'CUSTOMER_EXPERIENCE', 'FINANCIAL') NOT NULL,
    model_type ENUM('DEMAND_FORECASTING', 'INVENTORY_OPTIMIZATION', 'STAFF_SCHEDULING', 'MENU_ENGINEERING', 'DYNAMIC_PRICING', 'SUPPLIER_SELECTION', 'KITCHEN_OPERATIONS', 'TABLE_MANAGEMENT', 'DELIVERY_OPTIMIZATION', 'PERSONALIZATION', 'SENTIMENT_ANALYSIS', 'CHURN_PREDICTION', 'REVENUE_FORECASTING', 'COST_OPTIMIZATION', 'FRAUD_DETECTION') NOT NULL,
    model_version VARCHAR(20) NOT NULL,
    model_description TEXT,
    autonomy_level ENUM('RECOMMENDATION', 'AUTO_APPROVE_BOUNDS', 'FULL_AUTONOMY') DEFAULT 'RECOMMENDATION',
    training_data_source TEXT,
    model_accuracy DECIMAL(5,4),
    last_trained_at TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_model_category (model_category),
    INDEX idx_model_type (model_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ai_predictions table
CREATE TABLE IF NOT EXISTS ai_predictions (
    prediction_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    prediction_type VARCHAR(50) NOT NULL,
    input_data JSON,
    prediction_result JSON,
    confidence_score DECIMAL(5,4),
    prediction_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_prediction_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_prediction_model FOREIGN KEY (model_id) REFERENCES ai_models(model_id),
    INDEX idx_prediction_tenant (tenant_id),
    INDEX idx_prediction_model (model_id),
    INDEX idx_prediction_type (prediction_type),
    INDEX idx_prediction_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ai_model_feedback table
CREATE TABLE IF NOT EXISTS ai_model_feedback (
    feedback_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prediction_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    feedback_type ENUM('POSITIVE', 'NEGATIVE', 'NEUTRAL') NOT NULL,
    feedback_comment TEXT,
    actual_outcome JSON,
    accuracy_rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_feedback_prediction FOREIGN KEY (prediction_id) REFERENCES ai_predictions(prediction_id),
    INDEX idx_feedback_prediction (prediction_id),
    INDEX idx_feedback_type (feedback_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ai_decision_logs table
CREATE TABLE IF NOT EXISTS ai_decision_logs (
    decision_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    prediction_id BIGINT UNSIGNED,
    decision_type VARCHAR(50) NOT NULL,
    decision_data JSON,
    human_override TINYINT(1) DEFAULT 0,
    override_reason TEXT,
    override_user_id BIGINT UNSIGNED,
    decision_outcome JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_decision_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    CONSTRAINT fk_decision_model FOREIGN KEY (model_id) REFERENCES ai_models(model_id),
    CONSTRAINT fk_decision_prediction FOREIGN KEY (prediction_id) REFERENCES ai_predictions(prediction_id),
    INDEX idx_decision_tenant (tenant_id),
    INDEX idx_decision_model (model_id),
    INDEX idx_decision_override (human_override),
    INDEX idx_decision_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ai_governance_logs table
CREATE TABLE IF NOT EXISTS ai_governance_logs (
    governance_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_id BIGINT UNSIGNED NOT NULL,
    log_type ENUM('ETHICS_REVIEW', 'COMPLIANCE_CHECK', 'RISK_ASSESSMENT', 'AUDIT', 'MODEL_UPDATE', 'PERFORMANCE_REVIEW') NOT NULL,
    log_description TEXT,
    reviewer_id BIGINT UNSIGNED,
    review_status ENUM('PENDING', 'APPROVED', 'REJECTED', 'REQUIRES_CHANGES') DEFAULT 'PENDING',
    risk_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') DEFAULT 'MEDIUM',
    findings TEXT,
    recommendations TEXT,
    action_items JSON,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_governance_model FOREIGN KEY (model_id) REFERENCES ai_models(model_id),
    INDEX idx_governance_model (model_id),
    INDEX idx_governance_type (log_type),
    INDEX idx_governance_status (review_status),
    INDEX idx_governance_risk (risk_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create ai_autonomy_levels table
CREATE TABLE IF NOT EXISTS ai_autonomy_levels (
    autonomy_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    model_id BIGINT UNSIGNED NOT NULL,
    autonomy_level ENUM('RECOMMENDATION', 'AUTO_APPROVE_BOUNDS', 'FULL_AUTONOMY') NOT NULL,
    bounds_config JSON,
    approval_threshold DECIMAL(5,4),
    human_review_required TINYINT(1) DEFAULT 1,
    auto_approve_conditions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_autonomy_model FOREIGN KEY (model_id) REFERENCES ai_models(model_id),
    INDEX idx_autonomy_model (model_id),
    INDEX idx_autonomy_level (autonomy_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default AI models
INSERT INTO ai_models (model_code, model_name, model_category, model_type, model_version, model_description, autonomy_level) VALUES
-- Predictive AI
('DEMAND_FORECAST', 'Demand Forecasting Model', 'PREDICTIVE', 'DEMAND_FORECASTING', '1.0.0', 'Predicts daily and weekly demand for menu items', 'RECOMMENDATION'),
('INVENTORY_OPT', 'Inventory Optimization Model', 'PREDICTIVE', 'INVENTORY_OPTIMIZATION', '1.0.0', 'Optimizes stock levels and reorder points', 'AUTO_APPROVE_BOUNDS'),
('STAFF_SCHED', 'Staff Scheduling Model', 'PREDICTIVE', 'STAFF_SCHEDULING', '1.0.0', 'Optimizes staff schedules based on demand', 'RECOMMENDATION'),

-- Decision Support AI
('MENU_ENG', 'Menu Engineering Model', 'DECISION_SUPPORT', 'MENU_ENGINEERING', '1.0.0', 'Recommends price adjustments and menu changes', 'RECOMMENDATION'),
('DYNAMIC_PRICE', 'Dynamic Pricing Model', 'DECISION_SUPPORT', 'DYNAMIC_PRICING', '1.0.0', 'Suggests real-time price adjustments', 'AUTO_APPROVE_BOUNDS'),
('SUPPLIER_SEL', 'Supplier Selection Model', 'DECISION_SUPPORT', 'SUPPLIER_SELECTION', '1.0.0', 'Recommends optimal suppliers', 'RECOMMENDATION'),

-- Operational AI
('KITCHEN_OPS', 'Kitchen Operations Model', 'OPERATIONAL', 'KITCHEN_OPERATIONS', '1.0.0', 'Optimizes kitchen workflow', 'AUTO_APPROVE_BOUNDS'),
('TABLE_MGMT', 'Table Management Model', 'OPERATIONAL', 'TABLE_MANAGEMENT', '1.0.0', 'Optimizes table assignment and turnover', 'AUTO_APPROVE_BOUNDS'),
('DELIVERY_OPT', 'Delivery Optimization Model', 'OPERATIONAL', 'DELIVERY_OPTIMIZATION', '1.0.0', 'Optimizes delivery routes and timing', 'AUTO_APPROVE_BOUNDS'),

-- Customer Experience AI
('PERSONALIZE', 'Personalization Model', 'CUSTOMER_EXPERIENCE', 'PERSONALIZATION', '1.0.0', 'Personalizes recommendations for customers', 'RECOMMENDATION'),
('SENTIMENT', 'Sentiment Analysis Model', 'CUSTOMER_EXPERIENCE', 'SENTIMENT_ANALYSIS', '1.0.0', 'Analyzes customer reviews and feedback', 'RECOMMENDATION'),
('CHURN_PRED', 'Churn Prediction Model', 'CUSTOMER_EXPERIENCE', 'CHURN_PREDICTION', '1.0.0', 'Predicts customer churn risk', 'RECOMMENDATION'),

-- Financial AI
('REVENUE_FORE', 'Revenue Forecasting Model', 'FINANCIAL', 'REVENUE_FORECASTING', '1.0.0', 'Forecasts revenue and financial metrics', 'RECOMMENDATION'),
('COST_OPT', 'Cost Optimization Model', 'FINANCIAL', 'COST_OPTIMIZATION', '1.0.0', 'Identifies cost reduction opportunities', 'RECOMMENDATION'),
('FRAUD_DET', 'Fraud Detection Model', 'FINANCIAL', 'FRAUD_DETECTION', '1.0.0', 'Detects fraudulent transactions', 'FULL_AUTONOMY');
