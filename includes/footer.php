<?php ?>
<footer class="footer">
    <div class="container">
        <div class="footer__inner">
            <div class="footer__col">
                <div class="footer__logo">✈ TravelGuide</div>
                <p class="footer__text">Продажа готовых туров по направлениям: море, экскурсии, горы, круизы.</p>
            </div>
            <div class="footer__col">
                <h3 class="footer__title">Разделы</h3>
                <ul class="footer__list">
                    <li><a href="/">Главная</a></li>
                    <li><a href="/tours.php">Каталог туров</a></li>
                    <li><a href="/cart.php">Корзина</a></li>
                    <li><a href="/register.php">Регистрация</a></li>
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
