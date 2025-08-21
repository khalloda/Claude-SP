<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Product;
use App\Models\Dropdown;
use App\Models\Warehouse;

class ProductController extends Controller
{
    private Product $productModel;
    private Dropdown $dropdownModel;
    private Warehouse $warehouseModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->dropdownModel = new Dropdown();
        $this->warehouseModel = new Warehouse();
    }

    public function index(): void
    {
        $search = Helpers::input('search', '');
        $page = (int) Helpers::input('page', 1);
        
        if (!empty($search)) {
            $products = $this->productModel->search($search, $page);
        } else {
            $products = $this->productModel->paginate($page);
        }
        
        $this->
