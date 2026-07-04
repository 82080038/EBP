/**
 * Global API Helper for Kewer Application
 * Centralized API calls with error handling and response standardization
 */

(function($) {
    'use strict';

    // API Base URL
    const API_BASE = '/api';

    // API Helper Class
    class KewerAPI {
        constructor() {
            this.baseUrl = API_BASE;
        }

        // Generic GET request
        get(endpoint, params = {}) {
            const deferred = $.Deferred();
            $.ajax({
                url: this.baseUrl + endpoint,
                method: 'GET',
                data: params,
                dataType: 'json'
            })
            .done(response => {
                deferred.resolve(this.handleResponse(response));
            })
            .fail(error => {
                deferred.resolve(this.handleError(error));
            });
            return deferred.promise();
        }

        // Generic POST request
        post(endpoint, data = {}) {
            const deferred = $.Deferred();
            $.ajax({
                url: this.baseUrl + endpoint,
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json'
            })
            .done(response => {
                deferred.resolve(this.handleResponse(response));
            })
            .fail(error => {
                deferred.resolve(this.handleError(error));
            });
            return deferred.promise();
        }

        // Generic PUT request
        put(endpoint, data = {}) {
            const deferred = $.Deferred();
            $.ajax({
                url: this.baseUrl + endpoint,
                method: 'PUT',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json'
            })
            .done(response => {
                deferred.resolve(this.handleResponse(response));
            })
            .fail(error => {
                deferred.resolve(this.handleError(error));
            });
            return deferred.promise();
        }

        // Generic DELETE request
        delete(endpoint, params = {}) {
            const deferred = $.Deferred();
            $.ajax({
                url: this.baseUrl + endpoint,
                method: 'DELETE',
                data: params,
                dataType: 'json'
            })
            .done(response => {
                deferred.resolve(this.handleResponse(response));
            })
            .fail(error => {
                deferred.resolve(this.handleError(error));
            });
            return deferred.promise();
        }

        // Handle successful response
        handleResponse(response) {
            if (response.success === false) {
                console.error('API Error:', response.error);
            }
            return response;
        }

        // Handle error
        handleError(error) {
            console.error('API Error:', error);
            const message = error.responseJSON?.error || error.message || 'Terjadi kesalahan sistem';
            return { success: false, error: message };
        }

        // ============ NASABAH API ============
        getNasabah(params = {}) {
            return this.get('/nasabah.php', params);
        }

        getNasabahDetail(id) {
            return this.get('/nasabah.php', { id: id });
        }

        createNasabah(data) {
            return this.post('/nasabah.php', data);
        }

        updateNasabah(id, data) {
            return this.put('/nasabah.php?id=' + id, data);
        }

        deleteNasabah(id) {
            return this.delete('/nasabah.php', { id: id });
        }

        // ============ PINJAMAN API ============
        getPinjaman(params = {}) {
            return this.get('/pinjaman.php', params);
        }

        getPinjamanDetail(id) {
            return this.get('/pinjaman.php', { id: id });
        }

        createPinjaman(data) {
            return this.post('/pinjaman.php', data);
        }

        updatePinjaman(id, data) {
            return this.put('/pinjaman.php?id=' + id, data);
        }

        approvePinjaman(id) {
            return this.put('/pinjaman.php', { id: id, action: 'approve' });
        }

        rejectPinjaman(id) {
            return this.put('/pinjaman.php', { id: id, action: 'reject' });
        }

        deletePinjaman(id) {
            return this.delete('/pinjaman.php', { id: id });
        }

        // ============ ANGSURAN API ============
        getAngsuran(params = {}) {
            return this.get('/angsuran.php', params);
        }

        getAngsuranDetail(id) {
            return this.get('/angsuran.php', { id: id });
        }

        getAngsuranByPinjaman(pinjamanId) {
            return this.get('/angsuran.php', { pinjaman_id: pinjamanId });
        }

        // ============ PEMBAYARAN API ============
        getPembayaran(params = {}) {
            return this.get('/pembayaran.php', params);
        }

        getPembayaranDetail(id) {
            return this.get('/pembayaran.php', { id: id });
        }

        createPembayaran(data) {
            return this.post('/pembayaran.php', data);
        }

        updatePembayaran(id, data) {
            return this.put('/pembayaran.php?id=' + id, data);
        }

        // ============ CABANG API ============
        getCabang(params = {}) {
            return this.get('/cabang.php', params);
        }

        createCabang(data) {
            return this.post('/cabang.php', data);
        }

        updateCabang(id, data) {
            return this.put('/cabang.php?id=' + id, data);
        }

        deleteCabang(id) {
            return this.delete('/cabang.php', { id: id });
        }

        // ============ PETUGAS API ============
        getPetugas(params = {}) {
            return this.get('/users.php', params);
        }

        getPetugasDetail(id) {
            return this.get('/users.php', { id: id });
        }

        createPetugas(data) {
            return this.post('/users.php', data);
        }

        updatePetugas(id, data) {
            return this.put('/users.php?id=' + id, data);
        }

        deletePetugas(id) {
            return this.delete('/users.php', { id: id });
        }

        // ============ DASHBOARD API ============
        getDashboardStats() {
            return this.get('/dashboard.php', { action: 'stats' });
        }

        getDashboardCharts() {
            return this.get('/dashboard.php', { action: 'charts' });
        }

        getDashboardRecent() {
            return this.get('/dashboard.php', { action: 'recent' });
        }

        // ============ LAPORAN API ============
        getLaporanKeuangan(params = {}) {
            return this.get('/export.php', { type: 'keuangan', ...params });
        }

        getLaporanPinjaman(params = {}) {
            return this.get('/export.php', { type: 'pinjaman', ...params });
        }

        getLaporanNasabah(params = {}) {
            return this.get('/export.php', { type: 'nasabah', ...params });
        }

        // ============ KAS PETUGAS API ============
        getKasPetugas(params = {}) {
            return this.get('/kas_petugas.php', params);
        }

        createSetoran(data) {
            return this.post('/kas_petugas_setoran.php', data);
        }

        // ============ PENGELUARAN API ============
        getPengeluaran(params = {}) {
            return this.get('/pengeluaran.php', params);
        }

        createPengeluaran(data) {
            return this.post('/pengeluaran.php', data);
        }

        updatePengeluaran(id, data) {
            return this.put('/pengeluaran.php?id=' + id, data);
        }

        deletePengeluaran(id) {
            return this.delete('/pengeluaran.php', { id: id });
        }

        // ============ NOTIFICATIONS API ============
        getNotifications(params = {}) {
            return this.get('/notifications.php', params);
        }

        getNotificationCount(status = 'sent') {
            return this.get('/notifications.php', { action: 'count', status: status });
        }

        markNotificationAsRead(id) {
            return this.post('/notifications.php', { action: 'mark_read', id: id });
        }

        markAllNotificationsAsRead(status = 'sent') {
            return this.post('/notifications.php', { action: 'mark_all_read', status: status });
        }

        // ============ AUTH API ============
        login(username, password) {
            return this.post('/auth.php', { action: 'login', username, password });
        }

        logout() {
            return this.post('/auth.php', { action: 'logout' });
        }

        getCurrentUser() {
            return this.get('/auth.php', { action: 'me' });
        }

        // ============ SETTINGS API ============
        getSettings() {
            return this.get('/feature_flags.php');
        }

        updateSetting(key, value) {
            return this.put('/feature_flags.php', { key, value });
        }
    }

    // Create global instance
    window.KewerAPI = new KewerAPI();

    // jQuery plugin for data fetching
    $.fn.loadData = function(endpoint, params = {}) {
        const $el = $(this);
        $el.html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
        
        window.KewerAPI.get(endpoint, params).done(response => {
            $el.trigger('data:loaded', response);
        }).fail(error => {
            $el.html('<div class="alert alert-danger">Gagal memuat data</div>');
            $el.trigger('data:error', error);
        });
        
        return this;
    };

    // DataTables AJAX source helper
    $.fn.ajaxDataTable = function(options) {
        const settings = $.extend({
            ajax: {
                url: window.KewerAPI.baseUrl + options.endpoint,
                type: 'GET',
                data: options.params || {},
                dataSrc: function(json) {
                    return json.data || json || [];
                }
            },
            columns: options.columns || []
        }, options);

        $(this).DataTable(settings);
        return this;
    };

})(jQuery);
