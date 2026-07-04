<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-4">Booking Tour Guide</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <?php if ($guide['avatar']): ?>
                            <img src="<?= View::asset('uploads/avatars/' . $guide['avatar']) ?>" class="rounded-circle me-3" style="width: 80px; height: 80px; object-fit: cover;">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/80" class="rounded-circle me-3" style="width: 80px; height: 80px;">
                        <?php endif; ?>
                        <div>
                            <h3 class="mb-1"><?= View::e($guide['name']) ?></h3>
                            <div class="text-warning mb-1">
                                <i class="fas fa-star"></i> <?= number_format($guide['rating_avg'], 1) ?>
                            </div>
                            <p class="text-muted mb-0"><?= View::e($guide['city']) ?></p>
                        </div>
                    </div>
                    
                    <form method="POST" action="<?= View::url('booking/store') ?>">
                        <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
                        <input type="hidden" name="guide_id" value="<?= $guide['id'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="booking_date" class="form-label">Tanggal Booking</label>
                                <input type="date" class="form-control" id="booking_date" name="booking_date" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="booking_time" class="form-label">Waktu</label>
                                <input type="time" class="form-control" id="booking_time" name="booking_time" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="duration_hours" class="form-label">Durasi (Jam)</label>
                                <input type="number" class="form-control" id="duration_hours" name="duration_hours" value="4" min="1" max="12" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="participants" class="form-label">Jumlah Peserta</label>
                                <input type="number" class="form-control" id="participants" name="participants" value="1" min="1" max="20" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Permintaan Khusus (Opsional)</label>
                            <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Rincian Biaya</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tarif per jam:</span>
                                    <span><?= View::currency($guide['hourly_rate']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Durasi:</span>
                                    <span id="durationDisplay">4 jam</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong id="totalDisplay"><?= View::currency($guide['hourly_rate'] * 4) ?></strong>
                                </div>
                                <input type="hidden" name="total_amount" id="totalAmount" value="<?= $guide['hourly_rate'] * 4 ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-check me-2"></i>Konfirmasi Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Guide</h5>
                </div>
                <div class="card-body">
                    <h6>Tentang Guide</h6>
                    <p class="text-muted"><?= View::e($guide['bio']) ?></p>
                    
                    <h6>Pengalaman</h6>
                    <p><?= $guide['experience_years'] ?> tahun</p>
                    
                    <h6>Harga</h6>
                    <p><?= View::currency($guide['hourly_rate']) ?>/jam</p>
                    <p><?= View::currency($guide['daily_rate']) ?>/hari</p>
                    
                    <h6>Bahasa</h6>
                    <p class="text-muted">Bahasa yang dikuasai akan ditampilkan di sini</p>
                    
                    <h6>Spesialisasi</h6>
                    <p class="text-muted">Spesialisasi akan ditampilkan di sini</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var hourlyRate = <?= $guide['hourly_rate'] ?>;
var durationInput = document.getElementById('duration_hours');
var durationDisplay = document.getElementById('durationDisplay');
var totalDisplay = document.getElementById('totalDisplay');
var totalAmount = document.getElementById('totalAmount');

function updateTotal() {
    var duration = parseInt(durationInput.value) || 0;
    var total = duration * hourlyRate;
    
    durationDisplay.textContent = duration + ' jam';
    totalDisplay.textContent = formatCurrency(total);
    totalAmount.value = total;
}

function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

durationInput.addEventListener('change', updateTotal);
durationInput.addEventListener('input', updateTotal);
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
