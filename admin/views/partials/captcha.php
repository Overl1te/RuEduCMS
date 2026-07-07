<?php

use RuEdu\Engine\Captcha;

if (!Captcha::shouldRequire('login')) {
    return;
}
?>
<div class="mb-3" data-captcha-group>
    <label class="form-label">Капча</label>
    <div class="d-flex align-items-center gap-2 mb-2">
        <img src="<?= htmlspecialchars(Captcha::imageUrl()) ?>" alt="Капча" class="border rounded" data-captcha-image width="180" height="60">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-captcha-refresh>Обновить</button>
    </div>
    <input type="text" name="captcha_answer" class="form-control" required autocomplete="off">
</div>
<script>
document.querySelectorAll('[data-captcha-refresh]').forEach(function(button) {
    button.addEventListener('click', function() {
        var group = button.closest('[data-captcha-group]');
        var image = group ? group.querySelector('[data-captcha-image]') : null;
        var answer = group ? group.querySelector('input[name="captcha_answer"]') : null;
        if (answer) answer.value = '';
        fetch('<?= htmlspecialchars(route('captcha/refresh')) ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(res) { return res.json(); })
            .then(function(data) { if (image && data.imageUrl) image.src = data.imageUrl; });
    });
});
</script>
