<?php
declare(strict_types=1);

namespace App\Core;

class I18n
{
    private static string $locale = 'en';
    private static array $translations = [];

    public static function init(string $locale): void
    {
        // Validate and set locale
        $validLocales = ['en', 'ar'];
        self::$locale = in_array($locale, $validLocales) ? $locale : 'en';
        
        // Store in session for persistence
        $_SESSION['locale'] = self::$locale;
        
        // Load translations for the current locale
        self::loadTranslations();
        
        // Debug logging
        error_log("I18n::init called with locale: $locale");
        error_log("Set locale to: " . self::$locale);
        error_log("Session locale: " . ($_SESSION['locale'] ?? 'not set'));
    }

    public static function getLocale(): string
    {
        return self::$locale;
    }

    public static function isRTL(): bool
    {
        return self::$locale === 'ar';
    }

    public static function t(string $key, array $params = []): string
    {
        $translation = self::getTranslation($key);
        
        // Replace parameters
        foreach ($params as $param => $value) {
            $translation = str_replace(":{$param}", (string)$value, $translation);
        }
        
        return $translation;
    }

    private static function getTranslation(string $key): string
    {
        $keys = explode('.', $key);
        $translation = self::$translations;
        
        foreach ($keys as $k) {
            if (isset($translation[$k])) {
                $translation = $translation[$k];
            } else {
                // Return key if translation not found
                error_log("Translation not found for key: $key in locale: " . self::$locale);
                return $key;
            }
        }
        
        return is_string($translation) ? $translation : $key;
    }

    private static function loadTranslations(): void
    {
        $langFile = __DIR__ . "/../lang/" . self::$locale . ".php";
        
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
            error_log("Loaded translations for locale: " . self::$locale);
            error_log("Translations loaded: " . count(self::$translations) . " sections");
        } else {
            error_log("Language file not found: $langFile");
            self::$translations = [];
        }
    }

    public static function getDirection(): string
    {
        return self::isRTL() ? 'rtl' : 'ltr';
    }
    
    // Debug method to check current state
    public static function debug(): array
    {
        return [
            'locale' => self::$locale,
            'session_locale' => $_SESSION['locale'] ?? null,
            'is_rtl' => self::isRTL(),
            'direction' => self::getDirection(),
            'translations_loaded' => !empty(self::$translations),
            'translation_sections' => array_keys(self::$translations)
        ];
    }
}