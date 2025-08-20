<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\DB;

class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function find(int $id): ?array
    {
        $stmt = DB::query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function all(): array
    {
        $stmt = DB::query("SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        DB::query($sql, array_values($data));
        
        return (int) DB::lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        
        if (empty($data)) {
            return false;
        }
        
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = DB::query($sql, $params);
        
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = DB::query("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function where(string $column, $value): array
    {
        $stmt = DB::query("SELECT * FROM {$this->table} WHERE {$column} = ?", [$value]);
        return $stmt->fetchAll();
    }

    public function first(string $column, $value): ?array
    {
        $stmt = DB::query("SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1", [$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalStmt = DB::query("SELECT COUNT(*) as total FROM {$this->table}");
        $total = $totalStmt->fetch()['total'];
        
        // Get paginated results
        $stmt = DB::query("SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT ? OFFSET ?", [$perPage, $offset]);
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
