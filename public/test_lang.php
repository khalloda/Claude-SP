<?php
// Language Testing Page - Delete after debugging

session_start();

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\I18n;
use App\Config\Config;

Autoloader::register();
Config::init();

echo "<h1>Language System Test</h1>";

// Get current language request
$requestedLang = $_GET['lang'] ?? $_SESSION['locale'] ?? 'en';
echo "<p><strong>Requested Language:</strong> $requestedLang</p>";

// Initialize I18n
I18n::init($requestedLang);

// Show debug info
echo "<h2>I18n Debug Information</h2>";
$debug = I18n::debug();
echo "<pre>" . json_encode($debug, JSON_PRETTY_PRINT) . "</pre>";

// Test translations
echo "<h2>Translation Tests</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Key</th><th>English</th><th>Arabic</th></tr>";

$testKeys = [
    'app.name',
    'app.welcome', 
    'navigation.dashboard',
    'auth.login',
    'auth.logout'
];

foreach ($testKeys as $key) {
    echo "<tr>";
    echo "<td>$key</td>";
    
    // Test English
    I18n::init('en');
    $enTranslation = I18n::t($key);
    echo "<td>$enTranslation</td>";
    
    // Test Arabic
    I18n::init('ar');
    $arTranslation = I18n::t($key);
    echo "<td dir='rtl'>$arTranslation</td>";
    
    echo "</tr>";
}
echo "</table>";

// Reset to requested language
I18n::init($requestedLang);

// Show file status
echo "<h2>Language Files Status</h2>";
$enFile = __DIR__ . '/../app/lang/en.php';
$arFile = __DIR__ . '/../app/lang/ar.php';

echo "<p><strong>English file:</strong> " . ($enFile) . " - " . (file_exists($enFile) ? '✅ EXISTS' : '❌ NOT FOUND') . "</p>";
echo "<p><strong>Arabic file:</strong> " . ($arFile) . " - " . (file_exists($arFile) ? '✅ EXISTS' : '❌ NOT FOUND') . "</p>";

if (file_exists($enFile)) {
    $enData = require $enFile;
    echo "<p>English translations loaded: " . count($enData) . " sections</p>";
}

if (file_exists($arFile)) {
    $arData = require $arFile;
    echo "<p>Arabic translations loaded: " . count($arData) . " sections</p>";
}

// Test links
echo "<h2>Test Language Switching</h2>";
echo "<p>";
echo "<a href='?lang=en' style='margin-right: 10px; padding: 5px 10px; background: #007cba; color: white; text-decoration: none;'>Switch to English</a>";
echo "<a href='?lang=ar' style='margin-right: 10px; padding: 5px 10px; background: #007cba; color: white; text-decoration: none;'>Switch to Arabic</a>";
echo "</p>";

// Current page test
echo "<h2>Current Page Test</h2>";
echo "<p dir='" . I18n::getDirection() . "'>";
echo "Direction: <strong>" . I18n::getDirection() . "</strong><br>";
echo "Is RTL: <strong>" . (I18n::isRTL() ? 'YES' : 'NO') . "</strong><br>";
echo "App Name: <strong>" . I18n::t('app.name') . "</strong><br>";
echo "Dashboard: <strong>" . I18n::t('navigation.dashboard') . "</strong><br>";
echo "</p>";

echo "<p><a href='/dashboard'>← Back to Dashboard</a></p>";
?>