<?php
declare(strict_types=1);

namespace App\Core;

class Permissions
{
    public static function can(string $permission): bool
    {
        // Basic implementation - extend for RBAC later
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        
        // For now, all authenticated users have basic permissions
        // TODO: Implement role-based permissions in future phases
        return true;
    }

    public static function hasRole(string $role): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        // TODO: Implement role checking
        return true;
    }

    public static function guard(string $permission): void
    {
        if (!self::can($permission)) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Access Denied';
            exit;
        }
    }
}
