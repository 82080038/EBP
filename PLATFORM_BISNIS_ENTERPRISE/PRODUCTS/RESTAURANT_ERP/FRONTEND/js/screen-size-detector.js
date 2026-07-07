/**
 * Screen Size Detector Utility
 * Detects current screen size and provides responsive data fetching parameters
 */
class ScreenSizeDetector {
    constructor() {
        this.breakpoints = {
            mobile: 768,
            tablet: 1024
        };
        this.currentSize = this.detectScreenSize();
        this.initResizeListener();
    }

    /**
     * Detect current screen size category
     * @returns {string} 'mobile', 'tablet', or 'desktop'
     */
    detectScreenSize() {
        const width = window.innerWidth;
        
        if (width < this.breakpoints.mobile) {
            return 'mobile';
        } else if (width < this.breakpoints.tablet) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Get current screen size
     * @returns {string} Current screen size category
     */
    getScreenSize() {
        return this.currentSize;
    }

    /**
     * Get data limits based on screen size
     * @returns {Object} Data limits for current screen size
     */
    getDataLimits() {
        const limits = {
            mobile: {
                products: 10,
                orders: 5,
                reservations: 5,
                restaurants: 5,
                reviews: 3,
                categories: 5,
                tables: 6
            },
            tablet: {
                products: 20,
                orders: 10,
                reservations: 10,
                restaurants: 10,
                reviews: 5,
                categories: 10,
                tables: 12
            },
            desktop: {
                products: 100,
                orders: 50,
                reservations: 50,
                restaurants: 50,
                reviews: 10,
                categories: 20,
                tables: 24
            }
        };

        return limits[this.currentSize] || limits.desktop;
    }

    /**
     * Get field list based on screen size (simplified for mobile)
     * @param {string} resourceType - Type of resource (products, orders, etc.)
     * @returns {Array} List of fields to include
     */
    getFields(resourceType) {
        const fieldSets = {
            mobile: {
                products: ['product_id', 'product_name', 'price', 'category_name', 'image_url'],
                orders: ['order_id', 'order_number', 'table_id', 'status', 'total_amount'],
                reservations: ['reservation_id', 'restaurant_name', 'date', 'time', 'party_size', 'status'],
                restaurants: ['id', 'name', 'cuisine', 'rating', 'image', 'distance'],
                tables: ['table_id', 'table_number', 'status']
            },
            tablet: {
                products: ['product_id', 'product_name', 'price', 'description', 'category_name', 'image_url'],
                orders: ['order_id', 'order_number', 'table_id', 'status', 'total_amount', 'created_at'],
                reservations: ['reservation_id', 'restaurant_name', 'date', 'time', 'party_size', 'status', 'special_requests'],
                restaurants: ['id', 'name', 'cuisine', 'rating', 'image', 'distance', 'address', 'phone'],
                tables: ['table_id', 'table_number', 'status', 'capacity']
            },
            desktop: {
                products: ['*'], // All fields
                orders: ['*'],
                reservations: ['*'],
                restaurants: ['*'],
                tables: ['*']
            }
        };

        return fieldSets[this.currentSize]?.[resourceType] || ['*'];
    }

    /**
     * Check if current screen is mobile
     * @returns {boolean}
     */
    isMobile() {
        return this.currentSize === 'mobile';
    }

    /**
     * Check if current screen is tablet
     * @returns {boolean}
     */
    isTablet() {
        return this.currentSize === 'tablet';
    }

    /**
     * Check if current screen is desktop
     * @returns {boolean}
     */
    isDesktop() {
        return this.currentSize === 'desktop';
    }

    /**
     * Initialize resize listener to update screen size
     */
    initResizeListener() {
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const newSize = this.detectScreenSize();
                if (newSize !== this.currentSize) {
                    this.currentSize = newSize;
                    // Dispatch custom event for screen size change
                    window.dispatchEvent(new CustomEvent('screenSizeChanged', {
                        detail: { screenSize: newSize }
                    }));
                }
            }, 250); // Debounce resize events
        });
    }

    /**
     * Get API parameters for current screen size
     * @param {string} resourceType - Type of resource being fetched
     * @returns {Object} API parameters including screen_size, limit, and fields
     */
    getApiParams(resourceType) {
        const params = {
            screen_size: this.currentSize,
            limit: this.getDataLimits()[resourceType] || 20
        };

        const fields = this.getFields(resourceType);
        if (fields.length > 0 && !fields.includes('*')) {
            params.fields = fields.join(',');
        }

        return params;
    }
}

// Initialize global screen size detector
window.screenSizeDetector = new ScreenSizeDetector();
