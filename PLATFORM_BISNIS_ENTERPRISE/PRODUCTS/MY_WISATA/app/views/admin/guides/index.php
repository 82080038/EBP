<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Tour Guide</h2>
    </div>
    
    <!-- Guides Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Rating</th>
                            <th>Total Tour</th>
                            <th>Verified</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guides as $guide): ?>
                        <tr>
                            <td><?= $guide['id'] ?></td>
                            <td><?= View::e($guide['name']) ?></td>
                            <td><?= View::e($guide['email']) ?></td>
                            <td>
                                <i class="fas fa-star text-warning"></i> <?= number_format($guide['rating_avg'], 1) ?>
                            </td>
                            <td><?= $guide['total_tours'] ?></td>
                            <td>
                                <?php if ($guide['is_verified']): ?>
                                    <span class="badge bg-success">Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$guide['is_verified']): ?>
                                    <button class="btn btn-sm btn-success" onclick="approveGuide(<?= $guide['id'] ?>)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                <?php endif; ?>
                                <a href="#" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <p class="text-muted text-center">Total: <?= $total ?> tour guide</p>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
