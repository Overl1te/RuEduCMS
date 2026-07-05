<?php

declare(strict_types=1);

namespace RuEdu\Engine;

class Mail
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $from = Config::get('admin_email', '');
        if ($from === '') {
            return false;
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $from,
            'Reply-To: ' . $from,
        ];

        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
    }
}
