<?php

declare(strict_types=1);

namespace App\Models;

class Tecnico extends BaseModel
{
    private const FIELDS = [
        'name', 'naturalidade', 'email', 'rg', 'cpf', 'data_nascimento', 'genero',
        'nome_mae', 'nome_pai', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'pais',
        'celular_1', 'celular_2', 'whats', 'telefone_fixo', 'telefone',
        'referencia_bancaria', 'chave_pix', 'banco', 'cod_banco', 'agencia', 'conta', 'tipo_conta', 'operacao', 'favorecido',
        'razao_social', 'nome_fantasia', 'cnpj', 'inscricao_estadual', 'inscricao_municipal',
        'empresa_cep', 'empresa_endereco', 'empresa_numero', 'empresa_bairro', 'empresa_cidade', 'empresa_estado', 'empresa_pais',
        'empresa_referencia_bancaria', 'empresa_chave_pix', 'empresa_banco', 'empresa_cod_banco', 'empresa_agencia', 'empresa_conta', 'empresa_tipo_conta', 'empresa_operacao', 'empresa_favorecido',
    ];

    protected function getTable(): string
    {
        return 'tecnicos';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    public function create(array $data): int
    {
        $cols = [];
        $placeholders = [];
        $params = [];
        foreach (self::FIELDS as $f) {
            if (array_key_exists($f, $data)) {
                $cols[] = $f;
                $placeholders[] = '?';
                $params[] = $data[$f] === '' ? null : $data[$f];
            }
        }
        if (empty($cols) || !in_array('name', $cols, true)) {
            throw new \InvalidArgumentException('name is required');
        }
        $sql = 'INSERT INTO tecnicos (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $this->pdo->prepare($sql)->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = [];
        $params = [];
        foreach (self::FIELDS as $f) {
            if (array_key_exists($f, $data)) {
                $set[] = "$f = ?";
                $params[] = $data[$f] === '' ? null : $data[$f];
            }
        }
        if (empty($set)) {
            return false;
        }
        $params[] = $id;
        $sql = 'UPDATE tecnicos SET ' . implode(', ', $set) . ' WHERE id = ?';
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tecnicos WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function search(string $q): array
    {
        if (trim($q) === '') {
            return $this->all('name');
        }
        $term = '%' . trim($q) . '%';
        $stmt = $this->pdo->prepare(
            'SELECT * FROM tecnicos WHERE name LIKE ? OR email LIKE ? OR cpf LIKE ? OR celular_1 LIKE ? OR telefone LIKE ? ORDER BY name ASC'
        );
        $stmt->execute([$term, $term, $term, $term, $term]);
        return $stmt->fetchAll();
    }
}
