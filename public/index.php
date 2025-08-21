<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Router;
use App\Core\I18n;
use App\Config\Config;

// Initialize autoloader
Autoloader::register();

// Initialize configuration
Config::init();

// Handle language switching
$requestedLang = $_GET['lang'] ?? $_SESSION['locale'] ?? 'en';
I18n::init($requestedLang);

// Initialize router
$router = new Router();

// Public routes
$router->get('/', 'AuthController@loginForm');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Protected routes
$router->group(['auth' => true], function(Router $r) {
    // Dashboard
    $r->get('/dashboard', 'DashboardController@index');
    
    // Clients
    $r->resource('/clients', 'ClientController');
    
    // Suppliers  
    $r->resource('/suppliers', 'SupplierController');
    
    // Warehouses
    $r->resource('/warehouses', 'WarehouseController');
    
    // Products
    $r->resource('/products', 'ProductController');
    
    // Dropdowns
    $r->resource('/dropdowns', 'DropdownController');
    $r->get('/dropdowns/get-by-parent', 'DropdownController@getByParent');
});

// Handle the request
// Add this BEFORE $router->resolve() in your public/index.php

// Debug route for AJAX testing
if ($_SERVER['REQUEST_URI'] === '/debug-dropdowns' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    require_once __DIR__ . '/../app/core/Autoloader.php';
    use App\Core\Autoloader;
    use App\Models\Dropdown;
    use App\Config\Config;
    
    Autoloader::register();
    Config::init();
    
    $dropdown = new Dropdown();
    
    $parentId = $_GET['parent_id'] ?? null;
    $category = $_GET['category'] ?? null;
    
    $debug = [
        'request' => [
            'parent_id' => $parentId,
            'category' => $category,
            'url' => $_SERVER['REQUEST_URI'],
            'method' => $_SERVER['REQUEST_METHOD']
        ]
    ];
    
    try {
        if ($category === 'car_make') {
            $data = $dropdown->getByCategory('car_make');
            $debug['result'] = ['success' => true, 'data' => $data, 'count' => count($data)];
        } elseif ($category === 'car_model' && $parentId) {
            $data = $dropdown->getByCategory('car_model', (int)$parentId);
            $debug['result'] = ['success' => true, 'data' => $data, 'count' => count($data)];
        } else {
            $debug['result'] = ['success' => false, 'message' => 'Invalid parameters'];
        }
    } catch (Exception $e) {
        $debug['result'] = ['success' => false, 'error' => $e->getMessage()];
    }
    
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}
$router->resolve();

