<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-4">Pembayaran</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Rincian Pembayaran</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1">Kode Transaksi:</p>
                            <p class="fw-bold mb-3"><?= View::e($transaction['transaction_code']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1">Tanggal:</p>
                            <p class="fw-bold mb-3"><?= View::date($transaction['created_at']) ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?= View::currency($transaction['gross_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Diskon:</span>
                        <span><?= View::currency($transaction['discount_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pajak:</span>
                        <span><?= View::currency($transaction['tax_amount']) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong class="text-success"><?= View::currency($transaction['net_amount']) ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Metode Pembayaran</h5>
                    <form id="paymentForm">
                        <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
                        <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="transfer" value="transfer" required>
                            <label class="form-check-label" for="transfer">
                                <i class="fas fa-university me-2"></i>Transfer Bank
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="ewallet" value="ewallet">
                            <label class="form-check-label" for="ewallet">
                                <i class="fas fa-wallet me-2"></i>E-Wallet (GoPay, OVO, Dana)
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card">
                            <label class="form-check-label" for="credit_card">
                                <i class="fas fa-credit-card me-2"></i>Kartu Kredit/Debit
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="qris" value="qris">
                            <label class="form-check-label" for="qris">
                                <i class="fas fa-qrcode me-2"></i>QRIS
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-lock me-2"></i>Bayar Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi</h5>
                </div>
                <div class="card-body">
                    <h6>Keamanan Pembayaran</h6>
                    <p class="small text-muted">Pembayaran Anda aman dengan enkripsi SSL 256-bit.</p>
                    
                    <h6>Bantuan</h6>
                    <p class="small text-muted">Jika mengalami masalah, hubungi customer service kami.</p>
                    
                    <h6>Kebijakan Refund</h6>
                    <p class="small text-muted">Refund tersedia sesuai dengan kebijakan masing-masing layanan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    Swal.fire({
        title: 'Konfirmasi Pembayaran',
        text: 'Apakah Anda yakin ingin melanjutkan pembayaran?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Bayar',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch(window.APP_URL + 'payment/process', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = window.APP_URL + 'home';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#0d6efd'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan koneksi',
                    confirmButtonColor: '#0d6efd'
                });
            });
        }
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
