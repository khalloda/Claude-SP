<?php
// Create this file: app/controllers/ProfileController.php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\User;

class ProfileController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        $user = Auth::user();
        $this->view('profile/index', compact('user'));
    }

    public function edit(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        $user = Auth::user();
        $this->view('profile/edit', compact('user'));
    }

    public function update(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/profile');
        }

        $data = $this->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'locale' => 'required|in:en,ar'
        ]);

        $userId = Auth::id();

        try {
            // Check if email is already taken by another user
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                $this->setFlash('error', 'Email address is already taken');
                $this->redirect('/profile/edit');
            }

            $success = $this->userModel->update($userId, $data);

            if ($success) {
                // Update session data
                $_SESSION['user'] = array_merge($_SESSION['user'], $data);
                
                // Update language if changed
                if ($_SESSION['locale'] !== $data['locale']) {
                    $_SESSION['locale'] = $data['locale'];
                }

                $this->setFlash('success', 'Profile updated successfully');
            } else {
                $this->setFlash('error', 'Failed to update profile');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error updating profile: ' . $e->getMessage());
        }

        $this->redirect('/profile');
    }

    public function changePassword(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        $this->view('profile/change-password');
    }

    public function updatePassword(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }

        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/profile/change-password');
        }

        $data = $this->validate([
            'current_password' => 'required|min:6',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|min:6'
        ]);

        if ($data['new_password'] !== $data['confirm_password']) {
            $this->setFlash('error', 'New passwords do not match');
            $this->redirect('/profile/change-password');
        }

        $userId = Auth::id();
        $user = Auth::user();

        try {
            // Verify current password
            if (!password_verify($data['current_password'], $user['password_hash'])) {
                $this->setFlash('error', 'Current password is incorrect');
                $this->redirect('/profile/change-password');
            }

            // Update password
            $success = $this->userModel->updatePassword($userId, $data['new_password']);

            if ($success) {
                $this->setFlash('success', 'Password changed successfully');
                $this->redirect('/profile');
            } else {
                $this->setFlash('error', 'Failed to change password');
                $this->redirect('/profile/change-password');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error changing password: ' . $e->getMessage());
            $this->redirect('/profile/change-password');
        }
    }
}
