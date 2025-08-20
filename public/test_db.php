<?php
// Database Connection Test - Delete after testing

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Autoloader;
use App\Config\Config;
use App\Config\DB;
use App\Models\User;

Autoloader::register();
Config::init();

echo "<h1>Database Connection Test</h1>";

try {
    // Test 1: Basic connection
    echo "<h2>1. Testing Database Connection</h2>";
    $pdo = DB::getInstance();
    echo "✅ Database connection successful<br>";
    
    // Test 2: Check if users table exists
    echo "<h2>2. Testing Users Table</h2>";
    $stmt = DB::query("SHOW TABLES LIKE 'sp_users'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "✅ sp_users table exists<br>";
        
        // Test 3: Check user count
        $stmt = DB::query("SELECT COUNT(*) as count FROM sp_users");
        $count = $stmt->fetch()['count'];
        echo "📊 Total users in database: $count<br>";
        
        // Test 4: Check admin user
        $stmt = DB::query("SELECT * FROM sp_users WHERE email = ?", ['admin@example.com']);
        $adminUser = $stmt->fetch();
        
        if ($adminUser) {
            echo "✅ Admin user found<br>";
            echo "👤 User details:<br>";
            echo "- ID: " . $adminUser['id'] . "<br>";
            echo "- Name: " . $adminUser['name'] . "<br>";
            echo "- Email: " . $adminUser['email'] . "<br>";
            echo "- Password Hash: " . substr($adminUser['password_hash'], 0, 50) . "...<br>";
            
            // Test 5: Password verification
            echo "<h2>3. Testing Password Verification</h2>";
            $testPassword = 'Admin@123';
            $hashFromDB = $adminUser['password_hash'];
            
            echo "Testing password: '$testPassword'<br>";
            echo "Hash from DB: " . substr($hashFromDB, 0, 50) . "...<br>";
            
            $verify1 = password_verify($testPassword, $hashFromDB);
            echo "Password verify result: " . ($verify1 ? '✅ VALID' : '❌ INVALID') . "<br>";
            
            // Test with the exact hash from seed.sql
            $seedHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            echo "<br>Testing with seed hash:<br>";
            echo "Seed hash: " . substr($seedHash, 0, 50) . "...<br>";
            $verify2 = password_verify($testPassword, $seedHash);
            echo "Seed hash verify result: " . ($verify2 ? '✅ VALID' : '❌ INVALID') . "<br>";
            
            // Generate a new hash for comparison
            $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
            echo "<br>Newly generated hash: " . substr($newHash, 0, 50) . "...<br>";
            $verify3 = password_verify($testPassword, $newHash);
            echo "New hash verify result: " . ($verify3 ? '✅ VALID' : '❌ INVALID') . "<br>";
            
        } else {
            echo "❌ Admin user NOT found<br>";
            echo "Available users:<br>";
            $stmt = DB::query("SELECT id, name, email FROM sp_users");
            $users = $stmt->fetchAll();
            foreach ($users as $user) {
                echo "- ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}<br>";
            }
        }
        
    } else {
        echo "❌ sp_users table does not exist<br>";
        echo "Available tables:<br>";
        $stmt = DB::query("SHOW TABLES");
        $tables = $stmt->fetchAll();
        foreach ($tables as $table) {
            echo "- " . array_values($table)[0] . "<br>";
        }
    }
    
    // Test 4: User Model test
    echo "<h2>4. Testing User Model</h2>";
    $userModel = new User();
    $modelResult = $userModel->findByEmail('admin@example.com');
    
    if ($modelResult) {
        echo "✅ User model found admin user<br>";
        echo "Model result: " . json_encode($modelResult) . "<br>";
    } else {
        echo "❌ User model did NOT find admin user<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<br><a href='/'>← Back to Login</a>";
?>