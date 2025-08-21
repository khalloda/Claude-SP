<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Supplier;

class SupplierController extends Controller
{
    private Supplier $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new Supplier();
    }

    public function index(): void
    {
        $search = Helpers::input('search', '');
        $page = (int) Helpers::input('page', 1);
        
        if (!empty($search)) {
            $suppliers = $this->supplierModel->search($search, $page);
        } else {
            $suppliers = $this->supplierModel->paginate($page);
        }
        
        $this->view('suppliers/index', compact('suppliers', 'search'));
    }

    public function create(): void
    {
        $this->view('suppliers/form');
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/suppliers');
        }

        $data = $this->validate([
            'type' => 'required',
            'name' => 'required',
            'email' => 'email'
        ]);

        // Add the missing fields explicitly
        $data['phone'] = Helpers::input('phone', '');
        $data['address'] = Helpers::input('address', '');

        try {
            $this->supplierModel->create($data);
            $this->setFlash('success', I18n::t('messages.created'));
            $this->redirect('/suppliers');
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/suppliers/create');
        }
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $supplier = $this->supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/suppliers');
        }
        
        $this->view('suppliers/show', compact('supplier'));
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $supplier = $this->supplierModel->find($id);
        
        if (!$supplier) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/suppliers');
        }
        
        $this->view('suppliers/form', compact('supplier'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/suppliers');
        }

        $id = (int) $params['id'];
        $data = $this->validate([
            'type' => 'required',
            'name' => 'required',
            'email' => 'email'
        ]);

        // Add the missing fields explicitly
        $data['phone'] = Helpers::input('phone', '');
        $data['address'] = Helpers::input('address', '');

        try {
            $this->supplierModel->update($id, $data);
            $this->setFlash('success', I18n::t('messages.updated'));
            $this->redirect('/suppliers/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/suppliers/' . $id . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/suppliers');
        }

        $id = (int) $params['id'];
        
        try {
            $this->supplierModel->delete($id);
            $this->setFlash('success', I18n::t('messages.deleted'));
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/suppliers');
    }
}
