<?php
require_once __DIR__ . '/includes/header.php';

/* Параметры фильтров (GET) */
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search     = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();

/* Выборка туров с учётом фильтров (prepared statements) */
$sql = 'SELECT t.*, c.name AS category_name, c.slug AS category_slug
        FROM tours t
        JOIN categories c ON c.id = t.category_id
        WHERE 1 = 1';
$params = [];

if ($categoryId > 0) {
    $sql .= ' AND t.category_id = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $sql .= ' AND (t.title LIKE ? OR t.country LIKE ? OR t.resort LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like]);
}
$sql .= ' ORDER BY t.is_popular DESC, t.rating DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$tours = $stmt->fetchAll();
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Каталог туров</h1>

        <form class="filters" method="get" action="/tours.php">
            <div class="filters__field filters__field--search">
                <label class="form__label" for="q">Поиск туров</label>
                <input class="form__input" type="text" id="q" name="q"
                       placeholder="Страна, курорт или название тура…"
                       value="<?= e($search) ?>">
            </div>
            <div class="filters__field filters__field--category">
                <label class="form__label" for="category">Категория</label>
                <select class="form__select" id="category" name="category">
                    <option value="0">Все категории</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $categoryId === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filters__actions">
                <button type="submit" class="btn btn--primary">Найти</button>
                <?php if ($search !== '' || $categoryId > 0): ?>
                    <a href="/tours.php" class="btn btn--ghost">Сбросить</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (!$tours): ?>
            <div class="alert alert--error">По вашему запросу ничего не найдено. Попробуйте изменить условия поиска.</div>
        <?php else: ?>
            <p class="form__hint" style="margin:-16px 0 24px;">Найдено туров: <?= count($tours) ?></p>

            <div class="grid">
                <?php
                /* Карта slug категории -> класс градиента и иконка */
                $phMap = [
                    'beach'     => ['beach', '🏖'],
                    'excursion' => ['excursion', '🏛'],
                    'mountains' => ['mountains', '🏔'],
                    'cruise'    => ['cruise', '🚢'],
                    'health'    => ['health', '🌿'],
                ];
                foreach ($tours as $t):
                    [$phCls, $phIcon] = $phMap[$t['category_slug']] ?? ['', '🌍'];
                ?>
                <article class="card">
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
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
