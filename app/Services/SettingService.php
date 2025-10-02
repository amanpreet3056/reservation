<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\SettingRepository;

class SettingService
{
    public function __construct(private SettingRepository $settings)
    {
    }

    public function get(string $key, $default = null)
    {
        $value = $this->settings->getValue($key);
        return $value !== null ? $value : $default;
    }

    public function set(string $key, string $value, ?string $category = null): void
    {
        $this->settings->upsert($key, $value, $category);
    }

    public function setMany(string $category, array $values): void
    {
        foreach ($values as $key => $value) {
            $this->settings->upsert($key, (string) $value, $category);
        }
    }

    public function getCategory(string $category): array
    {
        return $this->settings->getCategory($category);
    }
}