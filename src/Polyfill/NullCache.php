<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Polyfill;

use Charcoal\Cache\CacheClient;
use Charcoal\Cache\Contracts\CacheDriverInterface;

/**
 * Class NullCache
 * @package Charcoal\App\Kernel\Polyfill
 */
class NullCache implements CacheDriverInterface
{
    public function createLink(CacheClient $cache): void
    {
    }

    public function isConnected(): bool
    {
        return true;
    }

    public function connect(): void
    {
    }

    public function disconnect(): void
    {
    }

    public function metaUniqueId(): string
    {
        return static::class;
    }

    public function metaPingSupported(): bool
    {
        return false;
    }

    public function ping(): bool
    {
        return false;
    }

    public function store(string $key, int|string $value, ?int $ttl = null): void
    {
    }

    public function resolve(string $key): int|string|null|bool
    {
        return null;
    }

    public function isStored(string $key): bool
    {
        return false;
    }

    public function delete(string $key): bool
    {
        return true;
    }

    public function truncate(): bool
    {
        return true;
    }
}