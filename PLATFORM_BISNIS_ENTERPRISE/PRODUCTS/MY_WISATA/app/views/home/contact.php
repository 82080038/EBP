<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="card-title text-center mb-4"><?= Language::trans('contact.title') ?></h1>
                    
                    <div class="contact-content">
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                                    <h5><?= Language::trans('contact.address') ?></h5>
                                    <p class="text-muted">
                                        Jl. Pariwisata No. 123<br>
                                        Jakarta, Indonesia<br>
                                        12345
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                                    <h5><?= Language::trans('contact.phone') ?></h5>
                                    <p class="text-muted">
                                        +62 21 1234 5678<br>
                                        +62 812 3456 7890
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                    <h5><?= Language::trans('contact.email') ?></h5>
                                    <p class="text-muted">
                                        info@mywisata.com<br>
                                        support@mywisata.com
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h4 class="mb-3"><?= Language::trans('contact.send_message') ?></h4>
                        <form action="<?= View::url('home/contact/send') ?>" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label"><?= Language::trans('contact.full_name') ?></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label"><?= Language::trans('common.email') ?></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label"><?= Language::trans('contact.subject') ?></label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label"><?= Language::trans('contact.message') ?></label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i><?= Language::trans('contact.send_message') ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
