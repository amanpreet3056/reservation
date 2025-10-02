<?php
declare(strict_types=1);

namespace App\Repositories;

use mysqli;
use mysqli_stmt;
use RuntimeException;

class TableRepository
{
    public function __construct(private mysqli $connection)
    {
    }

    public function all(): array
    {
        $sql = 'SELECT id, name, capacity, location_hint, status, notes, created_at, updated_at FROM restaurant_tables ORDER BY name ASC';
        $result = $this->connection->query($sql);
        if (!$result) {
            throw new RuntimeException('Unable to fetch tables: ' . $this->connection->error);
        }

        $tables = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();

        return $tables;
    }

    public function active(): array
    {
        $sql = 'SELECT id, name, capacity, location_hint FROM restaurant_tables WHERE status = "active" ORDER BY name ASC';
        $result = $this->connection->query($sql);
        if (!$result) {
            throw new RuntimeException('Unable to fetch active tables: ' . $this->connection->error);
        }

        $tables = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();

        return $tables;
    }

    public function findActiveById(int $id): ?array
    {
        $stmt = $this->prepare('SELECT id, name, capacity, status FROM restaurant_tables WHERE id = ? AND status = "active" LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $table = $result->fetch_assoc() ?: null;
        $stmt->close();

        return $table;
    }

    public function existsByName(string $name, ?int $ignoreId = null): bool
    {
        if ($ignoreId) {
            $stmt = $this->prepare('SELECT id FROM restaurant_tables WHERE name = ? AND id <> ? LIMIT 1');
            $stmt->bind_param('si', $name, $ignoreId);
        } else {
            $stmt = $this->prepare('SELECT id FROM restaurant_tables WHERE name = ? LIMIT 1');
            $stmt->bind_param('s', $name);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $exists = (bool) $result->fetch_assoc();
        $stmt->close();

        return $exists;
    }

    public function create(string $name, int $capacity, ?string $location = null, string $status = 'active', ?string $notes = null): int
    {
        $stmt = $this->prepare('INSERT INTO restaurant_tables (name, capacity, location_hint, status, notes) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sisss', $name, $capacity, $location, $status, $notes);

        if (!$stmt->execute()) {
            throw new RuntimeException('Unable to create table: ' . $stmt->error);
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return (int) $id;
    }

    public function count(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) AS total FROM restaurant_tables');
        if (!$result) {
            throw new RuntimeException('Unable to count tables: ' . $this->connection->error);
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