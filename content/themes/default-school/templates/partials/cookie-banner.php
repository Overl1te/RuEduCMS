<div class="cookie-banner" id="cookieBanner" style="display:none;">
    <div class="container">
        <p><?= htmlspecialchars(\RuEdu\Model\Setting::get('cookie_text', 'Данный сайт использует cookie-файлы для улучшения работы.')) ?></p>
        <div class="cookie-actions">
            <button onclick="acceptCookies()" class="btn btn-primary btn-sm">Принять</button>
            <button onclick="declineCookies()" class="btn btn-outline btn-sm">Отклонить</button>
        </div>
    </div>
</div>
