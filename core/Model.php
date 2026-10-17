<?php
/**
 * Base Model
 * Model ทุกตัวต้อง extends class นี้
 */
class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';

    /**
     * Find by ID
     */
    public function find(int $id): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    /**
     * Get all records
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy}"
        );
    }

    /**
     * Find by condition
     */
    public function where(string $column, mixed $value): array
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }

    /**
     * Find one by condition
     */
    public function findWhere(string $column, mixed $value): ?array
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }

    /**
     * Insert record
     */
    public function create(array $data): int
    {
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        return Database::insert(
            "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): int
    {
        $setClause = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $id;

        return Database::execute(
            "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?",
            $values
        );
    }

    /**
     * Delete record
     */
    public function delete(int $id): int
    {
        return Database::execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    /**
     * Count records
     */
    public function count(string $condition = '1=1', array $params = []): int
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE {$condition}",
            $params
        );
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Paginate records
     */
    public function paginate(int $page = 1, int $perPage = 20, string $condition = '1=1', array $params = [], string $orderBy = 'id DESC'): array
    {
        $total = $this->count($condition, $params);
        $totalPages = (int) ceil($total / $perPage);
        $page = max(1, min($page, $totalPages ?: 1));
        $offset = ($page - 1) * $perPage;

        $rows = Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE {$condition} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $rows,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }
}
