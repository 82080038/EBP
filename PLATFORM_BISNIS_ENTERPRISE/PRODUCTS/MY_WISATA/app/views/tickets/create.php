<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-4">Beli Tiket</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <?php if ($destination['main_image']): ?>
                            <img src="<?= View::asset('uploads/destinations/' . $destination['main_image']) ?>" class="rounded me-3" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/100" class="rounded me-3" style="width: 100px; height: 100px;">
                        <?php endif; ?>
                        <div>
                            <h3 class="mb-1"><?= View::e($destination['name']) ?></h3>
                            <p class="text-muted mb-0">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($destination['city']) ?>
                            </p>
                        </div>
                    </div>
                    
                    <form method="POST" action="<?= View::url('ticket/store') ?>">
                        <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
                        <input type="hidden" name="destination_id" value="<?= $destination['id'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="visit_date" class="form-label">Tanggal Kunjungan</label>
                                <input type="date" class="form-control" id="visit_date" name="visit_date" required min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Jumlah Tiket</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="50" required>
                            </div>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Rincian Biaya</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Harga per tiket:</span>
                                    <span><?= View::currency($destination['entry_fee']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Jumlah tiket:</span>
                                    <span id="quantityDisplay">1</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong id="totalDisplay"><?= View::currency($destination['entry_fee']) ?></strong>
                                </div>
                                <input type="hidden" name="total_amount" id="totalAmount" value="<?= $destination['entry_fee'] ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-ticket-alt me-2"></i>Beli Tiket
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Destinasi</h5>
                </div>
                <div class="card-body">
                    <h6>Deskripsi</h6>
                    <p class="text-muted small"><?= substr(View::e($destination['description']), 0, 200) ?>...</p>
                    
                    <h6>Jam Buka</h6>
                    <p><?= View::e($destination['opening_hours']) ?></p>
                    
                    <h6>Kontak</h6>
                    <p><?= View::e($destination['contact_phone']) ?></p>
                    
                    <h6>Rating</h6>
                    <div class="text-warning">
                        <i class="fas fa-star"></i> <?= number_format($destination['rating_avg'], 1) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var unitPrice = <?= $destination['entry_fee'] ?>;
var quantityInput = document.getElementById('quantity');
var quantityDisplay = document.getElementById('quantityDisplay');
var totalDisplay = document.getElementById('totalDisplay');
var totalAmount = document.getElementById('totalAmount');

function updateTotal() {
    var quantity = parseInt(quantityInput.value) || 0;
    var total = quantity * unitPrice;
    
    quantityDisplay.textContent = quantity;
    totalDisplay.textContent = formatCurrency(total);
    totalAmount.value = total;
}

function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

quantityInput.addEventListener('change', updateTotal);
quantityInput.addEventListener('input', updateTotal);
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
