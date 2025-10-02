<?php
declare(strict_types=1);

use App\Database\Connection;
use App\Repositories\SettingRepository;
use mysqli;
use RuntimeException;

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        $config = $GLOBALS['app_config'] ?? [];
        $segments = explode('.', $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = (string) config('app.url', '');
        if ($base === '') {
            return '/' . ltrim($path, '/');
        }

        if ($path === '') {
            return rtrim($base, '/');
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('admin_url')) {
    function admin_url(string $path = ''): string
    {
        return url('admin/' . ltrim($path, '/'));
    }
}

if (!function_exists('db')) {
    function db(): mysqli
    {
        static $connection = null;

        if ($connection instanceof mysqli) {
            return $connection;
        }

        $config = config('db');
        if (!is_array($config)) {
            throw new RuntimeException('Database configuration is missing.');
        }

        $connection = Connection::getInstance($config);

        return $connection;
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        if (!preg_match('/^https?:\/\//i', $path)) {
            $path = url($path);
        }

        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('admin_session_key')) {
    function admin_session_key(): string
    {
        return (string) config('auth.session_key', 'admin_id');
    }
}

if (!function_exists('current_admin_id')) {
    function current_admin_id(): ?int
    {
        $key = admin_session_key();
        return isset($_SESSION[$key]) ? (int) $_SESSION[$key] : null;
    }
}

if (!function_exists('current_admin_role')) {
    function current_admin_role(): ?string
    {
        return isset($_SESSION['admin_role']) ? (string) $_SESSION['admin_role'] : null;
    }
}

if (!function_exists('is_admin_authenticated')) {
    function is_admin_authenticated(): bool
    {
        return current_admin_id() !== null;
    }
}

if (!function_exists('require_admin_auth')) {
    function require_admin_auth(): void
    {
        if (!is_admin_authenticated()) {
            redirect('admin/login.php');
        }
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $value = null)
    {
        if ($value === null) {
            if (!isset($_SESSION['_flash'][$key])) {
                return null;
            }
            $message = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $message;
        }

        $_SESSION['_flash'][$key] = $value;
        return null;
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        $value = $_SESSION['_old_input'][$key] ?? $default;
        return is_string($value) ? $value : $default;
    }
}

if (!function_exists('remember_old_input')) {
    function remember_old_input(array $input): void
    {
        $_SESSION['_old_input'] = $input;
    }
}

if (!function_exists('clear_old_input')) {
    function clear_old_input(): void
    {
        unset($_SESSION['_old_input']);
    }
}

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $repository = new SettingRepository(db());
        $value = $repository->getValue($key);

        $cache[$key] = $value !== null ? $value : $default;

        return $cache[$key];
    }
}