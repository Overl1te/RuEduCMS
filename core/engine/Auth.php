<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Auth
{
    private const SESSION_KEY = 'user_id';
    private const ROLES = ['admin', 'editor', 'author'];

    public static function attempt(string $login, string $password): bool
    {
        $db = Database::getInstance();
        $login = trim($login);
        $user = $db->fetch(
            "SELECT * FROM " . $db->table('users') . " WHERE status = 'active' AND (login = ? OR email = ?)",
            [$login, $login]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        Session::set(self::SESSION_KEY, (int) $user['id']);
        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [(int) $user['id']]);
        return true;
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
    }

    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function id(): ?int
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function user(): ?array
    {
        $id = self::id();
        if (!$id) {
            return null;
        }

        $db = Database::getInstance();
        return $db->fetch(
            "SELECT id, name, login, email, role, status, created_at FROM " . $db->table('users') . " WHERE id = ?",
            [$id]
        );
    }

    public static function findByLoginOrEmail(string $value): ?array
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM " . $db->table('users') . " WHERE status = 'active' AND (login = ? OR email = ?)",
            [$value, $value]
        );
    }

    public static function sendPasswordReset(string $loginOrEmail): bool
    {
        $user = self::findByLoginOrEmail($loginOrEmail);
        if (!$user) {
            return true;
        }

        $db = Database::getInstance();
        $db->delete('password_resets', 'user_id = ?', [(int) $user['id']]);

        $token = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');
        $db->insert('password_resets', [
            'user_id' => (int) $user['id'],
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_at' => $now,
        ]);

        $resetUrl = Router::url('admin/reset-password?token=' . $token);
        $siteName = Config::get('site_name', Lang::APP_NAME);
        $subject = 'Восстановление пароля — ' . $siteName;
        $body = "Здравствуйте, " . ($user['login'] ?? $user['name']) . "!\n\n"
            . "Для сброса пароля перейдите по ссылке (действует 1 час):\n{$resetUrl}\n\n"
            . "Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.\n\n"
            . "— {$siteName}";

        return Mail::send((string) $user['email'], $subject, $body);
    }

    public static function findPasswordReset(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $db = Database::getInstance();
        $reset = $db->fetch(
            "SELECT pr.*, u.email, u.name FROM " . $db->table('password_resets') . " pr
             INNER JOIN " . $db->table('users') . " u ON u.id = pr.user_id
             WHERE pr.token = ? AND pr.expires_at > NOW() AND u.status = 'active'",
            [$token]
        );

        return $reset ?: null;
    }

    public static function resetPassword(string $token, string $password): bool
    {
        $reset = self::findPasswordReset($token);
        if (!$reset || strlen($password) < 6) {
            return false;
        }

        $db = Database::getInstance();
        $db->update('users', [
            'password' => self::hashPassword($password),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $reset['user_id']]);
        $db->delete('password_resets', 'user_id = ?', [(int) $reset['user_id']]);

        return true;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }

    public static function isEditor(): bool
    {
        $user = self::user();
        return $user && in_array($user['role'], ['admin', 'editor'], true);
    }

    public static function canPublish(): bool
    {
        return self::isEditor();
    }

    public static function canManageSettings(): bool
    {
        return self::isAdmin();
    }

    public static function canManageUsers(): bool
    {
        return self::isAdmin();
    }

    public static function canManageModules(): bool
    {
        return self::isAdmin();
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function roles(): array
    {
        return self::ROLES;
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            Router::redirect('admin/login');
        }
    }

    public static function requireEditor(): void
    {
        self::requireAuth();
        if (!self::isEditor()) {
            ErrorPage::send(403);
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            ErrorPage::send(403);
        }
    }
}
