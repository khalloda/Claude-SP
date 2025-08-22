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

    public function updateStatus(int $quoteId, string $status): bool
    {
        $validStatuses = ['sent', 'approved', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $oldQuote = $this->find($quoteId);
        if (!$oldQuote) {
            return false;
        }
        
        // If rejecting quote, release stock reservations
        if ($status === 'rejected' && $oldQuote['status'] !== 'rejected') {
            $this->reserveStock($quoteId, false);
        }
        
        // If approving quote, reserve stock
        if ($status === 'approved' && $oldQuote['status'] !== 'approved') {
            $this->reserveStock($quoteId, true);
        }
        
        return $this->update($quoteId, ['status' => $status]);
    }

    private function reserveStock(int $quoteId, bool $reserve): void
    {
        $items = $this->getItems($quoteId);
        $operation = $reserve ? '+' : '-';
        
        foreach ($items as $item) {
            $sql = "UPDATE sp_products SET reserved_quotes = reserved_quotes {$operation} ? WHERE id = ?";
            DB::query($sql, [$item['qty'], $item['product_id']]);
        }
    }

    public function convertToSalesOrder(int $quoteId): int
    {
        $quote = $this->getWithClient($quoteId);
        $items = $this->getItems($quoteId);
        
        if (!$quote || $quote['status'] !== 'approved') {
            throw new \Exception('Quote must be approved before converting to sales order');
        }
        
        DB::beginTransaction();
        
        try {
            // Create sales order
            $salesOrderData = [
                'client_id' => $quote['client_id'],
                'quote_id' => $quoteId,
                'status' => 'open',
                'items_subtotal' => $quote['items_subtotal'],
                'items_tax_total' => $quote['items_tax_total'],
                'items_discount_total' => $quote['items_discount_total'],
                'global_tax_type' => $quote['global_tax_type'],
                'global_tax_value' => $quote['global_tax_value'],
                'global_discount_type' => $quote['global_discount_type'],
                'global_discount_value' => $quote['global_discount_value'],
                'tax_total' => $quote['tax_total'],
                'discount_total' => $quote['discount_total'],
                'grand_total' => $quote['grand_total'],
                'notes' => $quote['notes']
            ];
            
            $salesOrderModel = new SalesOrder();
            $salesOrderId = $salesOrderModel->create($salesOrderData);
            
            // Create sales order items
            foreach ($items as $item) {
                $salesOrderItemData = [
                    'sales_order_id' => $salesOrderId,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'tax' => $item['tax'],
                    'tax_type' => $item['tax_type'],
                    'discount' => $item['discount'],
                    'discount_type' => $item['discount_type']
                ];
                
                $sql = "INSERT INTO sp_sales_order_items (sales_order_id, product_id, qty, price, tax, tax_type, discount, discount_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                DB::query($sql, array_values($salesOrderItemData));
            }
            
            // Transfer stock reservations from quotes to orders
            $this->transferStockReservations($quoteId, $salesOrderId);
            
            DB::commit();
            return $salesOrderId;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function transferStockReservations(int $quoteId, int $salesOrderId): void
    {
        $items = $this->getItems($quoteId);
        
        foreach ($items as $item) {
            // Release from quote reservations and add to order reservations
            $sql = "UPDATE sp_products 
                    SET reserved_quotes = reserved_quotes - ?,
                        reserved_orders = reserved_orders + ?
                    WHERE id = ?";
            DB::query($sql, [$item['qty'], $item['qty'], $item['product_id']]);
        }
    }

    // MISSING METHODS IMPLEMENTATION:

    public function createWithItems(array $quoteData, array $items): int
    {
        DB::beginTransaction();
        
        try {
            // Calculate totals
            $totals = $this->calculateQuoteTotals($items, $quoteData);
            
            // Merge calculated totals with quote data
            $quoteData = array_merge($quoteData, $totals);
            
            // Create the quote
            $quoteId = $this->create($quoteData);
            
            // Create quote items
            foreach ($items as $item) {
                $itemData = [
                    'quote_id' => $quoteId,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'tax' => $item['tax'] ?? 0,
                    'tax_type' => $item['tax_type'] ?? 'percent',
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'percent'
                ];
                
                $sql = "INSERT INTO sp_quote_items (quote_id, product_id, qty, price, tax, tax_type, discount, discount_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                DB::query($sql, array_values($itemData));
            }
            
            DB::commit();
            return $quoteId;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateWithItems(int $quoteId, array $quoteData, array $items): bool
    {
        $quote = $this->find($quoteId);
        if (!$quote) {
            throw new \Exception('Quote not found');
        }
        
        // Prevent updating approved quotes
        if ($quote['status'] === 'approved') {
            throw new \Exception('Cannot update approved quote');
        }

        DB::beginTransaction();
        
        try {
            // Release any existing stock reservations if quote was approved
            if ($quote['status'] === 'approved') {
                $this->reserveStock($quoteId, false);
            }
            
            // Calculate totals
            $totals = $this->calculateQuoteTotals($items, $quoteData);
            
            // Merge calculated totals with quote data
            $quoteData = array_merge($quoteData, $totals);
            
            // Update the quote
            $this->update($quoteId, $quoteData);
            
            // Delete existing items
            $deleteItemsSql = "DELETE FROM sp_quote_items WHERE quote_id = ?";
            DB::query($deleteItemsSql, [$quoteId]);
            
            // Create new quote items
            foreach ($items as $item) {
                $itemData = [
                    'quote_id' => $quoteId,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'tax' => $item['tax'] ?? 0,
                    'tax_type' => $item['tax_type'] ?? 'percent',
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'percent'
                ];
                
                $sql = "INSERT INTO sp_quote_items (quote_id, product_id, qty, price, tax, tax_type, discount, discount_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                DB::query($sql, array_values($itemData));
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function calculateQuoteTotals(array $items, array $quoteData): array
    {
        $itemsSubtotal = 0;
        $itemsTaxTotal = 0;
        $itemsDiscountTotal = 0;
        
        // Calculate item-level totals
        foreach ($items as $item) {
            $qty = (float) $item['qty'];
            $price = (float) $item['price'];
            $tax = (float) ($item['tax'] ?? 0);
            $taxType = $item['tax_type'] ?? 'percent';
            $discount = (float) ($item['discount'] ?? 0);
            $discountType = $item['discount_type'] ?? 'percent';
            
            $lineSubtotal = $qty * $price;
            $itemsSubtotal += $lineSubtotal;
            
            // Calculate line tax
            if ($tax > 0) {
                $lineTax = $taxType === 'percent' ? ($lineSubtotal * $tax / 100) : $tax;
                $itemsTaxTotal += $lineTax;
            }
            
            // Calculate line discount
            if ($discount > 0) {
                $lineDiscount = $discountType === 'percent' ? ($lineSubtotal * $discount / 100) : $discount;
                $itemsDiscountTotal += $lineDiscount;
            }
        }
        
        // Calculate global tax and discount
        $globalTaxType = $quoteData['global_tax_type'] ?? 'percent';
        $globalTaxValue = (float) ($quoteData['global_tax_value'] ?? 0);
        $globalDiscountType = $quoteData['global_discount_type'] ?? 'percent';
        $globalDiscountValue = (float) ($quoteData['global_discount_value'] ?? 0);
        
        $globalTax = 0;
        $globalDiscount = 0;
        
        if ($globalTaxValue > 0) {
            $globalTax = $globalTaxType === 'percent' ? ($itemsSubtotal * $globalTaxValue / 100) : $globalTaxValue;
        }
        
        if ($globalDiscountValue > 0) {
            $globalDiscount = $globalDiscountType === 'percent' ? ($itemsSubtotal * $globalDiscountValue / 100) : $globalDiscountValue;
        }
        
        $totalTax = $itemsTaxTotal + $globalTax;
        $totalDiscount = $itemsDiscountTotal + $globalDiscount;
        $grandTotal = $itemsSubtotal + $totalTax - $totalDiscount;
        
        return [
            'items_subtotal' => $itemsSubtotal,
            'items_tax_total' => $itemsTaxTotal,
            'items_discount_total' => $itemsDiscountTotal,
            'tax_total' => $totalTax,
            'discount_total' => $totalDiscount,
            'grand_total' => max(0, $grandTotal) // Ensure non-negative total
        ];
    }
}
