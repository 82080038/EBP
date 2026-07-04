<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tiket Saya</h1>
        <div class="btn-group">
            <a href="<?= View::url('tickets?status=all') ?>" class="btn btn-outline-primary <?= $status_filter === 'all' ? 'active' : '' ?>">Semua</a>
            <a href="<?= View::url('tickets?status=pending') ?>" class="btn btn-outline-primary <?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="<?= View::url('tickets?status=paid') ?>" class="btn btn-outline-primary <?= $status_filter === 'paid' ? 'active' : '' ?>">Paid</a>
            <a href="<?= View::url('tickets?status=used') ?>" class="btn btn-outline-primary <?= $status_filter === 'used' ? 'active' : '' ?>">Used</a>
            <a href="<?= View::url('tickets?status=cancelled') ?>" class="btn btn-outline-primary <?= $status_filter === 'cancelled' ? 'active' : '' ?>">Dibatalkan</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($tickets)): ?>
                <p class="text-muted text-center py-4">Tidak ada tiket ditemukan.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($tickets as $ticket): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <?php if ($ticket['main_image']): ?>
                                        <img src="<?= View::asset('uploads/destinations/' . $ticket['main_image']) ?>" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/80" class="rounded me-3" style="width: 80px; height: 80px;">
                                    <?php endif; ?>
                                    <div>
                                        <h5 class="card-title mb-1"><?= View::e($ticket['destination_name']) ?></h5>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-map-marker-alt me-1"></i><?= View::e($ticket['city']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Kode Order</small>
                                        <p class="mb-0 fw-bold"><?= View::e($ticket['order_code']) ?></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Tanggal Kunjungan</small>
                                        <p class="mb-0 fw-bold"><?= View::date($ticket['visit_date']) ?></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Jumlah Tiket</small>
                                        <p class="mb-0 fw-bold"><?= $ticket['quantity'] ?></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Total</small>
                                        <p class="mb-0 fw-bold text-success"><?= View::currency($ticket['total_amount']) ?></p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <?php
                                    $statusClass = 'secondary';
                                    if ($ticket['status'] === 'pending') $statusClass = 'warning';
                                    elseif ($ticket['status'] === 'paid') $statusClass = 'success';
                                    elseif ($ticket['status'] === 'used') $statusClass = 'primary';
                                    elseif ($ticket['status'] === 'cancelled') $statusClass = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= View::e(ucfirst($ticket['status'])) ?>
                                    </span>
                                    <?php if ($ticket['status'] === 'paid'): ?>
                                        <button class="btn btn-sm btn-success">
                                            <i class="fas fa-qrcode me-1"></i>Tiket
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
