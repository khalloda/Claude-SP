<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class Product extends Model
{
    protected string $table = 'sp_products';
    
    protected array $fillable = [
        'classification',
        'code',
        'name',
        'cost_price',
        'sale_price',
        'color',           // Added this
        'brand',           // Added this
        'car_make',        // Added this
        'car_model',       // Added this
        'total_qty',       // Added this
        'reserved_quotes',
        'reserved_orders'
    ];

    public function generateCode(string $classification): string
    {
        // Get classification prefix (first 3 letters, uppercase)
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $classification), 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, '0');
        }
        
        // Get next number for this classification
        $sql = "SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE code LIKE ?";
        $stmt = DB::query($sql, [$prefix . '%']);
        $result = $stmt->fetch();
        $nextNum = ($result['max_num'] ?? 0) + 1;
        
        return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function search(string $query, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $searchQuery = "%{$query}%";
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE name LIKE ? OR code LIKE ? OR classification LIKE ? OR brand LIKE ?";
        $countStmt = DB::query($countSql, [$searchQuery, $searchQuery, $searchQuery, $searchQuery]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated results
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR code LIKE ? OR classification LIKE ? OR brand LIKE ?
                ORDER BY name ASC 
                LIMIT ? OFFSET ?";
        $stmt = DB::query($sql, [$searchQuery, $searchQuery, $searchQuery, $searchQuery, $perPage, $offset]);
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

    public function getLocations(int $productId): array
    {
        $sql = "SELECT pl.*, w.name as warehouse_name 
                FROM sp_product_locations pl 
                JOIN sp_warehouses w ON pl.warehouse_id = w.id 
                WHERE pl.product_id = ? 
                ORDER BY w.name ASC";
        $stmt = DB::query($sql, [$productId]);
        return $stmt->fetchAll();
    }

    public function getStockMovements(int $productId, int $limit = 20): array
    {
        $sql = "SELECT * FROM sp_stock_movements 
                WHERE product_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        $stmt = DB::query($sql, [$productId, $limit]);
        return $stmt->fetchAll();
    }

    public function updateLocation(int $productId, int $warehouseId, float $qty, string $locationLabel = ''): void
    {
        // Check if location exists
        $existsSql = "SELECT id FROM sp_product_locations WHERE product_id = ? AND warehouse_id = ?";
        $existsStmt = DB::query($existsSql, [$productId, $warehouseId]);
        
        if ($existsStmt->fetch()) {
            // Update existing
            $updateSql = "UPDATE sp_product_locations 
                         SET qty = ?, location_label = ? 
                         WHERE product_id = ? AND warehouse_id = ?";
            DB::query($updateSql, [$qty, $locationLabel, $productId, $warehouseId]);
        } else {
            // Insert new
            $insertSql = "INSERT INTO sp_product_locations (product_id, warehouse_id, qty, location_label) 
                         VALUES (?, ?, ?, ?)";
            DB::query($insertSql, [$productId, $warehouseId, $qty, $locationLabel]);
        }
    }

    public function create(array $data): int
    {
        // Generate code if not provided
        if (empty($data['code']) && !empty($data['classification'])) {
            $data['code'] = $this->generateCode($data['classification']);
        }
        
        return parent::create($data);
    }
}
