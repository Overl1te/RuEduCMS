<?php

declare(strict_types=1);

use RuEdu\Engine\SiteStructure;

/**
 * Регистрация типовых разделов сайта как переключаемых модулей.
 */
return static function (\PDO $pdo, string $prefix): void {
    SiteStructure::seed($pdo, $prefix);
};
