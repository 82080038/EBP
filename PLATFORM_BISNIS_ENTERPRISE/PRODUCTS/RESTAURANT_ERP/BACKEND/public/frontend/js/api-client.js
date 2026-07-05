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
}

// Initialize global API client
window.apiClient = new APIClient();
