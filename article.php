<?php
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT * FROM articles WHERE id = ?');
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    echo '<main class="section"><div class="container"><div class="alert alert--error">Статья не найдена.</div><a href="/" class="btn btn--primary">На главную</a></div></main>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

/* добавление комментария (только авторизованные) */
$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $user = current_user();
    if ($user === null) {
        header('Location: /login.php?redirect=/article.php?id=' . $id);
        exit;
    }
    $body = trim((string)$_POST['comment']);
    if (mb_strlen($body) < 3) {
        $commentError = 'Комментарий слишком короткий.';
    } else {
        db()->prepare('INSERT INTO comments (user_id, article_id, body) VALUES (?, ?, ?)')
           ->execute([$user['id'], $id, $body]);
        flash('Комментарий добавлен.');
        header('Location: /article.php?id=' . $id . '#comments');
        exit;
    }
}

/* комментарии к статье */
$stmt = db()->prepare(
    'SELECT c.*, u.first_name, u.last_name FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.article_id = ? ORDER BY c.created_at DESC'
);
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

$user = current_user();
$sectionName = $article['section'] === 'advice' ? 'Советы и рекомендации' : 'Блог';
$sectionUrl   = $article['section'] === 'advice' ? '/advice.php' : '/blog.php';
$pageTitle = $article['title'];
?>

<main class="section">
    <div class="container">
        <div style="max-width:820px; margin:0 auto;">

        <!-- хлебные крошки -->
        <nav class="crumbs" aria-label="Хлебные крошки">
            <a href="/">Главная</a><span>/</span>
            <a href="<?= $sectionUrl ?>"><?= e($sectionName) ?></a><span>/</span>
            <span><?= e($article['title']) ?></span>
        </nav>

        <!-- статья: читабельная колонка -->
        <article class="article">
            <span class="chip"><?= e($sectionName) ?></span>
            <h1 style="font-size:clamp(24px,3vw,34px); margin:14px 0 0;"><?= e($article['title']) ?></h1>

            <div class="article__meta">
                <span><?= e($article['author_name']) ?></span>
                <span>·</span>
                <span><?= e(date('d.m.Y', strtotime($article['created_at']))) ?></span>
            </div>

            <p class="article__lead"><?= e($article['excerpt']) ?></p>
            <div class="article__body"><?= e($article['body']) ?></div>
        </article>

        <!-- комментарии: отдельная секция с отступом -->
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

            <!-- форма комментария -->
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
                Чтобы оставить комментарий, <a href="/login.php?redirect=/article.php?id=<?= (int)$id ?>">войдите</a>
                или <a href="/register.php">зарегистрируйтесь</a>.
            </div>
            <?php endif; ?>
        </section>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
