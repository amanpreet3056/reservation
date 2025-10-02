<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\TableRepository;
use InvalidArgumentException;

class TableService
{
    public function __construct(private TableRepository $tables)
    {
    }

    public function create(string $name, int $capacity, ?string $locationHint = null, string $status = 'active', ?string $notes = null): int
    {
        $name = trim($name);
        $locationHint = $locationHint !== null ? trim($locationHint) : null;
        $notes = $notes !== null ? trim($notes) : null;
        $status = in_array($status, ['active', 'inactive'], true) ? $status : 'active';

        if ($name === '') {
            throw new InvalidArgumentException('Table name is required.');
        }

        if ($capacity <= 0) {
            throw new InvalidArgumentException('Capacity must be at least 1.');
        }

        if ($this->tables->existsByName($name)) {
            throw new InvalidArgumentException('A table with this name already exists.');
        }

        return $this->tables->create($name, $capacity, $locationHint, $status, $notes);
    }

    public function all(): array
    {
        return $this->tables->all();
    }

    public function active(): array
    {
        return $this->tables->active();
    }

    public function count(): int
    {
        return $this->tables->count();
    }
}