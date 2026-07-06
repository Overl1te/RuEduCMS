<?php ob_start();
$page_title = 'Сведения об образовательной организации';
$page_breadcrumb = 'Сведения об ОО';
include __DIR__ . '/partials/page-header.php';
?>
<div class="ed-container ed-page-content">
    <nav class="ed-sveden-nav">
        <ul>
            <?php foreach ($sectionList as $key => $label): ?>
                <li><a href="#section-<?= $key ?>"><?= htmlspecialchars($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php foreach ($sectionList as $key => $label): ?>
        <section id="section-<?= $key ?>" class="ed-sveden-section">
            <h2><?= htmlspecialchars($label) ?></h2>
            <?php if ($key === 'common'): ?>
                <div class="ed-contacts">
                    <div class="ed-contacts__info">
                        <?php if (!empty($sections[$key])): ?>
                            <table class="ed-table ed-sveden-table">
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
                            <p class="ed-muted">Информация не размещена.</p>
                        <?php endif; ?>
                    </div>
                    <div class="ed-contacts__map">
                        <?php include __DIR__ . '/partials/yandex-map.php'; ?>
                    </div>
                </div>
            <?php elseif (!empty($sections[$key])): ?>
                    <table class="ed-table ed-sveden-table">
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
                <p class="ed-muted">Информация не размещена.</p>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>
<?php $content = ob_get_clean();
include __DIR__ . '/layout.php';
