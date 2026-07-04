<?php include APP_ROOT . '/app/views/layouts/tourguide_header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Booking Aktif</h5>
                    <h2 class="display-4"><?= $stats['active_bookings'] ?></h2>
                    <small>Booking dikonfirmasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Permintaan Baru</h5>
                    <h2 class="display-4"><?= $stats['pending_bookings'] ?></h2>
                    <small>Menunggu respon</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Tour Selesai</h5>
                    <h2 class="display-4"><?= $stats['completed_tours'] ?></h2>
                    <small>Total tour selesai</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Rating</h5>
                    <h2 class="display-4"><?= number_format($stats['rating_avg'], 1) ?></h2>
                    <small>Rata-rata rating</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Earnings -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pendapatan Bulan Ini</h5>
                    <h2 class="text-success"><?= View::currency($earnings['total']) ?></h2>
                    <small class="text-muted"><?= $earnings['count'] ?> transaksi</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Bookings -->
    <?php if (!empty($pending_bookings)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">Permintaan Booking Baru (<?= count($pending_bookings) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($pending_bookings as $booking): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= View::e($booking['user_name']) ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i><?= View::date($booking['booking_date']) ?>
                                        <i class="fas fa-clock ms-2 me-1"></i><?= View::e($booking['booking_time']) ?>
                                    </small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-success" onclick="acceptBooking(<?= $booking['id'] ?>)">
                                        <i class="fas fa-check"></i> Terima
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="rejectBooking(<?= $booking['id'] ?>)">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Today's Bookings -->
    <?php if (!empty($today_bookings)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Jadwal Hari Ini (<?= count($today_bookings) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($today_bookings as $booking): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="mb-1"><?= View::e($booking['user_name']) ?></h6>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i><?= View::e($booking['booking_time']) ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Reviews -->
    <?php if (!empty($recent_reviews)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Review Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($recent_reviews as $review): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1"><?= View::e($review['user_name']) ?></h6>
                                    <div class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $review['rating'] ? '' : 'far' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?= View::date($review['created_at']) ?></small>
                            </div>
                            <p class="mb-0 mt-2"><?= View::e($review['comment']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function acceptBooking(id) {
    Swal.fire({
        title: 'Terima Booking?',
        text: 'Apakah Anda yakin ingin menerima booking ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Terima',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            ajax({
                url: window.APP_URL + 'tourguide/acceptBooking',
                method: 'POST',
                data: { booking_id: id },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: '#0d6efd'
                        });
                    }
                }
            });
        }
    });
}

function rejectBooking(id) {
    Swal.fire({
        title: 'Tolak Booking?',
        text: 'Silakan berikan alasan penolakan:',
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Alasan penolakan...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            ajax({
                url: window.APP_URL + 'tourguide/rejectBooking',
                method: 'POST',
                data: { booking_id: id, reason: result.value },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonColor: '#0d6efd'
                        });
                    }
                }
            });
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/tourguide_footer.php'; ?>
