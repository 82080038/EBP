<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Pengaturan Sistem</h2>
    
    <form method="POST" action="<?= View::url('admin/settings') ?>">
        <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Aplikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Nama Aplikasi</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" value="<?= View::e($settings['site_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="site_description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="site_description" name="site_description" rows="3"><?= View::e($settings['site_description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Kontak</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Email Kontak</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= View::e($settings['contact_email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?= View::e($settings['contact_phone'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Fitur</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="enable_ai_chat" name="enable_ai_chat" value="1" <?= ($settings['enable_ai_chat'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enable_ai_chat">Aktifkan AI Chat</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="enable_audio_guide" name="enable_audio_guide" value="1" <?= ($settings['enable_audio_guide'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enable_audio_guide">Aktifkan Audio Guide</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="enable_notifications" name="enable_notifications" value="1" <?= ($settings['enable_notifications'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="enable_notifications">Aktifkan Notifikasi</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="maintenance_mode">Mode Maintenance</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pengaturan Upload</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="max_upload_size" class="form-label">Max Upload Size (bytes)</label>
                            <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" value="<?= View::e($settings['max_upload_size'] ?? '5242880') ?>">
                            <small class="text-muted">Default: 5242880 (5MB)</small>
                        </div>
                        <div class="mb-3">
                            <label for="items_per_page" class="form-label">Items Per Page</label>
                            <input type="number" class="form-control" id="items_per_page" name="items_per_page" value="<?= View::e($settings['items_per_page'] ?? '20') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
