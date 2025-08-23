<?php
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
        'is_primary',
        'is_active',
        'exchange_rate',
        'decimal_places'
    ];

    /**
     * Get all active currencies
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY is_primary DESC, name ASC";
        $stmt = DB::query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get primary currency
     */
    public function getPrimary(): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_primary = 1 AND is_active = 1 LIMIT 1";
        $stmt = DB::query($sql);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get currency by code
     */
    public function getByCode(string $code): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? AND is_active = 1";
        $stmt = DB::query($sql, [strtoupper($code)]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get exchange rate between two currencies
     */
    public function getExchangeRate(string $from, string $to): float
    {
        // If same currency, return 1
        if (strtoupper($from) === strtoupper($to)) {
            return 1.0;
        }

        // Get both currencies
        $fromCurrency = $this->getByCode($from);
        $toCurrency = $this->getByCode($to);

        if (!$fromCurrency || !$toCurrency) {
            throw new \InvalidArgumentException("Invalid currency code: {$from} or {$to}");
        }

        // Calculate rate: from_rate / to_rate
        // Since rates are stored relative to primary currency (EGP)
        // To convert from A to B: amount * (rate_A / rate_B)
        return (float) ($fromCurrency['exchange_rate'] / $toCurrency['exchange_rate']);
    }

    /**
     * Convert amount between currencies
     */
    public function convert(float $amount, string $from, string $to): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        $rate = $this->getExchangeRate($from, $to);
        return round($amount / $rate, 2);
    }

    /**
     * Convert amount to primary currency
     */
    public function convertToPrimary(float $amount, string $from): float
    {
        $primary = $this->getPrimary();
        if (!$primary) {
            throw new \RuntimeException('No primary currency configured');
        }

        return $this->convert($amount, $from, $primary['code']);
    }

    /**
     * Convert amount from primary currency
     */
    public function convertFromPrimary(float $amount, string $to): float
    {
        $primary = $this->getPrimary();
        if (!$primary) {
            throw new \RuntimeException('No primary currency configured');
        }

        return $this->convert($amount, $primary['code'], $to);
    }

    /**
     * Update exchange rate and log history
     */
    public function updateExchangeRate(string $code, float $newRate, ?int $userId = null): bool
    {
        DB::beginTransaction();
        
        try {
            // Get current rate for history
            $currency = $this->getByCode($code);
            if (!$currency) {
                throw new \InvalidArgumentException("Currency {$code} not found");
            }

            $previousRate = (float) $currency['exchange_rate'];

            // Update the rate
            $this->updateByCode($code, [
                'exchange_rate' => $newRate,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Log to history
            $this->logExchangeRateChange($code, $newRate, $previousRate, $userId);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get exchange rate history
     */
    public function getExchangeRateHistory(string $code, int $limit = 50): array
    {
        $sql = "SELECT h.*, u.username as changed_by_name
                FROM sp_exchange_rate_history h
                LEFT JOIN sp_users u ON h.changed_by = u.id
                WHERE h.currency_code = ?
                ORDER BY h.created_at DESC
                LIMIT ?";
        $stmt = DB::query($sql, [strtoupper($code), $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get currency statistics
     */
    public function getCurrencyStats(): array
    {
        $sql = "SELECT 
                    c.code,
                    c.name,
                    c.symbol,
                    c.is_primary,
                    c.exchange_rate,
                    COALESCE(quote_count.count, 0) as quotes_count,
                    COALESCE(quote_total.total, 0) as quotes_total,
                    COALESCE(invoice_count.count, 0) as invoices_count,
                    COALESCE(invoice_total.total, 0) as invoices_total
                FROM {$this->table} c
                LEFT JOIN (
                    SELECT currency_code, COUNT(*) as count 
                    FROM sp_quotes 
                    GROUP BY currency_code
                ) quote_count ON c.code = quote_count.currency_code
                LEFT JOIN (
                    SELECT currency_code, SUM(grand_total) as total 
                    FROM sp_quotes 
                    GROUP BY currency_code
                ) quote_total ON c.code = quote_total.currency_code
                LEFT JOIN (
                    SELECT currency_code, COUNT(*) as count 
                    FROM sp_invoices 
                    GROUP BY currency_code
                ) invoice_count ON c.code = invoice_count.currency_code
                LEFT JOIN (
                    SELECT currency_code, SUM(grand_total) as total 
                    FROM sp_invoices 
                    GROUP BY currency_code
                ) invoice_total ON c.code = invoice_total.currency_code
                WHERE c.is_active = 1
                ORDER BY c.is_primary DESC, c.name ASC";
        
        $stmt = DB::query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Validate currency code
     */
    public function isValidCurrency(string $code): bool
    {
        return $this->getByCode($code) !== null;
    }

    /**
     * Get currency dropdown options
     */
    public function getDropdownOptions(): array
    {
        $currencies = $this->getActive();
        $options = [];
        
        foreach ($currencies as $currency) {
            $label = $currency['name'] . ' (' . $currency['symbol'] . ')';
            if ($currency['is_primary']) {
                $label .= ' - Primary';
            }
            
            $options[] = [
                'value' => $currency['code'],
                'label' => $label,
                'symbol' => $currency['symbol'],
                'is_primary' => (bool) $currency['is_primary']
            ];
        }
        
        return $options;
    }

    /**
     * Set primary currency
     */
    public function setPrimary(string $code): bool
    {
        DB::beginTransaction();
        
        try {
            // Remove primary flag from all currencies
            DB::query("UPDATE {$this->table} SET is_primary = 0");
            
            // Set new primary
            $updated = $this->updateByCode($code, ['is_primary' => 1]);
            
            if (!$updated) {
                throw new \InvalidArgumentException("Currency {$code} not found");
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Private helper methods
     */
    private function updateByCode(string $code, array $data): bool
    {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $params[] = strtoupper($code);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE code = ?";
        $stmt = DB::query($sql, $params);
        
        return $stmt->rowCount() > 0;
    }

    private function logExchangeRateChange(string $code, float $newRate, float $previousRate, ?int $userId): void
    {
        $sql = "INSERT INTO sp_exchange_rate_history 
                (currency_code, rate, previous_rate, changed_by) 
                VALUES (?, ?, ?, ?)";
        DB::query($sql, [strtoupper($code), $newRate, $previousRate, $userId]);
    }

    /**
     * Create new currency
     */
    public function createCurrency(array $data): int
    {
        // Validate required fields
        $required = ['code', 'name', 'symbol', 'exchange_rate'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Field {$field} is required");
            }
        }

        // Ensure code is uppercase
        $data['code'] = strtoupper($data['code']);

        // Set defaults
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['is_primary'] = $data['is_primary'] ?? 0;
        $data['decimal_places'] = $data['decimal_places'] ?? 2;

        // Check if code already exists
        if ($this->getByCode($data['code'])) {
            throw new \InvalidArgumentException("Currency {$data['code']} already exists");
        }

        return $this->create($data);
    }

    /**
     * Get all currencies with pagination
     */
    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $countStmt = DB::query($countSql);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results
        $sql = "SELECT * FROM {$this->table} 
                ORDER BY is_primary DESC, is_active DESC, name ASC 
                LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$perPage, $offset]);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
}
