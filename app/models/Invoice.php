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
        $sql = "SELECT i.*, c.name as client_name, c.type as client_type
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
        $invoice = $this->find($invoiceId);
        if (!$invoice) {
            return ['total' => 0, 'paid' => 0, 'balance' => 0];
        }

        $balance = $invoice['grand_total'] - $invoice['paid_total'];

        return [
            'total' => (float) $invoice['grand_total'],
            'paid' => (float) $invoice['paid_total'],
            'balance' => (float) $balance
        ];
    }

    public function getClientInvoices(int $clientId): array
    {
        $sql = "SELECT i.*, 
                       (i.grand_total - i.paid_total) as balance
                FROM {$this->table} i
                WHERE i.client_id = ? AND i.status != 'void'
                ORDER BY i.created_at DESC";
        $stmt = DB::query($sql, [$clientId]);
        return $stmt->fetchAll();
    }

    public function getOverdueInvoices(int $days = 30): array
    {
        $sql = "SELECT i.*, c.name as client_name, c.email as client_email,
                       (i.grand_total - i.paid_total) as balance,
                       DATEDIFF(NOW(), i.created_at) as days_overdue
                FROM {$this->table} i
                LEFT JOIN sp_clients c ON i.client_id = c.id
                WHERE i.status IN ('open', 'partial') 
                AND DATEDIFF(NOW(), i.created_at) > ?
                ORDER BY days_overdue DESC";
        $stmt = DB::query($sql, [$days]);
        return $stmt->fetchAll();
    }

    public function getInvoicesByStatus(string $status): array
    {
        $sql = "SELECT i.*, c.name as client_name,
                       (i.grand_total - i.paid_total) as balance
                FROM {$this->table} i
                LEFT JOIN sp_clients c ON i.client_id = c.id
                WHERE i.status = ?
                ORDER BY i.created_at DESC";
        $stmt = DB::query($sql, [$status]);
        return $stmt->fetchAll();
    }

    public function getTotalsByStatus(): array
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(grand_total) as total_amount,
                    SUM(paid_total) as paid_amount,
                    SUM(grand_total - paid_total) as balance_amount
                FROM {$this->table}
                GROUP BY status
                ORDER BY status";
        $stmt = DB::query($sql);
        return $stmt->fetchAll();
    }

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalStmt = DB::query("SELECT COUNT(*) as total FROM {$this->table}");
        $total = $totalStmt->fetch()['total'];
        
        // Get paginated results with client names and balance
        $stmt = DB::query("SELECT i.*, c.name as client_name, c.type as client_type,
                                  (i.grand_total - i.paid_total) as balance
                          FROM {$this->table} i
                          LEFT JOIN sp_clients c ON i.client_id = c.id
                          ORDER BY i.created_at DESC 
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

    public function delete(int $id): bool
    {
        DB::beginTransaction();
        
        try {
            // Delete payments first
            DB::query("DELETE FROM sp_payments WHERE invoice_id = ?", [$id]);
            
            // Delete items
            DB::query("DELETE FROM sp_invoice_items WHERE invoice_id = ?", [$id]);
            
            // Delete invoice
            $result = parent::delete($id);
            
            DB::commit();
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
