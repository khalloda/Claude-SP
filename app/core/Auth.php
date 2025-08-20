<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        error_log("Auth::attempt called with email: $email");
        
        try {
            $user = new User();
            $userData = $user->first('email', $email);
            
            error_log("User lookup result: " . json_encode($userData));
            
            if (!$userData) {
                error_log("No user found with email: $email");
                return false;
            }
            
            error_log("Stored password hash: " . $userData['password_hash']);
            error_log("Attempting to verify password...");
            
            $passwordValid = password_verify($password, $userData['password_hash']);
            error_log("Password verification result: " . ($passwordValid ? 'true' : 'false'));
            
            if ($passwordValid) {
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['user'] = $userData;
                error_log("Login successful, session set");
                return true;
            } else {
                error_log("Password verification failed");
                return false;
            }
            
        } catch (\Exception $e) {
            error_log("Auth::attempt exception: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user']);
        session_destroy();
    }

    public static function guest(): bool
    {
        return !self::check();
    }
}