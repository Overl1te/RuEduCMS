<?php
use RuEdu\Engine\Config;
use RuEdu\Engine\SiteBranding;
use RuEdu\Engine\SiteSetup;

$hideLayout = true;
$title = 'Первоначальная настройка';
$step = SiteSetup::normalizeStep($currentStep ?? SiteSetup::getCurrentStep());
$stepLabels = SiteSetup::stepLabels();
$info = SiteSetup::getInfoDefaults();
$organization = SiteSetup::getOrganizationDefaults();
$codeModules = SiteSetup::getCodeModules();
?>
<link href="<?= url('admin/assets/css/setup.css') ?>" rel="stylesheet">
<div class="setup-page">
    <div class="setup-bg"></div>
    <div class="setup-container">
        <div class="setup-card">
            <header class="setup-header">
                <h1>Первоначальная настройка сайта</h1>
                <p class="subtitle">Заполните базовую информацию и выберите нужные модули</p>
            </header>

            <nav class="setup-stepper" aria-label="Шаги настройки">
                <?php foreach ($stepLabels as $idx => $item):
                    $state = $item['num'] === $step ? 'active' : ($item['num'] < $step ? 'done' : '');
                ?>
                    <div class="setup-step <?= $state ?>">
                        <div class="setup-step-circle">
                            <?php if ($item['num'] < $step): ?>
                                <i class="bi bi-check-lg"></i>
                            <?php else: ?>
                                <?= $idx + 1 ?>
                            <?php endif; ?>
                        </div>
                        <span class="setup-step-label"><?= htmlspecialchars($item['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </nav>

            <?php if ($flash_success ?? false): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="setup-error" role="alert">
                    <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
                </div>
            <?php endif; ?>

            <?php if ($step === SiteSetup::STEP_INFO): ?>
                <h2 class="setup-step-title">Основная информация</h2>
                <p class="setup-step-desc">Эти данные отобразятся на сайте и в поисковых системах.</p>
                <form method="POST" action="<?= url('admin/setup') ?>" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="step" value="<?= SiteSetup::STEP_INFO ?>">
                    <div class="setup-form-group">
                        <label for="site_name">Название сайта / учреждения</label>
                        <input type="text" id="site_name" name="site_name" required
                               value="<?= htmlspecialchars($info['site_name']) ?>">
                    </div>
                    <div class="setup-form-group">
                        <label for="site_description">Краткое описание</label>
                        <textarea id="site_description" name="site_description" rows="2"
                                  placeholder="Сайт муниципального бюджетного общеобразовательного учреждения"><?= htmlspecialchars($info['site_description']) ?></textarea>
                    </div>
                    <div class="setup-form-group">
                        <label for="site_url">URL сайта</label>
                        <input type="url" id="site_url" name="site_url" required
                               value="<?= htmlspecialchars($info['site_url']) ?>">
                    </div>
                    <div class="setup-form-group">
                        <label for="admin_email">Email администратора</label>
                        <input type="email" id="admin_email" name="admin_email"
                               value="<?= htmlspecialchars($info['admin_email']) ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="setup-form-group mb-0">
                                <label for="contact_phone">Телефон</label>
                                <input type="text" id="contact_phone" name="contact_phone"
                                       value="<?= htmlspecialchars($info['contact_phone']) ?>"
                                       placeholder="+7 (___) ___-__-__">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="setup-form-group mb-0">
                                <label for="contact_address">Адрес</label>
                                <input type="text" id="contact_address" name="contact_address"
                                       value="<?= htmlspecialchars($info['contact_address']) ?>"
                                       placeholder="г. Город, ул. Улица, д. 1">
                            </div>
                        </div>
                    </div>
                    <div class="setup-form-group mt-3">
                        <label for="site_logo">Логотип (необязательно)</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="<?= htmlspecialchars(SiteBranding::logoUrl()) ?>" alt="" class="setup-logo-preview">
                            <span class="small text-muted">PNG, JPG, GIF, WebP или ICO</span>
                        </div>
                        <input type="file" id="site_logo" name="site_logo" class="form-control"
                               accept="image/png,image/jpeg,image/gif,image/webp,image/x-icon,.ico">
                    </div>
                    <div class="setup-actions">
                        <span></span>
                        <button type="submit" class="setup-btn setup-btn-primary">
                            Далее <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step === SiteSetup::STEP_MODULES): ?>
                <h2 class="setup-step-title">Модули сайта</h2>
                <p class="setup-step-desc">Выберите разделы, которые нужны вашему учреждению. Настройку можно изменить позже.</p>
                <form method="POST" action="<?= url('admin/setup') ?>">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="step" value="<?= SiteSetup::STEP_MODULES ?>">
                    <div class="setup-module-list">
                        <?php foreach ($codeModules as $module): ?>
                            <label class="setup-module-item">
                                <input type="checkbox" name="modules[]" value="<?= htmlspecialchars($module['name']) ?>"
                                    <?= $module['enabled'] ? 'checked' : '' ?>>
                                <div>
                                    <strong>
                                        <?= htmlspecialchars($module['title']) ?>
                                        <?php if ($module['recommended']): ?>
                                            <span class="setup-badge">Рекомендуется</span>
                                        <?php endif; ?>
                                    </strong>
                                    <p><?= htmlspecialchars($module['description']) ?></p>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        Типовые страницы (История школы, ГИА и др.) включены по умолчанию.
                        Управление ими — в разделе <strong>Модули</strong>.
                    </p>
                    <div class="setup-actions">
                        <a href="<?= url('admin/setup?back=1') ?>" class="setup-btn setup-btn-secondary">
                            <i class="bi bi-arrow-left"></i> Назад
                        </a>
                        <button type="submit" class="setup-btn setup-btn-primary">
                            Далее <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step === SiteSetup::STEP_ORGANIZATION): ?>
                <h2 class="setup-step-title">Сведения об организации</h2>
                <p class="setup-step-desc">Обязательные данные для раздела «Сведения об образовательной организации».</p>
                <form method="POST" action="<?= url('admin/setup') ?>">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="step" value="<?= SiteSetup::STEP_ORGANIZATION ?>">
                    <div class="setup-form-group">
                        <label for="full_name">Полное наименование</label>
                        <input type="text" id="full_name" name="full_name" required
                               value="<?= htmlspecialchars($organization['full_name']) ?>">
                    </div>
                    <div class="setup-form-group">
                        <label for="short_name">Сокращённое наименование</label>
                        <input type="text" id="short_name" name="short_name"
                               value="<?= htmlspecialchars($organization['short_name']) ?>">
                    </div>
                    <div class="setup-form-group">
                        <label for="address">Адрес</label>
                        <input type="text" id="address" name="address" required
                               value="<?= htmlspecialchars($organization['address']) ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="setup-form-group mb-0">
                                <label for="phone">Телефон</label>
                                <input type="text" id="phone" name="phone" required
                                       value="<?= htmlspecialchars($organization['phone']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="setup-form-group mb-0">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email"
                                       value="<?= htmlspecialchars($organization['email']) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="setup-form-group mt-3">
                        <label for="work_schedule">Режим и график работы</label>
                        <input type="text" id="work_schedule" name="work_schedule"
                               value="<?= htmlspecialchars($organization['work_schedule']) ?>">
                    </div>
                    <div class="setup-actions">
                        <a href="<?= url('admin/setup?back=1') ?>" class="setup-btn setup-btn-secondary">
                            <i class="bi bi-arrow-left"></i> Назад
                        </a>
                        <button type="submit" class="setup-btn setup-btn-primary">
                            Далее <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step === SiteSetup::STEP_FINISH): ?>
                <div class="text-center">
                    <div class="setup-success-icon"><i class="bi bi-check-lg"></i></div>
                    <h2 class="setup-step-title">Всё готово!</h2>
                    <p class="setup-step-desc">Сайт настроен. Остальные разделы можно заполнить из панели управления.</p>
                </div>
                <dl class="setup-summary">
                    <dt>Название</dt>
                    <dd><?= htmlspecialchars($info['site_name'] ?: Config::get('site_name', '')) ?></dd>
                    <?php if ($info['contact_phone'] !== ''): ?>
                        <dt>Телефон</dt>
                        <dd><?= htmlspecialchars($info['contact_phone']) ?></dd>
                    <?php endif; ?>
                    <?php if ($info['contact_address'] !== ''): ?>
                        <dt>Адрес</dt>
                        <dd><?= htmlspecialchars($info['contact_address']) ?></dd>
                    <?php endif; ?>
                    <dt>Активных модулей</dt>
                    <dd><?= count(array_filter($codeModules, static fn(array $m): bool => $m['enabled'])) ?></dd>
                </dl>
                <form method="POST" action="<?= url('admin/setup') ?>">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="step" value="<?= SiteSetup::STEP_FINISH ?>">
                    <div class="setup-actions">
                        <a href="<?= url('admin/setup?back=1') ?>" class="setup-btn setup-btn-secondary">
                            <i class="bi bi-arrow-left"></i> Назад
                        </a>
                        <button type="submit" class="setup-btn setup-btn-primary">
                            Перейти в панель управления <i class="bi bi-speedometer2"></i>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
