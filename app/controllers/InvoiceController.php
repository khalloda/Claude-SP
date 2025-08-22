<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Product;

class InvoiceController extends Controller
{
    private Invoice $invoiceModel;
    private Client $clientModel;
    private Product $productModel;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->clientModel = new Client();
        $this->productModel = new Product();
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

    public function create(): void
    {
        $clients = $this->clientModel->all();
        $products = $this->productModel->all();
        
        $this->view('invoices/form', compact('clients', 'products'));
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/invoices');
        }

        $data = $this->validate([
            'client_id' => 'required|numeric'
        ]);

        // Add optional fields
        $data['status'] = 'open';
        $data['paid_total'] = 0;
        $data['notes'] = Helpers::input('notes', '');
        $data['global_tax_type'] = Helpers::input('global_tax_type', 'percent');
        $data['global_tax_value'] = (float) Helpers::input('global_tax_value', 0);
        $data['global_discount_type'] = Helpers::input('global_discount_type', 'percent');
        $data['global_discount_value'] = (float) Helpers::input('global_discount_value', 0);

        // Process invoice items
        $items = $this->processInvoiceItems();

        if (empty($items)) {
            $this->setFlash('error', 'At least one item is required');
            $this->redirect('/invoices/create');
        }

        try {
            $invoiceId = $this->invoiceModel->createWithItems($data, $items);
            $this->setFlash('success', I18n::t('messages.created'));
            $this->redirect('/invoices/' . $invoiceId);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error') . ': ' . $e->getMessage());
            $this->redirect('/invoices/create');
        }
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

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $invoice = $this->invoiceModel->getWithClient($id);
        
        if (!$invoice) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/invoices');
        }

        // Prevent editing paid or partially paid invoices
        if (in_array($invoice['status'], ['paid', 'partial'])) {
            $this->setFlash('error', 'Cannot edit invoice with payments');
            $this->redirect('/invoices/' . $id);
        }

        $clients = $this->clientModel->all();
        $products = $this->productModel->all();
        $items = $this->invoiceModel->getItems($id);
        
        $this->view('invoices/form', compact('invoice', 'clients', 'products', 'items'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/invoices');
        }

        $id = (int) $params['id'];
        $invoice = $this->invoiceModel->find($id);

        if (!$invoice) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/invoices');
        }

        // Prevent editing paid or partially paid invoices
        if (in_array($invoice['status'], ['paid', 'partial'])) {
            $this->setFlash('error', 'Cannot edit invoice with payments');
            $this->redirect('/invoices');
        }

        $data = $this->validate([
            'client_id' => 'required|numeric'
        ]);

        // Add optional fields
        $data['notes'] = Helpers::input('notes', '');
        $data['global_tax_type'] = Helpers::input('global_tax_type', 'percent');
        $data['global_tax_value'] = (float) Helpers::input('global_tax_value', 0);
        $data['global_discount_type'] = Helpers::input('global_discount_type', 'percent');
        $data['global_discount_value'] = (float) Helpers::input('global_discount_value', 0);

        // Process invoice items
        $items = $this->processInvoiceItems();

        if (empty($items)) {
            $this->setFlash('error', 'At least one item is required');
            $this->redirect('/invoices/' . $id . '/edit');
        }

        try {
            $this->invoiceModel->updateWithItems($id, $data, $items);
            $this->setFlash('success', I18n::t('messages.updated'));
            $this->redirect('/invoices/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error') . ': ' . $e->getMessage());
            $this->redirect('/invoices/' . $id . '/edit');
        }
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

    private function processInvoiceItems(): array
    {
        $items = [];
        $productIds = Helpers::input('product_id', []);
        $quantities = Helpers::input('qty', []);
        $prices = Helpers::input('price', []);
        $taxes = Helpers::input('tax', []);
        $taxTypes = Helpers::input('tax_type', []);
        $discounts = Helpers::input('discount', []);
        $discountTypes = Helpers::input('discount_type', []);

        if (!is_array($productIds)) {
            return [];
        }

        foreach ($productIds as $index => $productId) {
            $productId = (int) $productId;
            $qty = (float) ($quantities[$index] ?? 0);
            $price = (float) ($prices[$index] ?? 0);
            $tax = (float) ($taxes[$index] ?? 0);
            $taxType = $taxTypes[$index] ?? 'percent';
            $discount = (float) ($discounts[$index] ?? 0);
            $discountType = $discountTypes[$index] ?? 'percent';

            if ($productId > 0 && $qty > 0 && $price > 0) {
                $items[] = [
                    'product_id' => $productId,
                    'qty' => $qty,
                    'price' => $price,
                    'tax' => $tax,
                    'tax_type' => $taxType,
                    'discount' => $discount,
                    'discount_type' => $discountType
                ];
            }
        }

        return $items;
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

    // AJAX endpoint to get product details for invoice line items
    public function getProductDetails(): void
    {
        $productId = (int) Helpers::input('product_id');
        
        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Product ID required']);
        }

        $product = $this->productModel->find($productId);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found']);
        }

        $this->json([
            'success' => true,
            'data' => [
                'id' => $product['id'],
                'code' => $product['code'],
                'name' => $product['name'],
                'sale_price' => (float) $product['sale_price'],
                'available_qty' => (float) $product['total_qty']
            ]
        ]);
    }
}
