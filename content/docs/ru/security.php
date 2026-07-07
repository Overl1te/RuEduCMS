<p>RuEduCMS включает встроенные механизмы защиты от злоупотреблений, инъекций и спама. Для production рекомендуется дополнительно использовать reverse proxy (Cloudflare, nginx rate limiting).</p>

<h3>Защита от инъекций</h3>
<ul>
    <li><strong>SQL</strong> — все запросы выполняются через PDO с подготовленными выражениями.</li>
    <li><strong>XSS</strong> — контент страниц и статей очищается через <code>HtmlSanitizer</code> при сохранении; URL меню фильтруются через <code>UrlSafety</code>.</li>
    <li><strong>CSRF</strong> — формы админки, установщика и публичная обратная связь используют одноразовые токены сессии.</li>
</ul>

<h3>Настройки безопасности (только config.php)</h3>
<p>Параметры безопасности <strong>не отображаются в админке</strong>. Их задаёт администратор сервера в файле <code>config.php</code> в корне проекта. Образец — <code>config.example.php</code>.</p>
<pre>captcha_enabled = true          // включить капчу
captcha_on_forms = true         // капча на формах обратной связи
captcha_on_login = false        // капча при каждом входе
captcha_length = 5              // длина кода (4–8)
captcha_login_after_failures = 2  // капча после N неудачных попыток входа
trusted_proxies = []            // IP reverse proxy для X-Forwarded-For
post_max_bytes = 10485760       // лимит POST для публичных форм (админка не ограничивается)
rate_limit_cleanup_hours = 24   // очистка таблицы rate_limits</pre>
<p>Капча генерируется локально (GD), без внешних сервисов.</p>

<h3>Ограничение частоты запросов (rate limiting)</h3>
<p>На уровне приложения действуют лимиты по IP:</p>
<ul>
    <li>Форма обратной связи — 5 отправок за 15 минут.</li>
    <li>Вход в админку — 5 попыток за 15 минут.</li>
    <li>Восстановление пароля — 10 запросов за 15 минут.</li>
    <li>Сброс пароля по токену — 5 попыток за 15 минут.</li>
    <li>Загрузка изображения капчи — 30 запросов за 15 минут.</li>
</ul>
<p>Блокировки записываются в <code>storage/logs/security.log</code>.</p>

<h3>Заголовки безопасности</h3>
<p>CMS автоматически отправляет <code>X-Frame-Options</code>, <code>X-Content-Type-Options</code>, <code>Referrer-Policy</code>, <code>Permissions-Policy</code> и базовую <code>Content-Security-Policy</code>. Для HTTPS-сайтов добавляется <code>Strict-Transport-Security</code>.</p>

<h3>nginx / Apache</h3>
<p>Для VPS используйте пример <code>nginx.conf.example</code> в корне проекта:</p>
<ul>
    <li><code>limit_req</code> — ограничение частоты запросов на уровне веб-сервера.</li>
    <li><code>client_max_body_size 10m</code> — лимит размера тела запроса.</li>
</ul>
<p>На Apache можно подключить <code>mod_evasive</code> или <code>mod_qos</code> для аналогичной защиты.</p>

<h3>Доверенные прокси</h3>
<p>Если сайт стоит за reverse proxy, укажите в <code>config.php</code> массив <code>trusted_proxies</code> с IP прокси — тогда rate limiting будет учитывать реальный IP из заголовка <code>X-Forwarded-For</code>.</p>
