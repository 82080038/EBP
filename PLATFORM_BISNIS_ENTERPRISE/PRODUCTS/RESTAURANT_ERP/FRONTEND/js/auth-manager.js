/**
 * EBP Restaurant Authentication Manager
 * Handles authentication flow, token refresh, and permission-based UI
 */
class AuthManager {
    constructor() {
        this.token = localStorage.getItem('authToken') || null;
        this.user = JSON.parse(localStorage.getItem('ebp_user') || 'null');
        this.tenantId = localStorage.getItem('tenantId') || null;
        this.branchId = localStorage.getItem('branchId') || null;
        this.permissions = JSON.parse(localStorage.getItem('ebp_permissions') || '[]');
        this.tokenRefreshThreshold = Config.auth.tokenRefreshThreshold || 300; // 5 minutes
        this.refreshTimer = null;
        
        this.init();
    }

    init() {
        // Check if user is logged in
        if (this.token && this.user) {
            this.scheduleTokenRefresh();
            this.setupAutoLogout();
        }

        // Listen for API loading events
        window.addEventListener('apiLoading', (e) => {
            this.handleLoadingEvent(e.detail);
        });
    }

    async login(username, password) {
        try {
            const response = await window.apiClient.login(username, password);
            
            if (response.success) {
                this.setAuthData(response.token, response.user);
                return { success: true, user: response.user };
            } else {
                return { success: false, message: response.message };
            }
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, message: error.message };
        }
    }

    logout() {
        this.clearAuthData();
        window.location.href = '/dashboard/index.html';
    }

    setAuthData(token, user) {
        this.token = token;
        this.user = user;
        this.tenantId = user.tenant_id;
        this.branchId = user.branch_id;
        
        localStorage.setItem('authToken', token);
        localStorage.setItem('ebp_user', JSON.stringify(user));
        localStorage.setItem('tenantId', user.tenant_id);
        localStorage.setItem('branchId', user.branch_id);
        
        window.apiClient.setToken(token);
        window.apiClient.setTenant(user.tenant_id, user.branch_id);
        
        this.scheduleTokenRefresh();
        this.setupAutoLogout();
        this.loadPermissions();
    }

    clearAuthData() {
        this.token = null;
        this.user = null;
        this.tenantId = null;
        this.branchId = null;
        this.permissions = [];
        
        localStorage.removeItem('authToken');
        localStorage.removeItem('ebp_user');
        localStorage.removeItem('tenantId');
        localStorage.removeItem('branchId');
        localStorage.removeItem('ebp_permissions');
        
        window.apiClient.clearAuth();
        
        if (this.refreshTimer) {
            clearTimeout(this.refreshTimer);
            this.refreshTimer = null;
        }
    }

    async loadPermissions() {
        try {
            if (this.user && this.user.id) {
                const response = await window.apiClient.getUserPermissions(this.user.id);
                if (response && response.permissions) {
                    this.permissions = response.permissions;
                    localStorage.setItem('ebp_permissions', JSON.stringify(this.permissions));
                    this.applyPermissionBasedUI();
                }
            }
        } catch (error) {
            console.error('Failed to load permissions:', error);
        }
    }

    hasPermission(permission) {
        if (this.user && this.user.is_platform_owner) {
            return true;
        }
        return this.permissions.includes(permission);
    }

    hasAnyPermission(permissions) {
        if (this.user && this.user.is_platform_owner) {
            return true;
        }
        return permissions.some(p => this.permissions.includes(p));
    }

    hasAllPermissions(permissions) {
        if (this.user && this.user.is_platform_owner) {
            return true;
        }
        return permissions.every(p => this.permissions.includes(p));
    }

    applyPermissionBasedUI() {
        // Hide elements based on permissions
        document.querySelectorAll('[data-permission]').forEach(el => {
            const requiredPermission = el.getAttribute('data-permission');
            if (!this.hasPermission(requiredPermission)) {
                el.style.display = 'none';
            }
        });

        // Disable elements based on permissions
        document.querySelectorAll('[data-permission-disable]').forEach(el => {
            const requiredPermission = el.getAttribute('data-permission-disable');
            if (!this.hasPermission(requiredPermission)) {
                el.disabled = true;
            }
        });

        // Show elements based on permissions
        document.querySelectorAll('[data-permission-show]').forEach(el => {
            const requiredPermission = el.getAttribute('data-permission-show');
            if (this.hasPermission(requiredPermission)) {
                el.style.display = '';
            }
        });
    }

    getUser() {
        return this.user;
    }

    isAuthenticated() {
        return !!this.token && !!this.user;
    }

    getUserLevel() {
        if (!this.user) return null;
        return this.user.level || 'GUEST';
    }

    isPlatformOwner() {
        return this.user && this.user.is_platform_owner;
    }

    isTenantOwner() {
        return this.user && this.user.level === 'TENANT_OWNER';
    }

    isTenantMember() {
        return this.user && this.user.level === 'TENANT_MEMBER';
    }

    getUserRole() {
        return this.user ? this.user.role : null;
    }

    scheduleTokenRefresh() {
        if (this.refreshTimer) {
            clearTimeout(this.refreshTimer);
        }

        // Parse JWT to get expiration
        const payload = this.parseJWT(this.token);
        if (payload && payload.exp) {
            const expirationTime = payload.exp * 1000;
            const refreshTime = expirationTime - (this.tokenRefreshThreshold * 1000);
            const now = Date.now();

            if (refreshTime > now) {
                const delay = refreshTime - now;
                this.refreshTimer = setTimeout(() => {
                    this.refreshToken();
                }, delay);
            }
        }
    }

    async refreshToken() {
        try {
            // Implement token refresh logic if backend supports it
            // For now, just logout if token is expired
            console.log('Token refresh needed - implementing logout');
            this.logout();
        } catch (error) {
            console.error('Token refresh failed:', error);
            this.logout();
        }
    }

    setupAutoLogout() {
        // Auto logout after 8 hours of inactivity
        const inactivityTimeout = 8 * 60 * 60 * 1000; // 8 hours
        let inactivityTimer;

        const resetTimer = () => {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                this.logout();
            }, inactivityTimeout);
        };

        // Reset timer on user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetTimer);
        });

        resetTimer();
    }

    parseJWT(token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            return JSON.parse(jsonPayload);
        } catch (error) {
            console.error('Failed to parse JWT:', error);
            return null;
        }
    }

    handleLoadingEvent(detail) {
        const { isLoading, requestKey } = detail;
        
        // Show/hide global loading indicator
        const loadingIndicator = document.getElementById('globalLoadingIndicator');
        if (loadingIndicator) {
            if (isLoading) {
                loadingIndicator.style.display = 'flex';
            } else if (this.loadingRequestsCount === 0) {
                loadingIndicator.style.display = 'none';
            }
        }
    }

    get loadingRequestsCount() {
        return window.apiClient.loadingRequests.size;
    }

    requireAuth() {
        if (!this.isAuthenticated()) {
            window.location.href = '/dashboard/index.html';
            return false;
        }
        return true;
    }

    requirePermission(permission) {
        if (!this.requireAuth()) {
            return false;
        }
        
        if (!this.hasPermission(permission)) {
            alert('You do not have permission to access this page.');
            window.location.href = '/dashboard/index.html';
            return false;
        }
        
        return true;
    }

    requireRole(role) {
        if (!this.requireAuth()) {
            return false;
        }
        
        if (this.getUserRole() !== role && !this.isPlatformOwner()) {
            alert('You do not have the required role to access this page.');
            window.location.href = '/dashboard/index.html';
            return false;
        }
        
        return true;
    }

    requireLevel(level) {
        if (!this.requireAuth()) {
            return false;
        }
        
        const userLevel = this.getUserLevel();
        const levelHierarchy = {
            'PLATFORM_OWNER': 3,
            'TENANT_OWNER': 2,
            'TENANT_MEMBER': 1,
            'GUEST': 0
        };
        
        if (levelHierarchy[userLevel] < levelHierarchy[level]) {
            alert('You do not have the required access level to access this page.');
            window.location.href = '/dashboard/index.html';
            return false;
        }
        
        return true;
    }
}

// Initialize global auth manager
window.authManager = new AuthManager();
