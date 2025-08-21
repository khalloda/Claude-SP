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
        $this->clientModel = new Client();
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

        // Add the missing fields explicitly
        $data['phone'] = Helpers::input('phone', '');
        $data['address'] = Helpers::input('address', '');

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

        // Add the missing fields explicitly
        $data['phone'] = Helpers::input('phone', '');
        $data['address'] = Helpers::input('address', '');

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
