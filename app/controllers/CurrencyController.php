<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\I18n;
use App\Models\Currency;

class CurrencyController extends Controller
{
    private Currency $currencyModel;

    public function __construct()
    {
        $this->currencyModel = new Currency();
    }

    public function index(): void
    {
        $page = (int) Helpers::input('page', 1);
        $currencies = $this->currencyModel->paginate($page);
        $stats = $this->currencyModel->getCurrencyStats();
        
        $this->view('currencies/index', compact('currencies', 'stats'));
    }

    public function create(): void
    {
        $this->view('currencies/form');
    }

    public function store(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/currencies');
        }

        $data = $this->validate([
            'code' => 'required|min:3|max:3',
            'name' => 'required|max:50',
            'symbol' => 'required|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001'
        ]);

        // Additional validation
        $data['decimal_places'] = (int) Helpers::input('decimal_places', 2);
        $data['is_active'] = Helpers::input('is_active') === '1' ? 1 : 0;
        $data['is_primary'] = Helpers::input('is_primary') === '1' ? 1 : 0;

        // Validate decimal places
        if ($data['decimal_places'] < 0 || $data['decimal_places'] > 8) {
            $this->setFlash('error', 'Decimal places must be between 0 and 8');
            $this->redirect('/currencies/create');
        }

        try {
            $this->currencyModel->createCurrency($data);
            $this->setFlash('success', 'Currency created successfully');
            $this->redirect('/currencies');
        } catch (\InvalidArgumentException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/currencies/create');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error creating currency: ' . $e->getMessage());
            $this->redirect('/currencies/create');
        }
    }

    public function show(array $params): void
    {
        $code = strtoupper($params['code']);
        $currency = $this->currencyModel->getByCode($code);
        
        if (!$currency) {
            $this->setFlash('error', 'Currency not found');
            $this->redirect('/currencies');
        }

        // Get exchange rate history
        $history = $this->currencyModel->getExchangeRateHistory($code, 20);
        
        $this->view('currencies/show', compact('currency', 'history'));
    }

    public function edit(array $params): void
    {
        $code = strtoupper($params['code']);
        $currency = $this->currencyModel->getByCode($code);
        
        if (!$currency) {
            $this->setFlash('error', 'Currency not found');
            $this->redirect('/currencies');
        }

        $this->view('currencies/form', compact('currency'));
    }

    public function update(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/currencies');
        }

        $code = strtoupper($params['code']);
        $currency = $this->currencyModel->getByCode($code);
        
        if (!$currency) {
            $this->setFlash('error', 'Currency not found');
            $this->redirect('/currencies');
        }

        $data = $this->validate([
            'name' => 'required|max:50',
            'symbol' => 'required|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001'
        ]);

        // Additional validation
        $data['decimal_places'] = (int) Helpers::input('decimal_places', 2);
        $data['is_active'] = Helpers::input('is_active') === '1' ? 1 : 0;
        $data['is_primary'] = Helpers::input('is_primary') === '1' ? 1 : 0;

        // Validate decimal places
        if ($data['decimal_places'] < 0 || $data['decimal_places'] > 8) {
            $this->setFlash('error', 'Decimal places must be between 0 and 8');
            $this->redirect('/currencies/' . $code . '/edit');
        }

        try {
            // Get current user ID for history tracking
            $userId = $_SESSION['user_id'] ?? null;

            // Handle primary currency change
            if ($data['is_primary'] == 1 && !$currency['is_primary']) {
                $this->currencyModel->setPrimary($code);
            }

            // Handle exchange rate change
            $newRate = (float) $data['exchange_rate'];
            $currentRate = (float) $currency['exchange_rate'];
            
            if (abs($newRate - $currentRate) > 0.000001) {
                $this->currencyModel->updateExchangeRate($code, $newRate, $userId);
            }

            // Update other fields
            $updateData = [
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'decimal_places' => $data['decimal_places'],
                'is_active' => $data['is_active']
            ];

            $this->currencyModel->updateByCode($code, $updateData);

            $this->setFlash('success', 'Currency updated successfully');
            $this->redirect('/currencies/' . $code);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error updating currency: ' . $e->getMessage());
            $this->redirect('/currencies/' . $code . '/edit');
        }
    }

    public function destroy(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/currencies');
        }

        $code = strtoupper($params['code']);
        $currency = $this->currencyModel->getByCode($code);
        
        if (!$currency) {
            $this->setFlash('error', 'Currency not found');
            $this->redirect('/currencies');
        }

        // Prevent deletion of primary currency
        if ($currency['is_primary']) {
            $this->setFlash('error', 'Cannot delete primary currency');
            $this->redirect('/currencies');
        }

        try {
            // Check if currency is being used
            $usageCount = $this->checkCurrencyUsage($code);
            
            if ($usageCount > 0) {
                $this->setFlash('error', "Cannot delete currency. It is being used in {$usageCount} transactions.");
                $this->redirect('/currencies');
            }

            $this->currencyModel->delete($currency['id']);
            $this->setFlash('success', 'Currency deleted successfully');
            $this->redirect('/currencies');
            
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error deleting currency: ' . $e->getMessage());
            $this->redirect('/currencies');
        }
    }

    public function setPrimary(array $params): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/currencies');
        }

        $code = strtoupper($params['code']);

        try {
            $this->currencyModel->setPrimary($code);
            $this->setFlash('success', "Currency {$code} set as primary successfully");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error setting primary currency: ' . $e->getMessage());
        }

        $this->redirect('/currencies');
    }

    public function updateRates(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->setFlash('error', I18n::t('messages.error'));
            $this->redirect('/currencies');
        }

        $rates = Helpers::input('rates', []);
        $userId = $_SESSION['user_id'] ?? null;
        $updated = 0;
        $errors = [];

        foreach ($rates as $code => $newRate) {
            if (empty($newRate) || !is_numeric($newRate)) {
                continue;
            }

            $newRate = (float) $newRate;
            if ($newRate <= 0) {
                $errors[] = "Invalid rate for {$code}: must be greater than 0";
                continue;
            }

            try {
                $currency = $this->currencyModel->getByCode($code);
                if (!$currency) {
                    continue;
                }

                $currentRate = (float) $currency['exchange_rate'];
                if (abs($newRate - $currentRate) > 0.000001) {
                    $this->currencyModel->updateExchangeRate($code, $newRate, $userId);
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error updating {$code}: " . $e->getMessage();
            }
        }

        if ($updated > 0) {
            $this->setFlash('success', "Updated {$updated} exchange rates successfully");
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
        }

        if ($updated == 0 && empty($errors)) {
            $this->setFlash('info', 'No exchange rates were changed');
        }

        $this->redirect('/currencies');
    }

    public function setUserCurrency(): void
    {
        if (!Helpers::verifyCsrf()) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $currency = strtoupper(Helpers::input('currency', ''));

        try {
            if (!$this->currencyModel->isValidCurrency($currency)) {
                throw new \InvalidArgumentException('Invalid currency');
            }

            Helpers::setUserCurrency($currency);
            
            $this->jsonResponse([
                'success' => true, 
                'message' => "Currency changed to {$currency}",
                'currency' => $currency
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getExchangeRate(): void
    {
        $from = strtoupper(Helpers::input('from', ''));
        $to = strtoupper(Helpers::input('to', ''));

        try {
            if (empty($from) || empty($to)) {
                throw new \InvalidArgumentException('From and to currencies are required');
            }

            $rate = $this->currencyModel->getExchangeRate($from, $to);
            
            $this->jsonResponse([
                'success' => true,
                'rate' => $rate,
                'from' => $from,
                'to' => $to
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function convert(): void
    {
        $amount = (float) Helpers::input('amount', 0);
        $from = strtoupper(Helpers::input('from', ''));
        $to = strtoupper(Helpers::input('to', ''));

        try {
            if ($amount <= 0) {
                throw new \InvalidArgumentException('Amount must be greater than 0');
            }

            if (empty($from) || empty($to)) {
                throw new \InvalidArgumentException('From and to currencies are required');
            }

            $convertedAmount = $this->currencyModel->convert($amount, $from, $to);
            
            $this->jsonResponse([
                'success' => true,
                'original_amount' => $amount,
                'converted_amount' => $convertedAmount,
                'from' => $from,
                'to' => $to,
                'formatted_original' => Helpers::formatCurrency($amount, $from),
                'formatted_converted' => Helpers::formatCurrency($convertedAmount, $to)
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Private helper methods
     */
    private function checkCurrencyUsage(string $code): int
    {
        $tables = [
            'sp_quotes' => 'currency_code',
            'sp_sales_orders' => 'currency_code',
            'sp_invoices' => 'currency_code',
            'sp_payments' => 'currency_code',
            'sp_products' => 'currency_code'
        ];

        $totalUsage = 0;

        foreach ($tables as $table => $column) {
            try {
                $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
                $stmt = \App\Config\DB::query($sql, [$code]);
                $result = $stmt->fetch();
                $totalUsage += (int) $result['count'];
            } catch (\Exception $e) {
                // Table might not exist or column might not exist yet
                continue;
            }
        }

        return $totalUsage;
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
