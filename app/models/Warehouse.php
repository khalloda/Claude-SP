<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class Warehouse extends Model
{
    protected string $table = 'sp_warehouses';
    
    protected array $fillable = [
        'name',
        'address',
        'capacity',
        'responsible_name',
        'responsible_email',
        'responsible_phone'
    ];

    public function search(string $query, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $searchQuery = "%{$query}%";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE name LIKE ? OR responsible_name LIKE ? OR address LIKE ?";
        $countStmt = DB::query($countSql, [$searchQuery, $searchQuery, $searchQuery]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR responsible_name LIKE ? OR address LIKE ?
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

    public function getProducts(int $warehouseId): array
    {
        $sql = "SELECT p.*, pl.qty as warehouse_qty, pl.location_label 
                FROM sp_products p 
                LEFT JOIN sp_product_locations pl ON p.id = pl.product_id 
                WHERE pl.warehouse_id = ? 
                ORDER BY p.name ASC";
        $stmt = DB::query($sql, [$warehouseId]);
        return $stmt->fetchAll();
    }

    public function getTotalValue(int $warehouseId): float
    {
        $sql = "SELECT COALESCE(SUM(p.cost_price * pl.qty), 0) as total_value 
                FROM sp_products p 
                JOIN sp_product_locations pl ON p.id = pl.product_id 
                WHERE pl.warehouse_id = ?";
        $stmt = DB::query($sql, [$warehouseId]);
        return (float) $stmt->fetch()['total_value'];
    }
}
