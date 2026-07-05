<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Media
{
    private const ALLOWED_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public static function upload(array $file, ?int $userId = null): ?array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!in_array($file['type'], self::ALLOWED_TYPES, true)) {
            return null;
        }

        $subdir = date('Y/m');
        $uploadDir = UPLOADS_PATH . '/' . $subdir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_', true) . '.' . strtolower($ext);
        $relativePath = $subdir . '/' . $filename;
        $fullPath = UPLOADS_PATH . '/' . $relativePath;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return null;
        }

        $db = Database::getInstance();
        $id = $db->insert('media', [
            'filename' => $file['name'],
            'path' => $relativePath,
            'mime_type' => $file['type'],
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
            'mime_type' => $file['type'],
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
}
