<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminRepository;
use RuntimeException;

class AuthService
{
    public function __construct(
        private AdminRepository $admins,
        private string $sessionKey
    ) {
    }

    public function ensureDefaultSuperAdmin(?array $defaults): void
    {
        if (empty($defaults)) {
            return;
        }

        if ($this->admins->count() > 0) {
            return;
        }

        $email = $defaults['email'] ?? null;
        $password = $defaults['password'] ?? null;
        $name = $defaults['name'] ?? 'Super Admin';

        if (!$email || !$password) {
            throw new RuntimeException('Default super admin configuration is incomplete.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->admins->create($name, $email, $hash, 'super_admin');
    }

    public function attempt(string $email, string $password): bool
    {
        $admin = $this->admins->findByEmail($email);
        if (!$admin) {
            return false;
        }

        $allowedRoles = config('auth.roles', ['super_admin']);
        if (!in_array($admin['role'], $allowedRoles, true)) {
            return false;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            return false;
        }

        if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
            $this->reHashPassword((int) $admin['id'], $password);
        }

        $_SESSION[$this->sessionKey] = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['name'] ?? 'Admin';
        $_SESSION['admin_role'] = $admin['role'] ?? null;
        \clear_old_input();

        return true;
    }

    public function logout(): void
    {
        unset($_SESSION[$this->sessionKey], $_SESSION['admin_name'], $_SESSION['admin_role']);
    }

    public function user(): ?array
    {
        $adminId = \current_admin_id();
        if ($adminId === null) {
            return null;
        }

        return $this->admins->findById($adminId);
    }

    private function reHashPassword(int $id, string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = \db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return;
        }

        $stmt->bind_param('si', $hash, $id);
        $stmt->execute();
        $stmt->close();
    }
}