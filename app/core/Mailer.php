<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Config;

class Mailer
{
    public static function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        $config = Config::$mail;
        
        if ($config['driver'] === 'smtp') {
            return self::sendViaSmtp($to, $subject, $body, $attachments);
        }
        
        return self::sendViaMail($to, $subject, $body);
    }

    private static function sendViaSmtp(string $to, string $subject, string $body, array $attachments = []): bool
    {
        try {
            $mailer = new SMTPMailer();
            return $mailer->send($to, $subject, $body, $attachments);
        } catch (\Exception $e) {
            error_log('SMTP Error: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendViaMail(string $to, string $subject, string $body): bool
    {
        $config = Config::$mail;
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $config['from_name'] . ' <' . $config['from_address'] . '>',
            'Reply-To: ' . $config['from_address'],
            'X-Mailer: PHP/' . phpversion()
        ];
        
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
