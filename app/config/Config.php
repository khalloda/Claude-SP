<?php
declare(strict_types=1);

namespace App\Config;

class Config
{
    public static array $database = [
        'host' => 'p3nlmysql13plsk.secureserver.net:3306',
        'dbname' => 'sp_main',
        'username' => 'sp',
        'password' => 'Mi@SP@123',
        'charset' => 'utf8mb4',
        'options' => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ];

    public static array $app = [
        'name' => 'Spare Parts Management',
        'url' => 'http://localhost',
        'debug' => true,
        'timezone' => 'UTC',
        'locale' => 'en',
        'supported_locales' => ['en', 'ar'],
    ];

    public static array $mail = [
        'driver' => 'smtp', // smtp or mail
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'tls', // tls or ssl
        'from_address' => 'noreply@spareparts.com',
        'from_name' => 'Spare Parts System',
    ];

    public static function init(): void
    {
        // Set timezone
        date_default_timezone_set(self::$app['timezone']);
        
        // Set error reporting based on debug mode
        if (self::$app['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }
}
