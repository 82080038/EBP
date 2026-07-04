<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Booking Saya</h1>
        <div class="btn-group">
            <a href="<?= View::url('bookings?status=all') ?>" class="btn btn-outline-primary <?= $status_filter === 'all' ? 'active' : '' ?>">Semua</a>
            <a href="<?= View::url('bookings?status=pending') ?>" class="btn btn-outline-primary <?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="<?= View::url('bookings?status=confirmed') ?>" class="btn btn-outline-primary <?= $status_filter === 'confirmed' ? 'active' : '' ?>">Aktif</a>
            <a href="<?= View::url('bookings?status=completed') ?>" class="btn btn-outline-primary <?= $status_filter === 'completed' ? 'active' : '' ?>">Selesai</a>
            <a href="<?= View::url('bookings?status=cancelled') ?>" class="btn btn-outline-primary <?= $status_filter === 'cancelled' ? 'active' : '' ?>">Dibatalkan</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <p class="text-muted text-center py-4">Tidak ada booking ditemukan.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Tour Guide</th>
                                <th>Tanggal</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= View::e($booking['booking_code']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($booking['avatar']): ?>
                                            <img src="<?= View::asset('uploads/avatars/' . $booking['avatar']) ?>" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= View::e($booking['guide_name']) ?></strong>
                                            <div class="text-warning small">
                                                <i class="fas fa-star"></i> <?= number_format($booking['rating_avg'], 1) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= View::date($booking['booking_date']) ?></td>
                                <td><?= $booking['duration_hours'] ?> jam</td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    if ($booking['status'] === 'pending') $statusClass = 'warning';
                                    elseif ($booking['status'] === 'confirmed') $statusClass = 'success';
                                    elseif ($booking['status'] === 'completed') $statusClass = 'primary';
                                    elseif ($booking['status'] === 'cancelled') $statusClass = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= View::e(ucfirst($booking['status'])) ?>
                                    </span>
                                </td>
                                <td><?= View::currency($booking['total_amount']) ?></td>
                                <td>
                                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="cancelBooking(<?= $booking['id'] ?>)">
                                            <i class="fas fa-times"></i> Batalkan
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function cancelBooking(id) {
    Swal.fire({
        title: 'Batalkan Booking?',
        text: 'Silakan berikan alasan pembatalan:',
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Alasan pembatalan...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            var formData = new FormData();
            formData.append('booking_id', id);
            formData.append('reason', result.value);
            formData.append('csrf_token', '<?= Middleware::csrfToken() ?>');
            
            fetch(window.APP_URL + 'booking/cancel', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#0d6efd'
                    });
                }
            });
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
