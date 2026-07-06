<?php ob_start();
echo \RuEdu\Engine\FieldRenderer::renderEntity('home', [
    'site_name' => $site_name ?? null,
    'articles' => $articles ?? [],
]);
$content = ob_get_clean();
include __DIR__ . '/layout.php';
