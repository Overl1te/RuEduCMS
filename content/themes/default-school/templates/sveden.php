<?php ob_start(); ?>
<div class="container page-content">
    <h1>Сведения об образовательной организации</h1>
    <nav class="sveden-nav">
        <ul>
            <?php foreach ($sectionList as $key => $label): ?>
                <li><a href="#section-<?= $key ?>"><?= htmlspecialchars($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php foreach ($sectionList as $key => $label): ?>
        <section id="section-<?= $key ?>" class="mb-5">
            <h2><?= htmlspecialchars($label) ?></h2>
            <?php if (!empty($sections[$key])): ?>
                <table class="sveden-table">
                    <?php foreach ($sections[$key] as $field => $value): ?>
                        <?php if ($value): ?>
                            <tr>
                                <td><?= htmlspecialchars($sectionFields[$key][$field] ?? $field) ?></td>
                                <td><?= nl2br(htmlspecialchars($value)) ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="text-muted">Информация не размещена.</p>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
