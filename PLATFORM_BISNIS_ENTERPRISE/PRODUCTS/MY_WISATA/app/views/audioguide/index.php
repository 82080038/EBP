<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">Audio Guide</h1>
            <p class="text-muted">Dengarkan panduan audio untuk destinasi wisata</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($audio_guides)): ?>
                <p class="text-muted">Belum ada audio guide tersedia.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($audio_guides as $audio): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($audio['title']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($audio['destination_name']) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-language me-1"></i><?= View::e($audio['language']) ?>
                                </p>
                                <p class="card-text small">
                                    <?= View::e(substr($audio['description'], 0, 100)) ?>...
                                </p>
                                <a href="<?= View::url('audioguide/play?id=' . $audio['id']) ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-play me-2"></i>Dengarkan
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
