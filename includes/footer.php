<?php ?>
<!-- подвал сайта -->
<footer class="footer">
    <div class="container">
        <div class="footer__inner">
            <div class="footer__col">
                <div class="footer__logo">✈ TravelGuide</div>
                <p class="footer__text">Путеводители, советы и истории для самостоятельных путешественников.</p>
            </div>
            <div class="footer__col">
                <h3 class="footer__title">Разделы</h3>
                <ul class="footer__list">
                    <li><a href="/">Главная</a></li>
                    <li><a href="/guides.php">Путеводители</a></li>
                    <li><a href="/advice.php">Советы и рекомендации</a></li>
                    <li><a href="/gallery.php">Фотогалереи</a></li>
                    <li><a href="/about.php">О нас</a></li>
                    <li><a href="/blog.php">Блог</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h3 class="footer__title">Контакты</h3>
                <ul class="footer__list">
                    <li>+7 (800) 555-35-35</li>
                    <li>info@travel-guide.local</li>
                    <li>г. Стерлитамак, ул. Путешественников, 1</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="container">© <?= date('Y') ?> TravelGuide — учебный проект по дисциплине «Веб-разработка»</div>
    </div>
</footer>
<script src="/js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>
</html>
