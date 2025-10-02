<?php
declare(strict_types=1);

namespace App\Repositories;

use mysqli;
use mysqli_stmt;
use RuntimeException;

class GuestRepository
{
    public function __construct(private mysqli $connection)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->prepare('SELECT id, first_name, last_name, email, phone, notes, created_at, updated_at FROM guests WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $guest = $result->fetch_assoc() ?: null;
        $stmt->close();

        return $guest;
    }

    public function create(string $firstName, string $lastName, string $email, string $phone = '', string $notes = ''): int
    {
        $stmt = $this->prepare('INSERT INTO guests (first_name, last_name, email, phone, notes) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $firstName, $lastName, $email, $phone, $notes);

        if (!$stmt->execute()) {
            throw new RuntimeException('Unable to create guest: ' . $stmt->error);
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return (int) $id;
    }

    public function updateContact(int $id, string $firstName, string $lastName, string $phone = ''): void
    {
        $stmt = $this->prepare('UPDATE guests SET first_name = ?, last_name = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->bind_param('sssi', $firstName, $lastName, $phone, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function allWithStats(int $limit = 200, int $offset = 0): array
    {
        $sql = 'SELECT g.id, g.first_name, g.last_name, g.email, g.phone, g.notes, g.created_at, g.updated_at,
                       COUNT(r.id) AS reservations_count,
                       MAX(r.reservation_date) AS last_reservation_date
                FROM guests g
                LEFT JOIN reservations r ON r.guest_id = g.id
                GROUP BY g.id
                ORDER BY g.created_at DESC
                LIMIT ? OFFSET ?';

        $stmt = $this->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $guests = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $guests;
    }

    public function count(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) AS total FROM guests');
        if (!$result) {
            throw new RuntimeException('Unable to count guests: ' . $this->connection->error);
        }

        $row = $result->fetch_assoc();
        $result->free();

        return (int) ($row['total'] ?? 0);
    }

    private function prepare(string $sql): mysqli_stmt
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . $this->connection->error);
        }

        return $stmt;
    }
}