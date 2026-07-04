<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Daftar</h3>
                        <p class="text-muted">Buat akun baru di MyWisata</p>
                    </div>
                    
                    <ul class="nav nav-tabs mb-4" id="registerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="wisatawan-tab" data-bs-toggle="tab" data-bs-target="#wisatawan" type="button" role="tab">Wisatawan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tourguide-tab" data-bs-toggle="tab" data-bs-target="#tourguide" type="button" role="tab">Tour Guide</button>
                        </li>
                    </ul>
                    
                    <form id="registerForm">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf_token) ?>">
                        <input type="hidden" name="role" id="role" value="wisatawan">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Saya setuju dengan <a href="#" class="text-decoration-none">Syarat & Ketentuan</a>
                            </label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Daftar
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Sudah punya akun? 
                            <a href="<?= View::url('auth/login') ?>" class="text-decoration-none fw-bold">Masuk</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#registerTabs button').on('click', function() {
        var role = $(this).attr('id').replace('-tab', '');
        $('#role').val(role);
    });
    
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var btn = $(this).find('button[type="submit"]');
        var originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Daftar...');
        
        ajax({
            url: '<?= View::url('auth/doRegister') ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = response.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        confirmButtonColor: '#0d6efd'
                    });
                }
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
