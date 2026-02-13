<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class User extends BaseModel
{
    protected function getTable(): string
    {
        return 'users';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findAllAgents(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE role IN ('admin','agent') AND is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    /** @param string[] $roles */
    public function findByRoles(array $roles): array
    {
        if (empty($roles)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role IN ($placeholders) AND is_active = 1 ORDER BY name");
        $stmt->execute($roles);
        return $stmt->fetchAll();
    }

    public function findAllRequesters(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE role = 'requester' AND is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    public function findAllActive(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users WHERE is_active = 1 ORDER BY name');
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role, must_change_password) VALUES (?, ?, ?, ?, 1)'
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['password_hash'],
            $data['role'] ?? 'requester',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'email', 'role', 'is_active', 'password_hash', 'must_change_password'];
        $set = [];
        $params = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $set[] = "$key = ?";
                $params[] = $value;
            }
        }
        if (empty($set)) {
            return false;
        }
        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
