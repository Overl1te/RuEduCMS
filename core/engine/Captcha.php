<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Captcha
{
    private const SESSION_HASH = '_captcha_hash';
    private const SESSION_EXPIRES = '_captcha_expires';
    private const CHARSET = 'АБВГДЕЖИКЛМНПРСТУФХЦЧШЩЫЭЮЯ2456789';
    private const TTL_SECONDS = 600;

    public static function shouldRequire(string $context): bool
    {
        if (!self::configBool('captcha_enabled', true)) {
            return false;
        }

        return match ($context) {
            'forms' => self::configBool('captcha_on_forms', true),
            'login' => self::shouldRequireLogin(),
            default => false,
        };
    }

    public static function shouldRequireLogin(): bool
    {
        if (!self::configBool('captcha_enabled', true)) {
            return false;
        }

        if (self::configBool('captcha_on_login', false)) {
            return true;
        }

        $threshold = max(1, (int) Config::get('captcha_login_after_failures', 2));

        return (int) Session::get('_login_failures', 0) >= $threshold;
    }

    public static function generateImage(): void
    {
        if (!extension_loaded('gd')) {
            http_response_code(503);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'GD extension is required';
            exit;
        }

        $length = self::length();
        $answer = self::randomString($length);
        self::storeAnswer($answer);

        $width = 180;
        $height = 60;
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            http_response_code(500);
            exit;
        }

        $background = imagecolorallocate($image, 245, 247, 250);
        $textColor = imagecolorallocate($image, 25, 35, 55);
        $noiseColor = imagecolorallocate($image, 170, 180, 195);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        for ($i = 0; $i < 8; $i++) {
            imageline(
                $image,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $noiseColor
            );
        }

        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $noiseColor);
        }

        $x = 18;
        $chars = mb_str_split($answer);
        $fontPath = self::fontPath();
        $useTtf = is_file($fontPath);

        foreach ($chars as $char) {
            if ($useTtf) {
                imagettftext(
                    $image,
                    random_int(18, 22),
                    random_int(-18, 18),
                    $x,
                    random_int(38, 48),
                    $textColor,
                    $fontPath,
                    $char
                );
            } else {
                imagestring($image, 5, $x, random_int(18, 28), $char, $textColor);
            }
            $x += 24;
        }

        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        imagepng($image);
        imagedestroy($image);
        exit;
    }

    public static function verify(?string $answer): bool
    {
        $answer = self::normalize((string) $answer);
        if ($answer === '') {
            return false;
        }

        $expires = (int) Session::get(self::SESSION_EXPIRES, 0);
        $hash = (string) Session::get(self::SESSION_HASH, '');
        self::clearStoredAnswer();

        if ($expires < time() || $hash === '') {
            return false;
        }

        return hash_equals($hash, self::hashAnswer($answer));
    }

    public static function invalidate(): void
    {
        self::clearStoredAnswer();
    }

    public static function imageUrl(): string
    {
        return Router::url('captcha/image?t=' . time());
    }

    private static function storeAnswer(string $answer): void
    {
        Session::set(self::SESSION_HASH, self::hashAnswer($answer));
        Session::set(self::SESSION_EXPIRES, time() + self::TTL_SECONDS);
    }

    private static function clearStoredAnswer(): void
    {
        Session::remove(self::SESSION_HASH);
        Session::remove(self::SESSION_EXPIRES);
    }

    private static function hashAnswer(string $answer): string
    {
        $secret = (string) Config::get('secret_key', 'rueducms');
        if ($secret === '') {
            $secret = 'rueducms';
        }

        return hash_hmac('sha256', self::normalize($answer), $secret);
    }

    private static function normalize(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return mb_strtoupper($value);
    }

    private static function length(): int
    {
        $length = (int) Config::get('captcha_length', 5);
        return max(4, min(8, $length));
    }

    private static function configBool(string $key, bool $default): bool
    {
        $value = Config::get($key, $default);
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private static function randomString(int $length): string
    {
        $chars = mb_str_split(self::CHARSET);
        $result = '';
        $max = count($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }

    private static function fontPath(): string
    {
        $candidates = [
            'C:\\Windows\\Fonts\\arial.ttf',
            'C:\\Windows\\Fonts\\segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $candidates[0];
    }
}
