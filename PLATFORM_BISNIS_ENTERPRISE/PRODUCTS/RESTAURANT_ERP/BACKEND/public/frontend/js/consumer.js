// Consumer App JavaScript
class ConsumerApp {
    constructor() {
        this.currentUser = JSON.parse(localStorage.getItem('currentUser')) || null;
        this.currentRestaurant = null;
        this.cart = [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupNavigation();
        this.loadInitialData();
        i18n.updatePage();
    }

    setupEventListeners() {
        // Sidebar
        document.getElementById('menuBtn').addEventListener('click', () => this.toggleSidebar());
        document.getElementById('closeSidebar').addEventListener('click', () => this.toggleSidebar());
        document.getElementById('overlay').addEventListener('click', () => this.toggleSidebar());

        // Language
        document.getElementById('langBtn').addEventListener('click', () => this.toggleLanguage());
        document.getElementById('languageSelect').addEventListener('change', (e) => {
            i18n.setLanguage(e.target.value);
        });

        // Profile
        document.getElementById('profileBtn').addEventListener('click', () => this.showLoginModal());

        // Search
        document.getElementById('homeSearch').addEventListener('input', (e) => this.handleSearch(e.target.value));
        document.getElementById('searchInput').addEventListener('input', (e) => this.handleSearch(e.target.value));
        document.getElementById('filterBtn').addEventListener('click', () => this.toggleFilters());
        document.getElementById('applyFilters').addEventListener('click', () => this.applyFilters());

        // Quick filters
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.addEventListener('click', () => this.handleQuickFilter(chip));
        });

        // New reservation
        document.getElementById('newReservationBtn').addEventListener('click', () => this.showReservationModal());

        // Modals
        document.getElementById('closeReservation').addEventListener('click', () => this.closeModal('reservationModal'));
        document.getElementById('closeOrder').addEventListener('click', () => this.closeModal('orderModal'));
        document.getElementById('closeReview').addEventListener('click', () => this.closeModal('reviewModal'));
        document.getElementById('closeLogin').addEventListener('click', () => this.closeModal('loginModal'));

        // Forms
        document.getElementById('reservationForm').addEventListener('submit', (e) => this.handleReservationSubmit(e));
        document.getElementById('reviewForm').addEventListener('submit', (e) => this.handleReviewSubmit(e));
        document.getElementById('loginForm').addEventListener('submit', (e) => this.handleLoginSubmit(e));

        // Order type
        document.querySelectorAll('input[name="orderType"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.handleOrderTypeChange(e.target.value));
        });

        // Star rating
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', () => this.handleStarRating(star));
        });

        // Settings
        document.getElementById('darkModeToggle').addEventListener('change', (e) => this.toggleDarkMode(e.target.checked));
        document.getElementById('notificationsToggle').addEventListener('change', (e) => this.toggleNotifications(e.target.checked));
        document.getElementById('locationToggle').addEventListener('change', (e) => this.toggleLocation(e.target.checked));

        // Place order
        document.getElementById('placeOrderBtn').addEventListener('click', () => this.placeOrder());
    }

    setupNavigation() {
        // Sidebar navigation
        document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const page = item.getAttribute('data-page');
                this.navigateTo(page);
                this.toggleSidebar();
            });
        });

        // Bottom navigation
        document.querySelectorAll('.bottom-nav .nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const page = item.getAttribute('data-page');
                this.navigateTo(page);
            });
        });
    }

    navigateTo(page) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        
        // Show target page
        const targetPage = document.getElementById(page + 'Page');
        if (targetPage) {
            targetPage.classList.add('active');
        }

        // Update navigation
        document.querySelectorAll('.menu-item, .nav-item').forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-page') === page) {
                item.classList.add('active');
            }
        });

        // Load page-specific data
        this.loadPageData(page);
    }

    loadPageData(page) {
        switch(page) {
            case 'home':
                this.loadFeaturedRestaurants();
                this.loadNearbyRestaurants();
                this.loadCuisines();
                break;
            case 'search':
                this.loadCuisineFilters();
                break;
            case 'reservations':
                this.loadReservations();
                break;
            case 'orders':
                this.loadOrders();
                break;
            case 'favorites':
                this.loadFavorites();
                break;
            case 'loyalty':
                this.loadLoyalty();
                break;
            case 'help':
                this.loadFAQ();
                break;
        }
    }

    loadInitialData() {
        this.updateUserInfo();
        this.loadFeaturedRestaurants();
        this.loadNearbyRestaurants();
        this.loadCuisines();
    }

    async loadFeaturedRestaurants() {
        try {
            const response = await apiClient.get('/api/consumer/restaurants/featured');
            const restaurants = response.data;
            this.renderFeaturedRestaurants(restaurants);
        } catch (error) {
            console.error('Error loading featured restaurants:', error);
            this.renderFeaturedRestaurants(this.getMockRestaurants());
        }
    }

    async loadNearbyRestaurants() {
        try {
            const location = await this.getCurrentLocation();
            const response = await apiClient.get('/api/consumer/restaurants/nearby', {
                params: { lat: location.lat, lng: location.lng }
            });
            const restaurants = response.data;
            this.renderNearbyRestaurants(restaurants);
        } catch (error) {
            console.error('Error loading nearby restaurants:', error);
            this.renderNearbyRestaurants(this.getMockRestaurants());
        }
    }

    async loadCuisines() {
        try {
            const response = await apiClient.get('/api/consumer/cuisines');
            const cuisines = response.data;
            this.renderCuisines(cuisines);
        } catch (error) {
            console.error('Error loading cuisines:', error);
            this.renderCuisines(this.getMockCuisines());
        }
    }

    renderFeaturedRestaurants(restaurants) {
        const container = document.getElementById('featuredRestaurants');
        container.innerHTML = restaurants.map(restaurant => `
            <div class="restaurant-card" onclick="consumerApp.showRestaurantDetails(${restaurant.id})">
                <img src="${restaurant.image}" alt="${restaurant.name}" class="restaurant-image">
                <div class="restaurant-info">
                    <h4 class="restaurant-name">${restaurant.name}</h4>
                    <p class="restaurant-cuisine">${restaurant.cuisine}</p>
                    <div class="restaurant-rating">
                        <span>★</span>
                        <span>${restaurant.rating}</span>
                    </div>
                </div>
            </div>
        `).join('');
    }

    renderNearbyRestaurants(restaurants) {
        const container = document.getElementById('nearbyRestaurants');
        container.innerHTML = restaurants.map(restaurant => `
            <div class="restaurant-list-item" onclick="consumerApp.showRestaurantDetails(${restaurant.id})">
                <img src="${restaurant.image}" alt="${restaurant.name}" class="restaurant-image">
                <div class="restaurant-info">
                    <h4 class="restaurant-name">${restaurant.name}</h4>
                    <p class="restaurant-cuisine">${restaurant.cuisine}</p>
                    <div class="restaurant-rating">
                        <span>★</span>
                        <span>${restaurant.rating}</span>
                    </div>
                    <p class="restaurant-distance">${restaurant.distance} km</p>
                </div>
            </div>
        `).join('');
    }

    renderCuisines(cuisines) {
        const container = document.getElementById('cuisineGrid');
        container.innerHTML = cuisines.map(cuisine => `
            <div class="cuisine-item" onclick="consumerApp.filterByCuisine('${cuisine.name}')">
                <div class="cuisine-icon">${cuisine.icon}</div>
                <p class="cuisine-name">${cuisine.name}</p>
            </div>
        `).join('');
    }

    showRestaurantDetails(restaurantId) {
        // Load restaurant details and navigate to restaurant page
        this.currentRestaurant = restaurantId;
        this.loadRestaurantDetails(restaurantId);
        this.navigateTo('restaurant');
    }

    async loadRestaurantDetails(restaurantId) {
        try {
            const response = await apiClient.get(`/api/consumer/restaurants/${restaurantId}`);
            const restaurant = response.data;
            this.renderRestaurantDetails(restaurant);
        } catch (error) {
            console.error('Error loading restaurant details:', error);
        }
    }

    renderRestaurantDetails(restaurant) {
        // Render restaurant header
        document.getElementById('restaurantHeader').innerHTML = `
            <img src="${restaurant.image}" alt="${restaurant.name}" class="restaurant-hero-image">
            <div class="restaurant-hero-info">
                <h2>${restaurant.name}</h2>
                <p>${restaurant.cuisine}</p>
                <div class="restaurant-rating">
                    <span>★</span>
                    <span>${restaurant.rating}</span>
                </div>
            </div>
        `;

        // Render restaurant info
        document.getElementById('restaurantInfo').innerHTML = `
            <p><strong>Address:</strong> ${restaurant.address}</p>
            <p><strong>Phone:</strong> ${restaurant.phone}</p>
            <p><strong>Hours:</strong> ${restaurant.hours}</p>
            <p><strong>Price Range:</strong> ${restaurant.priceRange}</p>
            <p><strong>Features:</strong> ${restaurant.features.join(', ')}</p>
        `;

        // Render menu categories
        this.renderMenuCategories(restaurant.menuCategories);

        // Render menu items
        this.renderMenuItems(restaurant.menuItems);

        // Render reviews
        this.renderReviews(restaurant.reviews);
    }

    renderMenuCategories(categories) {
        const container = document.getElementById('menuCategories');
        container.innerHTML = categories.map((category, index) => `
            <button class="category-tab ${index === 0 ? 'active' : ''}" data-category="${category.id}">
                ${category.name}
            </button>
        `).join('');

        // Add click handlers
        container.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', () => this.filterMenuByCategory(tab.getAttribute('data-category')));
        });
    }

    renderMenuItems(items) {
        const container = document.getElementById('menuItems');
        container.innerHTML = items.map(item => `
            <div class="menu-item-card">
                <img src="${item.image}" alt="${item.name}" class="menu-item-image">
                <div class="menu-item-info">
                    <h4>${item.name}</h4>
                    <p class="menu-item-description">${item.description}</p>
                    <p class="menu-item-price">Rp ${item.price.toLocaleString()}</p>
                    <button class="add-to-cart-btn" onclick="consumerApp.addToCart(${item.id})">
                        Add to Cart
                    </button>
                </div>
            </div>
        `).join('');
    }

    renderReviews(reviews) {
        const container = document.getElementById('reviewsList');
        container.innerHTML = reviews.map(review => `
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-avatar">${review.reviewer.charAt(0)}</div>
                    <div class="reviewer-info">
                        <p class="reviewer-name">${review.reviewer}</p>
                        <div class="review-rating">
                            ${'★'.repeat(review.rating)}
                        </div>
                    </div>
                </div>
                <p class="review-comment">${review.comment}</p>
                <p class="review-date">${review.date}</p>
            </div>
        `).join('');
    }

    toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('overlay').classList.toggle('active');
    }

    toggleLanguage() {
        const newLang = i18n.getLanguage() === 'id' ? 'en' : 'id';
        i18n.setLanguage(newLang);
    }

    toggleDarkMode(enabled) {
        document.body.classList.toggle('dark-mode', enabled);
        localStorage.setItem('darkMode', enabled);
    }

    toggleNotifications(enabled) {
        localStorage.setItem('notifications', enabled);
        // Request notification permission
        if (enabled && 'Notification' in window) {
            Notification.requestPermission();
        }
    }

    toggleLocation(enabled) {
        localStorage.setItem('location', enabled);
        if (enabled) {
            this.getCurrentLocation();
        }
    }

    async getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        });
                    },
                    (error) => {
                        reject(error);
                    }
                );
            } else {
                reject(new Error('Geolocation not supported'));
            }
        });
    }

    handleSearch(query) {
        // Implement search logic
        console.log('Search query:', query);
    }

    handleQuickFilter(chip) {
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        const filter = chip.getAttribute('data-filter');
        this.applyQuickFilter(filter);
    }

    applyQuickFilter(filter) {
        // Implement filter logic
        console.log('Apply filter:', filter);
    }

    toggleFilters() {
        document.getElementById('filtersPanel').classList.toggle('active');
    }

    applyFilters() {
        // Implement advanced filter logic
        console.log('Apply advanced filters');
    }

    filterByCuisine(cuisine) {
        // Implement cuisine filter
        console.log('Filter by cuisine:', cuisine);
    }

    filterMenuByCategory(categoryId) {
        // Implement menu category filter
        console.log('Filter menu by category:', categoryId);
    }

    addToCart(itemId) {
        // Add item to cart
        console.log('Add to cart:', itemId);
    }

    showReservationModal() {
        if (!this.currentUser) {
            this.showLoginModal();
            return;
        }
        this.loadRestaurantsForReservation();
        this.openModal('reservationModal');
    }

    async loadRestaurantsForReservation() {
        try {
            const response = await apiClient.get('/api/consumer/restaurants');
            const restaurants = response.data;
            const select = document.getElementById('reservationRestaurant');
            select.innerHTML = restaurants.map(r => 
                `<option value="${r.id}">${r.name}</option>`
            ).join('');
        } catch (error) {
            console.error('Error loading restaurants:', error);
        }
    }

    async handleReservationSubmit(e) {
        e.preventDefault();
        const reservation = {
            restaurantId: document.getElementById('reservationRestaurant').value,
            date: document.getElementById('reservationDate').value,
            time: document.getElementById('reservationTime').value,
            partySize: document.getElementById('partySize').value,
            specialRequests: document.getElementById('specialRequests').value
        };

        try {
            await apiClient.post('/api/consumer/reservations', reservation);
            this.closeModal('reservationModal');
            this.loadReservations();
            this.showNotification('Reservation created successfully!');
        } catch (error) {
            console.error('Error creating reservation:', error);
            this.showNotification('Failed to create reservation', 'error');
        }
    }

    async loadReservations() {
        if (!this.currentUser) return;
        
        try {
            const response = await apiClient.get('/api/consumer/reservations');
            const reservations = response.data;
            this.renderReservations(reservations);
        } catch (error) {
            console.error('Error loading reservations:', error);
        }
    }

    renderReservations(reservations) {
        const container = document.getElementById('reservationsList');
        container.innerHTML = reservations.map(reservation => `
            <div class="reservation-card">
                <h4>${reservation.restaurantName}</h4>
                <p><strong>Date:</strong> ${reservation.date}</p>
                <p><strong>Time:</strong> ${reservation.time}</p>
                <p><strong>Party Size:</strong> ${reservation.partySize}</p>
                <p><strong>Status:</strong> ${reservation.status}</p>
            </div>
        `).join('');
    }

    async loadOrders() {
        if (!this.currentUser) return;
        
        try {
            const response = await apiClient.get('/api/consumer/orders');
            const orders = response.data;
            this.renderOrders(orders);
        } catch (error) {
            console.error('Error loading orders:', error);
        }
    }

    renderOrders(orders) {
        const container = document.getElementById('ordersList');
        container.innerHTML = orders.map(order => `
            <div class="order-card">
                <h4>Order #${order.id}</h4>
                <p><strong>Restaurant:</strong> ${order.restaurantName}</p>
                <p><strong>Total:</strong> Rp ${order.total.toLocaleString()}</p>
                <p><strong>Status:</strong> ${order.status}</p>
                <p><strong>Date:</strong> ${order.date}</p>
            </div>
        `).join('');
    }

    async loadFavorites() {
        if (!this.currentUser) return;
        
        try {
            const response = await apiClient.get('/api/consumer/favorites');
            const favorites = response.data;
            this.renderFavorites(favorites);
        } catch (error) {
            console.error('Error loading favorites:', error);
        }
    }

    renderFavorites(favorites) {
        const container = document.getElementById('favoritesList');
        container.innerHTML = favorites.map(favorite => `
            <div class="favorite-card" onclick="consumerApp.showRestaurantDetails(${favorite.restaurantId})">
                <img src="${favorite.image}" alt="${favorite.name}" class="favorite-image">
                <div class="favorite-info">
                    <h4>${favorite.name}</h4>
                    <p>${favorite.cuisine}</p>
                    <div class="restaurant-rating">
                        <span>★</span>
                        <span>${favorite.rating}</span>
                    </div>
                </div>
            </div>
        `).join('');
    }

    async loadLoyalty() {
        if (!this.currentUser) return;
        
        try {
            const response = await apiClient.get('/api/consumer/loyalty');
            const loyalty = response.data;
            this.renderLoyalty(loyalty);
        } catch (error) {
            console.error('Error loading loyalty:', error);
        }
    }

    renderLoyalty(loyalty) {
        const container = document.getElementById('loyaltyContent');
        container.innerHTML = `
            <div class="loyalty-card">
                <h3>Your Points</h3>
                <p class="points-value">${loyalty.points.toLocaleString()}</p>
                <p class="points-tier">Tier: ${loyalty.tier}</p>
            </div>
            <div class="rewards-section">
                <h3>Available Rewards</h3>
                <div class="rewards-list">
                    ${loyalty.rewards.map(reward => `
                        <div class="reward-card">
                            <h4>${reward.name}</h4>
                            <p>${reward.description}</p>
                            <p class="reward-points">${reward.points} points</p>
                            <button class="redeem-btn" onclick="consumerApp.redeemReward(${reward.id})">
                                Redeem
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    async redeemReward(rewardId) {
        try {
            await apiClient.post('/api/consumer/loyalty/redeem', { rewardId });
            this.loadLoyalty();
            this.showNotification('Reward redeemed successfully!');
        } catch (error) {
            console.error('Error redeeming reward:', error);
            this.showNotification('Failed to redeem reward', 'error');
        }
    }

    async loadFAQ() {
        try {
            const response = await apiClient.get('/api/consumer/faq');
            const faqs = response.data;
            this.renderFAQ(faqs);
        } catch (error) {
            console.error('Error loading FAQ:', error);
        }
    }

    renderFAQ(faqs) {
        const container = document.getElementById('faqList');
        container.innerHTML = faqs.map(faq => `
            <div class="faq-item">
                <h4 class="faq-question">${faq.question}</h4>
                <p class="faq-answer">${faq.answer}</p>
            </div>
        `).join('');
    }

    handleOrderTypeChange(type) {
        const addressSection = document.getElementById('deliveryAddressSection');
        addressSection.style.display = type === 'delivery' ? 'block' : 'none';
    }

    handleStarRating(star) {
        const rating = star.getAttribute('data-rating');
        document.querySelectorAll('.star').forEach(s => {
            const sRating = s.getAttribute('data-rating');
            s.classList.toggle('active', sRating <= rating);
        });
    }

    async handleReviewSubmit(e) {
        e.preventDefault();
        const review = {
            restaurantId: this.currentRestaurant,
            rating: document.querySelectorAll('.star.active').length,
            comment: document.getElementById('reviewComment').value
        };

        try {
            await apiClient.post('/api/consumer/reviews', review);
            this.closeModal('reviewModal');
            this.showNotification('Review submitted successfully!');
        } catch (error) {
            console.error('Error submitting review:', error);
            this.showNotification('Failed to submit review', 'error');
        }
    }

    async handleLoginSubmit(e) {
        e.preventDefault();
        const credentials = {
            email: document.getElementById('loginEmail').value,
            password: document.getElementById('loginPassword').value
        };

        try {
            const response = await apiClient.post('/api/consumer/auth/login', credentials);
            this.currentUser = response.data.user;
            localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
            localStorage.setItem('token', response.data.token);
            this.closeModal('loginModal');
            this.updateUserInfo();
            this.showNotification('Login successful!');
        } catch (error) {
            console.error('Error logging in:', error);
            this.showNotification('Login failed', 'error');
        }
    }

    updateUserInfo() {
        const userInfo = document.getElementById('userInfo');
        if (this.currentUser) {
            userInfo.innerHTML = `
                <div class="user-avatar">${this.currentUser.name.charAt(0)}</div>
                <div class="user-details">
                    <p class="user-name">${this.currentUser.name}</p>
                    <p class="user-role">${this.currentUser.email}</p>
                </div>
            `;
        } else {
            userInfo.innerHTML = `
                <div class="user-avatar">G</div>
                <div class="user-details">
                    <p class="user-name" data-i18n="guest">Guest</p>
                    <p class="user-role" data-i18n="login_prompt">Login to access features</p>
                </div>
            `;
        }
    }

    showLoginModal() {
        this.openModal('loginModal');
    }

    openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }

    closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    showNotification(message, type = 'success') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Mock data for development
    getMockRestaurants() {
        return [
            {
                id: 1,
                name: "Warung Padang Sederhana",
                cuisine: "Padang",
                rating: 4.5,
                image: "/images/restaurant1.jpg",
                distance: 0.5,
                address: "Jl. Sudirman No. 123",
                phone: "+62 21 1234 5678",
                hours: "10:00 - 22:00",
                priceRange: "$$",
                features: ["Halal", "Delivery", "Reservation"],
                menuCategories: [
                    { id: 1, name: "Rice Dishes" },
                    { id: 2, name: "Side Dishes" }
                ],
                menuItems: [
                    { id: 1, name: "Nasi Rendang", description: "Beef rendang with rice", price: 35000, image: "/images/dish1.jpg" },
                    { id: 2, name: "Ayam Pop", description: "Fried chicken", price: 25000, image: "/images/dish2.jpg" }
                ],
                reviews: [
                    { reviewer: "John Doe", rating: 5, comment: "Great food!", date: "2024-01-15" }
                ]
            },
            {
                id: 2,
                name: "Sate Ayam Cak Man",
                cuisine: "Indonesian",
                rating: 4.3,
                image: "/images/restaurant2.jpg",
                distance: 1.2,
                address: "Jl. Thamrin No. 456",
                phone: "+62 21 8765 4321",
                hours: "11:00 - 23:00",
                priceRange: "$",
                features: ["Halal", "Delivery"],
                menuCategories: [
                    { id: 1, name: "Satay" },
                    { id: 2, name: "Drinks" }
                ],
                menuItems: [
                    { id: 1, name: "Sate Ayam", description: "Chicken satay with peanut sauce", price: 20000, image: "/images/dish3.jpg" }
                ],
                reviews: []
            }
        ];
    }

    getMockCuisines() {
        return [
            { id: 1, name: "Indonesian", icon: "🇮🇩" },
            { id: 2, name: "Chinese", icon: "🥢" },
            { id: 3, name: "Japanese", icon: "🍣" },
            { id: 4, name: "Western", icon: "🍔" },
            { id: 5, name: "Thai", icon: "🍜" },
            { id: 6, name: "Indian", icon: "🍛" }
        ];
    }
}

// Initialize app
const consumerApp = new ConsumerApp();
