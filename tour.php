<?php
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* Данные тура */
$stmt = db()->prepare(
    'SELECT t.*, c.name AS category_name, c.slug AS category_slug
     FROM tours t JOIN categories c ON c.id = t.category_id
     WHERE t.id = ?'
);
$stmt->execute([$id]);
$tour = $stmt->fetch();

/* Заглушка изображения по категории */
$phMap = [
    'beach'     => ['beach', '🏖'],
    'excursion' => ['excursion', '🏛'],
    'mountains' => ['mountains', '🏔'],
    'cruise'    => ['cruise', '🚢'],
    'health'    => ['health', '🌿'],
];
[$phCls, $phIcon] = $phMap[$tour['category_slug'] ?? ''] ?? ['', '🌍'];

if (!$tour) {
    http_response_code(404);
    echo '<main class="section"><div class="container"><div class="alert alert--error">Тур не найден.</div><a href="/tours.php" class="btn btn--primary">В каталог</a></div></main>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

/* Похожие туры той же категории */
$stmt = db()->prepare(
    'SELECT t.*, c.slug AS category_slug FROM tours t JOIN categories c ON c.id = t.category_id
     WHERE t.category_id = ? AND t.id != ? ORDER BY t.rating DESC LIMIT 4'
);
$stmt->execute([$tour['category_id'], $id]);
$similar = $stmt->fetchAll();

/* Обработка добавления в корзину (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $u = current_user();
    if ($u === null) {
        // Неавторизованного — отправить на вход
        header('Location: /login.php?redirect=/tour.php?id=' . $id);
        exit;
    }
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    // Найти или создать корзину пользователя
    $stmt = db()->prepare('SELECT id FROM carts WHERE user_id = ?');
    $stmt->execute([$u['id']]);
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        db()->prepare('INSERT INTO carts (user_id) VALUES (?)')->execute([$u['id']]);
        $cartId = (int)db()->lastInsertId();
    }

    // Добавить тур или увеличить количество
    $stmt = db()->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = ? AND tour_id = ?');
    $stmt->execute([$cartId, $id]);
    $item = $stmt->fetch();

    if ($item) {
        db()->prepare('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?')
           ->execute([$qty, $item['id']]);
    } else {
        db()->prepare('INSERT INTO cart_items (cart_id, tour_id, quantity) VALUES (?, ?, ?)')
           ->execute([$cartId, $id, $qty]);
    }

    flash('Тур «' . $tour['title'] . '» добавлен в корзину.');
    header('Location: /cart.php');
    exit;
}
?>

<main class="section">
    <div class="container">
        <!-- Хлебные крошки -->
        <nav class="crumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span>
            <a href="/tours.php">Туры</a><span>/</span>
            <a href="/tours.php?category=<?= (int)$tour['category_id'] ?>"><?= e($tour['category_name']) ?></a><span>/</span>
            <span><?= e($tour['title']) ?></span>
        </nav>

        <div class="tour-detail">
            <div class="tour-detail__img tour-detail__img--<?= $phCls ?>">
                <?php if ($tour['image'] && is_file(UPLOAD_DIR . $tour['image'])): ?>
                    <img src="/uploads/<?= e($tour['image']) ?>" alt="<?= e($tour['title']) ?>">
                <?php else: echo $phIcon; endif; ?>
            </div>

            <div class="tour-detail__body">
                <span class="chip"><?= e($tour['category_name']) ?></span>
                <h1 style="font-size:clamp(24px,3vw,34px); margin:12px 0 8px;"><?= e($tour['title']) ?></h1>
                <div class="rating" style="font-size:16px;">★ Рейтинг <?= e($tour['rating']) ?> из 5</div>
                <p style="margin:16px 0;"><?= e($tour['description']) ?></p>
                <ul class="tour-detail__props">
                    <li><strong>Страна:</strong> <?= e($tour['country']) ?></li>
                    <li><strong>Курорт:</strong> <?= e($tour['resort'] ?: '—') ?></li>
                    <li><strong>Длительность:</strong> <?= (int)$tour['days'] ?> дней</li>
                    <li><strong>Вылет:</strong> ежедневно</li>
                </ul>
            </div>

            <div class="tour-detail__side">
                <div class="tour-detail__price"><?= price((float)$tour['price']) ?></div>
                <p class="form__hint">за одного человека</p>

                <?php $u = current_user(); ?>
                <?php if ($u && $u['role'] === 'client'): ?>
                    <form method="post">
                        <input type="hidden" name="add_to_cart" value="1">
                        <div class="form__row">
                            <label class="form__label" for="quantity">Туристов</label>
                            <input class="form__input" type="number" id="quantity" name="quantity"
                                   value="1" min="1" max="20" style="width:100%;">
                        </div>
                        <button type="submit" class="btn btn--accent btn--block">🛒 В корзину</button>
                    </form>
                <?php elseif ($u): ?>
                    <a href="/cart.php" class="btn btn--accent btn--block">Перейти в корзину</a>
                    <p class="form__hint" style="text-align:center;">Администратор не оформляет заказы.</p>
                <?php else: ?>
                    <a href="/login.php?redirect=/tour.php?id=<?= (int)$id ?>" class="btn btn--accent btn--block">Войти, чтобы купить</a>
                    <a href="/register.php" class="btn btn--ghost btn--block">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($similar): ?>
        <section class="section">
            <h2 class="section__title">Похожие туры</h2>
            <div class="grid">
                <?php foreach ($similar as $t):
                    [$phC, $phI] = $phMap[$t['category_slug'] ?? ''] ?? ['', '🌍']; ?>
                <article class="card">
                    <div class="card__img card__img--<?= $phC ?><?= $t['image'] ? ' card__img--img' : '' ?>">
                        <?php if ($t['image'] && is_file(UPLOAD_DIR . $t['image'])): ?>
                            <img src="/uploads/<?= e($t['image']) ?>" alt="<?= e($t['title']) ?>">
                        <?php else: echo $phI; endif; ?>
                    </div>
                    <div class="card__body">
                        <h3 class="card__title"><a href="/tour.php?id=<?= (int)$t['id'] ?>"><?= e($t['title']) ?></a></h3>
                        <div class="card__meta">
                            <span>📍 <?= e($t['country']) ?></span>
                            <span class="rating">★ <?= e($t['rating']) ?></span>
                        </div>
                        <div class="card__footer">
                            <span class="card__price"><?= price((float)$t['price']) ?></span>
                            <a href="/tour.php?id=<?= (int)$t['id'] ?>" class="btn btn--primary">Подробнее</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
