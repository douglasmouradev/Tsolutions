<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Ticket extends BaseModel
{
    protected function getTable(): string
    {
        return 'tickets';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    private const PLANILHA_FIELDS = [
        'cliente_raiz', 'cliente', 'sigla_unidade_loja_ag', 'tipo_contrato', 'tipo_ch', 'contrato_baseline',
        'sla', 'reversa', 'codigo_postagem', 'data_postagem', 'equipamento', 'n_serie', 'patrimonio', 'hostname',
        'usuario_contato', 'telefone_usuario', 'email_usuario', 'nome_solicitante_ch', 'numero_ch', 'moebius',
        'n_cl', 'n_tarefa_remessa', 'endereco', 'bairro', 'cidade', 'uf', 'cep', 'data_vencimento_ch',
        'data_disponibilidade', 'hora_disponibilidade', 'operacao', 'intercorrencias', 'observacao_tecnico',
        'nome_tecnico', 'cpf_tecnico', 'rg_tecnico', 'data_atendimento', 'hora_atendimento',
        'valor_tecnico', 'modalidade_tecnico',
    ];

    public function create(array $data): int
    {
        $base = ['title', 'description', 'requester_id', 'agent_id', 'category_id', 'priority', 'status', 'due_at'];
        $all = array_merge($base, self::PLANILHA_FIELDS);
        $cols = [];
        $placeholders = [];
        $params = [];
        foreach ($all as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $cols[] = $key;
            $placeholders[] = '?';
            $params[] = $data[$key] === '' ? null : $data[$key];
        }
        if (!in_array('title', $cols, true) || !in_array('requester_id', $cols, true)) {
            throw new \InvalidArgumentException('title and requester_id are required');
        }
        $defaults = ['priority' => 'baixa', 'status' => 'aberto'];
        foreach ($defaults as $k => $v) {
            if (!in_array($k, $cols, true)) {
                $cols[] = $k;
                $placeholders[] = '?';
                $params[] = $v;
            }
        }
        $sql = 'INSERT INTO tickets (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $this->pdo->prepare($sql)->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = array_merge(
            ['title', 'description', 'agent_id', 'category_id', 'priority', 'status', 'due_at', 'closed_at'],
            self::PLANILHA_FIELDS
        );
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
        $sql = 'UPDATE tickets SET ' . implode(', ', $set) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tickets WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function findWithRelations(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, 
                    r.name AS requester_name, r.email AS requester_email,
                    a.name AS agent_name, a.email AS agent_email,
                    c.name AS category_name
             FROM tickets t
             LEFT JOIN users r ON t.requester_id = r.id
             LEFT JOIN users a ON t.agent_id = a.id
             LEFT JOIN categories c ON t.category_id = c.id
             WHERE t.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countByStatus(): array
    {
        $stmt = $this->pdo->query(
            'SELECT status, COUNT(*) AS total FROM tickets GROUP BY status'
        );
        $rows = $stmt->fetchAll();
        $statuses = ['aberto' => 0, 'em_andamento' => 0, 'fechado' => 0, 'cancelado' => 0];
        foreach ($rows as $row) {
            $statuses[$row['status']] = (int) $row['total'];
        }
        return $statuses;
    }

    public function getLastUpdated(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, r.name AS requester_name, a.name AS agent_name
             FROM tickets t
             LEFT JOIN users r ON t.requester_id = r.id
             LEFT JOIN users a ON t.agent_id = a.id
             ORDER BY COALESCE(t.updated_at, t.created_at) DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function search(array $filters, int $page = 1, int $perPage = 25): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 't.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $where[] = 't.priority = ?';
            $params[] = $filters['priority'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 't.category_id = ?';
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['agent_id'])) {
            $where[] = 't.agent_id = ?';
            $params[] = $filters['agent_id'];
        }
        if (!empty($filters['requester_id'])) {
            $where[] = 't.requester_id = ?';
            $params[] = $filters['requester_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(t.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(t.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['sla_status']) && $filters['sla_status'] === 'vencidos') {
            $where[] = 't.due_at IS NOT NULL AND t.due_at < NOW() AND t.status NOT IN (\'fechado\', \'cancelado\')';
        }

        if (!empty($filters['q'])) {
            $term = trim($filters['q']);
            if ($term !== '') {
                $where[] = '(t.title LIKE ? OR t.description LIKE ?)';
                $like = '%' . $term . '%';
                $params[] = $like;
                $params[] = $like;
            }
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) FROM tickets t $whereClause";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $sql = "SELECT t.*, r.name AS requester_name, a.name AS agent_name, c.name AS category_name
                FROM tickets t
                LEFT JOIN users r ON t.requester_id = r.id
                LEFT JOIN users a ON t.agent_id = a.id
                LEFT JOIN categories c ON t.category_id = c.id
                $whereClause
                ORDER BY COALESCE(t.updated_at, t.created_at) DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return [
            'items' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }
}
