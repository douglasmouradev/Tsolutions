<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Attachment extends BaseModel
{
    protected function getTable(): string
    {
        return 'attachments';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    public function findByTicketId(int $ticketId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, u.name AS uploaded_by_name 
             FROM attachments a 
             JOIN users u ON a.uploaded_by = u.id 
             WHERE a.ticket_id = ? 
             ORDER BY a.created_at ASC'
        );
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO attachments (ticket_id, uploaded_by, stored_name, original_name, mime_type, size_bytes) 
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['ticket_id'],
            $data['uploaded_by'],
            $data['stored_name'],
            $data['original_name'],
            $data['mime_type'],
            $data['size_bytes'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM attachments WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
