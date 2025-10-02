<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminRepository;
use InvalidArgumentException;

class AdminService
{
    public function __construct(private AdminRepository $admins)
    {
    }

    public function updateProfile(int $id, string $name, string $email): void
    {
        $name = trim($name);
        $email = trim($email);

        if ($name === '') {
            throw new InvalidArgumentException('Name is required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid email address is required.');
        }

        if ($this->admins->emailExists($email, $id)) {
            throw new InvalidArgumentException('That email address is already in use.');
        }

        $this->admins->updateProfile($id, $name, $email);
    }

    public function updatePassword(int $id, string $password, string $confirm): void
    {
        if ($password === '' && $confirm === '') {
            return;
        }

        if ($password !== $confirm) {
            throw new InvalidArgumentException('Passwords do not match.');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->admins->updatePassword($id, $hash);
    }
}