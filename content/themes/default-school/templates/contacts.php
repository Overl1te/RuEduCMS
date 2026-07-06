<?php ob_start(); ?>
<div class="container page-content">
    <h1>Контакты</h1>
    <div class="contacts-layout">
        <div class="contacts-info">
            <h3><?= htmlspecialchars(\RuEdu\Engine\Config::get('site_name', '')) ?></h3>
            <?php if ($address): ?><p><strong>Адрес:</strong> <?= htmlspecialchars($address) ?></p><?php endif; ?>
            <?php if ($phone): ?><p><strong>Телефон:</strong> <a href="tel:<?= htmlspecialchars($phone) ?>"><?= htmlspecialchars($phone) ?></a></p><?php endif; ?>
            <?php if ($email): ?><p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></p><?php endif; ?>
        </div>
        <div class="contacts-map">
            <?php $show_map_empty = true; include __DIR__ . '/partials/yandex-map.php'; ?>
        </div>
    </div>
    <hr class="my-4">
    <h2>Обратная связь</h2>
    <form method="POST" action="<?= route('forms/submit/contact') ?>" class="contact-form" data-ajax-form style="max-width:500px">
        <div class="form-group"><label>Имя</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Сообщение</label><textarea name="message" rows="4" required></textarea></div>
        <div class="form-check">
            <input type="checkbox" name="consent" required id="consent">
            <label for="consent"><?= htmlspecialchars(\RuEdu\Model\Setting::get('fz152_text', 'Я согласен на обработку персональных данных.')) ?></label>
        </div>
        <button type="submit" class="btn btn-primary">Отправить</button>
    </form>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
