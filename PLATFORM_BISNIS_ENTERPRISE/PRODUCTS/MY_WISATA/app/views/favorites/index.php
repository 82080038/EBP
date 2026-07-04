<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Favorit Saya</h1>
        <div class="btn-group">
            <a href="<?= View::url('favorites?type=all') ?>" class="btn btn-outline-primary <?= $type_filter === 'all' ? 'active' : '' ?>">Semua</a>
            <a href="<?= View::url('favorites?type=destination') ?>" class="btn btn-outline-primary <?= $type_filter === 'destination' ? 'active' : '' ?>">Destinasi</a>
            <a href="<?= View::url('favorites?type=hotel') ?>" class="btn btn-outline-primary <?= $type_filter === 'hotel' ? 'active' : '' ?>">Hotel</a>
            <a href="<?= View::url('favorites?type=restaurant') ?>" class="btn btn-outline-primary <?= $type_filter === 'restaurant' ? 'active' : '' ?>">Restoran</a>
            <a href="<?= View::url('favorites?type=event') ?>" class="btn btn-outline-primary <?= $type_filter === 'event' ? 'active' : '' ?>">Event</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($favorites)): ?>
                <p class="text-muted text-center py-4">Belum ada favorit.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($favorites as $favorite): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><?= View::e(ucfirst($favorite['item_type'])) ?></h6>
                                <p class="card-text small text-muted">ID: <?= $favorite['item_id'] ?></p>
                                <p class="card-text small text-muted">Ditambahkan: <?= View::date($favorite['created_at']) ?></p>
                                <button class="btn btn-sm btn-danger" onclick="removeFavorite('<?= $favorite['item_type'] ?>', <?= $favorite['item_id'] ?>)">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function removeFavorite(itemType, itemId) {
    var formData = new FormData();
    formData.append('item_type', itemType);
    formData.append('item_id', itemId);
    formData.append('csrf_token', '<?= Middleware::csrfToken() ?>');
    
    fetch(window.APP_URL + 'favorite/remove', {
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
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
