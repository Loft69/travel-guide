<?php
require_once __DIR__ . '/includes/header.php';

/* популярные путеводители для главной */
$popular = db()->query(
    'SELECT * FROM guides WHERE is_popular = 1 ORDER BY id LIMIT 4'
)->fetchAll();

/* свежие статьи блога */
$blog = db()->query(
    "SELECT * FROM articles WHERE section = 'blog' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

/* свежие фото */
$photos = db()->query('SELECT * FROM photos ORDER BY id DESC LIMIT 4')->fetchAll();

/* карта классов заглушек фото по типу */
$imgCls = ['city', 'architecture', 'sea', 'mountains'];
?>

<main>
    <!-- главный баннер: приветствие и описание сайта -->
    <section class="hero">
        <div class="container">
            <div class="hero__badge">Путеводитель по миру</div>
            <h1 class="hero__title">Путешествуйте сами — без турагентов</h1>
            <p class="hero__text">Проверенные путеводители по странам и городам, практические советы
                по планированию, живые фотографии и личные истории из наших поездок.</p>
            <div class="hero__actions">
                <a href="/guides.php" class="btn btn--accent">Открыть путеводители</a>
                <a href="#advice-preview" class="btn btn--ghost">Советы путешественникам</a>
            </div>
        </div>
    </section>

    <!-- навигация по разделам сайта -->
    <section class="section">
        <div class="container">
            <h2 class="section__title">Разделы сайта</h2>
            <div class="grid">
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/guides.php">Путеводители</a></h3>
                        <p class="card__desc">Страны и города: достопримечательности, отели, рестораны, магазины и советы по каждому месту.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/advice.php">Советы и рекомендации</a></h3>
                        <p class="card__desc">Как планировать поездку, выбирать транспорт и жильё, оставаться в безопасности и экономить.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/gallery.php">Фотогалереи</a></h3>
                        <p class="card__desc">Фотографии из разных стран: пейзажи, архитектура и события — с подписями и комментариями.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/about.php">О нас</a></h3>
                        <p class="card__desc">Команда сайта, наши цели и миссия, контактная информация для связи.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/blog.php">Блог</a></h3>
                        <p class="card__desc">Личные истории и приключения авторов с рекомендациями из собственного опыта.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/register.php">Регистрация</a></h3>
                        <p class="card__desc">Зарегистрируйтесь, чтобы комментировать статьи и фотографии.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- о сайте: тематика и цели -->
    <section class="section">
        <div class="container">
            <h2 class="section__title">О чём сайт</h2>
            <div class="grid">
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title">Тематика</h3>
                        <p class="card__desc">Самостоятельные путешествия: страны, города, маршруты и всё, что нужно знать до и во время поездки.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title">Цель сайта</h3>
                        <p class="card__desc">Собрать проверенную на практике информацию в одном месте, чтобы планирование поездки занимало часы, а не недели.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title">Для кого</h3>
                        <p class="card__desc">Для тех, кто планирует поездку сам: без турагентов, но с картой, запасом времени и нашим сайтом.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <?php if ($popular): ?>
    <!-- популярные путеводители -->
    <section class="section" id="popular">
        <div class="container">
            <h2 class="section__title">Популярные направления</h2>
            <div class="grid">
                <?php foreach ($popular as $g): ?>
                <article class="card">
                    <div class="card__body">
                        <div><span class="chip"><?= e($g['country']) ?></span></div>
                        <h3 class="card__title">
                            <a href="/guide.php?id=<?= (int)$g['id'] ?>"><?= e($g['city']) ?>: <?= e($g['title']) ?></a>
                        </h3>
                        <p class="card__desc"><?= e($g['description']) ?></p>
                        <div class="card__footer">
                            <span class="card__price" style="font-size:14px"><?= e($g['city']) ?></span>
                            <a href="/guide.php?id=<?= (int)$g['id'] ?>" class="btn btn--ghost">Читать</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- превью советов -->
    <section class="section" id="advice-preview">
        <div class="container">
            <h2 class="section__title">Советы путешественникам</h2>
            <div class="grid">
                <?php
                $advicePreview = db()->query(
                    "SELECT * FROM articles WHERE section = 'advice' ORDER BY created_at DESC LIMIT 3"
                )->fetchAll();
                foreach ($advicePreview as $a): ?>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/article.php?id=<?= (int)$a['id'] ?>"><?= e($a['title']) ?></a></h3>
                        <p class="card__desc"><?= e($a['excerpt']) ?></p>
                        <div class="card__footer">
                            <span class="card__meta"><?= e($a['author_name']) ?></span>
                            <a href="/article.php?id=<?= (int)$a['id'] ?>" class="btn btn--primary">Читать</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- превью фотогалереи -->
    <section class="section">
        <div class="container">
            <h2 class="section__title">Из фотогалереи</h2>
            <div class="grid">
                <?php foreach ($photos as $i => $p): ?>
                <article class="card">
                    <div class="card__img card__img--<?= e($imgCls[$i % 4]) ?>">
                            <?php if (!empty($p['image_path'])): ?>
                                <img src="<?= e($p['image_path']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <?= e(mb_strtoupper($p['country'])) ?>
                            <?php endif; ?>
                        </div>
                    <div class="card__body">
                        <h3 class="card__title"><a href="/photo.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a></h3>
                        <p class="card__desc"><?= e(mb_strimwidth($p['caption'], 0, 80, '…')) ?></p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if ($blog): ?>
    <!-- превью блога -->
    <section class="section">
        <div class="container">
            <h2 class="section__title">Свежее в блоге</h2>
            <div class="grid">
                <?php foreach ($blog as $a): ?>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/article.php?id=<?= (int)$a['id'] ?>"><?= e($a['title']) ?></a></h3>
                        <p class="card__desc"><?= e($a['excerpt']) ?></p>
                        <div class="card__footer">
                            <span class="card__meta"><?= e($a['author_name']) ?></span>
                            <a href="/article.php?id=<?= (int)$a['id'] ?>" class="btn btn--primary">Читать</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
