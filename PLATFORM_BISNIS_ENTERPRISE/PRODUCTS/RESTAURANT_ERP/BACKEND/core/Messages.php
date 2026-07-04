<?php

/**
 * Class Messages
 * 
 * Menyimpan semua pesan response dalam bahasa Indonesia
 * untuk konsistensi dan kemudahan maintenance
 */
class Messages
{
    // ==================== GENERAL ====================
    
    // Success messages
    const SUCCESS = 'Berhasil';
    const SUCCESS_CREATED = 'Data berhasil dibuat';
    const SUCCESS_UPDATED = 'Data berhasil diperbarui';
    const SUCCESS_DELETED = 'Data berhasil dihapus';
    const SUCCESS_RETRIEVED = 'Data berhasil diambil';
    const SUCCESS_SAVED = 'Data berhasil disimpan';
    const SUCCESS_PROCESSED = 'Data berhasil diproses';
    
    // Error messages
    const ERROR_GENERAL = 'Terjadi kesalahan';
    const ERROR_NOT_FOUND = 'Data tidak ditemukan';
    const ERROR_INVALID = 'Data tidak valid';
    const ERROR_REQUIRED = 'Field wajib diisi';
    const ERROR_FAILED = 'Gagal memproses data';
    const ERROR_UNAUTHORIZED = 'Tidak memiliki akses';
    const ERROR_FORBIDDEN = 'Akses ditolak';
    const ERROR_SERVER = 'Terjadi kesalahan server';
    
    // ==================== AUTHENTICATION ====================
    
    const AUTH_LOGIN_SUCCESS = 'Login berhasil';
    const AUTH_LOGOUT_SUCCESS = 'Logout berhasil';
    const AUTH_TOKEN_INVALID = 'Token tidak valid';
    const AUTH_TOKEN_EXPIRED = 'Sesi telah berakhir';
    const AUTH_CREDENTIALS_INVALID = 'Username atau password salah';
    const AUTH_USER_NOT_FOUND = 'User tidak ditemukan';
    const AUTH_USER_INACTIVE = 'User tidak aktif';
    const AUTH_PERMISSION_DENIED = 'Permission ditolak';
    
    // ==================== VALIDATION ====================
    
    const VALIDATION_FAILED = 'Validasi gagal';
    const VALIDATION_EMAIL_INVALID = 'Format email salah';
    const VALIDATION_PHONE_INVALID = 'Format nomor telepon salah';
    const VALIDATION_PASSWORD_WEAK = 'Password terlalu lemah';
    const VALIDATION_VALUE_TOO_SMALL = 'Nilai terlalu kecil';
    const VALIDATION_VALUE_TOO_LARGE = 'Nilai terlalu besar';
    const VALIDATION_DUPLICATE = 'Data sudah ada';
    
    // ==================== ORDER ====================
    
    const ORDER_CREATED = 'Pesanan berhasil dibuat';
    const ORDER_UPDATED = 'Pesanan berhasil diperbarui';
    const ORDER_DELETED = 'Pesanan berhasil dihapus';
    const ORDER_CANCELLED = 'Pesanan berhasil dibatalkan';
    const ORDER_COMPLETED = 'Pesanan berhasil diselesaikan';
    const ORDER_NOT_FOUND = 'Pesanan tidak ditemukan';
    const ORDER_STATUS_UPDATED = 'Status pesanan berhasil diperbarui';
    const ORDER_ID_REQUIRED = 'ID pesanan wajib diisi';
    const ORDER_ITEMS_REQUIRED = 'Item pesanan wajib diisi';
    const ORDER_FAILED_CREATE = 'Gagal membuat pesanan';
    const ORDER_FAILED_UPDATE = 'Gagal memperbarui pesanan';
    
    // ==================== PRODUCT ====================
    
    const PRODUCT_CREATED = 'Produk berhasil dibuat';
    const PRODUCT_UPDATED = 'Produk berhasil diperbarui';
    const PRODUCT_DELETED = 'Produk berhasil dihapus';
    const PRODUCT_NOT_FOUND = 'Produk tidak ditemukan';
    const PRODUCT_ID_REQUIRED = 'ID produk wajib diisi';
    const PRODUCT_NAME_REQUIRED = 'Nama produk wajib diisi';
    const PRODUCT_PRICE_REQUIRED = 'Harga produk wajib diisi';
    const PRODUCT_FAILED_CREATE = 'Gagal membuat produk';
    const PRODUCT_FAILED_UPDATE = 'Gagal memperbarui produk';
    
    // ==================== CATEGORY ====================
    
    const CATEGORY_CREATED = 'Kategori berhasil dibuat';
    const CATEGORY_UPDATED = 'Kategori berhasil diperbarui';
    const CATEGORY_DELETED = 'Kategori berhasil dihapus';
    const CATEGORY_NOT_FOUND = 'Kategori tidak ditemukan';
    const CATEGORY_ID_REQUIRED = 'ID kategori wajib diisi';
    const CATEGORY_NAME_REQUIRED = 'Nama kategori wajib diisi';
    
    // ==================== INVENTORY ====================
    
    const INVENTORY_CREATED = 'Stok berhasil dibuat';
    const INVENTORY_UPDATED = 'Stok berhasil diperbarui';
    const INVENTORY_ADJUSTED = 'Stok berhasil disesuaikan';
    const INVENTORY_NOT_FOUND = 'Stok tidak ditemukan';
    const INVENTORY_ID_REQUIRED = 'ID stok wajib diisi';
    const INVENTORY_BRANCH_REQUIRED = 'ID cabang wajib diisi';
    const INVENTORY_PRODUCT_REQUIRED = 'ID produk wajib diisi';
    const INVENTORY_QUANTITY_REQUIRED = 'Jumlah wajib diisi';
    const INVENTORY_TYPE_REQUIRED = 'Tipe wajib diisi (IN, OUT, atau ADJUSTMENT)';
    const INVENTORY_FAILED_CREATE = 'Gagal membuat stok atau stok sudah ada untuk produk ini';
    const INVENTORY_FAILED_UPDATE = 'Gagal memperbarui stok';
    const INVENTORY_INSUFFICIENT = 'Stok tidak mencukupi';
    
    // ==================== KITCHEN ====================
    
    const KITCHEN_ORDER_CREATED = 'Kitchen order berhasil dibuat';
    const KITCHEN_ORDER_UPDATED = 'Kitchen order berhasil diperbarui';
    const KITCHEN_ORDER_NOT_FOUND = 'Kitchen order tidak ditemukan';
    const KITCHEN_ORDER_ID_REQUIRED = 'ID kitchen order wajib diisi';
    const KITCHEN_ORDER_ID_REQUIRED_ITEM = 'ID kitchen order item wajib diisi';
    const KITCHEN_STATUS_REQUIRED = 'Status wajib diisi';
    const KITCHEN_PRIORITY_REQUIRED = 'Priority wajib diisi';
    const KITCHEN_STATUS_INVALID = 'Status tidak valid';
    const KITCHEN_PRIORITY_INVALID = 'Priority tidak valid';
    const KITCHEN_FAILED_CREATE = 'Gagal membuat kitchen order';
    const KITCHEN_FAILED_UPDATE_STATUS = 'Gagal memperbarui status kitchen order';
    const KITCHEN_FAILED_UPDATE_PRIORITY = 'Gagal memperbarui priority kitchen order';
    const KITCHEN_FAILED_UPDATE_ITEM = 'Gagal memperbarui status item kitchen order';
    
    // ==================== TABLE ====================
    
    const TABLE_CREATED = 'Meja berhasil dibuat';
    const TABLE_UPDATED = 'Meja berhasil diperbarui';
    const TABLE_DELETED = 'Meja berhasil dihapus';
    const TABLE_NOT_FOUND = 'Meja tidak ditemukan';
    const TABLE_STATUS_UPDATED = 'Status meja berhasil diperbarui';
    const TABLE_ID_REQUIRED = 'ID meja wajib diisi';
    const TABLE_STATUS_REQUIRED = 'Status meja wajib diisi';
    
    // ==================== RESERVATION ====================
    
    const RESERVATION_CREATED = 'Reservasi berhasil dibuat';
    const RESERVATION_UPDATED = 'Reservasi berhasil diperbarui';
    const RESERVATION_DELETED = 'Reservasi berhasil dihapus';
    const RESERVATION_NOT_FOUND = 'Reservasi tidak ditemukan';
    const RESERVATION_ID_REQUIRED = 'ID reservasi wajib diisi';
    const RESERVATION_DATE_REQUIRED = 'Tanggal reservasi wajib diisi';
    const RESERVATION_TIME_REQUIRED = 'Waktu reservasi wajib diisi';
    
    // ==================== CUSTOMER ====================
    
    const CUSTOMER_CREATED = 'Pelanggan berhasil dibuat';
    const CUSTOMER_UPDATED = 'Pelanggan berhasil diperbarui';
    const CUSTOMER_DELETED = 'Pelanggan berhasil dihapus';
    const CUSTOMER_NOT_FOUND = 'Pelanggan tidak ditemukan';
    const CUSTOMER_ID_REQUIRED = 'ID pelanggan wajib diisi';
    const CUSTOMER_NAME_REQUIRED = 'Nama pelanggan wajib diisi';
    
    // ==================== USER ====================
    
    const USER_CREATED = 'User berhasil dibuat';
    const USER_UPDATED = 'User berhasil diperbarui';
    const USER_DELETED = 'User berhasil dihapus';
    const USER_NOT_FOUND = 'User tidak ditemukan';
    const USER_ID_REQUIRED = 'ID user wajib diisi';
    const USER_USERNAME_REQUIRED = 'Username wajib diisi';
    const USER_EMAIL_REQUIRED = 'Email wajib diisi';
    const USER_PASSWORD_REQUIRED = 'Password wajib diisi';
    
    // ==================== ROLE ====================
    
    const ROLE_CREATED = 'Role berhasil dibuat';
    const ROLE_UPDATED = 'Role berhasil diperbarui';
    const ROLE_DELETED = 'Role berhasil dihapus';
    const ROLE_NOT_FOUND = 'Role tidak ditemukan';
    const ROLE_ID_REQUIRED = 'ID role wajib diisi';
    const ROLE_NAME_REQUIRED = 'Nama role wajib diisi';
    
    // ==================== PERMISSION ====================
    
    const PERMISSION_GRANTED = 'Permission berhasil diberikan';
    const PERMISSION_REVOKED = 'Permission berhasil dicabut';
    const PERMISSION_NOT_FOUND = 'Permission tidak ditemukan';
    const PERMISSION_ID_REQUIRED = 'ID permission wajib diisi';
    
    // ==================== TENANT ====================
    
    const TENANT_CREATED = 'Tenant berhasil dibuat';
    const TENANT_UPDATED = 'Tenant berhasil diperbarui';
    const TENANT_DELETED = 'Tenant berhasil dihapus';
    const TENANT_NOT_FOUND = 'Tenant tidak ditemukan';
    const TENANT_ID_REQUIRED = 'ID tenant wajib diisi';
    const TENANT_BRANCH_REQUIRED = 'ID cabang wajib diisi';
    
    // ==================== BRANCH ====================
    
    const BRANCH_CREATED = 'Cabang berhasil dibuat';
    const BRANCH_UPDATED = 'Cabang berhasil diperbarui';
    const BRANCH_DELETED = 'Cabang berhasil dihapus';
    const BRANCH_NOT_FOUND = 'Cabang tidak ditemukan';
    const BRANCH_ID_REQUIRED = 'ID cabang wajib diisi';
    
    // ==================== PAYMENT ====================
    
    const PAYMENT_CREATED = 'Pembayaran berhasil dibuat';
    const PAYMENT_UPDATED = 'Pembayaran berhasil diperbarui';
    const PAYMENT_NOT_FOUND = 'Pembayaran tidak ditemukan';
    const PAYMENT_ID_REQUIRED = 'ID pembayaran wajib diisi';
    const PAYMENT_AMOUNT_REQUIRED = 'Jumlah pembayaran wajib diisi';
    const PAYMENT_METHOD_REQUIRED = 'Metode pembayaran wajib diisi';
    const PAYMENT_FAILED = 'Gagal memproses pembayaran';
    
    // ==================== SETTINGS ====================
    
    const SETTING_CREATED = 'Pengaturan berhasil dibuat';
    const SETTING_UPDATED = 'Pengaturan berhasil diperbarui';
    const SETTING_DELETED = 'Pengaturan berhasil dihapus';
    const SETTING_NOT_FOUND = 'Pengaturan tidak ditemukan';
    const SETTING_ID_REQUIRED = 'ID pengaturan wajib diisi';
    const SETTING_KEY_REQUIRED = 'Key pengaturan wajib diisi';
    const SETTING_VALUE_REQUIRED = 'Value pengaturan wajib diisi';
    const SETTING_PREFIX_REQUIRED = 'Prefix wajib diisi';
    const SETTING_FAILED_CREATE = 'Gagal membuat pengaturan atau key sudah ada';
    const SETTING_FAILED_UPDATE = 'Gagal memperbarui pengaturan';
    const SETTING_FAILED_SAVE = 'Gagal menyimpan pengaturan';
    const SETTING_FAILED_DELETE = 'Gagal menghapus pengaturan';
    const SETTING_FAILED_INIT = 'Gagal menginisialisasi pengaturan default';
    
    // ==================== REPORT ====================
    
    const REPORT_GENERATED = 'Laporan berhasil dibuat';
    const REPORT_NOT_FOUND = 'Laporan tidak ditemukan';
    const REPORT_TYPE_INVALID = 'Tipe laporan tidak valid';
    const REPORT_FORMAT_UNSUPPORTED = 'Format tidak didukung';
    const REPORT_TYPE_REQUIRED = 'Tipe laporan wajib diisi';
    
    // ==================== INTEGRATION ====================
    
    const INTEGRATION_CREATED = 'Integrasi berhasil dibuat';
    const INTEGRATION_UPDATED = 'Integrasi berhasil diperbarui';
    const INTEGRATION_DELETED = 'Integrasi berhasil dihapus';
    const INTEGRATION_TYPE_REQUIRED = 'Tipe integrasi wajib diisi';
    const INTEGRATION_EXTERNAL_ID_REQUIRED = 'ID eksternal wajib diisi';
    const INTEGRATION_LOGS_RETRIEVED = 'Log integrasi berhasil diambil';
    
    // ==================== KIOSK ====================
    
    const KIOSK_TENANT_BRANCH_REQUIRED = 'ID Tenant dan ID Cabang wajib diisi';
    const KIOSK_ORDER_CREATED = 'Pesanan kiosk berhasil dibuat';
    const KIOSK_MENU_RETRIEVED = 'Menu kiosk berhasil diambil';
    
    // ==================== MOBILE ====================
    
    const MOBILE_MENU_RETRIEVED = 'Menu mobile berhasil diambil';
    const MOBILE_ORDER_RETRIEVED = 'Pesanan mobile berhasil diambil';
    
    // ==================== ACCOUNTING ====================
    
    const ACCOUNTING_JOURNAL_CREATED = 'Jurnal berhasil dibuat';
    const ACCOUNTING_JOURNAL_POSTED = 'Jurnal berhasil diposting';
    const ACCOUNTING_JOURNAL_NOT_FOUND = 'Jurnal tidak ditemukan';
    const ACCOUNTING_ORDER_ID_REQUIRED = 'ID pesanan wajib diisi';
    const ACCOUNTING_COST_CENTER_REQUIRED = 'ID cost center wajib diisi';
    
    // ==================== CRM ====================
    
    const CRM_CUSTOMER_ID_REQUIRED = 'ID pelanggan wajib diisi';
    const CRM_PRODUCT_ID_REQUIRED = 'ID produk wajib diisi';
    const CRM_CREDIT_ID_REQUIRED = 'ID kredit wajib diisi';
    const CRM_AMOUNT_REQUIRED = 'Jumlah wajib diisi';
    const CRM_PROMOTION_ID_REQUIRED = 'ID promosi wajib diisi';
    
    // ==================== MAINTENANCE ====================
    
    const MAINTENANCE_ASSET_ID_REQUIRED = 'ID aset wajib diisi';
    const MAINTENANCE_WORK_ORDER_ID_REQUIRED = 'ID work order wajib diisi';
    
    // ==================== WHATSAPP ====================
    
    const WHATSAPP_REPORT_TYPE_REQUIRED = 'Tipe laporan wajib diisi';
    const WHATSAPP_MESSAGE_SENT = 'Pesan WhatsApp berhasil dikirim';
    const WHATSAPP_MESSAGE_FAILED = 'Gagal mengirim pesan WhatsApp';
    
    // ==================== QUALITY ====================
    
    const QUALITY_CHECK_CREATED = 'Quality check berhasil dibuat';
    const QUALITY_PROTOCOL_CREATED = 'Quality protocol berhasil dibuat';
    const QUALITY_INCIDENT_CREATED = 'Quality incident berhasil dibuat';
    
    // ==================== AI ====================
    
    const AI_MENU_ANALYSIS_RETRIEVED = 'Analisis menu berhasil diambil';
    const AI_FRAUD_ALERTS_RETRIEVED = 'Alert fraud berhasil diambil';
    const AI_EXECUTIVE_INSIGHTS_RETRIEVED = 'Insight eksekutif berhasil diambil';
    
    // ==================== SUPPLY CHAIN ====================
    
    const SUPPLIER_CREATED = 'Supplier berhasil dibuat';
    const SUPPLIER_UPDATED = 'Supplier berhasil diperbarui';
    const SUPPLIER_DELETED = 'Supplier berhasil dihapus';
    const SUPPLIER_NOT_FOUND = 'Supplier tidak ditemukan';
    
    // ==================== DELIVERY ====================
    
    const DELIVERY_CREATED = 'Pengiriman berhasil dibuat';
    const DELIVERY_UPDATED = 'Pengiriman berhasil diperbarui';
    const DELIVERY_STATUS_UPDATED = 'Status pengiriman berhasil diperbarui';
    const DELIVERY_NOT_FOUND = 'Pengiriman tidak ditemukan';
    
    // ==================== HR ====================
    
    const EMPLOYEE_CREATED = 'Karyawan berhasil dibuat';
    const EMPLOYEE_UPDATED = 'Karyawan berhasil diperbarui';
    const EMPLOYEE_DELETED = 'Karyawan berhasil dihapus';
    const EMPLOYEE_NOT_FOUND = 'Karyawan tidak ditemukan';
    const EMPLOYEE_ID_REQUIRED = 'ID karyawan wajib diisi';
    
    // ==================== SUSTAINABILITY ====================
    
    const SUSTAINABILITY_METRIC_CREATED = 'Metrik sustainability berhasil dibuat';
    const SUSTAINABILITY_REPORT_GENERATED = 'Laporan sustainability berhasil dibuat';
    
    // ==================== OFFLINE ====================
    
    const OFFLINE_DATA_SYNCED = 'Data offline berhasil disinkronisasi';
    const OFFLINE_DATA_FAILED = 'Gagal menyinkronisasi data offline';
    
    // ==================== LOCATION ====================
    
    const LOCATION_CREATED = 'Lokasi berhasil dibuat';
    const LOCATION_UPDATED = 'Lokasi berhasil diperbarui';
    const LOCATION_DELETED = 'Lokasi berhasil dihapus';
    const LOCATION_NOT_FOUND = 'Lokasi tidak ditemukan';
}
