<?php
declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        
        $viewPath = __DIR__ . "/../views/{$view}.php";
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }
        
        require $viewPath;
    }

    protected function redirect(string $path): void
    {
        header("Location: {$path}");
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function validate(array $rules): array
    {
        $errors = [];
        $data = [];

        foreach ($rules as $field => $rule) {
            $value = Helpers::input($field);
            $data[$field] = $value;

            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = I18n::t('validation.required', ['field' => $field]);
            }

            if (strpos($rule, 'email') !== false && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = I18n::t('validation.email');
            }

            if (strpos($rule, 'min:') !== false && !empty($value)) {
                $min = (int) substr($rule, strpos($rule, 'min:') + 4);
                if (strlen($value) < $min) {
                    $errors[$field] = I18n::t('validation.min', ['min' => $min]);
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->back();
        }

        return $data;
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    protected function getFlash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
}
