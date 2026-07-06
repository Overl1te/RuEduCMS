<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

class Scss
{
    private const VENDOR_BOOTSTRAP = CORE_PATH . '/vendor/scssphp-1.13.0/scss.inc.php';

    public static function themeUsesScss(string $slug): bool
    {
        if (!ThemeEditor::isValidSlug($slug)) {
            return false;
        }

        return is_file(self::mainScssPath($slug));
    }

    public static function publicStyleUrl(string $slug): string
    {
        return Router::asset('themes/' . $slug . '/style.css');
    }

    public static function mainScssPath(string $slug): string
    {
        return THEMES_PATH . '/' . $slug . '/scss/main.scss';
    }

    public static function compiledCssPath(string $slug): string
    {
        return THEMES_PATH . '/' . $slug . '/css/main.css';
    }

    public static function compile(string $slug): string
    {
        self::loadLibrary();

        $scssPath = self::mainScssPath($slug);
        if (!is_file($scssPath)) {
            throw new \RuntimeException('Файл scss/main.scss не найден');
        }

        $compiler = new Compiler();
        $compiler->setImportPaths([dirname($scssPath)]);
        $compiler->setOutputStyle(OutputStyle::COMPRESSED);

        return $compiler->compileString(
            (string) file_get_contents($scssPath),
            $scssPath
        )->getCss();
    }

    /**
     * @return true|string true on success, error message on failure
     */
    public static function writeCompiledCss(string $slug): true|string
    {
        if (!self::themeUsesScss($slug)) {
            return 'Тема не использует SCSS';
        }

        try {
            $css = self::compile($slug);
        } catch (\Throwable $e) {
            return 'Ошибка компиляции SCSS: ' . $e->getMessage();
        }

        $cssPath = self::compiledCssPath($slug);
        $dir = dirname($cssPath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return 'Не удалось создать папку css';
        }

        if (file_put_contents($cssPath, $css) === false) {
            return 'Не удалось записать css/main.css';
        }

        return true;
    }

    public static function serve(string $slug): void
    {
        if (!self::themeUsesScss($slug)) {
            ErrorPage::send(404);
            return;
        }

        try {
            $css = self::compile($slug);
        } catch (\Throwable $e) {
            if (Config::get('debug')) {
                header('Content-Type: text/plain; charset=utf-8');
                http_response_code(500);
                echo 'SCSS compile error: ' . $e->getMessage();
            } else {
                ErrorPage::send(500);
            }
            return;
        }

        @file_put_contents(self::compiledCssPath($slug), $css);

        if (!headers_sent()) {
            header('Content-Type: text/css; charset=utf-8');
            header('Cache-Control: public, max-age=3600');
        }

        echo $css;
    }

    private static function loadLibrary(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }

        if (!is_file(self::VENDOR_BOOTSTRAP)) {
            throw new \RuntimeException('Библиотека scssphp не найдена');
        }

        require_once self::VENDOR_BOOTSTRAP;
        $loaded = true;
    }
}
