<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'О нас';
?>

<main class="section">
    <div class="container">
        <div style="max-width:860px; margin:0 auto;">

        <h1 class="section__title">О нас</h1>

        <!-- миссия и цели -->
        <div class="page-head tour-detail__img--sea" style="min-height:260px;">
            <div class="page-head__content">
                <span class="chip">Команда Travel Guide</span>
                <h1>Мы помогаем путешествовать самостоятельно</h1>
                <p>Travel Guide — проект команды, которая объездила полтора десятка стран и считает,
                    что настоящее путешествие начинается, когда ты планируешь его сам.</p>
            </div>
        </div>

        <section class="content-block">
            <h2 class="block-title">Миссия</h2>
            <p>Собрать в одном месте честную, проверенную лично информацию о городах и странах:
                не глянцевые брошюры, а рабочие советы, реальные цены и места, куда мы возвращаемся сами.</p>
        </section>

        <section class="content-block">
            <h2 class="block-title">Цели сайта</h2>
            <p>Дать путешественнику всё для планирования — от выбора направления до списка вещей в рюкзак;
                делиться опытом без рекламы и скрытых промо; собрать сообщество людей,
                для которых дорога — это состояние души.</p>
        </section>

        <!-- команда -->
        <div style="margin-top:48px;">
            <h2 class="section__title">Команда</h2>
            <div class="grid">
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title">Анна Ковалёва</h3>
                        <p class="card__meta" style="margin-bottom:10px;">Редактор, автор путеводителей</p>
                        <p class="card__desc">14 стран, любимая — Япония. Отвечает за «Путеводители» и тексты о планировании.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title">Дмитрий Лебедев</h3>
                        <p class="card__meta" style="margin-bottom:10px;">Автор блога, фотограф</p>
                        <p class="card__desc">11 стран, от Каппадокии до Камчатки. Пишет истории из поездок и фотографирует всё вокруг.</p>
                    </div>
                </article>
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title">Иван Сидоров</h3>
                        <p class="card__meta" style="margin-bottom:10px;">Разработка и поддержка сайта</p>
                        <p class="card__desc">Собрал сайт, следит за тем, чтобы всё работало, и тестирует советы команды на себе.</p>
                    </div>
                </article>
            </div>
        </div>

        <!-- контакты -->
        <div style="margin-top:48px;">
            <h2 class="section__title">Контакты</h2>
            <section class="content-block">
                <ul class="tour-detail__props">
                    <li><strong>Почта:</strong> <span>info@travel-guide.local — письма, идеи, поправки</span></li>
                    <li><strong>Телефон:</strong> <span>+7 (800) 555-35-35 — пн–пт, 10:00–18:00 (МСК)</span></li>
                    <li><strong>Адрес:</strong> <span>г. Стерлитамак, ул. Путешественников, 1</span></li>
                </ul>
            </section>
        </div>

        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
