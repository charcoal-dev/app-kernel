<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Orm\Module;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\Cache\CacheClient;

/**
 * Interface CacheStoreAwareInterface
 * @package Charcoal\App\Kernel\Contracts\Orm\Module
 */
interface CacheStoreAwareInterface
{
    public function initializeCacheStoreAwareContainer(): true;

    public function normalizeStorageKey(string $key): string;

    public function declareCacheStoreEnum(): CacheStoreEnumInterface;

    public function getCacheStore(): ?CacheClient;
}