<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Currency;

class Helpers
{
    private static ?Currency $currencyModel = null;
    private static array $currencyCache = [];

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

    /**
     * Enhanced formatCurrency with multi-currency support
     */
    public static function formatCurrency(float $amount, ?string $currency = null, bool $showSymbol = true): string
    {
        // Get currency code
        if ($currency === null) {
            $currency = self::getDefaultCurrency();
        }

        // Get currency configuration
        $currencyConfig = self::getCurrencyConfig($currency);
        
        if (!$currencyConfig) {
            // Fallback to basic formatting
            return number_format($amount, 2) . ' ' . strtoupper($currency);
        }

        $decimalPlaces = (int) $currencyConfig['decimal_places'];
        $formattedAmount = number_format($amount, $decimalPlaces);

        if ($showSymbol) {
            return $formattedAmount . ' ' . $currencyConfig['symbol'];
        } else {
            return $formattedAmount . ' ' . $currencyConfig['code'];
        }
    }

    /**
     * Format currency with conversion display
     */
    public static function formatCurrencyWithConversion(
        float $amount, 
        string $currency, 
        ?string $convertTo = null,
        bool $showConversion = true
    ): string {
        $primary = self::formatCurrency($amount, $currency);
        
        if (!$showConversion || !$convertTo || $currency === $convertTo) {
            return $primary;
        }

        try {
            $currencyModel = self::getCurrencyModel();
            $convertedAmount = $currencyModel->convert($amount, $currency, $convertTo);
            $converted = self::formatCurrency($convertedAmount, $convertTo);
            
            return $primary . ' <small class="text-muted">(' . $converted . ')</small>';
        } catch (\Exception $e) {
            return $primary;
        }
    }

    /**
     * Get default currency for current user/session
     */
    public static function getDefaultCurrency(): string
    {
        // Check session preference first
        if (isset($_SESSION['user_currency'])) {
            return $_SESSION['user_currency'];
        }

        // Check user preference (if logged in)
        if (isset($_SESSION['user_id'])) {
            // TODO: Get from user preferences table when implemented
            // For now, fall back to system default
        }

        // System default - get from primary currency
        try {
            $currencyModel = self::getCurrencyModel();
            $primary = $currencyModel->getPrimary();
            return $primary ? $primary['code'] : 'EGP';
        } catch (\Exception $e) {
            return 'EGP'; // Hard fallback
        }
    }

    /**
     * Set user currency preference
     */
    public static function setUserCurrency(string $currency): void
    {
        $currency = strtoupper($currency);
        
        // Validate currency
        try {
            $currencyModel = self::getCurrencyModel();
            if (!$currencyModel->isValidCurrency($currency)) {
                throw new \InvalidArgumentException("Invalid currency: {$currency}");
            }
            
            $_SESSION['user_currency'] = $currency;
            
            // TODO: Save to user preferences table if user is logged in
            
        } catch (\Exception $e) {
            // Don't throw, just don't set invalid currency
        }
    }

    /**
     * Get currency configuration
     */
    public static function getCurrencyConfig(string $code): ?array
    {
        $code = strtoupper($code);
        
        // Check cache first
        if (isset(self::$currencyCache[$code])) {
            return self::$currencyCache[$code];
        }

        try {
            $currencyModel = self::getCurrencyModel();
            $config = $currencyModel->getByCode($code);
            
            if ($config) {
                self::$currencyCache[$code] = $config;
            }
            
            return $config;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all active currencies
     */
    public static function getActiveCurrencies(): array
    {
        try {
            $currencyModel = self::getCurrencyModel();
            return $currencyModel->getActive();
        } catch (\Exception $e) {
            return self::getFallbackCurrencies();
        }
    }

    /**
     * Get currency dropdown options for forms
     */
    public static function getCurrencyOptions(?string $selected = null): string
    {
        $html = '';
        $currencies = self::getActiveCurrencies();
        $defaultCurrency = $selected ?? self::getDefaultCurrency();

        foreach ($currencies as $currency) {
            $isSelected = ($currency['code'] === $defaultCurrency) ? 'selected' : '';
            $isPrimary = $currency['is_primary'] ? ' (Primary)' : '';
            
            $html .= sprintf(
                '<option value="%s" data-symbol="%s" data-rate="%s" %s>%s (%s)%s</option>',
                $currency['code'],
                htmlspecialchars($currency['symbol']),
                $currency['exchange_rate'],
                $isSelected,
                htmlspecialchars($currency['name']),
                htmlspecialchars($currency['symbol']),
                $isPrimary
            );
        }

        return $html;
    }

    /**
     * Convert between currencies
     */
    public static function convertCurrency(float $amount, string $from, string $to): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        try {
            $currencyModel = self::getCurrencyModel();
            return $currencyModel->convert($amount, $from, $to);
        } catch (\Exception $e) {
            // Log error if needed
            return $amount; // Return original amount as fallback
        }
    }

    /**
     * Get exchange rate between currencies
     */
    public static function getExchangeRate(string $from, string $to): float
    {
        try {
            $currencyModel = self::getCurrencyModel();
            return $currencyModel->getExchangeRate($from, $to);
        } catch (\Exception $e) {
            return 1.0; // Fallback rate
        }
    }

    /**
     * Format date
     */
    public static function formatDate(string $date, string $format = 'Y-m-d'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Format datetime
     */
    public static function formatDateTime(string $datetime, string $format = 'Y-m-d H:i'): string
    {
        return date($format, strtotime($datetime));
    }

    /**
     * Asset URL helper
     */
    public static function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }

    /**
     * URL helper
     */
    public static function url(string $path = ''): string
    {
        $baseUrl = rtrim($_SERVER['HTTP_HOST'], '/');
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $scheme . '://' . $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Generate currency JavaScript configuration
     */
    public static function getCurrencyJavaScriptConfig(): string
    {
        $currencies = self::getActiveCurrencies();
        $config = [];
        
        foreach ($currencies as $currency) {
            $config[$currency['code']] = [
                'name' => $currency['name'],
                'symbol' => $currency['symbol'],
                'decimal_places' => (int) $currency['decimal_places'],
                'exchange_rate' => (float) $currency['exchange_rate'],
                'is_primary' => (bool) $currency['is_primary']
            ];
        }

        return json_encode($config, JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Check if currency feature is enabled
     */
    public static function isMultiCurrencyEnabled(): bool
    {
        try {
            $currencies = self::getActiveCurrencies();
            return count($currencies) > 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Private helper methods
     */
    private static function getCurrencyModel(): Currency
    {
        if (self::$currencyModel === null) {
            self::$currencyModel = new Currency();
        }
        return self::$currencyModel;
    }

    private static function getFallbackCurrencies(): array
    {
        return [
            [
                'id' => 1,
                'code' => 'EGP',
                'name' => 'Egyptian Pound',
                'symbol' => 'ج.م',
                'is_primary' => 1,
                'is_active' => 1,
                'exchange_rate' => 1.000000,
                'decimal_places' => 2
            ],
            [
                'id' => 2,
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'is_primary' => 0,
                'is_active' => 1,
                'exchange_rate' => 0.032258,
                'decimal_places' => 2
            ]
        ];
    }

    /**
     * Format number with proper locale
     */
    public static function formatNumber(float $number, int $decimals = 2, string $locale = 'en'): string
    {
        if ($locale === 'ar') {
            // Arabic number formatting
            $formatted = number_format($number, $decimals, '.', ',');
            // Convert to Arabic numerals if needed
            $western = ['0','1','2','3','4','5','6','7','8','9'];
            $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            return str_replace($western, $arabic, $formatted);
        }
        
        return number_format($number, $decimals);
    }

    /**
     * Get currency symbol only
     */
    public static function getCurrencySymbol(string $currency): string
    {
        $config = self::getCurrencyConfig($currency);
        return $config ? $config['symbol'] : strtoupper($currency);
    }

    /**
     * Validate currency amount
     */
    public static function isValidAmount(float $amount, string $currency = 'EGP'): bool
    {
        if ($amount < 0) {
            return false;
        }

        $config = self::getCurrencyConfig($currency);
        if (!$config) {
            return true; // If we can't validate, assume it's valid
        }

        // Check decimal places
        $decimalPlaces = (int) $config['decimal_places'];
        $multiplier = pow(10, $decimalPlaces);
        $rounded = round($amount * $multiplier) / $multiplier;
        
        return abs($amount - $rounded) < 0.001; // Allow for floating point precision
    }
}
