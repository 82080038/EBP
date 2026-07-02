/**
 * EBP Restaurant Kiosk Application
 * Self-service kiosk functionality
 */
class KioskApp {
    constructor() {
        this.menu = [];
        this.categories = [];
        this.order = [];
        this.currentProduct = null;
        this.tenantId = 1; // Default tenant ID
        this.branchId = 2; // Default branch ID
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadMenu();
    }

    bindEvents() {
        // Category navigation
        document.getElementById('categoryNav').addEventListener('click', (e) => {
            if (e.target.classList.contains('category-btn')) {
                this.filterByCategory(e.target.dataset.category);
                this.updateActiveCategory(e.target);
            }
        });

        // Product modal
        document.getElementById('closeModal').addEventListener('click', () => {
            this.closeModal('productModal');
        });

        document.getElementById('qtyMinus').addEventListener('click', () => {
            this.adjustQuantity(-1);
        });

        document.getElementById('qtyPlus').addEventListener('click', () => {
            this.adjustQuantity(1);
        });

        document.getElementById('addToOrderBtn').addEventListener('click', () => {
            this.addToOrder();
        });

        // Order summary
        document.getElementById('clearOrder').addEventListener('click', () => {
            this.clearOrder();
        });

        document.getElementById('checkoutBtn').addEventListener('click', () => {
            this.showConfirmation();
        });

        // Confirmation modal
        document.getElementById('closeConfirmation').addEventListener('click', () => {
            this.closeModal('confirmationModal');
        });

        document.getElementById('cancelOrder').addEventListener('click', () => {
            this.closeModal('confirmationModal');
        });

        document.getElementById('confirmOrder').addEventListener('click', () => {
            this.placeOrder();
        });

        // Success modal
        document.getElementById('newOrderBtn').addEventListener('click', () => {
            this.closeModal('successModal');
            this.clearOrder();
        });

        // Close modals on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal.id);
                }
            });
        });
    }

    async loadMenu() {
        try {
            const response = await window.apiClient.getKioskMenu(this.tenantId, this.branchId);
            if (response && response.success) {
                this.menu = response.data;
                this.extractCategories();
                this.renderCategories();
                this.renderMenu();
            } else {
                console.error('Failed to load menu:', response ? response.message : 'No response');
                this.loadMockMenu();
            }
        } catch (error) {
            console.error('Error loading menu:', error);
            // Load mock data for demo
            this.loadMockMenu();
        }
    }

    loadMockMenu() {
        this.menu = [
            {
                category_id: 1,
                category_name: 'Main Course',
                products: [
                    { product_id: 1, product_name: 'Nasi Goreng', price: 25000, description: 'Fried rice with vegetables and egg', image_url: null },
                    { product_id: 2, product_name: 'Mie Goreng', price: 22000, description: 'Fried noodles with vegetables', image_url: null }
                ]
            },
            {
                category_id: 2,
                category_name: 'Beverages',
                products: [
                    { product_id: 3, product_name: 'Es Teh Manis', price: 5000, description: 'Sweet iced tea', image_url: null },
                    { product_id: 4, product_name: 'Jus Jeruk', price: 12000, description: 'Fresh orange juice', image_url: null }
                ]
            }
        ];
        this.extractCategories();
        this.renderCategories();
        this.renderMenu();
    }

    extractCategories() {
        this.categories = this.menu.map(cat => ({
            id: cat.category_id,
            name: cat.category_name
        }));
    }

    renderCategories() {
        const nav = document.getElementById('categoryNav');
        nav.innerHTML = '<button class="category-btn active" data-category="all">All</button>';

        this.categories.forEach(cat => {
            const btn = document.createElement('button');
            btn.className = 'category-btn';
            btn.dataset.category = cat.id;
            btn.textContent = cat.name;
            nav.appendChild(btn);
        });
    }

    renderMenu() {
        const grid = document.getElementById('menuGrid');
        grid.innerHTML = '';

        this.menu.forEach(category => {
            category.products.forEach(product => {
                const card = this.createProductCard(product);
                grid.appendChild(card);
            });
        });
    }

    createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.innerHTML = `
            <img src="${product.image_url || 'https://via.placeholder.com/200x150?text=Product'}" 
                 alt="${product.product_name}" 
                 class="product-image">
            <div class="product-info">
                <h3 class="product-name">${product.product_name}</h3>
                <p class="product-price">Rp ${this.formatPrice(product.price)}</p>
            </div>
        `;
        card.addEventListener('click', () => this.showProductModal(product));
        return card;
    }

    showProductModal(product) {
        this.currentProduct = product;
        document.getElementById('modalProductName').textContent = product.product_name;
        document.getElementById('modalProductDescription').textContent = product.description || 'No description';
        document.getElementById('modalProductPrice').textContent = `Rp ${this.formatPrice(product.price)}`;
        document.getElementById('modalProductImage').src = product.image_url || 'https://via.placeholder.com/400x250?text=Product';
        document.getElementById('qtyInput').value = 1;
        this.openModal('productModal');
    }

    adjustQuantity(delta) {
        const input = document.getElementById('qtyInput');
        let value = parseInt(input.value) + delta;
        if (value < 1) value = 1;
        if (value > 99) value = 99;
        input.value = value;
    }

    addToOrder() {
        if (!this.currentProduct) return;

        const quantity = parseInt(document.getElementById('qtyInput').value);
        const existingItem = this.order.find(item => item.product_id === this.currentProduct.product_id);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.order.push({
                product_id: this.currentProduct.product_id,
                product_name: this.currentProduct.product_name,
                price: this.currentProduct.price,
                quantity: quantity
            });
        }

        this.updateOrderSummary();
        this.closeModal('productModal');
    }

    updateOrderSummary() {
        const container = document.getElementById('orderItems');
        const checkoutBtn = document.getElementById('checkoutBtn');

        if (this.order.length === 0) {
            container.innerHTML = '<p class="empty-message">No items in your order</p>';
            checkoutBtn.disabled = true;
            document.getElementById('subtotal').textContent = 'Rp 0';
            document.getElementById('grandTotal').textContent = 'Rp 0';
            return;
        }

        container.innerHTML = '';
        let total = 0;

        this.order.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            const itemEl = document.createElement('div');
            itemEl.className = 'order-item';
            itemEl.innerHTML = `
                <div class="item-info">
                    <p class="item-name">${item.product_name}</p>
                    <p class="item-qty">x${item.quantity}</p>
                </div>
                <p class="item-price">Rp ${this.formatPrice(itemTotal)}</p>
                <button class="remove-item" data-index="${index}">×</button>
            `;
            container.appendChild(itemEl);
        });

        // Add remove button listeners
        container.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.dataset.index);
                this.order.splice(index, 1);
                this.updateOrderSummary();
            });
        });

        document.getElementById('subtotal').textContent = `Rp ${this.formatPrice(total)}`;
        document.getElementById('grandTotal').textContent = `Rp ${this.formatPrice(total)}`;
        checkoutBtn.disabled = false;
    }

    clearOrder() {
        this.order = [];
        this.updateOrderSummary();
    }

    showConfirmation() {
        const container = document.getElementById('orderReview');
        container.innerHTML = '';
        let total = 0;

        this.order.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            const itemEl = document.createElement('div');
            itemEl.className = 'review-item';
            itemEl.innerHTML = `
                <span>${item.product_name} x${item.quantity}</span>
                <span>Rp ${this.formatPrice(itemTotal)}</span>
            `;
            container.appendChild(itemEl);
        });

        document.getElementById('finalTotal').textContent = `Rp ${this.formatPrice(total)}`;
        this.openModal('confirmationModal');
    }

    async placeOrder() {
        const customerName = document.getElementById('customerName').value;
        if (!customerName) {
            alert('Please enter your name');
            return;
        }

        const total = this.order.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        const orderData = {
            customer_name: customerName,
            total_amount: total,
            items: this.order.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.price,
                total_price: item.price * item.quantity
            }))
        };

        try {
            const response = await window.apiClient.createKioskOrder(this.tenantId, this.branchId, orderData);
            if (response.success) {
                this.closeModal('confirmationModal');
                document.getElementById('orderNumber').textContent = response.order_number;
                this.openModal('successModal');
            } else {
                alert('Failed to place order: ' + response.message);
            }
        } catch (error) {
            console.error('Error placing order:', error);
            alert('Failed to place order. Please try again.');
        }
    }

    filterByCategory(categoryId) {
        const grid = document.getElementById('menuGrid');
        grid.innerHTML = '';

        if (categoryId === 'all') {
            this.renderMenu();
            return;
        }

        const category = this.menu.find(cat => cat.category_id == categoryId);
        if (category) {
            category.products.forEach(product => {
                const card = this.createProductCard(product);
                grid.appendChild(card);
            });
        }
    }

    updateActiveCategory(activeBtn) {
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        activeBtn.classList.add('active');
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

// Initialize kiosk app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.kioskApp = new KioskApp();
});
