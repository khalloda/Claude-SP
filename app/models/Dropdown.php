<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Config\DB;

class Dropdown extends Model
{
    protected string $table = 'sp_dropdowns';
    
    protected array $fillable = [
        'category',
        'value',
        'parent_id'
    ];

    public function getByCategory(string $category, ?int $parentId = null): array
    {
        if ($parentId !== null) {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE category = ? AND parent_id = ? 
                    ORDER BY value ASC";
            $stmt = DB::query($sql, [$category, $parentId]);
        } else {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE category = ? AND parent_id IS NULL 
                    ORDER BY value ASC";
            $stmt = DB::query($sql, [$category]);
        }
        
        return $stmt->fetchAll();
    }

    public function getCategories(): array
    {
        return [
            'classification' => 'Product Type/Classification',
            'color' => 'Colors',
            'brand' => 'Brands',
            'car_make' => 'Car Makes',
            'car_model' => 'Car Models'
        ];
    }

    public function getAllByCategory(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY category, value ASC";
        $stmt = DB::query($sql);
        $results = $stmt->fetchAll();
        
        $grouped = [];
        foreach ($results as $item) {
            $grouped[$item['category']][] = $item;
        }
        
        return $grouped;
    }

    public function deleteWithChildren(int $id): bool
    {
        DB::beginTransaction();
        
        try {
            // Delete children first
            $childrenSql = "DELETE FROM {$this->table} WHERE parent_id = ?";
            DB::query($childrenSql, [$id]);
            
            // Delete parent
            $parentSql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = DB::query($parentSql, [$id]);
            
            DB::commit();
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
