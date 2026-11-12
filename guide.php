<?php
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT * FROM guides WHERE id = ?');
$stmt->execute([$id]);
$guide = $stmt->fetch();

if (!$guide) {
    http_response_code(404);
    echo '<main class="section"><div class="container"><div class="alert alert--error">Путеводитель не найден.</div><a href="/guides.php" class="btn btn--primary">К списку путеводителей</a></div></main>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $guide['city'];

/* блоки обзора по ТЗ: 2.2–2.4 */
$sections = [
    ['Достопримечательности', $guide['sights']],
    ['Отели',                 $guide['hotels']],
    ['Рестораны и кафе',      $guide['food']],
    ['Магазины и шоппинг',    $guide['shops']],
    ['Полезные советы',       $guide['tips']],
];
$sections = array_values(array_filter($sections, fn($s) => trim((string)$s[1]) !== ''));
?>

<main class="section">
    <div class="container">

        <!-- хлебные крошки -->
        <nav class="crumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span>
            <a href="/guides.php">Путеводители</a><span>/</span>
            <a href="/guides.php?country=<?= urlencode($guide['country']) ?>"><?= e($guide['country']) ?></a><span>/</span>
            <span><?= e($guide['city']) ?></span>
        </nav>

        <!-- шапка путеводителя -->
        <div class="page-head tour-detail__img--<?= $guide['id'] % 2 ? 'city' : 'architecture' ?>">
            <div class="page-head__content">
                <span class="chip"><?= e($guide['country']) ?></span>
                <h1><?= e($guide['city']) ?> — <?= e($guide['title']) ?></h1>
                <p><?= e($guide['description']) ?></p>
            </div>
        </div>

        <!-- обзор по разделам ТЗ: читается последовательно, блоками -->
        <?php foreach ($sections as $s): ?>
        <section class="content-block">
            <h2 class="block-title"><?= e($s[0]) ?></h2>
            <p><?= e($s[1]) ?></p>
        </section>
        <?php endforeach; ?>

        <?php
        /* другие города этой страны */
        $stmt = db()->prepare('SELECT * FROM guides WHERE country = ? AND id != ? LIMIT 3');
        $stmt->execute([$guide['country'], $id]);
        $sameCountry = $stmt->fetchAll();
        if ($sameCountry): ?>
        <div style="margin-top:48px;">
            <h2 class="section__title">Ещё по стране: <?= e($guide['country']) ?></h2>
            <div class="grid">
                <?php foreach ($sameCountry as $g): ?>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/guide.php?id=<?= (int)$g['id'] ?>"><?= e($g['city']) ?></a></h3>
                        <p class="card__desc"><?= e($g['description']) ?></p>
                        <div class="card__footer">
                            <span class="card__meta"><?= e($g['country']) ?></span>
                            <a href="/guide.php?id=<?= (int)$g['id'] ?>" class="btn btn--ghost">Читать</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
