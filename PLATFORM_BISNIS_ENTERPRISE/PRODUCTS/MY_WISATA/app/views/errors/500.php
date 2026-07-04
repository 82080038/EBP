<?php
require_once APP_ROOT . '/app/helpers/Language.php';
$lang = Language::getLanguage();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - <?= Language::trans('error.500_title') ?> | MyWisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            animation: shake 2s infinite;
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
            color: #f5576c;
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
            color: #f093fb;
        }
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px);
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
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="error-code">500</div>
        <div class="error-message"><?= Language::trans('error.500_title') ?></div>
        <div class="error-description">
            <?= Language::trans('error.500_message') ?>
        </div>
        <a href="<?php echo BASE_URL; ?>" class="btn-home">
            <i class="fas fa-home me-2"></i><?= Language::trans('nav.home') ?>
        </a>
        <div class="mt-4">
            <small class="opacity-75">
                <?= $lang === 'id' ? 'Jika masalah berlanjut, silakan hubungi kami di admin@mywisata.com' : 'If the problem persists, please contact us at admin@mywisata.com' ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
