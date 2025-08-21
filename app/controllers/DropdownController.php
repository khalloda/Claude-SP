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
        $category = Helpers::input('category', '');
        $dropdowns = $this->dropdownModel->getAllByCategory();
        $categories = $this->dropdownModel->getCategories();
        
        $this->view('dropdowns/index', compact('dropdowns', 'categories', 'category'));
    }

    public function create(): void
    {
        $categories = $this->dropdownModel->getCategories();
        $parentCategory = Helpers::input('parent_category', '');
        $parents = [];
        
        if ($parentCategory === 'car_model') {
            $parents = $this->dropdownModel->getByCategory('car_make');
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
        $parentId = Helpers::input('parent_id');
        if (!empty($parentId)) {
            $data['parent_id'] = (int) $parentId;
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

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $dropdown = $this->dropdownModel->find($id);
        
        if (!$dropdown) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/dropdowns');
        }
        
        // Simple show view
        $this->view('dropdowns/show', compact('dropdown'));
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
        $parentId = Helpers::input('parent_id');
        if (!empty($parentId)) {
            $data['parent_id'] = (int) $parentId;
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
        $parentId = Helpers::input('parent_id');
        $category = Helpers::input('category');
        
        // Debug logging
        error_log("getByParent called with parent_id: $parentId, category: $category");
        
        try {
            if ($category === 'car_make') {
                // Return all car makes (no parent needed)
                $items = $this->dropdownModel->getByCategory('car_make');
                error_log("Returning car makes: " . count($items) . " items");
                $this->json(['success' => true, 'data' => $items]);
            } elseif ($category === 'car_model' && $parentId) {
                // Return car models for specific make
                $items = $this->dropdownModel->getByCategory('car_model', (int)$parentId);
                error_log("Returning car models for make $parentId: " . count($items) . " items");
                $this->json(['success' => true, 'data' => $items]);
            } else {
                error_log("Invalid parameters: category=$category, parent_id=$parentId");
                $this->json(['success' => false, 'message' => 'Invalid parameters']);
            }
        } catch (\Exception $e) {
            error_log("Error in getByParent: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
