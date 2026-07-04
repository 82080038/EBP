<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Laporan & Analitik</h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="display-4"><?= $stats['total_users'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Bookings</h5>
                    <h2 class="display-4"><?= $stats['total_bookings'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Tiket</h5>
                    <h2 class="display-4"><?= $stats['total_tickets'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <h2 class="display-4"><?= View::currency($stats['total_revenue']) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pendapatan Bulanan</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px; display: flex; align-items: flex-end; justify-content: space-around; padding: 20px;">
                        <?php if (empty($monthly_revenue)): ?>
                            <p class="text-muted text-center w-100">Belum ada data pendapatan.</p>
                        <?php else: ?>
                            <?php foreach (array_reverse($monthly_revenue) as $month): ?>
                            <div style="text-align: center;">
                                <div style="height: <?= min($month['revenue'] / 1000000 * 200, 200) ?>px; width: 40px; background-color: #0d6efd; margin: 0 auto;"></div>
                                <small><?= substr($month['month'], 5) ?></small>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Destinations -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Destinasi</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($top_destinations)): ?>
                        <p class="text-muted">Belum ada data.</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_destinations as $dest): ?>
                                <tr>
                                    <td><?= View::e($dest['name']) ?></td>
                                    <td><?= $dest['order_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Top Tour Guides -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Tour Guide</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($top_guides)): ?>
                        <p class="text-muted">Belum ada data.</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Booking</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_guides as $guide): ?>
                                <tr>
                                    <td><?= View::e($guide['name']) ?></td>
                                    <td><?= $guide['booking_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Export Laporan</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipe Laporan</label>
                                <select class="form-select" name="type">
                                    <option value="revenue">Pendapatan</option>
                                    <option value="bookings">Booking</option>
                                    <option value="tickets">Tiket</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">Export</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
