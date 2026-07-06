<?php
use RuEdu\Engine\Config;
$title = 'Настройки';
$s = $settings;
?>
<h2 class="mb-4">Настройки сайта</h2>
<form method="POST" action="<?= url('admin/settings/save') ?>" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3"><div class="card-header">Основные</div><div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Название сайта</label>
                    <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($s['site_name'] ?? Config::get('site_name', '')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea name="site_description" class="form-control" rows="2"><?= htmlspecialchars($s['site_description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">URL сайта</label>
                    <input type="url" name="site_url" class="form-control" value="<?= htmlspecialchars($s['site_url'] ?? Config::get('site_url', '')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email администратора</label>
                    <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($s['admin_email'] ?? Config::get('admin_email', '')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Логотип и favicon</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img src="<?= htmlspecialchars(\RuEdu\Engine\SiteBranding::logoUrl()) ?>" alt="" width="48" height="48" class="border rounded" style="object-fit:contain">
                        <div class="small text-muted">
                            <?= \RuEdu\Engine\SiteBranding::isCustom() ? 'Загружен свой логотип' : 'Используется логотип ядра по умолчанию' ?>
                        </div>
                    </div>
                    <input type="file" name="site_logo" class="form-control" accept="image/png,image/jpeg,image/gif,image/webp,image/x-icon,.ico">
                    <div class="form-text">PNG, JPG, GIF, WebP или ICO. Отображается в шапке сайта и как favicon.</div>
                    <?php if (\RuEdu\Engine\SiteBranding::isCustom()): ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="site_logo_reset" value="1" id="site_logo_reset">
                            <label class="form-check-label" for="site_logo_reset">Вернуть логотип по умолчанию</label>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Тема оформления</label>
                    <select name="theme" class="form-select">
                        <?php foreach ($themes as $t): ?>
                            <option value="<?= $t['slug'] ?>" <?= Config::get('theme') === $t['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name'] ?? $t['slug']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text"><a href="<?= url('admin/themes') ?>">Редактировать файлы темы</a></div>
                </div>
            </div></div>
            <div class="card mb-3"><div class="card-header">Контакты</div><div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Телефон</label>
                    <input type="tel" name="contact_phone" class="form-control" value="<?= htmlspecialchars($s['contact_phone'] ?? '') ?>" placeholder="+7 (___) ___-__-__">
                </div>
                <div class="mb-3">
                    <label class="form-label">Адрес</label>
                    <input type="text" name="contact_address" class="form-control" value="<?= htmlspecialchars($s['contact_address'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Код Яндекс.Карты (iframe)</label>
                    <textarea name="yandex_map" class="form-control" rows="3"><?= htmlspecialchars($s['yandex_map'] ?? '') ?></textarea>
                </div>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3"><div class="card-header">Законодательство</div><div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Текст согласия на обработку ПД (ФЗ-152)</label>
                    <textarea name="fz152_text" class="form-control" rows="3"><?= htmlspecialchars($s['fz152_text'] ?? 'Я согласен на обработку персональных данных в соответствии с Федеральным законом № 152-ФЗ.') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Текст cookie-баннера</label>
                    <textarea name="cookie_text" class="form-control" rows="2"><?= htmlspecialchars($s['cookie_text'] ?? 'Данный сайт использует cookie-файлы для улучшения работы.') ?></textarea>
                </div>
            </div></div>
            <div class="card mb-3"><div class="card-header">Поисковые системы</div><div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="seo_indexing" value="1"
                        <?= (Config::get('seo_indexing', true)) ? 'checked' : '' ?>>
                    <label class="form-check-label">Разрешить индексацию сайта</label>
                </div>
                <p class="form-text mb-0">
                    При публикации контента CMS автоматически уведомляет Яндекс, Bing и другие системы (IndexNow).
                    Карта сайта: <a href="<?= route('sitemap.xml') ?>" target="_blank">sitemap.xml</a>,
                    RSS: <a href="<?= route('news/rss') ?>" target="_blank">новости</a>.
                </p>
            </div></div>
            <div class="card mb-3"><div class="card-header">Производительность</div><div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cache_enabled" value="1" <?= ($s['cache_enabled'] ?? '1') ? 'checked' : '' ?>>
                    <label class="form-check-label">Включить кеширование страниц</label>
                </div>
            </div></div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить настройки</button>
</form>
