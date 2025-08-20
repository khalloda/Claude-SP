<?php
// Fix Admin Password - Delete after use

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Autoloader;
use App\Config\Config;
use App\Config\DB;

Autoloader::register();
Config::init();

echo "<h1>Fix Admin Password</h1>";

try {
    // Generate a new, correct password hash
    $password = 'Admin@123';
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<h2>Generating New Password Hash</h2>";
    echo "Password: <strong>$password</strong><br>";
    echo "New Hash: <code>" . htmlspecialchars($newHash) . "</code><br>";
    
    // Verify the new hash works
    $verify = password_verify($password, $newHash);
    echo "Hash verification: " . ($verify ? '✅ VALID' : '❌ INVALID') . "<br><br>";
    
    if ($verify) {
        // Update the admin user in database
        echo "<h2>Updating Admin User</h2>";
        
        $stmt = DB::query(
            "UPDATE sp_users SET password_hash = ? WHERE email = ?", 
            [$newHash, 'admin@example.com']
        );
        
        $rowsAffected = $stmt->rowCount();
        echo "Rows updated: $rowsAffected<br>";
        
        if ($rowsAffected > 0) {
            echo "✅ <strong>Admin password updated successfully!</strong><br>";
            echo "<br>You can now login with:<br>";
            echo "📧 Email: <strong>admin@example.com</strong><br>";
            echo "🔑 Password: <strong>Admin@123</strong><br>";
            
            // Test the update
            echo "<h2>Testing Updated Password</h2>";
            $stmt = DB::query("SELECT password_hash FROM sp_users WHERE email = ?", ['admin@example.com']);
            $result = $stmt->fetch();
            
            if ($result) {
                $testVerify = password_verify($password, $result['password_hash']);
                echo "Updated hash verification: " . ($testVerify ? '✅ SUCCESS' : '❌ FAILED') . "<br>";
            }
            
            echo "<br><a href='/login' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>→ Go to Login Page</a>";
            
        } else {
            echo "❌ No rows were updated. Admin user may not exist.<br>";
        }
    } else {
        echo "❌ Generated hash is invalid. Something is wrong with PHP password functions.<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<br><hr>";
echo "<h2>Alternative: Manual SQL Update</h2>";
echo "<p>If the above doesn't work, run this SQL command in phpMyAdmin:</p>";
echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
echo "UPDATE sp_users SET password_hash = '" . password_hash('Admin@123', PASSWORD_DEFAULT) . "' WHERE email = 'admin@example.com';";
echo "</pre>";
?>