<?php
// Replace your existing app/core/Auth.php with this enhanced version:

declare(strict_types=1);

namespace App\Core;

use App\Models\User;
use App\Config\DB;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        error_log("Auth::attempt called with email: $email");
        
        try {
            $user = new User();
            $userData = $user->findByEmail($email);
            
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
                // Load user roles
                $roles = self::loadUserRoles($userData['id']);
                
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['user'] = $userData;
                $_SESSION['roles'] = $roles;
                
                // Set primary role for easy access
                if (!empty($roles)) {
                    $_SESSION['role'] = $roles[0]['name']; // Primary role
                } else {
                    // Default role if no roles assigned
                    $_SESSION['role'] = 'user';
                }
                
                error_log("Login successful, session set with roles: " . json_encode($roles));
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

    /**
     * Load user roles from database
     */
    private static function loadUserRoles(int $userId): array
    {
        try {
            $query = "
                SELECT r.id, r.name, r.description 
                FROM sp_roles r 
                INNER JOIN sp_user_roles ur ON ur.role_id = r.id 
                WHERE ur.user_id = ?
                ORDER BY r.id ASC
            ";
            
            $stmt = DB::query($query, [$userId]);
            $roles = $stmt->fetchAll();
            
            // If no roles found, check if user ID 1 (admin) and assign admin role
            if (empty($roles) && $userId == 1) {
                // Auto-assign admin role to user ID 1 if not already assigned
                try {
                    DB::query("INSERT IGNORE INTO sp_user_roles (user_id, role_id) VALUES (1, 1)");
                    return [['id' => 1, 'name' => 'admin', 'description' => 'Full system access']];
                } catch (\Exception $e) {
                    error_log("Failed to auto-assign admin role: " . $e->getMessage());
                }
            }
            
            return $roles;
        } catch (\Exception $e) {
            error_log("Error loading user roles: " . $e->getMessage());
            return [];
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

    /**
     * Get user's primary role
     */
    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Get all user roles
     */
    public static function roles(): array
    {
        return $_SESSION['roles'] ?? [];
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole(string $roleName): bool
    {
        $userRoles = self::roles();
        
        foreach ($userRoles as $role) {
            if (strtolower($role['name']) === strtolower($roleName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user'], $_SESSION['roles'], $_SESSION['role']);
        session_destroy();
    }

    public static function guest(): bool
    {
        return !self::check();
    }
}
