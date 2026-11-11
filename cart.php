<?php
require_once __DIR__ . '/includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_login();   // корзина доступна только авторизованным

$user = current_user();

/* Обработка действий над корзиной (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'update') {
        // Изменение количества: increase / decrease / remove
        $itemId = (int)($_POST['item_id'] ?? 0);
        $op     = (string)($_POST['op'] ?? '');

        // Проверяем, что позиция принадлежит корзине текущего пользователя
        $stmt = db()->prepare(
            'SELECT ci.id, ci.quantity FROM cart_items ci
             JOIN carts c ON c.id = ci.cart_id
             WHERE ci.id = ? AND c.user_id = ?'
        );
        $stmt->execute([$itemId, $user['id']]);
        $item = $stmt->fetch();

        if ($item) {
            if ($op === 'increase') {
                db()->prepare('UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?')
                   ->execute([$itemId]);
            } elseif ($op === 'decrease') {
                if ($item['quantity'] > 1) {
                    db()->prepare('UPDATE cart_items SET quantity = quantity - 1 WHERE id = ?')
                       ->execute([$itemId]);
                } else {
                    db()->prepare('DELETE FROM cart_items WHERE id = ?')->execute([$itemId]);
                }
            } elseif ($op === 'remove') {
                db()->prepare('DELETE FROM cart_items WHERE id = ?')->execute([$itemId]);
            }
        }
        header('Location: /cart.php');
        exit;
    }

    if ($action === 'clear') {
        // Полная очистка корзины
        $stmt = db()->prepare('SELECT id FROM carts WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        if ($cartId = $stmt->fetchColumn()) {
            db()->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);
        }
        flash('Корзина очищена.');
        header('Location: /cart.php');
        exit;
    }
}

/* Содержимое корзины */
$stmt = db()->prepare(
    'SELECT ci.id AS item_id, ci.quantity, t.id AS tour_id, t.title, t.country, t.price, t.days
     FROM cart_items ci
     JOIN carts c  ON c.id = ci.cart_id
     JOIN tours t  ON t.id = ci.tour_id
     WHERE c.user_id = ?
     ORDER BY ci.id'
);
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $i) {
    $total += (float)$i['price'] * (int)$i['quantity'];
}

$pageTitle = 'Корзина';
require_once __DIR__ . '/includes/header.php';
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Корзина</h1>

        <?php if (!$items): ?>
            <div class="alert alert--success">Корзина пуста. <a href="/tours.php">Выберите тур в каталоге</a>.</div>
        <?php else: ?>

            <?php foreach ($items as $i): ?>
            <div class="cart-item">
                <div class="cart-item__icon">✈</div>
                <div>
                    <h3 class="card__title" style="font-size:17px;">
                        <a href="/tour.php?id=<?= (int)$i['tour_id'] ?>"><?= e($i['title']) ?></a>
                    </h3>
                    <div class="card__meta">
                        <span>📍 <?= e($i['country']) ?></span>
                        <span>📅 <?= (int)$i['days'] ?> дн.</span>
                        <span>Цена за 1 чел.: <?= price((float)$i['price']) ?></span>
                    </div>
                </div>

                <!-- Изменение количества -->
                <div class="cart-item__qty">
                    <form method="post" style="display:flex; gap:8px; align-items:center;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="item_id" value="<?= (int)$i['item_id'] ?>">
                        <button class="qty-btn" type="submit" name="op" value="decrease" aria-label="Уменьшить">−</button>
                        <span class="qty-num"><?= (int)$i['quantity'] ?></span>
                        <button class="qty-btn" type="submit" name="op" value="increase" aria-label="Увеличить">+</button>
                    </form>
                </div>

                <div class="cart-item__price card__price">
                    <?= price((float)$i['price'] * (int)$i['quantity']) ?>
                </div>

                <!-- Удаление позиции -->
                <form method="post" data-confirm="Удалить тур из корзины?">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="item_id" value="<?= (int)$i['item_id'] ?>">
                    <input type="hidden" name="op" value="remove">
                    <button class="btn btn--danger" type="submit">Удалить</button>
                </form>
            </div>
            <?php endforeach; ?>

            <div class="cart-summary" style="max-width:460px; margin-left:auto;">
                <div class="cart-summary__total">
                    <span>Итого:</span>
                    <span><?= price($total) ?></span>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="/checkout.php" class="btn btn--accent" style="flex:1;">Оформить заказ</a>
                    <form method="post" data-confirm="Очистить корзину полностью?">
                        <input type="hidden" name="action" value="clear">
                        <button class="btn btn--ghost" type="submit">Очистить</button>
                    </form>
                </div>
                <a href="/tours.php" class="btn btn--ghost">← Продолжить выбор туров</a>
            </div>

        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
