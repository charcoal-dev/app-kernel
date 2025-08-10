<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Cache;

use Charcoal\App\Kernel\Cache\RuntimeCache;

/**
 * Interface RuntimeCacheOwnerInterface
 * @package Charcoal\App\Kernel\Contracts\Cache
 */
interface RuntimeCacheOwnerInterface
{
    public function initializePrivateRuntimeCache(): true;

    public function normalizeStorageKey(string $key): string;

    public function getRuntimeMemory(): RuntimeCache;
}