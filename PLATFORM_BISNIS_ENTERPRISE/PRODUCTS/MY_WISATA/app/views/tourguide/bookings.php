<?php include APP_ROOT . '/app/views/layouts/tourguide_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Booking Saya</h2>
        <div class="btn-group">
            <a href="<?= View::url('tourguide/bookings?status=all') ?>" class="btn btn-outline-primary <?= $status_filter === 'all' ? 'active' : '' ?>">Semua</a>
            <a href="<?= View::url('tourguide/bookings?status=pending') ?>" class="btn btn-outline-primary <?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="<?= View::url('tourguide/bookings?status=confirmed') ?>" class="btn btn-outline-primary <?= $status_filter === 'confirmed' ? 'active' : '' ?>">Aktif</a>
            <a href="<?= View::url('tourguide/bookings?status=completed') ?>" class="btn btn-outline-primary <?= $status_filter === 'completed' ? 'active' : '' ?>">Selesai</a>
            <a href="<?= View::url('tourguide/bookings?status=cancelled') ?>" class="btn btn-outline-primary <?= $status_filter === 'cancelled' ? 'active' : '' ?>">Dibatalkan</a>
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
                                <th>Nama Wisatawan</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= View::e($booking['booking_code']) ?></td>
                                <td><?= View::e($booking['user_name']) ?></td>
                                <td><?= View::date($booking['booking_date']) ?></td>
                                <td><?= View::e($booking['booking_time']) ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    if ($booking['status'] === 'pending') $statusClass = 'warning';
                                    elseif ($booking['status'] === 'confirmed') $statusClass = 'success';
                                    elseif ($booking['status'] === 'completed') $statusClass = 'primary';
                                    elseif ($booking['status'] === 'cancelled') $statusClass = 'danger';
                                    elseif ($booking['status'] === 'rejected') $statusClass = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= View::e(ucfirst($booking['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-success" onclick="acceptBooking(<?= $booking['id'] ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectBooking(<?= $booking['id'] ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-info" onclick="viewDetails(<?= $booking['id'] ?>)">
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

function viewDetails(id) {
    // TODO: Implement booking details modal
    Swal.fire({
        icon: 'info',
        title: 'Detail Booking',
        text: 'Fitur detail booking akan segera tersedia',
        confirmButtonColor: '#0d6efd'
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/tourguide_footer.php'; ?>
