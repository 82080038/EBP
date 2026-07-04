<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('audioguide') ?>">Audio Guide</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= View::e($audio_guide['title']) ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title mb-3"><?= View::e($audio_guide['title']) ?></h1>
                    <p class="text-muted mb-3">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($audio_guide['destination_name']) ?>
                        <i class="fas fa-language ms-3 me-1"></i><?= View::e($audio_guide['language']) ?>
                    </p>
                    
                    <div class="audio-player mb-4">
                        <audio controls class="w-100">
                            <source src="<?= View::asset('uploads/audio/' . $audio_guide['audio_file']) ?>" type="audio/mpeg">
                            Browser Anda tidak mendukung elemen audio.
                        </audio>
                    </div>
                    
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Deskripsi</h5>
                            <p class="card-text"><?= nl2br(View::e($audio_guide['description'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi</h5>
                </div>
                <div class="card-body">
                    <h6>Durasi</h6>
                    <p class="card-text"><?= $audio_guide['duration'] ?> menit</p>
                    
                    <h6>Bahasa</h6>
                    <p class="card-text"><?= View::e($audio_guide['language']) ?></p>
                    
                    <h6>Destinasi</h6>
                    <p class="card-text"><?= View::e($audio_guide['destination_name']) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
