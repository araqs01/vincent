<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheableTrait
{
    /**
     * Универсальный метод кеширования с callback.
     */
    public function rememberCache(string $key, callable $callback, int $ttl = 3600, bool $forceRefresh = false)
    {
        if ($forceRefresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Удалить кеш по ключу.
     */
    public function forgetCache(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Проверить, существует ли кеш.
     */
    public function hasCache(string $key): bool
    {
        return Cache::has($key);
    }

    /**
     * Получить кеш напрямую.
     */
    public function getCache(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    /**
     * Сформировать ключ кеша с автоматическим языком.
     */
    public function cacheKey(string $prefix, string $slug = null): string
    {
        return implode('-', array_filter([
            $prefix,
            $slug,
            app()->getLocale(), // язык из твоего middleware
        ]));
    }
}
