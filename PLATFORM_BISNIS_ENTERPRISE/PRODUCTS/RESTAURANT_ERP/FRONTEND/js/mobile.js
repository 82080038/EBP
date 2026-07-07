/**
 * EBP Restaurant Mobile Waiter App
 * Mobile waiter application functionality
 */
class MobileApp {
    constructor() {
        this.currentPage = 'orders';
        this.menu = [];
        this.orders = [];
        this.tables = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialData();
        this.bindScreenSizeChange();
    }

    bindEvents() {
        // Sidebar
        document.getElementById('menuBtn').addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('active');
        });

        document.getElementById('closeSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.remove('active');
        });

        // Sidebar menu items
        document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const page = e.currentTarget.dataset.page;
                if (page) {
                    this.navigateTo(page);
                    document.getElementById('sidebar').classList.remove('active');
                }
            });
        });

        // Bottom navigation
        document.querySelectorAll('.bottom-nav .nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.currentTarget.dataset.page;
                this.navigateTo(page);
            });
        });

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            this.logout();
        });

        // New order button
        document.getElementById('newOrderBtn').addEventListener('click', () => {
            this.createNewOrder();
        });

        // Menu search
        document.getElementById('menuSearch').addEventListener('input', (e) => {
            this.searchMenu(e.target.value);
        });

        // Category tabs
        document.getElementById('categoryTabs').addEventListener('click', (e) => {
            if (e.target.classList.contains('tab-btn')) {
                this.filterMenuByCategory(e.target.dataset.category);
                this.updateActiveTab(e.target);
            }
        });

        // Quick order modal
        document.getElementById('closeQuickOrder').addEventListener('click', () => {
            this.closeModal('quickOrderModal');
        });

        document.getElementById('quickQtyMinus').addEventListener('click', () => {
            this.adjustQuickQuantity(-1);
        });

        document.getElementById('quickQtyPlus').addEventListener('click', () => {
            this.adjustQuickQuantity(1);
        });

        document.getElementById('quickAddBtn').addEventListener('click', () => {
            this.quickAddToOrder();
        });

        // Order details modal
        document.getElementById('closeOrderDetails').addEventListener('click', () => {
            this.closeModal('orderDetailsModal');
        });

        // Close modals on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal.id);
                }
            });
        });

        // Listen for offline status changes
        document.addEventListener('offlineStatusChanged', (e) => {
            this.handleOfflineStatusChange(e.detail.status);
        });
    }

    async loadInitialData() {
        await this.loadOrders();
        await this.loadMenu();
        await this.loadTables();
        this.updateUserInfo();
    }

    async loadOrders() {
        try {
            const response = await window.apiClient.getOrders({ status: 'PENDING,PREPARING,READY' });
            if (response && response.success) {
                this.orders = response.data || [];
                this.renderOrders();
            } else {
                this.loadMockOrders();
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            this.loadMockOrders();
        }
    }

    loadMockOrders() {
        this.orders = [
            { order_id: 1, order_number: 'ORD-001', table_id: 1, status: 'PREPARING', total_amount: 75000 },
            { order_id: 2, order_number: 'ORD-002', table_id: 2, status: 'READY', total_amount: 50000 },
            { order_id: 3, order_number: 'ORD-003', table_id: 3, status: 'PENDING', total_amount: 120000 }
        ];
        this.renderOrders();
    }

    renderOrders() {
        const container = document.getElementById('ordersList');
        container.innerHTML = '';

        if (this.orders.length === 0) {
            container.innerHTML = '<p class="empty-message">No active orders</p>';
            return;
        }

        this.orders.forEach(order => {
            const card = document.createElement('div');
            card.className = 'order-card';
            card.innerHTML = `
                <div class="order-card-header">
                    <span class="order-number">${order.order_number}</span>
                    <span class="order-status ${order.status}">${order.status}</span>
                </div>
                <div class="order-info">
                    <span>Table ${order.table_id || 'N/A'}</span>
                    <span class="order-total">Rp ${this.formatPrice(order.total_amount)}</span>
                </div>
            `;
            card.addEventListener('click', () => this.showOrderDetails(order));
            container.appendChild(card);
        });
    }

    async loadMenu() {
        try {
            const response = await window.apiClient.getProducts();
            if (response && response.success) {
                this.menu = response.data || [];
                this.renderMenu();
            } else {
                this.loadMockMenu();
            }
        } catch (error) {
            console.error('Error loading menu:', error);
            this.loadMockMenu();
        }
    }

    loadMockMenu() {
        this.menu = [
            { product_id: 1, product_name: 'Nasi Goreng', price: 25000, description: 'Fried rice with vegetables', category_name: 'Main Course', image_url: 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=200&h=150&fit=crop' },
            { product_id: 2, product_name: 'Mie Goreng', price: 22000, description: 'Fried noodles', category_name: 'Main Course', image_url: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=200&h=150&fit=crop' },
            { product_id: 3, product_name: 'Es Teh Manis', price: 5000, description: 'Sweet iced tea', category_name: 'Beverages', image_url: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=200&h=150&fit=crop' },
            { product_id: 4, product_name: 'Jus Jeruk', price: 12000, description: 'Fresh orange juice', category_name: 'Beverages', image_url: 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=200&h=150&fit=crop' }
        ];
        this.renderMenu();
    }

    renderMenu() {
        const container = document.getElementById('menuList');
        container.innerHTML = '';

        this.menu.forEach(product => {
            const item = document.createElement('div');
            item.className = 'menu-item';
            item.innerHTML = `
                <img src="${product.image_url || 'https://via.placeholder.com/80x80?text=Product'}" alt="${product.product_name}" class="menu-item-image">
                <div class="menu-item-details">
                    <h3 class="menu-item-name">${product.product_name}</h3>
                    <p class="menu-item-price">Rp ${this.formatPrice(product.price)}</p>
                </div>
            `;
            item.addEventListener('click', () => this.showQuickOrder(product));
            container.appendChild(item);
        });

        // Render category tabs
        this.renderCategoryTabs();
    }

    renderCategoryTabs() {
        const categories = [...new Set(this.menu.map(p => p.category_name).filter(c => c))];
        const container = document.getElementById('categoryTabs');
        container.innerHTML = '<button class="tab-btn active" data-category="all">All</button>';

        categories.forEach(cat => {
            const btn = document.createElement('button');
            btn.className = 'tab-btn';
            btn.dataset.category = cat;
            btn.textContent = cat;
            container.appendChild(btn);
        });
    }

    async loadTables() {
        try {
            const response = await window.apiClient.getTables();
            if (response && response.success) {
                this.tables = response.data || [];
                this.renderTables();
            } else {
                this.loadMockTables();
            }
        } catch (error) {
            console.error('Error loading tables:', error);
            this.loadMockTables();
        }
    }

    loadMockTables() {
        this.tables = [
            { table_id: 1, table_number: 'T1', status: 'occupied' },
            { table_id: 2, table_number: 'T2', status: 'available' },
            { table_id: 3, table_number: 'T3', status: 'occupied' },
            { table_id: 4, table_number: 'T4', status: 'available' },
            { table_id: 5, table_number: 'T5', status: 'available' },
            { table_id: 6, table_number: 'T6', status: 'occupied' }
        ];
        this.renderTables();
    }

    renderTables() {
        const container = document.getElementById('tablesGrid');
        container.innerHTML = '';

        this.tables.forEach(table => {
            const card = document.createElement('div');
            card.className = `table-card ${table.status}`;
            card.innerHTML = `
                <p class="table-number">${table.table_number}</p>
                <p class="table-status">${table.status}</p>
            `;
            container.appendChild(card);
        });
    }

    updateUserInfo() {
        const userName = 'Waiter John';
        const userEmail = 'john@ebp.com';

        document.getElementById('userName').textContent = userName;
        document.getElementById('profileName').textContent = userName;
        document.getElementById('profileEmail').textContent = userEmail;

        // Update stats
        document.getElementById('todayOrders').textContent = this.orders.length;
        const totalRevenue = this.orders.reduce((sum, o) => sum + (o.total_amount || 0), 0);
        document.getElementById('totalRevenue').textContent = `Rp ${this.formatPrice(totalRevenue)}`;
    }

    navigateTo(page) {
        this.currentPage = page;

        // Update sidebar menu
        document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.page === page) {
                item.classList.add('active');
            }
        });

        // Update bottom nav
        document.querySelectorAll('.bottom-nav .nav-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.page === page) {
                item.classList.add('active');
            }
        });

        // Update pages
        document.querySelectorAll('.page').forEach(p => {
            p.classList.remove('active');
        });
        document.getElementById(`${page}Page`).classList.add('active');
    }

    searchMenu(query) {
        const filtered = this.menu.filter(p =>
            p.product_name.toLowerCase().includes(query.toLowerCase())
        );
        this.renderFilteredMenu(filtered);
    }

    filterMenuByCategory(category) {
        if (category === 'all') {
            this.renderMenu();
            return;
        }

        const filtered = this.menu.filter(p => p.category_name === category);
        this.renderFilteredMenu(filtered);
    }

    renderFilteredMenu(products) {
        const container = document.getElementById('menuList');
        container.innerHTML = '';

        products.forEach(product => {
            const item = document.createElement('div');
            item.className = 'menu-item';
            item.innerHTML = `
                <img src="https://via.placeholder.com/80x80?text=Product" alt="${product.product_name}" class="menu-item-image">
                <div class="menu-item-details">
                    <h3 class="menu-item-name">${product.product_name}</h3>
                    <p class="menu-item-price">Rp ${this.formatPrice(product.price)}</p>
                </div>
            `;
            item.addEventListener('click', () => this.showQuickOrder(product));
            container.appendChild(item);
        });
    }

    updateActiveTab(activeBtn) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        activeBtn.classList.add('active');
    }

    showQuickOrder(product) {
        document.getElementById('quickOrderName').textContent = product.product_name;
        document.getElementById('quickOrderPrice').textContent = `Rp ${this.formatPrice(product.price)}`;
        document.getElementById('quickOrderDescription').textContent = product.description || 'No description';
        document.getElementById('quickOrderImage').src = 'https://via.placeholder.com/400x200?text=Product';
        document.getElementById('quickQtyInput').value = 1;
        this.currentQuickProduct = product;
        this.openModal('quickOrderModal');
    }

    adjustQuickQuantity(delta) {
        const input = document.getElementById('quickQtyInput');
        let value = parseInt(input.value) + delta;
        if (value < 1) value = 1;
        if (value > 99) value = 99;
        input.value = value;
    }

    quickAddToOrder() {
        const quantity = parseInt(document.getElementById('quickQtyInput').value);
        // In a real app, this would add to a current order
        alert(`Added ${quantity}x ${this.currentQuickProduct.product_name} to order`);
        this.closeModal('quickOrderModal');
    }

    showOrderDetails(order) {
        document.getElementById('detailOrderNumber').textContent = order.order_number;
        document.getElementById('detailTable').textContent = order.table_id || 'N/A';
        document.getElementById('detailStatus').textContent = order.status;
        document.getElementById('detailTotal').textContent = `Rp ${this.formatPrice(order.total_amount)}`;

        const itemsContainer = document.getElementById('detailOrderItems');
        itemsContainer.innerHTML = '<p class="empty-message">Order items would be loaded here</p>';

        this.openModal('orderDetailsModal');
    }

    createNewOrder() {
        alert('Create new order functionality - would open order creation form');
    }

    logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.apiClient.clearAuth();
            window.location.href = '/';
        }
    }

    handleOfflineStatusChange(status) {
        console.log('Offline status changed:', status);
        // Handle offline status changes
        if (status === 'OFFLINE') {
            // Show offline warning
        }
    }

    bindScreenSizeChange() {
        // Listen for screen size changes and reload data
        window.addEventListener('screenSizeChanged', (e) => {
            console.log('Screen size changed to:', e.detail.screenSize);
            // Reload data with new screen size parameters
            this.loadInitialData();
        });
    }

    openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }

    closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    formatPrice(price) {
        return price.toLocaleString('id-ID');
    }
}

// Initialize mobile app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.mobileApp = new MobileApp();
});
