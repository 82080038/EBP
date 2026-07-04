<?php
require_once APP_ROOT . '/app/helpers/Language.php';
$lang = Language::getLanguage();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - <?= Language::trans('error.404_title') ?> | MyWisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            text-align: center;
            color: white;
            padding: 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: bounce 2s infinite;
        }
        .error-message {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .error-description {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        .btn-home {
            background: white;
            color: #667eea;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: #764ba2;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <div class="error-code">404</div>
        <div class="error-message"><?= Language::trans('error.404_title') ?></div>
        <div class="error-description">
            <?= Language::trans('error.404_message') ?>
        </div>
        <a href="<?php echo BASE_URL; ?>" class="btn-home">
            <i class="fas fa-home me-2"></i><?= Language::trans('nav.home') ?>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
