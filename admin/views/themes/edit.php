<?php
use RuEdu\Engine\Config;
$title = 'Редактор темы: ' . ($theme['name'] ?? $slug);
$editorMode = $currentFile ? \RuEdu\Engine\ThemeEditor::modeForFile($currentFile) : 'text/plain';
$isActive = Config::get('theme') === $slug;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= url('admin/themes') ?>" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Все темы</a>
        <h2 class="mb-0 mt-1"><?= htmlspecialchars($theme['name'] ?? $slug) ?></h2>
        <?php if ($isActive): ?><span class="badge bg-primary">Активная тема</span><?php endif; ?>
    </div>
    <a href="<?= route('') ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
        <i class="bi bi-box-arrow-up-right"></i> Открыть сайт
    </a>
</div>

<div class="row g-3 theme-editor">
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header py-2"><strong>Файлы темы</strong></div>
            <div class="list-group list-group-flush theme-file-list">
                <?php
                $currentDir = '';
                foreach ($files as $file):
                    $dir = dirname($file);
                    if ($dir !== '.' && $dir !== $currentDir):
                        $currentDir = $dir;
                ?>
                    <div class="list-group-item py-1 px-3 bg-light small text-muted fw-semibold"><?= htmlspecialchars($dir) ?>/</div>
                <?php endif; ?>
                    <a href="<?= url('admin/themes/edit/' . rawurlencode($slug) . '?file=' . rawurlencode($file)) ?>"
                       class="list-group-item list-group-item-action py-2 small <?= $file === $currentFile ? 'active' : '' ?>">
                        <?= htmlspecialchars(basename($file)) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <?php if ($currentFile && $fileData !== null): ?>
            <form method="POST" action="<?= url('admin/themes/save') ?>" id="theme-save-form">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
                <input type="hidden" name="file" value="<?= htmlspecialchars($currentFile) ?>">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <code class="mb-0"><?= htmlspecialchars($currentFile) ?></code>
                        <span class="text-muted small">
                            <?= number_format($fileData['size'] / 1024, 1) ?> КБ ·
                            <?= date('d.m.Y H:i', $fileData['modified']) ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <textarea name="content" id="theme-editor" class="form-control border-0"><?= htmlspecialchars($fileData['content']) ?></textarea>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Ctrl+S — сохранить</span>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Сохранить</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">В этой теме нет файлов, доступных для редактирования.</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($currentFile && $fileData !== null): ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/theme/eclipse.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/clike/clike.min.js"></script>
<script>
(function () {
    var textarea = document.getElementById('theme-editor');
    var form = document.getElementById('theme-save-form');
    if (!textarea || !form) return;

    var editor = CodeMirror.fromTextArea(textarea, {
        lineNumbers: true,
        lineWrapping: true,
        mode: <?= json_encode($editorMode, JSON_UNESCAPED_UNICODE) ?>,
        theme: 'eclipse',
        indentUnit: 4,
        tabSize: 4,
        indentWithTabs: false
    });

    editor.setSize(null, '65vh');

    form.addEventListener('submit', function () {
        textarea.value = editor.getValue();
    });

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            textarea.value = editor.getValue();
            form.submit();
        }
    });
})();
</script>
<?php endif; ?>
