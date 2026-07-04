<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Masuk</h3>
                        <p class="text-muted">Selamat datang kembali di MyWisata</p>
                    </div>
                    
                    <form id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf_token) ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="true">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <a href="<?= View::url('auth/forgot-password') ?>" class="text-decoration-none">Lupa password?</a>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Belum punya akun? 
                            <a href="<?= View::url('auth/register') ?>" class="text-decoration-none fw-bold">Daftar sekarang</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <p class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Admin: admin@mywisata.com / admin123
                </p>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var btn = $(this).find('button[type="submit"]');
        var originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Masuk...');
        
        ajax({
            url: '<?= View::url('auth/doLogin') ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
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
