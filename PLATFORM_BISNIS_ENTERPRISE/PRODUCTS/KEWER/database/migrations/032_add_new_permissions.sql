-- Migration 032: Add New Permissions
-- Date: 8 Mei 2026
-- Description: Add permissions for new features (dashboard analytics, credit scoring, GPS tracking, audit log, geographic analysis, sync, webhooks)

-- Insert new permissions
INSERT INTO permissions (kode, nama, kategori, deskripsi) VALUES
('dashboard_analytics_view', 'View Dashboard Analytics', 'dashboard', 'View advanced dashboard analytics'),
('dashboard_analytics_export', 'Export Dashboard Analytics', 'dashboard', 'Export dashboard analytics data'),
('credit_scoring_view', 'View Credit Scoring', 'credit_scoring', 'View credit scoring information'),
('credit_scoring_calculate', 'Calculate Credit Scores', 'credit_scoring', 'Calculate credit scores'),
('credit_scoring_auto_approve', 'Auto-approve Pinjaman', 'credit_scoring', 'Auto-approve pinjaman based on credit score'),
('gps_tracking_view', 'View GPS Tracking', 'gps', 'View GPS tracking data'),
('gps_tracking_use', 'Use GPS Tracking', 'gps', 'Use GPS tracking for pembayaran'),
('visits_view', 'View Petugas Visits', 'visits', 'View petugas visits'),
('visits_create', 'Create Petugas Visits', 'visits', 'Create petugas visits'),
('audit_log_view', 'View Audit Log', 'audit', 'View audit log'),
('audit_log_export', 'Export Audit Log', 'audit', 'Export audit log'),
('geographic_analysis_view', 'View Geographic Analysis', 'geographic', 'View geographic analysis'),
('geographic_analysis_search', 'Geographic Radius Search', 'geographic', 'Use geographic radius search'),
('sync_view', 'View Sync Status', 'sync', 'View sync status'),
('sync_execute', 'Execute Data Sync', 'sync', 'Execute data sync'),
('webhook_manage', 'Manage Webhooks', 'webhook', 'Manage webhooks'),
('webhook_view', 'View Webhooks', 'webhook', 'View webhooks'),
('external_api_view', 'View External API Logs', 'api', 'View external API logs')
ON DUPLICATE KEY UPDATE nama = VALUES(nama), deskripsi = VALUES(deskripsi);

-- Assign permissions to roles
-- Bos (role = bos) - Full access to all new features
INSERT INTO role_permissions (role, permission_code, granted) VALUES
('bos', 'dashboard_analytics_view', 1),
('bos', 'dashboard_analytics_export', 1),
('bos', 'credit_scoring_view', 1),
('bos', 'credit_scoring_calculate', 1),
('bos', 'credit_scoring_auto_approve', 1),
('bos', 'gps_tracking_view', 1),
('bos', 'gps_tracking_use', 1),
('bos', 'visits_view', 1),
('bos', 'visits_create', 1),
('bos', 'audit_log_view', 1),
('bos', 'audit_log_export', 1),
('bos', 'geographic_analysis_view', 1),
('bos', 'geographic_analysis_search', 1),
('bos', 'sync_view', 1),
('bos', 'sync_execute', 1),
('bos', 'webhook_manage', 1),
('bos', 'webhook_view', 1),
('bos', 'external_api_view', 1)
ON DUPLICATE KEY UPDATE granted = VALUES(granted);

-- Manager Pusat (role = manager_pusat) - View and analytics
INSERT INTO role_permissions (role, permission_code, granted) VALUES
('manager_pusat', 'dashboard_analytics_view', 1),
('manager_pusat', 'dashboard_analytics_export', 1),
('manager_pusat', 'credit_scoring_view', 1),
('manager_pusat', 'credit_scoring_calculate', 1),
('manager_pusat', 'gps_tracking_view', 1),
('manager_pusat', 'visits_view', 1),
('manager_pusat', 'audit_log_view', 1),
('manager_pusat', 'audit_log_export', 1),
('manager_pusat', 'geographic_analysis_view', 1),
('manager_pusat', 'geographic_analysis_search', 1),
('manager_pusat', 'sync_view', 1),
('manager_pusat', 'webhook_view', 1),
('manager_pusat', 'external_api_view', 1)
ON DUPLICATE KEY UPDATE granted = VALUES(granted);

-- Manager Cabang (role = manager_cabang) - View and limited analytics
INSERT INTO role_permissions (role, permission_code, granted) VALUES
('manager_cabang', 'dashboard_analytics_view', 1),
('manager_cabang', 'credit_scoring_view', 1),
('manager_cabang', 'gps_tracking_view', 1),
('manager_cabang', 'visits_view', 1),
('manager_cabang', 'audit_log_view', 1),
('manager_cabang', 'geographic_analysis_view', 1)
ON DUPLICATE KEY UPDATE granted = VALUES(granted);

-- Admin Pusat (role = admin_pusat) - Full access to audit and logs
INSERT INTO role_permissions (role, permission_code, granted) VALUES
('admin_pusat', 'dashboard_analytics_view', 1),
('admin_pusat', 'credit_scoring_view', 1),
('admin_pusat', 'gps_tracking_view', 1),
('admin_pusat', 'visits_view', 1),
('admin_pusat', 'audit_log_view', 1),
('admin_pusat', 'audit_log_export', 1),
('admin_pusat', 'external_api_view', 1)
ON DUPLICATE KEY UPDATE granted = VALUES(granted);

-- Admin Cabang (role = admin_cabang) - View audit for their cabang
INSERT INTO role_permissions (role, permission_code, granted) VALUES
('admin_cabang', 'dashboard_analytics_view', 1),
('admin_cabang', 'audit_log_view', 1)
ON DUPLICATE KEY UPDATE granted = VALUES(granted);

-- Petugas Pusat (role = petugas_pusat) - GPS tracking and visits
INSERT INTO role_permissions (role, permission_code, granted) VALUES
('petugas_pusat', 'gps_tracking_use', 1),
('petugas_pusat', 'visits_view', 1),
('petugas_pusat', 'visits_create', 1)
ON DUPLICATE KEY UPDATE granted = VALUES(granted);

-- Petugas Cabang (role = petugas_cabang) - GPS tracking and visits
INSERT INTO role_permissions (role, permission_code, granted) VALUES
('petugas_cabang', 'gps_tracking_use', 1),
('petugas_cabang', 'visits_view', 1),
('petugas_cabang', 'visits_create', 1)
ON DUPLICATE KEY UPDATE granted = VALUES(granted);

-- Karyawan (role = karyawan) - View only
INSERT INTO role_permissions (role, permission_code, granted) VALUES
('karyawan', 'dashboard_analytics_view', 1)
ON DUPLICATE KEY UPDATE granted = VALUES(granted);
