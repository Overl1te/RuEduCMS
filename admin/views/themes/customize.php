<?php
use RuEdu\Engine\Config;
use RuEdu\Engine\Router;
use RuEdu\Engine\ThemeCustomizer;

$title = 'Настройка оформления';
$schema = ThemeCustomizer::getSchema($slug);
$values = ThemeCustomizer::getValues($slug);
$fonts = ThemeCustomizer::getFontOptions();
$previewUrl = Router::route('');
$isActive = Config::get('theme') === $slug;
$fontFieldStacks = [];
foreach ($schema['sections'] ?? [] as $section) {
    if (!is_array($section)) {
        continue;
    }
    foreach ($section['fields'] ?? [] as $field) {
        if (!is_array($field) || empty($field['key']) || ($field['type'] ?? '') !== 'font') {
            continue;
        }
        $fontFieldStacks[(string) $field['key']] = (string) ($field['stack'] ?? 'sans');
    }
}
?>
<link href="<?= url('admin/assets/css/theme-customizer.css') ?>" rel="stylesheet">

<div class="theme-customizer-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= url('admin/themes') ?>" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Темы</a>
        <h2 class="mb-0 mt-1"><?= htmlspecialchars($theme['name'] ?? $slug) ?></h2>
        <p class="text-muted small mb-0">Визуальный редактор оформления</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (!$isActive): ?>
            <span class="badge bg-secondary align-self-center">Не активна</span>
        <?php endif; ?>
        <form method="POST" action="<?= url('admin/themes/customize/reset') ?>" class="d-inline" onsubmit="return confirm('Сбросить все настройки оформления?');">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
            <button type="submit" class="btn btn-outline-secondary">Сбросить</button>
        </form>
        <button type="submit" form="customizerForm" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Сохранить
        </button>
    </div>
</div>

<div class="theme-customizer">
    <aside class="theme-customizer__panel card">
        <div class="card-body">
            <form method="POST" action="<?= url('admin/themes/customize/save') ?>" id="customizerForm">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">

                <?php foreach ($schema['sections'] ?? [] as $section): ?>
                    <?php if (!is_array($section)) continue; ?>
                    <div class="customizer-section mb-4">
                        <h6 class="customizer-section__title"><?= htmlspecialchars($section['title'] ?? '') ?></h6>
                        <?php foreach ($section['fields'] ?? [] as $field): ?>
                            <?php
                            if (!is_array($field) || empty($field['key'])) continue;
                            $key = (string) $field['key'];
                            $type = (string) ($field['type'] ?? 'text');
                            $label = (string) ($field['label'] ?? $key);
                            $value = $values[$key] ?? (string) ($field['default'] ?? '');
                            ?>
                            <div class="mb-3 customizer-field" data-key="<?= htmlspecialchars($key) ?>" data-type="<?= htmlspecialchars($type) ?>">
                                <label class="form-label small mb-1"><?= htmlspecialchars($label) ?></label>
                                <?php if ($type === 'color'): ?>
                                    <?php
                                    $hex = $value;
                                    if (preg_match('/^rgba?\(/', $value)) {
                                        $hex = '#1e40af';
                                    }
                                    ?>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="color" class="form-control form-control-color customizer-color"
                                               value="<?= htmlspecialchars($hex) ?>"
                                               data-css-key="<?= htmlspecialchars($key) ?>">
                                        <input type="text" name="<?= htmlspecialchars($key) ?>"
                                               class="form-control form-control-sm customizer-input"
                                               value="<?= htmlspecialchars($value) ?>"
                                               data-css-key="<?= htmlspecialchars($key) ?>">
                                    </div>
                                <?php elseif ($type === 'font'): ?>
                                    <?php $fontName = trim(explode(',', $value)[0], " '\""); ?>
                                    <select name="<?= htmlspecialchars($key) ?>" class="form-select form-select-sm customizer-input" data-css-key="<?= htmlspecialchars($key) ?>">
                                        <?php foreach ($fonts as $font): ?>
                                            <option value="<?= htmlspecialchars($font) ?>" <?= $fontName === $font ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($font) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($type === 'range'): ?>
                                    <?php
                                    $unit = (string) ($field['unit'] ?? 'px');
                                    $min = (int) ($field['min'] ?? 0);
                                    $max = (int) ($field['max'] ?? 100);
                                    $num = (float) rtrim($value, $unit);
                                    ?>
                                    <input type="range" class="form-range customizer-range"
                                           min="<?= $min ?>" max="<?= $max ?>" step="1"
                                           value="<?= (int) $num ?>"
                                           data-unit="<?= htmlspecialchars($unit) ?>"
                                           data-css-key="<?= htmlspecialchars($key) ?>">
                                    <input type="hidden" name="<?= htmlspecialchars($key) ?>"
                                           class="customizer-input"
                                           value="<?= htmlspecialchars($value) ?>"
                                           data-css-key="<?= htmlspecialchars($key) ?>">
                                    <div class="text-muted small text-end customizer-range-value"><?= htmlspecialchars($value) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </form>
        </div>
    </aside>

    <div class="theme-customizer__preview card">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="small text-muted">Предпросмотр</span>
            <a href="<?= htmlspecialchars($previewUrl) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-box-arrow-up-right"></i> Открыть сайт
            </a>
        </div>
        <div class="card-body p-0">
            <iframe id="customizerPreview" src="<?= htmlspecialchars($previewUrl) ?>" title="Предпросмотр сайта"></iframe>
        </div>
    </div>
</div>

<script src="<?= url('admin/assets/js/theme-customizer.js') ?>"></script>
<script>
window.themeCustomizerConfig = {
    fontStacks: {
        sans: "-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
        serif: "Georgia, serif"
    },
    fontFields: <?= json_encode($fontFieldStacks, JSON_UNESCAPED_UNICODE) ?>
};
</script>
