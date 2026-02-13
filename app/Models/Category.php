<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Category extends BaseModel
{
    protected function getTable(): string
    {
        return 'categories';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE name = ? LIMIT 1');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$name]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, string $name): bool
    {
        $stmt = $this->pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
        return $stmt->execute([$name, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
