<?php
require_once __DIR__ . '/includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_login();

$user = current_user();

if ($user['role'] === 'admin') {
    // Администратор не оформляет заказы
    header('Location: /cart.php');
    exit;
}

$error = '';

/* Создание заказа из корзины */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim((string)($_POST['comment'] ?? ''));

    // Данные корзины
    $stmt = db()->prepare(
        'SELECT c.id AS cart_id, ci.tour_id, ci.quantity, t.price
         FROM cart_items ci
         JOIN carts c ON c.id = ci.cart_id
         JOIN tours t ON t.id = ci.tour_id
         WHERE c.user_id = ?'
    );
    $stmt->execute([$user['id']]);
    $items = $stmt->fetchAll();

    if (!$items) {
        $error = 'Корзина пуста — заказывать нечего.';
    } else {
        $total = 0;
        foreach ($items as $i) {
            $total += (float)$i['price'] * (int)$i['quantity'];
        }

        $pdo = db();
        try {
            $pdo->beginTransaction();

            // 1. Заголовок заказа
            $stmt = $pdo->prepare(
                'INSERT INTO orders (user_id, total_price, status, comment) VALUES (?, ?, "new", ?)'
            );
            $stmt->execute([$user['id'], $total, $comment !== '' ? $comment : null]);
            $orderId = (int)$pdo->lastInsertId();

            // 2. Состав заказа (фиксируем цены на момент покупки)
            $stmtItem = $pdo->prepare(
                'INSERT INTO order_items (order_id, tour_id, quantity, price) VALUES (?, ?, ?, ?)'
            );
            foreach ($items as $i) {
                $stmtItem->execute([$orderId, $i['tour_id'], $i['quantity'], $i['price']]);
            }

            // 3. Очистка корзины
            $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')
                ->execute([$items[0]['cart_id']]);

            $pdo->commit();

            flash('Заказ №' . $orderId . ' оформлен! Менеджер свяжется с вами для подтверждения.');
            header('Location: /orders.php');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Не удалось оформить заказ. Попробуйте ещё раз.';
        }
    }
}

$pageTitle = 'Оформление заказа';
require_once __DIR__ . '/includes/header.php';
?>

<main class="section">
    <div class="container">
        <h1 class="section__title">Оформление заказа</h1>

        <?php if ($error): ?>
            <div class="alert alert--error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="tour-detail" style="grid-template-columns: 1fr 380px;">
            <div class="tour-detail__body">
                <h2 style="margin-bottom:16px;">Покупатель</h2>
                <ul class="tour-detail__props">
                    <li><strong>ФИО:</strong> <?= e($user['last_name'] . ' ' . $user['first_name'] . ' ' . ($user['middle_name'] ?: '')) ?></li>
                    <li><strong>E-mail:</strong> <?= e($user['email']) ?></li>
                    <li><strong>Логин:</strong> <?= e($user['login']) ?></li>
                </ul>

                <h2 style="margin:24px 0 16px;">Состав заказа</h2>
                <ul class="tour-detail__props">
                    <?php
                    // Повторный вывод состава для наглядности
                    $stmt = db()->prepare(
                        'SELECT t.title, ci.quantity, t.price
                         FROM cart_items ci
                         JOIN carts c ON c.id = ci.cart_id
                         JOIN tours t ON t.id = ci.tour_id
                         WHERE c.user_id = ?'
                    );
                    $stmt->execute([$user['id']]);
                    $view = $stmt->fetchAll();
                    $total = 0;
                    foreach ($view as $i):
                        $sum = (float)$i['price'] * (int)$i['quantity'];
                        $total += $sum;
                    ?>
                    <li><?= e($i['title']) ?> × <?= (int)$i['quantity'] ?> — <?= price($sum) ?></li>
                    <?php endforeach; ?>
                    <?php if (!$view): ?><li>Корзина пуста</li><?php endif; ?>
                </ul>
            </div>

            <div class="tour-detail__side">
                <div class="tour-detail__price"><?= price($total) ?></div>

                <form method="post">
                    <div class="form__row">
                        <label class="form__label" for="comment">Комментарий к заказу</label>
                        <textarea class="form__textarea" id="comment" name="comment"
                                  placeholder="Пожелания: даты вылета, номер телефона, предпочтения по отелю..."><?= e($_POST['comment'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn--accent btn--block"
                            <?= !$view ? 'disabled' : '' ?>>Подтвердить заказ</button>
                </form>

                <a href="/cart.php" class="btn btn--ghost">← Назад в корзину</a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
