<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_admin();

/* Удаление тура */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare('DELETE FROM tours WHERE id = ?')->execute([$id]);
    flash('Тур удалён.');
    header('Location: /admin/tours.php');
    exit;
}

/* Добавление тура */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $data = [
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'title'       => trim((string)($_POST['title'] ?? '')),
        'country'     => trim((string)($_POST['country'] ?? '')),
        'resort'      => trim((string)($_POST['resort'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'price'       => (float)($_POST['price'] ?? 0),
        'days'        => max(1, (int)($_POST['days'] ?? 1)),
        'rating'      => min(5, max(0, (float)($_POST['rating'] ?? 4.5))),
        'is_popular'  => isset($_POST['is_popular']) ? 1 : 0,
    ];

    if ($data['category_id'] > 0 && $data['title'] !== '' && $data['country'] !== '' && $data['price'] > 0) {
        db()->prepare(
            'INSERT INTO tours (category_id, title, country, resort, description, price, days, rating, is_popular)
             VALUES (:category_id, :title, :country, :resort, :description, :price, :days, :rating, :is_popular)'
        )->execute($data);
        flash('Тур «' . $data['title'] . '» добавлен.');
        header('Location: /admin/tours.php');
        exit;
    }
    flash('Ошибка: заполните категорию, название, страну и цену.');
    header('Location: /admin/tours.php');
    exit;
}

/* Редактирование тура */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'id'          => $id,
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'title'       => trim((string)($_POST['title'] ?? '')),
        'country'     => trim((string)($_POST['country'] ?? '')),
        'resort'      => trim((string)($_POST['resort'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'price'       => (float)($_POST['price'] ?? 0),
        'days'        => max(1, (int)($_POST['days'] ?? 1)),
        'rating'      => min(5, max(0, (float)($_POST['rating'] ?? 4.5))),
        'is_popular'  => isset($_POST['is_popular']) ? 1 : 0,
    ];

    if ($id > 0 && $data['category_id'] > 0 && $data['title'] !== '' && $data['price'] > 0) {
        db()->prepare(
            'UPDATE tours SET
                category_id = :category_id, title = :title, country = :country,
                resort = :resort, description = :description, price = :price,
                days = :days, rating = :rating, is_popular = :is_popular
             WHERE id = :id'
        )->execute($data);
        flash('Тур обновлён.');
    }
    header('Location: /admin/tours.php');
    exit;
}

/* Данные для страницы */
$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$tours = db()->query(
    'SELECT t.*, c.name AS category_name FROM tours t
     JOIN categories c ON c.id = t.category_id
     ORDER BY t.id'
)->fetchAll();

/* Тур для редактирования (?edit=ID) */
$editTour = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM tours WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editTour = $stmt->fetch();
}

$pageTitle = 'Управление турами';
require_once __DIR__ . '/../includes/header.php';

/** Фрагмент формы тура (используется для add и edit) */
function tour_form(array $categories, ?array $tour, string $action, string $buttonText): void
{
    $v = fn(string $key, $default = '') => e((string)($tour[$key] ?? $default));
    ?>
    <form class="form form--wide" method="post" style="max-width:100%;">
        <input type="hidden" name="action" value="<?= e($action) ?>">
        <?php if ($tour): ?><input type="hidden" name="id" value="<?= (int)$tour['id'] ?>"><?php endif; ?>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
            <div class="form__row">
                <label class="form__label">Название *</label>
                <input class="form__input" type="text" name="title" value="<?= $v('title') ?>" required>
            </div>
            <div class="form__row">
                <label class="form__label">Категория *</label>
                <select class="form__select" name="category_id" required>
                    <option value="">— выберите —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ($tour && (int)$tour['category_id'] === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__row">
                <label class="form__label">Страна *</label>
                <input class="form__input" type="text" name="country" value="<?= $v('country') ?>" required>
            </div>
            <div class="form__row">
                <label class="form__label">Курорт</label>
                <input class="form__input" type="text" name="resort" value="<?= $v('resort') ?>">
            </div>
            <div class="form__row">
                <label class="form__label">Цена, ₽ *</label>
                <input class="form__input" type="number" name="price" step="0.01" min="1" value="<?= $v('price') ?>" required>
            </div>
            <div class="form__row">
                <label class="form__label">Дней</label>
                <input class="form__input" type="number" name="days" min="1" value="<?= $v('days', 7) ?>">
            </div>
            <div class="form__row">
                <label class="form__label">Рейтинг (0–5)</label>
                <input class="form__input" type="number" name="rating" step="0.1" min="0" max="5" value="<?= $v('rating', 4.5) ?>">
            </div>
        </div>

        <div class="form__row">
            <label class="form__label">Описание</label>
            <textarea class="form__textarea" name="description"><?= $v('description') ?></textarea>
        </div>

        <div class="form__row">
            <label class="form__check">
                <input type="checkbox" name="is_popular" value="1" <?= ($tour && $tour['is_popular']) ? 'checked' : '' ?>>
                <span>Популярный тур (показывать на главной)</span>
            </label>
        </div>

        <button type="submit" class="btn btn--primary"><?= e($buttonText) ?></button>
        <?php if ($tour): ?>
            <a href="/admin/tours.php" class="btn btn--ghost">Отмена</a>
        <?php endif; ?>
    </form>
    <?php
}
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Управление турами</h1>

        <nav class="admin-bar">
            <a class="btn btn--ghost" href="/admin/index.php">📊 Сводка</a>
            <a class="btn btn--ghost active" href="/admin/tours.php">✈ Туры</a>
            <a class="btn btn--ghost" href="/admin/categories.php">🏷 Категории</a>
            <a class="btn btn--ghost" href="/admin/orders.php">📦 Заказы</a>
        </nav>

        <section class="section" style="padding-top:0;">
            <h2 class="section__title"><?= $editTour ? 'Редактирование тура' : 'Добавление тура' ?></h2>
            <?php tour_form($categories, $editTour, $editTour ? 'update' : 'create', $editTour ? 'Сохранить' : 'Добавить тур'); ?>
        </section>

        <section class="section">
            <h2 class="section__title">Список туров</h2>
            <div class="table-wrap">
                <table class="table">
                    <tr>
                        <th>ID</th><th>Название</th><th>Категория</th><th>Страна</th>
                        <th>Цена</th><th>Дней</th><th>Попул.</th><th>Действия</th>
                    </tr>
                    <?php foreach ($tours as $t): ?>
                    <tr>
                        <td><?= (int)$t['id'] ?></td>
                        <td><?= e($t['title']) ?></td>
                        <td><?= e($t['category_name']) ?></td>
                        <td><?= e($t['country']) ?></td>
                        <td><?= price((float)$t['price']) ?></td>
                        <td><?= (int)$t['days'] ?></td>
                        <td><?= $t['is_popular'] ? '★' : '—' ?></td>
                        <td style="white-space:nowrap;">
                            <a class="btn btn--ghost" href="/admin/tours.php?edit=<?= (int)$t['id'] ?>">Изменить</a>
                            <form method="post" style="display:inline;" data-confirm="Удалить тур «<?= e($t['title']) ?>»?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                <button class="btn btn--danger" type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
