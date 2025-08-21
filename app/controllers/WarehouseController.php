<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    private Warehouse $warehouseModel;

    public function __construct()
    {
        $this->warehouseModel = new Warehouse();
    }

    public function index(): void
    {
        $search = Helpers::input('search', '');
        $page = (int) Helpers::input('page', 1);
        
        if (!empty($search)) {
            $warehouses = $this->warehouseModel->search($search, $page);
        } else {
            $warehouses = $this->warehouseModel->paginate($page);
        }
        
        $this->view('warehouses/index', compact('warehouses', 'search'));
    }

    public function create(): void
    {
        $this->view('warehouses/form');
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/warehouses');
        }

        $data = $this->validate([
            'name' => 'required',
            'responsible_name' => 'required',
            'responsible_email' => 'email'
        ]);

        try {
            $this->warehouseModel->create($data);
            $this->setFlash('success', I18n::t('messages.created'));
            $this->redirect('/warehouses');
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/warehouses/create');
        }
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $warehouse = $this->warehouseModel->find($id);
        
        if (!$warehouse) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/warehouses');
        }

        $products = $this->warehouseModel->getProducts($id);
        $totalValue = $this->warehouseModel->getTotalValue($id);
        
        $this->view('warehouses/show', compact('warehouse', 'products', 'totalValue'));
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $warehouse = $this->warehouseModel->find($id);
        
        if (!$warehouse) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/warehouses');
        }
        
        $this->view('warehouses/form', compact('warehouse'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/warehouses');
        }

        $id = (int) $params['id'];
        $data = $this->validate([
            'name' => 'required',
            'responsible_name' => 'required',
            'responsible_email' => 'email'
        ]);

        try {
            $this->warehouseModel->update($id, $data);
            $this->setFlash('success', I18n::t('messages.updated'));
            $this->redirect('/warehouses/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/warehouses/' . $id . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/warehouses');
        }

        $id = (int) $params['id'];
        
        try {
            $this->warehouseModel->delete($id);
            $this->setFlash('success', I18n::t('messages.deleted'));
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/warehouses');
    }
}
