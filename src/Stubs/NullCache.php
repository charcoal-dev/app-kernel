<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Stubs;

use Charcoal\Contracts\Storage\Cache\CacheAdapterInterface;
use Charcoal\Contracts\Storage\Cache\CacheClientInterface;

/**
 * Class NullCache
 * @package Charcoal\App\Kernel\Stubs
 */
final class NullCache implements CacheAdapterInterface
{
    public function createLink(CacheClientInterface $cache): void
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

    public function getId(): string
    {
        return self::class;
    }

    public function supportsPing(): bool
    {
        return false;
    }

    public function ping(): bool
    {
        return false;
    }

    public function set(string $key, int|string $value, ?int $ttl = null): void
    {
    }

    public function get(string $key): int|string|null|bool
    {
        return null;
    }

    public function has(string $key): bool
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