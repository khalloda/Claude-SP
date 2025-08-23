<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Router;
use App\Core\I18n;
use App\Core\Auth;
use App\Config\Config;
use App\Controllers\DropdownController;
use App\Controllers\QuoteController;

// Initialize autoloader
Autoloader::register();

// Initialize configuration
Config::init();

// Handle language switching
$requestedLang = $_GET['lang'] ?? $_SESSION['locale'] ?? 'en';
I18n::init($requestedLang);

// Handle AJAX routes manually before router (to avoid routing conflicts)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Dropdown AJAX route
if ($path === '/dropdowns/get-by-parent' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check authentication
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

// Quote product details AJAX route
if ($path === '/quotes/get-product-details' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check authentication
    if (!Auth::check()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $controller = new QuoteController();
    $controller->getProductDetails();
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
    
    // Phase 2: Masters CRUD
    $r->resource('/clients', 'ClientController');
    $r->resource('/suppliers', 'SupplierController');
    $r->resource('/warehouses', 'WarehouseController');
    $r->resource('/products', 'ProductController');
    $r->resource('/dropdowns', 'DropdownController');
    
    // Phase 3: Sales Flow
    
    // Quotes
    $r->resource('/quotes', 'QuoteController');
    $r->post('/quotes/{id}/approve', 'QuoteController@approve');
    $r->post('/quotes/{id}/reject', 'QuoteController@reject');
    $r->post('/quotes/{id}/convert-to-order', 'QuoteController@convertToOrder');
    
    // Sales Orders
    $r->get('/salesorders', 'SalesOrderController@index');
    $r->get('/salesorders/{id}', 'SalesOrderController@show');
    $r->post('/salesorders/{id}/deliver', 'SalesOrderController@deliver');
    $r->post('/salesorders/{id}/reject', 'SalesOrderController@reject');
    $r->post('/salesorders/{id}/convert-to-invoice', 'SalesOrderController@convertToInvoice');
    $r->post('/salesorders/{id}/delete', 'SalesOrderController@destroy');
    
    // Invoices
    $r->resource('/invoices', 'InvoiceController');
    $r->post('/invoices/{id}/add-payment', 'InvoiceController@addPayment');
    $r->post('/invoices/{id}/void', 'InvoiceController@void');
    
    // Payments
    $r->get('/payments', 'PaymentController@index');
    $r->get('/payments/{id}', 'PaymentController@show');
	
	// Currency Management (Admin)
    $r->resource('/currencies', 'CurrencyController');
    $r->post('/currencies/{code}/set-primary', 'CurrencyController@setPrimary');
    $r->post('/currencies/update-rates', 'CurrencyController@updateRates');
    
    // Currency AJAX endpoints - Add these BEFORE the closing });
    $r->post('/currencies/set-user-currency', 'CurrencyController@setUserCurrency');
    $r->get('/currencies/exchange-rate', 'CurrencyController@getExchangeRate');
    $r->post('/currencies/convert', 'CurrencyController@convert');
});

// Handle the request
$router->resolve();
