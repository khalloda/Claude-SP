<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Invoice;
use App\Models\Client;

class InvoiceController extends Controller
{
    private Invoice $invoiceModel;
    private Client $clientModel;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->clientModel = new Client();
    }

    public function index(): void
    {
        $search = Helpers::input('search', '');
        $status = Helpers::input('status', '');
        $page = (int) Helpers::input('page', 1);
        
        if (!empty($search)) {
            $invoices = $this->invoiceModel->search($search, $page);
        } elseif (!empty($status)) {
            $invoicesData = $this->invoiceModel->getInvoicesByStatus($status);
            // Convert to paginated format for consistency
            $invoices = [
                'data' => array_slice($invoicesData, ($page - 1) * 15, 15),
                'total' => count($invoicesData),
                'per_page' => 15,
                'current_page' => $page,
                'last_page' => (int) ceil(count($invoicesData) / 15),
                'from' => (($page - 1) * 15) + 1,
                'to' => min($page * 15, count($invoicesData))
            ];
        } else {
            $invoices = $this->invoiceModel->paginate($page);
        }
        
        // Get status summary for filter
        $statusSummary = $this->invoiceModel->getTotalsByStatus();
        
        $this->view('invoices/index', compact('invoices', 'search', 'status', 'statusSummary'));
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $invoice = $this->invoiceModel->getWithClient($id);
        
        if (!$invoice) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/invoices');
        }

        $items = $this->invoiceModel->getItems($id);
        $payments = $this->invoiceModel->getPayments($id);
        $balance = $this->invoiceModel->getBalance($id);
        
        $this->view('invoices/show', compact('invoice', 'items', 'payments', 'balance'));
    }

    public function addPayment(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/invoices');
        }

        $id = (int) $params['id'];
        
        $data = $this->validate([
            'amount' => 'required|numeric',
            'method' => 'required'
        ]);

        $amount = (float) $data['amount'];
        $method = $data['method'];
        $note = Helpers::input('note', '');

        if ($amount <= 0) {
            $this->setFlash('error', 'Payment amount must be greater than zero');
            $this->redirect('/invoices/' . $id);
        }

        try {
            $this->invoiceModel->addPayment($id, $amount, $method, $note);
            $this->setFlash('success', 'Payment added successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error adding payment: ' . $e->getMessage());
        }
        
        $this->redirect('/invoices/' . $id);
    }

    public function void(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/invoices');
        }

        $id = (int) $params['id'];
        
        try {
            $this->invoiceModel->updateStatus($id, 'void');
            $this->setFlash('success', 'Invoice voided successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/invoices/' . $id);
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/invoices');
        }

        $id = (int) $params['id'];
        $invoice = $this->invoiceModel->find($id);

        if ($invoice && in_array($invoice['status'], ['paid', 'partial'])) {
            $this->setFlash('error', 'Cannot delete invoice with payments');
            $this->redirect('/invoices');
        }
        
        try {
            $this->invoiceModel->delete($id);
            $this->setFlash('success', I18n::t('messages.deleted'));
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/invoices');
    }

    public function overdue(): void
    {
        $days = (int) Helpers::input('days', 30);
        $overdueInvoices = $this->invoiceModel->getOverdueInvoices($days);
        
        $this->view('invoices/overdue', compact('overdueInvoices', 'days'));
    }

    // AJAX endpoint to get client balance
    public function getClientBalance(): void
    {
        $clientId = (int) Helpers::input('client_id');
        
        if (!$clientId) {
            $this->json(['success' => false, 'message' => 'Client ID required']);
        }

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            $this->json(['success' => false, 'message' => 'Client not found']);
        }

        $balance = $this->clientModel->getBalance($clientId);
        
        $this->json([
            'success' => true,
            'data' => $balance
        ]);
    }
}
