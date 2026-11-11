document.addEventListener('DOMContentLoaded', function () {

    var burger = document.getElementById('burgerBtn');
    var nav = document.getElementById('mainNav');

    if (burger && nav) {
        burger.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('open');
            burger.classList.toggle('open', isOpen);
            burger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            burger.setAttribute('aria-label', isOpen ? 'Закрыть меню' : 'Открыть меню');
        });
    }

    var form = document.querySelector('form[data-validate]');

    function setError(input, message) {
        var err = document.querySelector('[data-error-for="' + input.name + '"]');
        input.classList.toggle('is-invalid', !!message);
        if (err) { err.textContent = message || ''; }
    }

    var rules = {
        last_name: function (v) {
            if (!v) return 'Введите фамилию';
            if (v.length < 2) return 'Фамилия: минимум 2 символа';
            if (!/^[А-Яа-яЁёA-Za-z\- ]+$/.test(v)) return 'Фамилия: только буквы';
            return '';
        },
        first_name: function (v) {
            if (!v) return 'Введите имя';
            if (v.length < 2) return 'Имя: минимум 2 символа';
            if (!/^[А-Яа-яЁёA-Za-z\- ]+$/.test(v)) return 'Имя: только буквы';
            return '';
        },
        login: function (v) {
            if (!v) return 'Введите логин';
            if (v.length < 3) return 'Логин: минимум 3 символа';
            if (!/^[A-Za-z0-9_]+$/.test(v)) return 'Логин: латиница, цифры и _';
            return '';
        },
        email: function (v) {
            if (!v) return 'Введите e-mail';
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Некорректный e-mail';
            return '';
        },
        password: function (v) {
            if (!v) return 'Введите пароль';
            if (v.length < 6) return 'Пароль: минимум 6 символов';
            return '';
        },
        password_confirm: function (v, formEl) {
            var pass = formEl.querySelector('[name="password"]');
            if (!v) return 'Повторите пароль';
            if (pass && v !== pass.value) return 'Пароли не совпадают';
            return '';
        }
    };

    if (form) {
        function validateField(input) {
            var rule = rules[input.name];
            if (!rule) return true;
            var msg = rule(input.value.trim(), form);
            setError(input, msg);
            return !msg;
        }

        form.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('blur', function () { validateField(input); });
            input.addEventListener('input', function () {
                if (input.classList.contains('is-invalid')) validateField(input);
            });
        });

        var agree = form.querySelector('[name="agree"]');
        if (agree) {
            agree.addEventListener('change', function () {
                var err = document.querySelector('[data-error-for="agree"]');
                if (err) { err.textContent = agree.checked ? '' : 'Подтвердите согласие с правилами'; }
            });
        }

        form.addEventListener('submit', function (e) {
            var ok = true;
            form.querySelectorAll('input').forEach(function (input) {
                if (rules[input.name] && !validateField(input)) ok = false;
            });
            if (agree && !agree.checked) {
                var err = document.querySelector('[data-error-for="agree"]');
                if (err) { err.textContent = 'Подтвердите согласие с правилами'; }
                ok = false;
            }
            if (!ok) { e.preventDefault(); }
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!confirm(f.getAttribute('data-confirm'))) { e.preventDefault(); }
        });
    });

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.grid .card').forEach(function (card, i) {
            card.style.transitionDelay = Math.min(i % 8, 5) * 60 + 'ms';
            observer.observe(card);
        });
    }
});
