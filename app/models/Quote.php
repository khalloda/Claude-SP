<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class Quote extends Model
{
    protected string $table = 'sp_quotes';
    
    protected array $fillable = [
        'client_id',
        'status',
        'items_subtotal',
        'items_tax_total',
        'items_discount_total',
        'global_tax_type',
        'global_tax_value',
        'global_discount_type',
        'global_discount_value',
        'tax_total',
        'discount_total',
        'grand_total',
        'notes'
    ];

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $countStmt = DB::query($countSql);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results with client names
        $sql = "SELECT q.*, 
                       c.name as client_name, 
                       c.type as client_type,
                       c.email as client_email,
                       c.phone as client_phone,
                       c.address as client_address
                FROM {$this->table} q
                LEFT JOIN sp_clients c ON q.client_id = c.id
                ORDER BY q.created_at DESC 
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

    public function search(string $query, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $searchQuery = "%{$query}%";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} q
                     LEFT JOIN sp_clients c ON q.client_id = c.id
                     WHERE c.name LIKE ? OR q.notes LIKE ? OR q.id LIKE ?";
        $countStmt = DB::query($countSql, [$searchQuery, $searchQuery, $searchQuery]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results with client names
        $sql = "SELECT q.*, c.name as client_name, c.type as client_type
                FROM {$this->table} q
                LEFT JOIN sp_clients c ON q.client_id = c.id
                WHERE c.name LIKE ? OR q.notes LIKE ? OR q.id LIKE ?
                ORDER BY q.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$searchQuery, $searchQuery, $searchQuery, $perPage, $offset]);
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

    public function getWithClient(int $quoteId): ?array
    {
        $sql = "SELECT q.*, c.name as client_name, c.type as client_type, c.email as client_email,
                       c.phone as client_phone, c.address as client_address
                FROM {$this->table} q
                LEFT JOIN sp_clients c ON q.client_id = c.id
                WHERE q.id = ?";
        $stmt = DB::query($sql, [$quoteId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getItems(int $quoteId): array
    {
        $sql = "SELECT qi.*, p.code as product_code, p.name as product_name, p.classification,
                       p.total_qty as available_qty
                FROM sp_quote_items qi
                LEFT JOIN sp_products p ON qi.product_id = p.id
                WHERE qi.quote_id = ?
                ORDER BY qi.id ASC";
        $stmt = DB::query($sql, [$quoteId]);
        return $stmt->fetchAll();
    }

    public function getStatusSummary(): array
    {
        $sql = "SELECT status, COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total_value
                FROM {$this->table} 
                GROUP BY status";
        $stmt = DB::query($sql);
        $results = $stmt->fetchAll();
        
        $summary = [
            'sent' => ['count' => 0, 'total_value' => 0],
            'approved' => ['count' => 0, 'total_value' => 0], 
            'rejected' => ['count' => 0, 'total_value' => 0]
        ];
        
        foreach ($results as $result) {
            $summary[$result['status']] = [
                'count' => (int) $result['count'],
                'total_value' => (float) $result['total_value']
            ];
        }
        
        return $summary;
    }

    public function createWithItems(array $data, array $items): int
    {
        DB::beginTransaction();
        
        try {
            // Calculate totals
            $totals = $this->calculateTotals($data, $items);
            
            // Merge calculated totals with quote data
            $quoteData = array_merge($data, $totals);
            
            // Create quote
            $quoteId = $this->create($quoteData);
            
            // Create quote items
            foreach ($items as $item) {
                $item['quote_id'] = $quoteId;
                DB::query(
                    "INSERT INTO sp_quote_items (quote_id, product_id, qty, price, tax, tax_type, discount, discount_type) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $item['quote_id'],
                        $item['product_id'],
                        $item['qty'],
                        $item['price'],
                        $item['tax'],
                        $item['tax_type'],
                        $item['discount'],
                        $item['discount_type']
                    ]
                );
            }
            
            // Reserve stock for approved quotes
            if ($data['status'] === 'approved') {
                $this->reserveStock($quoteId, $items);
            }
            
            DB::commit();
            return $quoteId;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateWithItems(int $quoteId, array $data, array $items): bool
    {
        DB::beginTransaction();
        
        try {
            // Get current quote for stock management
            $currentQuote = $this->find($quoteId);
            
            // Calculate totals
            $totals = $this->calculateTotals($data, $items);
            
            // Merge calculated totals with quote data
            $quoteData = array_merge($data, $totals);
            
            // Update quote
            $this->update($quoteId, $quoteData);
            
            // Delete existing items
            DB::query("DELETE FROM sp_quote_items WHERE quote_id = ?", [$quoteId]);
            
            // Create new items
            foreach ($items as $item) {
                $item['quote_id'] = $quoteId;
                DB::query(
                    "INSERT INTO sp_quote_items (quote_id, product_id, qty, price, tax, tax_type, discount, discount_type) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $item['quote_id'],
                        $item['product_id'],
                        $item['qty'],
                        $item['price'],
                        $item['tax'],
                        $item['tax_type'],
                        $item['discount'],
                        $item['discount_type']
                    ]
                );
            }
            
            // Handle stock reservations
            if ($currentQuote['status'] === 'approved') {
                // Release old reservations
                $this->releaseStock($quoteId);
            }
            
            if ($data['status'] === 'approved') {
                // Reserve new stock
                $this->reserveStock($quoteId, $items);
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function approve(int $quoteId): bool
    {
        DB::beginTransaction();
        
        try {
            // Update status
            $this->update($quoteId, ['status' => 'approved']);
            
            // Reserve stock for all items
            $items = $this->getItems($quoteId);
            $this->reserveStock($quoteId, $items);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function reject(int $quoteId): bool
    {
        DB::beginTransaction();
        
        try {
            // Get current quote
            $quote = $this->find($quoteId);
            
            // Release stock if it was approved
            if ($quote['status'] === 'approved') {
                $this->releaseStock($quoteId);
            }
            
            // Update status
            $this->update($quoteId, ['status' => 'rejected']);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function calculateTotals(array $data, array $items): array
    {
        $itemsSubtotal = 0;
        $itemsTax = 0;
        $itemsDiscount = 0;
        
        foreach ($items as $item) {
            $lineSubtotal = $item['qty'] * $item['price'];
            $itemsSubtotal += $lineSubtotal;
            
            // Calculate tax
            if ($item['tax_type'] === 'percent') {
                $itemsTax += $lineSubtotal * ($item['tax'] / 100);
            } else {
                $itemsTax += $item['tax'];
            }
            
            // Calculate discount  
            if ($item['discount_type'] === 'percent') {
                $itemsDiscount += $lineSubtotal * ($item['discount'] / 100);
            } else {
                $itemsDiscount += $item['discount'];
            }
        }
        
        // Calculate global tax and discount
        $globalTaxValue = $data['global_tax_value'] ?? 0;
        $globalTaxType = $data['global_tax_type'] ?? 'percent';
        $globalDiscountValue = $data['global_discount_value'] ?? 0;
        $globalDiscountType = $data['global_discount_type'] ?? 'percent';
        
        $baseForGlobal = $itemsSubtotal + $itemsTax - $itemsDiscount;
        
        $globalTax = $globalTaxType === 'percent' ? 
            ($baseForGlobal * $globalTaxValue / 100) : $globalTaxValue;
            
        $baseForDiscount = $baseForGlobal + $globalTax;
        $globalDiscount = $globalDiscountType === 'percent' ? 
            ($baseForDiscount * $globalDiscountValue / 100) : $globalDiscountValue;
        
        $totalTax = $itemsTax + $globalTax;
        $totalDiscount = $itemsDiscount + $globalDiscount;
        $grandTotal = $itemsSubtotal + $totalTax - $totalDiscount;
        
        return [
            'items_subtotal' => round($itemsSubtotal, 2),
            'items_tax_total' => round($itemsTax, 2),
            'items_discount_total' => round($itemsDiscount, 2),
            'tax_total' => round($totalTax, 2),
            'discount_total' => round($totalDiscount, 2),
            'grand_total' => round(max(0, $grandTotal), 2)
        ];
    }

    private function reserveStock(int $quoteId, array $items): void
    {
        foreach ($items as $item) {
            // Update product reserved quantities
            DB::query(
                "UPDATE sp_products SET reserved_quotes = reserved_quotes + ? WHERE id = ?",
                [$item['qty'], $item['product_id']]
            );
            
            // Log stock movement
            DB::query(
                "INSERT INTO sp_stock_movements (product_id, direction, qty, reason, ref_table, ref_id) 
                 VALUES (?, 'out', ?, 'Reserved for quote', 'sp_quotes', ?)",
                [$item['product_id'], $item['qty'], $quoteId]
            );
        }
    }

    private function releaseStock(int $quoteId): void
    {
        $items = $this->getItems($quoteId);
        
        foreach ($items as $item) {
            // Update product reserved quantities
            DB::query(
                "UPDATE sp_products SET reserved_quotes = GREATEST(0, reserved_quotes - ?) WHERE id = ?",
                [$item['qty'], $item['product_id']]
            );
            
            // Log stock movement
            DB::query(
                "INSERT INTO sp_stock_movements (product_id, direction, qty, reason, ref_table, ref_id) 
                 VALUES (?, 'in', ?, 'Released from quote', 'sp_quotes', ?)",
                [$item['product_id'], $item['qty'], $quoteId]
            );
        }
    }
}
