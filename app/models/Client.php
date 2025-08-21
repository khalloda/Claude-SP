<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class Client extends Model
{
    protected string $table = 'sp_clients';
    
    protected array $fillable = [
        'type',
        'name',
        'phone',
        'email',
        'address'
    ];

    public function search(string $query, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $searchQuery = "%{$query}%";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?";
        $countStmt = DB::query($countSql, [$searchQuery, $searchQuery, $searchQuery]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?
                ORDER BY name ASC 
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

    public function getQuotes(int $clientId): array
    {
        $sql = "SELECT * FROM sp_quotes WHERE client_id = ? ORDER BY created_at DESC";
        $stmt = DB::query($sql, [$clientId]);
        return $stmt->fetchAll();
    }

    public function getSalesOrders(int $clientId): array
    {
        $sql = "SELECT * FROM sp_sales_orders WHERE client_id = ? ORDER BY created_at DESC";
        $stmt = DB::query($sql, [$clientId]);
        return $stmt->fetchAll();
    }

    public function getInvoices(int $clientId): array
    {
        $sql = "SELECT * FROM sp_invoices WHERE client_id = ? ORDER BY created_at DESC";
        $stmt = DB::query($sql, [$clientId]);
        return $stmt->fetchAll();
    }

    public function getPayments(int $clientId): array
    {
        $sql = "SELECT p.*, i.grand_total as invoice_total 
                FROM sp_payments p 
                LEFT JOIN sp_invoices i ON p.invoice_id = i.id 
                WHERE p.client_id = ? 
                ORDER BY p.created_at DESC";
        $stmt = DB::query($sql, [$clientId]);
        return $stmt->fetchAll();
    }

    public function getBalance(int $clientId): array
    {
        // Get total invoice amounts
        $invoiceSql = "SELECT COALESCE(SUM(grand_total), 0) as total_invoiced 
                       FROM sp_invoices WHERE client_id = ? AND status != 'void'";
        $invoiceStmt = DB::query($invoiceSql, [$clientId]);
        $totalInvoiced = $invoiceStmt->fetch()['total_invoiced'];
        
        // Get total payments
        $paymentSql = "SELECT COALESCE(SUM(amount), 0) as total_paid 
                       FROM sp_payments WHERE client_id = ?";
        $paymentStmt = DB::query($paymentSql, [$clientId]);
        $totalPaid = $paymentStmt->fetch()['total_paid'];
        
        return [
            'total_invoiced' => (float) $totalInvoiced,
            'total_paid' => (float) $totalPaid,
            'balance' => (float) ($totalInvoiced - $totalPaid)
        ];
    }
}
