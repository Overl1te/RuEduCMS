<?php

declare(strict_types=1);

namespace RuEdu\Engine;

use RuEdu\Model\Menu;

class Template
{
    private string $themePath;
    private array $data = [];

    public function __construct(?string $theme = null)
    {
        $theme = $theme ?? Config::get('theme', 'default-school');
        $this->themePath = THEMES_PATH . '/' . $theme;
    }

    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function render(string $template, array $data = []): string
    {
        $data = array_merge($this->defaultData(), $this->data, $data);
        $tpl = $this;
        extract($data, EXTR_SKIP);

        $file = $this->themePath . '/templates/' . $template . '.php';
        if (!file_exists($file)) {
            return '<!-- Шаблон не найден: ' . htmlspecialchars($template) . ' -->';
        }

        ob_start();
        include $file;
        return (string) ob_get_clean();
    }

    public function partial(string $partial, array $data = []): string
    {
        return $this->render('partials/' . $partial, $data);
    }

    public function route(string $path = ''): string
    {
        return Router::route($path);
    }

    public function asset(string $path): string
    {
        $theme = basename($this->themePath);
        return Router::asset("themes/{$theme}/{$path}");
    }

    public function cssUrl(): string
    {
        $theme = basename($this->themePath);
        if (Scss::themeUsesScss($theme)) {
            return Scss::publicStyleUrl($theme);
        }

        return $this->asset('css/main.css');
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultData(): array
    {
        $data = [
            'site_flash_success' => Session::flash('site_success'),
            'site_flash_error' => Session::flash('site_error'),
        ];

        if (!array_key_exists('side_menu', $this->data)) {
            try {
                $data['side_menu'] = Menu::getByLocation('side');
            } catch (\Throwable) {
                $data['side_menu'] = [];
            }
        }

        return $data;
    }

    public static function getThemes(): array
    {
        $themes = [];
        if (!is_dir(THEMES_PATH)) {
            return $themes;
        }

        foreach (scandir(THEMES_PATH) as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $jsonFile = THEMES_PATH . '/' . $dir . '/theme.json';
            if (file_exists($jsonFile)) {
                $meta = json_decode(file_get_contents($jsonFile), true);
                $meta['slug'] = $dir;
                $themes[] = $meta;
            }
        }

        return $themes;
    }
}
