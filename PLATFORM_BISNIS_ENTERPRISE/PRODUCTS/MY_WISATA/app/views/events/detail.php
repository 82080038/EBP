<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('events') ?>">Event</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= View::e($event['name']) ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <?php if (!empty($event['main_image'])): ?>
                    <img src="<?= View::asset('uploads/events/' . $event['main_image']) ?>" class="card-img-top" alt="<?= View::e($event['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/800x400" class="card-img-top" alt="<?= View::e($event['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?= View::e($event['name']) ?></h1>
                    <p class="text-muted">
                        <i class="fas fa-calendar me-1"></i><?= View::date($event['event_date']) ?>
                        <i class="fas fa-clock ms-3 me-1"></i><?= View::e($event['event_time']) ?>
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($event['venue']) ?>, <?= View::e($event['city']) ?>
                    </p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-star text-warning"></i> <?= number_format($event['rating_avg'], 1) ?>
                            <span class="text-muted small">(<?= $event['review_count'] ?> review)</span>
                        </div>
                        <span class="badge bg-primary"><?= View::e($event['category']) ?></span>
                    </div>
                    <p class="card-text"><?= nl2br(View::e($event['description'])) ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Harga Tiket</h6>
                            <p class="card-text fw-bold text-success"><?= View::currency($event['ticket_price']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Kapasitas</h6>
                            <p class="card-text"><?= $event['max_participants'] ?> peserta</p>
                        </div>
                    </div>
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
                    <h5 class="card-title mb-0">Daftar Event</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Tiket</label>
                            <input type="number" class="form-control" value="1" min="1" max="<?= $event['max_participants'] ?>" required>
                        </div>
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <span>Harga per tiket:</span>
                                    <span><?= View::currency($event['ticket_price']) ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong><?= View::currency($event['ticket_price']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Beli Tiket</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
