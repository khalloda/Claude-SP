<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Dropdown;

class DropdownController extends Controller
{
    private Dropdown $dropdownModel;

    public function __construct()
    {
        $this->dropdownModel = new Dropdown();
    }

    public function index(): void
    {
        $category = Helpers::input('category', 'classification');
        $dropdowns = $this->dropdownModel->getAllByCategory();
        $categories = $this->dropdownModel->getCategories();
        
        $this->view('dropdowns/index', compact('dropdowns', 'categories', 'category'));
    }

    public function create(): void
    {
        $categories = $this->dropdownModel->getCategories();
        $parentCategory = Helpers::input('parent_category');
        $parents = [];
        
        if ($parentCategory) {
            $parents = $this->dropdownModel->getByCategory($parentCategory);
        }
        
        $this->view('dropdowns/form', compact('categories', 'parents', 'parentCategory'));
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/dropdowns');
        }

        $data = $this->validate([
            'category' => 'required',
            'value' => 'required'
        ]);

        // Handle parent_id
        if (!empty(Helpers::input('parent_id'))) {
            $data['parent_id'] = (int) Helpers::input('parent_id');
        }

        try {
            $this->dropdownModel->create($data);
            $this->setFlash('success', I18n::t('messages.created'));
            $this->redirect('/dropdowns?category=' . $data['category']);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/dropdowns/create');
        }
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $dropdown = $this->dropdownModel->find($id);
        
        if (!$dropdown) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/dropdowns');
        }
        
        $categories = $this->dropdownModel->getCategories();
        $parents = [];
        
        if ($dropdown['category'] === 'car_model') {
            $parents = $this->dropdownModel->getByCategory('car_make');
        }
        
        $this->view('dropdowns/form', compact('dropdown', 'categories', 'parents'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/dropdowns');
        }

        $id = (int) $params['id'];
        $data = $this->validate([
            'category' => 'required',
            'value' => 'required'
        ]);

        // Handle parent_id
        if (!empty(Helpers::input('parent_id'))) {
            $data['parent_id'] = (int) Helpers::input('parent_id');
        } else {
            $data['parent_id'] = null;
        }

        try {
            $this->dropdownModel->update($id, $data);
            $this->setFlash('success', I18n::t('messages.updated'));
            $this->redirect('/dropdowns?category=' . $data['category']);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/dropdowns/' . $id . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/dropdowns');
        }

        $id = (int) $params['id'];
        
        try {
            $this->dropdownModel->deleteWithChildren($id);
            $this->setFlash('success', I18n::t('messages.deleted'));
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/dropdowns');
    }

    // AJAX endpoint for dependent dropdowns
    public function getByParent(): void
    {
        $parentId = (int) Helpers::input('parent_id');
        $category = Helpers::input('category');
        
        if ($parentId && $category) {
            $items = $this->dropdownModel->getByCategory($category, $parentId);
            $this->json(['success' => true, 'data' => $items]);
        } else {
            $this->json(['success' => false, 'message' => 'Invalid parameters']);
        }
    }
}
```clientModel = new Client();
    }

    public function index(): void
    {
        $search = Helpers::input('search', '');
        $page = (int) Helpers::input('page', 1);
        
        if (!empty($search)) {
            $clients = $this->clientModel->search($search, $page);
        } else {
            $clients = $this->clientModel->paginate($page);
        }
        
        $this->view('clients/index', compact('clients', 'search'));
    }

    public function create(): void
    {
        $this->view('clients/form');
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/clients');
        }

        $data = $this->validate([
            'type' => 'required',
            'name' => 'required',
            'email' => 'email'
        ]);

        try {
            $this->clientModel->create($data);
            $this->setFlash('success', I18n::t('messages.created'));
            $this->redirect('/clients');
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/clients/create');
        }
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/clients');
        }

        // Get related data for tabs
        $quotes = $this->clientModel->getQuotes($id);
        $salesOrders = $this->clientModel->getSalesOrders($id);
        $invoices = $this->clientModel->getInvoices($id);
        $payments = $this->clientModel->getPayments($id);
        $balance = $this->clientModel->getBalance($id);
        
        $this->view('clients/show', compact('client', 'quotes', 'salesOrders', 'invoices', 'payments', 'balance'));
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/clients');
        }
        
        $this->view('clients/form', compact('client'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/clients');
        }

        $id = (int) $params['id'];
        $data = $this->validate([
            'type' => 'required',
            'name' => 'required',
            'email' => 'email'
        ]);

        try {
            $this->clientModel->update($id, $data);
            $this->setFlash('success', I18n::t('messages.updated'));
            $this->redirect('/clients/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/clients/' . $id . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/clients');
        }

        $id = (int) $params['id'];
        
        try {
            $this->clientModel->delete($id);
            $this->setFlash('success', I18n::t('messages.deleted'));
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/clients');
    }
}
