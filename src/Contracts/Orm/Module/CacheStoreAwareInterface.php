<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Orm\Module;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\Cache\Cache;

/**
 * Interface CacheStoreAwareInterface
 * @package Charcoal\App\Kernel\Contracts\Orm\Module
 */
interface CacheStoreAwareInterface
{
    public function initializeCacheStoreAwareContainer(): true;

    public function normalizeStorageKey(string $key): string;

    public function declareCacheStoreEnum(): CacheStoreEnumInterface;

    public function getCacheStore(): ?Cache;
}