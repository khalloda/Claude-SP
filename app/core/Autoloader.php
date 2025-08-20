<?php
declare(strict_types=1);

namespace App\Core;

class Autoloader
{
    private static array $namespaces = [];

    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
        
        // Register App namespace
        self::addNamespace('App\\', __DIR__ . '/../');
    }

    public static function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        
        if (!isset(self::$namespaces[$prefix])) {
            self::$namespaces[$prefix] = [];
        }
        
        array_push(self::$namespaces[$prefix], $baseDir);
    }

    public static function load(string $class): bool
    {
        $prefix = $class;

        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);

            $mappedFile = self::loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }

            $prefix = rtrim($prefix, '\\');
        }

        return false;
    }

    private static function loadMappedFile(string $prefix, string $relativeClass): bool
    {
        if (!isset(self::$namespaces[$prefix])) {
            return false;
        }

        foreach (self::$namespaces[$prefix] as $baseDir) {
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            
            if (self::requireFile($file)) {
                return true;
            }
            
            // Fallback: try lowercase filename (for case-insensitive filesystems)
            $fileLower = $baseDir . str_replace('\\', '/', strtolower($relativeClass)) . '.php';
            if (self::requireFile($fileLower)) {
                return true;
            }
        }

        return false;
    }

    private static function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}
