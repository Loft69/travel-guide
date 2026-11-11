<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

$user = current_user();
$cartItems = cart_count();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? SITE_NAME) ?> — <?= SITE_NAME ?></title>
    <meta name="description" content="Каталог туров с онлайн-заказом: море, экскурсии, горы, круизы, санатории.">
    <link rel="stylesheet" href="/css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <div class="container header__inner">
        <a href="/" class="logo" aria-label="Travel Guide — на главную">
            <span class="logo__icon">✈</span>
            <span class="logo__text">Travel<span class="logo__accent">Guide</span></span>
        </a>

        <!-- меню -->
        <nav class="nav" id="mainNav" aria-label="Главное меню">
            <a href="/" class="nav__link">Главная</a>
            <a href="/tours.php" class="nav__link">Туры</a>
            <a href="/cart.php" class="nav__link">Корзина<?php if ($cartItems > 0): ?> <span class="badge"><?= $cartItems ?></span><?php endif; ?></a>
            <?php if ($user): ?>
                <a href="/orders.php" class="nav__link">Мои заказы</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/admin/index.php" class="nav__link nav__link--admin">Админ-панель</a>
                <?php endif; ?>
                <a href="/logout.php" class="nav__link">Выход</a>
            <?php else: ?>
                <a href="/login.php" class="nav__link">Вход</a>
                <a href="/register.php" class="nav__link">Регистрация</a>
            <?php endif; ?>
        </nav>

        <!-- бургер -->
        <button class="burger" type="button" id="burgerBtn" aria-label="Открыть меню" aria-expanded="false">
            <span class="burger__line"></span>
            <span class="burger__line"></span>
            <span class="burger__line"></span>
        </button>
    </div>
</header>

<!-- флеш-сообщение -->
<?php if ($msg = flash()): ?>
    <div class="container" style="padding-top:16px;"><div class="alert alert--success" role="status"><?= e($msg) ?></div></div>
<?php endif; ?>
