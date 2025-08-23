<?php
// Create this file: app/models/Currency.php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class Currency extends Model
{
    protected string $table = 'sp_currencies';
    
    protected array $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'exchange_rate',
        'is_active',
        'is_primary'
    ];

    /**
     * Get all active currencies
     */
    public function getActive(): array
    {
        $stmt = DB::query("
            SELECT * FROM {$this->table} 
            WHERE is_active = 1 
            ORDER BY is_primary DESC, code ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get primary currency
     */
    public function getPrimary(): ?array
    {
        $stmt = DB::query("SELECT * FROM {$this->table} WHERE is_primary = 1 LIMIT 1");
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get currency by code
     */
    public function getByCode(string $code): ?array
    {
        $stmt = DB::query("SELECT * FROM {$this->table} WHERE code = ?", [strtoupper($code)]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Check if currency code is valid and active
     */
    public function isValidCurrency(string $code): bool
    {
        $stmt = DB::query("SELECT id FROM {$this->table} WHERE code = ? AND is_active = 1", [strtoupper($code)]);
        return $stmt->fetch() !== false;
    }

    /**
     * Create new currency with validation
     */
    public function createCurrency(array $data): int
    {
        // Validate currency code
        $code = strtoupper($data['code']);
        if (strlen($code) !== 3) {
            throw new \InvalidArgumentException('Currency code must be exactly 3 characters');
        }

        // Check if code already exists
        if ($this->getByCode($code)) {
            throw new \InvalidArgumentException("Currency code {$code} already exists");
        }

        // Ensure only one primary currency
        if (!empty($data['is_primary']) && $data['is_primary']) {
            $this->clearPrimaryFlags();
            $data['exchange_rate'] = 1.000000; // Primary currency always has rate of 1.0
        }

        $data['code'] = $code;
        $currencyId = $this->create($data);

        // Record in history
        $this->recordExchangeRateHistory($code, null, $data['exchange_rate']);

        return $currencyId;
    }

    /**
     * Update currency
     */
    public function updateCurrency(string $code, array $data, ?int $userId = null): bool
    {
        $code = strtoupper($code);
        $currency = $this->getByCode($code);
        
        if (!$currency) {
            throw new \InvalidArgumentException("Currency {$code} not found");
        }

        // Handle primary currency logic
        if (!empty($data['is_primary']) && $data['is_primary']) {
            $this->clearPrimaryFlags();
            $data['exchange_rate'] = 1.000000;
        }

        // Record exchange rate history if rate changed
        if (isset($data['exchange_rate']) && (float)$data['exchange_rate'] !== (float)$currency['exchange_rate']) {
            $this->recordExchangeRateHistory($code, $currency['exchange_rate'], $data['exchange_rate'], $userId);
        }

        return $this->update($currency['id'], $data);
    }

    /**
     * Set currency as primary
     */
    public function setPrimary(string $code): bool
    {
        $code = strtoupper($code);
        $currency = $this->getByCode($code);
        
        if (!$currency) {
            throw new \InvalidArgumentException("Currency {$code} not found");
        }

        DB::beginTransaction();

        try {
            // Clear all primary flags
            $this->clearPrimaryFlags();

            // Set this currency as primary with rate 1.0
            $this->update($currency['id'], [
                'is_primary' => 1,
                'is_active' => 1,
                'exchange_rate' => 1.000000
            ]);

            // Record in history
            if ((float)$currency['exchange_rate'] !== 1.000000) {
                $this->recordExchangeRateHistory($code, $currency['exchange_rate'], 1.000000);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update exchange rate
     */
    public function updateExchangeRate(string $code, float $newRate, ?int $userId = null): bool
    {
        $code = strtoupper($code);
        $currency = $this->getByCode($code);
        
        if (!$currency) {
            throw new \InvalidArgumentException("Currency {$code} not found");
        }

        if ($currency['is_primary']) {
            throw new \InvalidArgumentException("Cannot change exchange rate of primary currency");
        }

        if ($newRate <= 0) {
            throw new \InvalidArgumentException("Exchange rate must be greater than 0");
        }

        $oldRate = (float)$currency['exchange_rate'];
        
        if (abs($newRate - $oldRate) < 0.000001) {
            return true; // No significant change
        }

        $success = $this->update($currency['id'], ['exchange_rate' => $newRate]);
        
        if ($success) {
            $this->recordExchangeRateHistory($code, $oldRate, $newRate, $userId);
        }

        return $success;
    }

    /**
     * Get exchange rate between two currencies
     */
    public function getExchangeRate(string $fromCode, string $toCode): float
    {
        $fromCode = strtoupper($fromCode);
        $toCode = strtoupper($toCode);

        if ($fromCode === $toCode) {
            return 1.0;
        }

        $fromCurrency = $this->getByCode($fromCode);
        $toCurrency = $this->getByCode($toCode);

        if (!$fromCurrency || !$toCurrency) {
            throw new \InvalidArgumentException("Invalid currency code");
        }

        // Convert through primary currency
        // Rate = (amount in from currency * from_rate) / to_rate
        return (float)$fromCurrency['exchange_rate'] / (float)$toCurrency['exchange_rate'];
    }

    /**
     * Convert amount between currencies
     */
    public function convert(float $amount, string $fromCode, string $toCode): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        $rate = $this->getExchangeRate($fromCode, $toCode);
        return $amount * $rate;
    }

    /**
     * Get exchange rate history for a currency
     */
    public function getExchangeRateHistory(string $code, int $limit = 50): array
    {
        $query = "
            SELECT ch.*, u.name as updated_by_name 
            FROM sp_currency_history ch 
            LEFT JOIN sp_users u ON u.id = ch.updated_by 
            WHERE ch.currency_code = ? 
            ORDER BY ch.created_at DESC 
            LIMIT ?
        ";
        
        $stmt = DB::query($query, [strtoupper($code), $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get currency statistics
     */
    public function getCurrencyStats(): array
    {
        $queries = [
            'total' => "SELECT COUNT(*) as count FROM {$this->table}",
            'active' => "SELECT COUNT(*) as count FROM {$this->table} WHERE is_active = 1",
            'primary' => "SELECT code FROM {$this->table} WHERE is_primary = 1",
            'last_updated' => "SELECT MAX(updated_at) as last_updated FROM {$this->table}"
        ];

        $stats = [];
        
        foreach ($queries as $key => $query) {
            $stmt = DB::query($query);
            $result = $stmt->fetch();
            
            if ($key === 'primary') {
                $stats[$key] = $result['code'] ?? 'None';
            } elseif ($key === 'last_updated') {
                $stats[$key] = $result['last_updated'];
            } else {
                $stats[$key] = (int)$result['count'];
            }
        }

        return $stats;
    }

    /**
     * Check if currency is being used in transactions
     */
    public function getUsageCount(string $code): int
    {
        // This will be implemented when transaction tables are added
        // For now, return 0 to allow deletions
        return 0;
        
        /* Future implementation:
        $tables = ['sp_quotes', 'sp_sales_orders', 'sp_invoices', 'sp_payments'];
        $totalUsage = 0;
        
        foreach ($tables as $table) {
            try {
                $stmt = DB::query("SELECT COUNT(*) as count FROM {$table} WHERE currency_code = ?", [$code]);
                $result = $stmt->fetch();
                $totalUsage += (int)($result['count'] ?? 0);
            } catch (\Exception $e) {
                // Table might not exist yet
                continue;
            }
        }
        
        return $totalUsage;
        */
    }

    /**
     * Paginate currencies
     */
    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countStmt = DB::query("SELECT COUNT(*) as count FROM {$this->table}");
        $totalRecords = (int)$countStmt->fetch()['count'];
        $totalPages = (int)ceil($totalRecords / $perPage);
        
        // Get paginated results
        $stmt = DB::query("
            SELECT * FROM {$this->table} 
            ORDER BY is_primary DESC, code ASC 
            LIMIT ? OFFSET ?
        ", [$perPage, $offset]);
        
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * Clear all primary currency flags
     */
    private function clearPrimaryFlags(): void
    {
        DB::query("UPDATE {$this->table} SET is_primary = 0");
    }

    /**
     * Record exchange rate change in history
     */
    private function recordExchangeRateHistory(string $code, ?float $oldRate, float $newRate, ?int $userId = null): void
    {
        try {
            DB::query("
                INSERT INTO sp_currency_history (currency_code, old_rate, new_rate, updated_by) 
                VALUES (?, ?, ?, ?)
            ", [$code, $oldRate, $newRate, $userId]);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to record currency history: " . $e->getMessage());
        }
    }
}
