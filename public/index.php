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
    
    // Dropdowns - IMPORTANT: Specific routes MUST come before resource routes
    $r->get('/dropdowns/get-by-parent', 'DropdownController@getByParent');
    $r->resource('/dropdowns', 'DropdownController');
});

// Handle the request
$router->resolve();
