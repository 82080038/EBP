<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Destinasi</h2>
        <a href="#" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Destinasi
        </a>
    </div>
    
    <!-- Destinations Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Kota</th>
                            <th>Harga Tiket</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinations as $dest): ?>
                        <tr>
                            <td><?= $dest['id'] ?></td>
                            <td><?= View::e($dest['name']) ?></td>
                            <td><?= View::e($dest['category_name']) ?></td>
                            <td><?= View::e($dest['city']) ?></td>
                            <td><?= View::currency($dest['entry_fee']) ?></td>
                            <td>
                                <i class="fas fa-star text-warning"></i> <?= number_format($dest['rating_avg'], 1) ?>
                            </td>
                            <td>
                                <?php if ($dest['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <p class="text-muted text-center">Total: <?= $total ?> destinasi</p>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
