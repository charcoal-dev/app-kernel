<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Cache;

use Charcoal\Cache\Cache;

/**
 * Interface CacheStoreOperationsInterface
 * @package Charcoal\App\Kernel\Contracts\Cache
 */
interface CacheStoreOperationsInterface
{
    public function normalizeStorageKey(string $key): string;

    public function getCacheStore(): ?Cache;

    public function storeInCache(string $key, mixed $value, ?int $ttl = null, ?array $referenceKeys = null): void;

    public function getFromCache(string $key): mixed;

    public function deleteFromCache(string $key): void;
}