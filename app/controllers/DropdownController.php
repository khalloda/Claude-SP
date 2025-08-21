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
