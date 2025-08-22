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
        $sql = "SELECT qi.*, p.code as product_code, p.name as product_name, p.classification
                FROM sp_quote_items qi
                LEFT JOIN sp_products p ON qi.product_id = p.id
                WHERE qi.quote_id = ?
                ORDER BY qi.id ASC";
        $stmt = DB::query($sql, [$quoteId]);
        return $stmt->fetchAll();
    }

    public function createWithItems(array $quoteData, array $items): int
    {
        DB::beginTransaction();
        
        try {
            // Create quote
            $quoteId = $this->create($quoteData);
            
            // Create quote items
            foreach ($items as $item) {
                $item['quote_id'] = $quoteId;
                $this->createItem($item);
            }
            
            // Update quote totals
            $this->updateTotals($quoteId);
            
            // Reserve stock for quote items
            $this->reserveStock($quoteId, true);
            
            DB::commit();
            return $quoteId;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateWithItems(int $quoteId, array $quoteData, array $items): bool
    {
        DB::beginTransaction();
        
        try {
            // Release existing stock reservations
            $this->reserveStock($quoteId, false);
            
            // Delete existing items
            DB::query("DELETE FROM sp_quote_items WHERE quote_id = ?", [$quoteId]);
            
            // Update quote
            $this->update($quoteId, $quoteData);
            
            // Create new items
            foreach ($items as $item) {
                $item['quote_id'] = $quoteId;
                $this->createItem($item);
            }
            
            // Update totals
            $this->updateTotals($quoteId);
            
            // Reserve stock for new items
            $this->reserveStock($quoteId, true);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createItem(array $itemData): void
    {
        $sql = "INSERT INTO sp_quote_items (quote_id, product_id, qty, price, tax, tax_type, discount, discount_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        DB::query($sql, [
            $itemData['quote_id'],
            $itemData['product_id'],
            $itemData['qty'],
            $itemData['price'],
            $itemData['tax'] ?? 0,
            $itemData['tax_type'] ?? 'percent',
            $itemData['discount'] ?? 0,
            $itemData['discount_type'] ?? 'percent'
        ]);
    }

    public function updateTotals(int $quoteId): void
    {
        $quote = $this->find($quoteId);
        $items = $this->getItems($quoteId);
        
        $itemsSubtotal = 0;
        $itemsTaxTotal = 0;
        $itemsDiscountTotal = 0;
        
        foreach ($items as $item) {
            $lineTotal = $item['qty'] * $item['price'];
            $itemsSubtotal += $lineTotal;
            
            // Calculate line tax
            $lineTax = $item['tax_type'] === 'percent' 
                ? ($lineTotal * $item['tax'] / 100)
                : $item['tax'];
            $itemsTaxTotal += $lineTax;
            
            // Calculate line discount
            $lineDiscount = $item['discount_type'] === 'percent'
                ? ($lineTotal * $item['discount'] / 100)
                : $item['discount'];
            $itemsDiscountTotal += $lineDiscount;
        }
        
        // Calculate global tax
        $globalTax = 0;
        if ($quote['global_tax_value'] > 0) {
            $globalTax = $quote['global_tax_type'] === 'percent'
                ? ($itemsSubtotal * $quote['global_tax_value'] / 100)
                : $quote['global_tax_value'];
        }
        
        // Calculate global discount
        $globalDiscount = 0;
        if ($quote['global_discount_value'] > 0) {
            $globalDiscount = $quote['global_discount_type'] === 'percent'
                ? ($itemsSubtotal * $quote['global_discount_value'] / 100)
                : $quote['global_discount_value'];
        }
        
        $totalTax = $itemsTaxTotal + $globalTax;
        $totalDiscount = $itemsDiscountTotal + $globalDiscount;
        $grandTotal = $itemsSubtotal + $totalTax - $totalDiscount;
        
        // Update quote totals
        $sql = "UPDATE {$this->table} SET 
                items_subtotal = ?, items_tax_total = ?, items_discount_total = ?,
                tax_total = ?, discount_total = ?, grand_total = ?
                WHERE id = ?";
        DB::query($sql, [
            $itemsSubtotal, $itemsTaxTotal, $itemsDiscountTotal,
            $totalTax, $totalDiscount, $grandTotal, $quoteId
        ]);
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
        
        return $this->update($quoteId, ['status' => $status]);
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
            foreach ($items as $item) {
                $sql = "UPDATE sp_products SET 
                        reserved_quotes = reserved_quotes - ?, 
                        reserved_orders = reserved_orders + ? 
                        WHERE id = ?";
                DB::query($sql, [$item['qty'], $item['qty'], $item['product_id']]);
            }
            
            DB::commit();
            return $salesOrderId;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        
        try {
            // Release stock reservations
            $this->reserveStock($id, false);
            
            // Delete items first
            DB::query("DELETE FROM sp_quote_items WHERE quote_id = ?", [$id]);
            
            // Delete quote
            $result = parent::delete($id);
            
            DB::commit();
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalStmt = DB::query("SELECT COUNT(*) as total FROM {$this->table}");
        $total = $totalStmt->fetch()['total'];
        
        // Get paginated results with client names
        $stmt = DB::query("SELECT q.*, c.name as client_name, c.type as client_type
                          FROM {$this->table} q
                          LEFT JOIN sp_clients c ON q.client_id = c.id
                          ORDER BY q.created_at DESC 
                          LIMIT ? OFFSET ?", [$perPage, $offset]);
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
