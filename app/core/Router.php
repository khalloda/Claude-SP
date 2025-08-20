<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Auth;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function get(string $path, string $action): void
    {
        $this->addRoute('GET', $path, $action);
    }

    public function post(string $path, string $action): void
    {
        $this->addRoute('POST', $path, $action);
    }

    public function resource(string $path, string $controller): void
    {
        $baseName = trim($path, '/');
        
        $this->get($path, $controller . '@index');
        $this->get($path . '/create', $controller . '@create');
        $this->post($path, $controller . '@store');
        $this->get($path . '/{id}', $controller . '@show');
        $this->get($path . '/{id}/edit', $controller . '@edit');
        $this->post($path . '/{id}', $controller . '@update');
        $this->post($path . '/{id}/delete', $controller . '@destroy');
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $path, string $action): void
    {
        // Normalize path (handle trailing slashes)
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $route = [
            'method' => $method,
            'path' => $path,
            'action' => $action,
            'middleware' => $this->getGroupMiddleware()
        ];

        $this->routes[] = $route;
    }

    private function getGroupMiddleware(): array
    {
        $middleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['auth']) && $group['auth']) {
                $middleware[] = 'auth';
            }
        }
        return $middleware;
    }

    public function resolve(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Normalize path
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                // Check middleware
                if (in_array('auth', $route['middleware']) && !Auth::check()) {
                    $this->redirect('/login');
                    return;
                }

                $this->executeAction($route['action'], $this->extractParams($route['path'], $path));
                return;
            }
        }

        $this->notFound();
    }

    private function matchPath(string $routePath, string $requestPath): bool
    {
        // Convert route path to regex
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        // Return boolean (preg_match returns int, we need bool)
        return (bool) preg_match($pattern, $requestPath);
    }

    private function extractParams(string $routePath, string $requestPath): array
    {
        $params = [];
        
        // Extract parameter names from route
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Extract values from request path
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches); // Remove full match
            
            foreach ($paramNames[1] as $index => $name) {
                if (isset($matches[$index])) {
                    $params[$name] = $matches[$index];
                }
            }
        }
        
        return $params;
    }

    private function executeAction(string $action, array $params = []): void
    {
        [$controllerName, $methodName] = explode('@', $action);
        
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $methodName)) {
            throw new \Exception("Method {$methodName} not found in {$controllerClass}");
        }
        
        $controller->$methodName($params);
    }

    private function redirect(string $path): void
    {
        header("Location: {$path}");
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo "404 - Page Not Found";
        exit;
    }
}