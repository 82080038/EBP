<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('destinations') ?>">Destinasi</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= View::e($destination['name']) ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Main Image -->
            <div class="card mb-4">
                <?php if (!empty($destination['main_image'])): ?>
                    <img src="<?= View::asset('uploads/destinations/' . $destination['main_image']) ?>" class="card-img-top" alt="<?= View::e($destination['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/800x400" class="card-img-top" alt="<?= View::e($destination['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?= View::e($destination['name']) ?></h1>
                    <p class="card-text text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($destination['address']) ?>, <?= View::e($destination['city']) ?>
                    </p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-star text-warning"></i> <?= number_format($destination['rating_avg'], 1) ?>
                            <span class="text-muted small">(<?= $destination['review_count'] ?> review)</span>
                        </div>
                        <span class="badge bg-primary"><?= View::e($destination['category_name']) ?></span>
                    </div>
                    <p class="card-text"><?= nl2br(View::e($destination['description'])) ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Informasi Tiket</h6>
                            <p class="card-text">Harga Tiket: <?= View::currency($destination['entry_fee']) ?></p>
                            <p class="card-text">Jam Buka: <?= View::e($destination['opening_hours']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Kontak</h6>
                            <p class="card-text">Telepon: <?= View::e($destination['contact_phone']) ?></p>
                            <p class="card-text">Website: <?= View::e($destination['website']) ?></p>
                        </div>
                    </div>
                    
                    <a href="#" class="btn btn-primary btn-lg mt-3">
                        <i class="fas fa-ticket-alt me-2"></i>Beli Tiket
                    </a>
                </div>
            </div>
            
            <!-- Gallery -->
            <?php if (!empty($images) && count($images) > 1): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Galeri</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($images as $img): ?>
                            <?php if ($img['image_path'] !== $destination['main_image']): ?>
                            <div class="col-md-4 mb-3">
                                <img src="<?= View::asset('uploads/destinations/' . $img['image_path']) ?>" class="img-fluid rounded" alt="Gallery">
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Reviews -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Review</h5>
                </div>
                <div class="card-body">
                    <?php if (Session::get('user_id')): ?>
                    <div class="mb-4">
                        <h6>Tulis Review</h6>
                        <form id="reviewForm">
                            <input type="hidden" name="destination_id" value="<?= $destination['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select class="form-select" name="rating" required>
                                    <option value="">Pilih Rating</option>
                                    <option value="5">5 - Sangat Baik</option>
                                    <option value="4">4 - Baik</option>
                                    <option value="3">3 - Biasa</option>
                                    <option value="2">2 - Kurang</option>
                                    <option value="1">1 - Sangat Buruk</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Komentar</label>
                                <textarea class="form-control" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Review</button>
                        </form>
                    </div>
                    <?php endif; ?>
                    
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
            <!-- Map -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lokasi</h5>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 300px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center;">
                        <span class="text-muted">Peta akan ditampilkan di sini</span>
                    </div>
                    <p class="mt-2 mb-0">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?= View::e($destination['latitude']) ?>, <?= View::e($destination['longitude']) ?>
                    </p>
                </div>
            </div>
            
            <!-- Nearby Destinations -->
            <?php if (!empty($nearby)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Destinasi Terdekat</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($nearby as $near): ?>
                        <?php if ($near['id'] != $destination['id']): ?>
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($near['main_image'])): ?>
                                <img src="<?= View::asset('uploads/destinations/' . $near['main_image']) ?>" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/60" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= View::e($near['name']) ?></h6>
                                <small class="text-muted"><?= number_format($near['distance'], 2) ?> km</small>
                            </div>
                            <a href="<?= View::url('destinations/detail?id=' . $near['id']) ?>" class="btn btn-sm btn-outline-primary">
                                Lihat
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    formData.append('csrf_token', '<?= Middleware::csrfToken() ?>');
    
    fetch(window.APP_URL + 'destinations/addReview', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(function() {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#0d6efd'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan',
            confirmButtonColor: '#0d6efd'
        });
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
