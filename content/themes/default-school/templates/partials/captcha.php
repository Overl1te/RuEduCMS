<?php

use RuEdu\Engine\Captcha;

if (!Captcha::shouldRequire('forms')) {
    return;
}
?>
<div class="captcha-group" data-captcha-group>
    <label class="form-label">Введите символы с картинки</label>
    <div class="captcha-group__row">
        <img src="<?= htmlspecialchars(Captcha::imageUrl()) ?>" alt="Капча" class="captcha-image" data-captcha-image width="180" height="60">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-captcha-refresh>Обновить</button>
    </div>
    <input type="text" name="captcha_answer" class="form-control mt-2" required autocomplete="off" inputmode="text">
</div>
