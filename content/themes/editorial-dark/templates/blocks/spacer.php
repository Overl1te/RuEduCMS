<?php
$sizes = ['small' => '2rem', 'medium' => '4rem', 'large' => '6rem'];
$size = (string) ($props['size'] ?? 'medium');
$height = $sizes[$size] ?? $sizes['medium'];
?>
<div class="ed-spacer" style="height: <?= htmlspecialchars($height) ?>" aria-hidden="true"></div>
