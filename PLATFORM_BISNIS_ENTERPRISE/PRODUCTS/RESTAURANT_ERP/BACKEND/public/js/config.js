/**
 * EBP Restaurant Frontend Configuration
 * 
 * This file contains all configuration values for the frontend application.
 * In production, these should be loaded from environment-specific config files.
 */

const Config = {
    // API Configuration
    api: {
        baseURL: window.API_BASE_URL || 'http://localhost:8000/api/v1',
        timeout: 30000, // 30 seconds
        retryAttempts: 3
    },
    
    // Application Configuration
    app: {
        name: 'EBP Restaurant',
        version: '1.0.0',
        environment: window.APP_ENV || 'development',
        debug: window.APP_DEBUG === 'true' || false
    },
    
    // Authentication Configuration
    auth: {
        tokenKey: 'authToken',
        userKey: 'ebp_user',
        tenantIdKey: 'tenantId',
        branchIdKey: 'branchId',
        tokenRefreshThreshold: 300 // 5 minutes before expiration
    },
    
    // UI Configuration
    ui: {
        itemsPerPage: 20,
        dateFormat: 'DD/MM/YYYY',
        timeFormat: 'HH:mm',
        currency: 'IDR',
        currencySymbol: 'Rp'
    },
    
    // Feature Flags
    features: {
        enableLoyalty: true,
        enableDelivery: true,
        enableReservations: true,
        enableKitchenDisplay: true,
        enableReports: true
    }
};

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Config;
}
