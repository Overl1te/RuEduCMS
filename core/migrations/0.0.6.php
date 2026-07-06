<?php

declare(strict_types=1);

use RuEdu\Engine\SiteStructure;

/**
 * Типовая структура сайта: страницы, главное и боковое меню.
 */
return static function (\PDO $pdo, string $prefix): void {
    SiteStructure::seedForMigration($pdo, $prefix);
};
