/*
========================================================

EBP RESTAURANT BACKEND - MIGRATION PHASE 17
PLATFORM OWNER / SUPER ADMIN SUPPORT

This migration adds support for platform-level users who can manage
the entire system without being tied to a specific tenant.

========================================================
*/

USE ebp_restaurant_db;

/*
========================================================
MODIFY USERS TABLE - ADD PLATFORM OWNER SUPPORT
========================================================
*/

-- Add is_platform_owner field to users table
ALTER TABLE users 
ADD COLUMN is_platform_owner BOOLEAN DEFAULT FALSE AFTER status;

-- Make tenant_id nullable to allow platform owner users
ALTER TABLE users 
MODIFY COLUMN tenant_id BIGINT UNSIGNED NULL;

-- Update unique constraints to handle platform owners
ALTER TABLE users 
DROP INDEX idx_users_tenant_username;

ALTER TABLE users 
ADD UNIQUE KEY idx_users_tenant_username (tenant_id, username),
ADD UNIQUE KEY idx_users_platform_username (is_platform_owner, username);

ALTER TABLE users 
DROP INDEX idx_users_tenant_email;

ALTER TABLE users 
ADD UNIQUE KEY idx_users_tenant_email (tenant_id, email),
ADD UNIQUE KEY idx_users_platform_email (is_platform_owner, email);

-- Update foreign key to allow NULL
ALTER TABLE users 
DROP FOREIGN KEY users_ibfk_1;

ALTER TABLE users 
ADD CONSTRAINT fk_users_tenant_id 
FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) 
ON DELETE CASCADE ON UPDATE CASCADE;

/*
========================================================
MODIFY ROLES TABLE - ADD PLATFORM ROLES SUPPORT
========================================================
*/

-- Add is_platform_role field to roles table
ALTER TABLE roles 
ADD COLUMN is_platform_role BOOLEAN DEFAULT FALSE AFTER is_system;

-- Make tenant_id nullable to allow platform-wide roles
ALTER TABLE roles 
MODIFY COLUMN tenant_id BIGINT UNSIGNED NULL;

-- Update unique constraints to handle platform roles
ALTER TABLE roles 
DROP INDEX idx_roles_tenant_code;

ALTER TABLE roles 
ADD UNIQUE KEY idx_roles_tenant_code (tenant_id, role_code),
ADD UNIQUE KEY idx_roles_platform_code (is_platform_role, role_code);

-- Update foreign key to allow NULL
ALTER TABLE roles 
DROP FOREIGN KEY roles_ibfk_1;

ALTER TABLE roles 
ADD CONSTRAINT fk_roles_tenant_id 
FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) 
ON DELETE CASCADE ON UPDATE CASCADE;

/*
========================================================
INSERT PLATFORM ROLES
========================================================
*/

-- Platform Owner / Super Admin - Full system access
INSERT INTO roles (tenant_id, role_code, role_name, description, is_system, is_platform_role, status) 
VALUES (NULL, 'PLATFORM_OWNER', 'Platform Owner', 'Full system access across all tenants', TRUE, TRUE, 'ACTIVE');

-- Platform Admin - Can manage tenants and system settings
INSERT INTO roles (tenant_id, role_code, role_name, description, is_system, is_platform_role, status) 
VALUES (NULL, 'PLATFORM_ADMIN', 'Platform Admin', 'Can manage tenants and system configuration', TRUE, TRUE, 'ACTIVE');

-- Platform Support - Read-only access to all tenants for support
INSERT INTO roles (tenant_id, role_code, role_name, description, is_system, is_platform_role, status) 
VALUES (NULL, 'PLATFORM_SUPPORT', 'Platform Support', 'Read-only access for customer support', TRUE, TRUE, 'ACTIVE');

/*
========================================================
CREATE DEFAULT PLATFORM OWNER USER
========================================================
*/

-- Create default platform owner user (password: ChangeMe123!)
-- IMPORTANT: Change this password immediately after first login
INSERT INTO users (tenant_id, branch_id, username, email, password, full_name, phone, status, is_platform_owner) 
VALUES (NULL, NULL, 'platform_owner', 'platform_owner@ebp-system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EBP Platform Owner', '+628000000000', 'ACTIVE', TRUE);

-- Assign PLATFORM_OWNER role to the default platform owner
INSERT INTO user_roles (user_id, role_id)
SELECT u.user_id, r.role_id 
FROM users u, roles r 
WHERE u.username = 'platform_owner' AND r.role_code = 'PLATFORM_OWNER';

/*
========================================================
ADD INDEXES FOR PERFORMANCE
========================================================
*/

-- Add index for platform owner queries
ALTER TABLE users 
ADD INDEX idx_users_is_platform_owner (is_platform_owner);

-- Add index for platform role queries
ALTER TABLE roles 
ADD INDEX idx_roles_is_platform_role (is_platform_role);

/*
========================================================
MIGRATION COMPLETE
========================================================
*/

-- Verify the migration
SELECT 'Migration Phase 17 - Platform Owner Support completed successfully' AS status;
SELECT COUNT(*) AS platform_owners FROM users WHERE is_platform_owner = TRUE;
SELECT COUNT(*) AS platform_roles FROM roles WHERE is_platform_role = TRUE;
