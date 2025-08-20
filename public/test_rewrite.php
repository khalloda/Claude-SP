<?php
// Test URL Rewriting - Delete this file after testing

echo "<h1>URL Rewriting Test</h1>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";

echo "<h2>Test Links:</h2>";
echo "<ul>";
echo "<li><a href='/'>Home (should work)</a></li>";
echo "<li><a href='/login'>Login (should work if rewrite works)</a></li>";
echo "<li><a href='/dashboard'>Dashboard (should work if rewrite works)</a></li>";
echo "<li><a href='/nonexistent'>Non-existent (should show 404)</a></li>";
echo "</ul>";

echo "<h2>Rewrite Status:</h2>";
if (strpos($_SERVER['REQUEST_URI'], 'test_rewrite.php') !== false) {
    echo "<p style='color: green;'>✅ Direct access works</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Accessed via rewrite</p>";
}

if (file_exists('.htaccess')) {
    echo "<p style='color: green;'>✅ .htaccess file exists</p>";
} else {
    echo "<p style='color: red;'>❌ .htaccess file missing</p>";
}

if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
    echo "<p style='color: green;'>✅ mod_rewrite is loaded</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Cannot verify mod_rewrite status</p>";
}
?>