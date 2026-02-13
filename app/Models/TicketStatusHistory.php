<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class TicketStatusHistory extends BaseModel
{
    protected function getTable(): string
    {
        return 'ticket_status_history';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    public function findByTicketId(int $ticketId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT h.*, u.name AS changed_by_name 
             FROM ticket_status_history h 
             JOIN users u ON h.changed_by = u.id 
             WHERE h.ticket_id = ? 
             ORDER BY h.created_at ASC'
        );
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function create(int $ticketId, ?string $oldStatus, string $newStatus, int $changedBy, ?string $note = null): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO ticket_status_history (ticket_id, old_status, new_status, changed_by, note) 
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$ticketId, $oldStatus, $newStatus, $changedBy, $note]);
        return (int) $this->pdo->lastInsertId();
    }
}
