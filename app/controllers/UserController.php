<?php
// Create this file: app/controllers/UserController.php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\User;
use App\Config\DB;

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::hasRole('admin')) {
            $this->setFlash('error', 'Access denied. Admin privileges required.');
            $this->redirect('/dashboard');
        }

        $this->userModel = new User();
    }

    public function index(): void
    {
        $page = (int) Helpers::input('page', 1);
        $search = Helpers::input('search', '');
        
        $users = $this->getUsersWithPagination($page, $search);
        $stats = $this->getUserStats();
        
        $this->view('users/index', compact('users', 'stats', 'search'));
    }

    public function create(): void
    {
        $roles = $this->getRoles();
        $this->view('users/form', compact('roles'));
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/users');
        }

        $data = $this->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
            'locale' => 'required|in:en,ar'
        ]);

        try {
            // Check if email already exists
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                $this->setFlash('error', 'Email address is already registered');
                $this->redirect('/users/create');
            }

            // Create user
            $userId = $this->userModel->createUser($data);
            
            // Assign role if specified
            $roleId = Helpers::input('role_id');
            if ($roleId) {
                $this->assignUserRole($userId, (int)$roleId);
            }

            $this->setFlash('success', 'User created successfully');
            $this->redirect('/users');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error creating user: ' . $e->getMessage());
            $this->redirect('/users/create');
        }
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        $userRoles = $this->getUserRoles($id);
        $userStats = $this->getUserActivityStats($id);
        
        $this->view('users/show', compact('user', 'userRoles', 'userStats'));
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        $roles = $this->getRoles();
        $userRoles = $this->getUserRoles($id);
        
        $this->view('users/form', compact('user', 'roles', 'userRoles'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/users');
        }

        $id = (int) $params['id'];
        $user = $this->userModel->find($id);
        
        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        $data = $this->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'locale' => 'required|in:en,ar'
        ]);

        try {
            // Check if email is taken by another user
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $id) {
                $this->setFlash('error', 'Email address is already taken');
                $this->redirect('/users/' . $id . '/edit');
            }

            // Update user
            $success = $this->userModel->update($id, $data);
            
            // Update password if provided
            $newPassword = Helpers::input('password');
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $this->setFlash('error', 'Password must be at least 6 characters');
                    $this->redirect('/users/' . $id . '/edit');
                }
                $this->userModel->updatePassword($id, $newPassword);
            }

            // Update role if specified
            $roleId = Helpers::input('role_id');
            if ($roleId) {
                $this->updateUserRole($id, (int)$roleId);
            }

            if ($success) {
                $this->setFlash('success', 'User updated successfully');
            } else {
                $this->setFlash('error', 'Failed to update user');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error updating user: ' . $e->getMessage());
        }

        $this->redirect('/users');
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/users');
        }

        $id = (int) $params['id'];
        
        // Prevent deletion of current user
        if ($id === Auth::id()) {
            $this->setFlash('error', 'Cannot delete your own account');
            $this->redirect('/users');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        try {
            $success = $this->userModel->delete($id);
            
            if ($success) {
                $this->setFlash('success', 'User deleted successfully');
            } else {
                $this->setFlash('error', 'Failed to delete user');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error deleting user: ' . $e->getMessage());
        }

        $this->redirect('/users');
    }

    private function getUsersWithPagination(int $page, string $search = ''): array
    {
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = 'WHERE u.name LIKE ? OR u.email LIKE ?';
            $params = ["%{$search}%", "%{$search}%"];
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as count FROM sp_users u {$whereClause}";
        $stmt = DB::query($countQuery, $params);
        $totalRecords = (int)$stmt->fetch()['count'];
        $totalPages = (int)ceil($totalRecords / $perPage);
        
        // Get paginated results with roles
        $query = "
            SELECT u.*, 
                   GROUP_CONCAT(r.name) as roles,
                   GROUP_CONCAT(r.id) as role_ids
            FROM sp_users u 
            LEFT JOIN sp_user_roles ur ON ur.user_id = u.id 
            LEFT JOIN sp_roles r ON r.id = ur.role_id 
            {$whereClause}
            GROUP BY u.id 
            ORDER BY u.created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $stmt = DB::query($query, array_merge($params, [$perPage, $offset]));
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }

    private function getUserStats(): array
    {
        $queries = [
            'total' => "SELECT COUNT(*) as count FROM sp_users",
            'active' => "SELECT COUNT(*) as count FROM sp_users WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)",
            'admins' => "SELECT COUNT(DISTINCT ur.user_id) as count FROM sp_user_roles ur JOIN sp_roles r ON r.id = ur.role_id WHERE r.name = 'admin'",
            'recent' => "SELECT COUNT(*) as count FROM sp_users WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
        ];

        $stats = [];
        foreach ($queries as $key => $query) {
            try {
                $stmt = DB::query($query);
                $result = $stmt->fetch();
                $stats[$key] = (int)($result['count'] ?? 0);
            } catch (\Exception $e) {
                $stats[$key] = 0;
            }
        }

        return $stats;
    }

    private function getRoles(): array
    {
        try {
            $stmt = DB::query("SELECT * FROM sp_roles ORDER BY name");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUserRoles(int $userId): array
    {
        try {
            $query = "
                SELECT r.* 
                FROM sp_roles r 
                JOIN sp_user_roles ur ON ur.role_id = r.id 
                WHERE ur.user_id = ?
            ";
            $stmt = DB::query($query, [$userId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function assignUserRole(int $userId, int $roleId): void
    {
        try {
            DB::query("INSERT IGNORE INTO sp_user_roles (user_id, role_id) VALUES (?, ?)", [$userId, $roleId]);
        } catch (\Exception $e) {
            // Ignore errors - role assignment is optional
        }
    }

    private function updateUserRole(int $userId, int $roleId): void
    {
        try {
            DB::beginTransaction();
            
            // Remove existing roles
            DB::query("DELETE FROM sp_user_roles WHERE user_id = ?", [$userId]);
            
            // Add new role
            DB::query("INSERT INTO sp_user_roles (user_id, role_id) VALUES (?, ?)", [$userId, $roleId]);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function getUserActivityStats(int $userId): array
    {
        // Placeholder for user activity statistics
        // Can be expanded later with actual activity tracking
        return [
            'last_login' => null,
            'login_count' => 0,
            'created_records' => 0
        ];
    }
}
