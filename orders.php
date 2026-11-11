<?php
require_once __DIR__ . '/includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_login();

$user = current_user();

/* Заказы пользователя */
$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC, id DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

/* Состав заказов (группировка по order_id) */
$orderItems = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT oi.order_id, t.title, oi.quantity, oi.price
         FROM order_items oi
         JOIN tours t ON t.id = oi.tour_id
         WHERE oi.order_id IN ($in)"
    );
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        $orderItems[$row['order_id']][] = $row;
    }
}

$statusNames = [
    'new'       => 'Новый',
    'confirmed' => 'Подтверждён',
    'paid'      => 'Оплачен',
    'cancelled' => 'Отменён',
];

$pageTitle = 'Мои заказы';
require_once __DIR__ . '/includes/header.php';
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Мои заказы</h1>

        <?php if (!$orders): ?>
            <div class="alert alert--success">У вас пока нет заказов.
                <a href="/tours.php">Выберите тур в каталоге</a>.</div>
        <?php else: ?>

            <?php foreach ($orders as $o): ?>
            <div class="tour-detail" style="margin-bottom:20px; grid-template-columns: 1fr;">
                <div class="tour-detail__body">
                    <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; align-items:center;">
                        <h2 style="font-size:20px;">Заказ №<?= (int)$o['id'] ?></h2>
                        <span class="status status--<?= e($o['status']) ?>">
                            <?= e($statusNames[$o['status']] ?? $o['status']) ?>
                        </span>
                    </div>

                    <p style="color:var(--text-dim); font-size:14px; margin:8px 0 16px;">
                        Дата: <?= e(date('d.m.Y H:i', strtotime($o['created_at']))) ?>
                    </p>

                    <ul class="tour-detail__props">
                        <?php foreach ($orderItems[$o['id']] ?? [] as $i): ?>
                        <li>
                            <?= e($i['title']) ?> × <?= (int)$i['quantity'] ?> —
                            <?= price((float)$i['price'] * (int)$i['quantity']) ?>
                        </li>
                        <?php endforeach; ?>
                        <li style="font-weight:700; border-top:2px solid var(--glass-line); margin-top:8px; padding-top:12px;">
                            Итого: <?= price((float)$o['total_price']) ?>
                        </li>
                    </ul>

                    <?php if ($o['comment']): ?>
                        <p style="margin-top:12px; font-size:14px; color:var(--text-dim);">
                            <strong>Комментарий:</strong> <?= e($o['comment']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
