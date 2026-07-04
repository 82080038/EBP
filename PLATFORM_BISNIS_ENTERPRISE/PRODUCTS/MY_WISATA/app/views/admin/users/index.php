<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Pengguna</h2>
        <a href="#" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Pengguna
        </a>
    </div>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= View::url('admin/users') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari nama atau email..." value="<?= View::e($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="role">
                            <option value="">Semua Role</option>
                            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="wisatawan" <?= $role_filter === 'wisatawan' ? 'selected' : '' ?>>Wisatawan</option>
                            <option value="tour_guide" <?= $role_filter === 'tour_guide' ? 'selected' : '' ?>>Tour Guide</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="banned" <?= $status_filter === 'banned' ? 'selected' : '' ?>>Banned</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= View::e($user['name']) ?></td>
                            <td><?= View::e($user['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'tour_guide' ? 'success' : 'primary') ?>">
                                    <?= View::e($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : ($user['status'] === 'banned' ? 'danger' : 'secondary') ?>">
                                    <?= View::e($user['status']) ?>
                                </span>
                            </td>
                            <td><?= View::date($user['created_at']) ?></td>
                            <td>
                                <a href="<?= View::url('admin/editUser?id=' . $user['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= View::url('admin/deleteUser?id=' . $user['id']) ?>', 'Hapus pengguna ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= View::url('admin/users?page=' . ($page - 1) . '&search=' . $search . '&role=' . $role_filter . '&status=' . $status_filter) ?>">Previous</a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="page-item active">
                        <span class="page-link"><?= $page ?></span>
                    </li>
                    
                    <?php if ($page * $limit < $total): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= View::url('admin/users?page=' . ($page + 1) . '&search=' . $search . '&role=' . $role_filter . '&status=' . $status_filter) ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <p class="text-muted text-center">Total: <?= $total ?> pengguna</p>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
