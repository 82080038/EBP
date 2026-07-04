<?php
// Get current language (Language helper already loaded in index.php)
$currentLang = Language::getLanguage();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= Language::trans('home.subtitle') ?>">
    <title><?= isset($title) ? $title : Language::trans('home.welcome') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= View::asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= View::url() ?>">
                <i class="fas fa-map-marked-alt me-2"></i>MyWisata
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url() ?>"><?= Language::trans('nav.home') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('home/about') ?>"><?= Language::trans('nav.about') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('home/contact') ?>"><?= Language::trans('nav.contact') ?></a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <!-- Language Selector -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-globe me-1"></i><?= Language::trans('common.language') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item <?= $currentLang === 'id' ? 'active' : '' ?>" 
                                   href="<?= View::url('language/set/id') ?>">
                                    <i class="fas fa-flag me-2"></i><?= Language::trans('common.indonesian') ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= $currentLang === 'en' ? 'active' : '' ?>" 
                                   href="<?= View::url('language/set/en') ?>">
                                    <i class="fas fa-flag me-2"></i><?= Language::trans('common.english') ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('auth/login') ?>">
                            <i class="fas fa-sign-in-alt me-1"></i><?= Language::trans('nav.login') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('auth/register') ?>">
                            <i class="fas fa-user-plus me-1"></i><?= Language::trans('nav.register') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main>
