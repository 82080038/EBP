-- Create ai_models table
CREATE TABLE IF NOT EXISTS ai_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(100) NOT NULL,
    model_type ENUM('predictive', 'decision_support', 'operational', 'customer_experience', 'financial') NOT NULL,
    model_category VARCHAR(50) NOT NULL,
    model_version VARCHAR(50) NOT NULL,
    model_description TEXT,
    training_data_source JSON,
    model_file_path VARCHAR(255),
    model_parameters JSON,
    performance_metrics JSON,
    status ENUM('development', 'training', 'testing', 'production', 'deprecated') DEFAULT 'development',
    deployed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_model_type (model_type),
    INDEX idx_model_category (model_category),
    INDEX idx_status (status)
);

-- Create ai_predictions table
CREATE TABLE IF NOT EXISTS ai_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ai_model_id INT NOT NULL,
    prediction_type VARCHAR(50) NOT NULL,
    input_data JSON,
    prediction_result JSON,
    confidence_score DECIMAL(5,2),
    prediction_context JSON,
    tenant_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ai_model_id (ai_model_id),
    INDEX idx_prediction_type (prediction_type),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Create ai_model_feedback table
CREATE TABLE IF NOT EXISTS ai_model_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ai_prediction_id INT NOT NULL,
    feedback_type ENUM('positive', 'negative', 'neutral') NOT NULL,
    feedback_text TEXT,
    actual_outcome JSON,
    feedback_provider VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ai_prediction_id (ai_prediction_id),
    INDEX idx_feedback_type (feedback_type)
) ENGINE=InnoDB;

-- Create ai_decision_logs table
CREATE TABLE IF NOT EXISTS ai_decision_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ai_model_id INT NOT NULL,
    decision_type VARCHAR(100) NOT NULL,
    decision_context JSON,
    decision_result JSON,
    autonomy_level ENUM('recommendation', 'auto_approve_bounds', 'full_autonomy') NOT NULL,
    human_override BOOLEAN DEFAULT FALSE,
    override_reason TEXT,
    tenant_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ai_model_id (ai_model_id),
    INDEX idx_decision_type (decision_type),
    INDEX idx_autonomy_level (autonomy_level),
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB;

-- Create demand_forecasts table
CREATE TABLE IF NOT EXISTS demand_forecasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    forecast_date DATE NOT NULL,
    forecast_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    predicted_orders INT,
    predicted_revenue DECIMAL(15,2),
    confidence_level DECIMAL(5,2),
    factors_considered JSON,
    actual_orders INT,
    actual_revenue DECIMAL(15,2),
    forecast_accuracy DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    UNIQUE KEY unique_tenant_date_type (tenant_id, forecast_date, forecast_type),
    INDEX idx_forecast_date (forecast_date),
    INDEX idx_forecast_type (forecast_type)
) ENGINE=InnoDB;

-- Create inventory_optimizations table
CREATE TABLE IF NOT EXISTS inventory_optimizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    inventory_id INT NOT NULL,
    optimization_date DATE NOT NULL,
    recommended_order_quantity DECIMAL(10,2),
    current_stock DECIMAL(10,2),
    predicted_demand DECIMAL(10,2),
    lead_time_days INT,
    safety_stock_level DECIMAL(10,2),
    reorder_point DECIMAL(10,2),
    cost_savings_estimate DECIMAL(10,2),
    implemented BOOLEAN DEFAULT FALSE,
    implemented_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_optimization_date (optimization_date)
) ENGINE=InnoDB;

-- Create staff_schedule_recommendations table
CREATE TABLE IF NOT EXISTS staff_schedule_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    schedule_date DATE NOT NULL,
    shift_type VARCHAR(50) NOT NULL,
    recommended_staff_count INT,
    predicted_demand INT,
    required_skills JSON,
    cost_estimate DECIMAL(10,2),
    implemented BOOLEAN DEFAULT FALSE,
    actual_staff_count INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_schedule_date (schedule_date)
) ENGINE=InnoDB;

-- Create menu_engineering_recommendations table
CREATE TABLE IF NOT EXISTS menu_engineering_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    product_id INT NOT NULL,
    recommendation_date DATE NOT NULL,
    recommendation_type ENUM('price_adjustment', 'promotion', 'remove', 'feature') NOT NULL,
    current_price DECIMAL(10,2),
    recommended_price DECIMAL(10,2),
    expected_impact JSON,
    confidence_score DECIMAL(5,2),
    implemented BOOLEAN DEFAULT FALSE,
    implemented_at TIMESTAMP NULL,
    result_measured BOOLEAN DEFAULT FALSE,
    actual_impact JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_product_id (product_id),
    INDEX idx_recommendation_date (recommendation_date)
) ENGINE=InnoDB;

-- Create dynamic_pricing_rules table
CREATE TABLE IF NOT EXISTS dynamic_pricing_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    product_id INT NOT NULL,
    rule_name VARCHAR(100) NOT NULL,
    rule_conditions JSON NOT NULL,
    price_adjustment_type ENUM('percentage', 'fixed') NOT NULL,
    price_adjustment_value DECIMAL(10,2) NOT NULL,
    min_price DECIMAL(10,2),
    max_price DECIMAL(10,2),
    autonomy_level ENUM('recommendation', 'auto_approve_bounds', 'full_autonomy') DEFAULT 'recommendation',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_product_id (product_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB;

-- Create ai_governance_logs table
CREATE TABLE IF NOT EXISTS ai_governance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ai_model_id INT NOT NULL,
    governance_type ENUM('ethics_review', 'compliance_check', 'risk_assessment', 'audit') NOT NULL,
    review_date DATE NOT NULL,
    reviewer VARCHAR(100),
    findings TEXT,
    recommendations TEXT,
    action_required BOOLEAN DEFAULT FALSE,
    action_taken TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'overdue') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ai_model_id) REFERENCES ai_models(id) ON DELETE CASCADE,
    INDEX idx_ai_model_id (ai_model_id),
    INDEX idx_governance_type (governance_type),
    INDEX idx_status (status)
);

-- Insert default AI models
INSERT INTO ai_models (model_name, model_type, model_category, model_version, model_description, status) VALUES
('Demand Forecasting Model', 'predictive', 'demand_forecasting', '1.0.0', 'Predicts daily/weekly demand based on historical data', 'development'),
('Inventory Optimization Model', 'predictive', 'inventory', '1.0.0', 'Optimizes inventory levels and reorder points', 'development'),
('Staff Scheduling Model', 'predictive', 'staffing', '1.0.0', 'Recommends optimal staff schedules', 'development'),
('Menu Engineering Model', 'decision_support', 'menu', '1.0.0', 'Provides menu engineering recommendations', 'development'),
('Dynamic Pricing Model', 'decision_support', 'pricing', '1.0.0', 'Suggests dynamic pricing adjustments', 'development'),
('Supplier Selection Model', 'decision_support', 'procurement', '1.0.0', 'Recommends optimal suppliers', 'development'),
('Kitchen Operations AI', 'operational', 'kitchen', '1.0.0', 'Optimizes kitchen operations and workflow', 'development'),
('Table Management AI', 'operational', 'front_of_house', '1.0.0', 'Optimizes table assignment and turnover', 'development'),
('Delivery Optimization AI', 'operational', 'delivery', '1.0.0', 'Optimizes delivery routes and timing', 'development'),
('Customer Personalization AI', 'customer_experience', 'personalization', '1.0.0', 'Personalizes customer experience', 'development'),
('Sentiment Analysis AI', 'customer_experience', 'feedback', '1.0.0', 'Analyzes customer sentiment from reviews', 'development'),
('Churn Prediction AI', 'customer_experience', 'retention', '1.0.0', 'Predicts customer churn risk', 'development'),
('Revenue Forecasting AI', 'financial', 'forecasting', '1.0.0', 'Forecasts revenue and financial metrics', 'development'),
('Cost Optimization AI', 'financial', 'cost_analysis', '1.0.0', 'Identifies cost optimization opportunities', 'development'),
('Fraud Detection AI', 'financial', 'security', '1.0.0', 'Detects fraudulent transactions', 'development');
