<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\SalesOrder;
use App\Models\Client;

class SalesOrderController extends Controller
{
    private SalesOrder $salesOrderModel;
    private Client $clientModel;

    public function __construct()
    {
        $this->salesOrderModel = new SalesOrder();
        $this->clientModel = new Client();
    }

    public function index(): void
    {
        $search = Helpers::input('search', '');
        $page = (int) Helpers::input('page', 1);
        
        if (!empty($search)) {
            $salesOrders = $this->salesOrderModel->search($search, $page);
        } else {
            $salesOrders = $this->salesOrderModel->paginate($page);
        }
        
        $this->view('salesorders/index', compact('salesOrders', 'search'));
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $salesOrder = $this->salesOrderModel->getWithClient($id);
        
        if (!$salesOrder) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/salesorders');
        }

        $items = $this->salesOrderModel->getItems($id);
        
        $this->view('salesorders/show', compact('salesOrder', 'items'));
    }

    public function deliver(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/salesorders');
        }

        $id = (int) $params['id'];
        
        try {
            $this->salesOrderModel->updateStatus($id, 'delivered');
            $this->setFlash('success', 'Sales order marked as delivered');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error delivering order: ' . $e->getMessage());
        }
        
        $this->redirect('/salesorders/' . $id);
    }

    public function reject(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/salesorders');
        }

        $id = (int) $params['id'];
        
        try {
            $this->salesOrderModel->updateStatus($id, 'rejected');
            $this->setFlash('success', 'Sales order rejected');
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/salesorders/' . $id);
    }

    public function convertToInvoice(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/salesorders');
        }

        $id = (int) $params['id'];
        
        try {
            $invoiceId = $this->salesOrderModel->convertToInvoice($id);
            $this->setFlash('success', 'Sales order converted to invoice successfully');
            $this->redirect('/invoices/' . $invoiceId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error converting to invoice: ' . $e->getMessage());
            $this->redirect('/salesorders/' . $id);
        }
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/salesorders');
        }

        $id = (int) $params['id'];
        $salesOrder = $this->salesOrderModel->find($id);

        if ($salesOrder && $salesOrder['status'] === 'delivered') {
            $this->setFlash('error', 'Cannot delete delivered sales order');
            $this->redirect('/salesorders');
        }
        
        try {
            $this->salesOrderModel->delete($id);
            $this->setFlash('success', I18n::t('messages.deleted'));
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/salesorders');
    }
}
