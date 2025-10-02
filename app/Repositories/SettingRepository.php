<?php
declare(strict_types=1);

namespace App\Repositories;

use mysqli;
use mysqli_stmt;
use RuntimeException;

class SettingRepository
{
    public function __construct(private mysqli $connection)
    {
    }

    public function getValue(string $key): ?string
    {
        $stmt = $this->prepare('SELECT value FROM settings WHERE `key` = ? LIMIT 1');
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['value'] ?? null;
    }

    public function upsert(string $key, string $value, ?string $category = null): void
    {
        $existing = $this->getValue($key);

        if ($existing === null) {
            $stmt = $this->prepare('INSERT INTO settings (`key`, value, category) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $key, $value, $category);
        } else {
            $stmt = $this->prepare('UPDATE settings SET value = ?, category = ?, updated_at = CURRENT_TIMESTAMP WHERE `key` = ?');
            $stmt->bind_param('sss', $value, $category, $key);
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Unable to save setting: ' . $stmt->error);
        }

        $stmt->close();
    }

    public function getCategory(string $category): array
    {
        $stmt = $this->prepare('SELECT `key`, value FROM settings WHERE category = ?');
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['key']] = $row['value'];
        }
        $stmt->close();

        return $settings;
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