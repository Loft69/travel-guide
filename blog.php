<?php
require_once __DIR__ . '/includes/header.php';

$articles = db()->query(
    "SELECT * FROM articles WHERE section = 'blog' ORDER BY created_at DESC"
)->fetchAll();

$pageTitle = 'Блог';
?>

<main class="section">
    <div class="container">
        <div style="max-width:860px; margin:0 auto;">

        <h1 class="section__title">Блог</h1>
        <p class="form__hint" style="margin:-16px 0 28px;">
            Личные путешествия авторов: истории, приключения и рекомендации из собственного опыта.
        </p>

        <?php if (!$articles): ?>
            <div class="alert alert--error">Записей в блоге пока нет.</div>
        <?php else: ?>
        <div class="postlist">
            <?php foreach ($articles as $a): ?>
            <article class="post">
                <div class="post__main">
                    <div class="post__meta">
                        <span class="chip">Блог</span>
                        <span><?= e($a['author_name']) ?></span>
                        <span><?= e(date('d.m.Y', strtotime($a['created_at']))) ?></span>
                    </div>
                    <h2 class="post__title">
                        <a href="/article.php?id=<?= (int)$a['id'] ?>"><?= e($a['title']) ?></a>
                    </h2>
                    <p class="post__excerpt"><?= e($a['excerpt']) ?></p>
                </div>
                <a href="/article.php?id=<?= (int)$a['id'] ?>" class="btn btn--primary">Читать</a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
