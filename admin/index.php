<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_admin();

/* Статистика для дашборда */
$stat = db()->query(
    'SELECT
        (SELECT COUNT(*) FROM tours)      AS tours_count,
        (SELECT COUNT(*) FROM categories) AS categories_count,
        (SELECT COUNT(*) FROM orders)     AS orders_count,
        (SELECT COUNT(*) FROM users)      AS users_count,
        (SELECT COUNT(*) FROM orders WHERE status = "new") AS new_orders,
        (SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = "paid") AS revenue'
)->fetch();

$pageTitle = 'Админ-панель';
require_once __DIR__ . '/../includes/header.php';
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Админ-панель</h1>

        <nav class="admin-bar">
            <a class="btn btn--ghost active" href="/admin/index.php">📊 Сводка</a>
            <a class="btn btn--ghost" href="/admin/tours.php">✈ Туры</a>
            <a class="btn btn--ghost" href="/admin/categories.php">🏷 Категории</a>
            <a class="btn btn--ghost" href="/admin/orders.php">📦 Заказы</a>
        </nav>

        <div class="grid">
            <div class="card"><div class="card__body">
                <div class="card__meta">Туров в каталоге</div>
                <div class="tour-detail__price"><?= (int)$stat['tours_count'] ?></div>
            </div></div>
            <div class="card"><div class="card__body">
                <div class="card__meta">Категорий</div>
                <div class="tour-detail__price"><?= (int)$stat['categories_count'] ?></div>
            </div></div>
            <div class="card"><div class="card__body">
                <div class="card__meta">Заказов всего</div>
                <div class="tour-detail__price"><?= (int)$stat['orders_count'] ?></div>
            </div></div>
            <div class="card"><div class="card__body">
                <div class="card__meta">Пользователей</div>
                <div class="tour-detail__price"><?= (int)$stat['users_count'] ?></div>
            </div></div>
            <div class="card"><div class="card__body">
                <div class="card__meta">Новых заказов</div>
                <div class="tour-detail__price" style="color:var(--accent);"><?= (int)$stat['new_orders'] ?></div>
            </div></div>
            <div class="card"><div class="card__body">
                <div class="card__meta">Оплачено (выручка)</div>
                <div class="tour-detail__price"><?= price((float)$stat['revenue']) ?></div>
            </div></div>
        </div>

        <section class="section">
            <h2 class="section__title">Последние заказы</h2>
            <?php
            $recent = db()->query(
                'SELECT o.*, u.last_name, u.first_name FROM orders o
                 JOIN users u ON u.id = o.user_id
                 ORDER BY o.created_at DESC LIMIT 5'
            )->fetchAll();
            ?>
            <?php if (!$recent): ?>
                <div class="alert alert--success">Заказов пока нет.</div>
            <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <tr>
                        <th>№</th><th>Клиент</th><th>Сумма</th><th>Статус</th><th>Дата</th><th></th>
                    </tr>
                    <?php foreach ($recent as $o): ?>
                    <tr>
                        <td><?= (int)$o['id'] ?></td>
                        <td><?= e($o['last_name'] . ' ' . $o['first_name']) ?></td>
                        <td><?= price((float)$o['total_price']) ?></td>
                        <td><span class="status status--<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
                        <td><?= e(date('d.m.Y H:i', strtotime($o['created_at']))) ?></td>
                        <td><a class="btn btn--ghost" href="/admin/orders.php">Управление</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
