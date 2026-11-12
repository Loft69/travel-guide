<?php
require_once __DIR__ . '/includes/header.php';

/* фильтр по стране */
$country = trim((string)($_GET['country'] ?? ''));

$countries = db()->query('SELECT DISTINCT country FROM guides ORDER BY country')->fetchAll(PDO::FETCH_COLUMN);

if ($country !== '' && in_array($country, $countries, true)) {
    $stmt = db()->prepare('SELECT * FROM guides WHERE country = ? ORDER BY city');
    $stmt->execute([$country]);
} else {
    $stmt = db()->query('SELECT * FROM guides ORDER BY is_popular DESC, country, city');
}
$guides = $stmt->fetchAll();

$pageTitle = 'Путеводители';
?>

<main class="section">
    <div class="container">
        <div style="max-width:860px; margin:0 auto;">

        <h1 class="section__title">Путеводители</h1>
        <p class="form__hint" style="margin:-16px 0 28px;">
            Страны и города: достопримечательности, отели, рестораны, магазины и советы по каждому месту.
        </p>

        <!-- фильтр по стране -->
        <nav class="admin-bar" aria-label="Фильтр по стране" style="margin-bottom:24px;">
            <a class="btn btn--ghost <?= $country === '' ? 'active' : '' ?>" href="/guides.php">Все страны</a>
            <?php foreach ($countries as $c): ?>
            <a class="btn btn--ghost <?= $country === $c ? 'active' : '' ?>"
               href="/guides.php?country=<?= urlencode($c) ?>"><?= e($c) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (!$guides): ?>
            <div class="alert alert--error">Путеводители по этой стране пока не написаны.</div>
        <?php else: ?>
        <div class="postlist">
            <?php foreach ($guides as $g): ?>
            <article class="post">
                <div class="post__main">
                    <div class="post__meta">
                        <span class="chip"><?= e($g['country']) ?></span>
                        <span><?= e($g['city']) ?></span>
                    </div>
                    <h2 class="post__title">
                        <a href="/guide.php?id=<?= (int)$g['id'] ?>"><?= e($g['title']) ?></a>
                    </h2>
                    <p class="post__excerpt"><?= e($g['description']) ?></p>
                </div>
                <a href="/guide.php?id=<?= (int)$g['id'] ?>" class="btn btn--primary">Читать</a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
