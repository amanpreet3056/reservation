<?php
declare(strict_types=1);

namespace App\Repositories;

use mysqli;
use mysqli_stmt;
use RuntimeException;

class AdminRepository
{
    public function __construct(private mysqli $connection)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->prepare('SELECT id, name, email, password_hash, role, created_at FROM admins WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc() ?: null;
        $stmt->close();

        return $admin;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->prepare('SELECT id, name, email, role, created_at FROM admins WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc() ?: null;
        $stmt->close();

        return $admin;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM admins';
        $result = $this->connection->query($sql);
        if (!$result) {
            throw new RuntimeException('Unable to count admins: ' . $this->connection->error);
        }

        $row = $result->fetch_assoc();
        $result->free();

        return (int) ($row['total'] ?? 0);
    }

    public function create(string $name, string $email, string $passwordHash, string $role = 'super_admin'): int
    {
        $stmt = $this->prepare('INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $passwordHash, $role);

        if (!$stmt->execute()) {
            throw new RuntimeException('Could not create admin: ' . $stmt->error);
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return (int) $id;
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        if ($ignoreId) {
            $stmt = $this->prepare('SELECT id FROM admins WHERE email = ? AND id <> ? LIMIT 1');
            $stmt->bind_param('si', $email, $ignoreId);
        } else {
            $stmt = $this->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $exists = (bool) $result->fetch_assoc();
        $stmt->close();

        return $exists;
    }

    public function updateProfile(int $id, string $name, string $email): void
    {
        $stmt = $this->prepare('UPDATE admins SET name = ?, email = ? WHERE id = ?');
        $stmt->bind_param('ssi', $name, $email, $id);
        if (!$stmt->execute()) {
            throw new RuntimeException('Unable to update profile: ' . $stmt->error);
        }
        $stmt->close();
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = $this->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
        $stmt->bind_param('si', $passwordHash, $id);
        if (!$stmt->execute()) {
            throw new RuntimeException('Unable to update password: ' . $stmt->error);
        }
        $stmt->close();
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