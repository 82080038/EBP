/**
 * EBP Restaurant Admin Dashboard
 * Admin dashboard functionality with role-based navigation
 */
class Dashboard {
    constructor() {
        this.currentPage = 'overview';
        this.user = null;
        this.data = {
            orders: [],
            menu: [],
            tables: [],
            inventory: [],
            customers: [],
            reservations: [],
            kitchenOrders: []
        };
        this.init();
    }

    async init() {
        // Check authentication first
        if (!this.isAuthenticated()) {
            this.redirectToLogin();
            return;
        }

        this.loadUserInfo();
        this.bindEvents();

        // Load permissions from backend if user is authenticated
        if (this.user && this.user.id && localStorage.getItem('authToken')) {
            try {
                await loadPermissionsFromBackend(this.user.id);
                console.log('Dynamic permissions loaded from backend');
            } catch (error) {
                console.warn('Failed to load dynamic permissions, using fallback:', error);
            }
        }

        this.initializeRoleBasedUI();

        // Only load initial data if user is authenticated
        if (this.user && localStorage.getItem('authToken')) {
            this.loadInitialData();
        }
    }

    isAuthenticated() {
        const token = localStorage.getItem('authToken');
        const user = localStorage.getItem('ebp_user');
        return !!token && !!user;
    }

    redirectToLogin() {
        const currentPath = window.location.pathname;
        window.location.href = '/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/FRONTEND/login.html';
    }

    loadUserInfo() {
        // Get user info from localStorage or API
        const userStr = localStorage.getItem('ebp_user');
        if (userStr) {
            this.user = JSON.parse(userStr);
        }
    }

    initializeRoleBasedUI() {
        if (this.user && typeof initializeRoleBasedUI === 'function') {
            initializeRoleBasedUI(this.user);
            this.renderNavigationByRole();
        }
    }

    renderNavigationByRole() {
        if (!this.user) return;

        const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
        const accessibleTabs = getMenuForUser(this.user);

        navItems.forEach(item => {
            const page = item.dataset.page;
            if (!accessibleTabs.includes(page)) {
                item.style.display = 'none';
            }
        });
    }

    bindEvents() {
        // Sidebar navigation
        document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.currentTarget.dataset.page;
                this.navigateTo(page);
            });
        });

        // Menu toggle
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', () => {
            this.logout();
        });

        // Search
        document.getElementById('searchInput').addEventListener('input', (e) => {
            this.handleSearch(e.target.value);
        });

        // Report cards
        document.querySelectorAll('.report-card').forEach(card => {
            card.addEventListener('click', () => {
                const reportType = card.dataset.report;
                this.generateReport(reportType);
            });
        });

        // Save settings
        document.getElementById('saveSettingsBtn').addEventListener('click', () => {
            this.saveSettings();
        });
    }

    async loadInitialData() {
        await Promise.all([
            this.loadDashboardStats(),
            this.loadRecentOrders(),
            this.loadKitchenStatus(),
            this.loadLowStockItems()
        ]);
    }

    async loadDashboardStats() {
        try {
            const response = await window.apiClient.getDashboardReport();
            if (response && response.success) {
                const stats = response.data;
                document.getElementById('todayRevenue').textContent = this.formatPrice(stats.today_revenue || 0);
                document.getElementById('todayOrders').textContent = stats.today_orders || 0;
                document.getElementById('activeCustomers').textContent = stats.active_customers || 0;
                document.getElementById('tableOccupancy').textContent = `${stats.table_occupancy || 0}%`;
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    async loadRecentOrders() {
        try {
            const response = await window.apiClient.getOrders({ limit: 5, sort: 'created_at', order: 'DESC' });
            if (response && response.success) {
                this.data.orders = response.data || [];
                this.renderRecentOrders();
            }
        } catch (error) {
            console.error('Error loading recent orders:', error);
        }
    }

    renderRecentOrders() {
        const container = document.getElementById('recentOrders');
        container.innerHTML = '';

        if (this.data.orders.length === 0) {
            container.innerHTML = '<p class="empty-message">No recent orders</p>';
            return;
        }

        this.data.orders.slice(0, 5).forEach(order => {
            const item = document.createElement('div');
            item.className = 'order-item';
            item.innerHTML = `
                <div class="order-info">
                    <span class="order-number">${order.order_number || 'N/A'}</span>
                    <span class="order-table">Table ${order.table_id || 'N/A'}</span>
                </div>
                <div class="order-details">
                    <span class="order-status ${order.status?.toLowerCase()}">${order.status || 'Unknown'}</span>
                    <span class="order-total">Rp ${this.formatPrice(order.total_amount || 0)}</span>
                </div>
            `;
            container.appendChild(item);
        });
    }

    async loadKitchenStatus() {
        try {
            const [pending, preparing, ready] = await Promise.all([
                window.apiClient.getPendingKitchenOrders(),
                window.apiClient.getInProgressKitchenOrders(),
                window.apiClient.getReadyKitchenOrders()
            ]);

            document.getElementById('pendingOrders').textContent = pending?.data?.length || 0;
            document.getElementById('preparingOrders').textContent = preparing?.data?.length || 0;
            document.getElementById('readyOrders').textContent = ready?.data?.length || 0;
        } catch (error) {
            console.error('Error loading kitchen status:', error);
        }
    }

    async loadLowStockItems() {
        try {
            const response = await window.apiClient.getLowStockItems();
            if (response && response.success) {
                this.data.inventory = response.data || [];
                this.renderLowStockItems();
            }
        } catch (error) {
            console.error('Error loading low stock items:', error);
        }
    }

    renderLowStockItems() {
        const container = document.getElementById('lowStockItems');
        container.innerHTML = '';

        if (this.data.inventory.length === 0) {
            container.innerHTML = '<p class="empty-message">No low stock items</p>';
            return;
        }

        this.data.inventory.slice(0, 5).forEach(item => {
            const div = document.createElement('div');
            div.className = 'low-stock-item';
            div.innerHTML = `
                <span class="item-name">${item.name || 'Unknown'}</span>
                <span class="item-stock">${item.stock || 0} ${item.unit || ''}</span>
            `;
            container.appendChild(div);
        });
    }

    navigateTo(page) {
        this.currentPage = page;

        // Update sidebar
        document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.page === page) {
                item.classList.add('active');
            }
        });

        // Update page title
        const titles = {
            overview: 'Overview',
            orders: 'Orders',
            menu: 'Menu',
            tables: 'Tables',
            inventory: 'Inventory',
            kitchen: 'Kitchen',
            reservations: 'Reservations',
            customers: 'Customers',
            reports: 'Reports',
            settings: 'Settings'
        };
        document.getElementById('pageTitle').textContent = titles[page] || 'Dashboard';

        // Show/hide pages
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        const targetPage = document.getElementById(`${page}Page`);
        if (targetPage) {
            targetPage.classList.add('active');
            this.loadPageData(page);
        }
    }

    async loadPageData(page) {
        switch (page) {
            case 'orders':
                await this.loadOrdersPage();
                break;
            case 'menu':
                await this.loadMenuPage();
                break;
            case 'tables':
                await this.loadTablesPage();
                break;
            case 'inventory':
                await this.loadInventoryPage();
                break;
            case 'kitchen':
                await this.loadKitchenPage();
                break;
            case 'reservations':
                await this.loadReservationsPage();
                break;
            case 'customers':
                await this.loadCustomersPage();
                break;
        }
    }

    async loadOrdersPage() {
        try {
            const response = await window.apiClient.getOrders();
            if (response && response.success) {
                this.data.orders = response.data || [];
                this.renderOrdersTable();
            }
        } catch (error) {
            console.error('Error loading orders:', error);
        }
    }

    renderOrdersTable() {
        const tbody = document.getElementById('ordersTableBody');
        tbody.innerHTML = '';

        if (this.data.orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">No orders found</td></tr>';
            return;
        }

        this.data.orders.forEach(order => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${order.order_number || 'N/A'}</td>
                <td>${order.table_id || 'N/A'}</td>
                <td><span class="status-badge ${order.status?.toLowerCase()}">${order.status || 'Unknown'}</span></td>
                <td>Rp ${this.formatPrice(order.total_amount || 0)}</td>
                <td>${this.formatTime(order.created_at)}</td>
                <td>
                    <button class="btn btn-sm btn-view" data-id="${order.order_id}">View</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    async loadMenuPage() {
        try {
            const [categories, products] = await Promise.all([
                window.apiClient.getCategories(),
                window.apiClient.getProducts()
            ]);

            if (categories?.success) {
                this.renderCategories(categories.data || []);
            }
            if (products?.success) {
                this.data.menu = products.data || [];
                this.renderProducts();
            }
        } catch (error) {
            console.error('Error loading menu:', error);
        }
    }

    renderCategories(categories) {
        const container = document.getElementById('categoryList');
        container.innerHTML = '';

        categories.forEach(cat => {
            const div = document.createElement('div');
            div.className = 'category-item';
            div.innerHTML = `
                <h4>${cat.name || 'Unknown'}</h4>
                <p>${cat.description || ''}</p>
            `;
            container.appendChild(div);
        });
    }

    renderProducts() {
        const container = document.getElementById('productList');
        container.innerHTML = '';

        this.data.menu.forEach(product => {
            const div = document.createElement('div');
            div.className = 'product-item';
            div.innerHTML = `
                <img src="${product.image_url || 'https://via.placeholder.com/80'}" alt="${product.name}">
                <div class="product-info">
                    <h4>${product.name || 'Unknown'}</h4>
                    <p>Rp ${this.formatPrice(product.price || 0)}</p>
                </div>
            `;
            container.appendChild(div);
        });
    }

    async loadTablesPage() {
        try {
            const response = await window.apiClient.getTables();
            if (response && response.success) {
                this.data.tables = response.data || [];
                this.renderTablesGrid();
            }
        } catch (error) {
            console.error('Error loading tables:', error);
        }
    }

    renderTablesGrid() {
        const container = document.getElementById('tablesGrid');
        container.innerHTML = '';

        this.data.tables.forEach(table => {
            const div = document.createElement('div');
            div.className = `table-card ${table.status?.toLowerCase() || 'available'}`;
            div.innerHTML = `
                <p class="table-number">${table.table_number || table.name || 'N/A'}</p>
                <p class="table-status">${table.status || 'Available'}</p>
                <p class="table-capacity">Capacity: ${table.capacity || 4}</p>
            `;
            container.appendChild(div);
        });
    }

    async loadInventoryPage() {
        try {
            const response = await window.apiClient.getInventory();
            if (response && response.success) {
                this.data.inventory = response.data || [];
                this.renderInventoryTable();
            }
        } catch (error) {
            console.error('Error loading inventory:', error);
        }
    }

    renderInventoryTable() {
        const tbody = document.getElementById('inventoryTableBody');
        tbody.innerHTML = '';

        this.data.inventory.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.name || 'Unknown'}</td>
                <td>${item.category || 'N/A'}</td>
                <td>${item.stock || 0}</td>
                <td>${item.unit || 'pcs'}</td>
                <td><span class="status-badge ${item.stock < item.min_stock ? 'low' : 'ok'}">${item.stock < item.min_stock ? 'Low' : 'OK'}</span></td>
                <td>
                    <button class="btn btn-sm btn-adjust" data-id="${item.id}">Adjust</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    async loadKitchenPage() {
        try {
            const response = await window.apiClient.getKitchenOrders();
            if (response && response.success) {
                this.data.kitchenOrders = response.data || [];
                this.renderKitchenOrders();
            }
        } catch (error) {
            console.error('Error loading kitchen orders:', error);
        }
    }

    renderKitchenOrders() {
        const container = document.getElementById('kitchenOrdersGrid');
        container.innerHTML = '';

        this.data.kitchenOrders.forEach(order => {
            const div = document.createElement('div');
            div.className = `kitchen-order-card ${order.status?.toLowerCase()}`;
            div.innerHTML = `
                <div class="kitchen-order-header">
                    <span class="order-number">${order.order_number || 'N/A'}</span>
                    <span class="order-status">${order.status || 'Unknown'}</span>
                </div>
                <div class="kitchen-order-items">
                    ${order.items?.map(item => `<p>${item.quantity}x ${item.name}</p>`).join('') || '<p>No items</p>'}
                </div>
                <div class="kitchen-order-actions">
                    <button class="btn btn-sm btn-update" data-id="${order.id}">Update Status</button>
                </div>
            `;
            container.appendChild(div);
        });
    }

    async loadReservationsPage() {
        try {
            const response = await window.apiClient.getReservations();
            if (response && response.success) {
                this.data.reservations = response.data || [];
                this.renderReservations();
            }
        } catch (error) {
            console.error('Error loading reservations:', error);
        }
    }

    renderReservations() {
        const container = document.getElementById('reservationsList');
        container.innerHTML = '';

        this.data.reservations.forEach(reservation => {
            const div = document.createElement('div');
            div.className = 'reservation-card';
            div.innerHTML = `
                <div class="reservation-info">
                    <h4>${reservation.customer_name || 'Unknown'}</h4>
                    <p>${reservation.date || 'N/A'} at ${reservation.time || 'N/A'}</p>
                    <p>Guests: ${reservation.guests || 0}</p>
                </div>
                <div class="reservation-status">
                    <span class="status-badge ${reservation.status?.toLowerCase()}">${reservation.status || 'Unknown'}</span>
                </div>
            `;
            container.appendChild(div);
        });
    }

    async loadCustomersPage() {
        try {
            const response = await window.apiClient.getCustomers();
            if (response && response.success) {
                this.data.customers = response.data || [];
                this.renderCustomersTable();
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    renderCustomersTable() {
        const tbody = document.getElementById('customersTableBody');
        tbody.innerHTML = '';

        this.data.customers.forEach(customer => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${customer.name || 'Unknown'}</td>
                <td>${customer.email || 'N/A'}</td>
                <td>${customer.phone || 'N/A'}</td>
                <td>${customer.loyalty_points || 0}</td>
                <td>${customer.total_visits || 0}</td>
                <td>
                    <button class="btn btn-sm btn-view" data-id="${customer.id}">View</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    async generateReport(reportType) {
        try {
            let response;
            switch (reportType) {
                case 'sales':
                    response = await window.apiClient.getSalesReport();
                    break;
                case 'inventory':
                    response = await window.apiClient.getInventoryReport();
                    break;
                case 'kitchen':
                    response = await window.apiClient.getKitchenPerformanceReport();
                    break;
                case 'financial':
                    response = await window.apiClient.getFinancialReport();
                    break;
            }

            if (response && response.success) {
                console.log(`${reportType} report:`, response.data);
                alert(`${reportType.charAt(0).toUpperCase() + reportType.slice(1)} report generated successfully!`);
            }
        } catch (error) {
            console.error(`Error generating ${reportType} report:`, error);
            alert(`Failed to generate ${reportType} report`);
        }
    }

    async saveSettings() {
        const settings = {
            restaurant_name: document.getElementById('restaurantName').value,
            currency: document.getElementById('currency').value,
            email_notifications: document.getElementById('emailNotifications').checked,
            sms_notifications: document.getElementById('smsNotifications').checked
        };

        try {
            const response = await window.apiClient.updateSetting('general', settings);
            if (response && response.success) {
                alert('Settings saved successfully!');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            alert('Failed to save settings');
        }
    }

    handleSearch(query) {
        console.log('Search:', query);
        // Implement search functionality
    }

    logout() {
        window.apiClient.clearAuth();
        window.location.href = '/frontend/landing.html';
    }

    formatPrice(price) {
        return new Intl.NumberFormat('id-ID').format(price);
    }

    formatTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new Dashboard();
});
