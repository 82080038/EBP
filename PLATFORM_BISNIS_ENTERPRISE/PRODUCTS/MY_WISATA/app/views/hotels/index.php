<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">Hotel & Homestay</h1>
            <p class="text-muted">Temukan akomodasi terbaik untuk perjalanan Anda</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= View::url('hotels') ?>">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari hotel..." value="<?= View::e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-5 mb-3">
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
    
    <!-- Hotels List -->
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($hotels)): ?>
                <p class="text-muted">Tidak ada hotel ditemukan.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($hotels as $hotel): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($hotel['main_image'])): ?>
                                <img src="<?= View::asset('uploads/hotels/' . $hotel['main_image']) ?>" class="card-img-top" alt="<?= View::e($hotel['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x200" class="card-img-top" alt="<?= View::e($hotel['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($hotel['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($hotel['city']) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fas fa-star text-warning"></i> <?= number_format($hotel['rating_avg'], 1) ?>
                                    </div>
                                    <span class="badge bg-info"><?= View::e($hotel['star_rating']) ?> Bintang</span>
                                </div>
                                <p class="card-text small text-muted">
                                    <?= View::e($hotel['address']) ?>
                                </p>
                                <a href="<?= View::url('hotels/detail?id=' . $hotel['id']) ?>" class="btn btn-primary w-100">
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
