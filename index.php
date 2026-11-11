<?php
require_once __DIR__ . '/includes/header.php';

// класс заглушки и иконка для карточки тура по slug категории
function tour_placeholder($t)
{
    $map = [
        'beach' => 'beach',
        'excursion' => 'excursion',
        'mountains' => 'mountains',
        'cruise' => 'cruise',
        'health' => 'health',
    ];
    $icons = [
        'beach' => '🏖',
        'excursion' => '🏛',
        'mountains' => '🏔',
        'cruise' => '🚢',
        'health' => '🌿',
    ];
    $slug = $t['slug'] ?? '';
    return [$map[$slug] ?? '', $icons[$slug] ?? '🌍'];
}
?>

<?php
/* Популярные туры (для главной) */
$popular = db()->query(
    'SELECT t.*, c.name AS category_name, c.slug AS category_slug
     FROM tours t
     JOIN categories c ON c.id = t.category_id
     WHERE t.is_popular = 1
     ORDER BY t.rating DESC
     LIMIT 8'
)->fetchAll();

/* Все категории */
$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>

<main>
    <section class="hero">
        <div class="container">
            <div class="hero__badge">Каталог туров онлайн</div>
            <h1 class="hero__title">Путешествия без лишней суеты</h1>
            <p class="hero__text">Море, экскурсии, горы и круизы — выбрали, оформили, полетели.
                Без походов по офисам и звонков менеджеру.</p>
            <div class="hero__actions">
                <a href="/tours.php" class="btn btn--accent">Выбрать тур</a>
                <a href="#popular" class="btn btn--ghost">Популярные направления</a>
            </div>
        </div>
    </section>

    <section class="section" id="popular">
        <div class="container">
            <h2 class="section__title">Популярные направления</h2>

            <?php if (!$popular): ?>
                <div class="alert alert--error">Туры пока не добавлены. Зайдите позже!</div>
            <?php else: ?>
            <div class="grid">
                <?php foreach ($popular as $t): [$phCls, $phIcon] = tour_placeholder(['slug' => $t['category_slug']]); ?>
                <article class="card">
                    <!-- Изображение тура или заглушка с градиентом по категории -->
                    <div class="card__img card__img--<?= $phCls ?><?= $t['image'] ? ' card__img--img' : '' ?>">
                        <?php if ($t['image'] && is_file(UPLOAD_DIR . $t['image'])): ?>
                            <img src="/uploads/<?= e($t['image']) ?>" alt="<?= e($t['title']) ?>">
                        <?php else: echo $phIcon; endif; ?>
                    </div>
                    <div class="card__body">
                        <div><span class="chip"><?= e($t['category_name']) ?></span></div>
                        <h3 class="card__title"><a href="/tour.php?id=<?= (int)$t['id'] ?>"><?= e($t['title']) ?></a></h3>
                        <div class="card__meta">
                            <span>📍 <?= e($t['country']) ?></span>
                            <span>📅 <?= (int)$t['days'] ?> дн.</span>
                            <span class="rating">★ <?= e($t['rating']) ?></span>
                        </div>
                        <p class="card__desc"><?= e($t['description']) ?></p>
                        <div class="card__footer">
                            <span class="card__price"><?= price((float)$t['price']) ?></span>
                            <a href="/tour.php?id=<?= (int)$t['id'] ?>" class="btn btn--primary">Подробнее</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section__title">Категории туров</h2>
            <div class="grid">
                <?php foreach ($categories as $c): ?>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title"><a href="/tours.php?category=<?= (int)$c['id'] ?>"><?= e($c['name']) ?></a></h3>
                        <p class="card__desc"><?= e($c['description']) ?></p>
                        <div class="card__footer">
                            <span class="card__price" style="font-size:14px">
                                <?php
                                    // Количество туров в категории
                                    $cnt = db()->prepare('SELECT COUNT(*) FROM tours WHERE category_id = ?');
                                    $cnt->execute([$c['id']]);
                                    echo (int)$cnt->fetchColumn() . ' туров';
                                ?>
                            </span>
                            <a href="/tours.php?category=<?= (int)$c['id'] ?>" class="btn btn--ghost">Смотреть</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section__title">Как это работает</h2>
            <div class="grid">
                <article class="card"><div class="card__body">
                    <h3 class="card__title">1. Выбираете тур</h3>
                    <p class="card__desc">В каталоге — актуальные направления с ценой, отелем и длительностью. Фильтруете по стране или типу отдыха.</p>
                </div></article>
                <article class="card"><div class="card__body">
                    <h3 class="card__title">2. Оформляете заказ</h3>
                    <p class="card__desc">Добавляете туры в корзину и оформляете одной формой. Цена фиксируется на момент заказа.</p>
                </div></article>
                <article class="card"><div class="card__body">
                    <h3 class="card__title">3. Ждёте подтверждения</h3>
                    <p class="card__desc">Менеджер проверяет наличие мест у оператора и связывается с вами.</p>
                </div></article>
                <article class="card"><div class="card__body">
                    <h3 class="card__title">4. Получаете документы</h3>
                    <p class="card__desc">Путёвка, ваучер на отель и страховка приходят на почту после оплаты.</p>
                </div></article>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
