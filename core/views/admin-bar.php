<?php
/** @var array $user */
/** @var list<array{title: string, url: string, highlight?: bool}> $items */
?>
<style>
    .ruedu-admin-bar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 99999;
        height: 32px;
        background: #1d2327;
        color: #f0f0f1;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        font-size: 13px;
        line-height: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
    }
    .ruedu-admin-bar__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 100%;
        max-width: 100%;
        padding: 0 12px;
        gap: 16px;
    }
    .ruedu-admin-bar__left,
    .ruedu-admin-bar__right {
        display: flex;
        align-items: center;
        gap: 4px;
        min-width: 0;
    }
    .ruedu-admin-bar__brand {
        color: #f0f0f1;
        text-decoration: none;
        font-weight: 600;
        white-space: nowrap;
        padding: 0 8px;
    }
    .ruedu-admin-bar__brand:hover {
        color: #72aee6;
    }
    .ruedu-admin-bar__sep {
        color: #50575e;
        padding: 0 2px;
        user-select: none;
    }
    .ruedu-admin-bar__link {
        color: #f0f0f1;
        text-decoration: none;
        padding: 0 8px;
        white-space: nowrap;
        border-radius: 2px;
        transition: color .15s, background .15s;
    }
    .ruedu-admin-bar__link:hover {
        color: #72aee6;
        background: rgba(255, 255, 255, .06);
    }
    .ruedu-admin-bar__link--highlight {
        color: #72aee6;
        font-weight: 600;
    }
    .ruedu-admin-bar__user {
        color: #a7aaad;
        white-space: nowrap;
        padding: 0 8px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    body.has-admin-bar {
        padding-top: 32px;
    }
    @media (max-width: 768px) {
        .ruedu-admin-bar__inner {
            overflow-x: auto;
            scrollbar-width: none;
        }
        .ruedu-admin-bar__inner::-webkit-scrollbar {
            display: none;
        }
        .ruedu-admin-bar__user {
            display: none;
        }
    }
</style>
<div id="ruedu-admin-bar" class="ruedu-admin-bar" role="navigation" aria-label="Панель администратора">
    <div class="ruedu-admin-bar__inner">
        <div class="ruedu-admin-bar__left">
            <a class="ruedu-admin-bar__brand" href="<?= htmlspecialchars(\RuEdu\Engine\Router::path('admin')) ?>">
                <?= htmlspecialchars(\RuEdu\Engine\Lang::appName()) ?>
            </a>
            <?php foreach ($items as $item): ?>
                <span class="ruedu-admin-bar__sep">|</span>
                <a class="ruedu-admin-bar__link<?= !empty($item['highlight']) ? ' ruedu-admin-bar__link--highlight' : '' ?>"
                   href="<?= htmlspecialchars($item['url']) ?>">
                    <?= htmlspecialchars($item['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="ruedu-admin-bar__right">
            <span class="ruedu-admin-bar__user">
                <?= htmlspecialchars($user['name']) ?>
                (<?= htmlspecialchars(\RuEdu\Engine\AdminBar::roleLabel($user['role'])) ?>)
            </span>
            <a class="ruedu-admin-bar__link" href="<?= htmlspecialchars(\RuEdu\Engine\Router::path('admin/logout')) ?>">
                Выход
            </a>
        </div>
    </div>
</div>
