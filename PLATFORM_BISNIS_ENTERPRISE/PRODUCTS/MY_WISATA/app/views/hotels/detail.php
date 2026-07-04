<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('hotels') ?>">Hotel</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= View::e($hotel['name']) ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <?php if (!empty($hotel['main_image'])): ?>
                    <img src="<?= View::asset('uploads/hotels/' . $hotel['main_image']) ?>" class="card-img-top" alt="<?= View::e($hotel['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/800x400" class="card-img-top" alt="<?= View::e($hotel['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?= View::e($hotel['name']) ?></h1>
                    <p class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($hotel['address']) ?>, <?= View::e($hotel['city']) ?>
                    </p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-star text-warning"></i> <?= number_format($hotel['rating_avg'], 1) ?>
                            <span class="text-muted small">(<?= $hotel['review_count'] ?> review)</span>
                        </div>
                        <span class="badge bg-info"><?= View::e($hotel['star_rating']) ?> Bintang</span>
                    </div>
                    <p class="card-text"><?= nl2br(View::e($hotel['description'])) ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Fasilitas</h6>
                            <p class="card-text"><?= View::e($hotel['amenities']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Kontak</h6>
                            <p class="card-text">Telepon: <?= View::e($hotel['contact_phone']) ?></p>
                            <p class="card-text">Email: <?= View::e($hotel['email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Rooms -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Kamar Tersedia</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($rooms)): ?>
                        <p class="text-muted">Belum ada kamar tersedia.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($rooms as $room): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= View::e($room['room_type']) ?></h6>
                                        <p class="card-text small text-muted">
                                            Kapasitas: <?= $room['capacity'] ?> orang
                                        </p>
                                        <p class="card-text fw-bold text-success">
                                            <?= View::currency($room['price_per_night']) ?>/malam
                                        </p>
                                        <button class="btn btn-sm btn-primary">Booking</button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Reviews -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Review</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <p class="text-muted">Belum ada review.</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Booking</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Check-in</label>
                            <input type="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Check-out</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Kamar</label>
                            <input type="number" class="form-control" value="1" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Cek Ketersediaan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
