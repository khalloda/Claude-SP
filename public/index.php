<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Router;
use App\Core\I18n;
use App\Config\Config;
use App\Controllers\DropdownController;

// Initialize autoloader
Autoloader::register();

// Initialize configuration
Config::init();

// Handle language switching
$requestedLang = $_GET['lang'] ?? $_SESSION['locale'] ?? 'en';
I18n::init($requestedLang);

// Handle AJAX route manually before router (to avoid routing conflicts)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/dropdowns/get-by-parent' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check authentication
    use App\Core\Auth;
    if (!Auth::check()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $controller = new DropdownController();
    $controller->getByParent();
    exit;
}

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
    
    // Dropdowns (AJAX route handled manually above)
    $r->resource('/dropdowns', 'DropdownController');
});

// Handle the request
$router->resolve();
