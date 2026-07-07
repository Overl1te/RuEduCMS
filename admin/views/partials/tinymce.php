<?php
$tinymceBase = url('admin/assets/vendor/tinymce');
$editorSelector = $editorSelector ?? '#editor';
$editorHeight = (int) ($editorHeight ?? 400);
?>
<script src="<?= htmlspecialchars($tinymceBase) ?>/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: <?= json_encode($editorSelector, JSON_UNESCAPED_UNICODE) ?>,
    height: <?= $editorHeight ?>,
    language: 'ru',
    language_url: <?= json_encode($tinymceBase . '/langs/ru.js', JSON_UNESCAPED_UNICODE) ?>,
    base_url: <?= json_encode($tinymceBase, JSON_UNESCAPED_UNICODE) ?>,
    suffix: '.min',
    plugins: 'link image lists table code',
    toolbar: 'undo redo | bold italic | link image | bullist numlist | table | code',
    menubar: false,
    branding: false,
    promotion: false,
    license_key: 'gpl'
});
</script>
