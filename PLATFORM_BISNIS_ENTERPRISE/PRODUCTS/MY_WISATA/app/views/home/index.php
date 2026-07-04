<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="hero-section text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3"><?= Language::trans('home.welcome') ?></h1>
        <p class="lead mb-4"><?= Language::trans('home.subtitle') ?></p>
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <a href="<?= View::url('tourguides') ?>" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                            <h5 class="card-title"><?= Language::trans('home.card_tour_guide_title') ?></h5>
                            <p class="card-text"><?= Language::trans('home.card_tour_guide_desc') ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-4">
                <a href="<?= View::url('destinations') ?>" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-map-marked-alt fa-3x text-success mb-3"></i>
                            <h5 class="card-title"><?= Language::trans('home.card_destination_title') ?></h5>
                            <p class="card-text"><?= Language::trans('home.card_destination_desc') ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-4">
                <a href="<?= View::url('destinations') ?>" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-ticket-alt fa-3x text-warning mb-3"></i>
                            <h5 class="card-title"><?= Language::trans('home.card_ticket_title') ?></h5>
                            <p class="card-text"><?= Language::trans('home.card_ticket_desc') ?></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4 mb-4">
                <a href="<?= View::url('hotels') ?>" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-hotel fa-3x text-info mb-3"></i>
                            <h5 class="card-title"><?= Language::trans('home.card_hotel_title') ?></h5>
                            <p class="card-text"><?= Language::trans('home.card_hotel_desc') ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-4">
                <a href="<?= View::url('restaurants') ?>" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-utensils fa-3x text-danger mb-3"></i>
                            <h5 class="card-title"><?= Language::trans('home.card_restaurant_title') ?></h5>
                            <p class="card-text"><?= Language::trans('home.card_restaurant_desc') ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-4">
                <a href="<?= View::url('events') ?>" class="text-decoration-none">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-alt fa-3x text-secondary mb-3"></i>
                            <h5 class="card-title"><?= Language::trans('home.card_event_title') ?></h5>
                            <p class="card-text"><?= Language::trans('home.card_event_desc') ?></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
