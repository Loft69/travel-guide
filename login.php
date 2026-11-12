<?php
require_once __DIR__ . '/includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (current_user()) {
    header('Location: /');
    exit;
}

$error = '';
$login = '';
$redirect = isset($_GET['redirect']) ? (string) $_GET['redirect'] : '';
// Разрешаем только внутренние редиректы
if ($redirect !== '' && $redirect[0] !== '/') {
    $redirect = '';
}

/* Проверка учётных данных */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim((string) ($_POST['login'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($login === '' || $password === '') {
        $error = 'Заполните логин и пароль';
    } else {
        // Поиск по логину ИЛИ e-mail
        $stmt = db()->prepare('SELECT * FROM users WHERE login = ? OR email = ? LIMIT 1');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Успешный вход: сохраняем в сессию
            session_regenerate_id(true);           // защита от фиксации сессии
            $_SESSION['user_id'] = (int) $user['id'];

            // Администратора — в админку, клиента — на главную
            $target = $user['role'] === 'admin' ? ($redirect ?: '/') : ($redirect ?: '/');
            header('Location: ' . $target);
            exit;
        }
        $error = 'Неверный логин или пароль';
    }
}

$pageTitle = 'Вход';
require_once __DIR__ . '/includes/header.php';
?>

<main class="section">
    <div class="container">
        <form class="form" method="post" data-validate novalidate>
            <h1 class="form__title">Вход на сайт</h1>

            <?php if ($error): ?>
                <div class="alert alert--error"><?= e($error) ?></div>
            <?php endif; ?>

            <!-- Логин -->
            <div class="form__row">
                <label class="form__label" for="login">Логин или e-mail</label>
                <input class="form__input" type="text" id="login" name="login" value="<?= e($login) ?>" required>
                <div class="form__error" data-error-for="login"></div>
            </div>

            <!-- Пароль -->
            <div class="form__row">
                <label class="form__label" for="password">Пароль</label>
                <input class="form__input" type="password" id="password" name="password" required>
                <div class="form__error" data-error-for="password"></div>
            </div>

            <?php if ($redirect): ?>
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
            <?php endif; ?>

            <button type="submit" class="btn btn--primary btn--block">Войти</button>
            <div class="form__footer">Нет аккаунта? <a href="/register.php">Зарегистрируйтесь</a></div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>