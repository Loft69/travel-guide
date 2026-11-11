<?php
require_once __DIR__ . '/includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Уже авторизованные на регистрацию не нужны
if (current_user()) {
    header('Location: /');
    exit;
}

$errors = [];
$old    = ['last_name' => '', 'first_name' => '', 'middle_name' => '', 'login' => '', 'email' => ''];

/* Обработка отправленной формы */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Сбор и очистка данных
    $last      = trim((string)($_POST['last_name'] ?? ''));
    $first     = trim((string)($_POST['first_name'] ?? ''));
    $middle    = trim((string)($_POST['middle_name'] ?? ''));
    $login     = trim((string)($_POST['login'] ?? ''));
    $email     = trim((string)($_POST['email'] ?? ''));
    $password  = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password_confirm'] ?? '');
    $agree     = isset($_POST['agree']);

    $old['last_name'] = $last; $old['first_name'] = $first;
    $old['middle_name'] = $middle; $old['login'] = $login; $old['email'] = $email;

    /* Серверная валидация */
    if ($last === '' || !preg_match('/^[А-Яа-яЁёA-Za-z\- ]{2,}$/u', $last)) {
        $errors['last_name'] = 'Укажите фамилию (только буквы, минимум 2 символа)';
    }
    if ($first === '' || !preg_match('/^[А-Яа-яЁёA-Za-z\- ]{2,}$/u', $first)) {
        $errors['first_name'] = 'Укажите имя (только буквы, минимум 2 символа)';
    }
    if ($middle !== '' && !preg_match('/^[А-Яа-яЁёA-Za-z\- ]{2,}$/u', $middle)) {
        $errors['middle_name'] = 'Отчество: только буквы';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,}$/', $login)) {
        $errors['login'] = 'Логин: 3+ символа, латиница, цифры, подчёркивание';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный e-mail';
    }
    if (strlen($password) < 6) {
        $errors['password'] = 'Пароль: минимум 6 символов';
    }
    if ($password !== $password2) {
        $errors['password_confirm'] = 'Пароли не совпадают';
    }
    if (!$agree) {
        $errors['agree'] = 'Необходимо согласие с правилами регистрации';
    }

    // Проверка занятости логина и e-mail
    if (!isset($errors['login']) && !isset($errors['email'])) {
        $stmt = db()->prepare('SELECT login, email FROM users WHERE login = ? OR email = ?');
        $stmt->execute([$login, $email]);
        if ($row = $stmt->fetch()) {
            if (strcasecmp($row['login'], $login) === 0) {
                $errors['login'] = 'Этот логин уже занят';
            }
            if (strcasecmp($row['email'], $email) === 0) {
                $errors['email'] = 'Этот e-mail уже зарегистрирован';
            }
        }
    }

    /* Сохранение пользователя */
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = db()->prepare(
            'INSERT INTO users (last_name, first_name, middle_name, login, email, password_hash, role)
             VALUES (?, ?, ?, ?, ?, ?, "client")'
        );
        $stmt->execute([$last, $first, $middle ?: null, $login, $email, $hash]);

        // Автоматический вход после регистрации
        $_SESSION['user_id'] = (int)db()->lastInsertId();
        flash('Регистрация завершена! Добро пожаловать, ' . $first . '.');
        header('Location: /');
        exit;
    }
}

$pageTitle = 'Регистрация';
require_once __DIR__ . '/includes/header.php';
?>

<main class="section">
    <div class="container">
        <form class="form" method="post" data-validate novalidate>
            <h1 class="form__title">Регистрация</h1>

            <?php if ($errors): ?>
                <div class="alert alert--error">Проверьте правильность заполнения полей.</div>
            <?php endif; ?>

            <!-- Фамилия -->
            <div class="form__row">
                <label class="form__label" for="last_name">Фамилия *</label>
                <input class="form__input<?= isset($errors['last_name']) ? ' is-invalid' : '' ?>"
                       type="text" id="last_name" name="last_name"
                       value="<?= e($old['last_name']) ?>" required>
                <div class="form__error" data-error-for="last_name"><?= e($errors['last_name'] ?? '') ?></div>
            </div>

            <!-- Имя -->
            <div class="form__row">
                <label class="form__label" for="first_name">Имя *</label>
                <input class="form__input<?= isset($errors['first_name']) ? ' is-invalid' : '' ?>"
                       type="text" id="first_name" name="first_name"
                       value="<?= e($old['first_name']) ?>" required>
                <div class="form__error" data-error-for="first_name"><?= e($errors['first_name'] ?? '') ?></div>
            </div>

            <!-- Отчество -->
            <div class="form__row">
                <label class="form__label" for="middle_name">Отчество</label>
                <input class="form__input" type="text" id="middle_name" name="middle_name"
                       value="<?= e($old['middle_name']) ?>">
                <div class="form__error" data-error-for="middle_name"></div>
            </div>

            <!-- Логин -->
            <div class="form__row">
                <label class="form__label" for="login">Логин *</label>
                <input class="form__input<?= isset($errors['login']) ? ' is-invalid' : '' ?>"
                       type="text" id="login" name="login"
                       value="<?= e($old['login']) ?>" required>
                <div class="form__error" data-error-for="login"><?= e($errors['login'] ?? '') ?></div>
            </div>

            <!-- E-mail -->
            <div class="form__row">
                <label class="form__label" for="email">E-mail *</label>
                <input class="form__input<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                       type="email" id="email" name="email"
                       value="<?= e($old['email']) ?>" required>
                <div class="form__error" data-error-for="email"><?= e($errors['email'] ?? '') ?></div>
            </div>

            <!-- Пароль -->
            <div class="form__row">
                <label class="form__label" for="password">Пароль *</label>
                <input class="form__input<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                       type="password" id="password" name="password" required>
                <div class="form__hint">Минимум 6 символов</div>
                <div class="form__error" data-error-for="password"><?= e($errors['password'] ?? '') ?></div>
            </div>

            <!-- Повтор пароля -->
            <div class="form__row">
                <label class="form__label" for="password_confirm">Повторите пароль *</label>
                <input class="form__input<?= isset($errors['password_confirm']) ? ' is-invalid' : '' ?>"
                       type="password" id="password_confirm" name="password_confirm" required>
                <div class="form__error" data-error-for="password_confirm"><?= e($errors['password_confirm'] ?? '') ?></div>
            </div>

            <!-- Согласие с правилами -->
            <div class="form__row">
                <label class="form__check">
                    <input type="checkbox" name="agree" value="1" <?= isset($_POST['agree']) ? 'checked' : '' ?>>
                    <span>Согласен с <a href="#">правилами регистрации</a> и обработкой персональных данных *</span>
                </label>
                <div class="form__error" data-error-for="agree"><?= e($errors['agree'] ?? '') ?></div>
            </div>

            <button type="submit" class="btn btn--primary btn--block">Зарегистрироваться</button>
            <div class="form__footer">Уже есть аккаунт? <a href="/login.php">Войдите</a></div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
