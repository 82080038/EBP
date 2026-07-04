<?php include APP_ROOT . '/app/views/layouts/tourguide_header.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Pendapatan Saya</h2>
    
    <!-- Earnings Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Bulan Ini</h5>
                    <h3 class="display-6"><?= View::currency($monthly_earnings['total']) ?></h3>
                    <small><?= $monthly_earnings['count'] ?> transaksi</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Tahun Ini</h5>
                    <h3 class="display-6"><?= View::currency($yearly_earnings['total']) ?></h3>
                    <small><?= $yearly_earnings['count'] ?> transaksi</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pendapatan</h5>
                    <h3 class="display-6"><?= View::currency($total_earnings['total']) ?></h3>
                    <small><?= $total_earnings['count'] ?> transaksi</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transaction History -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Riwayat Transaksi</h5>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <p class="text-muted text-center py-4">Belum ada transaksi.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?= View::e($transaction['booking_code']) ?></td>
                                <td><?= View::date($transaction['created_at']) ?></td>
                                <td><?= View::currency($transaction['net_amount']) ?></td>
                                <td>
                                    <span class="badge bg-success">Paid</span>
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

<?php include APP_ROOT . '/app/views/layouts/tourguide_footer.php'; ?>
