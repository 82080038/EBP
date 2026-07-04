<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="card-title text-center mb-4"><?= Language::trans('about.title') ?></h1>
                    
                    <div class="about-content">
                        <h3 class="mb-3"><?= Language::trans('about.title') ?></h3>
                        <p class="text-muted mb-4">
                            <?= Language::trans('about.description') ?>
                        </p>
                        
                        <h4 class="mb-3"><?= Language::trans('about.vision') ?></h4>
                        <p class="text-muted mb-4">
                            <?= Language::trans('about.vision_text') ?>
                        </p>
                        
                        <h4 class="mb-3"><?= Language::trans('about.mission') ?></h4>
                        <ul class="text-muted mb-4">
                            <li><?= Language::trans('about.mission_1') ?></li>
                            <li><?= Language::trans('about.mission_2') ?></li>
                            <li><?= Language::trans('about.mission_3') ?></li>
                            <li><?= Language::trans('about.mission_4') ?></li>
                            <li><?= Language::trans('about.mission_5') ?></li>
                        </ul>
                        
                        <h4 class="mb-3"><?= Language::trans('about.features') ?></h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-user-tie fa-2x text-primary me-3"></i>
                                    <div>
                                        <h5 class="mb-1"><?= Language::trans('about.feature_tour_guide') ?></h5>
                                        <p class="text-muted small"><?= Language::trans('about.feature_tour_guide_desc') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-map-marked-alt fa-2x text-primary me-3"></i>
                                    <div>
                                        <h5 class="mb-1"><?= Language::trans('about.feature_destination') ?></h5>
                                        <p class="text-muted small"><?= Language::trans('about.feature_destination_desc') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-hotel fa-2x text-primary me-3"></i>
                                    <div>
                                        <h5 class="mb-1"><?= Language::trans('about.feature_hotel') ?></h5>
                                        <p class="text-muted small"><?= Language::trans('about.feature_hotel_desc') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-utensils fa-2x text-primary me-3"></i>
                                    <div>
                                        <h5 class="mb-1"><?= Language::trans('about.feature_restaurant') ?></h5>
                                        <p class="text-muted small"><?= Language::trans('about.feature_restaurant_desc') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h4 class="mb-3"><?= Language::trans('about.contact_us') ?></h4>
                        <p class="text-muted mb-3">
                            <?= Language::trans('about.contact_desc') ?>
                        </p>
                        <a href="<?= View::url('home/contact') ?>" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i><?= Language::trans('nav.contact') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
