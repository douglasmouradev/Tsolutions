<?php

declare(strict_types=1);

namespace App\Models;

class Unidade extends BaseModel
{
    protected function getTable(): string
    {
        return 'unidades';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO unidades (name, sigla, endereco, bairro, cidade, uf, cep, centro_de_lucro) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['sigla'] ?? null,
            $data['endereco'] ?? null,
            $data['bairro'] ?? null,
            $data['cidade'] ?? null,
            $data['uf'] ?? null,
            $data['cep'] ?? null,
            $data['centro_de_lucro'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE unidades SET name = ?, sigla = ?, endereco = ?, bairro = ?, cidade = ?, uf = ?, cep = ?, centro_de_lucro = ? WHERE id = ?'
        );
        return $stmt->execute([
            $data['name'],
            $data['sigla'] ?? null,
            $data['endereco'] ?? null,
            $data['bairro'] ?? null,
            $data['cidade'] ?? null,
            $data['uf'] ?? null,
            $data['cep'] ?? null,
            $data['centro_de_lucro'] ?? null,
            $id,
        ]);
    }

    public function search(string $q): array
    {
        if (trim($q) === '') {
            return $this->all('name');
        }
        $term = '%' . trim($q) . '%';
        $stmt = $this->pdo->prepare(
            'SELECT * FROM unidades WHERE name LIKE ? OR sigla LIKE ? OR endereco LIKE ? OR bairro LIKE ? OR cidade LIKE ? OR uf LIKE ? OR cep LIKE ? OR centro_de_lucro LIKE ? ORDER BY name ASC'
        );
        $stmt->execute([$term, $term, $term, $term, $term, $term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM unidades WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
