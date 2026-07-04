<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pengguna</h5>
                    <h2 class="display-4"><?= $stats['total_users'] ?></h2>
                    <small>Pengguna aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Tour Guide</h5>
                    <h2 class="display-4"><?= $stats['total_guides'] ?></h2>
                    <small>Guide terverifikasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Destinasi</h5>
                    <h2 class="display-4"><?= $stats['total_destinations'] ?></h2>
                    <small>Destinasi aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Transaksi</h5>
                    <h2 class="display-4"><?= $stats['total_transactions'] ?></h2>
                    <small>Transaksi sukses</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Approvals -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">Guide Pending</h5>
                    <h3><?= $stats['pending_guides'] ?></h3>
                    <a href="<?= View::url('admin/guides') ?>" class="btn btn-sm btn-warning">Review</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">Hotel Pending</h5>
                    <h3><?= $stats['pending_hotels'] ?></h3>
                    <a href="#" class="btn btn-sm btn-warning">Review</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">Restoran Pending</h5>
                    <h3><?= $stats['pending_restaurants'] ?></h3>
                    <a href="#" class="btn btn-sm btn-warning">Review</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pendapatan Bulan Ini</h5>
                    <h2 class="text-success"><?= View::currency($monthly_revenue) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Booking Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_bookings)): ?>
                        <p class="text-muted">Belum ada booking.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kode Booking</th>
                                        <th>User</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><?= View::e($booking['booking_code']) ?></td>
                                        <td><?= View::e($booking['user_name']) ?></td>
                                        <td><?= View::date($booking['booking_date']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : 'warning' ?>">
                                                <?= View::e($booking['status']) ?>
                                            </span>
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
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
