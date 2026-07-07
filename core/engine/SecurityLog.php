<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class SecurityLog
{
    public static function write(string $event, array $context = []): void
    {
        if (!is_dir(STORAGE_PATH)) {
            return;
        }

        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $line = json_encode([
            'time' => date('c'),
            'event' => $event,
            'ip' => Request::clientIp(),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'context' => $context,
        ], JSON_UNESCAPED_UNICODE);

        if ($line !== false) {
            @file_put_contents($logDir . '/security.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}
