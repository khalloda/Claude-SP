<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Quote;
use App\Models\Client;
use App\Models\Product;

class QuoteController extends Controller
{
    private Quote $quoteModel;
    private Client $clientModel;
    private Product $productModel;

    public function __construct()
    {
        $this->quoteModel = new Quote();
        $this->clientModel = new Client();
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $search = Helpers::input('search', '');
        $page = (int) Helpers::input('page', 1);
        
        if (!empty($search)) {
            $quotes = $this->quoteModel->search($search, $page);
        } else {
            $quotes = $this->quoteModel->paginate($page);
        }
        
        $this->view('quotes/index', compact('quotes', 'search'));
    }

    public function create(): void
    {
        $clients = $this->clientModel->all();
        $products = $this->productModel->all();
        
        $this->view('quotes/form', compact('clients', 'products'));
    }

public function store(): void
{
    if (!Helpers::verifyCsrf()) {
        $this->setFlash('error', I18n::t('messages.error'));
        $this->redirect('/quotes');
    }

    $data = $this->validate([
        'client_id' => 'required|numeric',
        'currency_code' => 'required|min:3|max:3' // NEW: Currency validation
    ]);

    // Add optional fields
    $data['status'] = 'sent';
    $data['notes'] = Helpers::input('notes', '');
    $data['currency_code'] = strtoupper($data['currency_code']); // NEW: Ensure uppercase
    $data['exchange_rate'] = (float) Helpers::input('exchange_rate', 1.0); // NEW: Store exchange rate
    $data['global_tax_type'] = Helpers::input('global_tax_type', 'percent');
    $data['global_tax_value'] = (float) Helpers::input('global_tax_value', 0);
    $data['global_discount_type'] = Helpers::input('global_discount_type', 'percent');
    $data['global_discount_value'] = (float) Helpers::input('global_discount_value', 0);

    // NEW: Validate currency
    $currencyModel = new \App\Models\Currency();
    if (!$currencyModel->isValidCurrency($data['currency_code'])) {
        $this->setFlash('error', 'Invalid currency selected');
        $this->redirect('/quotes/create');
    }

    // Process quote items
    $items = $this->processQuoteItems($data['currency_code']); // NEW: Pass currency to items

    if (empty($items)) {
        $this->setFlash('error', 'At least one item is required');
        $this->redirect('/quotes/create');
    }

    try {
        $quoteId = $this->quoteModel->createWithItems($data, $items);
        $this->setFlash('success', I18n::t('messages.created'));
        $this->redirect('/quotes/' . $quoteId);
    } catch (\Exception $e) {
        $this->setFlash('error', I18n::t('messages.error') . ': ' . $e->getMessage());
        $this->redirect('/quotes/create');
    }
}

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $quote = $this->quoteModel->getWithClient($id);
        
        if (!$quote) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/quotes');
        }

        $items = $this->quoteModel->getItems($id);
        
        $this->view('quotes/show', compact('quote', 'items'));
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $quote = $this->quoteModel->getWithClient($id);
        
        if (!$quote) {
            $this->setFlash('error', I18n::t('messages.not_found'));
            $this->redirect('/quotes');
        }

        if ($quote['status'] === 'approved') {
            $this->setFlash('error', 'Cannot edit approved quote');
            $this->redirect('/quotes/' . $id);
        }

        $clients = $this->clientModel->all();
        $products = $this->productModel->all();
        $items = $this->quoteModel->getItems($id);
        
        $this->view('quotes/form', compact('quote', 'clients', 'products', 'items'));
    }

public function update(array $params): void
{
    if (!Helpers::verifyCsrf()) {
        $this->setFlash('error', I18n::t('messages.error'));
        $this->redirect('/quotes');
    }

    $id = (int) $params['id'];
    $quote = $this->quoteModel->find($id);

    if (!$quote || $quote['status'] === 'approved') {
        $this->setFlash('error', 'Cannot update this quote');
        $this->redirect('/quotes');
    }

    $data = $this->validate([
        'client_id' => 'required|numeric',
        'currency_code' => 'required|min:3|max:3' // NEW: Currency validation
    ]);

    // Add optional fields
    $data['notes'] = Helpers::input('notes', '');
    $data['currency_code'] = strtoupper($data['currency_code']); // NEW: Ensure uppercase
    $data['exchange_rate'] = (float) Helpers::input('exchange_rate', 1.0); // NEW: Store exchange rate
    $data['global_tax_type'] = Helpers::input('global_tax_type', 'percent');
    $data['global_tax_value'] = (float) Helpers::input('global_tax_value', 0);
    $data['global_discount_type'] = Helpers::input('global_discount_type', 'percent');
    $data['global_discount_value'] = (float) Helpers::input('global_discount_value', 0);

    // NEW: Validate currency
    $currencyModel = new \App\Models\Currency();
    if (!$currencyModel->isValidCurrency($data['currency_code'])) {
        $this->setFlash('error', 'Invalid currency selected');
        $this->redirect('/quotes/' . $id . '/edit');
    }

    // Process quote items
    $items = $this->processQuoteItems($data['currency_code']); // NEW: Pass currency to items

    if (empty($items)) {
        $this->setFlash('error', 'At least one item is required');
        $this->redirect('/quotes/' . $id . '/edit');
    }

    try {
        $this->quoteModel->updateWithItems($id, $data, $items);
        $this->setFlash('success', I18n::t('messages.updated'));
        $this->redirect('/quotes/' . $id);
    } catch (\Exception $e) {
        $this->setFlash('error', I18n::t('messages.error') . ': ' . $e->getMessage());
        $this->redirect('/quotes/' . $id . '/edit');
    }
}

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/quotes');
        }

        $id = (int) $params['id'];
        $quote = $this->quoteModel->find($id);

        if ($quote && $quote['status'] === 'approved') {
            $this->setFlash('error', 'Cannot delete approved quote');
            $this->redirect('/quotes');
        }
        
        try {
            $this->quoteModel->delete($id);
            $this->setFlash('success', I18n::t('messages.deleted'));
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/quotes');
    }

    public function approve(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/quotes');
        }

        $id = (int) $params['id'];
        
        try {
            $this->quoteModel->updateStatus($id, 'approved');
            $this->setFlash('success', 'Quote approved successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/quotes/' . $id);
    }

    public function reject(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/quotes');
        }

        $id = (int) $params['id'];
        
        try {
            $this->quoteModel->updateStatus($id, 'rejected');
            $this->setFlash('success', 'Quote rejected');
        } catch (\Exception $e) {
            $this->setFlash('error', I18n::t('messages.error'));
        }
        
        $this->redirect('/quotes/' . $id);
    }

    public function convertToOrder(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/quotes');
        }

        $id = (int) $params['id'];
        
        try {
            $salesOrderId = $this->quoteModel->convertToSalesOrder($id);
            $this->setFlash('success', 'Quote converted to sales order successfully');
            $this->redirect('/salesorders/' . $salesOrderId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error converting quote: ' . $e->getMessage());
            $this->redirect('/quotes/' . $id);
        }
    }

private function processQuoteItems(string $currency = 'EGP'): array
{
    $productIds = Helpers::input('product_ids', []);
    $quantities = Helpers::input('quantities', []);
    $prices = Helpers::input('prices', []);
    $taxes = Helpers::input('taxes', []);
    $taxTypes = Helpers::input('tax_types', []);
    $discounts = Helpers::input('discounts', []);
    $discountTypes = Helpers::input('discount_types', []);

    $items = [];

    if (!is_array($productIds)) {
        return $items;
    }

    for ($i = 0; $i < count($productIds); $i++) {
        if (empty($productIds[$i]) || !isset($quantities[$i]) || !isset($prices[$i])) {
            continue;
        }

        $qty = (float) $quantities[$i];
        $price = (float) $prices[$i];

        if ($qty <= 0 || $price < 0) {
            continue;
        }

        $items[] = [
            'product_id' => (int) $productIds[$i],
            'currency_code' => $currency, // NEW: Include currency in items
            'qty' => $qty,
            'price' => $price,
            'original_price' => $price, // NEW: Store original price for conversion tracking
            'exchange_rate' => (float) Helpers::input('exchange_rate', 1.0), // NEW: Exchange rate per item
            'tax' => (float) ($taxes[$i] ?? 0),
            'tax_type' => in_array($taxTypes[$i] ?? 'percent', ['percent', 'amount']) ? $taxTypes[$i] : 'percent',
            'discount' => (float) ($discounts[$i] ?? 0),
            'discount_type' => in_array($discountTypes[$i] ?? 'percent', ['percent', 'amount']) ? $discountTypes[$i] : 'percent'
        ];
    }

    return $items;
}

    // AJAX endpoint to get product details
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
                'sale_price' => $product['sale_price'],
                'available_qty' => $product['total_qty'] - $product['reserved_quotes'] - $product['reserved_orders']
            ]
        ]);
    }
}
