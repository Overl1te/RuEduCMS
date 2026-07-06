(function () {
    'use strict';

    var LOADER_DURATION = 1600;
    var MESSAGE_INTERVAL = 400;

    function showLoader(messages) {
        var loader = document.getElementById('install-loader');
        var statusEl = document.getElementById('loader-status');
        var barFill = document.getElementById('loader-bar-fill');
        if (!loader || !statusEl || !barFill) return Promise.resolve();

        loader.classList.remove('hidden');
        barFill.style.width = '0%';

        var msgs = messages && messages.length ? messages : ['Загрузка…'];
        var msgIndex = 0;
        statusEl.textContent = msgs[0];

        var msgTimer = setInterval(function () {
            msgIndex++;
            if (msgIndex < msgs.length) {
                statusEl.style.opacity = '0';
                setTimeout(function () {
                    statusEl.textContent = msgs[msgIndex];
                    statusEl.style.opacity = '1';
                }, 150);
            }
        }, MESSAGE_INTERVAL);

        var start = performance.now();
        function tick(now) {
            var elapsed = now - start;
            var pct = Math.min(100, (elapsed / LOADER_DURATION) * 100);
            barFill.style.width = pct + '%';
            if (elapsed < LOADER_DURATION) {
                requestAnimationFrame(tick);
            }
        }
        requestAnimationFrame(tick);

        return new Promise(function (resolve) {
            setTimeout(function () {
                clearInterval(msgTimer);
                barFill.style.width = '100%';
                statusEl.textContent = msgs[msgs.length - 1];
                setTimeout(resolve, 200);
            }, LOADER_DURATION);
        });
    }

    function hideLoader() {
        var loader = document.getElementById('install-loader');
        if (loader) loader.classList.add('hidden');
    }

    function parseMessages(el, fallback) {
        try {
            var raw = el.getAttribute('data-messages');
            if (raw) return JSON.parse(raw);
        } catch (e) { /* ignore */ }
        return fallback;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('loaded');

        document.querySelectorAll('[data-install-loading]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var messages = parseMessages(form, ['Загрузка…']);
                showLoader(messages).then(function () {
                    form.removeAttribute('data-install-loading');
                    form.submit();
                });
            });
        });

        document.querySelectorAll('[data-install-link]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var href = link.getAttribute('href');
                var messages = parseMessages(link, ['Проверка окружения…']);
                showLoader(messages).then(function () {
                    window.location.href = href;
                });
            });
        });

        document.querySelectorAll('form[data-install-check]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var messages = parseMessages(form, ['Проверка подключения…', 'Установка соединения…', 'Готово!']);
                showLoader(messages).then(function () {
                    form.removeAttribute('data-install-check');
                    form.submit();
                });
            });
        });
    });

    hideLoader();
})();
