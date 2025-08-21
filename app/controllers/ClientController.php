<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Client;

class ClientController extends Controller
{
    private Client $clientModel;

    public function __construct()
    {
        $this->view('products/index', compact('products', 'search'));
    }

    public function create(): void
    {
        $dropdowns = $this->getDropdownsForForm();
        $warehouses = $this->warehouseModel->all();
        
        $this->view('products/form', compact('dropdowns', 'warehouses'));
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/products');
        }

        $data = $this->validate([
            'classification' => 'required',
            'name' => 'required',
            'cost_price' => 'required|numeric',
            'sale_price' => 'required|numeric'
        ]);

        try {
            $productId = $this->productModel->create($data);
            
            // Handle warehouse locations
            $this->updateProductLocations($productId);
            
            $this->setFlash('success', I18n::t('messages.created'));
            $this->redirect('/products/' . $productId);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error') . ': ' . $e->getMessage());
            $this->redirect('/products/create');
        }
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/products');
        }

        $locations = $this->productModel->getLocations($id);
        $stockMovements = $this->productModel->getStockMovements($id);
        
        $this->view('products/show', compact('product', 'locations', 'stockMovements'));
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/products');
        }
        
        $dropdowns = $this->getDropdownsForForm();
        $warehouses = $this->warehouseModel->all();
        $locations = $this->productModel->getLocations($id);
        
        $this->view('products/form', compact('product', 'dropdowns', 'warehouses', 'locations'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/products');
        }

        $id = (int) $params['id'];
        $data = $this->validate([
            'classification' => 'required',
            'name' => 'required',
            'cost_price' => 'required|numeric',
            'sale_price' => 'required|numeric'
        ]);

        try {
            $this->productModel->update($id, $data);
            
            // Handle warehouse locations
            $this->updateProductLocations($id);
            
            $this->setFlash('success', I18n::t('messages.updated'));
            $this->redirect('/products/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/products/' . $id . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/products');
        }

        $id = (int) $params['id'];
        
        try {
            $this->productModel->delete($id);
            $this->setFlash('success', I18n::t('messages.deleted'));
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/products');
    }

    private function getDropdownsForForm(): array
    {
        return [
            'classifications' => $this->dropdownModel->getByCategory('classification'),
            'colors' => $this->dropdownModel->getByCategory('color'),
            'brands' => $this->dropdownModel->getByCategory('brand'),
            'car_makes' => $this->dropdownModel->getByCategory('car_make'),
            'car_models' => $this->dropdownModel->getByCategory('car_model')
        ];
    }

    private function updateProductLocations(int $productId): void
    {
        $warehouses = Helpers::input('warehouses', []);
        $quantities = Helpers::input('quantities', []);
        $locations = Helpers::input('locations', []);
        
        if (is_array($warehouses)) {
            foreach ($warehouses as $index => $warehouseId) {
                $qty = (float) ($quantities[$index] ?? 0);
                $location = $locations[$index] ?? '';
                
                if ($qty > 0) {
                    $this->productModel->updateLocation($productId, (int)$warehouseId, $qty, $location);
                }
            }
        }
    }
}
