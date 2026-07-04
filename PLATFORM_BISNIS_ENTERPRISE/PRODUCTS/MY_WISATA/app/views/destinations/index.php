<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">Destinasi Wisata</h1>
            <p class="text-muted">Temukan destinasi wisata terbaik di Indonesia</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= View::url('destinations') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari destinasi..." value="<?= View::e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <select class="form-select" name="category">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($filters['category_id']) && $filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= View::e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="city" placeholder="Kota..." value="<?= View::e($filters['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Featured Destinations -->
    <?php if (!empty($featured)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="mb-3">Destinasi Unggulan</h3>
            <div class="row">
                <?php foreach ($featured as $dest): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($dest['main_image'])): ?>
                            <img src="<?= View::asset('uploads/destinations/' . $dest['main_image']) ?>" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= View::e($dest['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($dest['city']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-star text-warning"></i> <?= number_format($dest['rating_avg'], 1) ?>
                                </div>
                                <span class="badge bg-primary"><?= View::e($dest['category_name']) ?></span>
                            </div>
                            <a href="<?= View::url('destinations/detail?id=' . $dest['id']) ?>" class="btn btn-primary mt-3 w-100">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Popular Destinations -->
    <?php if (!empty($popular)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="mb-3">Destinasi Populer</h3>
            <div class="row">
                <?php foreach ($popular as $dest): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($dest['main_image'])): ?>
                            <img src="<?= View::asset('uploads/destinations/' . $dest['main_image']) ?>" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= View::e($dest['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($dest['city']) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-star text-warning"></i> <?= number_format($dest['rating_avg'], 1) ?>
                                </div>
                                <span class="badge bg-success">Populer</span>
                            </div>
                            <a href="<?= View::url('destinations/detail?id=' . $dest['id']) ?>" class="btn btn-primary mt-3 w-100">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- All Destinations -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3">Semua Destinasi</h3>
            <?php if (empty($destinations)): ?>
                <p class="text-muted">Tidak ada destinasi ditemukan.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($destinations as $dest): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($dest['main_image'])): ?>
                                <img src="<?= View::asset('uploads/destinations/' . $dest['main_image']) ?>" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($dest['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($dest['city']) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-star text-warning"></i> <?= number_format($dest['rating_avg'], 1) ?>
                                    </div>
                                    <span class="badge bg-secondary"><?= View::e($dest['category_name']) ?></span>
                                </div>
                                <a href="<?= View::url('destinations/detail?id=' . $dest['id']) ?>" class="btn btn-primary mt-3 w-100">
                                    Lihat Detail
                                </a>
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
