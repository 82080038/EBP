<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Notifikasi</h1>
            
            <div class="card">
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <p class="text-muted text-center py-4">Tidak ada notifikasi.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item <?= $notification['is_read'] ? '' : 'bg-light' ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= View::e($notification['title']) ?></h6>
                                    <small><?= View::date($notification['created_at']) ?></small>
                                </div>
                                <p class="mb-1"><?= View::e($notification['message']) ?></p>
                                <?php if ($notification['link']): ?>
                                    <a href="<?= View::e($notification['link']) ?>" class="btn btn-sm btn-outline-primary mt-2">Lihat Detail</a>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
