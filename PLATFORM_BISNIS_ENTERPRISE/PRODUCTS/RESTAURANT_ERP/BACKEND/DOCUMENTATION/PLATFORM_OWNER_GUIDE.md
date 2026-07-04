# Platform Owner / Super Admin Guide

## Overview

EBP Restaurant ERP now supports platform-level users who can manage the entire system without being tied to a specific tenant. This allows for:

- **Platform Owners** - Full system access across all tenants
- **Platform Admins** - Can manage tenants and system settings
- **Platform Support** - Read-only access for customer support

## Architecture

### Database Changes

**Users Table:**
- Added `is_platform_owner` (BOOLEAN) - Identifies platform-level users
- Made `tenant_id` nullable - Platform owners don't belong to a specific tenant
- Updated unique constraints to handle platform owner usernames/emails

**Roles Table:**
- Added `is_platform_role` (BOOLEAN) - Identifies platform-wide roles
- Made `tenant_id` nullable - Platform roles are not tenant-specific
- Updated unique constraints to handle platform role codes

### Platform Roles

Three default platform roles are created:

1. **PLATFORM_OWNER** - Full system access across all tenants
2. **PLATFORM_ADMIN** - Can manage tenants and system configuration
3. **PLATFORM_SUPPORT** - Read-only access for customer support

## Authentication

### Login Process

Platform owners login through the same endpoint as regular users:
```
POST /api/auth/login
{
  "username": "platform_owner",
  "password": "ChangeMe123!"
}
```

The JWT token includes:
- `user_id` - User ID
- `username` - Username
- `tenant_id` - NULL for platform owners
- `branch_id` - NULL for platform owners
- `is_platform_owner` - TRUE for platform owners
- `role` - Role name (e.g., "PLATFORM_OWNER")

### Default Platform Owner

**Username:** `platform_owner`  
**Email:** `platform_owner@ebp-system.com`  
**Password:** `ChangeMe123!`  
**Role:** PLATFORM_OWNER

⚠️ **IMPORTANT:** Change the default password immediately after first login!

## Authorization

### Permission Middleware

Platform owners automatically bypass all permission checks:
```php
PermissionMiddleware::handle($request, 'any_permission');
// Returns TRUE for platform owners regardless of permission
```

### Tenant Middleware

Platform owners can access system without tenant context:
```php
TenantMiddleware::validate($tenantId, $isPlatformOwner);
// Returns NULL for platform owners (platform-level access)
// Returns $tenantId for regular users
```

## Usage Examples

### Creating a Platform Owner

```sql
INSERT INTO users (tenant_id, branch_id, username, email, password, full_name, status, is_platform_owner) 
VALUES (NULL, NULL, 'admin', 'admin@ebp.com', '$2y$10$hashed_password', 'System Admin', 'ACTIVE', TRUE);

INSERT INTO user_roles (user_id, role_id)
SELECT u.user_id, r.role_id 
FROM users u, roles r 
WHERE u.username = 'admin' AND r.role_code = 'PLATFORM_ADMIN';
```

### Checking if User is Platform Owner

```php
// From JWT payload
$isPlatformOwner = $payload['is_platform_owner'] ?? false;

if ($isPlatformOwner) {
    // Platform-level access
    // Can access all tenants
} else {
    // Tenant-level access
    // Restricted to user's tenant
}
```

### Platform Owner API Access

Platform owners can:
- View/manage all tenants
- Access any tenant's data
- Create/modify/delete tenants
- Manage system-wide settings
- View audit logs across all tenants

Regular users can:
- Only access their own tenant's data
- Be restricted by their role permissions
- Cannot access other tenants

## Security Considerations

1. **Password Security:** Always change default passwords
2. **Least Privilege:** Use PLATFORM_ADMIN or PLATFORM_SUPPORT when full access isn't needed
3. **Audit Logging:** All platform owner actions are logged in audit_logs table
4. **Token Expiration:** Platform owner tokens expire after 8 hours (configurable)

## Migration

To enable platform owner support, run:

```bash
mysql -u root -p ebp_restaurant_db < migration_phase17_platform_owner.sql
```

This will:
- Add `is_platform_owner` column to users table
- Make `tenant_id` nullable in users and roles tables
- Create platform roles
- Create default platform owner user
- Update indexes and constraints

## Future Enhancements

Potential improvements:
- Platform owner activity dashboard
- Multi-factor authentication for platform owners
- IP whitelisting for platform owner access
- Platform owner session management
- Granular platform permissions (beyond role-based)
