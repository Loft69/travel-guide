<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_admin();

/* Добавление категории */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $name = trim((string)($_POST['name'] ?? ''));
    $desc = trim((string)($_POST['description'] ?? ''));

    if ($name !== '') {
        // slug из названия (латиница транслитом не требуется — используем id)
        $slug = 'cat-' . mb_strtolower(preg_replace('/[^0-9]+/', '', (string)time()));
        try {
            db()->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)')
               ->execute([$name, $slug, $desc ?: null]);
            flash('Категория «' . $name . '» добавлена.');
        } catch (PDOException $e) {
            flash('Ошибка: такая категория уже существует.');
        }
    }
    header('Location: /admin/categories.php');
    exit;
}

/* Удаление категории (туры удалятся каскадно) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT name FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $name = $stmt->fetchColumn();

    if ($name) {
        db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        flash('Категория «' . $name . '» и её туры удалены.');
    }
    header('Location: /admin/categories.php');
    exit;
}

/* Список категорий с числом туров */
$categories = db()->query(
    'SELECT c.*, (SELECT COUNT(*) FROM tours t WHERE t.category_id = c.id) AS tours_count
     FROM categories c ORDER BY c.name'
)->fetchAll();

$pageTitle = 'Управление категориями';
require_once __DIR__ . '/../includes/header.php';
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Управление категориями</h1>

        <nav class="admin-bar">
            <a class="btn btn--ghost" href="/admin/index.php">📊 Сводка</a>
            <a class="btn btn--ghost" href="/admin/tours.php">✈ Туры</a>
            <a class="btn btn--ghost active" href="/admin/categories.php">🏷 Категории</a>
            <a class="btn btn--ghost" href="/admin/orders.php">📦 Заказы</a>
        </nav>

        <section class="section" style="padding-top:0;">
            <h2 class="section__title">Новая категория</h2>
            <form class="form form--wide" method="post" style="max-width:100%;">
                <input type="hidden" name="action" value="create">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                    <div class="form__row">
                        <label class="form__label">Название *</label>
                        <input class="form__input" type="text" name="name" required>
                    </div>
                    <div class="form__row">
                        <label class="form__label">Описание</label>
                        <input class="form__input" type="text" name="description">
                    </div>
                </div>
                <button type="submit" class="btn btn--primary">Добавить категорию</button>
            </form>
        </section>

        <section class="section">
            <h2 class="section__title">Список категорий</h2>
            <div class="table-wrap">
                <table class="table">
                    <tr>
                        <th>ID</th><th>Название</th><th>Описание</th><th>Туров</th><th>Действия</th>
                    </tr>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= (int)$c['id'] ?></td>
                        <td><?= e($c['name']) ?></td>
                        <td><?= e($c['description']) ?></td>
                        <td><?= (int)$c['tours_count'] ?></td>
                        <td>
                            <form method="post" data-confirm="Удалить категорию «<?= e($c['name']) ?>»? Вместе с ней удалятся все её туры!">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
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
