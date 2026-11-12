<?php
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT * FROM photos WHERE id = ?');
$stmt->execute([$id]);
$photo = $stmt->fetch();

if (!$photo) {
    http_response_code(404);
    echo '<main class="section"><div class="container"><div class="alert alert--error">Фотография не найдена.</div><a href="/gallery.php" class="btn btn--primary">К галерее</a></div></main>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

/* добавление комментария к фото */
$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $user = current_user();
    if ($user === null) {
        header('Location: /login.php?redirect=/photo.php?id=' . $id);
        exit;
    }
    $body = trim((string)$_POST['comment']);
    if (mb_strlen($body) < 3) {
        $commentError = 'Комментарий слишком короткий.';
    } else {
        db()->prepare('INSERT INTO comments (user_id, photo_id, body) VALUES (?, ?, ?)')
           ->execute([$user['id'], $id, $body]);
        flash('Комментарий добавлен.');
        header('Location: /photo.php?id=' . $id . '#comments');
        exit;
    }
}

/* список комментариев */
$stmt = db()->prepare(
    'SELECT c.*, u.first_name, u.last_name FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.photo_id = ? ORDER BY c.created_at DESC'
);
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

$user = current_user();
$pageTitle = $photo['title'];
?>

<main class="section">
    <div class="container">
        <!-- хлебные крошки -->
        <nav class="crumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span>
            <a href="/gallery.php">Фотогалереи</a><span>/</span>
            <a href="/gallery.php?country=<?= urlencode($photo['country']) ?>"><?= e($photo['country']) ?></a><span>/</span>
            <span><?= e($photo['title']) ?></span>
        </nav>

        <!-- фото и подпись -->
        <div class="page-head tour-detail__img--<?= e($photo['ph_class']) ?>" style="align-items:flex-start;">
            <div class="page-head__content">
                <span class="chip"><?= e($photo['country']) ?></span>
                <h1><?= e($photo['title']) ?></h1>
                <p><?= e($photo['caption']) ?></p>
            </div>
        </div>

        <!-- комментарии -->
        <section class="comments" id="comments">
            <h2 class="section__title">Комментарии <small style="font-weight:400; color:var(--text-dim); font-size:16px;">(<?= count($comments) ?>)</small></h2>

            <?php foreach ($comments as $c): ?>
            <div class="comment">
                <div class="comment__head">
                    <span class="comment__author"><?= e($c['last_name'] . ' ' . $c['first_name']) ?></span>
                    <span><?= e(date('d.m.Y H:i', strtotime($c['created_at']))) ?></span>
                </div>
                <p class="comment__text"><?= nl2br(e($c['body'])) ?></p>
            </div>
            <?php endforeach; ?>

            <?php if (!$comments): ?>
                <p class="form__hint">Комментариев пока нет — будьте первым.</p>
            <?php endif; ?>

            <?php if ($user): ?>
            <form class="form form--wide" method="post" style="max-width:760px; margin-top:24px;">
                <div class="form__row">
                    <label class="form__label" for="comment">Ваш комментарий</label>
                    <textarea class="form__textarea" id="comment" name="comment" rows="4" required></textarea>
                </div>
                <?php if ($commentError): ?>
                    <div class="alert alert--error"><?= e($commentError) ?></div>
                <?php endif; ?>
                <button type="submit" class="btn btn--primary">Отправить</button>
            </form>
            <?php else: ?>
            <div class="alert alert--success" style="margin-top:24px;">
                Чтобы прокомментировать фото, <a href="/login.php?redirect=/photo.php?id=<?= (int)$id ?>">войдите</a>
                или <a href="/register.php">зарегистрируйтесь</a>.
            </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
