<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-key fa-3x text-warning mb-3"></i>
                        <h3 class="fw-bold">Lupa Password</h3>
                        <p class="text-muted">Masukkan email untuk reset password</p>
                    </div>
                    
                    <form id="forgotPasswordForm">
                        <input type="hidden" name="csrf_token" value="<?= View::e($csrf_token) ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <a href="<?= View::url('auth/login') ?>" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#forgotPasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var btn = $(this).find('button[type="submit"]');
        var originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...');
        
        ajax({
            url: '<?= View::url('auth/doForgotPassword') ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    confirmButtonColor: '#0d6efd'
                }).then(function() {
                    window.location.href = '<?= View::url('auth/login') ?>';
                });
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
