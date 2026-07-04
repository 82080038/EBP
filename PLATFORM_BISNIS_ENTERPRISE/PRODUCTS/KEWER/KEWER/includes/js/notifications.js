/**
 * In-App Notification System - jQuery Component
 * Fetches and displays notifications from notification_queue
 */

(function($) {
    'use strict';

    // Notification System Class
    class NotificationSystem {
        constructor(options) {
            this.options = $.extend({
                apiUrl: '/api/notifications.php',
                pollInterval: 30000, // 30 seconds
                maxNotifications: 20,
                badgeSelector: '#notification-badge',
                dropdownSelector: '#notification-dropdown',
                autoLoad: true
            }, options);

            this.init();
        }

        init() {
            if (this.options.autoLoad) {
                this.startPolling();
                this.updateBadge();
            }
        }

        // Fetch notifications from API
        fetchNotifications(status = 'sent', limit = 20, offset = 0) {
            return $.ajax({
                url: this.options.apiUrl,
                method: 'GET',
                data: {
                    action: 'list',
                    status: status,
                    limit: limit,
                    offset: offset
                },
                dataType: 'json'
            });
        }

        // Fetch notification count
        fetchCount(status = 'sent') {
            return $.ajax({
                url: this.options.apiUrl,
                method: 'GET',
                data: {
                    action: 'count',
                    status: status
                },
                dataType: 'json'
            });
        }

        // Mark notification as read
        markAsRead(id) {
            return $.ajax({
                url: this.options.apiUrl,
                method: 'POST',
                data: JSON.stringify({
                    action: 'mark_read',
                    id: id
                }),
                contentType: 'application/json',
                dataType: 'json'
            });
        }

        // Mark all notifications as read
        markAllAsRead(status = 'sent') {
            return $.ajax({
                url: this.options.apiUrl,
                method: 'POST',
                data: JSON.stringify({
                    action: 'mark_all_read',
                    status: status
                }),
                contentType: 'application/json',
                dataType: 'json'
            });
        }

        // Update notification badge
        updateBadge() {
            this.fetchCount().done((response) => {
                if (response.success) {
                    const count = response.count;
                    const $badge = $(this.options.badgeSelector);
                    
                    if (count > 0) {
                        $badge.text(count).show();
                    } else {
                        $badge.hide();
                    }
                    
                    // Trigger custom event
                    $(document).trigger('notification:countUpdated', { count: count });
                }
            }).fail((error) => {
                console.error('Failed to fetch notification count:', error);
            });
        }

        // Load and display notifications
        loadNotifications(container) {
            const $container = $(container);
            $container.html('<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div></div>');

            this.fetchNotifications().done((response) => {
                if (response.success && response.data.length > 0) {
                    let html = '<div class="notification-list">';
                    
                    response.data.forEach(notification => {
                        html += this.renderNotificationItem(notification);
                    });
                    
                    html += '</div>';
                    $container.html(html);
                    
                    // Trigger custom event
                    $(document).trigger('notification:loaded', { data: response.data });
                } else {
                    $container.html('<div class="text-center text-muted p-3">Tidak ada notifikasi</div>');
                }
            }).fail((error) => {
                console.error('Failed to load notifications:', error);
                $container.html('<div class="text-center text-danger p-3">Gagal memuat notifikasi</div>');
            });
        }

        // Render single notification item
        renderNotificationItem(notification) {
            const statusColors = {
                'sent': 'success',
                'pending': 'warning',
                'processing': 'info',
                'failed': 'danger',
                'cancelled': 'secondary'
            };

            const statusLabels = {
                'sent': 'Terkirim',
                'pending': 'Menunggu',
                'processing': 'Proses',
                'failed': 'Gagal',
                'cancelled': 'Batal'
            };

            const timeAgo = this.timeAgo(notification.created_at);
            const statusColor = statusColors[notification.status] || 'secondary';
            const statusLabel = statusLabels[notification.status] || notification.status;

            return `
                <div class="notification-item p-3 border-bottom" data-id="${notification.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-${statusColor} me-2">${statusLabel}</span>
                                <span class="badge bg-light text-dark">${notification.type || notification.tipe || 'Lainnya'}</span>
                            </div>
                            <p class="mb-1 text-wrap">${notification.pesan}</p>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> ${timeAgo}
                                ${notification.sender_name ? `<span class="ms-2"><i class="bi bi-person"></i> ${notification.sender_name}</span>` : ''}
                            </small>
                        </div>
                        <button class="btn btn-sm btn-link text-muted mark-read-btn" title="Tandai dibaca">
                            <i class="bi bi-check2"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        // Format time ago
        timeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            const intervals = {
                'tahun': 31536000,
                'bulan': 2592000,
                'minggu': 604800,
                'hari': 86400,
                'jam': 3600,
                'menit': 60
            };

            for (const [unit, secondsInUnit] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInUnit);
                if (interval >= 1) {
                    return `${interval} ${unit} yang lalu`;
                }
            }

            return 'Baru saja';
        }

        // Start polling for notifications
        startPolling() {
            this.pollInterval = setInterval(() => {
                this.updateBadge();
            }, this.options.pollInterval);
        }

        // Stop polling
        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        }

        // Show notification dropdown
        showDropdown() {
            this.loadNotifications(this.options.dropdownSelector);
        }
    }

    // jQuery plugin
    $.fn.notificationSystem = function(options) {
        return this.each(function() {
            if (!$.data(this, 'notificationSystem')) {
                $.data(this, 'notificationSystem', new NotificationSystem(options));
            }
        });
    };

    // Global instance
    window.KewerNotifications = new NotificationSystem();

    // Auto-initialize notification dropdown
    $(document).ready(function() {
        // Update badge on page load
        window.KewerNotifications.updateBadge();

        // Handle mark as read click
        $(document).on('click', '.mark-read-btn', function(e) {
            e.preventDefault();
            const $item = $(this).closest('.notification-item');
            const id = $item.data('id');
            
            window.KewerNotifications.markAsRead(id).done((response) => {
                if (response.success) {
                    $item.fadeOut(300, function() {
                        $(this).remove();
                        window.KewerNotifications.updateBadge();
                    });
                }
            });
        });

        // Handle mark all as read
        $(document).on('click', '#mark-all-read', function(e) {
            e.preventDefault();
            window.KewerNotifications.markAllAsRead().done((response) => {
                if (response.success) {
                    $('.notification-item').fadeOut(300);
                    window.KewerNotifications.updateBadge();
                }
            });
        });

        // Load notifications when dropdown is opened
        $(document).on('click', '[data-toggle="notification-dropdown"]', function(e) {
            e.preventDefault();
            const $dropdown = $($(this).data('target'));
            window.KewerNotifications.loadNotifications($dropdown);
        });
    });

})(jQuery);
