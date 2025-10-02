<?php
declare(strict_types=1);

namespace App\Repositories;

use mysqli;
use mysqli_stmt;
use RuntimeException;

class ReservationRepository
{
    public function __construct(private mysqli $connection)
    {
    }

    public function create(array $data): array
    {
        $sql = 'INSERT INTO reservations (restaurant_name, people, reservation_date, reservation_time, fname, lname, email, phone, purpose, message, token, status, guest_id, table_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->prepare($sql);

        $guestId = $data['guest_id'] ?? null;
        $tableId = $data['table_id'] ?? null;

        $stmt->bind_param(
            'sissssssssssii',
            $data['restaurant_name'],
            $data['people'],
            $data['reservation_date'],
            $data['reservation_time'],
            $data['fname'],
            $data['lname'],
            $data['email'],
            $data['phone'],
            $data['purpose'],
            $data['message'],
            $data['token'],
            $data['status'],
            $guestId,
            $tableId
        );

        if (!$stmt->execute()) {
            throw new RuntimeException('Unable to create reservation: ' . $stmt->error);
        }

        $insertId = $stmt->insert_id;
        $stmt->close();

        return [
            'id' => (int) $insertId,
            'token' => $data['token'],
        ];
    }

    public function findActiveByToken(int $id, string $token): ?array
    {
        $sql = 'SELECT r.*, t.name AS table_name
                FROM reservations r
                LEFT JOIN restaurant_tables t ON r.table_id = t.id
                WHERE r.id = ? AND r.token = ? AND r.status = "active" LIMIT 1';
        $stmt = $this->prepare($sql);
        $stmt->bind_param('is', $id, $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservation = $result->fetch_assoc() ?: null;
        $stmt->close();

        return $reservation;
    }

    public function cancel(int $id): bool
    {
        $sql = 'UPDATE reservations SET status = "cancelled", updated_at = NOW() WHERE id = ? AND status = "active" LIMIT 1';
        $stmt = $this->prepare($sql);
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateDateTime(int $id, string $date, string $time): bool
    {
        $sql = 'UPDATE reservations SET reservation_date = ?, reservation_time = ?, updated_at = NOW() WHERE id = ? LIMIT 1';
        $stmt = $this->prepare($sql);
        $stmt->bind_param('ssi', $date, $time, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function recent(int $limit = 20): array
    {
        $sql = 'SELECT r.id, r.fname, r.lname, r.email, r.reservation_date, r.reservation_time, r.people, r.status, r.created_at,
                       t.name AS table_name
                FROM reservations r
                LEFT JOIN restaurant_tables t ON r.table_id = t.id
                ORDER BY r.reservation_date DESC, r.reservation_time DESC
                LIMIT ?';
        $stmt = $this->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservations = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $reservations;
    }

    public function all(int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT r.id, r.reservation_date, r.reservation_time, r.people, r.status, r.fname, r.lname, r.email, r.phone,
                       r.purpose, r.message, r.created_at, t.name AS table_name
                FROM reservations r
                LEFT JOIN restaurant_tables t ON r.table_id = t.id
                ORDER BY r.reservation_date DESC, r.reservation_time DESC
                LIMIT ? OFFSET ?';
        $stmt = $this->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservations = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $reservations;
    }

    public function countAll(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) AS total FROM reservations');
        if (!$result) {
            throw new RuntimeException('Unable to count reservations: ' . $this->connection->error);
        }

        $row = $result->fetch_assoc();
        $result->free();

        return (int) ($row['total'] ?? 0);
    }

    public function countUpcoming(): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM reservations WHERE reservation_date >= CURDATE() AND status = "active"';
        $result = $this->connection->query($sql);
        if (!$result) {
            throw new RuntimeException('Unable to count upcoming reservations: ' . $this->connection->error);
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