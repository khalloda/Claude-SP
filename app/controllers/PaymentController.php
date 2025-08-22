<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Client;

class PaymentController extends Controller
{
    private Payment $paymentModel;
    private Invoice $invoiceModel;
    private Client $clientModel;

    public function __construct()
    {
        $this->paymentModel = new Payment();
        $this->invoiceModel = new Invoice();
        $this->clientModel = new Client();
    }

    public function index(): void
    {
        $search = Helpers::input('search', '');
        $clientId = Helpers::input('client_id', '');
        $method = Helpers::input('method', '');
        $page = (int) Helpers::input('page', 1);
        
        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        if (!empty($clientId)) {
            $filters['client_id'] = (int) $clientId;
        }
        if (!empty($method)) {
            $filters['method'] = $method;
        }
        
        if (!empty($filters)) {
            $payments = $this->paymentModel->searchWithFilters($filters, $page);
        } else {
            $payments = $this->paymentModel->paginate($page);
        }
        
        // Get clients for filter dropdown
        $clients = $this->clientModel->all();
        
        // Get payment methods for filter
        $paymentMethods = $this->paymentModel->getPaymentMethods();
        
        // Get payment summary statistics
        $summary = $this->paymentModel->getPaymentSummary();
        
        $this->view('payments/index', compact('payments', 'search', 'clientId', 'method', 'clients', 'paymentMethods', 'summary'));
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $payment = $this->paymentModel->getWithDetails($id);
        
        if (!$payment) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/payments');
        }
        
        // Get related payments for the same invoice
        $relatedPayments = $this->paymentModel->getByInvoice($payment['invoice_id']);
        
        // Get client payment history
        $clientPayments = $this->paymentModel->getByClient($payment['client_id'], 5); // Last 5 payments
        
        $this->view('payments/show', compact('payment', 'relatedPayments', 'clientPayments'));
    }

    public function create(): void
    {
        $invoiceId = (int) Helpers::input('invoice_id', 0);
        $invoice = null;
        
        if ($invoiceId > 0) {
            $invoice = $this->invoiceModel->getWithClient($invoiceId);
            if (!$invoice) {
                $this->setFlash('error', 'Invoice not found');
                $this->redirect('/payments');
            }
            
            // Check if invoice can receive payments
            if (in_array($invoice['status'], ['void', 'paid'])) {
                $this->setFlash('error', 'Cannot add payment to this invoice');
                $this->redirect('/invoices/' . $invoiceId);
            }
        }
        
        $clients = $this->clientModel->all();
        $openInvoices = $this->invoiceModel->getOpenInvoices();
        
        $this->view('payments/form', compact('invoice', 'clients', 'openInvoices'));
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/payments');
        }

        $data = $this->validate([
            'invoice_id' => 'required|numeric',
            'amount' => 'required|numeric',
            'method' => 'required'
        ]);

        $invoiceId = (int) $data['invoice_id'];
        $amount = (float) $data['amount'];
        $method = $data['method'];
        $note = Helpers::input('note', '');

        // Validate invoice exists and can receive payments
        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('/payments/create');
        }

        if (in_array($invoice['status'], ['void', 'paid'])) {
            $this->setFlash('error', 'Cannot add payment to this invoice');
            $this->redirect('/payments/create');
        }

        // Validate amount
        if ($amount <= 0) {
            $this->setFlash('error', 'Payment amount must be greater than zero');
            $this->redirect('/payments/create');
        }

        $remainingBalance = $invoice['grand_total'] - $invoice['paid_total'];
        if ($amount > $remainingBalance) {
            $this->setFlash('error', 'Payment amount cannot exceed remaining balance');
            $this->redirect('/payments/create');
        }

        try {
            $paymentId = $this->invoiceModel->addPayment($invoiceId, $amount, $method, $note);
            $this->setFlash('success', 'Payment recorded successfully');
            $this->redirect('/payments/' . $paymentId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error recording payment: ' . $e->getMessage());
            $this->redirect('/payments/create');
        }
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $payment = $this->paymentModel->getWithDetails($id);
        
        if (!$payment) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/payments');
        }

        // Only allow editing recent payments (within last 24 hours) for data integrity
        $paymentTime = strtotime($payment['created_at']);
        $twentyFourHoursAgo = time() - (24 * 60 * 60);
        
        if ($paymentTime < $twentyFourHoursAgo) {
            $this->setFlash('error', 'Cannot edit payments older than 24 hours');
            $this->redirect('/payments/' . $id);
        }

        $clients = $this->clientModel->all();
        $openInvoices = $this->invoiceModel->getOpenInvoices();
        
        $this->view('payments/form', compact('payment', 'clients', 'openInvoices'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/payments');
        }

        $id = (int) $params['id'];
        $payment = $this->paymentModel->find($id);

        if (!$payment) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/payments');
        }

        // Check if payment can be edited (within 24 hours)
        $paymentTime = strtotime($payment['created_at']);
        $twentyFourHoursAgo = time() - (24 * 60 * 60);
        
        if ($paymentTime < $twentyFourHoursAgo) {
            $this->setFlash('error', 'Cannot edit payments older than 24 hours');
            $this->redirect('/payments/' . $id);
        }

        $data = $this->validate([
            'method' => 'required'
        ]);

        // Only allow updating method and note for data integrity
        $updateData = [
            'method' => $data['method'],
            'note' => Helpers::input('note', '')
        ];

        try {
            $this->paymentModel->update($id, $updateData);
            $this->setFlash('success', I18n::t('messages.updated'));
            $this->redirect('/payments/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/payments/' . $id . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/payments');
        }

        $id = (int) $params['id'];
        $payment = $this->paymentModel->getWithDetails($id);

        if (!$payment) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/payments');
        }

        // Only allow deleting recent payments (within last 1 hour) for data integrity
        $paymentTime = strtotime($payment['created_at']);
        $oneHourAgo = time() - (60 * 60);
        
        if ($paymentTime < $oneHourAgo) {
            $this->setFlash('error', 'Cannot delete payments older than 1 hour. Please contact system administrator for payment reversals.');
            $this->redirect('/payments/' . $id);
        }

        try {
            // This will also update the invoice totals and status
            $this->paymentModel->reversePayment($id);
            $this->setFlash('success', 'Payment reversed successfully');
            $this->redirect('/payments');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error reversing payment: ' . $e->getMessage());
            $this->redirect('/payments/' . $id);
        }
    }

    // AJAX endpoint to get client's open invoices
    public function getClientInvoices(): void
    {
        $clientId = (int) Helpers::input('client_id');
        
        if (!$clientId) {
            $this->json(['success' => false, 'message' => 'Client ID required']);
        }

        $invoices = $this->invoiceModel->getClientOpenInvoices($clientId);
        
        $this->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    // AJAX endpoint to get invoice details
    public function getInvoiceDetails(): void
    {
        $invoiceId = (int) Helpers::input('invoice_id');
        
        if (!$invoiceId) {
            $this->json(['success' => false, 'message' => 'Invoice ID required']);
        }

        $invoice = $this->invoiceModel->getWithClient($invoiceId);
        if (!$invoice) {
            $this->json(['success' => false, 'message' => 'Invoice not found']);
        }

        $balance = $invoice['grand_total'] - $invoice['paid_total'];

        $this->json([
            'success' => true,
            'data' => [
                'id' => $invoice['id'],
                'client_name' => $invoice['client_name'],
                'grand_total' => (float) $invoice['grand_total'],
                'paid_total' => (float) $invoice['paid_total'],
                'balance' => (float) $balance,
                'status' => $invoice['status']
            ]
        ]);
    }

    // Export payments to CSV
    public function export(): void
    {
        $search = Helpers::input('search', '');
        $clientId = Helpers::input('client_id', '');
        $method = Helpers::input('method', '');
        $dateFrom = Helpers::input('date_from', '');
        $dateTo = Helpers::input('date_to', '');
        
        $filters = [];
        if (!empty($search)) $filters['search'] = $search;
        if (!empty($clientId)) $filters['client_id'] = (int) $clientId;
        if (!empty($method)) $filters['method'] = $method;
        if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
        if (!empty($dateTo)) $filters['date_to'] = $dateTo;
        
        $payments = $this->paymentModel->exportData($filters);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="payments_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'Payment ID',
            'Date',
            'Client',
            'Invoice #',
            'Amount',
            'Method',
            'Note'
        ]);
        
        // CSV data
        foreach ($payments as $payment) {
            fputcsv($output, [
                $payment['id'],
                $payment['created_at'],
                $payment['client_name'],
                '#' . str_pad($payment['invoice_id'], 4, '0', STR_PAD_LEFT),
                $payment['amount'],
                $payment['method'],
                $payment['note'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
}
