<?php
declare(strict_types=1);

namespace App\Core;

class Helpers
{
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
    }

    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }

    public static function verifyCsrf(): bool
    {
        $token = self::input('_token');
        if (!isset($_SESSION['_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['_token'], $token);
    }

    public static function input(string $key, $default = null)
    {
        // Check POST first, then GET
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        
        return $default;
    }

    public static function old(string $key, $default = '')
    {
        $old = $_SESSION['old'][$key] ?? $default;
        return htmlspecialchars((string)$old);
    }

    public static function errors(string $key = null)
    {
        $errors = $_SESSION['errors'] ?? [];
        
        if ($key === null) {
            return $errors;
        }
        
        return $errors[$key] ?? null;
    }

    public static function clearOldInputAndErrors(): void
    {
        unset($_SESSION['old'], $_SESSION['errors']);
    }

    public static function escape($value): string
    {
        // Handle null and non-string values safely
        if ($value === null) {
            return '';
        }
        
        // Convert to string if it's not already
        if (!is_string($value)) {
            $value = (string) $value;
        }
        
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        return number_format($amount, 2) . ' ' . $currency;
    }

    public static function formatDate(string $date, string $format = 'Y-m-d'): string
    {
        return date($format, strtotime($date));
    }

    public static function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }

    public static function url(string $path = ''): string
    {
        $baseUrl = rtrim($_SERVER['HTTP_HOST'], '/');
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $scheme . '://' . $baseUrl . '/' . ltrim($path, '/');
    }
}
