/**
 * Offline Indicator Component
 * Monitors online/offline status and displays appropriate indicator
 */
class OfflineIndicator {
    constructor(elementId) {
        this.element = document.getElementById(elementId);
        this.status = 'ONLINE';
        this.checkInterval = null;
        this.init();
    }

    init() {
        if (!this.element) {
            console.error('Offline indicator element not found');
            return;
        }

        // Listen for browser online/offline events
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());

        // Start periodic status check
        this.startStatusCheck();

        // Initial status check
        this.checkStatus();
    }

    startStatusCheck() {
        // Check status every 30 seconds
        this.checkInterval = setInterval(() => {
            this.checkStatus();
        }, 30000);
    }

    stopStatusCheck() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }

    async checkStatus() {
        try {
            // Try to fetch from the public API to check connectivity (no auth required)
            const response = await fetch('/api/v1/public/offline/status', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                cache: 'no-cache'
            });

            if (response.ok) {
                const data = await response.json();
                this.updateStatus(data.data.status, data.data);
            } else {
                this.handleOffline();
            }
        } catch (error) {
            // If API call fails, check browser navigator status
            if (navigator.onLine) {
                this.updateStatus('ONLINE', { is_offline: false });
            } else {
                this.handleOffline();
            }
        }
    }

    handleOnline() {
        this.updateStatus('ONLINE', { is_offline: false });
    }

    handleOffline() {
        this.updateStatus('OFFLINE', { is_offline: true });
    }

    updateStatus(status, data = {}) {
        this.status = status;

        const statusDot = this.element.querySelector('.status-dot');
        const statusText = this.element.querySelector('.status-text');

        if (statusDot) {
            statusDot.className = 'status-dot ' + status.toLowerCase();
        }

        if (statusText) {
            statusText.textContent = status;
        }

        // Show/hide indicator based on status
        if (status === 'OFFLINE') {
            this.element.classList.add('offline');
            this.element.style.display = 'flex';
        } else {
            this.element.classList.remove('offline');
            this.element.style.display = 'none';
        }

        // Dispatch custom event for other components to listen
        const event = new CustomEvent('offlineStatusChanged', {
            detail: { status, data }
        });
        document.dispatchEvent(event);
    }

    isOnline() {
        return this.status === 'ONLINE';
    }

    getStatus() {
        return this.status;
    }
}

// Initialize offline indicator when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize for kiosk
    const kioskIndicator = document.getElementById('offlineIndicator');
    if (kioskIndicator) {
        window.kioskOfflineIndicator = new OfflineIndicator('offlineIndicator');
    }

    // Initialize for mobile
    const mobileIndicator = document.getElementById('offlineIndicator');
    if (mobileIndicator && !window.kioskOfflineIndicator) {
        window.mobileOfflineIndicator = new OfflineIndicator('offlineIndicator');
    }
});
