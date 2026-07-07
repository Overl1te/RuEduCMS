<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Media
{
    private const MAX_SIZE_BYTES = 10485760;

    /** @var array<string, list<string>> */
    private const ALLOWED_EXTENSIONS = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
    ];

    private const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'phar', 'htaccess', 'svg', 'cgi', 'pl', 'exe',
    ];

    public static function upload(array $file, ?int $userId = null): ?array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ((int) ($file['size'] ?? 0) > self::MAX_SIZE_BYTES) {
            return null;
        }

        $ext = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if ($ext === '' || in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
            return null;
        }

        $mime = self::detectMime((string) $file['tmp_name']);
        if ($mime === null || !isset(self::ALLOWED_EXTENSIONS[$mime])) {
            return null;
        }

        if (!in_array($ext, self::ALLOWED_EXTENSIONS[$mime], true)) {
            return null;
        }

        $subdir = date('Y/m');
        $uploadDir = UPLOADS_PATH . '/' . $subdir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('media_', true) . '.' . $ext;
        $relativePath = $subdir . '/' . $filename;
        $fullPath = UPLOADS_PATH . '/' . $relativePath;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return null;
        }

        $db = Database::getInstance();
        $id = $db->insert('media', [
            'filename' => $file['name'],
            'path' => $relativePath,
            'mime_type' => $mime,
            'size' => $file['size'],
            'alt' => '',
            'uploaded_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'id' => $id,
            'filename' => $file['name'],
            'path' => $relativePath,
            'url' => Router::asset('uploads/' . $relativePath),
            'mime_type' => $mime,
            'size' => $file['size'],
        ];
    }

    public static function getUrl(string $path): string
    {
        return Router::asset('uploads/' . ltrim($path, '/'));
    }

    public static function delete(int $id): bool
    {
        $db = Database::getInstance();
        $media = $db->fetch("SELECT * FROM " . $db->table('media') . " WHERE id = ?", [$id]);
        if (!$media) {
            return false;
        }

        $fullPath = UPLOADS_PATH . '/' . $media['path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $db->delete('media', 'id = ?', [$id]);
        return true;
    }

    public static function getAll(int $limit = 50, int $offset = 0): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM " . $db->table('media') . " ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    private static function detectMime(string $tmpPath): ?string
    {
        if (!is_file($tmpPath)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        return is_string($mime) ? $mime : null;
    }
}
