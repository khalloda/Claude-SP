<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class Invoice extends Model
{
    protected string $table = 'sp_invoices';
    
    protected array $fillable = [
        'client_id',
        'sales_order_id',
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
        'paid_total',
        'notes'
    ];

    public function search(string $query, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $searchQuery = "%{$query}%";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} i
                     LEFT JOIN sp_clients c ON i.client_id = c.id
                     WHERE c.name LIKE ? OR i.notes LIKE ? OR i.id LIKE ?";
        $countStmt = DB::query($countSql, [$searchQuery, $searchQuery, $searchQuery]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results with client names
        $sql = "SELECT i.*, 
                       c.name as client_name, 
                       c.type as client_type,
                       c.email as client_email,
                       c.phone as client_phone,
                       c.address as client_address
                FROM {$this->table} i
                LEFT JOIN sp_clients c ON i.client_id = c.id
                WHERE c.name LIKE ? OR i.notes LIKE ? OR i.id LIKE ?
                ORDER BY i.created_at DESC 
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

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $countStmt = DB::query($countSql);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results with client names
        $sql = "SELECT i.*, 
                       c.name as client_name, 
                       c.type as client_type,
                       c.email as client_email,
                       c.phone as client_phone,
                       c.address as client_address
                FROM {$this->table} i
                LEFT JOIN sp_clients c ON i.client_id = c.id
                ORDER BY i.created_at DESC 
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

    public function getWithClient(int $invoiceId): ?array
    {
        $sql = "SELECT i.*, c.name as client_name, c.type as client_type, c.email as client_email,
                       c.phone as client_phone, c.address as client_address,
                       so.id as sales_order_id
                FROM {$this->table} i
                LEFT JOIN sp_clients c ON i.client_id = c.id
                LEFT JOIN sp_sales_orders so ON i.sales_order_id = so.id
                WHERE i.id = ?";
        $stmt = DB::query($sql, [$invoiceId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getItems(int $invoiceId): array
    {
        $sql = "SELECT ii.*, p.code as product_code, p.name as product_name, p.classification
                FROM sp_invoice_items ii
                LEFT JOIN sp_products p ON ii.product_id = p.id
                WHERE ii.invoice_id = ?
                ORDER BY ii.id ASC";
        $stmt = DB::query($sql, [$invoiceId]);
        return $stmt->fetchAll();
    }

    public function getPayments(int $invoiceId): array
    {
        $sql = "SELECT p.*, c.name as client_name
                FROM sp_payments p
                LEFT JOIN sp_clients c ON p.client_id = c.id
                WHERE p.invoice_id = ?
                ORDER BY p.created_at DESC";
        $stmt = DB::query($sql, [$invoiceId]);
        return $stmt->fetchAll();
    }

    public function addPayment(int $invoiceId, float $amount, string $method = 'cash', string $note = ''): int
    {
        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            throw new \Exception('Invoice not found');
        }

        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than zero');
        }

        $remainingAmount = $invoice['grand_total'] - $invoice['paid_total'];
        if ($amount > $remainingAmount) {
            throw new \Exception('Payment amount cannot exceed remaining balance');
        }

        DB::beginTransaction();

        try {
            // Create payment record
            $paymentSql = "INSERT INTO sp_payments (invoice_id, client_id, amount, method, note)
                          VALUES (?, ?, ?, ?, ?)";
            DB::query($paymentSql, [$invoiceId, $invoice['client_id'], $amount, $method, $note]);
            $paymentId = (int) DB::lastInsertId();

            // Update invoice paid total
            $newPaidTotal = $invoice['paid_total'] + $amount;
            $this->update($invoiceId, ['paid_total' => $newPaidTotal]);

            // Update invoice status based on payment
            $newStatus = 'partial';
            if ($newPaidTotal >= $invoice['grand_total']) {
                $newStatus = 'paid';
            } elseif ($newPaidTotal == 0) {
                $newStatus = 'open';
            }
            
            $this->update($invoiceId, ['status' => $newStatus]);

            DB::commit();
            return $paymentId;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $invoiceId, string $status): bool
    {
        $validStatuses = ['open', 'partial', 'paid', 'void'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        // Void invoice - special handling
        if ($status === 'void') {
            return $this->voidInvoice($invoiceId);
        }

        return $this->update($invoiceId, ['status' => $status]);
    }

    private function voidInvoice(int $invoiceId): bool
    {
        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            return false;
        }

        DB::beginTransaction();

        try {
            // Update invoice status
            $this->update($invoiceId, ['status' => 'void']);

            // Note: In a real system, you might want to handle payments differently
            // For now, we'll keep payment records but mark the invoice as void
            // You could add a note to payments or create a refund system

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getBalance(int $invoiceId): array
    {
        $sql = "SELECT 
                    grand_total,
                    paid_total,
                    (grand_total - paid_total) as balance,
                    CASE 
                        WHEN paid_total = 0 THEN 'unpaid'
                        WHEN paid_total >= grand_total THEN 'paid'
                        ELSE 'partial'
                    END as payment_status
                FROM {$this->table}
                WHERE id = ?";
        
        $stmt = DB::query($sql, [$invoiceId]);
        $result = $stmt->fetch();
        
        return $result ?: [
            'grand_total' => 0,
            'paid_total' => 0,
            'balance' => 0,
            'payment_status' => 'unknown'
        ];
    }

    public function getTotalsByStatus(): array
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(grand_total) as total,
                    SUM(paid_total) as paid,
                    SUM(grand_total - paid_total) as balance
                FROM {$this->table} 
                GROUP BY status";
        
        $stmt = DB::query($sql);
        $results = $stmt->fetchAll();
        
        $summary = [
            'open' => ['count' => 0, 'total' => 0, 'paid' => 0, 'balance' => 0],
            'partial' => ['count' => 0, 'total' => 0, 'paid' => 0, 'balance' => 0],
            'paid' => ['count' => 0, 'total' => 0, 'paid' => 0, 'balance' => 0],
            'void' => ['count' => 0, 'total' => 0, 'paid' => 0, 'balance' => 0]
        ];
        
        foreach ($results as $result) {
            $status = $result['status'];
            if (isset($summary[$status])) {
                $summary[$status] = [
                    'count' => (int) $result['count'],
                    'total' => (float) $result['total'],
                    'paid' => (float) $result['paid'],
                    'balance' => (float) $result['balance']
                ];
            }
        }
        
        return $summary;
    }

    public function getInvoicesByStatus(string $status): array
    {
        $sql = "SELECT i.*, 
                       c.name as client_name, 
                       c.type as client_type,
                       c.email as client_email,
                       c.phone as client_phone,
                       c.address as client_address,
                       (i.grand_total - i.paid_total) as balance
                FROM {$this->table} i
                LEFT JOIN sp_clients c ON i.client_id = c.id
                WHERE i.status = ?
                ORDER BY i.created_at DESC";
        
        $stmt = DB::query($sql, [$status]);
        return $stmt->fetchAll();
    }

    public function createWithItems(array $invoiceData, array $items): int
    {
        DB::beginTransaction();
        
        try {
            // Calculate totals
            $totals = $this->calculateInvoiceTotals($items, $invoiceData);
            
            // Merge calculated totals with invoice data
            $invoiceData = array_merge($invoiceData, $totals);
            
            // Set initial paid total and status
            $invoiceData['paid_total'] = 0;
            $invoiceData['status'] = 'open';
            
            // Create the invoice
            $invoiceId = $this->create($invoiceData);
            
            // Create invoice items
            foreach ($items as $item) {
                $itemData = [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'tax' => $item['tax'] ?? 0,
                    'tax_type' => $item['tax_type'] ?? 'percent',
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'percent'
                ];
                
                $sql = "INSERT INTO sp_invoice_items (invoice_id, product_id, qty, price, tax, tax_type, discount, discount_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                DB::query($sql, array_values($itemData));
            }
            
            DB::commit();
            return $invoiceId;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateWithItems(int $invoiceId, array $invoiceData, array $items): bool
    {
        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            throw new \Exception('Invoice not found');
        }
        
        // Prevent updating paid or partially paid invoices
        if (in_array($invoice['status'], ['paid', 'partial'])) {
            throw new \Exception('Cannot update invoice with payments');
        }

        DB::beginTransaction();
        
        try {
            // Calculate totals
            $totals = $this->calculateInvoiceTotals($items, $invoiceData);
            
            // Merge calculated totals with invoice data
            $invoiceData = array_merge($invoiceData, $totals);
            
            // Update the invoice
            $this->update($invoiceId, $invoiceData);
            
            // Delete existing items
            $deleteItemsSql = "DELETE FROM sp_invoice_items WHERE invoice_id = ?";
            DB::query($deleteItemsSql, [$invoiceId]);
            
            // Create new invoice items
            foreach ($items as $item) {
                $itemData = [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'tax' => $item['tax'] ?? 0,
                    'tax_type' => $item['tax_type'] ?? 'percent',
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'percent'
                ];
                
                $sql = "INSERT INTO sp_invoice_items (invoice_id, product_id, qty, price, tax, tax_type, discount, discount_type)
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

    private function calculateInvoiceTotals(array $items, array $invoiceData): array
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
        $globalTaxType = $invoiceData['global_tax_type'] ?? 'percent';
        $globalTaxValue = (float) ($invoiceData['global_tax_value'] ?? 0);
        $globalDiscountType = $invoiceData['global_discount_type'] ?? 'percent';
        $globalDiscountValue = (float) ($invoiceData['global_discount_value'] ?? 0);
        
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
