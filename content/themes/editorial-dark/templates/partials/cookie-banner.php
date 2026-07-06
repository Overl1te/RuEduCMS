<div class="ed-cookie-banner" id="cookieBanner" style="display:none;">
    <div class="ed-container ed-cookie-banner__inner">
        <p><?= htmlspecialchars(\RuEdu\Model\Setting::get('cookie_text', 'Данный сайт использует cookie-файлы для улучшения работы.')) ?></p>
        <div class="ed-cookie-banner__actions">
            <button onclick="acceptCookies()" class="ed-btn ed-btn--primary ed-btn--sm">Принять</button>
            <button onclick="declineCookies()" class="ed-btn ed-btn--outline ed-btn--sm">Отклонить</button>
        </div>
    </div>
</div>
