<?php
// параметры БД
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root'); // Open Server: root без пароля
define('DB_PASS', '');
define('DB_NAME', 'travel_guide');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'Travel Guide');
define('CURRENCY', '₽');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// подключение к БД (одиночка, PDO)
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Ошибка подключения к базе данных: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function price($value): string
{
    return number_format((float)$value, 0, ',', ' ') . ' ' . CURRENCY;
}

function current_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function is_admin()
{
    $u = current_user();
    return $u !== null && $u['role'] === 'admin';
}

function require_login()
{
    if (current_user() === null) {
        header('Location: /login.php');
        exit;
    }
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Доступ запрещён. Раздел только для администратора.');
    }
}

function cart_count(): int
{
    $u = current_user();
    if ($u === null) {
        return 0;
    }
    $stmt = db()->prepare('SELECT COALESCE(SUM(ci.quantity), 0) FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = ?');
    $stmt->execute([$u['id']]);
    return (int)$stmt->fetchColumn();
}

function flash($message = null)
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}
