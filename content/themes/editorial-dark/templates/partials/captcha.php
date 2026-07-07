<?php

use RuEdu\Engine\Captcha;

if (!Captcha::shouldRequire('forms')) {
    return;
}
?>
<div class="ed-captcha" data-captcha-group>
    <label>Введите символы с картинки</label>
    <div class="ed-captcha__row">
        <img src="<?= htmlspecialchars(Captcha::imageUrl()) ?>" alt="Капча" class="ed-captcha__image" data-captcha-image width="180" height="60">
        <button type="button" class="ed-btn ed-btn--ghost ed-btn--sm" data-captcha-refresh>Обновить</button>
    </div>
    <input type="text" name="captcha_answer" required autocomplete="off" inputmode="text">
</div>
