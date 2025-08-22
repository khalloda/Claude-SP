<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class Payment extends Model
{
    protected string $table = 'sp_payments';
    
    protected array $fillable = [
        'invoice_id',
        'client_id',
        'amount',
        'method',
        'note'
    ];

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalStmt = DB::query("SELECT COUNT(*) as total FROM {$this->table}");
        $total = $totalStmt->fetch()['total'];
        
        // Get paginated results with client and invoice info
        $sql = "SELECT p.*, c.name as client_name, c.type as client_type,
                       i.grand_total as invoice_total
                FROM {$this->table} p
                LEFT JOIN sp_clients c ON p.client_id = c.id
                LEFT JOIN sp_invoices i ON p.invoice_id = i.id
                ORDER BY p.created_at DESC 
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

    public function searchWithFilters(array $filters, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $conditions = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $searchQuery = "%{$filters['search']}%";
            $conditions[] = "(c.name LIKE ? OR p.note LIKE ? OR p.method LIKE ? OR p.id LIKE ?)";
            $params = array_merge($params, [$searchQuery, $searchQuery, $searchQuery, $searchQuery]);
        }
        
        if (!empty($filters['client_id'])) {
            $conditions[] = "p.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        if (!empty($filters['method'])) {
            $conditions[] = "p.method = ?";
            $params[] = $filters['method'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(p.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(p.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} p
                     LEFT JOIN sp_clients c ON p.client_id = c.id
                     LEFT JOIN sp_invoices i ON p.invoice_id = i.id
                     {$whereClause}";
        $countStmt = DB::query($countSql, $params);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results
        $sql = "SELECT p.*, c.name as client_name, c.type as client_type,
                       i.grand_total as invoice_total
                FROM {$this->table} p
                LEFT JOIN sp_clients c ON p.client_id = c.id
                LEFT JOIN sp_invoices i ON p.invoice_id = i.id
                {$whereClause}
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        $stmt = DB::query($sql, $params);
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

    public function getWithDetails(int $paymentId): ?array
    {
        $sql = "SELECT p.*, c.name as client_name, c.type as client_type, c.email as client_email,
                       c.phone as client_phone, c.address as client_address,
                       i.grand_total as invoice_total, i.paid_total as invoice_paid,
                       i.status as invoice_status, i.created_at as invoice_date
                FROM {$this->table} p
                LEFT JOIN sp_clients c ON p.client_id = c.id
                LEFT JOIN sp_invoices i ON p.invoice_id = i.id
                WHERE p.id = ?";
        $stmt = DB::query($sql, [$paymentId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByInvoice(int $invoiceId): array
    {
        $sql = "SELECT p.*, c.name as client_name
                FROM {$this->table} p
                LEFT JOIN sp_clients c ON p.client_id = c.id
                WHERE p.invoice_id = ?
                ORDER BY p.created_at DESC";
        $stmt = DB::query($sql, [$invoiceId]);
        return $stmt->fetchAll();
    }

    public function getByClient(int $clientId, int $limit = 10): array
    {
        $sql = "SELECT p.*, i.grand_total as invoice_total
                FROM {$this->table} p
                LEFT JOIN sp_invoices i ON p.invoice_id = i.id
                WHERE p.client_id = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        $stmt = DB::query($sql, [$clientId, $limit]);
        return $stmt->fetchAll();
    }

    public function getPaymentMethods(): array
    {
        $sql = "SELECT DISTINCT method FROM {$this->table} ORDER BY method ASC";
        $stmt = DB::query($sql);
        $results = $stmt->fetchAll();
        return array_column($results, 'method');
    }

    public function getPaymentSummary(int $days = 30): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_payments,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_amount,
                    COUNT(DISTINCT client_id) as unique_clients,
                    COUNT(DISTINCT DATE(created_at)) as active_days
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = DB::query($sql, [$days]);
        $summary = $stmt->fetch();
        
        // Get method breakdown
        $methodSql = "SELECT method, COUNT(*) as count, SUM(amount) as total
                      FROM {$this->table}
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                      GROUP BY method
                      ORDER BY total DESC";
        $methodStmt = DB::query($methodSql, [$days]);
        $methods = $methodStmt->fetchAll();
        
        // Get daily totals for chart
        $dailySql = "SELECT DATE(created_at) as payment_date, 
                            COUNT(*) as count, 
                            SUM(amount) as total
                     FROM {$this->table}
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                     GROUP BY DATE(created_at)
                     ORDER BY payment_date DESC";
        $dailyStmt = DB::query($dailySql, [$days]);
        $dailyTotals = $dailyStmt->fetchAll();
        
        return [
            'summary' => $summary,
            'methods' => $methods,
            'daily_totals' => $dailyTotals
        ];
    }

    public function reversePayment(int $paymentId): bool
    {
        $payment = $this->find($paymentId);
        if (!$payment) {
            throw new \Exception('Payment not found');
        }

        DB::beginTransaction();

        try {
            // Delete the payment record
            $this->delete($paymentId);

            // Update invoice paid total and status
            $invoiceModel = new Invoice();
            $invoice = $invoiceModel->find($payment['invoice_id']);
            
            if ($invoice) {
                $newPaidTotal = $invoice['paid_total'] - $payment['amount'];
                $invoiceModel->update($payment['invoice_id'], ['paid_total' => $newPaidTotal]);

                // Update invoice status
                $newStatus = 'open';
                if ($newPaidTotal > 0) {
                    $newStatus = 'partial';
                }
                if ($newPaidTotal >= $invoice['grand_total']) {
                    $newStatus = 'paid';
                }
                
                $invoiceModel->update($payment['invoice_id'], ['status' => $newStatus]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function exportData(array $filters = []): array
    {
        $conditions = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $searchQuery = "%{$filters['search']}%";
            $conditions[] = "(c.name LIKE ? OR p.note LIKE ? OR p.method LIKE ?)";
            $params = array_merge($params, [$searchQuery, $searchQuery, $searchQuery]);
        }
        
        if (!empty($filters['client_id'])) {
            $conditions[] = "p.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        if (!empty($filters['method'])) {
            $conditions[] = "p.method = ?";
            $params[] = $filters['method'];
        }
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "DATE(p.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "DATE(p.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT p.*, c.name as client_name, c.type as client_type
                FROM {$this->table} p
                LEFT JOIN sp_clients c ON p.client_id = c.id
                {$whereClause}
                ORDER BY p.created_at DESC";
        $stmt = DB::query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getTotalByClient(int $clientId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM {$this->table} WHERE client_id = ?";
        $stmt = DB::query($sql, [$clientId]);
        $result = $stmt->fetch();
        return (float) $result['total'];
    }

    public function getRecentPayments(int $limit = 10): array
    {
        $sql = "SELECT p.*, c.name as client_name, c.type as client_type
                FROM {$this->table} p
                LEFT JOIN sp_clients c ON p.client_id = c.id
                ORDER BY p.created_at DESC
                LIMIT ?";
        $stmt = DB::query($sql, [$limit]);
        return $stmt->fetchAll();
    }
}
