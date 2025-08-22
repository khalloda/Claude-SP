<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class SalesOrder extends Model
{
    protected string $table = 'sp_sales_orders';
    
    protected array $fillable = [
        'client_id',
        'quote_id',
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
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} so
                     LEFT JOIN sp_clients c ON so.client_id = c.id
                     WHERE c.name LIKE ? OR so.notes LIKE ? OR so.id LIKE ?";
        $countStmt = DB::query($countSql, [$searchQuery, $searchQuery, $searchQuery]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results with client names
        $sql = "SELECT so.*, c.name as client_name, c.type as client_type
                FROM {$this->table} so
                LEFT JOIN sp_clients c ON so.client_id = c.id
                WHERE c.name LIKE ? OR so.notes LIKE ? OR so.id LIKE ?
                ORDER BY so.created_at DESC 
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

    public function getWithClient(int $salesOrderId): ?array
    {
        $sql = "SELECT so.*, c.name as client_name, c.type as client_type, c.email as client_email,
                       c.phone as client_phone, c.address as client_address,
                       q.id as quote_id
                FROM {$this->table} so
                LEFT JOIN sp_clients c ON so.client_id = c.id
                LEFT JOIN sp_quotes q ON so.quote_id = q.id
                WHERE so.id = ?";
        $stmt = DB::query($sql, [$salesOrderId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getItems(int $salesOrderId): array
    {
        $sql = "SELECT soi.*, p.code as product_code, p.name as product_name, p.classification,
                       p.total_qty as available_qty
                FROM sp_sales_order_items soi
                LEFT JOIN sp_products p ON soi.product_id = p.id
                WHERE soi.sales_order_id = ?
                ORDER BY soi.id ASC";
        $stmt = DB::query($sql, [$salesOrderId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $salesOrderId, string $status): bool
    {
        $validStatuses = ['open', 'delivered', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $oldOrder = $this->find($salesOrderId);
        if (!$oldOrder) {
            return false;
        }
        
        DB::beginTransaction();
        
        try {
            // Handle status changes
            if ($status === 'delivered' && $oldOrder['status'] !== 'delivered') {
                // Reduce actual stock and remove reservations
                $this->deliverStock($salesOrderId);
            } elseif ($status === 'rejected' && $oldOrder['status'] !== 'rejected') {
                // Release stock reservations
                $this->releaseStock($salesOrderId);
            }
            
            $result = $this->update($salesOrderId, ['status' => $status]);
            
            DB::commit();
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function deliverStock(int $salesOrderId): void
    {
        $items = $this->getItems($salesOrderId);
        
        foreach ($items as $item) {
            // Reduce total quantity and reserved orders
            $sql = "UPDATE sp_products SET 
                    total_qty = total_qty - ?, 
                    reserved_orders = reserved_orders - ? 
                    WHERE id = ?";
            DB::query($sql, [$item['qty'], $item['qty'], $item['product_id']]);
            
            // Record stock movement
            $stockMovementSql = "INSERT INTO sp_stock_movements 
                               (product_id, direction, qty, reason, ref_table, ref_id)
                               VALUES (?, 'out', ?, 'Sales Order Delivery', 'sp_sales_orders', ?)";
            DB::query($stockMovementSql, [$item['product_id'], $item['qty'], $salesOrderId]);
        }
    }

    private function releaseStock(int $salesOrderId): void
    {
        $items = $this->getItems($salesOrderId);
        
        foreach ($items as $item) {
            // Release reserved stock
            $sql = "UPDATE sp_products SET reserved_orders = reserved_orders - ? WHERE id = ?";
            DB::query($sql, [$item['qty'], $item['product_id']]);
        }
    }

    public function convertToInvoice(int $salesOrderId): int
    {
        $salesOrder = $this->getWithClient($salesOrderId);
        $items = $this->getItems($salesOrderId);
        
        if (!$salesOrder) {
            throw new \Exception('Sales order not found');
        }
        
        if ($salesOrder['status'] === 'rejected') {
            throw new \Exception('Cannot create invoice for rejected sales order');
        }
        
        DB::beginTransaction();
        
        try {
            // Create invoice
            $invoiceData = [
                'client_id' => $salesOrder['client_id'],
                'sales_order_id' => $salesOrderId,
                'status' => 'open',
                'items_subtotal' => $salesOrder['items_subtotal'],
                'items_tax_total' => $salesOrder['items_tax_total'],
                'items_discount_total' => $salesOrder['items_discount_total'],
                'global_tax_type' => $salesOrder['global_tax_type'],
                'global_tax_value' => $salesOrder['global_tax_value'],
                'global_discount_type' => $salesOrder['global_discount_type'],
                'global_discount_value' => $salesOrder['global_discount_value'],
                'tax_total' => $salesOrder['tax_total'],
                'discount_total' => $salesOrder['discount_total'],
                'grand_total' => $salesOrder['grand_total'],
                'paid_total' => 0,
                'notes' => $salesOrder['notes']
            ];
            
            $invoiceModel = new Invoice();
            $invoiceId = $invoiceModel->create($invoiceData);
            
            // Create invoice items
            foreach ($items as $item) {
                $invoiceItemData = [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'tax' => $item['tax'],
                    'tax_type' => $item['tax_type'],
                    'discount' => $item['discount'],
                    'discount_type' => $item['discount_type']
                ];
                
                $sql = "INSERT INTO sp_invoice_items (invoice_id, product_id, qty, price, tax, tax_type, discount, discount_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                DB::query($sql, array_values($invoiceItemData));
            }
            
            DB::commit();
            return $invoiceId;
            
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
        $stmt = DB::query("SELECT so.*, c.name as client_name, c.type as client_type
                          FROM {$this->table} so
                          LEFT JOIN sp_clients c ON so.client_id = c.id
                          ORDER BY so.created_at DESC 
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
            // Release stock reservations
            $this->releaseStock($id);
            
            // Delete items first
            DB::query("DELETE FROM sp_sales_order_items WHERE sales_order_id = ?", [$id]);
            
            // Delete sales order
            $result = parent::delete($id);
            
            DB::commit();
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
