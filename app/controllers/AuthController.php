<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Helpers;
use App\Core\I18n;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->view('auth/login');
    }

    public function login(): void
    {
        // Debug information
        error_log("AuthController::login called");
        error_log("POST data: " . json_encode($_POST));
        error_log("SESSION before: " . json_encode($_SESSION));
        
        if (!Helpers::verifyCsrf()) {
            error_log("CSRF verification failed");
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/login');
            return;
        }

        $data = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        error_log("Validation passed, attempting login for: " . $data['email']);

        if (Auth::attempt($data['email'], $data['password'])) {
            error_log("Login successful");
            Helpers::clearOldInputAndErrors();
            $this->setFlash('success', I18n::t('auth.login_success'));
            
            // Use absolute URL for redirect
            header('Location: /dashboard');
            exit;
        } else {
            error_log("Login failed for: " . $data['email']);
            $this->setFlash('error', I18n::t('auth.login_failed'));
            
            // Use absolute URL for redirect
            header('Location: /login');
            exit;
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $this->setFlash('success', I18n::t('auth.logout_success'));
        header('Location: /login');
        exit;
    }
}