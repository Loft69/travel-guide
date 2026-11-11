<?php
require_once __DIR__ . '/../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_admin();

$statuses = [
    'new'       => 'Новый',
    'confirmed' => 'Подтверждён',
    'paid'      => 'Оплачен',
    'cancelled' => 'Отменён',
];

/* Смена статуса заказа */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'status') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');

    if ($id > 0 && isset($statuses[$status])) {
        db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $id]);
        flash('Статус заказа №' . $id . ' изменён на «' . $statuses[$status] . '».');
    }
    header('Location: /admin/orders.php');
    exit;
}

/* Все заказы с данными клиентов */
$stmt = db()->query(
    'SELECT o.*, u.last_name, u.first_name, u.login, u.email
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY FIELD(o.status, "new", "confirmed", "paid", "cancelled"), o.created_at DESC'
);
$orders = $stmt->fetchAll();

/* Состав всех заказов */
$items = [];
if ($orders) {
    $rows = db()->query(
        'SELECT oi.order_id, t.title, oi.quantity, oi.price
         FROM order_items oi JOIN tours t ON t.id = oi.tour_id
         ORDER BY oi.order_id'
    )->fetchAll();
    foreach ($rows as $r) {
        $items[$r['order_id']][] = $r;
    }
}

$pageTitle = 'Управление заказами';
require_once __DIR__ . '/../includes/header.php';
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Управление заказами</h1>

        <nav class="admin-bar">
            <a class="btn btn--ghost" href="/admin/index.php">📊 Сводка</a>
            <a class="btn btn--ghost" href="/admin/tours.php">✈ Туры</a>
            <a class="btn btn--ghost" href="/admin/categories.php">🏷 Категории</a>
            <a class="btn btn--ghost active" href="/admin/orders.php">📦 Заказы</a>
        </nav>

        <?php if (!$orders): ?>
            <div class="alert alert--success">Заказов пока нет.</div>
        <?php endif; ?>

        <?php foreach ($orders as $o): ?>
        <div class="tour-detail" style="margin-bottom:20px; grid-template-columns: 1fr;">
            <div class="tour-detail__body">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; align-items:center;">
                    <h2 style="font-size:20px;">Заказ №<?= (int)$o['id'] ?></h2>
                    <span class="status status--<?= e($o['status']) ?>">
                        <?= e($statuses[$o['status']] ?? $o['status']) ?>
                    </span>
                </div>

                <p style="color:var(--text-dim); font-size:14px; margin:8px 0 12px;">
                    <?= e($o['last_name'] . ' ' . $o['first_name']) ?>
                    (<?= e($o['login']) ?>, <?= e($o['email']) ?>) ·
                    <?= e(date('d.m.Y H:i', strtotime($o['created_at']))) ?>
                </p>

                <ul class="tour-detail__props">
                    <?php foreach ($items[$o['id']] ?? [] as $i): ?>
                    <li><?= e($i['title']) ?> × <?= (int)$i['quantity'] ?> —
                        <?= price((float)$i['price'] * (int)$i['quantity']) ?></li>
                    <?php endforeach; ?>
                    <li style="font-weight:700; border-top:2px solid var(--glass-line); margin-top:8px; padding-top:12px;">
                        Итого: <?= price((float)$o['total_price']) ?>
                    </li>
                </ul>

                <?php if ($o['comment']): ?>
                    <p style="margin-top:12px; font-size:14px; color:var(--text-dim);">
                        <strong>Комментарий клиента:</strong> <?= e($o['comment']) ?></p>
                <?php endif; ?>

                <form method="post" style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                    <label class="form__label" style="margin:0;">Новый статус:</label>
                    <select class="form__select" name="status" style="width:auto;">
                        <?php foreach ($statuses as $key => $name): ?>
                            <option value="<?= e($key) ?>" <?= $o['status'] === $key ? 'selected' : '' ?>>
                                <?= e($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn--primary">Изменить статус</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
