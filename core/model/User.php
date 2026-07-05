<?php

declare(strict_types=1);

namespace RuEdu\Model;

use RuEdu\Engine\Database;
use RuEdu\Engine\Migrate;

class User
{
    public static function getAll(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT id, name, login, email, role, status, last_login, created_at FROM " . $db->table('users') . " ORDER BY login"
        );
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT id, name, login, email, role, status, created_at FROM " . $db->table('users') . " WHERE id = ?",
            [$id]
        );
    }

    public static function loginExists(string $login, ?int $excludeId = null): bool
    {
        $db = Database::getInstance();
        $sql = "SELECT id FROM " . $db->table('users') . " WHERE login = ?";
        $params = [$login];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (bool) $db->fetch($sql, $params);
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $now = date('Y-m-d H:i:s');

        $login = $data['login'];

        return $db->insert('users', [
            'name' => $data['name'] ?? $login,
            'login' => $login,
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'author',
            'status' => $data['status'] ?? 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public static function update(int $id, array $data): int
    {
        $db = Database::getInstance();
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $db->update('users', $data, 'id = ?', [$id]);
    }

    public static function delete(int $id): int
    {
        $db = Database::getInstance();
        return $db->delete('users', 'id = ?', [$id]);
    }

    public static function makeLogin(string $login, ?int $excludeId = null): string
    {
        $login = Migrate::normalizeLogin($login);
        if ($login === '') {
            $login = 'user';
        }

        $base = $login;
        $i = 1;
        while (self::loginExists($login, $excludeId)) {
            $login = $base . $i;
            $i++;
        }

        return $login;
    }
}
