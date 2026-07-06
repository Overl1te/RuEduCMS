<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use RuEdu\Engine\Config;
use RuEdu\Engine\Scss;
use RuEdu\Engine\ThemeEditor;

if (Config::isInstalled()) {
    Config::load();
}

$theme = isset($_GET['theme']) ? (string) $_GET['theme'] : '';

if (!ThemeEditor::isValidSlug($theme)) {
    http_response_code(400);
    exit;
}

Scss::serve($theme);
