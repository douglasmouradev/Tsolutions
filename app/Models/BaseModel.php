<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract protected function getTable(): string;

    abstract protected function getPrimaryKey(): string;

    public function find(int|string $id): ?array
    {
        $sql = sprintf(
            'SELECT * FROM %s WHERE %s = ? LIMIT 1',
            $this->getTable(),
            $this->getPrimaryKey()
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(string $orderBy = 'id', string $order = 'ASC'): array
    {
        $sql = sprintf('SELECT * FROM %s ORDER BY %s %s', $this->getTable(), $orderBy, $order);
        return $this->pdo->query($sql)->fetchAll();
    }
}
