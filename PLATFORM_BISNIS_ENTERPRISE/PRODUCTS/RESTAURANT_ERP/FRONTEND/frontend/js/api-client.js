/**
 * EBP Restaurant API Client
 * Handles all API communication with the backend
 */
class APIClient {
    constructor() {
        this.baseURL = '/api/v1';
        this.token = localStorage.getItem('authToken') || null;
        this.tenantId = localStorage.getItem('tenantId') || null;
        this.branchId = localStorage.getItem('branchId') || null;
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
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        const config = {
            ...options,
            headers
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
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
        const queryString = new URLSearchParams(params).toString();
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
    async getTables() {
        return this.request('/tables', {
            method: 'GET'
        });
    }

    // Products
    async getProducts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
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
}

// Initialize global API client
window.apiClient = new APIClient();
