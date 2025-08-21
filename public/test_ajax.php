<?php
// Simple test file - place in public/ folder
// Test URL: https://sp.elmadeenaelmunawarah.com/test_ajax.php

session_start();

require_once __DIR__ . '/../app/core/Autoloader.php';

use App\Core\Autoloader;
use App\Config\Config;
use App\Models\Dropdown;

Autoloader::register();
Config::init();

header('Content-Type: application/json');

try {
    $dropdown = new Dropdown();
    
    // Test getting car makes
    $carMakes = $dropdown->getByCategory('car_make');
    
    // Test getting car models for first car make
    $carModels = [];
    if (!empty($carMakes)) {
        $carModels = $dropdown->getByCategory('car_model', $carMakes[0]['id']);
    }
    
    echo json_encode([
        'success' => true,
        'car_makes' => $carMakes,
        'car_models_for_first_make' => $carModels,
        'test_url' => '/dropdowns/get-by-parent?category=car_make'
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
