<?php
$pageTitle = 'Фотогалереи';
require_once __DIR__ . '/includes/header.php';

/* Фильтр по стране */
$country = trim((string) ($_GET['country'] ?? ''));

$countries = db()->query(
    'SELECT DISTINCT country FROM photos ORDER BY country'
)->fetchAll(PDO::FETCH_COLUMN);

if ($country !== '' && in_array($country, $countries, true)) {
    $stmt = db()->prepare(
        'SELECT * FROM photos WHERE country = ? ORDER BY id'
    );
    $stmt->execute([$country]);
} else {
    $stmt = db()->query('SELECT * FROM photos ORDER BY id');
}

$photos = $stmt->fetchAll();

/* Классы заглушек */
$imgCls = ['city', 'architecture', 'sea', 'mountains'];
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Фотогалереи</h1>
        <p class="form__hint" style="margin:-16px 0 24px;">
            Фотографии из разных стран: пейзажи, архитектура
            и события — с подписями и комментариями.
        </p>
        <!-- Фильтр по стране -->
        <nav class="admin-bar" aria-label="Фильтр по стране">
            <a class="btn btn--ghost <?= $country === '' ? 'active' : '' ?>" href="/gallery.php">
                Все страны
            </a>
            <?php foreach ($countries as $c): ?>
                <a class="btn btn--ghost <?= $country === $c ? 'active' : '' ?>"
                    href="/gallery.php?country=<?= urlencode($c) ?>">
                    <?= e($c) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php if (!$photos): ?>
            <div class="alert alert--error">
                Фотографий по этой стране пока нет.
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($photos as $i => $p): ?>
                    <article class="card">
                        <!-- Изображение фотографии -->
                        <div class="card__img card__img--<?= e($imgCls[$i % 4]) ?>">
                            <?php if (!empty($p['image_path'])): ?>
                                <img src="<?= e($p['image_path']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <?= e(mb_strtoupper($p['country'])) ?>
                            <?php endif; ?>
                        </div>
                        <div class="card__body">
                            <div>
                                <span class="chip">
                                    <?= e($p['country']) ?>
                                </span>
                            </div>
                            <h3 class="card__title">
                                <a href="/photo.php?id=<?= (int) $p['id'] ?>">
                                    <?= e($p['title']) ?>
                                </a>
                            </h3>
                            <p class="card__desc">
                                <?= e(mb_strimwidth($p['caption'], 0, 100, '…')) ?>
                            </p>
                            <div class="card__footer">
                                <span class="card__meta">
                                    Смотреть
                                </span>
                                <a href="/photo.php?id=<?= (int) $p['id'] ?>" class="btn btn--primary">
                                    Открыть
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>