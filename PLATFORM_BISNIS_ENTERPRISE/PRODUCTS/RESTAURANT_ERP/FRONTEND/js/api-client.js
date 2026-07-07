/**
 * EBP Restaurant API Client
 * Handles all API communication with the backend
 */
class APIClient {
    constructor() {
        this.baseURL = Config.api.baseURL;
        this.token = localStorage.getItem('authToken') || null;
        this.tenantId = localStorage.getItem('tenantId') || null;
        this.branchId = localStorage.getItem('branchId') || null;
        this.retryAttempts = Config.api.retryAttempts || 3;
        this.cache = new Map();
        this.cacheTTL = 5 * 60 * 1000; // 5 minutes
        this.loadingRequests = new Map();
    }

    setToken(token) {
        this.token = token;
        localStorage.setItem('authToken', token);
    }

    setTenant(tenantId, branchId) {
        this.tenantId = tenantId;
        this.branchId = branchId;
        localStorage.setItem('tenantId', tenantId);
        localStorage.setItem('branchId', branchId);
    }

    clearAuth() {
        this.token = null;
        this.tenantId = null;
        this.branchId = null;
        localStorage.removeItem('authToken');
        localStorage.removeItem('tenantId');
        localStorage.removeItem('branchId');
        this.clearCache();
    }

    clearCache() {
        this.cache.clear();
    }

    getCacheKey(method, endpoint, body) {
        return `${method}:${endpoint}:${JSON.stringify(body)}`;
    }

    getCachedResponse(cacheKey) {
        const cached = this.cache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < this.cacheTTL) {
            return cached.data;
        }
        return null;
    }

    setCachedResponse(cacheKey, data) {
        this.cache.set(cacheKey, {
            data,
            timestamp: Date.now()
        });
    }

    invalidateCache(pattern) {
        for (const key of this.cache.keys()) {
            if (key.includes(pattern)) {
                this.cache.delete(key);
            }
        }
    }

    setLoading(requestKey, isLoading) {
        if (isLoading) {
            this.loadingRequests.set(requestKey, true);
            this.emitLoadingEvent(true, requestKey);
        } else {
            this.loadingRequests.delete(requestKey);
            this.emitLoadingEvent(false, requestKey);
        }
    }

    isLoading(requestKey) {
        return this.loadingRequests.has(requestKey);
    }

    emitLoadingEvent(isLoading, requestKey) {
        const event = new CustomEvent('apiLoading', {
            detail: { isLoading, requestKey }
        });
        window.dispatchEvent(event);
    }

    async request(endpoint, options = {}) {
        const method = options.method || 'GET';
        const cacheKey = this.getCacheKey(method, endpoint, options.body);
        
        // Check cache for GET requests
        if (method === 'GET' && !options.skipCache) {
            const cached = this.getCachedResponse(cacheKey);
            if (cached) {
                return cached;
            }
        }

        // Set loading state
        this.setLoading(cacheKey, true);

        const url = `${this.baseURL}${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        // Add screen size information to headers
        if (window.screenSizeDetector) {
            headers['X-Screen-Size'] = window.screenSizeDetector.getScreenSize();
            headers['X-Screen-Width'] = window.innerWidth.toString();
        }

        const config = {
            ...options,
            headers
        };

        let lastError = null;
        
        // Retry logic
        for (let attempt = 0; attempt < this.retryAttempts; attempt++) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), Config.api.timeout);
                
                const response = await fetch(url, {
                    ...config,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);

                const data = await response.json();

                if (!response.ok) {
                    // Handle 401 Unauthorized - token expired
                    if (response.status === 401) {
                        this.clearAuth();
                        window.location.href = '/dashboard/index.html';
                        throw new Error('Session expired. Please login again.');
                    }
                    
                    // Handle 403 Forbidden - insufficient permissions
                    if (response.status === 403) {
                        throw new Error('You do not have permission to perform this action.');
                    }

                    // Handle 404 Not Found
                    if (response.status === 404) {
                        throw new Error('The requested resource was not found.');
                    }

                    // Handle 500 Server Error
                    if (response.status >= 500) {
                        if (attempt < this.retryAttempts - 1) {
                            await new Promise(resolve => setTimeout(resolve, 1000 * (attempt + 1)));
                            continue;
                        }
                    }

                    throw new Error(data.message || data.error || 'API request failed');
                }

                // Cache successful GET responses
                if (method === 'GET' && !options.skipCache) {
                    this.setCachedResponse(cacheKey, data);
                }

                // Invalidate cache on mutations
                if (method !== 'GET') {
                    this.invalidateCache(endpoint.split('/')[1]);
                }

                return data;
            } catch (error) {
                lastError = error;
                
                // Don't retry on client errors (4xx)
                if (error.name === 'AbortError') {
                    throw new Error('Request timeout. Please try again.');
                }
                
                if (error.message.includes('Session expired') || 
                    error.message.includes('permission') ||
                    error.message.includes('not found')) {
                    throw error;
                }

                // Retry on network errors or server errors
                if (attempt < this.retryAttempts - 1) {
                    await new Promise(resolve => setTimeout(resolve, 1000 * (attempt + 1)));
                    continue;
                }
            }
        }

        // All retries failed
        console.error('API Error after retries:', lastError);
        
        // User-friendly error messages
        if (lastError.message.includes('Failed to fetch') || lastError.message.includes('NetworkError')) {
            throw new Error('Network error. Please check your internet connection.');
        }
        
        throw lastError;
    }

    // Auth
    async login(email, password) {
        return this.request('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
    }

    // Kiosk
    async getKioskMenu(tenantId, branchId) {
        return this.request(`/kiosk/menu?tenant_id=${tenantId}&branch_id=${branchId}`, {
            method: 'GET'
        });
    }

    async createKioskOrder(tenantId, branchId, orderData) {
        return this.request(`/kiosk/orders?tenant_id=${tenantId}&branch_id=${branchId}`, {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
    }

    // Mobile
    async getMobileMenu() {
        return this.request('/mobile/menu', {
            method: 'GET'
        });
    }

    async getQuickOrder(productId) {
        return this.request(`/mobile/quick-order/${productId}`, {
            method: 'GET'
        });
    }

    // Orders
    async getOrders(params = {}) {
        // Merge screen size parameters with provided params
        const screenSizeParams = window.screenSizeDetector ?
            window.screenSizeDetector.getApiParams('orders') : {};
        const mergedParams = { ...screenSizeParams, ...params };
        const queryString = new URLSearchParams(mergedParams).toString();
        return this.request(`/orders${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getOrder(orderId) {
        return this.request(`/orders/${orderId}`, {
            method: 'GET'
        });
    }

    async createOrder(orderData) {
        return this.request('/orders', {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
    }

    async updateOrder(orderId, orderData) {
        return this.request(`/orders/${orderId}`, {
            method: 'PUT',
            body: JSON.stringify(orderData)
        });
    }

    // Tables
    async getTables(params = {}) {
        // Merge screen size parameters with provided params
        const screenSizeParams = window.screenSizeDetector ?
            window.screenSizeDetector.getApiParams('tables') : {};
        const mergedParams = { ...screenSizeParams, ...params };
        const queryString = new URLSearchParams(mergedParams).toString();
        return this.request(`/tables${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    // Products
    async getProducts(params = {}) {
        // Merge screen size parameters with provided params
        const screenSizeParams = window.screenSizeDetector ?
            window.screenSizeDetector.getApiParams('products') : {};
        const mergedParams = { ...screenSizeParams, ...params };
        const queryString = new URLSearchParams(mergedParams).toString();
        return this.request(`/products${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    // Categories
    async getCategories() {
        return this.request('/categories', {
            method: 'GET'
        });
    }

    // Offline Status
    async getOfflineStatus() {
        return this.request('/offline/status', {
            method: 'GET'
        });
    }

    // Quality
    async getFoodSafetyProtocols() {
        return this.request('/quality/food-safety-protocols', {
            method: 'GET'
        });
    }

    // Reservations
    async getReservations(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reservations${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getReservation(id) {
        return this.request(`/reservations/${id}`, {
            method: 'GET'
        });
    }

    async createReservation(reservationData) {
        return this.request('/reservations', {
            method: 'POST',
            body: JSON.stringify(reservationData)
        });
    }

    async updateReservation(id, reservationData) {
        return this.request(`/reservations/${id}`, {
            method: 'PUT',
            body: JSON.stringify(reservationData)
        });
    }

    async checkAvailability(date, time, guests) {
        return this.request('/reservations/check-availability', {
            method: 'POST',
            body: JSON.stringify({ date, time, guests })
        });
    }

    // Kitchen
    async getKitchenOrders() {
        return this.request('/kitchen/orders', {
            method: 'GET'
        });
    }

    async getPendingKitchenOrders() {
        return this.request('/kitchen/orders/pending', {
            method: 'GET'
        });
    }

    async getInProgressKitchenOrders() {
        return this.request('/kitchen/orders/in-progress', {
            method: 'GET'
        });
    }

    async getReadyKitchenOrders() {
        return this.request('/kitchen/orders/ready', {
            method: 'GET'
        });
    }

    async updateKitchenOrderStatus(id, status) {
        return this.request(`/kitchen/orders/${id}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status })
        });
    }

    // Inventory
    async getInventory(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/inventory${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getLowStockItems() {
        return this.request('/inventory/low-stock', {
            method: 'GET'
        });
    }

    async getSuppliers() {
        return this.request('/inventory/suppliers', {
            method: 'GET'
        });
    }

    async getStockAdjustments() {
        return this.request('/inventory/stock-adjustments', {
            method: 'GET'
        });
    }

    async getStockOpname() {
        return this.request('/inventory/stock-opname', {
            method: 'GET'
        });
    }

    async getPurchaseOrders() {
        return this.request('/inventory/purchase-orders', {
            method: 'GET'
        });
    }

    async getGoodsReceipts() {
        return this.request('/inventory/goods-receipts', {
            method: 'GET'
        });
    }

    // Loyalty
    async getLoyaltyPoints() {
        return this.request('/loyalty/points', {
            method: 'GET'
        });
    }

    async getLoyaltyRewards() {
        return this.request('/loyalty/rewards', {
            method: 'GET'
        });
    }

    async getCustomerLoyalty() {
        return this.request('/loyalty/customers', {
            method: 'GET'
        });
    }

    async awardLoyaltyPoints(userId, points, transactionType) {
        return this.request('/loyalty/points/award', {
            method: 'POST',
            body: JSON.stringify({ user_id: userId, points, transaction_type: transactionType })
        });
    }

    async redeemLoyaltyReward(rewardId, userId) {
        return this.request(`/loyalty/rewards/${rewardId}/redeem`, {
            method: 'POST',
            body: JSON.stringify({ user_id: userId })
        });
    }

    // Settings
    async getSettings(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/settings${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getSettingsByGroup(prefix) {
        return this.request(`/settings/group/${prefix}`, {
            method: 'GET'
        });
    }

    async updateSetting(id, value) {
        return this.request(`/settings/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ value })
        });
    }

    // Reports
    async getSalesReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/sales${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getTopProductsReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/top-products${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getInventoryReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/inventory${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getStockMovementReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/stock-movement${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getKitchenPerformanceReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/kitchen-performance${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getReservationsReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/reservations${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getFinancialReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/financial${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getDashboardReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/dashboard${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getProfitLossReport(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reports/profit-loss${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    // Users
    async getUsers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/users${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getUser(id) {
        return this.request(`/users/${id}`, {
            method: 'GET'
        });
    }

    async createUser(userData) {
        return this.request('/users', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    }

    async updateUser(id, userData) {
        return this.request(`/users/${id}`, {
            method: 'PUT',
            body: JSON.stringify(userData)
        });
    }

    async getUserPermissions(userId) {
        return this.request(`/users/${userId}/permissions`, {
            method: 'GET'
        });
    }

    async getUserRoles() {
        return this.request('/users/roles', {
            method: 'GET'
        });
    }

    // Customers
    async getCustomers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/crm/customers${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async createCustomer(customerData) {
        return this.request('/crm/customers', {
            method: 'POST',
            body: JSON.stringify(customerData)
        });
    }

    async updateCustomer(id, customerData) {
        return this.request(`/crm/customers/${id}`, {
            method: 'PUT',
            body: JSON.stringify(customerData)
        });
    }

    // Location
    async getNearbyBranches(latitude, longitude, radius) {
        return this.request('/location/nearby-branches', {
            method: 'POST',
            body: JSON.stringify({ latitude, longitude, radius })
        });
    }

    async detectBranch(latitude, longitude) {
        return this.request('/location/detect-branch', {
            method: 'POST',
            body: JSON.stringify({ latitude, longitude })
        });
    }

    // Delivery
    async getDeliveryOrders(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/delivery/orders${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    // Analytics
    async getDailySalesSummary(startDate, endDate) {
        return this.request('/analytics/daily-sales', {
            method: 'GET'
        });
    }

    async getHourlySalesSummary(date) {
        return this.request('/analytics/hourly-sales', {
            method: 'GET'
        });
    }

    async getTopSellingProducts(startDate, endDate, limit = 10) {
        return this.request('/analytics/top-products', {
            method: 'GET'
        });
    }

    async getCategoryPerformance(startDate, endDate) {
        return this.request('/analytics/category-performance', {
            method: 'GET'
        });
    }

    async getPaymentMethodBreakdown(startDate, endDate) {
        return this.request('/analytics/payment-breakdown', {
            method: 'GET'
        });
    }

    async getOrderTypeBreakdown(startDate, endDate) {
        return this.request('/analytics/order-type-breakdown', {
            method: 'GET'
        });
    }

    async getCustomerAnalytics(startDate, endDate) {
        return this.request('/analytics/customer-analytics', {
            method: 'GET'
        });
    }

    async getTablePerformance(startDate, endDate) {
        return this.request('/analytics/table-performance', {
            method: 'GET'
        });
    }

    async getStaffPerformance(startDate, endDate) {
        return this.request('/analytics/staff-performance', {
            method: 'GET'
        });
    }

    async getRevenueTrends(months = 12) {
        return this.request('/analytics/revenue-trends', {
            method: 'GET'
        });
    }

    async getComparisonWithPrevious(startDate, endDate) {
        return this.request('/analytics/comparison', {
            method: 'GET'
        });
    }

    // Consumer
    async getConsumers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/consumer${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getConsumerById(consumerId) {
        return this.request(`/consumer/${consumerId}`, {
            method: 'GET'
        });
    }

    async searchConsumers(query) {
        return this.request(`/consumer/search?q=${query}`, {
            method: 'GET'
        });
    }

    async getConsumerOrders(consumerId, limit = 50) {
        return this.request(`/consumer/${consumerId}/orders?limit=${limit}`, {
            method: 'GET'
        });
    }

    async getConsumerLoyaltyPoints(consumerId) {
        return this.request(`/consumer/${consumerId}/loyalty`, {
            method: 'GET'
        });
    }

    async getTopConsumers(limit = 10, startDate = null, endDate = null) {
        const params = { limit };
        if (startDate) params.start_date = startDate;
        if (endDate) params.end_date = endDate;
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/consumer/top${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    // Customer Analytics
    async getCustomerBehavior(customerId, startDate, endDate) {
        return this.request(`/customer-analytics/${customerId}/behavior`, {
            method: 'GET'
        });
    }

    async getCohortAnalysis(startDate, endDate) {
        return this.request('/customer-analytics/cohort', {
            method: 'GET'
        });
    }

    async getCustomerJourney(customerId) {
        return this.request(`/customer-analytics/${customerId}/journey`, {
            method: 'GET'
        });
    }

    async getCustomerSegment(customerId) {
        return this.request(`/customer-analytics/${customerId}/segment`, {
            method: 'GET'
        });
    }

    async getCustomerLifetimeValue(customerId) {
        return this.request(`/customer-analytics/${customerId}/lifetime-value`, {
            method: 'GET'
        });
    }

    async getRetentionRate(startDate, endDate) {
        return this.request('/customer-analytics/retention', {
            method: 'GET'
        });
    }

    async getChurnAnalysis(daysInactive = 90) {
        return this.request(`/customer-analytics/churn?days=${daysInactive}`, {
            method: 'GET'
        });
    }

    async getPreferenceAnalytics(customerId) {
        return this.request(`/customer-analytics/${customerId}/preferences`, {
            method: 'GET'
        });
    }

    async getPeakHours(customerId) {
        return this.request(`/customer-analytics/${customerId}/peak-hours`, {
            method: 'GET'
        });
    }

    // Feedback
    async getFeedback(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/feedback${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getFeedbackById(feedbackId) {
        return this.request(`/feedback/${feedbackId}`, {
            method: 'GET'
        });
    }

    async createFeedback(feedbackData) {
        return this.request('/feedback', {
            method: 'POST',
            body: JSON.stringify(feedbackData)
        });
    }

    async updateFeedbackStatus(feedbackId, status) {
        return this.request(`/feedback/${feedbackId}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status })
        });
    }

    async getFeedbackSummary() {
        return this.request('/feedback/summary', {
            method: 'GET'
        });
    }

    // Reconciliation
    async getReconciliationTransactions(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/reconciliation${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getReconciliationById(transactionId) {
        return this.request(`/reconciliation/${transactionId}`, {
            method: 'GET'
        });
    }

    async getDiscrepancies() {
        return this.request('/reconciliation/discrepancies', {
            method: 'GET'
        });
    }

    async getReconciliationSummary() {
        return this.request('/reconciliation/summary', {
            method: 'GET'
        });
    }

    async getReconciliationSources() {
        return this.request('/reconciliation/sources', {
            method: 'GET'
        });
    }

    async getReconciliationRules() {
        return this.request('/reconciliation/rules', {
            method: 'GET'
        });
    }

    // Franchise
    async getFranchisees(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/franchise${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getFranchiseeById(franchiseeId) {
        return this.request(`/franchise/${franchiseeId}`, {
            method: 'GET'
        });
    }

    async createFranchisee(franchiseeData) {
        return this.request('/franchise', {
            method: 'POST',
            body: JSON.stringify(franchiseeData)
        });
    }

    async getFranchiseAgreements(franchiseeId) {
        return this.request(`/franchise/${franchiseeId}/agreements`, {
            method: 'GET'
        });
    }

    async getFranchisePerformance(franchiseeId) {
        return this.request(`/franchise/${franchiseeId}/performance`, {
            method: 'GET'
        });
    }

    async getFranchiseRoyalties(franchiseeId) {
        return this.request(`/franchise/${franchiseeId}/royalties`, {
            method: 'GET'
        });
    }

    // Ghost Kitchen
    async getVirtualBrands(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/ghost-kitchen${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getVirtualBrandById(brandId) {
        return this.request(`/ghost-kitchen/${brandId}`, {
            method: 'GET'
        });
    }

    async createVirtualBrand(brandData) {
        return this.request('/ghost-kitchen', {
            method: 'POST',
            body: JSON.stringify(brandData)
        });
    }

    async getBrandMenuItems(brandId) {
        return this.request(`/ghost-kitchen/${brandId}/menu`, {
            method: 'GET'
        });
    }

    async getDeliveryPlatforms() {
        return this.request('/ghost-kitchen/platforms', {
            method: 'GET'
        });
    }

    async getBrandDeliveryPlatforms(brandId) {
        return this.request(`/ghost-kitchen/${brandId}/platforms`, {
            method: 'GET'
        });
    }

    // Innovation
    async getInnovationProjects(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/innovation${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getInnovationProjectById(projectId) {
        return this.request(`/innovation/${projectId}`, {
            method: 'GET'
        });
    }

    async createInnovationProject(projectData) {
        return this.request('/innovation', {
            method: 'POST',
            body: JSON.stringify(projectData)
        });
    }

    async getInnovationIdeas() {
        return this.request('/innovation/ideas', {
            method: 'GET'
        });
    }

    async getInnovationMetrics(projectId) {
        return this.request(`/innovation/${projectId}/metrics`, {
            method: 'GET'
        });
    }

    // Integration Hub
    async getExternalIntegrations(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/integration-hub${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getExternalIntegrationById(integrationId) {
        return this.request(`/integration-hub/${integrationId}`, {
            method: 'GET'
        });
    }

    async createExternalIntegration(integrationData) {
        return this.request('/integration-hub', {
            method: 'POST',
            body: JSON.stringify(integrationData)
        });
    }

    async getIntegrationMappings(integrationId) {
        return this.request(`/integration-hub/${integrationId}/mappings`, {
            method: 'GET'
        });
    }

    async getIntegrationSyncLogs(integrationId) {
        return this.request(`/integration-hub/${integrationId}/sync-logs`, {
            method: 'GET'
        });
    }

    // AI - Demand Forecasting
    async generateSalesForecast(days = 7) {
        return this.request('/ai/sales-forecast', {
            method: 'POST',
            body: JSON.stringify({ days })
        });
    }

    async getSalesForecast() {
        return this.request('/ai/sales-forecast', {
            method: 'GET'
        });
    }

    async generateInventoryPrediction(inventoryId) {
        return this.request('/ai/inventory-prediction', {
            method: 'POST',
            body: JSON.stringify({ inventory_id: inventoryId })
        });
    }

    async getInventoryPredictions() {
        return this.request('/ai/inventory-predictions', {
            method: 'GET'
        });
    }

    // AI - Customer Intelligence
    async getCustomerSegmentation() {
        return this.request('/ai/customer-segmentation', {
            method: 'GET'
        });
    }

    async getCustomerLifetimeValue(customerId) {
        return this.request(`/ai/customer-ltv/${customerId}`, {
            method: 'GET'
        });
    }

    async getChurnPrediction() {
        return this.request('/ai/churn-prediction', {
            method: 'GET'
        });
    }

    // AI - Menu Optimization
    async getMenuOptimization() {
        return this.request('/ai/menu-optimization', {
            method: 'GET'
        });
    }

    async getDynamicPricing(productId) {
        return this.request(`/ai/dynamic-pricing/${productId}`, {
            method: 'GET'
        });
    }

    async updateDynamicPricing(productId, priceData) {
        return this.request(`/ai/dynamic-pricing/${productId}`, {
            method: 'POST',
            body: JSON.stringify(priceData)
        });
    }

    // AI - Kitchen Intelligence
    async getKitchenEfficiency() {
        return this.request('/ai/kitchen-efficiency', {
            method: 'GET'
        });
    }

    async getPreparationTimePrediction(orderId) {
        return this.request(`/ai/prep-time/${orderId}`, {
            method: 'GET'
        });
    }

    // AI - Waste Reduction
    async getWastePrediction() {
        return this.request('/ai/waste-prediction', {
            method: 'GET'
        });
    }

    async getWasteReductionRecommendations() {
        return this.request('/ai/waste-recommendations', {
            method: 'GET'
        });
    }

    // AI - Smart Procurement
    async getProcurementRecommendations() {
        return this.request('/ai/procurement-recommendations', {
            method: 'GET'
        });
    }

    async getSupplierPerformance() {
        return this.request('/ai/supplier-performance', {
            method: 'GET'
        });
    }

    // AI - Advanced Analytics
    async getAdvancedInsights() {
        return this.request('/ai/advanced-insights', {
            method: 'GET'
        });
    }

    async getPredictiveAnalytics() {
        return this.request('/ai/predictive-analytics', {
            method: 'GET'
        });
    }
}

// Initialize global API client
window.apiClient = new APIClient();
