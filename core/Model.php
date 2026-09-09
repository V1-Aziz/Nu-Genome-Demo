<?php
namespace Core;

/**
 * Base model. Subclasses declare $table and inherit basic lookups;
 * anything more specific belongs in the subclass as a named method.
 */
abstract class Model
{
    protected string $table;

    public function find(int $id): ?array
    {
        return Database::selectOne(
            "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        return Database::select("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
    }

    public function delete(int $id): bool
    {
        return Database::run("DELETE FROM {$this->table} WHERE id = ?", [$id])->rowCount() > 0;
    }
}
